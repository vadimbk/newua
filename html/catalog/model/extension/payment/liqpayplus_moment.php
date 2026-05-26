<?php
// All rights reserved ART&PR studio -> https://store.pe-art.ru
class ModelExtensionPaymentLiqpayplusmoment extends Model {
	private $pname 			 = 'liqpayplus_moment';
    private $StartPname      = 'liqpayplus';
    private $extclass        = 'payment';
    private $ext_name        = 'extension_'; // ''
    private $ext_folder      = 'extension/'; // ''

	public function getMethod($address, $total) {
		$this->load->model($this->ext_folder.$this->extclass.'/'.$this->StartPname);
		$method_data =$this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->StartPname}->secondmodel($address, $total, $this->pname);
		return $method_data;
	}
		
}
?>