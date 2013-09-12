<?php
class Sitewards_B2BProfessional_Model_Order extends Mage_Core_Model_Abstract{
	public function _construct() {
		parent::_construct();
		$this->_init('b2bprofessional/order');
	}

	/**
	 * @param int $iOrderId
	 * @param string $sKey
	 */
	public function deleteByOrder($iOrderId, $sKey) {
		$this->_getResource()->deleteByOrder($iOrderId, $sKey);
	}

	/**
	 * @param int $iOrderId
	 * @param string $sKey
	 * @return string
	 */
	public function getByOrder($iOrderId, $sKey = '') {
		return $this->_getResource()->getByOrder($iOrderId, $sKey);
	}
}