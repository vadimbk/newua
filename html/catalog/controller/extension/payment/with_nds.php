<?php
class ControllerExtensionPaymentWithNDS extends Controller {
	public function index() {
		$this->load->language('extension/payment/with_nds');

		$data['bank'] = nl2br($this->config->get('payment_with_nds_bank' . $this->config->get('config_language_id')));

		return $this->load->view('extension/payment/with_nds', $data);
	}

	public function confirm() {
		$json = array();
		
		if ($this->session->data['payment_method']['code'] == 'with_nds') {
			$this->load->language('extension/payment/with_nds');

			$this->load->model('checkout/order');

			$comment  = $this->language->get('text_instruction') . "\n\n";
			$comment .= $this->config->get('payment_with_nds_bank' . $this->config->get('config_language_id')) . "\n\n";
			$comment .= $this->language->get('text_payment');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_with_nds_order_status_id'), $comment, true);
		
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
}