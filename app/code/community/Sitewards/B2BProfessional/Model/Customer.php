<?php
/**
 * Sitewards_B2BProfessional_Model_Customer
 * 	- Authenticate the user and display correct message
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2012 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Model_Customer extends Mage_Customer_Model_Customer {
	const XML_PATH_DISTRIBUTOR_EMAIL_TEMPLATE = 'customer/create_account/email_distributor_template';
	const EXCEPTION_CUSTOMER_NOT_ACTIVATED = 996;

	/**
	 * Authenticate customer,
	 * 	- Check they have been confirmed,
	 * 	- Validate password,
	 * 	- If cusomter is not active,
	 * 		- Throw correct exception or add system message,
	 *
	 * @param string $sLoginEmail        	
	 * @param string $sLoginPassword        	
	 * @return true
	 * @throws Exception
	 * 	- Customer is not confirmed and confirmation is required,
	 * 	- When password is not valid,
	 * 	- If customer is not active and the action name is not 'createpost'
	 */
	public function authenticate($sLoginEmail, $sLoginPassword) {
		/* @var $oCustomerHelper Mage_Customer_Helper_Data */
		$oCustomerHelper = Mage::helper('customer');
		$this->loadByEmail($sLoginEmail);
		if ($this->getConfirmation() && $this->isConfirmationRequired()) {
			throw new Exception($oCustomerHelper->__('This account is not confirmed.'), self::EXCEPTION_EMAIL_NOT_CONFIRMED );
		}
		if (!$this->validatePassword($sLoginPassword)) {
			throw new Exception($oCustomerHelper->__('Invalid login or password.'), self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD );
		}

		if (!$this->getData('customer_activated')) {
			if (Mage::app()->getRequest()->getActionName() == 'createpost') {
				$oSession->addSuccess($oCustomerHelper->__('Please wait for your account to be activated'));
			} else {
				throw new Exception ($oCustomerHelper->__ ('This account is not activated.'), self::EXCEPTION_CUSTOMER_NOT_ACTIVATED );
			}
		}

		Mage::dispatchEvent('customer_customer_authenticated', array(
			'model' => $this,
			'password' => $sLoginPassword 
		));

		return true;
	}
}