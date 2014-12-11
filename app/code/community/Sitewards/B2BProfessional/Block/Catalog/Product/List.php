<?php

/**
 * Sitewards_B2BProfessional_Block_Catalog_Product_List
 *  - List block which checks if the products can be ordered and if not
 *    replaces the add-to-cart button with the view-details button
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{
    /**
     * Checks if the product can be added to the cart and if not, adds a dummy
     * option in order to replace the add-to-cart button with the view-details button
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getLoadedProductCollection()
    {
        $oProductCollection = parent::getLoadedProductCollection();
        $oB2BHelper         = Mage::helper('sitewards_b2bprofessional');
        $oDummyOption       = Mage::getModel('catalog/product_option');
        foreach ($oProductCollection as $oProduct) {
            if ($oB2BHelper->isProductActive($oProduct) === false) {
                $oProduct->setRequiredOptions(array($oDummyOption));
            }
        }
        return $oProductCollection;
    }
}