<?php
/**
 * Copyright (C) 2017-2018 Petr Hucik <petr@getdatakick.com>
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@getdatakick.com so we can send you a copy immediately.
 *
 * @author    Petr Hucik <petr@getdatakick.com>
 * @copyright 2017-2018 Petr Hucik
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

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
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        return (
            parent::install() &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionAdminCustomersListingResultsModifier') &&
            $this->registerHook('displayAdminCustomers')
        );
    }

    /**
     * @throws PrestaShopException
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('controller') === 'AdminCustomers') {
            Media::addJsDef(['LOGIN_AS_URL' => $this->context->link->getModuleLink($this->name, 'login')]);
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . '/views/js/customer-list.js');
        }
    }

    public function hookActionAdminCustomersListingResultsModifier($params)
    {
        $list = [];
        foreach ($params['list'] as $item) {
            $id = (int)$item['id_customer'];
            $list[$id] = $this->getSecret($id);
        }
        Media::addJsDef(['LOGIN_AS_SECRETS' => $list]);
    }

    /**
     * @param $params
     * @return string
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws HTMLPurifier_Exception
     */
    public function hookDisplayAdminCustomers($params)
    {
        $customerId = (int)$params['id_customer'];
        $customer = new Customer($customerId);
        $this->context->smarty->assign([
            'customerName' => $customer->firstname . ' ' . $customer->lastname,
            'loginAsUrl' => $this->context->link->getModuleLink($this->name, 'login', [
                'id_customer' => $customerId,
                'secret' => $this->getSecret($customerId)
            ], null, $customer->id_lang)
        ]);
        return $this->display(__FILE__, 'customer-form.tpl');
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
