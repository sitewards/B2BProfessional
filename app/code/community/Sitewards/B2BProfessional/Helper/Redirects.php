<?php
/**
 * Class Sitewards_B2BProfessional_Helper_Redirects
 *  - contains each redirect type config path as constant
 *  - function that takes in a config path and returns a url
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Redirects extends Mage_Core_Helper_Abstract {
	/**
	 * String containing the configuration path for the login redirect
	 */
	const REDIRECT_TYPE_LOGIN = 'requirelogin/requireloginredirect';

	/**
	 * String containing the configuration path for the add to cart redirect
	 */
	const REDIRECT_TYPE_ADD_TO_CART = 'generalsettings/addtocartredirect';

	/**
	 * The getRedirect function will for a given redirect configuration path
	 *  - load the value for the given configuration path
	 *  - return a formatted url based on this value
	 *
	 * @param string $sConfigPath
	 * @return string
	 */
	public function getRedirect($sConfigPath) {
		$sConfigPath = Sitewards_B2BProfessional_Helper_Core::CONFIG_B2B_PROFESSIONAL_NODE . '/' . $sConfigPath;
		$sRedirectPath = '/';
		$sConfigVar = Mage::getStoreConfig($sConfigPath);
		if (isset($sConfigVar)) {
			$sRedirectPath = $sConfigVar;
		}
		return Mage::getUrl($sRedirectPath);
	}
}