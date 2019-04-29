<?php

class LoginAs extends Module
{
    const SALT = 'LOGIN_AS_SALT';

    protected $access = null;

    public function __construct()
    {
        $this->name = 'loginas';
        $this->tab = 'back_office_features';
        $this->author = 'datakick';
        $this->version = '0.0.1';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Login As Customer');
        $this->description = $this->l('this module allows you to login as a customer');
        $this->controllers = ['login'];
    }

    /**
     * @return bool
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        return (
            parent::install() &&
            $this->installTab() &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayAdminCustomers')
        );
    }

    /**
     * @return bool
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        $this->removeTab();
        return parent::uninstall();
    }

    /**
     * @return int
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminLoginAsBackend';
        $tab->module = $this->name;
        $tab->id_parent = $this->getTabParent();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Login As Customer';
        }
        return $tab->add();
    }

    /**
     * @return bool
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function removeTab()
    {
        $tabId = Tab::getIdFromClassName('AdminLoginAsBackend');
        if ($tabId) {
            $tab = new Tab($tabId);
            return $tab->delete();
        }
        return true;
    }

    /**
     * @return int
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getTabParent()
    {
        $parent = Tab::getIdFromClassName('AdminCustomers');
        if ($parent !== false) {
            return $parent;
        }
        return 0;
    }

    /**
     * @throws PrestaShopException
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('controller') === 'AdminCustomers' && $this->hasPermission()) {
            Media::addJsDef(['LOGIN_AS_URL' => $this->context->link->getAdminLink('AdminLoginAsBackend')]);
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . '/views/js/customer-list.js');
        }
    }

    /**
     * @param $params
     * @return string
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayAdminCustomers($params)
    {
        if ($this->hasPermission()) {
            $customerId = (int)$params['id_customer'];
            $customer = new Customer($customerId);
            $this->context->smarty->assign([
                'customerName' => $customer->firstname . ' ' . $customer->lastname,
                'loginAsUrl' => $this->context->link->getAdminLink('AdminLoginAsBackend', true) . "&id_customer=$customerId"
            ]);
            return $this->display(__FILE__, 'customer-form.tpl');
        }
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hasPermission()
    {
        if (is_null($this->access)) {
            $access = Profile::getProfileAccess($this->context->employee->id_profile, Tab::getIdFromClassName('AdminLoginAsBackend'));
            $this->access = isset($access['view']) && $access['view'];
        }
        return $this->access;
    }

    /**
     * @param int $customerId
     * @return string
     * @throws HTMLPurifier_Exception
     * @throws PrestaShopException
     */
    public function getSecret($customerId)
    {
        return Tools::encrypt($this->getSalt() . 'LOGIN_AS' . (int)$customerId . date('Ymd'));
    }

    /**
     * @return string
     * @throws HTMLPurifier_Exception
     * @throws PrestaShopException
     */
    private function getSalt()
    {
        if (!Configuration::hasKey(static::SALT)) {
            Configuration::updateValue(static::SALT, Tools::passwdGen(20));
        }
        return Configuration::get(static::SALT);
    }
}
