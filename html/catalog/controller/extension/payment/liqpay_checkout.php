<?php
class ControllerExtensionPaymentLiqPayCheckout extends Controller {
  public function index() {
    $data['button_confirm'] = $this->language->get('button_confirm');
    $order_id = $this->session->data['order_id'];

    $this->load->model('checkout/order');

	$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

      $version     = '3';
      $description = $this->config->get('config_name') . ', Заказ №' . $this->session->data['order_id'] . ', Email: ' . $order_info['email'];
      $result_url  = $this->url->link('extension/payment/liqpay_checkout/callback', '', true);
      $server_url  = $this->url->link('extension/payment/liqpay_checkout/callback', '', true);
      $private_key = $this->config->get('payment_liqpay_checkout_signature');
      $public_key  = $this->config->get('payment_liqpay_checkout_merchant');
      $action      = 'pay';

      $currency = $order_info['currency_code'];
      if ($currency == 'RUR') { $currency = 'RUB'; }
      $amount = $this->currency->format(
          $order_info['total'],
          $order_info['currency_code'],
          $order_info['currency_value'],
          false
      );

      $pay_way  = $this->config->get('payment_liqpay_checkout_pay_way');
      $language = $this->config->get('payment_liqpay_checkout_language');

      $send_data = array('version'    => $version,
                        'public_key'  => $public_key,
                        'amount'      => $amount,
                        'currency'    => $currency,
                        'description' => $description,
                        'order_id'    => $order_id,
                        'action'      => $action,
                        'language'    => $language,
                        'server_url'  => $server_url,
                        'result_url'  => $result_url);

      $pay_way  = $this->config->get('payment_liqpay_checkout_pay_way');
      if(isset($pay_way)){
        $send_data['pay_way'] = $pay_way;
      }

      $liqpay_data       = base64_encode(json_encode($send_data));
      $liqpay_signature  = base64_encode(sha1($private_key.$liqpay_data.$private_key, 1));

      $data['data']      = $liqpay_data;
      $data['signature'] = $liqpay_signature;
      $data['action']    = $this->config->get('payment_liqpay_checkout_api');
	  
	  $this->language->load('extension/payment/liqpay_checkout');
	  // $data['text_header'] = $this->language->get('text_header');
	  // $data['text_instruction'] = $this->language->get('text_instruction');
	  
	  return $this->load->view('extension/payment/liqpay_checkout', $data);
  }

  public function callback() {
		
    $this->load->model('checkout/order');
	  
    if ($this->request->post) {
	
      $data = $this->request->post['data'];
      $parsed_data = json_decode(base64_decode($data), true);
	
      $signature = base64_encode(sha1($this->config->get('payment_liqpay_checkout_signature') . $data . $this->config->get('payment_liqpay_checkout_signature'), true));
        
      $order_id    = $parsed_data['order_id'];
      $status      = $parsed_data['status'];

      if ($signature == $this->request->post['signature'] && $status == 'success') {
        $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_liqpay_checkout_order_status_id'));
        $this->response->redirect($this->url->link('checkout/success', '', true));
      } else if ($status == 'failure' || $status == 'error') {
        $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_liqpay_checkout_order_status_failed_id'));
        $this->response->redirect($this->url->link('checkout/failure', '', true));
      } else {
        $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_liqpay_checkout_order_status_wait_id'));
        $this->response->redirect($this->url->link('checkout/failure', '', true));
      }
    }
	
  }
	
}
