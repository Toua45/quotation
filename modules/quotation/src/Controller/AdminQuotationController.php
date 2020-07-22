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
use Quotation\Form\QuotationShowStatusType;
use Quotation\Form\QuotationStatusType;
use Quotation\Service\QuotationFileSystem;
use Quotation\Service\QuotationPdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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

        // on récupère la fonction getAdminLink qui permet de passer une commande à partir d'un panier
        for ($i = 0; $i < count($quotations['records']); $i++) {
            $quotations['records'][$i]['url_to_order'] = $this->getAdminLink('AdminOrders', ['id_cart' => $quotations['records'][$i]['id_cart'], 'addorder' => 1]);
            // on regarde si le panier est associé à une commande
            $quotations['records'][$i]['orders'] = $quotationRepository->findOrdersByCustomer($quotations['records'][$i]['id_customer'], $quotations['records'][$i]['id_cart']);
            $quotations['records'][$i]['url_to_show_order'] = '';
            // on récupère la fonction getAdminLink qui permet de voir une commande à partir d'un panier
            if ($quotations['records'][$i]['orders']) {
                $quotations['records'][$i]['url_to_show_order'] = $this->getAdminLink('AdminOrders', ['id_order' => $quotations['records'][$i]['orders'][0]['id_order'], 'vieworder' => 1]);
            }
            // On récupère le cart lié au quotation
            $quotations['records'][$i]['cart'] = $quotationRepository->findOneCartById($quotations['records'][$i]['id_cart']);
            if ($quotations['records'][$i]['cart']['id_cart']) {
                $quotations['records'][$i]['cart']['products'] = $quotationRepository->findProductsCustomerByCarts($quotations['records'][$i]['cart']['id_cart']);
                $quotations['records'][$i]['cart']['discounts'] = $quotationRepository->findDiscountsByIdCart($quotations['records'][$i]['cart']['id_cart']);
            }
            // On récupère les produits du cart
            for ($j = 0; $j < count($quotations['records'][$i]['cart']['products']); $j++) {
                if ($quotations['records'][$i]['cart']['products']) {
                    $quotations['records'][$i]['cart']['products'][$j]['total_product'] = number_format($quotations['records'][$i]['cart']['products'][$j]['total_product'], 2);
                    $quotations['records'][$i]['cart']['products'][$j]['tva_amount_product'] = number_format(($quotations['records'][$i]['cart']['products'][$j]['product_price'] * $quotations['records'][$i]['cart']['products'][$j]['rate']) / 100, 2);
                    $quotations['records'][$i]['cart']['products'][$j]['total_tva_amount_product'] = number_format((($quotations['records'][$i]['cart']['products'][$j]['product_price'] * $quotations['records'][$i]['cart']['products'][$j]['rate']) / 100) * $quotations['records'][$i]['cart']['products'][$j]['product_quantity'], 2);

                }
            }
            // Partie Discount
            $quotations['records'][$i]['cart']['total_discounts'] = 0;
            for ($k = 0; $k < count($quotations['records'][$i]['cart']['discounts']); $k++) {
                if ($quotations['records'][$i]['cart']['discounts'][$k]['reduction_product']) {
                    $quotations['records'][$i]['cart']['discounts'][$k]['reduction_product'] = $quotationRepository->findProductAssignToDiscount($quotations['records'][$i]['cart']['discounts'][$k]['reduction_product']);
                    if ($quotations['records'][$i]['cart']['discounts'][$k]['reduction_percent'] !== '0.00') {
                        $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount'] =
                            $quotations['records'][$i]['cart']['discounts'][$k]['reduction_product']['product_price'] * $quotations['records'][$i]['cart']['discounts'][$k]['reduction_percent'] / 100;
                        $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount'] = strval($quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount']);
                    }
                } else if ($quotations['records'][$i]['cart']['discounts'][$k]['reduction_percent'] !== '0.00') {
                    $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount'] = $quotations['records'][$i]['cart']['total_cart'] * $quotations['records'][$i]['cart']['discounts'][$k]['reduction_percent'] / 100;
                    $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount'] = strval($quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount']);
                }
                $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_ht'] = $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount'];

                $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_tax'] = '0';
                if ($quotations['records'][$i]['cart']['discounts'][$k]['reduction_tax'] !== null) {
                    $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_tax'] = ($quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount'] * 20) / 100;
                    $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_tax'] = number_format($quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_tax'], 2);
                    $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_tax'] = strval($quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_tax']);
                    if ($quotations['records'][$i]['cart']['discounts'][$k]['reduction_tax'] == '1') {
                        $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_ht'] = $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount'] - $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_tax'];
                        $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_ht'] = number_format($quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_ht'], 2);
                        $quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_ht'] = strval($quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_ht']);
                    }
                }
                $quotations['records'][$i]['cart']['total_discounts'] += number_format($quotations['records'][$i]['cart']['discounts'][$k]['reduction_amount_ht'], 2);
            }
            $quotations['records'][$i]['cart']['total_discounts'] = strval(($quotations['records'][$i]['cart']['total_discounts']));

            // On calcule le montant total de la tva des réductions HT
            $quotations['records'][$i]['cart']['total_discounts_tax'] = '0';
            for ($l = 0; $l < count($quotations['records'][$i]['cart']['discounts']); $l++) {
                $quotations['records'][$i]['cart']['total_discounts_tax'] += $quotations['records'][$i]['cart']['discounts'][$l]['reduction_amount_tax'];
            }
            $quotations['records'][$i]['cart']['total_discounts_tax'] = number_format($quotations['records'][$i]['cart']['total_discounts_tax'], 2);
            $quotations['records'][$i]['cart']['total_discounts_tax'] = strval($quotations['records'][$i]['cart']['total_discounts_tax']);

            // On calcule le montant total de la tva des produits HT
            $quotations['records'][$i]['cart']['total_product_taxes'] = '0';
            for ($m = 0; $m < count($quotations['records'][$i]['cart']['products']); $m++) {
                $quotations['records'][$i]['cart']['total_product_taxes'] += $quotations['records'][$i]['cart']['products'][$m]['total_tva_amount_product'];
            }
            $quotations['records'][$i]['cart']['total_product_taxes'] = number_format($quotations['records'][$i]['cart']['total_product_taxes'], 2);
            $quotations['records'][$i]['cart']['total_product_taxes'] = strval($quotations['records'][$i]['cart']['total_product_taxes']);

            // On calcule le montant HT après réductions
            $quotations['records'][$i]['cart']['total_ht_with_discount'] = '0';
            $quotations['records'][$i]['cart']['total_ht_with_discount'] = $quotations['records'][$i]['cart']['total_cart'] - $quotations['records'][$i]['cart']['total_discounts'];
            $quotations['records'][$i]['cart']['total_ht_with_discount'] = strval($quotations['records'][$i]['cart']['total_ht_with_discount']);

            // On calucule le montant total de la tva
            $quotations['records'][$i]['cart']['total_taxes'] = '0';
            $quotations['records'][$i]['cart']['total_taxes'] = $quotations['records'][$i]['cart']['total_product_taxes'] - $quotations['records'][$i]['cart']['total_discounts_tax'];
            $quotations['records'][$i]['cart']['total_taxes'] = strval($quotations['records'][$i]['cart']['total_taxes']);

            // On calule le montant total ttc du panier$quotations['records'][$i]['cart']
            $quotations['records'][$i]['cart']['total_ttc'] = number_format(($quotations['records'][$i]['cart']['total_cart'] - $quotations['records'][$i]['cart']['total_discounts']) + $quotations['records'][$i]['cart']['total_taxes'], 2);
        }

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'quotations' => $quotations['records'],
            'page' => $page,
            'currentPage' => $currentPage,
            'maxPages' => (int)ceil($quotations['nbRecords'] / Quotation::NB_MAX_QUOTATIONS_PER_PAGE),
            'nbRecords' => $quotations['nbRecords'],
            'quotationFilterForm' => $quotationFilterForm->createView(),
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

        // On récupère les adresses du client
        if ($quotation['id_customer']) {
            $quotation['addresses'] = $quotationRepository->findAddressesByCustomer($quotation['id_customer']);
            // Si le client ne dispose pas d'adresse, on affecte l'id_adress à 0
            if ($quotation['addresses'] === []) {
                $customerIdAddress['id_address'] = $quotation['addresses'] === [] ? '0' : $quotation['addresses'];
                array_push($quotation['addresses'], $customerIdAddress);
            }
        }

        $cart = $quotationRepository->findOneCartById($quotation['id_cart']);

        if ($cart['id_cart']) {
            // On récupère l'adresse du magasin
            $cart['addressStore'] = $quotationRepository->findAddressStore ($cart['id_cart']);
            $cart['products'] = $quotationRepository->findProductsCustomerByCarts($cart['id_cart']);
            $cart['discounts'] = $quotationRepository->findDiscountsByIdCart($cart['id_cart']);
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
                    $cart['products'][$j]['tva_amount_product'] = number_format(($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100, 2);
                    $cart['products'][$j]['total_tva_amount_product'] = number_format((($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100) * $cart['products'][$j]['product_quantity'], 2);
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
                        if (count($cart['products'][$j]['path']) !== 1) {
                            $cart['products'][$j]['url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/img/p/' . $cart['products'][$j]['path'][0] . '/' . $cart['products'][$j]['path'][1] . '/' . $cart['products'][$j]['id_image'] . '-cart_default.jpg';
                        } else {
                            $cart['products'][$j]['url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/img/p/' . $cart['products'][$j]['path'][0] . '/' . $cart['products'][$j]['id_image'] . '-cart_default.jpg';
                        }

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

        // Partie Discount
        $cart['total_discounts'] = 0;
        for ($m = 0; $m < count($cart['discounts']); $m++) {
            if ($cart['discounts'][$m]['reduction_product']) {
                $cart['discounts'][$m]['reduction_product'] = $quotationRepository->findProductAssignToDiscount($cart['discounts'][$m]['reduction_product']);
                if ($cart['discounts'][$m]['reduction_percent'] !== '0.00') {
                    $cart['discounts'][$m]['reduction_amount'] =
                        $cart['discounts'][$m]['reduction_product']['product_price'] * $cart['discounts'][$m]['reduction_percent'] / 100;
                    $cart['discounts'][$m]['reduction_amount'] = strval($cart['discounts'][$m]['reduction_amount']);
                }
            } else if ($cart['discounts'][$m]['reduction_percent'] !== '0.00') {
                $cart['discounts'][$m]['reduction_amount'] = $cart['total_cart'] * $cart['discounts'][$m]['reduction_percent'] / 100;
                $cart['discounts'][$m]['reduction_amount'] = strval($cart['discounts'][$m]['reduction_amount']);
            }
            $cart['discounts'][$m]['reduction_amount_ht'] = $cart['discounts'][$m]['reduction_amount'];

            $cart['discounts'][$m]['reduction_amount_tax'] = '0';
            if ($cart['discounts'][$m]['reduction_tax'] !== null) {
                $cart['discounts'][$m]['reduction_amount_tax'] = ($cart['discounts'][$m]['reduction_amount'] * 20) / 100;
                $cart['discounts'][$m]['reduction_amount_tax'] = number_format($cart['discounts'][$m]['reduction_amount_tax'], 2);
                $cart['discounts'][$m]['reduction_amount_tax'] = strval($cart['discounts'][$m]['reduction_amount_tax']);
                if ($cart['discounts'][$m]['reduction_tax'] == '1') {
                    $cart['discounts'][$m]['reduction_amount_ht'] = $cart['discounts'][$m]['reduction_amount'] - $cart['discounts'][$m]['reduction_amount_tax'];
                    $cart['discounts'][$m]['reduction_amount_ht'] = number_format($cart['discounts'][$m]['reduction_amount_ht'], 2);
                    $cart['discounts'][$m]['reduction_amount_ht'] = strval($cart['discounts'][$m]['reduction_amount_ht']);
                }
            }
            $cart['total_discounts'] += number_format($cart['discounts'][$m]['reduction_amount_ht'], 2);
        }
        $cart['total_discounts'] = strval(($cart['total_discounts']));

        // On calcule le montant total de la tva des réductions HT
        $cart['total_discounts_tax'] = '0';
        for ($n = 0; $n < count($cart['discounts']); $n++) {
            $cart['total_discounts_tax'] += $cart['discounts'][$n]['reduction_amount_tax'];
        }
        $cart['total_discounts_tax'] = number_format($cart['total_discounts_tax'], 2);
        $cart['total_discounts_tax'] = strval($cart['total_discounts_tax']);

        // On calcule le montant total de la tva des produits HT
        $cart['total_product_taxes'] = '0';
        for ($l = 0; $l < count($cart['products']); $l++) {
            $cart['total_product_taxes'] += $cart['products'][$l]['total_tva_amount_product'];
        }
        $cart['total_product_taxes'] = number_format($cart['total_product_taxes'], 2);
        $cart['total_product_taxes'] = strval($cart['total_product_taxes']);

        // On calcule le montant HT après réductions
        $cart['total_ht_with_discount'] = '0';
        $cart['total_ht_with_discount'] = $cart['total_cart'] - $cart['total_discounts'];
        $cart['total_ht_with_discount'] = strval($cart['total_ht_with_discount']);

        // On calucule le montant total de la tva
        $cart['total_taxes'] = '0';
        $cart['total_taxes'] = $cart['total_product_taxes'] - $cart['total_discounts_tax'];
        $cart['total_taxes'] = strval($cart['total_taxes']);

        // On calule le montant total ttc du panier
        $cart['total_ttc'] = number_format(($cart['total_cart'] - $cart['total_discounts']) + $cart['total_taxes'], 2);

        $quotationPdf = new QuotationPdf();

        // Nom du fichier pdf qui comprend le nom et prénom du client et le numéro de devis
        $filename = $quotation['firstname'] . ' ' . $filename = $quotation['lastname'] . '  - Référence n° ' . $filename = $quotation['reference'];

        $html = $this->renderView('@Modules/quotation/templates/admin/pdf/pdf_quotation.html.twig', [
            'quotation' => $quotation,
            'cart' => $cart,
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

        // On récupère les adresses du client
        if ($quotation['id_customer']) {
            $quotation['addresses'] = $quotationRepository->findAddressesByCustomer($quotation['id_customer']);
            // Si le client ne dispose pas d'adresse, on affecte l'id_adress à 0
            if ($quotation['addresses'] === []) {
                $customerIdAddress['id_address'] = $quotation['addresses'] === [] ? '0' : $quotation['addresses'];
                array_push($quotation['addresses'], $customerIdAddress);
            }
        }

        $cart = $quotationRepository->findOneCartById($quotation['id_cart']);

        if ($cart['id_cart']) {
            // On récupère l'adresse du magasin
            $cart['addressStore'] = $quotationRepository->findAddressStore ($cart['id_cart']);
            $cart['products'] = $quotationRepository->findProductsCustomerByCarts($cart['id_cart']);
            $cart['discounts'] = $quotationRepository->findDiscountsByIdCart($cart['id_cart']);
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
                    $cart['products'][$j]['tva_amount_product'] = number_format(($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100, 2);
                    $cart['products'][$j]['total_tva_amount_product'] = number_format((($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100) * $cart['products'][$j]['product_quantity'], 2);
                    // On récupère les images liées aux produits
                    $cart['products'][$j]['attributes'] = $quotationRepository->findAttributesByProduct($cart['products'][$j]['id_product'],
                        $cart['products'][$j]['id_product_attribute']);
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

        // Partie Discount
        $cart['total_discounts'] = 0;
        for ($m = 0; $m < count($cart['discounts']); $m++) {
            if ($cart['discounts'][$m]['reduction_product']) {
                $cart['discounts'][$m]['reduction_product'] = $quotationRepository->findProductAssignToDiscount($cart['discounts'][$m]['reduction_product']);
                if ($cart['discounts'][$m]['reduction_percent'] !== '0.00') {
                    $cart['discounts'][$m]['reduction_amount'] =
                        $cart['discounts'][$m]['reduction_product']['product_price'] * $cart['discounts'][$m]['reduction_percent'] / 100;
                    $cart['discounts'][$m]['reduction_amount'] = strval($cart['discounts'][$m]['reduction_amount']);
                }
            } else if ($cart['discounts'][$m]['reduction_percent'] !== '0.00') {
                $cart['discounts'][$m]['reduction_amount'] = $cart['total_cart'] * $cart['discounts'][$m]['reduction_percent'] / 100;
                $cart['discounts'][$m]['reduction_amount'] = strval($cart['discounts'][$m]['reduction_amount']);
            }
            $cart['discounts'][$m]['reduction_amount_ht'] = $cart['discounts'][$m]['reduction_amount'];

            $cart['discounts'][$m]['reduction_amount_tax'] = '0';
            if ($cart['discounts'][$m]['reduction_tax'] !== null) {
                $cart['discounts'][$m]['reduction_amount_tax'] = ($cart['discounts'][$m]['reduction_amount'] * 20) / 100;
                $cart['discounts'][$m]['reduction_amount_tax'] = number_format($cart['discounts'][$m]['reduction_amount_tax'], 2);
                $cart['discounts'][$m]['reduction_amount_tax'] = strval($cart['discounts'][$m]['reduction_amount_tax']);
                if ($cart['discounts'][$m]['reduction_tax'] == '1') {
                    $cart['discounts'][$m]['reduction_amount_ht'] = $cart['discounts'][$m]['reduction_amount'] - $cart['discounts'][$m]['reduction_amount_tax'];
                    $cart['discounts'][$m]['reduction_amount_ht'] = number_format($cart['discounts'][$m]['reduction_amount_ht'], 2);
                    $cart['discounts'][$m]['reduction_amount_ht'] = strval($cart['discounts'][$m]['reduction_amount_ht']);
                }
            }
            $cart['total_discounts'] += number_format($cart['discounts'][$m]['reduction_amount_ht'], 2);
        }
        $cart['total_discounts'] = strval(($cart['total_discounts']));

        // On calcule le montant total de la tva des réductions HT
        $cart['total_discounts_tax'] = '0';
        for ($n = 0; $n < count($cart['discounts']); $n++) {
            $cart['total_discounts_tax'] += $cart['discounts'][$n]['reduction_amount_tax'];
        }
        $cart['total_discounts_tax'] = number_format($cart['total_discounts_tax'], 2);
        $cart['total_discounts_tax'] = strval($cart['total_discounts_tax']);

        // On calcule le montant total de la tva des produits HT
        $cart['total_product_taxes'] = '0';
        for ($l = 0; $l < count($cart['products']); $l++) {
            $cart['total_product_taxes'] += $cart['products'][$l]['total_tva_amount_product'];
        }
        $cart['total_product_taxes'] = number_format($cart['total_product_taxes'], 2);
        $cart['total_product_taxes'] = strval($cart['total_product_taxes']);

        // On calcule le montant HT après réductions
        $cart['total_ht_with_discount'] = '0';
        $cart['total_ht_with_discount'] = $cart['total_cart'] - $cart['total_discounts'];
        $cart['total_ht_with_discount'] = strval($cart['total_ht_with_discount']);

        // On calucule le montant total de la tva
        $cart['total_taxes'] = '0';
        $cart['total_taxes'] = $cart['total_product_taxes'] - $cart['total_discounts_tax'];
        $cart['total_taxes'] = strval($cart['total_taxes']);

        // On calule le montant total ttc du panier
        $cart['total_ttc'] = number_format(($cart['total_cart'] - $cart['total_discounts']) + $cart['total_taxes'], 2);

        // Nom du fichier pdf qui comprend le nom et prénom du client et le numéro de devis
        $filename = $quotation['firstname'] . ' ' . $filename = $quotation['lastname'] . '  - Référence n° ' . $filename = $quotation['reference'];

        $html = $this->renderView('@Modules/quotation/templates/admin/pdf/pdf_quotation.html.twig', [
            'quotation' => $quotation,
            'cart' => $cart,
        ]);

        // Rendu PDF
        $pdf_file = new Dompdf();
        $pdf_file->loadHtml($html);

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
            ->attach(\Swift_Attachment::newInstance($pdf_content, $filename, 'application/pdf'));

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

        $formQuotationStatus = $this->createForm(QuotationStatusType::class, $quotation);
        $formQuotationStatus->handleRequest($request);

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
            'formQuotationStatus' => $formQuotationStatus->createView(),
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
                // En fonction des carts qui ont été récupérés, on récupère les réductions liés à ce cart avec la méthode findDiscountsByIdCart
                $carts[$i]['discounts'] = $quotationRepository->findDiscountsByIdCart($carts[$i]['id_cart']);
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
                    $carts[$i]['total_taxes'] = '0';
                    $carts[$i]['total_discounts'] = '0';
                    if ($carts[$i]['products']) {
                        $carts[$i]['products'][$j]['id_product'];
                        $carts[$i]['products'][$j]['product_name'];
                        $carts[$i]['products'][$j]['product_price'] = number_format($carts[$i]['products'][$j]['product_price'], 2);
                        $carts[$i]['products'][$j]['product_quantity'];
                        $carts[$i]['products'][$j]['total_product'] = number_format($carts[$i]['products'][$j]['total_product'], 2);
                        $carts[$i]['products'][$j]['tva_amount_product'] = number_format(($carts[$i]['products'][$j]['product_price'] * $carts[$i]['products'][$j]['rate']) / 100, 2);
                        $carts[$i]['products'][$j]['total_tva_amount_product'] = number_format((($carts[$i]['products'][$j]['product_price'] * $carts[$i]['products'][$j]['rate']) / 100) * $carts[$i]['products'][$j]['product_quantity'], 2);
                        // On récupère les images liées aux produits
                        $carts[$i]['products'][$j]['attributes'] = $quotationRepository->findAttributesByProduct($carts[$i]['products'][$j]['id_product'],
                            $carts[$i]['products'][$j]['id_product_attribute']);
                        $carts[$i]['products'][$j]['id_image'] = $quotationRepository->findPicturesByAttributesProduct($carts[$i]['products'][$j]['id_product'],
                            $carts[$i]['products'][$j]['id_product_attribute'])['id_image'];
                        if ($carts[$i]['products'][$j]['id_image'] == '0' || $carts[$i]['products'][$j]['id_product_attribute'] == '0') {
                            $carts[$i]['products'][$j]['id_image'] = $quotationRepository->findPicturesByProduct($carts[$i]['products'][$j]['id_product'])['id_image'];
                        }

                        // Pour créer le path, on va séparer l'id_image s'il dispose d'un nombre à 2 chiffres sinon on récupère l'id_image
                        $carts[$i]['products'][$j]['path'] = $carts[$i]['products'][$j]['id_image'];
                        if ($carts[$i]['products'][$j]['path']) {
                            $carts[$i]['products'][$j]['path'] = str_split($carts[$i]['products'][$j]['path']);
                        }
                    }
                }
            }

            for ($k = 0; $k < count($carts[$i]['products']); $k++) {
                $attributes = '';
                if (isset($carts[$i]['products'][$k]['attributes'])) {
                    for ($l = 0; $l < count($carts[$i]['products'][$k]['attributes']); $l++) {
                        $attributes .= $carts[$i]['products'][$k]['attributes'][$l]['attribute_details'] . ' - ';
                    }
                    $carts[$i]['products'][$k]['attributes'] = rtrim($attributes, ' - ');
                }
            }

            // Partie Discount
            for ($m = 0; $m < count($carts[$i]['discounts']); $m++) {
                if ($carts[$i]['discounts'][$m]['reduction_product']) {
                    $carts[$i]['discounts'][$m]['reduction_product'] = $quotationRepository->findProductAssignToDiscount($carts[$i]['discounts'][$m]['reduction_product']);
                    if ($carts[$i]['discounts'][$m]['reduction_percent'] !== '0.00') {
                        $carts[$i]['discounts'][$m]['reduction_amount'] =
                            $carts[$i]['discounts'][$m]['reduction_product']['product_price'] * $carts[$i]['discounts'][$m]['reduction_percent'] / 100;
                        $carts[$i]['discounts'][$m]['reduction_amount'] = strval($carts[$i]['discounts'][$m]['reduction_amount']);
                    }
                } else if ($carts[$i]['discounts'][$m]['reduction_percent'] !== '0.00') {
                    $carts[$i]['discounts'][$m]['reduction_amount'] = $carts[$i]['total_cart'] * $carts[$i]['discounts'][$m]['reduction_percent'] / 100;
                    $carts[$i]['discounts'][$m]['reduction_amount'] = strval($carts[$i]['discounts'][$m]['reduction_amount']);
                }
                $carts[$i]['discounts'][$m]['reduction_amount_ht'] = $carts[$i]['discounts'][$m]['reduction_amount'];

                $carts[$i]['discounts'][$m]['reduction_amount_tax'] = '0';
                if ($carts[$i]['discounts'][$m]['reduction_tax'] !== null) {
                    $carts[$i]['discounts'][$m]['reduction_amount_tax'] = ($carts[$i]['discounts'][$m]['reduction_amount'] * 20) / 100;
                    $carts[$i]['discounts'][$m]['reduction_amount_tax'] = number_format($carts[$i]['discounts'][$m]['reduction_amount_tax'], 2);
                    $carts[$i]['discounts'][$m]['reduction_amount_tax'] = strval($carts[$i]['discounts'][$m]['reduction_amount_tax']);
                    if ($carts[$i]['discounts'][$m]['reduction_tax'] == '1') {
                        $carts[$i]['discounts'][$m]['reduction_amount_ht'] = $carts[$i]['discounts'][$m]['reduction_amount'] - $carts[$i]['discounts'][$m]['reduction_amount_tax'];
                        $carts[$i]['discounts'][$m]['reduction_amount_ht'] = number_format($carts[$i]['discounts'][$m]['reduction_amount_ht'], 2);
                        $carts[$i]['discounts'][$m]['reduction_amount_ht'] = strval($carts[$i]['discounts'][$m]['reduction_amount_ht']);
                    }
                }
                $carts[$i]['total_discounts'] += number_format($carts[$i]['discounts'][$m]['reduction_amount_ht'], 2);
            }
            $carts[$i]['total_discounts'] = strval(($carts[$i]['total_discounts']));

            // On calcule le montant total de la tva des réductions HT
            $carts[$i]['total_discounts_tax'] = '0';
            for ($n = 0; $n < count($carts[$i]['discounts']); $n++) {
                $carts[$i]['total_discounts_tax'] += $carts[$i]['discounts'][$n]['reduction_amount_tax'];
            }
            $carts[$i]['total_discounts_tax'] = number_format($carts[$i]['total_discounts_tax'], 2);
            $carts[$i]['total_discounts_tax'] = strval($carts[$i]['total_discounts_tax']);

            // On calcule le montant total de la tva des produits HT
            $carts[$i]['total_product_taxes'] = '0';
            for ($o = 0; $o < count($carts[$i]['products']); $o++) {
                $carts[$i]['total_product_taxes'] += $carts[$i]['products'][$o]['total_tva_amount_product'];
            }
            $carts[$i]['total_product_taxes'] = number_format($carts[$i]['total_product_taxes'], 2);
            $carts[$i]['total_product_taxes'] = strval($carts[$i]['total_product_taxes']);

            // On calucule le montant total de la tva
            $carts[$i]['total_taxes'] = '0';
            $carts[$i]['total_taxes'] = $carts[$i]['total_product_taxes'] - $carts[$i]['total_discounts_tax'];
            $carts[$i]['total_taxes'] = strval($carts[$i]['total_taxes']);

            // On calule le montant total ttc du panier
            $carts[$i]['total_ttc'] = number_format(($carts[$i]['total_cart'] - $carts[$i]['total_discounts']) + $carts[$i]['total_taxes'], 2);

            for ($p = 0; $p < count($carts[$i]['orders']); $p++) {
                if ($carts[$i]['orders']) {
                    $carts[$i]['orders'][$p]['total_products'] = number_format($carts[$i]['orders'][$p]['total_products'], 2);
                    $carts[$i]['orders'][$p]['total_shipping'] = number_format($carts[$i]['orders'][$p]['total_shipping'], 2);
                    $carts[$i]['orders'][$p]['total_paid'] = number_format($carts[$i]['orders'][$p]['total_paid'], 2);
                }
            }

            for ($q = 0; $q < count($carts[$i]['quotations']); $q++) {
                if ($carts[$i]['quotations']) {
                    $carts[$i]['quotations'][$q]['price'] = number_format($carts[$i]['quotations'][$q]['price'], 2);
                    $carts[$i]['quotations'][$q]['total_quotation'] = number_format($carts[$i]['quotations'][$q]['total_quotation'], 2);
                }
            }

            foreach ($quotations as $key => $quotation) {
                $response[$key]['id_customer'] = $id_customer;
                $response[$key]['id_quotation'] = $quotation['id_quotation'];
                $response[$key]['quotation_reference'] = $quotation['quotation_reference'];
                $response[$key]['id_cart'] = $quotation['id_cart'];
                $response[$key]['date_quotation'] = date("d/m/Y", strtotime($quotation['date_quotation']));
                $response[$key]['total_quotation'] = number_format(($carts[$i]['total_cart'] - $carts[$i]['total_discounts']) + $carts[$i]['total_taxes'], 2);
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

        foreach ($quotations as $key => $quotation) {
            $quotations[$key]['id_customer'] = $id_customer;
            $quotations[$key]['id_quotation'] = $quotation['id_quotation'];
            $quotations[$key]['quotation_reference'] = $quotation['quotation_reference'];
            $quotations[$key]['id_cart'] = $quotation['id_cart'];
            $quotations[$key]['date_quotation'] = date("d/m/Y", strtotime($quotation['date_quotation']));
            $quotations[$key]['total_quotation'] = number_format($quotation['total_quotation'], 2);
            $quotations[$key]['products'] = $quotationRepository->findProductsCustomerByCarts($quotation['id_cart']);
            for ($r = 0; $r < count($quotations[$key]['products']); $r++) {
                if ($quotations[$key]['products'][$r]) {
                    $quotations[$key]['products'][$r]['total_product'] = number_format($quotations[$key]['products'][$r]['total_product'], 2);
                    $quotations[$key]['products'][$r]['tva_amount_product'] = number_format(($quotations[$key]['products'][$r]['product_price'] *
                            $quotations[$key]['products'][$r]['rate']) / 100, 2);
                    $quotations[$key]['products'][$r]['total_tva_amount_product'] = number_format((($quotations[$key]['products'][$r]['product_price'] *
                                $quotations[$key]['products'][$r]['rate']) / 100) * $quotations[$key]['products'][$r]['product_quantity'], 2);
                }
            }
            $quotations[$key]['total_discounts'] = '0';
            $quotations[$key]['discounts'] = $quotationRepository->findDiscountsByIdCart($quotations[$key]['id_cart']);
            for ($s = 0; $s < count($quotations[$key]['discounts']); $s++) {
                if ($quotations[$key]['discounts'][$s]['reduction_product']) {
                    $quotations[$key]['discounts'][$s]['reduction_product'] = $quotationRepository->findProductAssignToDiscount($quotations[$key]['discounts'][$s]['reduction_product']);
                    if ($quotations[$key]['discounts'][$s]['reduction_percent'] !== '0.00') {
                        $quotations[$key]['discounts'][$s]['reduction_amount'] =
                            $quotations[$key]['discounts'][$s]['reduction_product']['product_price'] * $quotations[$key]['discounts'][$s]['reduction_percent'] / 100;
                        $quotations[$key]['discounts'][$s]['reduction_amount'] = strval($quotations[$key]['discounts'][$s]['reduction_amount']);
                    }
                } else if ($quotations[$key]['discounts'][$s]['reduction_percent'] !== '0.00') {
                    $quotations[$key]['discounts'][$s]['reduction_amount'] = $quotations[$key]['total_quotation'] * $quotations[$key]['discounts'][$s]['reduction_percent'] / 100;
                    $quotations[$key]['discounts'][$s]['reduction_amount'] = strval($quotations[$key]['discounts'][$s]['reduction_amount']);
                }
                $quotations[$key]['discounts'][$s]['reduction_amount_ht'] = $quotations[$key]['discounts'][$s]['reduction_amount'];

                $quotations[$key]['discounts'][$s]['reduction_amount_tax'] = '0';
                if ($quotations[$key]['discounts'][$s]['reduction_tax'] !== null) {
                    $quotations[$key]['discounts'][$s]['reduction_amount_tax'] = ($quotations[$key]['discounts'][$s]['reduction_amount'] * 20) / 100;
                    $quotations[$key]['discounts'][$s]['reduction_amount_tax'] = number_format($quotations[$key]['discounts'][$s]['reduction_amount_tax'], 2);
                    $quotations[$key]['discounts'][$s]['reduction_amount_tax'] = strval($quotations[$key]['discounts'][$s]['reduction_amount_tax']);
                }
                $quotations[$key]['total_discounts'] += number_format($quotations[$key]['discounts'][$s]['reduction_amount_ht'], 2);
            }
            $quotations[$key]['total_discounts'] = strval(($quotations[$key]['total_discounts']));

            // On calcule le montant total de la tva des réductions HT
            $quotations[$key]['total_discounts_tax'] = '0';
            for ($t = 0; $t < count($quotations[$key]['discounts']); $t++) {
                $quotations[$key]['total_discounts_tax'] += $quotations[$key]['discounts'][$t]['reduction_amount_tax'];
            }
            $quotations[$key]['total_discounts_tax'] = number_format($quotations[$key]['total_discounts_tax'], 2);
            $quotations[$key]['total_discounts_tax'] = strval($quotations[$key]['total_discounts_tax']);

            // On calcule le montant total de la tva des produits HT
            $quotations[$key]['total_product_taxes'] = '0';
            for ($u = 0; $u < count($quotations[$key]['products']); $u++) {
                $quotations[$key]['total_product_taxes'] += $quotations[$key]['products'][$u]['total_tva_amount_product'];
            }
            $quotations[$key]['total_product_taxes'] = number_format($quotations[$key]['total_product_taxes'], 2);
            $quotations[$key]['total_product_taxes'] = strval($quotations[$key]['total_product_taxes']);

            // On calucule le montant total de la tva
            $quotations[$key]['total_taxes'] = '0';
            $quotations[$key]['total_taxes'] = $quotations[$key]['total_product_taxes'] - $quotations[$key]['total_discounts_tax'];
            $quotations[$key]['total_taxes'] = strval($quotations[$key]['total_taxes']);

            // On calule le montant total ttc du panier
            $quotations[$key]['total_ttc'] = number_format(($quotations[$key]['total_quotation'] - $quotations[$key]['total_discounts']) + $quotations[$key]['total_taxes'], 2);
        }

        return new JsonResponse(json_encode([
            'customer' => $customer,
            'carts' => $carts,
            'orders' => $orders,
            'quotations' => $quotations,
            'id_last_cart' => $idLastCart = $quotationRepository->findLastCartByCustomerId()['id_cart'] + 1,  // Permet de récupérer le dernier cart d'un customer que l'on récupère ensuite en js via le json
            'id_last_quotation' => $idLastQuotation = $quotationRepository->findLastQuotationByCustomerId()['id_quotation'] + 1,  // Permet de récupérer le dernier quotation d'un customer que l'on récupère ensuite en js via le json
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
            $cart['discounts'] = $quotationRepository->findDiscountsByIdCart($cart['id_cart']);
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
                    $cart['products'][$j]['tva_amount_product'] = number_format(($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100, 2);
                    $cart['products'][$j]['total_tva_amount_product'] = number_format((($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100) * $cart['products'][$j]['product_quantity'], 2);
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

        // Partie Discount
        $cart['total_discounts'] = '0';
        for ($m = 0; $m < count($cart['discounts']); $m++) {
            if ($cart['discounts'][$m]['reduction_product']) {
                $cart['discounts'][$m]['reduction_product'] = $quotationRepository->findProductAssignToDiscount($cart['discounts'][$m]['reduction_product']);
                if ($cart['discounts'][$m]['reduction_percent'] !== '0.00') {
                    $cart['discounts'][$m]['reduction_amount'] =
                        $cart['discounts'][$m]['reduction_product']['product_price'] * $cart['discounts'][$m]['reduction_percent'] / 100;
                    $cart['discounts'][$m]['reduction_amount'] = strval($cart['discounts'][$m]['reduction_amount']);
                }
            } else if ($cart['discounts'][$m]['reduction_percent'] !== '0.00') {
                $cart['discounts'][$m]['reduction_amount'] = $cart['total_cart'] * $cart['discounts'][$m]['reduction_percent'] / 100;
                $cart['discounts'][$m]['reduction_amount'] = strval($cart['discounts'][$m]['reduction_amount']);
            }
            $cart['discounts'][$m]['reduction_amount_ht'] = $cart['discounts'][$m]['reduction_amount'];

            $cart['discounts'][$m]['reduction_amount_tax'] = '0';
            if ($cart['discounts'][$m]['reduction_tax'] !== null) {
                $cart['discounts'][$m]['reduction_amount_tax'] = ($cart['discounts'][$m]['reduction_amount'] * 20) / 100;
                $cart['discounts'][$m]['reduction_amount_tax'] = number_format($cart['discounts'][$m]['reduction_amount_tax'], 2);
                $cart['discounts'][$m]['reduction_amount_tax'] = strval($cart['discounts'][$m]['reduction_amount_tax']);
                if ($cart['discounts'][$m]['reduction_tax'] == '1') {
                    $cart['discounts'][$m]['reduction_amount_ht'] = $cart['discounts'][$m]['reduction_amount'] - $cart['discounts'][$m]['reduction_amount_tax'];
                    $cart['discounts'][$m]['reduction_amount_ht'] = number_format($cart['discounts'][$m]['reduction_amount_ht'], 2);
                    $cart['discounts'][$m]['reduction_amount_ht'] = strval($cart['discounts'][$m]['reduction_amount_ht']);
                }
            }
            $cart['total_discounts'] += number_format($cart['discounts'][$m]['reduction_amount_ht'], 2);
        }
        $cart['total_discounts'] = strval(($cart['total_discounts']));

        // On calcule le montant total de la tva des réductions HT
        $cart['total_discounts_tax'] = '0';
        for ($n = 0; $n < count($cart['discounts']); $n++) {
            $cart['total_discounts_tax'] += $cart['discounts'][$n]['reduction_amount_tax'];
        }
        $cart['total_discounts_tax'] = number_format($cart['total_discounts_tax'], 2);
        $cart['total_discounts_tax'] = strval($cart['total_discounts_tax']);

        // On calcule le montant total de la tva des produits HT
        $cart['total_product_taxes'] = '0';
        for ($l = 0; $l < count($cart['products']); $l++) {
            $cart['total_product_taxes'] += $cart['products'][$l]['total_tva_amount_product'];
        }
        $cart['total_product_taxes'] = number_format($cart['total_product_taxes'], 2);
        $cart['total_product_taxes'] = strval($cart['total_product_taxes']);

        // On calucule le montant total de la tva
        $cart['total_taxes'] = '0';
        $cart['total_taxes'] = $cart['total_product_taxes'] - $cart['total_discounts_tax'];
        $cart['total_taxes'] = strval($cart['total_taxes']);

        // On calule le montant total ttc du panier
        $cart['total_ttc'] = number_format(($cart['total_cart'] - $cart['total_discounts']) + $cart['total_taxes'], 2);


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
                $cart['total_taxes'] = 0;
                if ($cart['products']) {
                    $cart['products'][$j]['total_product'] = number_format($cart['products'][$j]['total_product'], 2);
                    $cart['products'][$j]['tva_amount_product'] = number_format(($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100, 2);
                    $cart['products'][$j]['total_tva_amount_product'] = number_format((($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100) * $cart['products'][$j]['product_quantity'], 2);
                }
            }
        }

        // On calcule le montant total de la tva
        for ($l = 0; $l < count($cart['products']); $l++) {
            $cart['total_taxes'] += number_format($cart['products'][$l]['total_tva_amount_product'], 2);
        }
        $cart['total_taxes'] = strval($cart['total_taxes']);

        // On calule le montant total ttc du panier
        $cart['total_ttc'] = number_format($cart['total_cart'] + $cart['total_taxes'], 2);

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
            $cart['products'] = $quotationRepository->findProductsCustomerByCarts($cart['id_cart']);
        }

        for ($j = 0; $j < count($cart['products']); $j++) {
            if ($cart['id_cart']) {
                $cart['total_cart'] = number_format($cart['total_cart'], 2);
                $cart['total_taxes'] = 0;
                if ($cart['products']) {
                    $cart['products'][$j]['total_product'] = number_format($cart['products'][$j]['total_product'], 2);
                    $cart['products'][$j]['tva_amount_product'] = number_format(($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100, 2);
                    $cart['products'][$j]['total_tva_amount_product'] = number_format((($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100) * $cart['products'][$j]['product_quantity'], 2);
                }
            }
        }

        // On calcule le montant total de la tva
        for ($l = 0; $l < count($cart['products']); $l++) {
            $cart['total_taxes'] += number_format($cart['products'][$l]['total_tva_amount_product'], 2);
        }
        $cart['total_taxes'] = strval($cart['total_taxes']);

        // On calule le montant total ttc du panier
        $cart['total_ttc'] = number_format($cart['total_cart'] + $cart['total_taxes'], 2);

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
        if ($discount['reduction_product'] === '0') {
            $discount['reduction_product'] = null;
        }

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
     * Delete discount on cart
     * @param $id_cart
     * @param $id_cart_rule
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteDiscountCart($id_cart, $id_cart_rule)
    {
        $quotationRepository = $this->get('quotation_repository');
        $discount = $quotationRepository->deleteDiscountOnCart($id_cart, $id_cart_rule);
        $cart = $quotationRepository->findOneCartById($id_cart);

        if ($cart['id_cart']) {
            $cart['products'] = $quotationRepository->findProductsCustomerByCarts($cart['id_cart']);
            $cart['discounts'] = $quotationRepository->findDiscountsByIdCart($cart['id_cart']);
        }

        for ($j = 0; $j < count($cart['products']); $j++) {
            if ($cart['id_cart']) {
                $cart['id_cart'];
                $cart['date_cart'] = date("d/m/Y", strtotime($cart['date_cart']));
                $cart['total_cart'] = number_format($cart['total_cart'], 2);
                $cart['total_taxes'] = 0;
                $cart['total_discounts'] = 0;
                if ($cart['products']) {
                    $cart['products'][$j]['id_product'];
                    $cart['products'][$j]['product_name'];
                    $cart['products'][$j]['product_price'] = number_format($cart['products'][$j]['product_price'], 2);
                    $cart['products'][$j]['product_quantity'];
                    $cart['products'][$j]['total_product'] = number_format($cart['products'][$j]['total_product'], 2);
                    $cart['products'][$j]['tva_amount_product'] = number_format(($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100, 2);
                    $cart['products'][$j]['total_tva_amount_product'] = number_format((($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100) * $cart['products'][$j]['product_quantity'], 2);
                }
            }
        }

        // On calcule le montant total de la tva
        for ($l = 0; $l < count($cart['products']); $l++) {
            $cart['total_taxes'] += number_format($cart['products'][$l]['total_tva_amount_product'], 2);
        }
        $cart['total_taxes'] = strval($cart['total_taxes']);

        // Partie Discount
        for ($m = 0; $m < count($cart['discounts']); $m++) {
            if ($cart['discounts'][$m]['reduction_product']) {
                $cart['discounts'][$m]['reduction_product'] = $quotationRepository->findProductAssignToDiscount($cart['discounts'][$m]['reduction_product']);
                if ($cart['discounts'][$m]['reduction_percent'] !== '0.00') {
                    $cart['discounts'][$m]['reduction_amount'] =
                        $cart['discounts'][$m]['reduction_product']['product_price'] * $cart['discounts'][$m]['reduction_percent'] / 100;
                    $cart['discounts'][$m]['reduction_amount'] = strval($cart['discounts'][$m]['reduction_amount']);
                }
            } else if ($cart['discounts'][$m]['reduction_percent'] !== '0.00') {
                $cart['discounts'][$m]['reduction_amount'] = $cart['total_cart'] * $cart['discounts'][$m]['reduction_percent'] / 100;
                $cart['discounts'][$m]['reduction_amount'] = strval($cart['discounts'][$m]['reduction_amount']);
            }
            $cart['total_discounts'] += number_format($cart['discounts'][$m]['reduction_amount'], 2);
        }
        $cart['total_discounts'] = strval(($cart['total_discounts']));

        // On calule le montant total ttc du panier
        $cart['total_ttc'] = number_format(($cart['total_cart'] - $cart['total_discounts']) + $cart['total_taxes'], 2);

        return new JsonResponse(json_encode($cart), 200, [], true);
    }

    /**
     * Create new quotation
     * @param $id_cart
     * @param $id_customer
     * @param $reference
     * @param $message_visible
     * @param $date_add
     * @param $status
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function createNewQuotation($id_cart, $id_customer, $reference, $message_visible, $date_add, $status)
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotation = $quotationRepository->createQuotation($id_cart, $id_customer, $reference, $message_visible, $date_add, $status);

        return new JsonResponse(json_encode('Quotation create !'));
    }

    /**
     * Show quotation
     * @param $id_quotation
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showQuotation($id_quotation, Request $request, SessionInterface $session)
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotation = $quotationRepository->findQuotationById($id_quotation);

        // On récupère les adresses du client
        if ($quotation['id_customer']) {
            $quotation['addresses'] = $quotationRepository->findAddressesByCustomer($quotation['id_customer']);
            // Si le client ne dispose pas d'adresse, on affecte l'id_adress à 0
            if ($quotation['addresses'] === []) {
                $customerIdAddress['id_address'] = $quotation['addresses'] === [] ? '0' : $quotation['addresses'];
                array_push($quotation['addresses'], $customerIdAddress);
            }
        }

        $cart = $quotationRepository->findOneCartById($quotation['id_cart']);

        if ($cart['id_cart']) {
            $cart['products'] = $quotationRepository->findProductsCustomerByCarts($cart['id_cart']);
            $cart['discounts'] = $quotationRepository->findDiscountsByIdCart($cart['id_cart']);
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
                    $cart['products'][$j]['tva_amount_product'] = number_format(($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100, 2);
                    $cart['products'][$j]['total_tva_amount_product'] = number_format((($cart['products'][$j]['product_price'] * $cart['products'][$j]['rate']) / 100) * $cart['products'][$j]['product_quantity'], 2);
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
                        if (count($cart['products'][$j]['path']) !== 1) {
                            $cart['products'][$j]['url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/img/p/' . $cart['products'][$j]['path'][0] . '/' . $cart['products'][$j]['path'][1] . '/' . $cart['products'][$j]['id_image'] . '-cart_default.jpg';
                        } else {
                            $cart['products'][$j]['url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/img/p/' . $cart['products'][$j]['path'][0] . '/' . $cart['products'][$j]['id_image'] . '-cart_default.jpg';
                        }

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

        // Partie Discount
        $cart['total_discounts'] = 0;
        for ($m = 0; $m < count($cart['discounts']); $m++) {
            if ($cart['discounts'][$m]['reduction_product']) {
                $cart['discounts'][$m]['reduction_product'] = $quotationRepository->findProductAssignToDiscount($cart['discounts'][$m]['reduction_product']);
                if ($cart['discounts'][$m]['reduction_percent'] !== '0.00') {
                    $cart['discounts'][$m]['reduction_amount'] =
                        $cart['discounts'][$m]['reduction_product']['product_price'] * $cart['discounts'][$m]['reduction_percent'] / 100;
                    $cart['discounts'][$m]['reduction_amount'] = strval($cart['discounts'][$m]['reduction_amount']);
                }
            } else if ($cart['discounts'][$m]['reduction_percent'] !== '0.00') {
                $cart['discounts'][$m]['reduction_amount'] = $cart['total_cart'] * $cart['discounts'][$m]['reduction_percent'] / 100;
                $cart['discounts'][$m]['reduction_amount'] = strval($cart['discounts'][$m]['reduction_amount']);
            }
            $cart['discounts'][$m]['reduction_amount_ht'] = $cart['discounts'][$m]['reduction_amount'];

            $cart['discounts'][$m]['reduction_amount_tax'] = '0';
            if ($cart['discounts'][$m]['reduction_tax'] !== null) {
                $cart['discounts'][$m]['reduction_amount_tax'] = ($cart['discounts'][$m]['reduction_amount'] * 20) / 100;
                $cart['discounts'][$m]['reduction_amount_tax'] = number_format($cart['discounts'][$m]['reduction_amount_tax'], 2);
                $cart['discounts'][$m]['reduction_amount_tax'] = strval($cart['discounts'][$m]['reduction_amount_tax']);
                if ($cart['discounts'][$m]['reduction_tax'] == '1') {
                    $cart['discounts'][$m]['reduction_amount_ht'] = $cart['discounts'][$m]['reduction_amount'] - $cart['discounts'][$m]['reduction_amount_tax'];
                    $cart['discounts'][$m]['reduction_amount_ht'] = number_format($cart['discounts'][$m]['reduction_amount_ht'], 2);
                    $cart['discounts'][$m]['reduction_amount_ht'] = strval($cart['discounts'][$m]['reduction_amount_ht']);
                }
            }
            $cart['total_discounts'] += number_format($cart['discounts'][$m]['reduction_amount_ht'], 2);
        }
        $cart['total_discounts'] = strval(($cart['total_discounts']));

        // On calcule le montant total de la tva des réductions HT
        $cart['total_discounts_tax'] = '0';
        for ($n = 0; $n < count($cart['discounts']); $n++) {
            $cart['total_discounts_tax'] += $cart['discounts'][$n]['reduction_amount_tax'];
        }
        $cart['total_discounts_tax'] = number_format($cart['total_discounts_tax'], 2);
        $cart['total_discounts_tax'] = strval($cart['total_discounts_tax']);

        // On calcule le montant total de la tva des produits HT
        $cart['total_product_taxes'] = '0';
        for ($l = 0; $l < count($cart['products']); $l++) {
            $cart['total_product_taxes'] += $cart['products'][$l]['total_tva_amount_product'];
        }
        $cart['total_product_taxes'] = number_format($cart['total_product_taxes'], 2);
        $cart['total_product_taxes'] = strval($cart['total_product_taxes']);

        // On calcule le montant HT après réductions
        $cart['total_ht_with_discount'] = '0';
        $cart['total_ht_with_discount'] = $cart['total_cart'] - $cart['total_discounts'];
        $cart['total_ht_with_discount'] = strval($cart['total_ht_with_discount']);

        // On calucule le montant total de la tva
        $cart['total_taxes'] = '0';
        $cart['total_taxes'] = $cart['total_product_taxes'] - $cart['total_discounts_tax'];
        $cart['total_taxes'] = strval($cart['total_taxes']);

        // On calule le montant total ttc du panier
        $cart['total_ttc'] = number_format(($cart['total_cart'] - $cart['total_discounts']) + $cart['total_taxes'], 2);

        $formShowQuotationStatus = $this->createForm(QuotationShowStatusType::class, $quotation);
        $formShowQuotationStatus->handleRequest($request);

        $session->clear();

        return $this->render('@Modules/quotation/templates/admin/show_quotation.html.twig', [
            'quotation' => $quotation,
            'cart' => $cart,
            'formShowQuotationStatus' => $formShowQuotationStatus->createView(),
        ]);
    }

    /**
     * Update message from show quotation
     * @param $id_quotation
     * @param $message_visible
     * @return JsonResponse
     * @throws \Exception
     */
    public function updateMessageQuotation($id_quotation, $message_visible)
    {
        $quotationRepository = $this->get('quotation_repository');
        $messageQuotation = $quotationRepository->updateMessageQuotation($id_quotation, $message_visible);
        $quotation = $quotationRepository->findQuotationById($id_quotation);

        return new JsonResponse(json_encode($quotation), 200, [], true);
    }

    /**
     * Update status from show quotation
     * @param $id_quotation
     * @param $status
     * @return JsonResponse
     * @throws \Exception
     */
    public function updateStatusQuotation($id_quotation, $status)
    {
        $quotationRepository = $this->get('quotation_repository');
        $statusQuotation = $quotationRepository->updateStatusQuotation($id_quotation, $status);
        $quotation = $quotationRepository->findQuotationById($id_quotation);

        return new JsonResponse(json_encode($quotation), 200, [], true);
    }

    /**
     * Delete quotation
     * @param $id_quotation
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteQuotation($id_quotation)
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotation = $quotationRepository->deleteQuotation($id_quotation);

        $this->addFlash('success', 'Le devis a été supprimé.');
        return $this->redirectToRoute('quotation_admin');
    }
}
