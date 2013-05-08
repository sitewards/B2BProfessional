<?php
/**
 * Sitewards_B2BProfessional_Helper_Data
 *	- Create functions to,
 *		- Check if user is allowed on store,
 *		- Check if product category is active,
 *		- Check customer group is active,
 *		- Check module global flag,
 *		- Check that customer is logged in and active,
 *		- Check that the product/customer is activated,
 *		- Check that the current cart is valid,
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Data extends Mage_Core_Helper_Abstract {
	/**
	 * Object for the sitewards b2bprofessional customer helper
	 *
	 * @var Sitewards_B2BProfessional_Helper_Customer
	 */
	protected $oB2BCustomerHelper;

	/**
	 * Create an instance of the sitewards b2bprofessional customer helper
	 */
	public function __construct() {
		$this->oB2BCustomerHelper = Mage::helper('b2bprofessional/customer');
	}

	/**
	 * Validate that the category of a give product is activated in the module
	 *
	 * @param int $iProductId
	 * @return boolean
	 */
	private function isCategoryActiveByProduct($iProductId) {
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
	 * Validate that the category is activated in the module
	 * 
	 * @return boolean
	 */
	private function isCategoryActive() {
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
	 * Check to see if the extension is active
	 * Returns the extension's general setting "active"
	 *
	 * @return bool
	 */
	public function isExtensionActive() {
		return Mage::getStoreConfigFlag('b2bprofessional/generalsettings/active');
	}

	/**
	 * Check to see if the extension is activated by category
	 *
	 * @return bool
	 */
	private function isExtensionActivatedByCategory() {
		return Mage::getStoreConfigFlag('b2bprofessional/activatebycategorysettings/activebycategory');
	}

	/**
	 * Check that the price can be displayed for the given product id
	 *  - Check that the extension is active
	 *  - Check that the customer is allowed in the store
	 *  - When the extension is activated by customer group and category
	 *   - Check that:
	 *    - The category is active by product
	 *    - AND The customer is active
	 *  - When the extension is activated by customer group
	 *   - Check that:
	 *    - The customer is active
	 *  - When the extension is activated by category
	 *   - Check that:
	 *    - The category is active by product
	 *    - AND the user is not logged in
	 *  - Else
	 *   - Check if the user is not logged in
	 *
	 * @param int $iProductId
	 * @return bool
	 */
	public function isProductActive($iProductId) {
		$bIsLoggedIn = false;
		// global extension activation
		if ($this->isExtensionActive()) {
			// check user logged in and has store access
			if ($this->oB2BCustomerHelper->isCustomerAllowedInStore()) {
				$bIsLoggedIn = true;
			}

			$bCheckUser		= $this->oB2BCustomerHelper->isExtensionActivatedByCustomer();
			$bCheckCategory	= $this->isExtensionActivatedByCategory();

			if($bCheckUser == true && $bCheckCategory == true) {
				$bIsActive = $this->isCategoryActiveByProduct($iProductId) && $this->oB2BCustomerHelper->isCustomerActive();
			} elseif($bCheckUser == true) {
				$bIsActive = $this->oB2BCustomerHelper->isCustomerActive();
			} elseif ($bCheckCategory == true) {
				$bIsActive = $this->isCategoryActiveByProduct($iProductId) && !$bIsLoggedIn;
			} else {
				$bIsActive = !$bIsLoggedIn;
			}
		} else {
			$bIsActive = false;
		}
		return $bIsActive;
	}

	/**
	 * Check that the price can be displayed when no product id is given
	 *  - Check that the extension is active
	 *  - Check that the customer is allowed in the store
	 *  - When the extension is activated by customer group and category
	 *   - Check that:
	 *    - The category is active
	 *    - AND The customer is active
	 *  - When the extension is activated by customer group
	 *   - Check that:
	 *    - The customer is active
	 *  - When the extension is activated by category
	 *   - Check that:
	 *    - The category is active
	 *    - AND the user is not logged in
	 *  - Else
	 *   - Check if the user not is logged in
	 *
	 * @return bool
	 */
	public function isActive() {
		$bIsLoggedIn = false;
		// global extension activation
		if ($this->isExtensionActive()) {
			// check user logged in and has store access
			if ($this->oB2BCustomerHelper->isCustomerAllowedInStore()) {
				$bIsLoggedIn = true;
			}

			$bCheckUser		= $this->oB2BCustomerHelper->isExtensionActivatedByCustomer();
			$bCheckCategory	= $this->isExtensionActivatedByCategory();

			if($bCheckUser == true && $bCheckCategory == true) {
				$bIsActive = $this->isCategoryActive() && $this->oB2BCustomerHelper->isCustomerActive();
			} elseif($bCheckUser == true) {
				$bIsActive = $this->oB2BCustomerHelper->isCustomerActive();
			} elseif ($bCheckCategory == true) {
				$bIsActive = $this->isCategoryActive() && !$bIsLoggedIn;
			} else {
				$bIsActive = !$bIsLoggedIn;
			}
		} else {
			$bIsActive = false;
		}
		return $bIsActive;
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
		$sActivatedCategoryIds = Mage::getStoreConfig('b2bprofessional/activatebycategorysettings/activecategories');
		return explode(',', $sActivatedCategoryIds);
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

	/**
	 * Get the replacement text for the add to cart url
	 *
	 * @return string
	 */
	public function getReplaceAddToCartUrl() {
		return Mage::getStoreConfig('b2bprofessional/add_to_cart/value');
	}

	/**
	 * Check if the add to cart button should be replaced
	 *
	 * @param int $iProductId
	 * @return bool
	 */
	public function replaceAddToCart($iProductId) {
		/* @var $oB2BReplacementsHelper Sitewards_B2BProfessional_Helper_Replacements */
		$oB2BReplacementsHelper = Mage::helper('b2bprofessional/replacements');
		return (bool) $this->isProductActive($iProductId) && $oB2BReplacementsHelper->replaceSection('add_to_cart');
	}

	/**
	 * Validate that the current quote in the checkout session is valid for the user
	 *  - Check each item in the quote against the function checkActive
	 *
	 * @return bool
	 */
	public function hasValidCart() {
		$bValidCart = true;
		/* @var $oQuote Mage_Sales_Model_Quote */
		$oQuote = Mage::getSingleton('checkout/session')->getQuote();
		foreach($oQuote->getAllItems() as $oItem) {
			/* @var $oItem Mage_Sales_Model_Quote_Item */
			$iProductId = $oItem->getProductId();
			/*
			 * For each item check if it is active for the current user
			 */
			if ($this->isProductActive($iProductId)) {
				$bValidCart = false;
			}
		}
		return $bValidCart;
	}
}