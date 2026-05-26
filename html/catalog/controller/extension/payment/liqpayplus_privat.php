<?php
// All rights reserved ART&PR studio -> https://store.pe-art.ru
class ControllerExtensionPaymentLiqpayplusprivat extends Controller {
	private $pname = 'liqpayplus_privat';
	
	public function index() {
    
		return $this->load->controller('extension/payment/liqpayplus', array('name' => $this->pname));

	}

	public function confirm() {
    
		$this->load->controller('extension/payment/liqpayplus/confirm', array('name' => $this->pname));

	}
	
}
?>