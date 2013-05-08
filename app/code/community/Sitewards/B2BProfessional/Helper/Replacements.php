<?php
/**
 * Class Sitewards_B2BProfessional_Helper_Replacements
 *  - contains the replacement pattern regular expression
 *  - functions to replace given sections
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Replacements extends Mage_Core_Helper_Abstract {
	/**
	 * Regular expression for replacements
	 */
	const PATTERN_BASE = '@<%1$s [^>]*?%2$s="[^"]*?%3$s[^"]*?"[^>]*?>.*?</%1$s>@siu';

	/**
	 * Object for the main sitewards b2bprofessional helper
	 *
	 * @var Sitewards_B2BProfessional_Helper_Data
	 */
	protected $oB2BHelper;

	/**
	 * Create an instance of the main sitewards b2bprofessional helper
	 */
	public function __construct() {
		$this->oB2BHelper = Mage::helper('b2bprofessional');
	}

	/**
	 * Build two arrays,
	 *  - one for patterns
	 *  - one for replacements,
	 * Using these two array call to replace the patterns when the cart is invalid
	 *
	 * @param array $aSections
	 * @param string $sHtml
	 * @return string
	 */
	public function replaceSections($aSections, $sHtml) {
		$aPatterns = array();
		$aReplacements = array();
		/*
		 * Foreach section to replace
		 *  - add the pattern
		 *  - add the replacement
		 */
		$bReplaceOnInvalidCart = false;
		foreach($aSections as $sReplaceSection) {
			if($this->replaceSection($sReplaceSection)) {
				$aPatterns[] = $this->getPattern($sReplaceSection);
				$aReplacements[] = $this->getReplacement($sReplaceSection);
				if($this->checkInvalidCart($sReplaceSection)) {
					$bReplaceOnInvalidCart = true;
				}
			}
		}
		if($bReplaceOnInvalidCart == true) {
			return $this->replaceOnInvalidCart($aPatterns, $aReplacements, $sHtml);
		} else {
			return $this->replaceOnIsActive($aPatterns, $aReplacements, $sHtml);
		}
	}

	/**
	 * Build two arrays,
	 *  - one for patterns
	 *  - one for replacements,
	 * Using these two array call to replace the patterns when the cart is invalid
	 *
	 * @param array $aSections
	 * @param string $sHtml
	 * @param int $iProductId
	 * @return string
	 */
	public function replaceSectionsByProductId($aSections, $sHtml, $iProductId) {
		$aPatterns = array();
		$aReplacements = array();
		/*
		 * Foreach section to replace
		 *  - add the pattern
		 *  - add the replacement
		 */
		foreach($aSections as $sReplaceSection) {
			if($this->replaceSection($sReplaceSection)) {
				$aPatterns[] = $this->getPattern($sReplaceSection);
				$aReplacements[] = $this->getReplacement($sReplaceSection);
			}
		}
		return $this->replaceOnInvalidCartByProductId($aPatterns, $aReplacements, $sHtml, $iProductId);
	}

	/**
	 * Check if a given config section should be replaced
	 *
	 * @param string $sConfigSection
	 * @return bool
	 */
	public function replaceSection($sConfigSection) {
		return Mage::getStoreConfigFlag('b2bprofessional/'.$sConfigSection.'/replace');
	}

	/**
	 * From a given config section
	 *  - Load all the config
	 *  - remove unused sections
	 *  - perform a sprintf on given config items
	 *
	 * @param string $sConfigSection
	 * @return string
	 */
	private function getPattern($sConfigSection) {
		// Load config array and unset unused information
		$aSectionConfig = Mage::getStoreConfig('b2bprofessional/'.$sConfigSection);
		unset($aSectionConfig['replace']);
		unset($aSectionConfig['remove']);

		// Replace the tag, id and value sections of the regular expression
		return sprintf($this::PATTERN_BASE, $aSectionConfig['tag'], $aSectionConfig['id'], $aSectionConfig['value']);
	}

	/**
	 * Get replacement text for a given config section
	 *
	 * @param string $sConfigSection
	 * @return string
	 */
	private function getReplacement($sConfigSection) {
		// Check for the remove flag
		if(!Mage::getStoreConfigFlag('b2bprofessional/'.$sConfigSection.'/remove')) {
			// If the remove flag is not set then get the module's price message
			/* @var $oB2BMessagesHelper Sitewards_B2BProfessional_Helper_Messages */
			$oB2BMessagesHelper = Mage::helper('b2bprofessional/messages');
			return $oB2BMessagesHelper->getMessage($oB2BMessagesHelper::MESSAGE_TYPE_PRICE);
		}
	}

	/**
	 * Check if a given config section should check if the cart is valid
	 *
	 * @param string $sConfigSection
	 * @return bool
	 */
	private function checkInvalidCart($sConfigSection) {
		return Mage::getStoreConfigFlag('b2bprofessional/'.$sConfigSection.'/check_cart');
	}

	/**
	 * When we have an invalid cart
	 *  - Perform a preg_replace with a given set of patterns and replacements on a string
	 *  - When product id is given check for valid product
	 *  - When no product id is given then check to complete cart
	 *
	 * @param array $aPatterns
	 * @param array $aReplacements
	 * @param string $sBlockHtml
	 * @return string
	 */
	private function replaceOnInvalidCart($aPatterns, $aReplacements, $sBlockHtml) {
		/*
		 * If you have no product id provided and an invalid cart
		 *
		 * THEN
		 * Perform the preg_replace
		 */
		if (
			!$this->oB2BHelper->hasValidCart()
		) {
			$sBlockHtml = $this->getNewBlockHtml($aPatterns, $aReplacements, $sBlockHtml);
		}
		return $sBlockHtml;
	}

	/**
	 * When we have an invalid user
	 *  - Perform a preg_replace with a given set of patterns and replacements on a string
	 *  - Use only global isActive
	 *
	 * @param array $aPatterns
	 * @param array $aReplacements
	 * @param string $sBlockHtml
	 * @return string
	 */
	private function replaceOnIsActive($aPatterns, $aReplacements, $sBlockHtml) {
		if (
			$this->oB2BHelper->isActive()
		) {
			$sBlockHtml = $this->getNewBlockHtml($aPatterns, $aReplacements, $sBlockHtml);
		}
		return $sBlockHtml;
	}

	/**
	 * When we have an invalid cart
	 *  - Perform a preg_replace with a given set of patterns and replacements on a string
	 *  - When product id is given check for valid product
	 *  - When no product id is given then check to complete cart
	 *
	 * @param array $aPatterns
	 * @param array $aReplacements
	 * @param string $sBlockHtml
	 * @param int $iProductId
	 * @return string
	 */
	private function replaceOnInvalidCartByProductId($aPatterns, $aReplacements, $sBlockHtml, $iProductId) {
		/*
		 * If you have a product id provided and it is invalid
		 *
		 * THEN
		 * Perform the preg_replace
		 */
		if (
			$this->oB2BHelper->isProductActive($iProductId)
		) {
			$sBlockHtml = $this->getNewBlockHtml($aPatterns, $aReplacements, $sBlockHtml);
		}
		return $sBlockHtml;
	}

	/**
	 * Perform a preg_replace with the pattern and replacements given
	 *
	 * @param array $aPatterns
	 * @param array $aReplacements
	 * @param string $sBlockHtml
	 * @return mixed
	 */
	private function getNewBlockHtml($aPatterns, $aReplacements, $sBlockHtml) {
		return preg_replace(
			$aPatterns,
			$aReplacements,
			$sBlockHtml
		);
	}
}