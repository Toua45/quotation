<?php

namespace Quotation\Controller;

use Prestashop\modules\Quotation\Repository\QuotationRepository;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Quotation\Entity\Quotation;

class AdminQuotationController extends FrameworkBundleAdminController
{
    public function quotationIndex()
    {
        $quotations = $this->getDoctrine()
            ->getRepository(Quotation::class)
            ->findAll();

        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig', [
            'quotations' => $quotations
        ]);
    }

}
