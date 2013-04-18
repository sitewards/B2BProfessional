<?php
/**
 * Sitewards_B2BProfessional_Model_System_Config_Source_Tags
 * 	- Create an options array with the current tags allowed in the system config
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Model_System_Config_Source_Tags {
	/**
	 * Options array for the source model
	 *
	 * @var array
	 */
	protected $_aOptions;

	/**
	 * Create an array with all the allowed tags
	 *
	 * @return array
	 */
	public function toOptionArray() {
		if (!$this->_aOptions) {
			$this->_aOptions = array(
				array(
					'value' => 'a',
					'label' => '<a>'
				),
				array(
					'value' => 'div',
					'label' => '<div>'
				),
				array(
					'value' => 'p',
					'label' => '<p>'
				),
				array(
					'value' => 'span',
					'label' => '<span>'
				),
				array(
					'value' => 'button',
					'label' => '<button>'
				),
			);
		}
		return $this->_aOptions;
	}
}