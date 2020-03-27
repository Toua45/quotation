<?php

namespace Quotation\Controller;

use PrestaShop\PrestaShop\Core\Domain\Customer\Exception\CustomerNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Customer\Query\GetCustomerForViewing;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewableCustomer;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\Password;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Quotation\Entity\Quotation;
use Quotation\Form\QuotationCustomerType;
use Quotation\Service\QuotationFileSystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminQuotationController extends FrameworkBundleAdminController
{
    public function quotationIndex()
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotations = $quotationRepository->findAll();

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'quotations' => $quotations,
        ]);
    }

    public function add(Request $request)
    {
        $quotation = new Quotation();

        $formQuotationCustomer = $this->createForm(QuotationCustomerType::class, $quotation);
        $formQuotationCustomer->handleRequest($request);

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

//        if ($form->isSubmitted() && $form->isValid()) {
//            $quotation->setDateAdd(new \DateTime('now'));
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($quotation);
//            $entityManager->flush();
//
//            return $this->redirectToRoute('quotation_admin');
//        }

        return $this->render('@Modules/quotation/templates/admin/add_quotation.html.twig', [
            'quotation' => $quotation,
            'formQuotationCustomer' => $formQuotationCustomer->createView(),
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

        $response = [];

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

    /**
     * Show details customer by ID
     * @param Request $request
     * @param $query
     * @return JsonResponse
     */
    public function showCustomerDetails(Request $request, $id_customer)
    {
        $quotationRepository = $this->get('quotation_repository');
        $carts = $quotationRepository->findCartsByCustomer($id_customer);
        $orders = $quotationRepository->findOrdersByCustomer($id_customer);
        $quotations = $quotationRepository->findQuotationsByCustomer($id_customer);

        $response = [];

        foreach ($carts as $key => $cart) {
            $response[$key]['id_customer'] = $id_customer;
            $response[$key]['id_cart'] = $cart['id_cart'];
            $response[$key]['firstname'] = $cart['firstname'];
            $response[$key]['lastname'] = $cart['lastname'];
            $response[$key]['date_cart'] = date("d/m/Y", strtotime($cart['date_cart']));
            $response[$key]['total_cart'] = number_format($cart['total_cart'], 2);
            $response[$key]['total_product'] = number_format($cart['total_product'], 2);
            $response[$key]['id_product'] = $cart['id_product'];
            $response[$key]['product_name'] = $cart['name'];
            $response[$key]['product_price'] = number_format($cart['price'], 2);
            $response[$key]['product_quantity'] = $cart['quantity'];
        }

        foreach ($orders as $key => $order) {
            $response[$key]['id_customer'] = $id_customer;
            $response[$key]['id_order'] = $order['id_order'];
            $response[$key]['date_order'] = date("d/m/Y", strtotime($order['date_order']));
            $response[$key]['total_paid'] = number_format($order['total_paid'], 2);
            $response[$key]['payment'] = $order['payment'];
        }

        foreach ($quotations as $key => $quotation) {
            $response[$key]['id_customer'] = $id_customer;
            $response[$key]['id_quotation'] = $quotation['id_quotation'];
            $response[$key]['date_quotation'] = date("d/m/Y", strtotime($quotation['date_quotation']));
            $response[$key]['total_quotation'] = number_format($quotation['total_quotation'], 2);
        }

        return new JsonResponse(json_encode($response), 200, [], true);
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

//    /**
//     * Show cart by ID
//     * @param Request $request
//     * @param $query
//     * @return JsonResponse
//     */
//    public function showCart(Request $request, $id_cart)
//    {
//        $cartRepository = $this->get('quotation_repository');
//        $cart = $cartRepository->findOneCartById($id_cart);
////        dump($cart);die;
//
//        return new JsonResponse(json_encode($cart), 200, [], true);
//    }
}
