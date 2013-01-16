<?php
/**
 * Sitewards_B2BProfessional_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Checkbox
 * 	- Validate that the selection is active and display the custom information
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Checkbox extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Checkbox {
	/**
	 * Validate the it is active the display the selection name
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

	/**
	 * Validate the it is active the display the selection quantity and name
	 * 
	 * @param Mage_Catalog_Model_Product $oBundleSelection
	 * @param boolean $bIncludeContainer
	 * @return string
	 */
	public function getSelectionQtyTitlePrice($oBundleSelection, $bIncludeContainer = true) {
		if (Mage::helper ( 'b2bprofessional' )->checkActive ()) {
			return $oBundleSelection->getSelectionQty() * 1 . ' x ' . $oBundleSelection->getName();
		} else {
			return parent::getSelectionQtyTitlePrice($oBundleSelection, $bIncludeContainer);
		}
	}
}
