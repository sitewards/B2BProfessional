<?php

/**
 * Sitewards_B2BProfessional_Helper_Core
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Core extends Mage_Core_Helper_Abstract
{
    /**
     * Get a store flag value and set to against the object
     *
     * @param string $sStoreFlagPath
     * @param string $sStoreFlagAttribute
     * @return bool
     */
    public function getStoreFlag($sStoreFlagPath, $sStoreFlagAttribute)
    {
        if ($this->$sStoreFlagAttribute === null) {
            $this->$sStoreFlagAttribute = Mage::getStoreConfigFlag($sStoreFlagPath);
        }
        return $this->$sStoreFlagAttribute;
    }
}