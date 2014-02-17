<?php
/**
 * Sitewards_B2BProfessional_CartController
 *    - Add product check on the preDispatch function
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
require_once 'Mage/Checkout/controllers/CartController.php';

class Sitewards_B2BProfessional_CartController extends Mage_Checkout_CartController
{
    /**
     * On checkout cart controller preDispatch
     *    - validate that all products are active for customer/customer group,
     *    - assign error message,
     *    - redirect to customer login page,
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        parent::preDispatch();

        /* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
        $oB2BHelper = Mage::helper('b2bprofessional');
        /*
         * Let's check if the extension is active before we do anything funny
         */
        if ($oB2BHelper->isExtensionActive()) {
            /**
             * Check customer is logged in
             *  - If it is activated in the extension
             */
            if (
                Mage::helper('b2bprofessional/customer')->isLoginRequired()
                &&
                !Mage::getSingleton('customer/session')->isLoggedIn()
            ) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            }

            $oRequest = $this->getRequest();
            $iProductId = $oRequest->get('product');

            // check for grouped products
            $bAllowed = true;
            $aMultiProducts = $oRequest->getPost('super_group');
            if (!empty($aMultiProducts)) {
                foreach ($aMultiProducts as $iMultiProductId => $iMultiProductValue) {
                    if ($iMultiProductValue > 0) {
                        if ($oB2BHelper->isProductActive($iMultiProductId)) {
                            $bAllowed = false;
                        }
                    }
                }
            }

            $bHasProduct = !empty($iProductId);
            $bIsProductActive = $oB2BHelper->isProductActive($iProductId);
            if (
                ($bHasProduct && $bIsProductActive)
                ||
                !$bAllowed
            ) {
                /* @var $oB2BMessagesHelper Sitewards_B2BProfessional_Helper_Messages */
                $oB2BMessagesHelper = Mage::helper('b2bprofessional/messages');

                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                Mage::getSingleton('customer/session')->addError(
                    $oB2BMessagesHelper->getMessage($oB2BMessagesHelper::MESSAGE_TYPE_CHECKOUT)
                );
                Mage::app()->getResponse()->setRedirect(
                    Sitewards_B2BProfessional_Helper_Redirects::getRedirect(
                        Sitewards_B2BProfessional_Helper_Redirects::REDIRECT_TYPE_ADD_TO_CART
                    )
                )->sendHeaders();
            }
        }
        return $this;
    }

    /**
     * adds multiple products to cart
     */
    public function addmultipleAction()
    {
        $oRequest = $this->getRequest();
        $aSkus = $oRequest->getParam('sku');
        $aQtys = $oRequest->getParam('qty');
        $bSuccess = false;

        $oQuote = $this->_getQuote();
        foreach ($aSkus as $iKey => $sSku) {
            $oProduct = Mage::getModel('catalog/product');
            $oProduct->load($oProduct->getIdBySku($sSku));
            if ($oProduct->getId() && !Mage::helper('b2bprofessional')->isProductActive($oProduct->getId())) {
                $oRequest->setParam('product', $oProduct->getId());
                $iQty = isset($aQtys[$iKey]) ? $aQtys[$iKey] : 1;
                $oRequest->setParam('qty', $iQty);
                $this->addAction();
                $bSuccess = true;
            }
        }
        if ($bSuccess) {
            $oQuote->save();
        } else {
            Mage::getSingleton('customer/session')->addError(
                $this->__('Please enter valid product sku.')
            );
            $this->_redirect('b2bprofessional/order/form');
        }
    }
}
