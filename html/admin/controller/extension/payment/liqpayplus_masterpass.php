<?php
// All rights reserved ART&PR studio -> https://store.pe-art.ru
class ControllerExtensionPaymentLiqpayplusmasterpass extends Controller {
	
	private $error = array();
	private $pname = 'liqpayplus_masterpass';
	public function index() {
    
		$this->load->controller('extension/payment/liqpayplus', array('name' => $this->pname));

	}
}
?>