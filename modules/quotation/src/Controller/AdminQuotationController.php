<?php

namespace Quotation\Controller;

use Quotation\Repository\QuotationRepository;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Doctrine\ORM\EntityManagerInterface;
use Quotation\Entity\Quotation;
use Quotation\Form\QuotationType;
use Symfony\Component\HttpFoundation\Request;

class AdminQuotationController extends FrameworkBundleAdminController
{
    public function quotationIndex(Request $request)
    {
        dump($this->get('quotation_repository')->findAll());die;



        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($quotation);
            $entityManager->flush();
        }

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'quotations' => $quotations,
            'form' => $form->createView()
        ]);
    }

}
