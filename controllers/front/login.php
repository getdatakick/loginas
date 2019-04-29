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
            if (Validate::isLoadedObject($customer) && $this->autoLogin($customer)) {
                Tools::redirect($this->context->link->getPageLink('my-account', null, $this->context->language->id));
            }
        }
        $this->setTemplate('unauthorized.tpl');
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
        Hook::exec('actionBeforeAuthentication');
        $this->context->cookie->id_compare = isset($this->context->cookie->id_compare) ? $this->context->cookie->id_compare : CompareProduct::getIdCompareByIdCustomer($customer->id);
        $this->context->cookie->id_customer = (int)($customer->id);
        $this->context->cookie->customer_lastname = $customer->lastname;
        $this->context->cookie->customer_firstname = $customer->firstname;
        $this->context->cookie->logged = 1;
        $this->context->cookie->is_guest = $customer->isGuest();
        $this->context->cookie->passwd = $customer->passwd;
        $this->context->cookie->email = $customer->email;

        // Add customer to the context
        $customer->logged = 1;
        $this->context->customer = $customer;

        $carts = Cart::getCustomerCarts($customer->id, true);
        if (count($carts)) {
            $cartData = array_shift($carts);
            $cart = new Cart((int)$cartData['id_cart']);
            $this->context->cart = $cart;
        }
        $this->context->cookie->id_cart = (int)$this->context->cart->id;

        $this->context->cookie->write();
        $this->context->cart->autosetProductAddress();
        Hook::exec('actionAuthentication', ['customer' => $this->context->customer]);
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);
        return $this->context->customer->isLogged();
    }
}
