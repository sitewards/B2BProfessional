<?php
/**
 * Sitewards_B2BProfessional_CartController
 *	- Add product check on the preDispatch function
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
require_once 'Mage/Checkout/controllers/CartController.php';
class Sitewards_B2BProfessional_CartController extends Mage_Checkout_CartController
{
    /**
     * On checkout cart controller preDispatch
     * 	- validate that all products are active for customer/customer group,
     * 	- assign error message,
     * 	- redirect to customer login page,
     */
    public function preDispatch()
    {
        parent::preDispatch();

        /**
         * Check customer authentication
         */

        $sLoginUrl = Mage::helper('customer')
            ->getLoginUrl();

        if (!Mage::getSingleton('customer/session')
            ->authenticate($this, $sLoginUrl)
        ) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }

        $oRequest = $this->getRequest();
        $iProductId = $oRequest->get('product');
        /* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
        $oB2BHelper = Mage::helper('b2bprofessional');

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

        if ((!empty($iProductId) && $oB2BHelper->isProductActive($iProductId)) || ! $bAllowed) {
            /* @var $oB2BMessagesHelper Sitewards_B2BProfessional_Helper_Messages */
            $oB2BMessagesHelper = Mage::helper('b2bprofessional/messages');

            $this->setFlag('', 'no-dispatch', true);
            Mage::getSingleton('customer/session')->addError(
                $oB2BMessagesHelper->getMessage($oB2BMessagesHelper::MESSAGE_TYPE_CHECKOUT)
            );
            Mage::app()->getResponse()->setRedirect(Sitewards_B2BProfessional_Helper_Redirects::getRedirect(Sitewards_B2BProfessional_Helper_Redirects::REDIRECT_TYPE_ADD_TO_CART))->sendHeaders();
        }
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
