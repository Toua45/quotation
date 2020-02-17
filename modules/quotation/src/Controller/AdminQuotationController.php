<?php

namespace Quotation\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use quotation\Entity\Quotation;
use Doctrine\ORM\EntityManagerInterface;
use Quotation\Form\QuotationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class AdminQuotationController extends FrameworkBundleAdminController
{
    public function adminAction(Request $request):Response
    {
        $quotation = new \Quotation();

        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($quotation);
            $entityManager->flush();
        }

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
