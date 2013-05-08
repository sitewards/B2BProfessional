<?php
/**
 * Sitewards_B2BProfessional_Block_Checkout_Links
 *	- Override the addCheckoutLink to validate the current cart
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Checkout_Links extends Mage_Checkout_Block_Links {
	/**
	 * Validate the user's cart
	 *  - when inactive remove the link
	 *  - when active continue to the parent function
	 *
	 * @return Mage_Checkout_Block_Links
	 */
	public function addCheckoutLink() {
		/* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
		$oB2BHelper = Mage::helper('b2bprofessional');

		if (!$oB2BHelper->hasValidCart()) {
			return $this;
		} else {
			return parent::addCheckoutLink();
		}
	}
}