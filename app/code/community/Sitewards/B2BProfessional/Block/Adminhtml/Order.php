<?php
/**
 * Sitewards_B2BProfessional_Block_Adminhtml_Order
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Sales_Order_Abstract {
	/**
	 * @return array
	 */
	public function getB2BProfessionalVars () {
		$oModel = Mage::getModel('b2bprofessional/order');
		return $oModel->getByOrder(
			$this->getOrder()
				->getId());
	}
}