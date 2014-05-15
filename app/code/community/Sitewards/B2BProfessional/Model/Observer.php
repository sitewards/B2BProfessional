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
    protected static $_iLastProductId = 0;

    /**
     * blocks which display prices
     *
     * @var array
     */
    protected $_aPriceBlockClassNames = array(
        'Mage_Catalog_Block_Product_Price' => 1,
        'Mage_Bundle_Block_Catalog_Product_Price' => 1,
    );

    /**
     * Check to see if a product can be sold to the current logged in user
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

            $oSalable->setIsSalable($oB2BHelper->isProductActive($oProduct));
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
                throw new Mage_Catalog_Exception($oB2BHelper->__('Please login'));
            }
        }
    }

    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $oObserver)
    {
        /** @var Sitewards_B2BProfessional_Helper_Data $oB2BHelper */
        $oB2BHelper = Mage::helper('sitewards_b2bprofessional');
        if ($oB2BHelper->isExtensionActive() === true) {
            $oBlock = $oObserver->getData('block');
            $oTransport = $oObserver->getData('transport');

            /*
             * Check to see if we should remove the product price
             */
            if ($this->isExactlyPriceBlock($oBlock)) {
                $oProduct = $oBlock->getProduct();
                $iCurrentProductId = $oProduct->getId();

                if ($oB2BHelper->isProductActive($oProduct) === false) {
                    // To stop duplicate information being displayed validate that we only do this once per product
                    if ($iCurrentProductId !== self::$_iLastProductId) {
                        self::$_iLastProductId = $iCurrentProductId;

                        $oTransport->setHtml($oB2BHelper->__('Please login'));
                    } else {
                        $oTransport->setHtml('');
                    }

                    // Set type id to combined to stop tax being displayed via Symmetrics_TweaksGerman_Block_Tax
                    if (
                        Mage::helper('core')->isModuleEnabled('Symmetrics_TweaksGerman')
                        && $oProduct->getTypeId() == 'bundle'
                    ) {
                        $oProduct->setTypeId('combined');
                    }
                }
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
            if ($oB2BCustomerHelper->isLoginRequired() == true && !$oB2BCustomerHelper->isCustomerActive()) {
                $oB2BRedirectsHelper = Mage::helper('sitewards_b2bprofessional/redirects');

                if ($oB2BRedirectsHelper->isRedirectRequired($oControllerAction)) {
                    /* @var $oResponse Mage_Core_Controller_Response_Http */
                    $oResponse = $oControllerAction->getResponse();
                    $oResponse->setRedirect(
                        $oB2BRedirectsHelper->getRedirect(
                            $oB2BRedirectsHelper::REDIRECT_TYPE_LOGIN
                        )
                    );

                    /*
                     * Add message to the session
                     *  - Note:
                     *      We need session_write_close otherwise the messages get lots in redirect
                     */
                    /* @var $oSession Mage_Core_Model_Session */
                    $oSession = Mage::getSingleton('core/session');
                    $oSession->addNotice($oB2BHelper->__('Please login'));
                    session_write_close();
                }
            }
        }
    }

    /**
     * Remove the price option from the order by options
     *  - TODO: we should try to check if it is possible to only remove this if not needed
     * 
     * @param Varien_Event_Observer $oObserver
     */
    public function coreBlockAbstractToHtmlBefore(Varien_Event_Observer $oObserver)
    {
        /** @var Sitewards_B2BProfessional_Helper_Data $oB2BHelper */
        $oB2BHelper = Mage::helper('sitewards_b2bprofessional');
        if ($oB2BHelper->isExtensionActive() === true) {
            $oBlock = $oObserver->getData('block');

            // TODO: we should check if we should remove the price not just remove it always
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
                /* @var $oCategoryFilter Mage_Catalog_Block_Layer_Filter_Category */
                $oCategoryFilter = $oBlock->getChild('category_filter');
                $aCategoryOptions = array();
                if ($oCategoryFilter instanceof Mage_Catalog_Block_Layer_Filter_Category) {
                    $oCategories = $oCategoryFilter->getItems();
                    foreach ($oCategories as $oCategory) {
                        /* @var $oCategory Mage_Catalog_Model_Layer_Filter_Item */
                        $iCategoryId = $oCategory->getValue();
                        $aCategoryOptions[] = $iCategoryId;
                    }

                    if (empty($aCategoryOptions)) {
                        /* @var $oCategory Mage_Catalog_Model_Category */
                        $oCategory = Mage::registry('current_category_filter');
                        if (is_null($oCategory)) {
                            $oCategory = Mage::registry('current_category');
                            if (is_null($oCategory)) {
                                $oCategory = Mage::getModel('catalog/category')->load(
                                    Mage::app()->getStore()->getRootCategoryId()
                                );
                            }
                        }
                        $aCategoryOptions[] = $oCategory->getId();
                    }
                }

                if ($oB2BHelper->hasActiveCategories($aCategoryOptions)) {
                    $aFilterableAttributes = $oBlock->getData('_filterable_attributes');
                    $aNewFilterableAttributes = array();
                    foreach ($aFilterableAttributes as $oFilterableAttribute) {
                        if ($oFilterableAttribute->getAttributeCode() != 'price') {
                            $aNewFilterableAttributes[] = $oFilterableAttribute;
                        }
                    }
                    $oBlock->setData('_filterable_attributes', $aNewFilterableAttributes);
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
        return ($oBlock && isset($this->_aPriceBlockClassNames[get_class($oBlock)]));
    }
}