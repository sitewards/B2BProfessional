<?php
/**
 * Sitewards_B2BProfessional_OrderController
 * implements formAction to show New Order Form
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_OrderController extends Mage_Core_Controller_Front_Action {
	public function formAction() {
		$this->loadLayout();
		$this->renderLayout();
	}

	public function cancelAction() {
		$iOrderId = $this->getRequest()->getParam('id');
		$oB2BModel = Mage::getModel('b2bprofessional/order');
		$oMageModel = Mage::getModel('sales/order')->load($iOrderId);
		$oSession = Mage::getSingleton('checkout/session');;
		if ($oB2BModel->canCancel($iOrderId)) {
			$oMageModel->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
		}
		$this->getResponse()->setRedirect(Mage::helper('b2bprofessional')->getOrderHistoryUrl());
	}
}