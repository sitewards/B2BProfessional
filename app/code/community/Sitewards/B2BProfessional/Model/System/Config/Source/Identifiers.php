<?php
/**
 * Sitewards_B2BProfessional_Model_System_Config_Source_Identifiers
 * 	- Create an options array with the current identifiers allowed in the system config
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Model_System_Config_Source_Identifiers {
	/**
	 * Options array for the source model
	 *
	 * @var array
	 */
	protected $_aOptions;

	/**
	 * Create an array with all the allowed identifiers
	 *
	 * @return array
	 */
	public function toOptionArray() {
		if (!$this->_aOptions) {
			$this->_aOptions = array(
				array(
					'value' => 'id',
					'label' => 'id'
				),
				array(
					'value' => 'class',
					'label' => 'class'
				),
				array(
					'value' => 'onclick',
					'label' => 'onclick'
				),
			);
		}
		return $this->_aOptions;
	}
}