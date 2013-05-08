<?php
/**
 * Sitewards_B2BProfessional_Helper_Catalog_Product_Compare
 * - rewrite the getAddToCartUrl function to check if the product is active
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Catalog_Product_Compare extends Mage_Catalog_Helper_Product_Compare {
	/**
	 * Check if the product is valid
	 *  - return the replacement text for the add to cart url when required
	 *
	 * @param Mage_Catalog_Model_Product $oProduct
	 * @return string
	 */
	public function getAddToCartUrl($oProduct) {
		/* @var $oB2BReplacementsHelper Sitewards_B2BProfessional_Helper_Replacements */
		$oB2BReplacementsHelper = Mage::helper('b2bprofessional/replacements');

		if ($oB2BReplacementsHelper->replaceAddToCart($oProduct->getId())) {
			return $oB2BReplacementsHelper->getReplaceAddToCartUrl();
		}
		return parent::getAddToCartUrl($oProduct);
	}
}