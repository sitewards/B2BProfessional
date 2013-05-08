<?php
/**
 * Sitewards_B2BProfessional_CartController
 *	- Add product check on the preDispatch function
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
require_once 'Mage/Checkout/controllers/CartController.php';
class Sitewards_B2BProfessional_CartController extends Mage_Checkout_CartController {
	/**
	 * On checkout cart controller preDispatch
	 * 	- validate that all products are active for customer/customer group,
	 * 	- assign error message,
	 * 	- redirect to customer login page,
	 */
	public function preDispatch() {
		parent::preDispatch ();

		$oRequest = $this->getRequest();
		$iProductId = $oRequest->get('product');
		/* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
		$oB2BHelper = Mage::helper('b2bprofessional');

		// check for grouped products
		$bAllowed = true;
		$aMultiProducts = $oRequest->getPost('super_group');
		if (!empty($aMultiProducts)) {
			foreach ($aMultiProducts as $iMultiProductId => $iMultiProductValue) {
				if ($iMultiProductValue > 0) {
					if ($oB2BHelper->isProductActive($iMultiProductId)) {
						$bAllowed = false;
					}
				}
			}
		}

		if ((!empty($iProductId) && $oB2BHelper->isProductActive($iProductId)) || ! $bAllowed) {
			/* @var $oB2BMessagesHelper Sitewards_B2BProfessional_Helper_Messages */
			$oB2BMessagesHelper = Mage::helper('b2bprofessional/messages');
			/* @var $oB2BRedirectsHelper Sitewards_B2BProfessional_Helper_Redirects */
			$oB2BRedirectsHelper = Mage::helper('b2bprofessional/redirects');

			$this->setFlag('', 'no-dispatch', true);
			Mage::getSingleton('customer/session')->addError($oB2BMessagesHelper->getMessage($oB2BMessagesHelper::MESSAGE_TYPE_CHECKOUT));
			Mage::app()->getResponse()->setRedirect($oB2BRedirectsHelper->getRedirect($oB2BRedirectsHelper::REDIRECT_TYPE_ADD_TO_CART))->sendHeaders();
		}
	}
}