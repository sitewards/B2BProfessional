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
	public function getB2BProfessionalVars () {
		$oModel = Mage::getModel('b2bprofessional/order');
		return $oModel->getByOrder(
			$this
				->getOrder()
				->getId());
	}

	public function getOrder () {
		return Mage::registry('current_order');
	}
}