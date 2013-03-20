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
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Data extends Mage_Core_Helper_Abstract {
	/**
	 * Check to see if the website is set-up to require a user login to view pages
	 *
	 * @return boolean
	 */
	public function checkRequireLogin() {
		return Mage::getStoreConfigFlag('b2bprofessional/generalsettings/requirelogin');
	}

	/**
	 * Check to see if the user is allowed on the current store
	 * 
	 * @return boolean
	 */
	public function checkAllowed() {
		// if option is not active return true!
		if (Mage::getStoreConfig('b2bprofessional/generalsettings/activecustomers')) {
			return true;
		}

		$bCreatedViaAdmin = false;
		$oCustomer = Mage::getSingleton('customer/session')->getCustomer();
		if($oCustomer->getCreatedIn() == 'Admin') {
			$bCreatedViaAdmin = true;
		}
		$iUserStoreId		= $oCustomer->getStoreId();
		$iCurrentStoreId	= Mage::app()->getStore()->getId();

		if ($iUserStoreId == $iCurrentStoreId || $bCreatedViaAdmin == true) {
			return true;
		}
	}

	/**
	 * Validate that the category of a give product is activated in the module
	 * 
	 * @param int $iProductId
	 * @return boolean
	 */
	public function checkCategoryIsActive($iProductId = null) {
		$aCurrentCategories = array ();

		// activate by category
		if ($iProductId !== null) {
			/* @var $oProduct Mage_Catalog_Model_Product */
			$oProduct = Mage::getModel('catalog/product')->load($iProductId);
			$aParentProductIds = $oProduct->loadParentProductIds()->getData('parent_product_ids');
			if (!empty($aParentProductIds) && $oProduct->isGrouped()) {
				foreach ($aParentProductIds as $iParentProductId) {
					/* @var $oParentProduct Mage_Catalog_Model_Product */
					$oParentProduct = Mage::getModel('catalog/product')->load($iParentProductId);
					$aParentProductCategories = $oParentProduct->getCategoryIds();
					$aCurrentCategories = array_merge($aCurrentCategories, $aParentProductCategories);
				}
			} else {
				$aCurrentCategories = $oProduct->getCategoryIds();
			}
		} else {
			$aCurrentCategories = array(Mage::getModel('catalog/product')->getCategoryId());
		}
		$aCurrentCategories = array_unique($aCurrentCategories);

		$aActiveCategories = $this->getActiveCategories();
		if (!is_array($aCurrentCategories)) {
			$aCurrentCategories = array (
				$aCurrentCategories
			);
		}
		$bActive = false;
		foreach ($aCurrentCategories as $iCategoryId) {
			if (in_array($iCategoryId, $aActiveCategories)) {
				$bActive = true;
			}
		}
		return $bActive;
	}

	/**
	 * Check that the current customer has an active group id
	 * 
	 * @return boolean
	 */
	public function checkCustomerIsActive() {
		// activate by customer group
		/* @var $oCustomerSession Mage_Customer_Model_Session */
		$oCustomerSession = Mage::getModel('customer/session');
		$iCurrentCustomerGroupId = $oCustomerSession->getCustomerGroupId();
		$aActiveCustomerGroupIds = explode(',', Mage::getStoreConfig('b2bprofessional/activatebycustomersettings/activecustomers'));

		/*
		 * Always add the guest user when activated by customer group
		 * Note: the the group code for the guest user can not be changed via the admin section 
		 */
		$iGuestGroupId = Mage::getModel('customer/group')->load('NOT LOGGED IN', 'customer_group_code')->getId();
		$aActiveCustomerGroupIds[] = $iGuestGroupId;

		if (in_array($iCurrentCustomerGroupId, $aActiveCustomerGroupIds)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check the global active flag
	 * 
	 * @return boolean
	 */
	public function checkGlobalActive() {
		if (Mage::getStoreConfig('b2bprofessional/generalsettings/active') == 1) {
			return true;
		}
	}

	/**
	 * Check that a customer is logged in,
	 * 	- If they are logged in validate their account usint the checkAllowed function
	 * 
	 * @return boolean
	 */
	public function checkLoggedIn() {
		$bLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();

		if (!$this->checkAllowed()) {
			$bLoggedIn = false;
		}
		return $bLoggedIn;
	}

	/**
	 * Check that the product/customer is activated
	 * 
	 * @param int $iProductId
	 * @return boolean
	 */
	public function checkActive($iProductId = null) {
		$bIsLoggedIn = false;
		// global extension activation
		if ($this->checkGlobalActive()) {
			// check user logged in and has store access
			if ($this->checkLoggedIn()) {
				$bIsLoggedIn = true;
			}

			$bCheckUser = Mage::getStoreConfigFlag('b2bprofessional/activatebycustomersettings/activebycustomer');
			$bCheckCategory = Mage::getStoreConfigFlag('b2bprofessional/activatebycategorysettings/activebycategory');

			if($bCheckUser == true && $bCheckCategory == true) {
				// check both the category and customer group is active via the extension
				if ($this->checkCategoryIsActive($iProductId) && $this->checkCustomerIsActive()) {
					$bIsActive = true;
				} else {
					$bIsActive = false;
				}
			} elseif($bCheckUser == true) {
				// check user group is active via the extension
				if ($this->checkCustomerIsActive()) {
					$bIsActive = true;
				} else {
					$bIsActive = false;
				}
			} elseif ($bCheckCategory == true) {
				// check category is active via the extension
				if (!$this->checkCategoryIsActive($iProductId) || $bIsLoggedIn == true) {
					$bIsActive = false;
				} else {
					$bIsActive = true;
				}
			} else {
				if ($bIsLoggedIn == false) {
					$bIsActive = true;
				} else {
					$bIsActive = false;
				}
			}
		} else {
			$bIsActive = false;
		}
		return $bIsActive;
	}

	/**
	 * Get all active categories
	 * 	- allow for sub categories also
	 * @param boolean $bIncludeSubCategories
	 * @return array
	 * 	array(
	 * 		cat_id_1,
	 * 		cat_id_2
	 * 	)
	 */
	public function getActiveCategories($bIncludeSubCategories = true) {
		$aCurrentActiveCategories = explode(',', Mage::getStoreConfig('b2bprofessional/activatebycategorysettings/activecategories'));
		if($bIncludeSubCategories == false) {
			return $aCurrentActiveCategories;
		}

		$aSubActiveCategories = array();
		foreach ($aCurrentActiveCategories as $iCategoryId) {
			$aSubActiveCategories = $this->addCategoryChildren($iCategoryId, $aSubActiveCategories);
		}
		return array_unique($aSubActiveCategories);
	}

	/**
	 * From given category id load all child ids into an array
	 * 
	 * @param int $iCategoryId
	 * @param array $aCurrentCategories
	 * 	array(
	 * 		cat_id_1,
	 * 		cat_id_2
	 * 	)
	 * @return array
	 * 	array(
	 * 		cat_id_1,
	 * 		cat_id_2
	 * 	)
	 */
	public function addCategoryChildren($iCategoryId, $aCurrentCategories = array()) {
		/* @var $oCurrentCategory Mage_Catalog_Model_Category */
		$oCurrentCategory = Mage::getModel('catalog/category');
		$oCurrentCategory = $oCurrentCategory->load($iCategoryId);
		return array_merge($aCurrentCategories, $oCurrentCategory->getAllChildren(true));
	}

	/**
	 * Get the require login message
	 *
	 * @return string
	 */
	public function getRequireLoginMessage() {
		// text displayed instead of price
		if (Mage::getStoreConfig('b2bprofessional/languagesettings/languageoverride') == 1) {
			$sLoginMessage = Mage::getStoreConfig('b2bprofessional/languagesettings/requireloginmessage');
		} else {
			$sLoginMessage = $this->__('You do not have access to view this store.');
		}
		return $sLoginMessage;
	}

	/**
	 * Get the url of the require login redirect
	 *
	 * @return string
	 */
	public function getRequireLoginRedirect() {
		$sRedirectPath = '/';
		$sConfigVar = Mage::getStoreConfig('b2bprofessional/generalsettings/requireloginredirect');
		if (isset($sConfigVar)) {
			$sRedirectPath = $sConfigVar;
		}
		return Mage::getUrl($sRedirectPath);
	}
}