<?php
/**
 * Sitewards_B2BProfessional_OrderController
 * implements formAction to show New Order Form
 *
 * @category    Sitewards
 * @package     Sitewards_B2BProfessional
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_B2BProfessional_OrderController extends Mage_Core_Controller_Front_Action {
	public function formAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
}