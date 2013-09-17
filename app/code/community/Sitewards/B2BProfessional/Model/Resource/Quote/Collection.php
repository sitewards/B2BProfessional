<?php

class Sitewards_B2BProfessional_Model_Resource_Quote_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function _construct () {
		parent::_construct();
		$this->_init('b2bprofessional/quote');
	}
}