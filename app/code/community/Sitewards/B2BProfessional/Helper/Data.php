<?php

class Sitewards_B2BProfessional_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path for the config for extension active status
     */
    const CONFIG_EXTENSION_ACTIVE = 'b2bprofessional/generalsettings/active';

    /**
     * Variable for if the extension is active
     *
     * @var bool
     */
    protected $_isExtensionActive;

    /**
     * Check to see if the extension is active
     *
     * @return bool
     */
    public function isExtensionActive() {
        if (empty($this->_isExtensionActive)) {
            $this->_isExtensionActive = Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_ACTIVE);
        }
        return $this->_isExtensionActive;
    }

    /**
     * Check to see if the given product is active
     *  - In this case active means product behaves as normal in a magento shop
     *
     * @param Mage_Catalog_Model_Product $oProduct
     * @return bool
     */
    public function isProductActive(Mage_Catalog_Model_Product $oProduct)
    {
        $bIsProductActive = true;
        if ($this->isExtensionActive() === true) {
            $bIsProductActive = $this->isCustomerActive();
        }

        return $bIsProductActive;
    }

    /**
     * Check to see if the customer is active
     *  - If customer is not logged in than they are not active
     *
     * @return bool
     */
    public function isCustomerActive()
    {
        $bIsCustomerActive = true;
        if (Mage::helper('customer')->isLoggedIn() === false) {
            $bIsCustomerActive = false;
        }

        return $bIsCustomerActive;
    }
}