<?php
/**
 * Sitewards_B2BProfessional_OrderController
 * implements actions for new order form and cancel an order
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_OrderController extends Mage_Core_Controller_Front_Action {

	/**
	 * Check customer authentication
	 */
	public function preDispatch () {
		parent::preDispatch();
		$sLoginUrl = Mage::helper('customer')
			->getLoginUrl();

		if (!Mage::getSingleton('customer/session')
			->authenticate($this, $sLoginUrl)
		) {
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);
		}
	}

	public function formAction() {
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->renderLayout();
	}

	/**
	 * cancels an order
	 */
	public function cancelAction() {
		$iOrderId = $this->getRequest()->getParam('id');
		$oB2BModel = Mage::getModel('b2bprofessional/order');
		$oMageModel = Mage::getModel('sales/order')->load($iOrderId);
		if ($oB2BModel->canCancel($iOrderId)) {
			$oMageModel->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
		}
		$this->getResponse()->setRedirect(Mage::helper('b2bprofessional')->getOrderHistoryUrl());
	}
}