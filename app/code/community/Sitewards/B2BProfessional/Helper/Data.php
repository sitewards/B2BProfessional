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
class Sitewards_B2BProfessional_Helper_Data extends Sitewards_B2BProfessional_Helper_Core
{
    /**
     * Path for the config for extension active status
     */
    const CONFIG_EXTENSION_ACTIVE = 'b2bprofessional/generalsettings/active';

    /**
     * Path for the config for price block class names
     */
    const CONFIG_EXTENSION_PRICE_BLOCKS = 'b2bprofessional/generalsettings/priceblocks';

    /**
     * Variable for if the extension is active
     *
     * @var bool
     */
    protected $isExtensionActive;

    /**
     * Variable for if the extension is active by category
     *
     * @var bool
     */
    protected $isExtensionActiveByCategory;

    /**
     * Variable for if the extension is active by customer group
     *
     * @var bool
     */
    protected $isExtensionActiveByCustomerGroup;

    /**
     * Variable for the extension's price blocks
     *
     * @var string[]
     */
    protected $aPriceBlockClassNames;

    /**
     * Check to see if the extension is active
     *
     * @return bool
     */
    public function isExtensionActive()
    {
        return $this->getStoreFlag(self::CONFIG_EXTENSION_ACTIVE, 'isExtensionActive');
    }

    /**
     * Check to see if the extension is active by category
     *
     * @return bool
     */
    protected function isExtensionActiveByCategory()
    {
        if ($this->isExtensionActiveByCategory === null) {
            $this->isExtensionActiveByCategory = Mage::helper(
                'sitewards_b2bprofessional/category'
            )->isExtensionActivatedByCategory();
        }
        return $this->isExtensionActiveByCategory;
    }

    /**
     * Check to see if the extension is active by user group
     *
     * @return bool
     */
    protected function isExtensionActivatedByCustomerGroup()
    {
        if ($this->isExtensionActiveByCustomerGroup === null) {
            $this->isExtensionActiveByCustomerGroup = Mage::helper(
                'sitewards_b2bprofessional/customer'
            )->isExtensionActivatedByCustomerGroup();
        }
        return $this->isExtensionActiveByCustomerGroup;
    }

    /**
     * Check to see if the block is a price block
     *
     * @param Mage_Core_Block_Template $oBlock
     * @return bool
     */
    public function isBlockPriceBlock($oBlock)
    {
        $aPriceBlockClassNames = $this->getPriceBlocks();
        return in_array(get_class($oBlock), $aPriceBlockClassNames);
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
            $bCheckUser     = $this->isExtensionActivatedByCustomerGroup();

            /** @var Sitewards_B2BProfessional_Helper_Category $oCategoryHelper */
            $oCategoryHelper = Mage::helper('sitewards_b2bprofessional/category');
            /** @var Sitewards_B2BProfessional_Helper_Customer $oCustomerHelper */
            $oCustomerHelper = Mage::helper('sitewards_b2bprofessional/customer');

            $bIsCategoryEnabled      = $oCategoryHelper->isCategoryActiveByProduct($oProduct);
            $bIsCustomerGroupEnabled = $oCustomerHelper->isCustomerGroupActive();

            if ($bCheckCategory && $bCheckUser) {
                $bIsProductActive = !($bIsCategoryEnabled && $bIsCustomerGroupEnabled);
            } elseif ($bCheckUser) {
                $bIsProductActive = !$bIsCustomerGroupEnabled;
            } elseif ($bCheckCategory) {
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
     * @param int[] $aCategoryIds
     * @return bool
     */
    public function hasEnabledCategories($aCategoryIds)
    {
        $bHasCategories = false;
        if ($this->isExtensionActive() === true) {
            $bCheckCategory = $this->isExtensionActiveByCategory();
            $bCheckUser     = $this->isExtensionActivatedByCustomerGroup();

            /** @var Sitewards_B2BProfessional_Helper_Category $oCategoryHelper */
            $oCategoryHelper = Mage::helper('sitewards_b2bprofessional/category');
            /** @var Sitewards_B2BProfessional_Helper_Customer $oCustomerHelper */
            $oCustomerHelper = Mage::helper('sitewards_b2bprofessional/customer');

            $bHasActiveCategories = $oCategoryHelper->hasActiveCategory($aCategoryIds);
            $bIsUserGroupActive   = $oCustomerHelper->isCustomerGroupActive();

            if ($bCheckCategory && $bCheckUser) {
                $bHasCategories = $bHasActiveCategories && $bIsUserGroupActive;
            } elseif ($bCheckUser) {
                $bHasCategories = $bIsUserGroupActive;
            } elseif ($bCheckCategory) {
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

    /**
     * Get the price blocks as defined in the xml
     *
     * @return string[]
     */
    protected function getPriceBlocks()
    {
        if ($this->aPriceBlockClassNames === null) {
            $this->aPriceBlockClassNames = Mage::getStoreConfig(self::CONFIG_EXTENSION_PRICE_BLOCKS);
        }
        return $this->aPriceBlockClassNames;
    }
}