<?php
// All rights reserved ART&PR studio -> https://store.pe-art.ru
class ControllerExtensionPaymentLiqpaypluscard extends Controller {
	
	private $error = array();
	private $pname = 'liqpayplus_card';
	public function index() {
    
		$this->load->controller('extension/payment/liqpayplus', array('name' => $this->pname));

	}
}
?>