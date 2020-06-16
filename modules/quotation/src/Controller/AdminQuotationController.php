<?php

namespace Quotation\Controller;

use Dompdf\Dompdf;
use PrestaShop\PrestaShop\Core\Domain\Customer\Query\GetCustomerForViewing;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewableCustomer;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\Password;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Quotation\Entity\Quotation;
use Quotation\Form\QuotationCustomerType;
use Quotation\Form\QuotationDiscountType;
use Quotation\Form\QuotationProductType;
use Quotation\Form\QuotationSearchType;
use Quotation\Service\QuotationFileSystem;
use Quotation\Service\QuotationPdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class AdminQuotationController extends FrameworkBundleAdminController
{
    const DEFAULT_PERCENTAGE_REDUCTION_AMOUNT = 20;

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

        $currentPage = $page;

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'quotations' => $quotations['records'],
            'page' => $page,
            'currentPage' => $currentPage,
            'maxPages' => (int)ceil($quotations['nbRecords'] / Quotation::NB_MAX_QUOTATIONS_PER_PAGE),
            'nbRecords' => $quotations['nbRecords'],
            'quotationFilterForm' => $quotationFilterForm->createView()
        ]);
    }

    /**
     * @param $id_quotation
     * Fonction qui fait appelle au service "QuotationPdf" pour créer un nouveau document et renvoyer les informations du devis de chaque client
     */
    public function quotationPdf($id_quotation)
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotation = $quotationRepository->findQuotationById($id_quotation);

        // Récupération des méthodes "findAddressesByCustomer", et "findAddressesByCustomer" pour les adresses des clients et les produits associés au devis
        if ($quotation['id_quotation']) {
            $quotation['addresses'] = $quotationRepository->findAddressesByCustomer($quotation['id_customer']);
            $quotation['products'] = $quotationRepository->findProductsCustomerByCarts($quotation['id_cart']);
        }
        // Calcul total par produit avec les réductions associées (Montant HT)
        $total_product_price = 0;
        for ($i = 0; $i < count($quotation['products']); $i++) {
            $total_product_price += $quotation['products'][$i]['total_product'] - $quotation['products'][$i]['reduction'];
        }

        // Calcul de la réduction si celle-ci est affichée en pourcentage
        $reduction_percent_calculation = $quotation['reduction_percent'] * $total_product_price / 100;

        // Calcul de la TVA sur les frais de réduction
        $tva_reduction_amount = 0;
        if ($quotation['reduction_tax'] == false) {
            $tva_reduction_amount = $quotation['reduction_amount'] * self::DEFAULT_PERCENTAGE_REDUCTION_AMOUNT / 100;
        }

        // Calcul de la TVA à partir de chaque produit
        $price_tva = 0;
        for ($i = 0; $i < count($quotation['products']); $i++) {
            $price_tva += ($quotation['products'][$i]['total_product'] - $quotation['products'][$i]['reduction']) * $quotation['products'][$i]['rate'] / 100;
        }

        // Premier résultat avant addition de la TVA
        $total_product_with_reduction = $total_product_price;
        if ($quotation['reduction_amount'] != 0.00) {
            $total_product_with_reduction = $total_product_price - $quotation['reduction_amount'];
        } elseif ($quotation['reduction_percent'] != 0.00) {
            $total_product_with_reduction = $total_product_price - $reduction_percent_calculation;
        }

        // Calcul total de la TVA
        $price_tva -= $tva_reduction_amount;

        // Calcul total TTC
        $total_ttc = $total_product_with_reduction + $price_tva;


        $quotationPdf = new QuotationPdf();

        // Nom du fichier pdf qui comprend le nom et prénom du client et le numéro de devis
        $filename = $quotation['firstname'] . ' ' . $filename = $quotation['lastname'] . '  - Référence n° ' . $filename = $quotation['reference'];

        $html = $this->renderView('@Modules/quotation/templates/admin/pdf/pdf_quotation.html.twig', [
            'quotation' => $quotation,
            'total_product_price' => $total_product_price,
            'reduction_percent_calculation' => $reduction_percent_calculation,
            'tva_reduction_amount' => $tva_reduction_amount,
            'total_product_with_reduction' => $total_product_with_reduction,
            'price_tva' => $price_tva,
            'total_ttc' => $total_ttc
        ]);

        $quotationPdf->createPDF($html, $filename);
    }


    /**
     * @param $id_quotation
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function mailerAction($id_quotation)
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotation = $quotationRepository->findQuotationById($id_quotation);

        // Permet de générer notre email au format HTML
        $renderer = new Environment();

        // Rendu PDF
        $pdf_file = new Dompdf();
        $pdf_file->loadHtml('@Modules/quotation/templates/admin/pdf/pdf_quotation.html.twig');
        // Conversion du HTML en PDF
        $pdf_file->render();

        // Affichage du contenu PDF
        $pdf_content = $pdf_file->output();

        // Paramétrage de SmtpTransport pour l'envoi d'un email
        $transport = (new \Swift_SmtpTransport('smtp.mailtrap.io', 2525))
            ->setUsername('24289db038041d')
            ->setPassword('382974f58fd7f9');

        $mailer = new \Swift_Mailer($transport);

        // Création d'un message
        $message = (new \Swift_Message())
            ->setSubject('Aquapure France - extrait devis n° ' . $quotation['reference'] . ' en date du ' . strftime("%A %d %B %G", strtotime($quotation['date_add'])))
            ->setFrom('mailtestphp45@gmail.com')
            ->setTo($quotation['email'])
            // Contenu de la page à charger pour l'email
            ->setBody(
                $renderer = $this->renderView(
                    '@Modules/quotation/templates/admin/email/email.html.twig', [
                    // Informations sur l'utilisateur
                    'quotation' => $quotation
                ]),
                // Définition du format à rendre
                'text/html'
            )
            ->attach(\Swift_Attachment::newInstance($pdf_content, 'test.pdf', 'application/pdf'));

        // Envoi de l'email qui prend en paramètre le message
        $mailer->send($message);

        $this->addFlash('success', 'Votre message a été envoyé avec succès.');

        return $this->redirectToRoute('quotation_admin');
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

    public function add(Request $request)
    {
        $quotation = new Quotation();

        $formQuotationCustomer = $this->createForm(QuotationCustomerType::class, $quotation);
        $formQuotationCustomer->handleRequest($request);

        if (!$this->get('prestashop.adapter.shop.context')->isSingleShopContext()) {
            return $this->redirectToRoute('quotation_admin_add');
        }

        // Permet d'appeler la méthode addGroupSelectionToRequest du CustomerController
        $this->redirect('@PrestaShop/Admin/Sell/Customer/CustomerController/addGroupSelectionToRequest');

        $customerForm = $this->get('prestashop.core.form.identifiable_object.builder.customer_form_builder')->getForm();
        $customerForm->handleRequest($request);

        $customerFormHandler = $this->get('prestashop.core.form.identifiable_object.handler.customer_form_handler');

        try {
            $result = $customerFormHandler->handle($customerForm);

            if ($customerId = $result->getIdentifiableObjectId()) {
                $this->addFlash('success', $this->trans('Successful creation.', 'Admin.Notifications.Success'));

                if ($request->query->has('submitFormAjax')) {
                    /** @var ViewableCustomer $customerInformation */
                    $customerInformation = $this->getQueryBus()->handle(new GetCustomerForViewing((int)$customerId));

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

        $formQuotationDiscount = $this->createForm(QuotationDiscountType::class, $quotation);
        $formQuotationDiscount->handleRequest($request);

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
            'formQuotationDiscount' => $formQuotationDiscount->createView(),
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
    public function showCustomerDetails(Request $request, $id_customer, SessionInterface $session)
    {
        $quotationRepository = $this->get('quotation_repository');
        $customer = $quotationRepository->getCustomerInfoById($id_customer);
        $carts = $quotationRepository->findCartsByCustomer($id_customer);

        // On boucle sur les carts
        for ($i = 0; $i < count($carts); $i++) {
            if ($carts[$i]['id_cart']) {
                // En fonction des carts qui ont été récupérés, on récupère les produits liés à ce cart avec la méthode findProductsCustomerByCarts
                $carts[$i]['products'] = $quotationRepository->findProductsCustomerByCarts($carts[$i]['id_cart']);
                // En fonction des carts qui ont été récupérés, on récupère les commandes liés à ce cart avec la méthode findOrdersByCustomer
                $carts[$i]['orders'] = $quotationRepository->findOrdersByCustomer($id_customer, $carts[$i]['id_cart']);
                // En fonction des carts qui ont été récupérés, on récupère les quotations liés à ce cart avec la méthode findQuotationsByCustomer
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

        $addresses = $quotationRepository->findAddressesByCustomer($id_customer);

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
            'customer' => $customer,
            'carts' => $carts,
            'orders' => $orders,
            'response' => $response,
            'id_last_cart' => $idLastCart = $quotationRepository->findLastCartByCustomerId()['id_cart'] + 1,  // Permet de récupérer le dernier cart d'un customer que l'on récupère ensuite en js via le json
            'addresses' => $addresses,
        ]), 200, [], true);
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
                    // On récupère les images liées aux produits
                    $cart['products'][$j]['attributes'] = $quotationRepository->findAttributesByProduct($cart['products'][$j]['id_product'],
                        $cart['products'][$j]['id_product_attribute']);
                    $cart['products'][$j]['id_image'] = $quotationRepository->findPicturesByAttributesProduct($cart['products'][$j]['id_product'],
                        $cart['products'][$j]['id_product_attribute'])['id_image'];
                    if ($cart['products'][$j]['id_image'] == '0' || $cart['products'][$j]['id_product_attribute'] == '0') {
                        $cart['products'][$j]['id_image'] = $quotationRepository->findPicturesByProduct($cart['products'][$j]['id_product'])['id_image'];
                    }

                    // Pour créer le path, on va séparer l'id_image s'il dispose d'un nombre à 2 chiffres sinon on récupère l'id_image
                    $cart['products'][$j]['path'] = $cart['products'][$j]['id_image'];
                    if ($cart['products'][$j]['path']) {
                        $cart['products'][$j]['path'] = str_split($cart['products'][$j]['path']);
                    }
                }
            }
        }

        for ($k = 0; $k < count($cart['products']); $k++) {
            $attributes = '';
            if (isset($cart['products'][$k]['attributes'])) {
                for ($l = 0; $l < count($cart['products'][$k]['attributes']); $l++) {
                    $attributes .= $cart['products'][$k]['attributes'][$l]['attribute_details'] . ' - ';
                }
                $cart['products'][$k]['attributes'] = rtrim($attributes, ' - ');
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
        $productWithoutAttributes = [];

        for ($i = 0; $i < count($product); $i++) {
            $product[$i]['tax_amount'] = $product[$i]['product_price'] * $product[$i]['rate'] / 100;
            $product[$i]['price_product_ttc'] = $product[$i]['product_price'] + $product[$i]['tax_amount'];
            if ($product[$i]['id_product_attribute']) {
                $product[$i]['attributes'] = $quotationRepository->findAttributesByProduct($id_product, $product[$i]['id_product_attribute']);
            }
        }

        if (is_null($product[0]['id_product_attribute'])) {
            $productWithoutAttributes['id_product'] = $product[0]['id_product'];
            $productWithoutAttributes['product_name'] = $product[0]['product_name'];
            $productWithoutAttributes['product_price'] = $product[0]['product_price'];
            $productWithoutAttributes['id_product_attribute'] = '0';
            $productWithoutAttributes['quantity'] = $quotationRepository->findQuantityByProduct($id_product, $product[0]['id_product_attribute'])['quantity'];
            $product = $productWithoutAttributes;
        }

        for ($i = 0; $i < count($product); $i++) {
            $attributes = '';
            if (isset($product[$i]['attributes'])) {
                for ($j = 0; $j < count($product[$i]['attributes']); $j++) {
                    $attributes .= $product[$i]['attributes'][$j]['attribute_details'] . ' - ';
                    $attributesDetails = $attributes . $product[$i]['product_price'] . ' €';
                }
                $product[$i]['attributes'] = rtrim($attributesDetails, ' - ');
            }
        }

        return new JsonResponse(json_encode($product), 200, [], true);
    }

    /**
     * Duplicate cart
     * @param $id_customer
     * @param $id_cart
     * @param $new_id_cart
     * @return JsonResponse
     * @throws \Exception
     */
    public function duplicateCart($id_customer, $id_cart, $new_id_cart, SessionInterface $session)
    {
        $quotationRepository = $this->get('quotation_repository');
        $customer = $quotationRepository->getCustomerInfoById($id_customer);

        // On récupère les adresses du client
        if ($customer['id_customer']) {
            $customer['addresses'] = $quotationRepository->findAddressesByCustomer($id_customer);
            // Si le client ne dispose pas d'adresse, on affecte l'id_adress à 0
            if ($customer['addresses'] === []) {
                $customerIdAddress['id_address'] = $customer['addresses'] === [] ? '0' : $customer['addresses'];
                array_push($customer['addresses'], $customerIdAddress);
            }
        }

        $idShopGroup = $this->getContext()->shop->id_shop_group;
        $idShop = $this->getContextShopId();
        $idLang = $this->getContext()->language->id;
        $idAddressDelivery = $customer['addresses'][0]['id_address'];
        $idAddressInvoice = $customer['addresses'][0]['id_address'];
        $idCurrency = $this->getContext()->currency->id;
        $idGuest = $id_customer;
        $secureKey = $customer['secure_key'];
        $dateAdd = date_format(new \DateTime('now'), 'Y-m-d H:i:s');
        $dateUpd = date_format(new \DateTime('now'), 'Y-m-d H:i:s');
        $id_customization = 0;

        // create cart
        $newCart = $quotationRepository->addNewCart(
            $idShopGroup,
            $idShop,
            $idLang,
            $idAddressDelivery,
            $idAddressInvoice,
            $idCurrency,
            $id_customer,
            $idGuest,
            $secureKey,
            $dateAdd,
            $dateUpd,
            1,
            '',
            0,
            0,
            0,
            0
        );

        $cart = $quotationRepository->findOneCartById($id_cart);

        if ($cart['id_cart']) {
            $cart['products'] = $quotationRepository->findProductsCustomerByCarts($cart['id_cart']);
        }

        $session->set('cart',
            [
                'id_cart' => $new_id_cart,
                'id_customer' => $id_customer,
                'products' => $quotationRepository->findProductsCustomerByCarts($cart['id_cart'])
            ]
        );

        for ($i = 0; $i < count($session->get('cart')['products']); $i++) {
            $quotationRepository->insertProductsToCart(
                $session->get('cart')['id_cart'],
                $session->get('cart')['products'][$i]['id_product'],
                $idAddressDelivery,
                $idShop,
                $session->get('cart')['products'][$i]['id_product_attribute'],
                $id_customization,
                $session->get('cart')['products'][$i]['product_quantity'],
                $dateAdd
            );
        }

        return new JsonResponse(json_encode([
            'customer' => $customer,
            'cart' => $cart,
            'session' => $session->get('cart')
        ]), 200, [], true);

    }

    /**
     * Create new cart
     * @param $id_product
     * @param $id_product_attribute
     * @param $quantity
     * @param $id_customer
     * @return JsonResponse
     * @throws \Exception
     */
    public function createNewCart($id_product, $id_product_attribute, $quantity, $id_customer, $id_cart, SessionInterface $session)
    {
        $quotationRepository = $this->get('quotation_repository');
        $customer = $quotationRepository->getCustomerInfoById($id_customer);

        // On récupère les adresses du client
        if ($customer['id_customer']) {
            $customer['addresses'] = $quotationRepository->findAddressesByCustomer($id_customer);
            // Si le client ne dispose pas d'adresse, on affecte l'id_adress à 0
            if ($customer['addresses'] === []) {
                $customerIdAddress['id_address'] = $customer['addresses'] === [] ? '0' : $customer['addresses'];
                array_push($customer['addresses'], $customerIdAddress);
            }
        }

        $idShopGroup = $this->getContext()->shop->id_shop_group;
        $idShop = $this->getContextShopId();
        $idLang = $this->getContext()->language->id;
        $idAddressDelivery = $customer['addresses'][0]['id_address'];
        $idAddressInvoice = $customer['addresses'][0]['id_address'];
        $idCurrency = $this->getContext()->currency->id;
        $idGuest = $id_customer;
        $secureKey = $customer['secure_key'];
        $dateAdd = date_format(new \DateTime('now'), 'Y-m-d H:i:s');
        $dateUpd = date_format(new \DateTime('now'), 'Y-m-d H:i:s');
        $id_customization = 0;

        // On utilise la session pour stocker les éléments
        if ($session->get('cart')['id_customer'] === $id_customer) {
            $newProduct = [
                'id_product' => $id_product,
                'id_product_attribute' => $id_product_attribute,
                'quantity' => $quantity
            ];

            $session->set('cart',
                [
                    'id_cart' => $session->get('cart')['id_cart'],
                    'id_customer' => $session->get('cart')['id_customer'],
                    'product' => $newProduct
                ]
            );
        } else {
            // create cart
            $cart = $quotationRepository->addNewCart(
                $idShopGroup,
                $idShop,
                $idLang,
                $idAddressDelivery,
                $idAddressInvoice,
                $idCurrency,
                $id_customer,
                $idGuest,
                $secureKey,
                $dateAdd,
                $dateUpd,
                1,
                '',
                0,
                0,
                0,
                0
            );

            $session->set('cart',
                [
                    'id_cart' => $id_cart,
                    'id_customer' => $id_customer,
                    'product' => [
                        'id_product' => $id_product,
                        'id_product_attribute' => $id_product_attribute,
                        'quantity' => $quantity
                    ]
                ]
            );
        }

        // On récupère le dernier cart du client
        $products = $quotationRepository->findProductsCustomerByCarts($session->get('cart')['id_cart']);
        // On va crée un tableau pour récupérer tous les id_product
        $productsID = array_map(function ($product) {
            return $product['id_product'];
        }, $products);
        //On vérifie si l'id_product existe dans le tableau, s'il n'existe pas, on insert les données en base de données
        if (!in_array($session->get('cart')['product']['id_product'], $productsID)) {
            $quotationRepository->insertProductsToCart(
                $session->get('cart')['id_cart'],
                $session->get('cart')['product']['id_product'],
                $idAddressDelivery,
                $idShop,
                $session->get('cart')['product']['id_product_attribute'],
                $id_customization,
                $session->get('cart')['product']['quantity'],
                $dateAdd
            );
        }

        return new JsonResponse(json_encode($session->get('cart')), 200, [], true);
    }

    /**
     * Update product quantity on cart
     * @param $id_cart
     * @param $id_product
     * @param $id_product_attribute
     * @param $quantity
     * @return JsonResponse
     * @throws \Exception
     */
    public function updateQuantityProductCart($id_cart, $id_product, $id_product_attribute, $quantity)
    {
        $quotationRepository = $this->get('quotation_repository');
        $productQty = $quotationRepository->updateQuantityProductOnCart($id_cart, $id_product, $id_product_attribute, $quantity);
        $cart = $quotationRepository->findOneCartById($id_cart);

        if ($cart['id_cart']) {
            $cart['products'] = $quotationRepository->findProductsCustomerByCarts($cart['id_cart']);
        }

        for ($j = 0; $j < count($cart['products']); $j++) {
            if ($cart['id_cart']) {
                $cart['total_cart'] = number_format($cart['total_cart'], 2);
                if ($cart['products']) {
                    $cart['products'][$j]['total_product'] = number_format($cart['products'][$j]['total_product'], 2);
                }
            }
        }

        return new JsonResponse(json_encode($cart), 200, [], true);
    }

    /**
     * Delete product on cart
     * @param $id_cart
     * @param $id_product
     * @param $id_product_attribute
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteProductCart($id_cart, $id_product, $id_product_attribute)
    {
        $quotationRepository = $this->get('quotation_repository');
        $product = $quotationRepository->deleteProductOnCart($id_cart, $id_product, $id_product_attribute);
        $cart = $quotationRepository->findOneCartById($id_cart);

        if ($cart['id_cart']) {
            $cart['total_cart'] = number_format($cart['total_cart'], 2);
        }

        return new JsonResponse(json_encode($cart), 200, [], true);
    }

    /**
     * Autocompletion on discounts
     */
    public function ajaxDiscount(Request $request)
    {
        $QuotationRepository = $this->get('quotation_repository');
        $discounts = $QuotationRepository->findAllDiscounts();
        $response = [];

        foreach ($discounts as $key => $discount) {
            $response[$key]['fullname'] = $discount['fullname'];
        }

        $file = 'data-discount.js';
        $fileSystem = new QuotationFileSystem();
        if (!is_file($file)) {
            $fileSystem->writeFile($file, $response);
        } else {
            $fileSystem->writeFile($file, $response);
        }
        return new JsonResponse(json_encode($response), 200, [], true);
    }

    /**
     * Search discounts
     * @param Request $request
     * @param $query
     * @return JsonResponse
     */
    public function searchDiscounts(Request $request, $query)
    {
        $quotationRepository = $this->get('quotation_repository');
        $discount = $quotationRepository->findDiscountByQuery($query);

        return new JsonResponse(json_encode($discount), 200, [], true);
    }

    /**
     * Show discount by ID
     * @param Request $request
     * @param $id_cart_rule
     * @return JsonResponse
     */
    public function showDiscount(Request $request, $id_cart_rule)
    {
        $quotationRepository = $this->get('quotation_repository');
        $discount = $quotationRepository->findOneDiscountById($id_cart_rule);

        $discount['reduction_percent'] = $discount['reduction_percent'] . ' %';
        $discount['reduction_amount'] = $discount['reduction_amount'] . ' €';

        return new JsonResponse(json_encode($discount), 200, [], true);
    }

    /**
     * Assign cart_rule to cart
     * @param $id_cart
     * @param $id_cart_rule
     * @return JsonResponse
     */
    public function insertCartRule($id_cart, $id_cart_rule)
    {
        $quotationRepository = $this->get('quotation_repository');
        $cart = $quotationRepository->assignCartRuleToCart($id_cart, $id_cart_rule);

        return new JsonResponse(json_encode('It works !'));
    }

    /**
     * Show Cart discounts
     * @param Request $request
     * @param $id_cart
     * @return JsonResponse
     */
    public function showCartDiscounts(Request $request, $id_cart)
    {
        $quotationRepository = $this->get('quotation_repository');
        $cart = $quotationRepository->findOneCartById($id_cart);

        if ($cart['id_cart']) {
            $cart['discounts'] = $quotationRepository->findDiscountsByIdCart($cart['id_cart']);
        }

        for ($j = 0; $j < count($cart['discounts']); $j++) {
            if ($cart['discounts']) {
                $cart['discounts'][$j]['reduction_percent'] = $cart['discounts'][$j]['reduction_percent'] . ' %';
                $cart['discounts'][$j]['reduction_amount'] = $cart['discounts'][$j]['reduction_amount'] . ' €';
            }
        }

        return new JsonResponse(json_encode($cart), 200, [], true);
    }
}
