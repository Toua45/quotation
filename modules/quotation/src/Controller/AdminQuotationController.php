<?php

namespace Quotation\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Quotation\Entity\Quotation;
use Quotation\Repository\QuotationRepository;
use Symfony\Component\HttpFoundation\Request;

class AdminQuotationController extends FrameworkBundleAdminController
{
    public function quotationIndex(Request $req, $page)
    {
        // Call entityManager
        $em = $this->get('doctrine.orm.entity_manager');

        // Allows default methods
        $this->getDoctrine()->getRepository(Quotation::class);

        // Allows custom methods
        QuotationRepository::getRepository('Quotation', $em)->findAllCustomerQuotations();

        // Implementation exemples:
        $this->getDoctrine()->getRepository(Quotation::class)->findAll();
        $this->getDoctrine()->getRepository(Quotation::class)->findOneBy(['id' => 24]);

        QuotationRepository::getRepository('Quotation', $em)->findAllCustomerQuotations();
    }
}
