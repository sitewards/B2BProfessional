<?php
/**
 * Sitewards_B2BProfessional_Block_Catalog_Product_Widget_New
 * - rewrite the getAddToCartUrl function to check if the product is active
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Catalog_Product_Widget_New extends Mage_Catalog_Block_Product_Widget_New {
	/**
	 * Check if the product is valid
	 *  - return the replacement text for the add to cart url when required
	 *
	 * @param Mage_Catalog_Model_Product $oProduct
	 * @param array $aAdditional
	 * @return string
	 */
	public function getAddToCartUrl($oProduct, $aAdditional = array()) {
		/* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
		$oB2BHelper = Mage::helper('b2bprofessional');

		if ($oB2BHelper->replaceAddToCart($oProduct->getId())) {
			return $oB2BHelper->getReplaceAddToCartUrl();
		}
		return parent::getAddToCartUrl($oProduct, $aAdditional);
	}
}