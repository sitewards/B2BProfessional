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
class Sitewards_B2BProfessional_Helper_Category extends Sitewards_B2BProfessional_Helper_Core
{
    /**
     * Path for the config for extension active by category
     */
    const CONFIG_EXTENSION_ACTIVE_BY_CATEGORY = 'b2bprofessional/activatebycategorysettings/activebycategory';

    /**
     * Path for the config for activated categories
     */
    const CONFIG_EXTENSION_ACTIVATED_CATEGORIES = 'b2bprofessional/activatebycategorysettings/activecategories';

    /**
     * Variable for if the extension is active by category
     *
     * @var bool
     */
    protected $bExtensionActiveByCategory;

    /**
     * Check to see if the website is set-up to require a user login to view pages
     *
     * @return bool
     */
    public function isExtensionActivatedByCategory()
    {
        return $this->getStoreFlag(self::CONFIG_EXTENSION_ACTIVE_BY_CATEGORY, 'bExtensionActiveByCategory');
    }

    /**
     * For a product get all the parent and children product ids when they are set
     *
     * @param Mage_Catalog_Model_Product $oProduct
     * @return int[]
     */
    protected function getChildrenAndParentIds(Mage_Catalog_Model_Product $oProduct)
    {
        $oProductType             = $oProduct->getTypeInstance();
        $aLinkedProductIds        = $oProductType->getParentIdsByChild($oProduct->getId());
        $aChildProductIdsByGroups = $oProductType->getChildrenIds($oProduct->getId());
        foreach ($aChildProductIdsByGroups as $aChildProductIds) {
            $aLinkedProductIds = array_unique(array_merge($aLinkedProductIds, $aChildProductIds));
        }
        return $aLinkedProductIds;
    }

    /**
     * Validate that the category of a give product is activated in the module
     *
     * @param Mage_Catalog_Model_Product $oProduct
     * @return bool
     */
    public function isCategoryActiveByProduct(Mage_Catalog_Model_Product $oProduct)
    {
        $aCurrentCategories = $oProduct->getCategoryIds();

        $aLinkedProductIds = array();
        if ($oProduct->isSuper()) {
            $aLinkedProductIds = $this->getChildrenAndParentIds($oProduct);
        }

        if (!empty($aLinkedProductIds)) {
            $aCurrentCategories = $this->getAllCategoryIds($aLinkedProductIds, $aCurrentCategories);
        }

        if (!is_array($aCurrentCategories)) {
            $aCurrentCategories = array(
                $aCurrentCategories
            );
        }
        return $this->hasActiveCategory($aCurrentCategories);
    }

    /**
     * Check that at least one of the given category ids is active
     *
     * @param int[] $aCategoryIds
     * @return bool
     */
    public function hasActiveCategory($aCategoryIds)
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
     * @return int[]
     */
    protected function getActiveCategories()
    {
        $aCurrentActiveCategories = $this->getExtensionActivatedCategoryIds();

        /**
         * Loop through each activated category ids and add children category ids
         */
        $aSubActiveCategories = $this->addCategoryChildren($aCurrentActiveCategories);
        return array_unique(array_merge($aCurrentActiveCategories, $aSubActiveCategories));
    }

    /**
     * Get an array of category ids activated via the admin config section
     *
     * @return int[]
     */
    protected function getExtensionActivatedCategoryIds()
    {
        /*
         * Category Ids are saved in the config in format
         *  - "category1,category2"
         */
        $sActivatedCategoryIds = Mage::getStoreConfig(self::CONFIG_EXTENSION_ACTIVATED_CATEGORIES);
        return explode(',', $sActivatedCategoryIds);
    }

    /**
     * From given category id load all child ids into an array
     *
     * @param int[] $aCategoryIds
     * @return int[]
     */
    protected function addCategoryChildren($aCategoryIds)
    {
        $oCategoryResource = Mage::getResourceModel('catalog/category');
        $oAdapter          = $oCategoryResource->getReadConnection();

        $oSelect = $oAdapter->select();
        $oSelect->from(array('m' => $oCategoryResource->getEntityTable()), 'entity_id');

        foreach ($aCategoryIds as $iCategoryId) {
            $oSelect->orWhere($oAdapter->quoteIdentifier('path') . ' LIKE ?', '%/' . $iCategoryId . '/%');
        }
        return $oAdapter->fetchCol($oSelect);
    }

    /**
     * From an array of all product ids get all unique entries in the product category table
     *
     * @param int[] $aProductIds
     * @param int[] $aCurrentCategories
     * @return int[]
     */
    protected function getAllCategoryIds($aProductIds, $aCurrentCategories)
    {
        $oProductResource = Mage::getResourceModel('catalog/product');
        $oAdapter         = $oProductResource->getReadConnection();

        $oSelect = $oAdapter->select();
        $oSelect->from($oProductResource->getTable('catalog/category_product'), 'category_id');
        $oSelect->where('product_id IN (?)', $aProductIds);

        return array_unique(array_merge($aCurrentCategories, $oAdapter->fetchCol($oSelect)));
    }
}