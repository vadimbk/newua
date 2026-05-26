<?php
// All rights reserved ART&PR studio -> https://store.pe-art.ru
class ControllerExtensionPaymentLiqpaypluscard extends Controller {
	private $pname = 'liqpayplus_card';
	
	public function index() {
    
		return $this->load->controller('extension/payment/liqpayplus', array('name' => $this->pname));

	}

	public function confirm() {
    
		$this->load->controller('extension/payment/liqpayplus/confirm', array('name' => $this->pname));

	}
	
}
?>