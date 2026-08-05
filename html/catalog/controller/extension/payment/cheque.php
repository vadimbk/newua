<?php
class ControllerExtensionPaymentCheque extends Controller {
	public function index() {
		$this->load->language('extension/payment/cheque');

		$data['payable'] = $this->config->get('payment_cheque_payable');
		$data['address'] = nl2br($this->config->get('config_address'));

		return $this->load->view('extension/payment/cheque', $data);
	}

	public function confirm() {
		$json = array();

		if ($this->session->data['payment_method']['code'] == 'cheque') {
			$this->load->model('checkout/order');

			// Cash on collection: no payment instructions in the order email
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_cheque_order_status_id'), '', true);

			$json['redirect'] = $this->url->link('checkout/success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
