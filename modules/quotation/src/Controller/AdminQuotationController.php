<?php

namespace Quotation\Controller;

use PrestaShop\PrestaShop\Core\Domain\Customer\Query\GetCustomerForViewing;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewableCustomer;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\Password;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Quotation\Entity\Quotation;
use Quotation\Form\QuotationCustomerType;
use Quotation\Form\QuotationSearchType;
use Quotation\Service\QuotationFileSystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminQuotationController extends FrameworkBundleAdminController
{
    /**
     * Fonction privée qui récupère toutes les données à partir du tableau 'quotation_search'
     */
    private function getReq(Request $req)
    {
        return $req->query->all()['quotation_search'];
    }

    public function quotationIndex(Request $req, int $page)
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotationFilterForm = $this->createForm(QuotationSearchType::class);
        $quotationFilterForm->handleRequest($req);

        if ($quotationFilterForm->isSubmitted() && $quotationFilterForm->isValid()) {
            $name = $this->getReq($req)['name'];
            $reference = $this->getReq($req)['reference'];
            $status = $this->getReq($req)['status'];
            $start = $this->getReq($req)['start'];
            $end = $this->getReq($req)['end'];

            $quotations = $quotationRepository->findQuotationsByFilters($page, $name, $reference, $status, $start, $end);
        } else {
            $quotations = $quotationRepository->findQuotationsByFilters($page);
        }

//        dump('page -> ' . $page);
//        dump($quotations);
//        dump('nbPages -> ' . (int) ceil($quotations['nbRecords'] / Quotation::NB_MAX_QUOTATIONS_PER_PAGE));
//        dump('nbRecords -> ' . $quotations['nbRecords']);
//        dump($quotations['records']);
//        die;

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'quotations' => $quotations['records'],
            'page' => $page,
            'nbPages' => (int) ceil($quotations['nbRecords'] / Quotation::NB_MAX_QUOTATIONS_PER_PAGE),
            'nbRecords' => $quotations['nbRecords'],
            'quotationFilterForm' => $quotationFilterForm->createView()
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

        for ($i = 0; $i < count($carts); $i++) {
            if ($carts[$i]['id_cart']) {
                $carts[$i]['products'] = $quotationRepository->findProductsCustomerByCarts($carts[$i]['id_cart']);
                $carts[$i]['orders'] = $quotationRepository->findOrdersByCustomer($id_customer, $carts[$i]['id_cart']);
                $carts[$i]['quotations'] = $quotationRepository->findQuotationsByCustomer($id_customer, $carts[$i]['id_cart']);
            }
        }

        $orders = $quotationRepository->findOrdersByCustomer($id_customer, null);
        $quotations = $quotationRepository->findQuotationsByCustomer($id_customer, null);

        /*
         * carts section
        */
        for ($i = 0; $i < count($carts); $i++) {
            for ($j = 0; $j < count($carts[$i]['products']); $j++) {
                if ($carts[$i]['id_cart']) {
                    $carts[$i]['id_cart'] = $carts[$i]['id_cart'];
                    $carts[$i]['firstname'] = $carts[$i]['firstname'];
                    $carts[$i]['lastname'] = $carts[$i]['lastname'];
                    $carts[$i]['date_cart'] = date("d/m/Y", strtotime($carts[$i]['date_cart']));
                    $carts[$i]['total_cart'] = number_format($carts[$i]['total_cart'], 2);
                    if ($carts[$i]['products']) {
                        $carts[$i]['products'][$j]['id_product'] = $carts[$i]['products'][$j]['id_product'];
                        $carts[$i]['products'][$j]['product_name'] = $carts[$i]['products'][$j]['product_name'];
                        $carts[$i]['products'][$j]['product_price'] = number_format($carts[$i]['products'][$j]['product_price'], 2);
                        $carts[$i]['products'][$j]['product_quantity'] = $carts[$i]['products'][$j]['product_quantity'];
                        $carts[$i]['products'][$j]['total_product'] = number_format($carts[$i]['products'][$j]['total_product'], 2);
                    }
                }
            }

            for ($k = 0; $k < count($carts[$i]['orders']); $k++) {
                if ($carts[$i]['orders']) {
                    $carts[$i]['orders'][$k]['total_products'] = number_format($carts[$i]['orders'][$k]['total_products'], 2);
                    $carts[$i]['orders'][$k]['total_shipping'] = number_format($carts[$i]['orders'][$k]['total_shipping'], 2);
                    $carts[$i]['orders'][$k]['total_paid'] = number_format($carts[$i]['orders'][$k]['total_paid'], 2);
                }
            }

            for ($l = 0; $l < count($carts[$i]['quotations']); $l++) {
                if ($carts[$i]['quotations']) {
                    $carts[$i]['quotations'][$l]['price'] = number_format($carts[$i]['quotations'][$l]['price'], 2);
                    $carts[$i]['quotations'][$l]['total_quotation'] = number_format($carts[$i]['quotations'][$l]['total_quotation'], 2);
                }
            }
        }

        $response = [];

        foreach ($orders as $key => $order) {
            $response[$key]['id_customer'] = $id_customer;
            $response[$key]['firstname'] = $order['firstname'];
            $response[$key]['lastname'] = $order['lastname'];
            $response[$key]['id_order'] = $order['id_order'];
            $response[$key]['order_reference'] = $order['order_reference'];
            $response[$key]['id_cart'] = $order['id_cart'];
            $response[$key]['date_order'] = date("d/m/Y", strtotime($order['date_order']));
            $response[$key]['total_products'] = number_format($order['total_products'], 2);
            $response[$key]['total_shipping'] = number_format($order['total_shipping'], 2);
            $response[$key]['total_paid'] = number_format($order['total_paid'], 2);
            $response[$key]['payment'] = $order['payment'];
            $response[$key]['order_status'] = $order['order_status'];
            $response[$key]['address1'] = $order['address1'];
            $response[$key]['address2'] = $order['address2'];
            $response[$key]['postcode'] = $order['postcode'];
            $response[$key]['city'] = $order['city'];
        }

        foreach ($quotations as $key => $quotation) {
            $response[$key]['id_customer'] = $id_customer;
            $response[$key]['id_quotation'] = $quotation['id_quotation'];
            $response[$key]['quotation_reference'] = $quotation['quotation_reference'];
            $response[$key]['id_cart_product'] = $quotation['id_cart_product'];
            $response[$key]['date_quotation'] = date("d/m/Y", strtotime($quotation['date_quotation']));
            $response[$key]['total_quotation'] = number_format($quotation['total_quotation'], 2);
        }

        return new JsonResponse(json_encode([
            'carts' => $carts,
            'response' => $response,
        ]), 200, [], true);
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
