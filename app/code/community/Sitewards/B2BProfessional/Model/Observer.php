<?php
/**
 * Sitewards_B2BProfessional_Model_Observer
 * 	- Observer to catch the following actions
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Model_Observer {
	/**
	 * The last product Id
	 *
	 * @var int
	 */
	protected static $_iLastProductId = 0;

	/**
	 * Check if the site requires login to work
	 * 	- Add notice,
	 * 	- Redirect to the home page,
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function onControllerActionPreDispatch(Varien_Event_Observer $oObserver) {
		/* @var $oHelper Sitewards_B2BProfessional_Helper_Data */
		$oHelper = Mage::helper('b2bprofessional');
		if($oHelper->checkGlobalActive() == true) {
			/*
			 * Check to see if the system requires a login
			 * And there is no logged in user
			 */
			if($oHelper->checkRequireLogin() == true && !Mage::getSingleton('customer/session')->isLoggedIn()) {
				/* @var $oControllerAction Mage_Core_Controller_Front_Action */
				$oControllerAction = $oObserver->getData('controller_action');
				/*
				 * Check to see if the controller is:
				 * 	1) Cms related for cms pages,
				 * 	2) A front action to allow for admin pages,
				 * 	3) Customer account to allow for login
				 */
				if(
					!$oControllerAction instanceof Mage_Cms_IndexController
						&&
					!$oControllerAction instanceof Mage_Cms_PageController
						&&
					$oControllerAction instanceof Mage_Core_Controller_Front_Action
						&&
					!$oControllerAction instanceof Mage_Customer_AccountController
				){
					// Redirect to the homepage
					/* @var $oResponse Mage_Core_Controller_Response_Http */
					$oResponse = $oControllerAction->getResponse();
					$oResponse->setRedirect($oHelper->getRequireLoginRedirect());

					/*
					 * Add message to the session
					 * 	- Note:
					 * 		We need session_write_close otherwise the messages get lots in redirect
					 */
					/* @var $oSession Mage_Core_Model_Session */
					$oSession = Mage::getSingleton('core/session');
					$oSession->addNotice($oHelper->getRequireLoginMessage());
					session_write_close();
				}
			}
		}
	}

	/**
	 * Check for block Mage_Catalog_Block_Product_Price
	 * 	- Check the product is active via the Sitewards_B2BProfessional_Helper_Data
	 * 	- Replace the text with that on the b2bprofessional
	 *
	 * @param Varien_Event_Observer $oObserver
	 * @return string
	 */
	public function onCoreBlockAbstractToHtmlAfter(Varien_Event_Observer $oObserver) {
		$oBlock = $oObserver->getData('block');
		$oTransport = $oObserver->getData('transport');

		if($oBlock instanceof Mage_Catalog_Block_Product_Price) {
			$oProduct = $oBlock->getProduct();
			$iCurrentProductId = $oProduct->getId();

			/* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
			$oB2BHelper = Mage::helper('b2bprofessional');

			if ($oB2BHelper->checkActive($iCurrentProductId)) {
				// To stop duplicate information being displayed validate that we only do this once per product
				if ($iCurrentProductId != self::$_iLastProductId) {
					self::$_iLastProductId = $iCurrentProductId;

					// text displayed instead of price
					if (Mage::getStoreConfig('b2bprofessional/languagesettings/languageoverride') == 1) {
						$sReplacementText = Mage::getStoreConfig('b2bprofessional/languagesettings/logintext');
					} else {
						$sReplacementText = $oB2BHelper->__('Please login');
					}

					$oTransport->setHtml($sReplacementText);
				} else {
					$oTransport->setHtml('');
				}
				// Set can show price to false to stop tax being displayed via Symmetrics_TweaksGerman_Block_Tax
				$oProduct->setCanShowPrice(false);
			}
		}
	}

	/**
	 * On the event catalog_product_type_configurable_price
	 * Set the COnfigurable price of a product to 0 to stop the changed price showing up in the drop down
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function onCatalogProductTypeConfigurablePrice(Varien_Event_Observer $oObserver) {
		$oProduct = $oObserver->getData('product');
		/* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
		$oB2BHelper = Mage::helper('b2bprofessional');

		if ($oB2BHelper->checkActive($oProduct->getId())) {
			$oProduct->setConfigurablePrice(0);
		}
	}
}