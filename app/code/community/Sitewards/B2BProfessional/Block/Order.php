<?php
/**
 * Sitewards_B2BProfessional_Block_Order
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Order extends Mage_Core_Block_Template {
	/**
	 * return special b2bprof vars
	 *
	 * @return mixed
	 */
	public function getB2BProfessionalVars() {
		$oModel = Mage::getModel('b2bprofessional/order');
		return $oModel->getByOrder(
			$this->getOrder()
				->getId()
		);
	}

	/**
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder () {
		return Mage::registry('current_order');
	}

	/**
	 * @return bool
	 */
	public function canCancel() {
		$oModel = Mage::getModel('b2bprofessional/order');
		return $oModel->canCancel(
			$this->getOrder()
				->getId()
		);
	}

	/**
	 * @return string
	 */
	public function getCancelUrl() {
		return $this->getUrl('b2bprofessional/order/cancel', array('id' => $this->getOrder()->getId()));
	}
}