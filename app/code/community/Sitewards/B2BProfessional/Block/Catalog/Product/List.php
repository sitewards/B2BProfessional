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
     * Fires a custom event we can catch in the observer and process
     * the products where the add-to-cart-button should be hidden
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getLoadedProductCollection()
    {
        $oProductCollection = parent::getLoadedProductCollection();

        Mage::dispatchEvent(
            'sitewards_b2bprofessional_product_list_collection_load_after',
            array(
                'product_collection' => $oProductCollection
            )
        );

        return $oProductCollection;
    }
}