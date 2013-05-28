<?php
/**
 * This only exists for historical reasons:
 * - B2BProfessional extended catalog/product_price
 * - germansetup extended B2BProfessionals catalog/product_price
 * - we improved the B2BProfessional code, so that part is now done by observers and removed the rewrite
 * - when this class is removed germansetup will be broken
 */
class Sitewards_B2BProfessional_Block_Product_Price extends Mage_Catalog_Block_Product_Price {

}