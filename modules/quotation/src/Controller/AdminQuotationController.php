<?php

namespace Quotation\Controller;

use Prestashop\modules\Quotation\Repository\QuotationRepository;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Doctrine\ORM\EntityManagerInterface;
use Quotation\Entity\Quotation;
use Quotation\Form\QuotationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminQuotationController extends FrameworkBundleAdminController
{
    public function quotationIndex(Request $request)
    {
        $quotations = $this->getDoctrine()
            ->getRepository(Quotation::class)
            ->findAll();

        $quotation = new Quotation();

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
