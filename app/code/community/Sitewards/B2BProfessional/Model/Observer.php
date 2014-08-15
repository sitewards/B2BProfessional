<?php

/**
 * Sitewards_B2BProfessional_Model_Observer
 *  - Observer containing the following event methods
 *      - catalog_product_is_salable_after - remove add to cart buttons,
 *      - catalog_product_type_prepare_full_options - stop product being added to cart via url
 *      - core_block_abstract_to_html_after - remove price from product pages
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Model_Observer
{
    /**
     * The last product Id
     *
     * @var int
     */
    protected static $iLastProductId = 0;

    /**
     * Check to see if a product can be sold to the current logged in user
     *  - if the flag of salable is already false then we should do nothing
     *
     * @param Varien_Event_Observer $oObserver
     */
    public function catalogProductIsSalableAfter(Varien_Event_Observer $oObserver)
    {
        /** @var Sitewards_B2BProfessional_Helper_Data $oB2BHelper */
        $oB2BHelper = Mage::helper('sitewards_b2bprofessional');
        if ($oB2BHelper->isExtensionActive() === true) {
            $oProduct = $oObserver->getEvent()->getProduct();
            $oSalable = $oObserver->getEvent()->getSalable();

            if ($oSalable->getIsSalable() == true) {
                $oSalable->setIsSalable($oB2BHelper->isProductActive($oProduct));
                $oProduct->setData('is_salable', $oB2BHelper->isProductActive($oProduct));
            }
        }
    }

    /**
     * Check to see if the product being added to the cart can be bought
     *
     * @param Varien_Event_Observer $oObserver
     * @throws Mage_Catalog_Exception
     */
    public function catalogProductTypePrepareFullOptions(Varien_Event_Observer $oObserver)
    {
        /** @var Sitewards_B2BProfessional_Helper_Data $oB2BHelper */
        $oB2BHelper = Mage::helper('sitewards_b2bprofessional');
        if ($oB2BHelper->isExtensionActive() === true) {
            $oProduct = $oObserver->getEvent()->getProduct();

            if ($oB2BHelper->isProductActive($oProduct) === false) {
                throw new Mage_Catalog_Exception($oB2BHelper->__('Your account is not allowed to access this store.'));
            }
        }
    }

    /**
     * Replace the price information with the desired message
     *
     * @param Varien_Event_Observer $oObserver
     */
    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $oObserver)
    {
        /** @var Sitewards_B2BProfessional_Helper_Data $oB2BHelper */
        $oB2BHelper = Mage::helper('sitewards_b2bprofessional');
        if ($oB2BHelper->isExtensionActive() === true) {
            $oBlock     = $oObserver->getData('block');
            $oTransport = $oObserver->getData('transport');

            /*
             * Check to see if we should remove the product price
             */
            if ($this->isExactlyPriceBlock($oBlock)) {
                $this->transformPriceBlock($oBlock, $oTransport);
            }
        }
    }

    /**
     * Check to see if the user will need to be redirected to the login page or another saved via the admin
     *
     * @param Varien_Event_Observer $oObserver
     */
    public function controllerActionPredispatch(Varien_Event_Observer $oObserver)
    {
        /** @var Sitewards_B2BProfessional_Helper_Data $oB2BHelper */
        $oB2BHelper = Mage::helper('sitewards_b2bprofessional');
        if ($oB2BHelper->isExtensionActive() === true) {
            /* @var $oControllerAction Mage_Core_Controller_Front_Action */
            $oControllerAction = $oObserver->getData('controller_action');

            /* @var $oB2BCustomerHelper Sitewards_B2BProfessional_Helper_Customer */
            $oB2BCustomerHelper = Mage::helper('sitewards_b2bprofessional/customer');

            /*
             * Check to see if the system requires a login
             * And there is no logged in user
             */
            if ($oB2BCustomerHelper->isLoginRequired() == true && !$oB2BCustomerHelper->isCustomerLoggedIn()) {
                Mage::helper('sitewards_b2bprofessional/redirects')->performRedirect($oControllerAction);
            }
        }
    }

    /**
     * Remove the price option from the order by options
     *
     * @param Varien_Event_Observer $oObserver
     */
    public function coreBlockAbstractToHtmlBefore(Varien_Event_Observer $oObserver)
    {
        /** @var Sitewards_B2BProfessional_Helper_Data $oB2BHelper */
        $oB2BHelper = Mage::helper('sitewards_b2bprofessional');
        if ($oB2BHelper->isExtensionActive() === true) {
            $oBlock = $oObserver->getData('block');

            if ($oBlock instanceof Mage_Catalog_Block_Product_List_Toolbar) {
                $oBlock->removeOrderFromAvailableOrders('price');
            }
        }
    }

    /**
     * If we have a Mage_Catalog_Block_Layer_View
     *     - remove the price attribute
     *
     * @param Varien_Event_Observer $oObserver
     */
    public function coreLayoutBlockCreateAfter(Varien_Event_Observer $oObserver)
    {
        /* @var Sitewards_B2BProfessional_Helper_Data $oB2BHelper */
        $oB2BHelper = Mage::helper('sitewards_b2bprofessional');
        if ($oB2BHelper->isExtensionActive()) {
            $oBlock = $oObserver->getData('block');
            if ($oBlock instanceof Mage_Catalog_Block_Layer_View) {
                $aCategoryOptions = $this->getCategoryFilters($oBlock);

                if ($oB2BHelper->hasEnabledCategories($aCategoryOptions)) {
                    $this->removePriceFilter($oBlock);
                }
            }
        }
    }

    /**
     * checks if the block represents a price block
     *
     * @param Mage_Core_Block_Abstract $oBlock
     * @return bool
     */
    protected function isExactlyPriceBlock($oBlock)
    {
        return $oBlock
            && Mage::helper('sitewards_b2bprofessional')->isBlockPriceBlock($oBlock);
    }

    /**
     * From a block get all the category filters when set
     *
     * @param Mage_Catalog_Block_Layer_View $oBlock
     * @return int[]
     */
    protected function getCategoryFilters($oBlock)
    {
        /* @var $oCategoryFilter Mage_Catalog_Block_Layer_Filter_Category */
        $oCategoryFilter  = $oBlock->getChild('category_filter');
        $aCategoryOptions = array();
        if ($oCategoryFilter instanceof Mage_Catalog_Block_Layer_Filter_Category) {
            $oCategories = $oCategoryFilter->getItems();
            foreach ($oCategories as $oCategory) {
                /* @var $oCategory Mage_Catalog_Model_Layer_Filter_Item */
                $iCategoryId        = $oCategory->getValue();
                $aCategoryOptions[] = $iCategoryId;
            }

            if (empty($aCategoryOptions)) {
                return $this->getDefaultCategoryOptions();
            }
        }
        return $aCategoryOptions;
    }

    /**
     * Return the default category,
     *  - either from the filter,
     *  - the current category,
     *  - or the root category
     *
     * @return int[]
     */
    protected function getDefaultCategoryOptions()
    {
        $aCategoryOptions = array();

        /* @var $oCategory Mage_Catalog_Model_Category */
        $oCategory = Mage::registry('current_category_filter');
        if ($oCategory === null) {
            $oCategory = Mage::registry('current_category');
            if ($oCategory === null) {
                $oCategory = Mage::getModel('catalog/category')->load(
                    Mage::app()->getStore()->getRootCategoryId()
                );
            }
        }
        $aCategoryOptions[] = $oCategory->getId();
        return $aCategoryOptions;
    }

    /**
     * Remove the price filter options from the filterable attributes
     *
     * @param Mage_Catalog_Block_Layer_View $oBlock
     */
    protected function removePriceFilter($oBlock)
    {
        $aFilterableAttributes    = $oBlock->getData('_filterable_attributes');
        $aNewFilterableAttributes = array();
        foreach ($aFilterableAttributes as $oFilterableAttribute) {
            if ($oFilterableAttribute->getAttributeCode() != 'price') {
                $aNewFilterableAttributes[] = $oFilterableAttribute;
            }
        }
        $oBlock->setData('_filterable_attributes', $aNewFilterableAttributes);
    }

    /**
     * Set type id to combined to stop tax being displayed via Symmetrics_TweaksGerman_Block_Tax
     *
     * @param Mage_Catalog_Model_Product $oProduct
     */
    protected function setSymmetricsProductType(Mage_Catalog_Model_Product $oProduct)
    {
        if (
            Mage::helper('core')->isModuleEnabled('Symmetrics_TweaksGerman')
            && $oProduct->getTypeId() == 'bundle'
        ) {
            $oProduct->setTypeId('combined');
        }
    }

    /**
     * For a given product price block check if the product is active
     *  - if it is active then set the price html as the default message
     *
     * @param Mage_Catalog_Block_Product_Price $oBlock
     * @param Varien_Object $oTransport
     */
    protected function transformPriceBlock($oBlock, $oTransport)
    {
        /** @var Sitewards_B2BProfessional_Helper_Data $oB2BHelper */
        $oB2BHelper        = Mage::helper('sitewards_b2bprofessional');
        $oProduct          = $oBlock->getProduct();
        $iCurrentProductId = $oProduct->getId();

        if ($oB2BHelper->isProductActive($oProduct) === false) {
            // To stop duplicate information being displayed validate that we only do this once per product
            if ($iCurrentProductId !== self::$iLastProductId) {
                self::$iLastProductId = $iCurrentProductId;
                $oTransport->setHtml($oB2BHelper->__('Please login'));
            } else {
                $oTransport->setHtml('');
            }
            $this->setSymmetricsProductType($oProduct);
        }
    }
}