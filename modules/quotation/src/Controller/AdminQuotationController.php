<?php

namespace Quotation\Controller;

use PrestaShop\PrestaShop\Core\Domain\Customer\Query\GetCustomerForViewing;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewableCustomer;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\Password;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Quotation\Entity\Quotation;
use Quotation\Form\QuotationSearchType;
use Quotation\Form\QuotationType;
use Quotation\Service\QuotationFileSystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminQuotationController extends FrameworkBundleAdminController
{

    /**
     * quotation_search type=array
     * Fonction privée qui récupère toutes les données à partir du tableau 'quotation_search
     */
    private function queryQuotation(Request $request)
    {
        return $request->query->all()['quotation_search'];
    }

    public function quotationIndex(Request $request)
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotations = $quotationRepository->findAll();

        $quotationFilterForm = $this->get('form.factory')->createNamed('', QuotationSearchType::class);
        $quotationFilterForm = $this->createForm(QuotationSearchType::class);

        $quotationFilterForm->handleRequest($request);
        if ($quotationFilterForm->isSubmitted() && $quotationFilterForm->isValid()) {

//            dump($end = $request->query->all()['quotation_search']  );die();

            $name = $this->queryQuotation($request)['name'];
            $reference = $this->queryQuotation($request)['reference'];
            $status = $this->queryQuotation($request)['status'];
            $start = $this->queryQuotation($request)['start']['date']['year'];
            $end = $this->queryQuotation($request)['end']['date']['year'];

            $_reference = $this->queryQuotation($request)['reference'];
            $_status = $this->queryQuotation($request)['status'];
            $_start = $this->queryQuotation($request)['start']['date']['year'];
            $_end = $this->queryQuotation($request)['end']['date']['year'];

            $quotations = $quotationRepository->findQuotationsByFilters($name, $reference, $status, $start, $end
                                                                        , $_reference, $_status, $_start, $_end
            );
        } else {
            $quotations = $quotationRepository->findAll();
        }

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'quotations' => $quotations,
            'quotationFilterForm' => $quotationFilterForm->createView(),
        ]);
    }

//    public function searchQuotationsByFilters(Request $request, $name, $reference, $status, $start, $end
//                                                                ,$_reference, $_status, $_start, $_end
//    )
//    {
////        dump('---------------Recherche simple---------------');
////        dump('name -> ' . $name);
////        dump('reference -> ' . $reference);
////        dump('status => ' . $status);
////        dump('interval_start (supérieures) -> ' . $start);
////        dump('interval_end (inférieures) -> ' . $end);
////
////
////        dump('---------------Recherche élargie---------------');
////        dump('_reference -> ' . $_reference);
////        dump('_status -> ' . $_status);
////        dump('_interval_start (_supérieures) -> ' . $_start);
////        dump('_interval_end (_inférieures) -> ' . $_end);
//

//    }

    public function add(Request $request)
    {
        $quotation = new Quotation();

        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);

        if (!$this->get('prestashop.adapter.shop.context')->isSingleShopContext()) {
            return $this->redirectToRoute('quotation_admin_add');
        }

        $this->redirect('@PrestaShop/Admin/Sell/Customer/CustomerController/addGroupSelectionToRequest'); // Permet d'appeler la méthode addGroupSelectionToRequest du CustomerController

        $customerForm = $this->get('prestashop.core.form.identifiable_object.builder.customer_form_builder')->getForm();
        $customerForm->handleRequest($request);

        $customerFormHandler = $this->get('prestashop.core.form.identifiable_object.handler.customer_form_handler');

        try {
            $result = $customerFormHandler->handle($customerForm);

            if ($customerId = $result->getIdentifiableObjectId()) {
                $this->addFlash('success', $this->trans('Successful creation.', 'Admin.Notifications.Success'));

                if ($request->query->has('submitFormAjax')) {
                    /** @var ViewableCustomer $customerInformation */
                    $customerInformation = $this->getQueryBus()->handle(new GetCustomerForViewing((int) $customerId));

                    return $this->render('@PrestaShop/Admin/Sell/Customer/modal_create_success.html.twig', [
                        'customerId' => $customerId,
                        'customerEmail' => $customerInformation->getPersonalInformation()->getEmail(),
                    ]);
                }

                return $this->redirectToRoute('quotation_admin_add');
            }
        } catch (Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $quotation->setDateAdd(new \DateTime('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($quotation);
            $entityManager->flush();

            return $this->redirectToRoute('quotation_admin');
        }

        return $this->render('@Modules/quotation/templates/admin/add_quotation.html.twig', [
            'quotation' => $quotation,
            'form' => $form->createView(),
            'customerForm' => $customerForm->createView(),
            'isB2bFeatureActive' => $this->get('prestashop.core.b2b.b2b_feature')->isActive(),
            'minPasswordLength' => Password::MIN_LENGTH,
            'displayInIframe' => $request->query->has('submitFormAjax'),
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ]);
    }

    public function ajaxCarts(Request $request)
    {
        // Permet de récupérer l'id customer de l'url en excluant les autres caractères
        $idCustomer = (int)preg_replace('/[^\d]/', '', $request->getPathInfo());
        $quotationRepository = $this->get('quotation_repository');
        $carts = $quotationRepository->findCartsByCustomer($idCustomer);

        $cart = $response = [];

        foreach ($carts as $key => $cart) {

            $response[$key]['id_cart'] = $cart['id_cart'];
            $response[$key]['date_cart'] = date("d/m/Y", strtotime($cart['date_add']));
            $response[$key]['id_customer'] = $idCustomer;
        }
        return new JsonResponse(json_encode($response), 200, [], true);
    }

    /**
     * Search customers
     * @param Request $request
     * @param $query
     * @return JsonResponse
     */
    public function searchCustomers(Request $request, $query)
    {
        $quotationRepository = $this->get('quotation_repository');
        $customer = $quotationRepository->findByQuery($query);

        return new JsonResponse(json_encode($customer), 200, [], true);
    }

    /**
     * Show customer by ID
     * @param Request $request
     * @param $query
     * @return JsonResponse
     */
    public function showCustomer(Request $request, $id_customer)
    {
        $quotationRepository = $this->get('quotation_repository');
        $customer = $quotationRepository->findOneCustomerById($id_customer);

        return new JsonResponse(json_encode($customer), 200, [], true);
    }

    public function ajaxCustomer(Request $request)
    {
        $customerRepository = $this->get('quotation_repository');
        $customers = $customerRepository->findAllCustomers();
        $response = [];

        foreach ($customers as $key => $customer) {
            $response[$key]['fullname'] = $customer['fullname'];
        }

        $file = 'data-customer.js';
        $fileSystem = new QuotationFileSystem();
        if (!is_file($file)) {
            $fileSystem->writeFile($file, $response);
        } else {
            $fileSystem->writeFile($file, $response);
        }
        return new JsonResponse(json_encode($response), 200, [], true);
    }
}
