<?php
class Sitewards_B2BProfessional_Block_Product_View_Options_Type_Select extends Mage_Catalog_Block_Product_View_Options_Type_Select {
	/**
	 * Return formated price
	 *
	 * @param array $value
	 * @return string
	 */
	protected function _formatPrice($value, $flag=true) {
		if (!Mage::helper('b2bprofessional')->checkActive($this->getProduct()->getId())) {
			return parent::_formatPrice($value, $flag);
		} else {
			return $this->__('Please login');
		}
	}
}