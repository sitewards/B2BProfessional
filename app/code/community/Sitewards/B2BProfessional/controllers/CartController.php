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
					if ($oB2BHelper->checkActive($iMultiProductId)) {
						$bAllowed = false;
					}
				}
			}
		}

		if ((!empty($iProductId) && $oB2BHelper->checkActive($iProductId)) || ! $bAllowed) {
			$this->setFlag('', 'no-dispatch', true);
			Mage::getSingleton('customer/session')->addError($oB2BHelper->getCheckoutMessage());
			Mage::app()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'))->sendHeaders();
		}
	}
}