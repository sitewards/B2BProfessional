<?php
/**
 * Sitewards_B2BProfessional_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Multi
 * 	- Validate that the selection is active and display the custom information
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Multi extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi {
	/**
	 * Validate that the selection is active and display custom information
	 * 
	 * @param Mage_Catalog_Model_Product $oBundleSelection
	 * @param boolean $bIncludeContainer
	 * @return string
	 */
	public function getSelectionTitlePrice($oBundleSelection, $bIncludeContainer = true) {
		if (Mage::helper('b2bprofessional')->checkActive()) {
			return $oBundleSelection->getName();
		} else {
			return parent::getSelectionTitlePrice($oBundleSelection, $bIncludeContainer);
		}
	}
}
