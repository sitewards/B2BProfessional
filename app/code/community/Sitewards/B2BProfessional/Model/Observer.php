<?php

class Sitewards_B2BProfessional_Model_Observer
{
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
}