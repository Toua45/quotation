<?php

namespace Quotation\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use quotation\Entity\Quotation;
use Doctrine\ORM\EntityManagerInterface;

class AdminQuotationController extends FrameworkBundleAdminController
{
    public function adminAction()
    {
        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig');
    }
}
