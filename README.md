B2BProfessional
===============

The Sitewards B2B Extension extends your webshop with some essential functions to realize a B2B Store

Sitewards B2B Professional
===============

The Sitewards B2B Extension extends your webshop with some essential functions to realize a business-to-business store. Product prices and add-to-cart functions are only available for registered customer accounts that were approved by the site administrator. All other product details remain visible for non-registered users.

Features of the B2BProfessional Extension
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

B2BProfessional Extension File list
------------------
* app\etc\modules\Sitewards_B2BProfessional.xml
* app\code\community\Sitewards\B2BProfessional\etc\adminhtml.xml
* app\code\community\Sitewards\B2BProfessional\etc\config.xml
* app\code\community\Sitewards\B2BProfessional\etc\system.xml
* app\code\community\Sitewards\B2BProfessional\Block\Bundle\Catalog\Price.php
* app\code\community\Sitewards\B2BProfessional\Block\Bundle\Catalog\Product\View\Type\Bundle\Option\Checkbox.php
* app\code\community\Sitewards\B2BProfessional\Block\Bundle\Catalog\Product\View\Type\Bundle\Option\Multi.php
* app\code\community\Sitewards\B2BProfessional\Block\Bundle\Catalog\Product\View\Type\Bundle\Option\Radio.php
* app\code\community\Sitewards\B2BProfessional\Block\Bundle\Catalog\Product\View\Type\Bundle\Option\Select.php
* app\code\community\Sitewards\B2BProfessional\controllers\CartController.php
* app\code\community\Sitewards\B2BProfessional\Helper\Data.php
* app\code\community\Sitewards\B2BProfessional\Model\Customer.php
* app\code\community\Sitewards\B2BProfessional\Model\Observer.php
* app\code\community\Sitewards\B2BProfessional\Model\System\Config\Source\Category.php
* app\code\community\Sitewards\B2BProfessional\Model\System\Config\Source\Page.php