<?php
/**
 * Sitewards_B2BProfessional_Block_Configurable
 * 	- Check that the current product is active and override the price
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable {
	/**
	 * Check that the product is active
	 * 	- If it is not active the override the price
	 * 
	 * @param float $fProductPrice
	 * @param boolean $bIsPercent
	 * @return boolean
	 */
	protected function _preparePrice($fProductPrice, $bIsPercent = false) {
		if (Mage::helper('b2bprofessional')->checkActive()) {
			return false;
		}
		return parent::_preparePrice($fProductPrice, $bIsPercent);
	}
}