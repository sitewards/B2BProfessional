<?php

/**
 * Sitewards_B2BProfessional_Helper_Category
 *  - Helper containing the checks for
 *      - if extension is activated by category
 *      - checking if a category is active from a given product
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Category extends Mage_Core_Helper_Abstract
{

    /**
     * Check to see if the website is set-up to require a user login to view pages
     *
     * @return boolean
     */
    public function isExtensionActivatedByCategory()
    {
        return Mage::getStoreConfigFlag('b2bprofessional/activatebycategorysettings/activebycategory');
    }

    /**
     * Validate that the category of a give product is activated in the module
     *
     * @param Mage_Catalog_Model_Product $oProduct
     * @return boolean
     */
    public function isCategoryActiveByProduct(Mage_Catalog_Model_Product $oProduct)
    {
        $aCurrentCategories = $oProduct->getCategoryIds();

        $aParentProductIds = array();
        if ($oProduct->isSuper()) {
            $oProductType = $oProduct->getTypeInstance();
            $aParentProductIds = $oProductType->getParentIdsByChild($oProduct->getId());
        }

        if (!empty($aParentProductIds)) {
            foreach ($aParentProductIds as $iParentProductId) {
                /* @var $oParentProduct Mage_Catalog_Model_Product */
                $oParentProduct = Mage::getModel('catalog/product')->load($iParentProductId);
                $aParentProductCategories = $oParentProduct->getCategoryIds();
                $aCurrentCategories = array_merge($aCurrentCategories, $aParentProductCategories);
            }
        }
        $aCurrentCategories = array_unique($aCurrentCategories);

        if (!is_array($aCurrentCategories)) {
            $aCurrentCategories = array(
                $aCurrentCategories
            );
        }
        return $this->_hasActiveCategory($aCurrentCategories);
    }

    /**
     * Check that at least one of the given category ids is active
     *
     * @param array $aCategoryIds
     * @return bool
     */
    protected function _hasActiveCategory($aCategoryIds)
    {
        $aActiveCategoryIds = $this->getActiveCategories();
        foreach ($aCategoryIds as $iCategoryId) {
            if (in_array($iCategoryId, $aActiveCategoryIds)) {
                return true;
            }
        }
        return false;
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
    protected function getActiveCategories()
    {
        $aCurrentActiveCategories = $this->_getActivatedCategoryIds();

        /**
         * Loop through each activated category ids and add children category ids
         */
        $aSubActiveCategories = array();
        foreach ($aCurrentActiveCategories as $iCategoryId) {
            $aSubActiveCategories = $this->_addCategoryChildren($iCategoryId, $aSubActiveCategories);
        }
        return array_unique($aSubActiveCategories);
    }

    /**
     * Get an array of category ids activated via the admin config section
     *
     * @return array
     */
    protected function _getActivatedCategoryIds()
    {
        /*
         * Category Ids are saved in the config in format
         *  - "category1,category2"
         */
        $sActivatedCategoryIds = Mage::getStoreConfig(
            'b2bprofessional/activatebycategorysettings/activecategories'
        );
        return explode(',', $sActivatedCategoryIds);
    }

    /**
     * From given category id load all child ids into an array
     *
     * @param int $iCategoryId
     * @param array $aCurrentCategories
     *    array(
     *        cat_id_1,
     *        cat_id_2
     *    )
     * @return array
     *    array(
     *        cat_id_1,
     *        cat_id_2
     *    )
     */
    protected function _addCategoryChildren($iCategoryId, $aCurrentCategories = array())
    {
        /* @var $oCurrentCategory Mage_Catalog_Model_Category */
        $oCurrentCategory = Mage::getModel('catalog/category');
        $oCurrentCategory = $oCurrentCategory->load($iCategoryId);

        return array_merge($aCurrentCategories, $oCurrentCategory->getAllChildren(true));
    }
}