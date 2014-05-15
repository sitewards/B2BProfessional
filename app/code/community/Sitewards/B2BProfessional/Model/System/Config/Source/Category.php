<?php

/**
 * Sitewards_B2BProfessional_Model_System_Config_Source_Category
 *    - Create an options array with the current system categories
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Model_System_Config_Source_Category extends Mage_Adminhtml_Model_System_Config_Source_Category
{
    /**
     * Populate an options array with the current system categories
     *
     * @param boolean $bAddEmpty
     * @return array
     */
    public function toOptionArray($bAddEmpty = true)
    {
        /* @var $oCategoryCollection Mage_Catalog_Model_Resource_Category_Collection */
        $oCategoryCollection = Mage::getResourceModel('catalog/category_collection');
        $oCategoryCollection->addAttributeToSelect('name')->load();

        $aCategoryOptions = array();
        if ($bAddEmpty) {
            $aCategoryOptions [] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please select at least one category --'),
                'value' => ''
            );
        }
        foreach ($oCategoryCollection as $oCategory) {
            /* @var $oCategory Mage_Catalog_Model_Category */
            $aCategoryOptions [] = array(
                'label' => $oCategory->getName(),
                'value' => $oCategory->getId()
            );
        }

        return $aCategoryOptions;
    }
}