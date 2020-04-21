<?php

namespace Quotation\Controller;

use PrestaShop\PrestaShop\Adapter\Entity\Product;
use PrestaShop\PrestaShop\Core\Domain\Customer\Query\GetCustomerForViewing;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewableCustomer;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\Password;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Quotation\Entity\Quotation;
use Quotation\Form\QuotationCustomerType;
use Quotation\Form\QuotationProductType;
use Quotation\Form\QuotationSearchType;
use Quotation\Service\QuotationFileSystem;
use Quotation\Service\QuotationPdf;
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

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'quotations' => $quotations['records'],
            'page' => $page,
            'nbPages' => (int) ceil($quotations['nbRecords'] / Quotation::NB_MAX_QUOTATIONS_PER_PAGE),
            'nbRecords' => $quotations['nbRecords'],
            'quotationFilterForm' => $quotationFilterForm->createView()
        ]);
    }

    public function pdfView($id_quotation)
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotation = $quotationRepository->findQuotationById($id_quotation);

        $quotationPdf = new QuotationPdf();
        $filename = $quotation['firstname'] . ' ' . $filename = $quotation['lastname'] .  '  - Référence ' . $filename = $quotation['reference'];
        $html = $this->renderView('@Modules/quotation/templates/admin/pdf/pdf_quotation.html.twig', [
            'id_quotation' => $quotation['id_quotation'],
            'firstname' => $quotation['firstname'],
            'lastname' => $quotation['lastname'],
            'reference' => $quotation['reference']
        ]);

        $quotationPdf->createPDF($html, $filename);
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

        $formQuotationProduct = $this->createForm(QuotationProductType::class, $quotation);
        $formQuotationProduct->handleRequest($request);

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
            'formQuotationProduct' => $formQuotationProduct->createView(),
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

        if ($customer['id_customer']) {
            $customer['orders'] = $quotationRepository->findOrdersByCustomer($id_customer);
            $customer['nb_carts'] = $quotationRepository->findNbCartsByCustomer($id_customer);
            $customer['carts'] = $quotationRepository->findCartsByCustomer($id_customer);
            $customer['addresses'] = $quotationRepository->findAddressesByCustomer($id_customer);
        }

        for ($j = 0; $j < count($customer['orders']); $j++) {
            if ($customer['id_customer']) {
                $customer['id_customer'];
                if ($customer['orders']) {
                    $customer['orders'][$j]['id_order'];
                    $customer['orders'][$j]['nb_products'] = $quotationRepository->findProductsByOrder($customer['orders'][$j]['id_order']);
                }
            }
        }

        for ($k = 0; $k < count($customer['addresses']); $k++) {
            if ($customer['id_customer']) {
                $customer['id_customer'];
                if ($customer['addresses']) {
                    $customer['addresses'][$k]['id_address'];
                    if ($customer['addresses']) {
                        $customer['addresses'][$k]['further_address'];
                    } else {
                        $customer['addresses'][$k]['further_address'] = '';
                    }
                }
            }
        }

        return new JsonResponse(json_encode($customer), 200, [], true);
    }

    /**
     * Show details customer by ID
     * @param Request $request
     * @param $id_customer
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
                    $carts[$i]['id_cart'];
                    $carts[$i]['firstname'];
                    $carts[$i]['lastname'];
                    $carts[$i]['date_cart'] = date("d/m/Y", strtotime($carts[$i]['date_cart']));
                    $carts[$i]['total_cart'] = number_format($carts[$i]['total_cart'], 2);
                    if ($carts[$i]['products']) {
                        $carts[$i]['products'][$j]['id_product'];
                        $carts[$i]['products'][$j]['product_name'];
                        $carts[$i]['products'][$j]['product_price'] = number_format($carts[$i]['products'][$j]['product_price'], 2);
                        $carts[$i]['products'][$j]['product_quantity'];
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

        foreach ($orders as $key => $order) {
            $orders[$key]['id_customer'] = $id_customer;
            $orders[$key]['firstname'] = $order['firstname'];
            $orders[$key]['lastname'] = $order['lastname'];
            $orders[$key]['id_order'] = $order['id_order'];
            $orders[$key]['order_reference'] = $order['order_reference'];
            $orders[$key]['id_cart'] = $order['id_cart'];
            $orders[$key]['date_order'] = date("d/m/Y", strtotime($order['date_order']));
            $orders[$key]['total_products'] = number_format($order['total_products'], 2);
            $orders[$key]['total_shipping'] = number_format($order['total_shipping'], 2);
            $orders[$key]['total_paid'] = number_format($order['total_paid'], 2);
            $orders[$key]['payment'] = $order['payment'];
            $orders[$key]['order_status'] = $order['order_status'];
            $orders[$key]['address1'] = $order['address1'];
            $orders[$key]['address2'] = $order['address2'];
            $orders[$key]['postcode'] = $order['postcode'];
            $orders[$key]['city'] = $order['city'];
        }

        $response = [];

        foreach ($quotations as $key => $quotation) {
            $response[$key]['id_customer'] = $id_customer;
            $response[$key]['id_quotation'] = $quotation['id_quotation'];
            $response[$key]['quotation_reference'] = $quotation['quotation_reference'];
            $response[$key]['id_cart'] = $quotation['id_cart'];
            $response[$key]['date_quotation'] = date("d/m/Y", strtotime($quotation['date_quotation']));
            $response[$key]['total_quotation'] = number_format($quotation['total_quotation'], 2);
        }

        return new JsonResponse(json_encode([
            'carts' => $carts,
            'orders' => $orders,
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

    /**
     * Show cart by ID
     * @param Request $request
     * @param $idCart
     * @return JsonResponse
     */
    public function showCart(Request $request, $id_cart)
    {
        $quotationRepository = $this->get('quotation_repository');
        $cart = $quotationRepository->findOneCartById($id_cart);

        if ($cart['id_cart']) {
            $cart['products'] = $quotationRepository->findProductsCustomerByCarts($cart['id_cart']);
            $cart['order'] = $quotationRepository->findOrderByCart($cart['id_cart']);
            $cart['quotation'] = $quotationRepository->findQuotationByCart($cart['id_cart']);
        }

        for ($j = 0; $j < count($cart['products']); $j++) {
            if ($cart['id_cart']) {
                $cart['id_cart'];
                $cart['date_cart'] = date("d/m/Y", strtotime($cart['date_cart']));
                $cart['total_cart'] = number_format($cart['total_cart'], 2);
                if ($cart['products']) {
                    $cart['products'][$j]['id_product'];
                    $cart['products'][$j]['product_name'];
                    $cart['products'][$j]['product_price'] = number_format($cart['products'][$j]['product_price'], 2);
                    $cart['products'][$j]['product_quantity'];
                    $cart['products'][$j]['total_product'] = number_format($cart['products'][$j]['total_product'], 2);
                }
            }
        }

        return new JsonResponse(json_encode($cart), 200, [], true);
    }

    public function ajaxProduct(Request $request)
    {
        $customerRepository = $this->get('quotation_repository');
        $products = $customerRepository->findAllProducts();
        $response = [];

        foreach ($products as $key => $product) {
            $response[$key]['fullname'] = $product['fullname'];
        }

        $file = 'data-product.js';
        $fileSystem = new QuotationFileSystem();
        if (!is_file($file)) {
            $fileSystem->writeFile($file, $response);
        } else {
            $fileSystem->writeFile($file, $response);
        }
        return new JsonResponse(json_encode($response), 200, [], true);
    }

    /**
     * Search products
     * @param Request $request
     * @param $query
     * @return JsonResponse
     */
    public function searchProducts(Request $request, $query)
    {
        $quotationRepository = $this->get('quotation_repository');
        $product = $quotationRepository->findProductByQuery($query);

        return new JsonResponse(json_encode($product), 200, [], true);
    }

    /**
     * Show product by ID
     * @param Request $request
     * @param $id_product
     * @return JsonResponse
     */
    public function showProduct(Request $request, $id_product)
    {
        $quotationRepository = $this->get('quotation_repository');
        $product = $quotationRepository->findOneProductById($id_product);

        for ($i = 0; $i < count($product); $i++) {
            if ($product[$i]['id_product_attribute']) {
                $product[$i]['attributes'] = $quotationRepository->findAttributesByProduct($id_product, $product[$i]['id_product_attribute']);
            }
            if (is_null($product[$i]['id_product_attribute'])) {
                $product[$i]['quantity'] = $quotationRepository->findQuantityByProduct($id_product, $product[$i]['id_product_attribute'])['quantity'];
            }
        }

        for ($i = 0; $i < count($product); $i++) {
            $attributes = '';
            if (isset($product[$i]['attributes'])) {
                for ($j = 0; $j < count($product[$i]['attributes']); $j++) {
                    $attributes .= $product[$i]['attributes'][$j]['attribute_details'] . ' - ';
                    $attributesDetails = $attributes . $product[$i]['product_price'];
                }
                $product[$i]['attributes'] = rtrim($attributesDetails,  ' - ');
            }
        }

        return new JsonResponse(json_encode($product), 200, [], true);
    }

    /**
     * Show attributes product by ID
     * @param Request $request
     * @param $id_product
     * @return JsonResponse
     */
    public function showAttributesByProduct(Request $request, $id_product)
    {
        $quotationRepository = $this->get('quotation_repository');
        $product = $quotationRepository->findAttributesByProduct($id_product);

        return new JsonResponse(json_encode($product), 200, [], true);
    }

    public function createNewCart (Request $request)
    {
        dump($request->query->all());die;
    }
}
