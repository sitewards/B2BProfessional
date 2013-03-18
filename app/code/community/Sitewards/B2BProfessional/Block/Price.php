<?php
/**
 * Sitewards_B2BProfessional_Block_Price
 * 	- Check that the current product is active and display custom text
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Price extends Mage_Catalog_Block_Product_Price {
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

			// text displayed instead of price
			if (Mage::getStoreConfig('b2bprofessional/languagesettings/languageoverride') == 1) {
				$sReplacementText = Mage::getStoreConfig('b2bprofessional/languagesettings/logintext');
			} else {
				$sReplacementText = $this->__('Please login');
			}
			return $sReplacementText;
		}
		return $sPriceHtml;
	}
}
