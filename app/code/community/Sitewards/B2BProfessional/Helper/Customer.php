<?php

/**
 * Sitewards_B2BProfessional_Helper_Customer
 *  - Helper containing the checks for
 *      - customer is active
 *      - if login is required
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Customer extends Mage_Core_Helper_Abstract
{
    /**
     * Path for the config for extension requires login
     */
    const CONFIG_EXTENSION_REQUIRES_LOGIN = 'b2bprofessional/requirelogin/requirelogin';

    /**
     * Path for the config for extension is active by customer group
     */
    const CONFIG_EXTENSION_ACTIVE_BY_CUSTOMER_GROUP = 'b2bprofessional/activatebycustomersettings/activebycustomer';

    /**
     * Path for the config for customer groups that are activated
     */
    const CONFIG_EXTENSION_ACTIVE_CUSTOMER_GROUPS = 'b2bprofessional/activatebycustomersettings/activecustomers';

    /**
     * Array of activated customer group ids
     *
     * @var array<int>
     */
    protected $_aActivatedCustomerGroupIds = array();

    /**
     * Check to see if the customer is active
     *  - If customer is not logged in than they are not active
     *
     * @return bool
     */
    public function isCustomerActive()
    {
        $bIsCustomerActive = true;
        if (Mage::helper('customer')->isLoggedIn() === false) {
            $bIsCustomerActive = false;
        }

        return $bIsCustomerActive;
    }

    /**
     * Check to see if the customer's group is active
     *
     * @return bool
     */
    public function isCustomerGroupActive()
    {
        /* @var $oCustomerSession Mage_Customer_Model_Session */
        $oCustomerSession = Mage::getModel('customer/session');
        $iCurrentCustomerGroupId = $oCustomerSession->getCustomerGroupId();
        $aActiveCustomerGroupIds = $this->getActivatedCustomerGroupIds();

        return in_array($iCurrentCustomerGroupId, $aActiveCustomerGroupIds);
    }

    /**
     * Check to see if the website is set-up to require a user login to view pages
     *
     * @return bool
     */
    public function isLoginRequired()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_REQUIRES_LOGIN);
    }

    /**
     * Check to see if the extension is activated by customer group
     *
     * @return bool
     */
    public function isExtensionActivatedByCustomerGroup()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_ACTIVE_BY_CUSTOMER_GROUP);
    }

    /**
     * Get an array of all the activated customer group ids
     *  - always include the 'NOT LOGGED IN' group
     *
     * @return array<int>
     */
    private function getActivatedCustomerGroupIds()
    {
        if (empty($this->_aActivatedCustomerGroupIds)) {
            /*
             * Customer group ids are saved in the config in format
             *  - "group1,group2"
             */
            $sActivatedCustomerGroups = Mage::getStoreConfig(self::CONFIG_EXTENSION_ACTIVE_CUSTOMER_GROUPS);
            $this->_aActivatedCustomerGroupIds = explode(',', $sActivatedCustomerGroups);

            /*
             * Always add the guest user group id when activated by customer group
             */
            $this->_aActivatedCustomerGroupIds[] = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        }
        return $this->_aActivatedCustomerGroupIds;
    }
}