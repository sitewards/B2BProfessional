<?php
/**
 * Class Sitewards_B2BProfessional_Helper_Category
 *  - function to check if extension is activated by category
 *  - function to check if category is active
 *  - function to check if category is active by a given product id
 *  - function to get the current category id and all children ids
 *  - function to get the category ids of each category filter set
 *  - function to check that at least one of the given category ids is active
 *  - function to get the current category object
 *  - function to get all category ids activated via the system config
 *  - function to get an array of category ids activated via the admin config section
 *  - function to load all child category ids into an array from a given category id
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Category extends Mage_Core_Helper_Abstract {
	/**
	 * Check to see if the extension is activated by category
	 *
	 * @return bool
	 */
	public function isExtensionActivatedByCategory() {
		return Mage::getStoreConfigFlag(Sitewards_B2BProfessional_Helper_Core::CONFIG_B2B_PROFESSIONAL_NODE . '/' . Sitewards_B2BProfessional_Helper_Core::CONFIG_CATEGORY_SETTINGS_NODE . '/activebycategory');
	}

	/**
	 * Validate that the category is activated in the module
	 *
	 * @return boolean
	 */
	public function isCategoryActive() {
		/*
		 * Check if there is a filtered category
		 * 	- If not check for a current_category,
		 * 		- If not load the store default category,
		 */
		$aB2BProfFilters = Mage::registry('b2bprof_category_filters');
		if(empty($aB2BProfFilters)) {
			$aCurrentCategories = $this->getCurrentCategoryIds();
		} else {
			$aCurrentCategories = $this->getCategoryIdsByB2BProfFilters($aB2BProfFilters);
		}
		$aCurrentCategories = array_unique($aCurrentCategories);

		if (!is_array($aCurrentCategories)) {
			$aCurrentCategories = array (
				$aCurrentCategories
			);
		}
		return $this->hasActiveCategory($aCurrentCategories);
	}

	/**
	 * Validate that the category of a give product is activated in the module
	 *
	 * @param int $iProductId
	 * @return boolean
	 */
	public function isCategoryActiveByProduct($iProductId) {
		/* @var $oProduct Mage_Catalog_Model_Product */
		$oProduct = Mage::getModel('catalog/product')->load($iProductId);
		$aCurrentCategories = $oProduct->getCategoryIds();
		$aParentProductIds = array();

		if($oProduct->isGrouped()) {
			/* @var $oGroupedProductModel Mage_Catalog_Model_Product_Type_Grouped */
			$oGroupedProductModel = Mage::getModel('catalog/product_type_grouped');
			$aParentProductIds = $oGroupedProductModel->getParentIdsByChild($iProductId);
		} elseif($oProduct->isConfigurable()) {
			/* @var $oConfigurableProductModel Mage_Catalog_Model_Product_Type_Configurable */
			$oConfigurableProductModel = Mage::getModel('catalog/product_type_configurable');
			$aParentProductIds = $oConfigurableProductModel->getParentIdsByChild($iProductId);
		}

		if(!empty($aParentProductIds)) {
			foreach ($aParentProductIds as $iParentProductId) {
				/* @var $oParentProduct Mage_Catalog_Model_Product */
				$oParentProduct = Mage::getModel('catalog/product')->load($iParentProductId);
				$aParentProductCategories = $oParentProduct->getCategoryIds();
				$aCurrentCategories = array_merge($aCurrentCategories, $aParentProductCategories);
			}
		}
		$aCurrentCategories = array_unique($aCurrentCategories);

		if (!is_array($aCurrentCategories)) {
			$aCurrentCategories = array (
				$aCurrentCategories
			);
		}
		return $this->hasActiveCategory($aCurrentCategories);
	}

	/**
	 * Get the current category id and all children ids
	 *
	 * @return array
	 */
	private function getCurrentCategoryIds() {
		/* @var $oCategory Mage_Catalog_Model_Category */
		$oCategory = $this->getCurrentCategory();

		$aCurrentCategories = $oCategory->getAllChildren(true);
		$aCurrentCategories[] = $oCategory->getId();

		return $aCurrentCategories;
	}

	/**
	 * Get the category ids of each category filter set
	 *
	 * @param array $aB2BProfFilters
	 * array(
	 *  cat_id_1,
	 *  cat_id_2
	 * )
	 * @return array
	 */
	private function getCategoryIdsByB2BProfFilters($aB2BProfFilters) {
		$aCurrentCategories = $aB2BProfFilters;
		foreach($aB2BProfFilters as $iCategoryId) {
			/* @var $oCategory Mage_Catalog_Model_Category */
			$oCategory = Mage::getModel('catalog/category')->load($iCategoryId);

			$aCurrentCategories = array_merge($aCurrentCategories, $oCategory->getAllChildren(true));
		}
		return $aCurrentCategories;
	}

	/**
	 * Check that at least one of the given category ids is active
	 *
	 * @param array $aCategoryIds
	 * @return bool
	 */
	private function hasActiveCategory($aCategoryIds) {
		$aActiveCategoryIds = $this->getActiveCategories();
		foreach ($aCategoryIds as $iCategoryId) {
			if (in_array($iCategoryId, $aActiveCategoryIds)) {
				return true;
			}
		}
	}

	/**
	 * Get the current category object
	 *  - Use the "filter category" if set
	 *  - Use the "current_category" if set
	 *  - Use the store root category
	 *
	 * @return Mage_Catalog_Model_Category
	 */
	private function getCurrentCategory() {
		/* @var $oCategory Mage_Catalog_Model_Category */
		$oCategory = Mage::registry('current_category_filter');
		if(is_null($oCategory)) {
			$oCategory = Mage::registry('current_category');
			if(is_null($oCategory)) {
				$oCategory = Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId());
			}
		}
		return $oCategory;
	}

	/**
	 * Get all category ids activated via the system config
	 *  - Include the children category ids
	 *
	 * @return array
	 * array(
	 *  cat_id_1,
	 *  cat_id_2
	 * )
	 */
	private function getActiveCategories() {
		$aCurrentActiveCategories = $this->getActivatedCategoryIds();

		/**
		 * Loop through each activated category ids and add children category ids
		 */
		$aSubActiveCategories = array();
		foreach ($aCurrentActiveCategories as $iCategoryId) {
			$aSubActiveCategories = $this->addCategoryChildren($iCategoryId, $aSubActiveCategories);
		}
		return array_unique($aSubActiveCategories);
	}

	/**
	 * Get an array of category ids activated via the admin config section
	 *
	 * @return array
	 */
	private function getActivatedCategoryIds() {
		/*
		 * Category Ids are saved in the config in format
		 *  - "category1,category2"
		 */
		$sActivatedCategoryIds = Mage::getStoreConfig(Sitewards_B2BProfessional_Helper_Core::CONFIG_B2B_PROFESSIONAL_NODE . '/' . Sitewards_B2BProfessional_Helper_Core::CONFIG_CATEGORY_SETTINGS_NODE . '/activecategories');
		return explode(',', $sActivatedCategoryIds);
	}

	/**
	 * From given category id load all child ids into an array
	 *
	 * @param int $iCategoryId
	 * @param array $aCurrentCategories
	 * 	array(
	 * 		cat_id_1,
	 * 		cat_id_2
	 * 	)
	 * @return array
	 * 	array(
	 * 		cat_id_1,
	 * 		cat_id_2
	 * 	)
	 */
	private function addCategoryChildren($iCategoryId, $aCurrentCategories = array()) {
		/* @var $oCurrentCategory Mage_Catalog_Model_Category */
		$oCurrentCategory = Mage::getModel('catalog/category');
		$oCurrentCategory = $oCurrentCategory->load($iCategoryId);
		return array_merge($aCurrentCategories, $oCurrentCategory->getAllChildren(true));
	}
}