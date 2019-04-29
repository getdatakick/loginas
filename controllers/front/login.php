<?php

class LoginAsLoginModuleFrontController extends ModuleFrontControllerCore
{
    /** @var LoginAs */
    public $module;

    /**
     * @throws Adapter_Exception
     * @throws HTMLPurifier_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();
        $customerId = (int)Tools::getValue('id_customer');
        $secret = Tools::getValue('secret');
        if ($customerId && $secret && $secret === $this->module->getSecret($customerId)) {
            $customer = new Customer($customerId);
            if (Validate::isLoadedObject($customer)) {
                if ($this->autoLogin($customer)) {
                    Tools::redirect($this->context->link->getPageLink('my-account', null, $this->context->language->id));
                }
            }
        }
        $this->setTemplate('unauthorized.tpl');
    }

    /**
     * @param Customer $customer
     * @return bool
     * @throws PrestaShopException
     */
    private function canLogin(Customer $customer)
    {
        return Customer::checkPassword($customer->id, $customer->passwd);
    }

    /**
     * @param Customer $customer
     * @return bool
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function autoLogin(Customer $customer)
    {
        if ($this->canLogin($customer)) {
            Context::getContext()->updateCustomer($customer);
            return true;
        }
        return false;
    }
}
