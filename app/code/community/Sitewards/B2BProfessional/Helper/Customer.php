<?php
/**
 * Class Sitewards_B2BProfessional_Helper_Customer
 *  - function to check if extension is activated by customer
 *  - function to check if extension requires login
 *  - function to check if customer is active
 *  - function to check if customer is allowed in the current store
 *  - function to check if customers are activated globally
 *  - function to check if customer is active for store
 *  - function to check if customer was created via the admin section
 *  - function to get the extension's activated customer groups
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Customer extends Mage_Core_Helper_Abstract {
	/**
	 * Check to see if the extension is activated by customer
	 *
	 * @return bool
	 */
	public function isExtensionActivatedByCustomer() {
		return Mage::getStoreConfigFlag(Sitewards_B2BProfessional_Helper_Core::CONFIG_B2B_PROFESSIONAL_NODE . '/' . Sitewards_B2BProfessional_Helper_Core::CONFIG_CUSTOMER_SETTINGS_NODE . '/activebycustomer');
	}

	/**
	 * Check to see if the website is set-up to require a user login to view pages
	 *
	 * @return boolean
	 */
	public function isLoginRequired() {
		return Mage::getStoreConfigFlag(Sitewards_B2BProfessional_Helper_Core::CONFIG_B2B_PROFESSIONAL_NODE . '/requirelogin/requirelogin');
	}

	/**
	 * Check that the current customer's group id is in the list of active group ids
	 *
	 * @return boolean
	 */
	public function isCustomerActive() {
		/* @var $oCustomerSession Mage_Customer_Model_Session */
		$oCustomerSession = Mage::getModel('customer/session');
		$iCurrentCustomerGroupId = $oCustomerSession->getCustomerGroupId();
		$aActiveCustomerGroupIds = $this->getActivatedCustomerGroupIds();

		if (in_array($iCurrentCustomerGroupId, $aActiveCustomerGroupIds)) {
			return true;
		}
	}

	/**
	 * Check to see if the user is allowed on the current store
	 *  - Check if the customer is logged in,
	 *   - Check if the extension is set to have global customer activation,
	 *   - Check if the user is active for the current store
	 *
	 * @return bool
	 */
	public function isCustomerAllowedInStore() {
		/* @var $oCustomerSession Mage_Customer_Model_Session */
		$oCustomerSession = Mage::getSingleton('customer/session');
		if ($oCustomerSession->isLoggedIn()) {
			/*
			 * If customer activation is global
			 *  - then any customer can access any store
			 */
			if ($this->isCustomerActivationGlobal()) {
				return true;
			}

			/* @var $oCustomer Mage_Customer_Model_Customer */
			$oCustomer = $oCustomerSession->getCustomer();
			if($this->isCustomerActiveForStore($oCustomer)) {
				return true;
			}
		}
	}

	/**
	 * Check to see if the extension has the "global customer activation"
	 *
	 * @return bool
	 */
	private function isCustomerActivationGlobal() {
		return Mage::getStoreConfigFlag(Sitewards_B2BProfessional_Helper_Core::CONFIG_B2B_PROFESSIONAL_NODE . '/' . Sitewards_B2BProfessional_Helper_Core::CONFIG_GENERAL_SETTINGS_NODE . '/activecustomers');
	}

	/**
	 * Check to see if the user is active for current store
	 *  NOTE: users created via the admin section cannot be attached to a front end store and so have global activation
	 *
	 * @param $oCustomer Mage_Customer_Model_Customer
	 * @return bool
	 */
	private function isCustomerActiveForStore($oCustomer) {
		/*
		 * Check to see if the user was created via the admin section
		 *  - Note: users created via the admin section cannot be attached to a front end store
		 */
		if ($this->isCustomerAdminCreation($oCustomer)) {
			return true;
		}

		/*
		 * Get user's store and current store for comparison
		 */
		$iUserStoreId		= $oCustomer->getStoreId();
		$iCurrentStoreId	= Mage::app()->getStore()->getId();

		/*
		 * Return true if:
		 *  - the user's store id matches the current store id
		 */
		if ($iUserStoreId == $iCurrentStoreId) {
			return true;
		}
	}

	/**
	 * Check to see if the user has been created via the admin section
	 *
	 * @param $oCustomer Mage_Customer_Model_Customer
	 * @return bool
	 */
	private function isCustomerAdminCreation($oCustomer) {
		if($oCustomer->getCreatedIn() == 'Admin') {
			return true;
		}
	}

	/**
	 * Get an array of all the activated customer group ids
	 *  - always include the 'NOT LOGGED IN' group
	 *
	 * @return array
	 */
	private function getActivatedCustomerGroupIds() {
		/*
		 * Customer group ids are saved in the config in format
		 *  - "group1,group2"
		 */
		$sActivatedCustomerGroups = Mage::getStoreConfig(Sitewards_B2BProfessional_Helper_Core::CONFIG_B2B_PROFESSIONAL_NODE . '/' . Sitewards_B2BProfessional_Helper_Core::CONFIG_CUSTOMER_SETTINGS_NODE . '/activecustomers');
		$aActivatedCustomerGroupIds = explode(',', $sActivatedCustomerGroups);

		/*
		 * Always add the guest user group id when activated by customer group
		 * Note: the the group code for the guest user can not be changed via the admin section
		 */
		/* @var $oCustomerGroup Mage_Customer_Model_Group */
		$oCustomerGroup = Mage::getModel('customer/group');
		$iGuestGroupId = $oCustomerGroup->load('NOT LOGGED IN', 'customer_group_code')->getId();
		$aActivatedCustomerGroupIds[] = $iGuestGroupId;

		return $aActivatedCustomerGroupIds;
	}

	/**
	 * checks if user is allowed to view products
	 *
	 * @return bool
	 */
	public function isUserAllowed()
	{
		/* @var $oHelper Sitewards_B2BProfessional_Helper_Data */
		$oHelper = Mage::helper('b2bprofessional');
		return (
			$oHelper->isExtensionActive() == true
			&& (
				$this->isLoginRequired() == false
				|| Mage::getSingleton('customer/session')->isLoggedIn()
			)
		);
	}
}