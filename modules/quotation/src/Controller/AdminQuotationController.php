<?php

namespace Quotation\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Quotation\Entity\Quotation;
use Quotation\Form\QuotationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminQuotationController extends FrameworkBundleAdminController
{
    public function quotationIndex()
    {
        $quotationRepository = $this->get('quotation_repository');
        $quotations = $quotationRepository->findAll();

        //dump($quotationRepository->findAllCarts());die;

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'quotations' => $quotations,
        ]);
    }

    public function add(Request $request): Response
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
        ]);
    }

    public function ajaxCarts(): Response
    {
        $quotationRepository = $this->get('quotation_repository');
        $carts = $quotationRepository->findAllCarts();

        $id = $cart = [];
        foreach ($carts as $key => $cart) {
                $id[$key] = $cart['id_cart'];
                $date[$key] = $cart['date_add'];
        }
        $response = array_combine($id, $date);
        return new JsonResponse(json_encode($response), 200, [], true);
    }
}
