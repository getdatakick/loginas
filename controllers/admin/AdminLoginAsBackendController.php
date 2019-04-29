<?php

class AdminLoginAsBackendController extends ModuleAdminController
{
    /** @var LoginAs */
    public $module;

    public function __construct()
    {
        parent::__construct();
        $this->display = 'view';
    }

    /**
     * @throws HTMLPurifier_Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function initContent()
    {
        parent::initContent();
        $customerId = (int)Tools::getValue('id_customer');
        if ($customerId) {
            $customer = new Customer($customerId);
            if (Validate::isLoadedObject($customer)) {
                $url = $this->context->link->getModuleLink($this->module->name, 'login', [
                    'id_customer' => $customerId,
                    'secret' => $this->module->getSecret($customerId)
                ], null, $customer->id_lang);
                Tools::redirect($url);
            }
        }
    }

}
