<?php
/**
 * Sitewards_B2BProfessional_Helper_Data
 *	- Create functions to,
 *		- Check if user is allowed on store,
 *		- Check if product category is active,
 *		- Check customer group is active,
 *		- Check module global flag,
 *		- Check that customer is logged in and active,
 *		- Check that the product/customer is activated,
 *		- Check that the current cart is valid,
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Data extends Mage_Core_Helper_Abstract {
	/**
	 * Object for the sitewards b2bprofessional customer helper
	 *
	 * @var Sitewards_B2BProfessional_Helper_Customer
	 */
	protected $oB2BCustomerHelper;

	/**
	 * Object for the sitewards b2bprofessional category helper
	 *
	 * @var Sitewards_B2BProfessional_Helper_Category
	 */
	protected $oB2BCategoryHelper;

	/**
	 * Create an instance of the sitewards b2bprofessional customer helper
	 */
	public function __construct() {
		$this->oB2BCustomerHelper = Mage::helper('b2bprofessional/customer');
		$this->oB2BCategoryHelper = Mage::helper('b2bprofessional/category');
	}

	/**
	 * Check to see if the extension is active
	 * Returns the extension's general setting "active"
	 *
	 * @return bool
	 */
	public function isExtensionActive() {
		return Mage::getStoreConfigFlag('b2bprofessional/generalsettings/active');
	}

	/**
	 * Check that the price can be displayed for the given product id
	 *  - Check that the extension is active
	 *  - Check that the customer is allowed in the store
	 *  - When the extension is activated by customer group and category
	 *   - Check that:
	 *    - The category is active by product
	 *    - AND The customer is active
	 *  - When the extension is activated by customer group
	 *   - Check that:
	 *    - The customer is active
	 *  - When the extension is activated by category
	 *   - Check that:
	 *    - The category is active by product
	 *    - AND the user is not logged in
	 *  - Else
	 *   - Check if the user is not logged in
	 *
	 * @param int $iProductId
	 * @return bool
	 */
	public function isProductActive($iProductId) {
		$bIsLoggedIn = false;
		// global extension activation
		if ($this->isExtensionActive()) {
			// check user logged in and has store access
			if ($this->oB2BCustomerHelper->isCustomerAllowedInStore()) {
				$bIsLoggedIn = true;
			}

			$bCheckUser		= $this->oB2BCustomerHelper->isExtensionActivatedByCustomer();
			$bCheckCategory	= $this->oB2BCategoryHelper->isExtensionActivatedByCategory();

			if($bCheckUser == true && $bCheckCategory == true) {
				$bIsActive = $this->oB2BCategoryHelper->isCategoryActiveByProduct($iProductId) && $this->oB2BCustomerHelper->isCustomerActive();
			} elseif($bCheckUser == true) {
				$bIsActive = $this->oB2BCustomerHelper->isCustomerActive();
			} elseif ($bCheckCategory == true) {
				$bIsActive = $this->oB2BCategoryHelper->isCategoryActiveByProduct($iProductId) && !$bIsLoggedIn;
			} else {
				$bIsActive = !$bIsLoggedIn;
			}
		} else {
			$bIsActive = false;
		}
		return $bIsActive;
	}

	/**
	 * Check that the price can be displayed when no product id is given
	 *  - Check that the extension is active
	 *  - Check that the customer is allowed in the store
	 *  - When the extension is activated by customer group and category
	 *   - Check that:
	 *    - The category is active
	 *    - AND The customer is active
	 *  - When the extension is activated by customer group
	 *   - Check that:
	 *    - The customer is active
	 *  - When the extension is activated by category
	 *   - Check that:
	 *    - The category is active
	 *    - AND the user is not logged in
	 *  - Else
	 *   - Check if the user not is logged in
	 *
	 * @return bool
	 */
	public function isActive() {
		$bIsLoggedIn = false;
		// global extension activation
		if ($this->isExtensionActive()) {
			// check user logged in and has store access
			if ($this->oB2BCustomerHelper->isCustomerAllowedInStore()) {
				$bIsLoggedIn = true;
			}

			$bCheckUser		= $this->oB2BCustomerHelper->isExtensionActivatedByCustomer();
			$bCheckCategory	= $this->oB2BCategoryHelper->isExtensionActivatedByCategory();

			if($bCheckUser == true && $bCheckCategory == true) {
				$bIsActive = $this->oB2BCategoryHelper->isCategoryActive() && $this->oB2BCustomerHelper->isCustomerActive();
			} elseif($bCheckUser == true) {
				$bIsActive = $this->oB2BCustomerHelper->isCustomerActive();
			} elseif ($bCheckCategory == true) {
				$bIsActive = $this->oB2BCategoryHelper->isCategoryActive() && !$bIsLoggedIn;
			} else {
				$bIsActive = !$bIsLoggedIn;
			}
		} else {
			$bIsActive = false;
		}
		return $bIsActive;
	}

	/**
	 * Validate that the current quote in the checkout session is valid for the user
	 *  - Check each item in the quote against the function checkActive
	 *
	 * @return bool
	 */
	public function hasValidCart() {
		$bValidCart = true;
		/* @var $oQuote Mage_Sales_Model_Quote */
		$oQuote = Mage::getSingleton('checkout/session')->getQuote();
		foreach($oQuote->getAllItems() as $oItem) {
			/* @var $oItem Mage_Sales_Model_Quote_Item */
			$iProductId = $oItem->getProductId();
			/*
			 * For each item check if it is active for the current user
			 */
			if ($this->isProductActive($iProductId)) {
				$bValidCart = false;
			}
		}
		return $bValidCart;
	}
}