<?php
/**
 * Class Sitewards_B2BProfessional_Helper_Core
 *  - define the reusable sections of the config file
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Core extends Mage_Core_Helper_Abstract {
	/**
	 * The root node of the b2b professional configuration
	 */
	const CONFIG_B2B_PROFESSIONAL_NODE = 'b2bprofessional';

	/**
	 * The general settings node of the b2b professional configuration
	 */
	const CONFIG_GENERAL_SETTINGS_NODE = 'generalsettings';

	/**
	 * The language settings node of the b2b professional configuration
	 */
	const CONFIG_LANGUAGE_SETTINGS_NODE = 'languagesettings';

	/**
	 * The category settings node of the b2b professional configuration
	 */
	const CONFIG_CATEGORY_SETTINGS_NODE = 'activatebycategorysettings';

	/**
	 * The customer settings node of the b2b professional configuration
	 */
	const CONFIG_CUSTOMER_SETTINGS_NODE = 'activatebycustomersettings';
}