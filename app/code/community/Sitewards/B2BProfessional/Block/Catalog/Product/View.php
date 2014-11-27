<?php

/**
 * Sitewards_B2BProfessional_Block_Catalog_Product_View
 *  - Block to check if it's an add-to-cart block and if the output should be hidden
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
    /**
     * Check if it's an add-to-cart block and should not be displayed
     *
     * @return string
     */
    protected function _toHtml()
    {
        $oB2BHelper = Mage::helper('sitewards_b2bprofessional');
        if ($oB2BHelper->isAddToCartBlockAndHidden($this)) {
            return '';
        } else {
            return parent::_toHtml();
        }
    }
}