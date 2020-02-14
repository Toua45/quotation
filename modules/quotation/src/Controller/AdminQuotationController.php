<?php

namespace Quotation\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class AdminQuotationController extends FrameworkBundleAdminController
{
    public function adminAction()
    {
        return $this->render('@Modules/quotation/templates/admin/index_quotation.html.twig');
    }
}