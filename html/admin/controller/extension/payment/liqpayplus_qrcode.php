<?php
// All rights reserved ART&PR studio -> https://store.pe-art.ru
class ControllerExtensionPaymentLiqpayplusqrcode extends Controller {
	
	private $error = array();
	private $pname = 'liqpayplus_qrcode';
	public function index() {
    
		$this->load->controller('extension/payment/liqpayplus', array('name' => $this->pname));

	}
}
?>