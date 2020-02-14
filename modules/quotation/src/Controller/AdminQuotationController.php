<?php

namespace Quotation\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class AdminQuotationController extends FrameworkBundleAdminController
{
    public function adminAction()
    {
        return $this->render('@Modules/quotation/templates/admin/base_quotation.html.twig');
    }
}