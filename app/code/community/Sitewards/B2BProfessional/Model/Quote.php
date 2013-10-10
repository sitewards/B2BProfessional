<?php
class Sitewards_B2BProfessional_Model_Quote extends Mage_Core_Model_Abstract {
	public function _construct() {
		parent::_construct();
		$this->_init('b2bprofessional/quote');
	}

	/**
	 * @param int $iQuoteId
	 * @param string $sKey
	 */
	public function deleteByQuote($iQuoteId, $sKey) {
		$this->_getResource()->deleteByQuote($iQuoteId, $sKey);
	}

	/**
	 * @param int $iQuoteId
	 * @param string $sKey
	 * @return string
	 */
	public function getByQuote($iQuoteId, $sKey = '') {
		return $this->_getResource()->getByQuote($iQuoteId, $sKey);
	}
}