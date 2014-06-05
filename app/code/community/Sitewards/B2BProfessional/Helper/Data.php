<?php

/**
 * Sitewards_B2BProfessional_Helper_Data
 *  - Helper containing the checks for
 *      - extension is active,
 *      - product is active,
 *      - is the category active,
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path for the config for extension active status
     */
    const CONFIG_EXTENSION_ACTIVE = 'b2bprofessional/generalsettings/active';

    /**
     * Variable for if the extension is active
     *
     * @var bool
     */
    protected $_isExtensionActive;

    /**
     * Variable for if the extension is active by category
     *
     * @var bool
     */
    protected $_isExtensionActiveByCategory;

    /**
     * Variable for if the extension is active by customer group
     *
     * @var bool
     */
    protected $_isExtensionActiveByCustomerGroup;

    /**
     * Check to see if the extension is active
     *
     * @return bool
     */
    public function isExtensionActive()
    {
        if (is_null($this->_isExtensionActive)) {
            $this->_isExtensionActive = Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_ACTIVE);
        }
        return $this->_isExtensionActive;
    }

    /**
     * Check to see if the extension is active by category
     *
     * @return bool
     */
    protected function isExtensionActiveByCategory()
    {
        if (is_null($this->_isExtensionActiveByCategory)) {
            $this->_isExtensionActiveByCategory = Mage::helper(
                'sitewards_b2bprofessional/category'
            )->isExtensionActivatedByCategory();
        }
        return $this->_isExtensionActiveByCategory;
    }

    /**
     * Check to see if the extension is active by user group
     *
     * @return bool
     */
    protected function isExtensionActivatedByCustomerGroup()
    {
        if (is_null($this->_isExtensionActiveByCustomerGroup)) {
            $this->_isExtensionActiveByCustomerGroup = Mage::helper(
                'sitewards_b2bprofessional/customer'
            )->isExtensionActivatedByCustomerGroup();
        }
        return $this->_isExtensionActiveByCustomerGroup;
    }

    /**
     * Check to see if the given product is active
     *  - In this case active means product behaves as normal in a magento shop
     *
     * @param Mage_Catalog_Model_Product $oProduct
     * @return bool
     */
    public function isProductActive(Mage_Catalog_Model_Product $oProduct)
    {
        $bIsProductActive = true;
        if ($this->isExtensionActive() === true) {
            $bCheckCategory = $this->isExtensionActiveByCategory();
            $bCheckUser = $this->isExtensionActivatedByCustomerGroup();

            /** @var Sitewards_B2BProfessional_Helper_Category $oCategoryHelper */
            $oCategoryHelper = Mage::helper('sitewards_b2bprofessional/category');
            /** @var Sitewards_B2BProfessional_Helper_Customer $oCustomerHelper */
            $oCustomerHelper = Mage::helper('sitewards_b2bprofessional/customer');

            $bIsCategoryEnabled = $oCategoryHelper->isCategoryActiveByProduct($oProduct);
            $bIsCustomerGroupEnabled = $oCustomerHelper->isCustomerGroupActive();

            if ($bCheckCategory && $bCheckUser) {
                $bIsProductActive = !($bIsCategoryEnabled && $bIsCustomerGroupEnabled);
            } else if ($bCheckUser) {
                $bIsProductActive = !$bIsCustomerGroupEnabled;
            } else if ($bCheckCategory) {
                $bIsProductActive = !$bIsCategoryEnabled;
            } else {
                $bIsProductActive = $this->isCustomerLoggedIn();
            }
        }

        return $bIsProductActive;
    }

    /**
     * From an array of category ids check to see if any are enabled via the extension to hide prices
     *
     * @param array<int> $aCategoryIds
     * @return bool
     */
    public function hasEnabledCategories($aCategoryIds)
    {
        $bHasCategories = false;
        if ($this->isExtensionActive() === true) {
            $bCheckCategory = $this->isExtensionActiveByCategory();
            $bCheckUser = $this->isExtensionActivatedByCustomerGroup();

            /** @var Sitewards_B2BProfessional_Helper_Category $oCategoryHelper */
            $oCategoryHelper = Mage::helper('sitewards_b2bprofessional/category');
            /** @var Sitewards_B2BProfessional_Helper_Customer $oCustomerHelper */
            $oCustomerHelper = Mage::helper('sitewards_b2bprofessional/customer');

            $bHasActiveCategories = $oCategoryHelper->hasActiveCategory($aCategoryIds);
            $bIsUserGroupActive = $oCustomerHelper->isCustomerGroupActive();

            if ($bCheckCategory && $bCheckUser) {
                $bHasCategories = $bHasActiveCategories && $bIsUserGroupActive;
            } else if ($bCheckUser) {
                $bHasCategories = $bIsUserGroupActive;
            } else if ($bCheckCategory) {
                $bHasCategories = $bHasActiveCategories;
            } else {
                $bHasCategories = !$this->isCustomerLoggedIn();
            }
        }
        return $bHasCategories;
    }

    /**
     * Check if the customer is logged in
     *
     * @return bool
     */
    protected function isCustomerLoggedIn()
    {
        return Mage::helper('sitewards_b2bprofessional/customer')->isCustomerLoggedIn();
    }
}