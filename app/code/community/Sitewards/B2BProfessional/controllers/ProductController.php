<?php
/**
 * Sitewards_B2BProfessional_ProductController
 * implements infoAction to validate a product request by sku
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_ProductController extends Mage_Core_Controller_Front_Action
{
    /**
     * Check customer authentication
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $sLoginUrl = Mage::helper('customer')
            ->getLoginUrl();

        if (!Mage::getSingleton('customer/session')
            ->authenticate($this, $sLoginUrl)
        ) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * gets a sku as input parameter
     * sets JSON response with product data if it is allowed to be viewed
     */
    public function infoAction()
    {
        $sSku = $this->getRequest()->getParam('sku');
        /* @var Mage_Catalog_Model_Product $oProduct */
        $oProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sSku);
        if ($this->isUserAllowed() and $oProduct) {
            if (Mage::helper('b2bprofessional')->isProductActive($oProduct->getId())) {
                $sMessage = Mage::helper('b2bprofessional')->__('Your account is not allowed to access this product.');
                $sResponse = json_encode(
                    array(
                        'result' => 1,
                        'error'  => $sMessage,
                    )
                );
            } else {
                $sResponse = json_encode(
                    array(
                        'result' => 0,
                        'sku'    => $oProduct->getSku(),
                        'name'   => $oProduct->getName(),
                        'price'  => Mage::helper('core')->currency($oProduct->getPrice()),
                        'qty'    => $oProduct->getStockItem()->getMinSaleQty(),
                    )
                );
            }
            $this->getResponse()->setHeader('Content-type', 'text/json');
            $this->getResponse()->setBody($sResponse);
        } else {
            $this->getResponse()->setHttpResponseCode(404);
        }
    }

    /**
     * checks if user is allowed to view products
     *
     * @return bool
     */
    private function isUserAllowed()
    {
        /* @var $oHelper Sitewards_B2BProfessional_Helper_Data */
        $oHelper = Mage::helper('b2bprofessional');
        $oB2BCustomerHelper = Mage::helper('b2bprofessional/customer');
        return (
            $oHelper->isExtensionActive() == true
            && (
                $oB2BCustomerHelper->isLoginRequired() == false
                || Mage::getSingleton('customer/session')->isLoggedIn()
            )
        );
    }
}
