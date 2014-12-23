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
class Sitewards_B2BProfessional_Helper_Redirects extends Sitewards_B2BProfessional_Helper_Core
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
        $sConfigVar    = Mage::getStoreConfig($sConfigPath);
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
     * @param Mage_Core_Controller_Front_Action $oControllerAction
     * @return bool
     */
    protected function isRedirectRequired($oControllerAction)
    {
        $bIsCmsController        = $this->isCmsController($oControllerAction);
        $bIsFrontController      = $this->isFrontController($oControllerAction);
        $bIsApiController        = $this->isApiController($oControllerAction);
        $bIsCustomerController   = $this->isCustomerController($oControllerAction);
        $bIsXmlConnectController = $this->isXmlConnectController($oControllerAction);

        return !$bIsCmsController && $bIsFrontController && !$bIsCustomerController
            && !$bIsApiController && !$bIsXmlConnectController;
    }

    /**
     * Check to see if the controller is a customer controller
     *
     * @param Mage_Core_Controller_Front_Action $oControllerAction
     * @return bool
     */
    protected function isCustomerController($oControllerAction)
    {
        return $oControllerAction instanceof Mage_Customer_AccountController;
    }

    /**
     * Check to see if the controller is an api controller
     *
     * @param Mage_Core_Controller_Front_Action $oControllerAction
     * @return bool
     */
    protected function isApiController($oControllerAction)
    {
        return $oControllerAction instanceof Mage_Api_Controller_Action;
    }

    /**
     * Check to see if the controller is a front controller
     *
     * @param Mage_Core_Controller_Front_Action $oControllerAction
     * @return bool
     */
    protected function isFrontController($oControllerAction)
    {
        return $oControllerAction instanceof Mage_Core_Controller_Front_Action;
    }

    /**
     * Check to see if the controller is a cms controller
     *
     * @param Mage_Core_Controller_Front_Action $oControllerAction
     * @return bool
     */
    protected function isCmsController($oControllerAction)
    {
        return $oControllerAction instanceof Mage_Cms_IndexController
        || $oControllerAction instanceof Mage_Cms_PageController;
    }

    /**
     * Check to see if the controller is an xml-connect controller
     *
     * @param Mage_Core_Controller_Front_Action $oControllerAction
     * @return bool
     */
    protected function isXmlConnectController($oControllerAction)
    {
        return $oControllerAction instanceof Mage_XmlConnect_ConfigurationController;
    }

    /**
     * Set-up the redirect to the customer login page
     *
     * @param Mage_Core_Controller_Front_Action $oControllerAction
     */
    public function performRedirect($oControllerAction)
    {
        if ($this->isRedirectRequired($oControllerAction)) {
            /* @var $oResponse Mage_Core_Controller_Response_Http */
            $oResponse = $oControllerAction->getResponse();
            $oResponse->setRedirect(
                $this->getRedirect(
                    self::REDIRECT_TYPE_LOGIN
                )
            );

            /*
             * Add message to the session
             *  - Note:
             *      We need session_write_close otherwise the messages get lots in redirect
             */
            /* @var $oSession Mage_Core_Model_Session */
            $oSession = Mage::getSingleton('core/session');
            $oSession->addUniqueMessages(
                Mage::getSingleton('core/message')->notice(
                    Mage::helper('sitewards_b2bprofessional')->getLoginMessage()
                )
            );
            session_write_close();
        }
    }
}