<?php
/**
 * Sitewards_B2BProfessional_Model_Observer
 * 	- Observer to catch the following actions
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Model_Observer {
	/**
	 * The last product Id
	 *
	 * @var int
	 */
	protected static $_iLastProductId = 0;

	/**
	 * blocks which display prices
	 *
	 * @var array
	 */
	protected $aPriceBlockClassNames = array(
		'Mage_Catalog_Block_Product_Price'        => 1,
		'Mage_Bundle_Block_Catalog_Product_Price' => 1,
	);

	/**
	 * Check if the site requires login to work
	 * 	- Add notice,
	 * 	- Redirect to the home page,
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function onControllerActionPreDispatch(Varien_Event_Observer $oObserver) {
		/* @var $oHelper Sitewards_B2BProfessional_Helper_Data */
		$oHelper = Mage::helper('b2bprofessional');
		if($oHelper->isExtensionActive() == true) {
			/* @var $oControllerAction Mage_Core_Controller_Front_Action */
			$oControllerAction = $oObserver->getData('controller_action');

			/* @var $oB2BMessagesHelper Sitewards_B2BProfessional_Helper_Messages */
			$oB2BMessagesHelper = Mage::helper('b2bprofessional/messages');
			/* @var $oB2BCustomerHelper Sitewards_B2BProfessional_Helper_Customer */
			$oB2BCustomerHelper = Mage::helper('b2bprofessional/customer');

			/*
			 * Check to see if the system requires a login
			 * And there is no logged in user
			 */
			if($oB2BCustomerHelper->isLoginRequired() == true && !Mage::getSingleton('customer/session')->isLoggedIn()) {
				/*
				 * Check to see if the controller is:
				 * 	1) Cms related for cms pages,
				 * 	2) A front action to allow for admin pages,
				 * 	3) Customer account to allow for login
				 * 	4) Magento API to allow api requests
				 */
				if(
					!$oControllerAction instanceof Mage_Cms_IndexController
						&&
					!$oControllerAction instanceof Mage_Cms_PageController
						&&
					$oControllerAction instanceof Mage_Core_Controller_Front_Action
						&&
					!$oControllerAction instanceof Mage_Customer_AccountController
					&&
					!$oControllerAction instanceof Mage_Api_Controller_Action
				){
					// Redirect to the homepage
					/* @var $oResponse Mage_Core_Controller_Response_Http */
					$oResponse = $oControllerAction->getResponse();
					$oResponse->setRedirect(Sitewards_B2BProfessional_Helper_Redirects::getRedirect(Sitewards_B2BProfessional_Helper_Redirects::REDIRECT_TYPE_LOGIN));

					/*
					 * Add message to the session
					 * 	- Note:
					 * 		We need session_write_close otherwise the messages get lots in redirect
					 */
					/* @var $oSession Mage_Core_Model_Session */
					$oSession = Mage::getSingleton('core/session');
					$oSession->addNotice($oB2BMessagesHelper->getMessage($oB2BMessagesHelper::MESSAGE_TYPE_LOGIN));
					session_write_close();
				}
			/*
			 * On Multishipping or Onepage actions
			 *  - validate that the cart is valid
			 *  - if not redirect the user to the account section and display message
			 */
			} elseif(
				$oControllerAction instanceof Mage_Checkout_MultishippingController
				||
				$oControllerAction instanceof Mage_Checkout_OnepageController
			) {
				if (!$oHelper->hasValidCart()) {
					// Stop the default action from being dispatched
					$oControllerAction->setFlag('', 'no-dispatch', true);
					//Set the appropriate error message to the user session
					Mage::getSingleton('customer/session')->addError($oB2BMessagesHelper->getMessage($oB2BMessagesHelper::MESSAGE_TYPE_CHECKOUT));
					//Redirect to the account login url
					Mage::app()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'))->sendHeaders();
				}
			/*
			 * On Cart action
			 *  - validate that the cart is valid
			 *  - add message to the checkout session
			 */
			} elseif($oControllerAction instanceof Mage_Checkout_CartController) {
				if (!$oHelper->hasValidCart()) {
					Mage::getSingleton('checkout/session')->addError($oB2BMessagesHelper->getMessage($oB2BMessagesHelper::MESSAGE_TYPE_CHECKOUT));
				}
			}
		}
	}

	/**
	 * checks if the block represents a price block
	 *
	 * @param Mage_Core_Block_Abstract $oBlock
	 * @return bool
	 */
	protected function isExactlyPriceBlock($oBlock)
	{
		return ($oBlock && isset($this->aPriceBlockClassNames[get_class($oBlock)]));
	}

	/**
	 * Check for block Mage_Catalog_Block_Product_Price
	 * 	- Check the product is active via the Sitewards_B2BProfessional_Helper_Data
	 * 	- Replace the text with that on the b2bprofessional
	 *
	 * @param Varien_Event_Observer $oObserver
	 * @return string
	 */
	public function onCoreBlockAbstractToHtmlAfter(Varien_Event_Observer $oObserver) {
		$oBlock = $oObserver->getData('block');
		$oTransport = $oObserver->getData('transport');

		/* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
		$oB2BHelper = Mage::helper('b2bprofessional');
		/* @var $oB2BMessagesHelper Sitewards_B2BProfessional_Helper_Messages */
		$oB2BMessagesHelper = Mage::helper('b2bprofessional/messages');
		/* @var $oB2BReplacementsHelper Sitewards_B2BProfessional_Helper_Replacements */
		$oB2BReplacementsHelper = Mage::helper('b2bprofessional/replacements');

		/*
		 * Check to see if we should remove the product price
		 */
		if($this->isExactlyPriceBlock($oBlock)) {
			$oProduct = $oBlock->getProduct();
			$iCurrentProductId = $oProduct->getId();

			if ($oB2BHelper->isProductActive($iCurrentProductId)) {
				// To stop duplicate information being displayed validate that we only do this once per product
				if ($iCurrentProductId != self::$_iLastProductId) {
					self::$_iLastProductId = $iCurrentProductId;

					$oTransport->setHtml($oB2BMessagesHelper->getMessage($oB2BMessagesHelper::MESSAGE_TYPE_PRICE));
				} else {
					$oTransport->setHtml('');
				}
				// Set type id to combined to stop tax being displayed via Symmetrics_TweaksGerman_Block_Tax
				if (
					Mage::helper('core')->isModuleEnabled('Symmetrics_TweaksGerman')
					&& $oProduct->getTypeId() == 'bundle'
				){
					$oProduct->setTypeId('combined');
				}
			}
		/*
		 * Check to see if we should remove the add to cart button on the product page
		 */
		} elseif(
			$oBlock instanceof Mage_Catalog_Block_Product_View
			&&
			$oBlock->getNameInLayout() == 'product.info.addtocart'
		) {
			$iCurrentProductId = $oBlock->getProduct()->getId();
			if ($oB2BReplacementsHelper->isAddToCartToBeReplaced($iCurrentProductId)) {
				$oTransport->setHtml('');
			}
		/*
		 * Check to see if we should remove the add to cart button on the wishlist item
		 */
		} elseif (
			$oBlock instanceof Mage_Wishlist_Block_Customer_Wishlist_Item_Column_Cart
		) {
			$iCurrentProductId = $oBlock->getItem()->getProduct()->getId();
			if ($oB2BReplacementsHelper->isAddToCartToBeReplaced($iCurrentProductId)) {
				$oTransport->setHtml($oB2BMessagesHelper->getMessage($oB2BMessagesHelper::MESSAGE_TYPE_PRICE));
			}
		/*
		 * Check to see if we should remove totals and actions from the cart
		 */
		} elseif(
			$oBlock instanceof Mage_Checkout_Block_Cart_Totals
			||
			$oBlock instanceof Mage_Checkout_Block_Onepage_Link
			||
			$oBlock instanceof Mage_Checkout_Block_Multishipping_Link
		) {
			/*
			 * If the current cart is not valid
			 *  - remove the block html
			 */
			if (!$oB2BHelper->hasValidCart()) {
				$oTransport->setHtml('');
			}
		/*
		 * Check to see if we should replace totals and actions from the cart sidebar
		 */
		} elseif (
			$oBlock instanceof Mage_Checkout_Block_Cart_Sidebar
		) {
			$aSections = array(
				'cart_sidebar_totals',
				'cart_sidebar_actions'
			);
			$sOriginalHtml = $oB2BReplacementsHelper->replaceSections($aSections, $oTransport->getHtml());
			$oTransport->setHtml($sOriginalHtml);
		/*
		 * Check to see if we should replace item price from the cart
		 */
		} elseif (
			$oBlock instanceof Mage_Checkout_Block_Cart_Item_Renderer
		) {
			$iProductId = $oBlock->getItem()->getProductId();
			$aSections = array(
				'cart_item_price'
			);
			$sOriginalHtml = $oB2BReplacementsHelper->replaceSectionsByProductId($aSections, $oTransport->getHtml(), $iProductId);
			$oTransport->setHtml($sOriginalHtml);
		/*
		 * Check to see if we should replace the add to cart button on product blocks
		 */
		} elseif (
			$oBlock instanceof Mage_Catalog_Block_Product_Abstract
			||
			$oBlock instanceof Mage_Catalog_Block_Product_Compare_Abstract
		) {
			$aSections = array(
				'add_to_cart'
			);
			// Check for the block's product so we can filter on product id
			$oCurrentProduct = $oBlock->getProduct();
			if(!is_null($oCurrentProduct)) {
				$iCurrentProductId = $oBlock->getProduct()->getId();
				$sOriginalHtml = $oB2BReplacementsHelper->replaceSectionsByProductId($aSections, $oTransport->getHtml(), $iCurrentProductId);
			} else {
				$sOriginalHtml = $oB2BReplacementsHelper->replaceSections($aSections, $oTransport->getHtml());
			}
			$oTransport->setHtml($sOriginalHtml);
		}
	}

	/**
	 * On the event core_block_abstract_to_html_before
	 * 	 - Check extension is active
	 * 	 - Check for the block type Mage_Catalog_Block_Product_List_Toolbar
	 * 	 - Remove the price order
	 * 	 - Check for the block type Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Checkbox
	 * 	 - Check for the block type Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi
	 * 	 - Check for the block type Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Radio
	 * 	 - Check for the block type Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Select
	 * 	 - Rewrite bundle options price templates
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function onCoreBlockAbstractToHtmlBefore(Varien_Event_Observer $oObserver) {
		$oBlock = $oObserver->getData('block');

		if (Mage::helper('b2bprofessional')->isActive()) {
			if($oBlock instanceof Mage_Catalog_Block_Product_List_Toolbar) {
				$oBlock->removeOrderFromAvailableOrders('price');
			}
		}
		/*
		 * Used isExtensionActive because isActive returns false
		 * for bundle product which is not under active category
		 */
		if (Mage::helper('b2bprofessional')->isExtensionActive()) {

			if (version_compare(Mage::getVersion(), '1.8.0.0') >= 0){
				if ($oBlock instanceof Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Checkbox) {
					$oBlock->setTemplate('sitewards/b2bprofessional/catalog/product/view/type/bundle/option-post-180/checkbox.phtml');
				} else if ($oBlock instanceof Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi) {
					$oBlock->setTemplate('sitewards/b2bprofessional/catalog/product/view/type/bundle/option-post-180/multi.phtml');
				} else if ($oBlock instanceof Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Radio) {
					$oBlock->setTemplate('sitewards/b2bprofessional/catalog/product/view/type/bundle/option-post-180/radio.phtml');
				} else if ($oBlock instanceof Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Select) {
					$oBlock->setTemplate('sitewards/b2bprofessional/catalog/product/view/type/bundle/option-post-180/select.phtml');
				}
			} else {
				if ($oBlock instanceof Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Checkbox) {
					$oBlock->setTemplate('sitewards/b2bprofessional/catalog/product/view/type/bundle/option-pre-180/checkbox.phtml');
				} else if ($oBlock instanceof Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi) {
					$oBlock->setTemplate('sitewards/b2bprofessional/catalog/product/view/type/bundle/option-pre-180/multi.phtml');
				} else if ($oBlock instanceof Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Radio) {
					$oBlock->setTemplate('sitewards/b2bprofessional/catalog/product/view/type/bundle/option-pre-180/radio.phtml');
				} else if ($oBlock instanceof Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Select) {
					$oBlock->setTemplate('sitewards/b2bprofessional/catalog/product/view/type/bundle/option-pre-180/select.phtml');
				}
			}
		}
	}

	/**
	 * On the event catalog_product_type_configurable_price
	 * Set the Configurable price of a product to 0 to stop the changed price showing up in the drop down
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function onCatalogProductTypeConfigurablePrice(Varien_Event_Observer $oObserver) {
		$oProduct = $oObserver->getData('product');
		/* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
		$oB2BHelper = Mage::helper('b2bprofessional');

		if ($oB2BHelper->isProductActive($oProduct->getId())) {
			$oProduct->setConfigurablePrice(0);
		}
	}

	/**
	 * If we have a Mage_Catalog_Block_Layer_View
	 *	 - remove the price attribute
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function onCoreLayoutBlockCreateAfter(Varien_Event_Observer $oObserver) {
		$oBlock = $oObserver->getData('block');
		if($oBlock instanceof Mage_Catalog_Block_Layer_View) {
			/* @var $oB2BHelper Sitewards_B2BProfessional_Helper_Data */
			$oB2BHelper = Mage::helper('b2bprofessional');

			/*
			 * Get all possible category filters
			 * Assign to value b2bprof_category_filters to be used in
			 * Sitewards_B2BProfessional_Helper_Category->isCategoryActive
			 */
			/* @var $oCategoryFilter Mage_Catalog_Block_Layer_Filter_Category */
			$oCategoryFilter = $oBlock->getChild('category_filter');
			if($oCategoryFilter instanceof Mage_Catalog_Block_Layer_Filter_Category) {
				$oCategories = $oCategoryFilter->getItems();
				$aCategoryOptions = array();
				foreach($oCategories as $oCategory) {
					/* @var $oCategory Mage_Catalog_Model_Layer_Filter_Item */
					$iCategoryId = $oCategory->getValue();
					$aCategoryOptions[] = $iCategoryId;
				}

				if (Mage::registry('b2bprof_category_filters') !== null){
					Mage::unregister('b2bprof_category_filters');
				}
				Mage::register('b2bprof_category_filters', $aCategoryOptions);
			}

			if($oB2BHelper->isActive()) {
				$aFilterableAttributes = $oBlock->getData('_filterable_attributes');
				$aNewFilterableAttributes = array();
				foreach ($aFilterableAttributes as $oFilterableAttribute) {
					if($oFilterableAttribute->getAttributeCode() != 'price') {
						$aNewFilterableAttributes[] = $oFilterableAttribute;
					}
				}
				$oBlock->setData('_filterable_attributes', $aNewFilterableAttributes);
			}
		}
	}

	/**
	 * caused by some magento magic we have to save each quote item individually if we're adding multiple of them in the same request
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function onCheckoutCartProductAddAfter(Varien_Event_Observer $oObserver) {
		if (
			Mage::app()->getRequest()->getModuleName() == 'b2bprofessional'
			AND Mage::app()->getRequest()->getControllerName() == 'cart'
			AND Mage::app()->getRequest()->getActionName() == 'addmultiple'
		) {
			$oObserver->getQuoteItem()->save();
		}
	}

	/**
	 * This function is called just before $quote object get stored to database.
	 * Here, from POST data, we capture our custom field and put it in the quote object
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function saveQuoteBefore (Varien_Event_Observer $oObserver) {
		$oQuote = $oObserver->getQuote();
		$aPost = Mage::app()
			->getFrontController()
			->getRequest()
			->getPost();
		if (isset($aPost['b2bprofessional']['delivery_date'])) {
			$sVar = $aPost['b2bprofessional']['delivery_date'];
			$oQuote->setDeliveryDate($sVar);
		}
	}

	/**
	 * This function is called, just after $quote object get saved to database.
	 * Here, after the quote object gets saved in database
	 * we save our custom field in the our table created i.e sales_quote_custom
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function saveQuoteAfter (Varien_Event_Observer $oObserver) {
		$oQuote = $oObserver->getQuote();
		if ($oQuote->getDeliveryDate()) {
			$sVar = $oQuote->getDeliveryDate();
			if (!empty($sVar)) {
				$oModel = Mage::getModel('b2bprofessional/quote');
				$oModel->deleteByQuote($oQuote->getId(), 'delivery_date');
				$oModel->setQuoteId($oQuote->getId());
				$oModel->setKey('delivery_date');
				$oModel->setValue($sVar);
				$oModel->save();
			}
		}
	}

	/**
	 * When load() function is called on the quote object,
	 * we read our custom fields value from database and put them back in quote object.
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function loadQuoteAfter (Varien_Event_Observer $oObserver) {
		$oQuote = $oObserver->getQuote();
		$oModel = Mage::getModel('b2bprofessional/quote');
		$aData = $oModel->getByQuote($oQuote->getId());
		foreach ($aData as $sKey => $sValue) {
			$oQuote->setData($sKey, $sValue);
		}
	}

	/**
	 * This function is called after order gets saved to database.
	 * Here we transfer our custom fields from quote table to order table i.e sales_order_custom
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function saveOrderAfter (Varien_Event_Observer $oObserver) {
		$oOrder = $oObserver->getOrder();
		$oQuote = $oObserver->getQuote();
		if ($oQuote->getDeliveryDate()) {
			$sVar = $oQuote->getDeliveryDate();
			if (!empty($sVar)) {
				$oModel = Mage::getModel('b2bprofessional/order');
				$oModel->deleteByOrder($oOrder->getId(), 'delivery_date');
				$oModel->setOrderId($oOrder->getId());
				$oModel->setKey('delivery_date');
				$oModel->setValue($sVar);
				$oOrder->setDeliveryDate($sVar);
				$oModel->save();
			}
		}
	}

	/**
	 * This function is called when $order->load() is done.
	 * Here we read our custom fields value from database and set it in order object.
	 *
	 * @param Varien_Event_Observer $oObserver
	 */
	public function loadOrderAfter (Varien_Event_Observer $oObserver) {
		$oOrder = $oObserver->getOrder();
		$oModel = Mage::getModel('b2bprofessional/order');
		$aData = $oModel->getByOrder($oOrder->getId());
		foreach ($aData as $sKey => $sValue) {
			$oOrder->setData($sKey, $sValue);
		}
	}
}