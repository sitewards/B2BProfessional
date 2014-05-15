<?php

/**
 * Sitewards_B2BProfessional_Model_System_Config_Source_Page
 *    - Create an options array with the current system cms pages and the customer login page
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Model_System_Config_Source_Page extends Mage_Adminhtml_Model_System_Config_Source_Cms_Page
{
    /**
     * Populate an options array with the current system cms pages and the customer login page
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            parent::toOptionArray();
            $aNewCmsOption = array(
                'value' => '',
                'label' => Mage::helper('sitewards_b2bprofessional')->__('-- Please Select --')
            );
            $aCustomerLogin = array(
                'value' => 'customer/account/login',
                'label' => Mage::helper('sitewards_b2bprofessional')->__('Customer Login')
            );
            array_unshift($this->_options, $aNewCmsOption, $aCustomerLogin);
        }
        return $this->_options;
    }
}