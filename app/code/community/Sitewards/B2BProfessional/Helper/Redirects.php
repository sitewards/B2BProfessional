<?php

/**
 * Class Sitewards_B2BProfessional_Helper_Redirects
 *  - contains each redirect type config path as constant
 *  - function that takes in a config path and returns a url
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Redirects extends Mage_Core_Helper_Abstract
{
    /**
     * String containing the configuration path for the login redirect
     */
    const REDIRECT_TYPE_LOGIN = 'b2bprofessional/requirelogin/requireloginredirect';

    /**
     * The getRedirect function will for a given redirect configuration path
     *  - load the value for the given configuration path
     *  - return a formatted url based on this value
     *
     * @param string $sConfigPath
     * @return string
     */
    public function getRedirect($sConfigPath)
    {
        $sRedirectPath = '/';
        $sConfigVar = Mage::getStoreConfig($sConfigPath);
        if (isset($sConfigVar)) {
            $sRedirectPath = $sConfigVar;
        }
        return Mage::getUrl($sRedirectPath);
    }

    /**
     * From a given controller check to see if we need to redirect the user
     *  - Cms related for cms pages,
     *  - A front action to allow for admin pages,
     *  - Customer account to allow for login
     *  - Magento API to allow api requests
     *
     * @param $oControllerAction
     * @return bool
     */
    public function isRedirectRequired($oControllerAction)
    {
        return !$oControllerAction instanceof Mage_Cms_IndexController
        && !$oControllerAction instanceof Mage_Cms_PageController
        && $oControllerAction instanceof Mage_Core_Controller_Front_Action
        && !$oControllerAction instanceof Mage_Customer_AccountController
        && !$oControllerAction instanceof Mage_Api_Controller_Action;
    }
}