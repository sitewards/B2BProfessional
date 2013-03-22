Sitewards B2B Professional
===============

The Sitewards B2B Extension extends your webshop with some essential functions to realize a business-to-business store. Product prices and add-to-cart functions are only available for registered customer accounts that were approved by the site administrator. All other product details remain visible for non-registered users.

Features
------------------
* Customers can register, but have to be approved to see product prices and to order products
* Prices of all product types and options are only visible for approved customers
* Consequently, the Shopping Cart and Checkout are only available for approved customers
* Multi-store: The extension can be activated for each store/view separately
* Create both, B2B and B2C stores in one Magento installation
* Optional info message (e.g. "Please login to see price") in place of price
* Multilanguage support (German and English installed, extendible to other languages)
* Activation for specific product categories
* Activation for specific customer groups
* Require login to access store
* Set page to redirect user to when they are not logged in

File list
------------------
* app\etc\modules\Sitewards_B2BProfessional.xml
	* Activate module
	* Specify community code pool
	* Set-up dependencies
		* Mage_Adminhtml
		* Mage_Bundle
		* Mage_Catalog
		* Mage_Checkout
		* Mage_Customer
		* Netzarbeiter_CustomerActivation
* app\code\community\Sitewards\B2BProfessional\etc\adminhtml.xml
	* Create magento access control list for this module
* app\code\community\Sitewards\B2BProfessional\etc\config.xml
	* Set-up block rewrites for bundle products
	* Set-up helper, block and model declaration
	* Set-up event observers for module
		* controller_action_predispatch
		* core_block_abstract_to_html_after
		* catalog_product_type_configurable_price
		* core_block_abstract_to_html_before
		* core_layout_block_create_after
	* Set-up url rewrite
		* From /checkout/cart
		* To B2BProfessional/cart/
	* Set-up frontend router
		* Front name B2BProfessional
	* Set-up translations
		* Frontend
		* Adminhtml
	* Set-up default values
		* logintext
		* errortext
		* requireloginmessage
		* requireloginredirect
* app\code\community\Sitewards\B2BProfessional\etc\system.xml
	* Create admin config tab for module
	* Assign admin config fields to sections
		* General settings
		* Language settings
		* Activate by category
		* Activate by customer group
* app\code\community\Sitewards\B2BProfessional\Block\Bundle\Catalog\Price.php
	* Override the Mage_Bundle_Block_Catalog_Product_Price _toHtml function
		* Check that the current product is active
		* Override the text with the module text
* app\code\community\Sitewards\B2BProfessional\Block\Bundle\Catalog\Product\View\Type\Bundle\Option\Checkbox.php
	* Override the Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Checkbox getSelectionTitlePrice function
		* Validate the it is active then display the selection name
	* Override the Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Checkbox getSelectionQtyTitlePrice function
		* Validate the it is active then display the selection quantity and name
* app\code\community\Sitewards\B2BProfessional\Block\Bundle\Catalog\Product\View\Type\Bundle\Option\Multi.php
	* Override the Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi getSelectionTitlePrice function
		* Validate that the selection is active and display custom information
* app\code\community\Sitewards\B2BProfessional\Block\Bundle\Catalog\Product\View\Type\Bundle\Option\Radio.php
	* Override the Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Radio getSelectionTitlePrice function
		* Validate that the selection is active and display custom information
* app\code\community\Sitewards\B2BProfessional\Block\Bundle\Catalog\Product\View\Type\Bundle\Option\Select.php
	* Override the Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Select getSelectionTitlePrice function
		* Validate that the selection is active and display custom information
* app\code\community\Sitewards\B2BProfessional\controllers\CartController.php
	* On checkout cart controller preDispatch
		* Validate that all products are active for customer/customer group,
		* Assign error message,
		* Redirect to customer login page,
* app\code\community\Sitewards\B2BProfessional\Helper\Data.php
	* checkRequireLogin
		* Check to see if the website is set-up to require a user login to view pages
	* checkAllowed
		* Check to see if the user is allowed on the current store
	* checkCategoryIsActive
		* Validate that the category of a give product is activated in the module
	* checkCustomerIsActive
		* Check that the current customer has an active group id
	* checkGlobalActive
		* Check the global active flag
	* checkLoggedIn
		* Check that a customer is logged in,
		* If they are logged in validate their account usint the checkAllowed function
	* checkActive
		* Check that the product/customer is activated
	* getActiveCategories
		* Get all active categories and allow for sub categories
	* addCategoryChildren
		* From given category id load all child ids into an array
	* getRequireLoginMessage
		* Get the require login message
	* getRequireLoginRedirect
		* Get the url of the require login redirect
* app\code\community\Sitewards\B2BProfessional\Model\Customer.php
	* Authenticate the user and display correct message
		* Check they have been confirmed
			* Validate password
		* If cusomter is not active then throw the correct exception or add system message
* app\code\community\Sitewards\B2BProfessional\Model\Observer.php
	* onControllerActionPreDispatch
		* Check if the site requires login to work
			* Add notice
			* Redirect to set redirect page
	* onCoreBlockAbstractToHtmlAfter
		* Check for block Mage_Catalog_Block_Product_Price
			* Check the product is active via the Sitewards_B2BProfessional_Helper_Data
			* Replace the text with that on the b2bprofessional
	* onCatalogProductTypeConfigurablePrice
		* On the event catalog_product_type_configurable_price
		* Set the COnfigurable price of a product to 0 to stop the changed price showing up in the drop down
	* onCoreBlockAbstractToHtmlBefore
		* On the event core_block_abstract_to_html_before
			* Check for the block type Mage_Catalog_Block_Product_List_Toolbar
			* Remove the price order when required
	* onCoreLayoutBlockCreateAfter
		* If we have a Mage_Catalog_Block_Layer_View then remove the price attribute
* app\code\community\Sitewards\B2BProfessional\Model\System\Config\Source\Category.php
	* Populate an options array with the current system categories
* app\code\community\Sitewards\B2BProfessional\Model\System\Config\Source\Page.php
	* Populate an options array with the current system cms pages and the customer login page