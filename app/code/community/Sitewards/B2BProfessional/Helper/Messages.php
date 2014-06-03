<?php

/**
 * Class Sitewards_B2BProfessional_Helper_Messages
 *  - contains each message type constant
 *      - checkout = 0
 *      - price = 1
 *      - login = 2
 *  - contains an array with all messages
 *  - configuration path to override message
 *  - default message
 *  - function that takes in message type and returns the valid message
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2014 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_Helper_Messages extends Mage_Core_Helper_Abstract
{
    /**
     * Path for the config for extension language overridden
     */
    const CONFIG_EXTENSION_LANGUAGE_OVERRIDE = 'b2bprofessional/languagesettings/languageoverride';

    /**
     * Array id for the checkout message
     */
    const MESSAGE_TYPE_CHECKOUT = 0;

    /**
     * Array id for the price message
     */
    const MESSAGE_TYPE_PRICE = 1;

    /**
     * Array id for the login message
     */
    const MESSAGE_TYPE_LOGIN = 2;

    /**
     * Array containing
     *   all the message config paths
     *   the default message for each type
     *
     * @var array
     */
    protected $aMessages = array(
        self::MESSAGE_TYPE_CHECKOUT => array(
            'config' => 'errortext',
            'default' => 'Your account is not allowed to access this store.'
        ),
        self::MESSAGE_TYPE_PRICE => array(
            'config' => 'logintext',
            'default' => 'Please login'
        ),
        self::MESSAGE_TYPE_LOGIN => array(
            'config' => 'requireloginmessage',
            'default' => 'You do not have access to view this store.'
        )
    );

    /**
     * Variable to check if language override is active
     *
     * @var bool
     */
    protected $_isLanguageOverridden;

    /**
     * The getMessage function will for a given message type
     *   - check if the language override flag has been set via the admin config
     *   - if override is set it will load the message from the admin config
     *   - else it will load and translate the default message from the $_aMessages array
     *
     * @param int $iMessageType
     * @return string
     */
    public function getMessage($iMessageType)
    {
        if ($this->isLanguageOverridden()) {
            $sMessage = Mage::getStoreConfig(
                'b2bprofessional/languagesettings/' . $this->aMessages[$iMessageType]['config']
            );
        } else {
            $sMessage = $this->__($this->aMessages[$iMessageType]['default']);
        }
        return $sMessage;
    }

    /**
     * Is the language override activated
     *
     * @return bool
     */
    private function isLanguageOverridden() {
        if (is_null($this->_isLanguageOverridden)) {
            $this->_isLanguageOverridden = Mage::getStoreConfigFlag(self::CONFIG_EXTENSION_LANGUAGE_OVERRIDE);
        }
        return $this->_isLanguageOverridden;
    }
}