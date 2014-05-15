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
     * Check to see if the website is set-up to require a user login to view pages
     *
     * @return boolean
     */
    public function isLoginRequired()
    {
        return Mage::getStoreConfigFlag('b2bprofessional/requirelogin/requirelogin');
    }
}