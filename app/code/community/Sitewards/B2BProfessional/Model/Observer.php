<?php

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
                throw new Mage_Catalog_Exception('Please log in to buy items');
            }
        }
    }

    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $oObserver)
    {
        $oBlock = $oObserver->getData('block');
        $oTransport = $oObserver->getData('transport');

        /** @var Sitewards_B2BProfessional_Helper_Data $oB2BHelper */
        $oB2BHelper = Mage::helper('sitewards_b2bprofessional');
        if ($oB2BHelper->isExtensionActive() === true) {
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

                        $oTransport->setHtml('Please login to see price');
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