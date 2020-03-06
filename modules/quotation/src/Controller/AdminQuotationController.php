<?php

namespace Quotation\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Quotation\Entity\Quotation;
use Quotation\Form\QuotationType;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminQuotationController extends FrameworkBundleAdminController
{
    public function quotationIndex()
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotations = $quotationRepository->findAll();

        //dump($quotationRepository->findAllCarts());
        //die;

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'quotations' => $quotations,
        ]);
    }

    public function add(Request $request)
    {
        $quotation = new Quotation();

        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);

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
            'test' => _MODULE_DIR_
        ]);
    }

    //http://localhost:8000/admin130mdhxh9/index.php/modules/quotation/admin/1/ajax
    public function ajaxCarts(Request $request)
    {
        //permet de récupérer l'id customer de l'url en excluant les autres caractères
        $idCustomer = (int) preg_replace('/[^\d]/', '', $request->getPathInfo());
        $quotationRepository = $this->get('quotation_repository');
        $carts = $quotationRepository->findCartsByCustomer($idCustomer);

        $cart = $response = [];

        foreach ($carts as $key => $cart) {

            $response[$key]['id_cart'] = $cart['id_cart'];
            $response[$key]['date_cart'] = date("d/m/Y", strtotime($cart['date_add']));
            $response[$key]['id_customer'] = $idCustomer;
        }

        $file = 'test.js';
        if (!is_file($file)) {
            $file = fopen($file, 'w') or die('Unable to open file!');
            for ($i = 0; $i < count($response); $i++) {
                fwrite($file,
                    ($i === 0 ? ('["' . $response[$i]['date_cart'] . '",') :
                        ($i === count($response) - 1 ? ('"' . $response[$i]['date_cart'] . '"]') :
                            ('"' . $response[$i]['date_cart'] . '",')))
                );
            }
            fclose($file);
        }

        //dump($response);die;
        return new JsonResponse(json_encode($response), 200, [], true);
    }
}
