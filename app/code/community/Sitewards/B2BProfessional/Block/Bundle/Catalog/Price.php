<?php
/**
 * Sitewards_B2BProfessional_Block_Price
 * 	- Check that the current product is active and display custom text
 *
 * @author      david.manners <david.manners@sitewards.com>
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Bundle_Catalog_Price extends Mage_Bundle_Block_Catalog_Product_Price {
	/**
	 * The last product Id
	 * 
	 * @var int
	 */
	protected static $_iLastProductId = 0;

	/**
	 * Check that the current product is active
	 * 	- Override the text with the module text
	 * 
	 * @return string
	 */
	protected function _toHtml() {
		$sPriceHtml = parent::_toHtml();
		$iCurrentProductId = $this->getProduct()->getId();

		/* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
		$oB2BHelper = Mage::helper('b2bprofessional');
		if ($oB2BHelper->checkActive($iCurrentProductId)) {
			if ($iCurrentProductId == self::$_iLastProductId) {
				return '';
			}
			self::$_iLastProductId = $iCurrentProductId;

			return $oB2BHelper->getPriceMessage();
		}
		return $sPriceHtml;
	}
}
