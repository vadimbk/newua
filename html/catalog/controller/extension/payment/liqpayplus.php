<?php
// All rights reserved ART&PR studio -> https://store.pe-art.ru
class ControllerExtensionPaymentLiqpayplus extends Controller {
    private $pname           = 'liqpayplus';
    private $proname         = 'liqpaypro';
    private $extclass        = 'payment';
    private $ext_name        = 'extension_'; // ''
    private $ext_folder      = 'extension/'; // ''
    private $pnameplus       = 'payment_'; // 'payment_'
    private $token_name      = 'user_token'; // user_token
    private $ssl             = true; // 'SSL'

	public function index ($payname = array('name' => 'liqpayplus')) {
		$pname = isset($payname['name']) ? $payname['name'] : $this->pname;
		$this->load->model('checkout/order');
		$this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);
		$this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
		$this->language->load($this->ext_folder.$this->extclass.'/' . $pname);
		$data['instructionat'] = $this->config->get($this->pnameplus.$pname.'_instruction_attach');
		$data['btnlater'] = $this->config->get($this->pnameplus.$pname.'_button_later');
		$data['text_loading'] = $this->language->get('text_loading');
		$data['pname'] = $pname;
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

	  	$data['continue'] = $this->url->link('checkout/success', '', $this->ssl);

		if ($this->config->get($this->pnameplus.$pname.'_createorder_or_notcreate')){
			if ($this->config->get($this->pnameplus.$pname.'_otlog') == 'stock'){
				if ($this->cart->hasStock()) {
					$data['notcreate'] = 'notcreate';
				}
			}
			else{
				$data['notcreate'] = 'notcreate';
			}
		}

		if ($this->config->get($this->pnameplus.$pname.'_otlog') == 'stock'){
			if ($this->cart->hasStock()) {
				$data['pay_url'] = htmlspecialchars_decode($this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, '~payhref~').'&first=1');
			}
			else{
				$data['pay_url'] = $this->url->link('checkout/success', '', $this->ssl);
			}
		}
		else if ($this->config->get($this->pnameplus.$pname.'_otlog') == 'pay'){
			$data['pay_url'] = $this->url->link('checkout/success', '', $this->ssl);
		}
		else{
			$data['pay_url'] = htmlspecialchars_decode($this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, '~payhref~').'&first=1');
		}

		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['payment_url'] = $this->url->link('checkout/success', '', $this->ssl);
		$data['button_later'] = $this->language->get('button_pay_later');

		if ($this->config->get($this->pnameplus.$pname.'_instruction_attach')){
			$data['text_instruction'] = $this->language->get('text_instruction');
			$data['instr'] = htmlspecialchars_decode($this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, $this->config->get($this->pnameplus.$pname.'_instruction_' . $this->config->get('config_language_id'))));
		}

        return $this->load->view($this->ext_folder.$this->extclass.'/'.$this->pname, $data);
	}
	
	public function confirm($payname = array('name' => 'liqpayplus')) {
		
		if (strpos($this->session->data['payment_method']['code'], $this->pname) !== false) {
			$pname = isset($payname['name']) ? $payname['name'] : $this->pname;
			$comment = '';	
		  	$this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
		  	$this->language->load($this->ext_folder.$this->extclass.'/' . $pname);
			$this->load->model('checkout/order');
			$this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);

			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			if ($this->config->get($this->pnameplus.$pname.'_otlog') == 'stock'){
				if ($this->cart->hasStock()) {
					$ostatus = $this->config->get($this->pnameplus.$pname.'_on_status_id');
					$comment = sprintf($this->language->get('stock'), $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, '~href~'));
				}
				else{
					$ostatus = $this->config->get($this->pnameplus.$pname.'_start_status_id');
					$comment = $this->language->get('no_stock');
				}
			}
			else if ($this->config->get($this->pnameplus.$pname.'_otlog') == 'pay'){
				$ostatus = $this->config->get($this->pnameplus.$pname.'_start_status_id');
			}
			else{
				$ostatus = $this->config->get($this->pnameplus.$pname.'_on_status_id');
			}

			if ($this->config->get($this->pnameplus.$pname.'_mail_instruction_attach')){
				$comment .= htmlspecialchars_decode($this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, $this->config->get($this->pnameplus.$pname.'_mail_instruction_' . $this->config->get('config_language_id'))));
		    }

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $ostatus, $comment, true);
		}
	}

	public function go() {

        $order_info = $this->payLinkChecker($this->request->get, true);

        $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
        $this->language->load($this->ext_folder.$this->extclass.'/' . $order_info['payment_code']);
        $data['button_pay']    = $this->language->get('button_pay');
        $data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->language->get('heading_title'));
        $data['paystat'] = $order_info['paystat'];
        if ($order_info['paystat']) {

            $data['merchant_url'] = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, '~payhref~');

            if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_hrefpage_text_attach')) {
                $data['send_text'] = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, $this->config->get($this->pnameplus.$order_info['payment_code'] . '_hrefpage_text_' . $this->config->get('config_language_id')));
            } else {
                $data['send_text'] = sprintf($this->language->get('send_text'), $order_info['order_id'], $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, 'paysum-symbol'));
            }

        } else {
            $data['send_text'] = $this->language->get('oplachen');
        }

        $data['column_left']    = $this->load->controller('common/column_left');
        $data['column_right']   = $this->load->controller('common/column_right');
        $data['content_top']    = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view($this->ext_folder.$this->extclass.'/' . $this->pname.'_go', $data));

    }

    public function pay() {

        $order_info = $this->payLinkChecker($this->request->get, true);
        $out_summ = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, 'paysum');
    
        if (!is_numeric($out_summ)) { 
            echo 'error: no total sum';
            return;
        }

        $paydata = $this->payData($order_info, $out_summ, 'order');

        if (isset($master['error'])){
            echo $master['error'];
            return;
        }

        if (!$this->config->get($this->pnameplus.$order_info['payment_code'] . '_createorder_or_notcreate')) {
            if (isset($this->session->data['order_id'])) {
                if ($this->request->get['order_id'] == $this->session->data['order_id']) {
                    $this->cart->clear();
                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                    unset($this->session->data['guest']);
                    unset($this->session->data['comment']);
                    unset($this->session->data['order_id']);
                    unset($this->session->data['coupon']);
                    unset($this->session->data['reward']);
                    unset($this->session->data['voucher']);
                    unset($this->session->data['vouchers']);
                }
            }
        }

        if (isset($this->session->data['order_id'])) {
            if ($this->request->get['order_id'] == $this->session->data['order_id']) {
                unset($this->session->data['order_id']);
            }
        }
   
        $this->doPayRedirect($order_info, $paydata, 'order');

    }

    private function doPayRedirect($order_info, $paydata, $method) {

        $data['action'] = 'https://www.liqpay.ua/api/3/checkout';
        $this->debuger($paydata, $order_info['payment_code'], 'PAY-'.$method, $order_info['order_id']);

        $data['form_data'] = array(
            'data' => base64_encode(json_encode($paydata)),
            'signature' => $this->getSignature($order_info, $paydata),
        );

        $this->response->setOutput($this->load->view($this->ext_folder.$this->extclass.'/' . $this->pname.'_pay', $data));

    }


    private function payLinkChecker($request_data, $status = false) {

        if (!isset($request_data['code']) && !isset($request_data['order_id'])) {
            echo "No data";
            return;
        }

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($request_data['order_id']);
        if ($order_info['order_id'] == 0) {
            echo 'No order';
            return;
        }

        $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);
        $platp = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getSecureCode($order_info['order_id']);
        if ($request_data['code'] != $platp) {
            $this->response->redirect($this->url->link('error/not_found', '', $this->ssl));
        }

        if (strpos($order_info['payment_code'], $this->pname) === false) {
            $this->response->redirect($this->url->link('error/not_found'));
        }

        if (!$this->config->get($this->pnameplus.$order_info['payment_code'] . '_status')) {
            $this->response->redirect($this->url->link('error/not_found'));
        }

        if ($status) {
            $paystat = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getPaymentStatus($order_info['order_id']);
            if (!isset($paystat['status'])) {$paystat['status'] = 0;}
            $order_info['paystat'] = true;
            if ($paystat['status'] != 0) {
                $order_info['paystat'] = false;
            }
        }

        return $order_info;

    }


    private function payData($order_info, $out_summ, $method = 'order') {


        $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
        $this->language->load($this->ext_folder.$this->extclass.'/' . $order_info['payment_code']);

        $urls = $this->getUrls($method);

        $paydata = array(
            'version'     => 3,
            'public_key'  => $this->config->get($this->pnameplus.$order_info['payment_code'] . '_login'),
            'action'      => $this->config->get($this->pnameplus.$order_info['payment_code'] . '_twostage') ? 'hold' : 'pay',
            'amount'      => number_format($out_summ, 2, '.', ''),
            'currency'    => $this->config->get($this->pnameplus.$order_info['payment_code'] . '_currency_merch'),
            'description' => $this->getFormComment($order_info, $method),
            'order_id'    => $this->getOrderNumber($order_info, $method),
            'language'    => $this->getLanguage(),
            'paytypes'    => $this->config->get($this->pnameplus.$order_info['payment_code'] . '_methodcode'),
            'result_url'  => $urls['result_url'],
            'server_url'  => $urls['server_url'],
            'info'        => $this->getInfo($order_info, $method),
        );

        return $paydata;

    }

    private function getInfo($order_info, $method = 'order') {
        if ($method == 'order') {
           return $method.'~'.$order_info['payment_code'];
        }

        if ($method == 'fpay') {
           return $method.'~'.$order_info['payment_code'].'~'.$order_info['email'].'~'.$order_info['telephone'].'~'.$order_info['comment'];
        }

        if ($method == 'bpay') {
           return $method.'~'.$order_info['payment_code'].'~'.$order_info['customer'];
        }

    }


    private function getSignature($order_info, $paydata) {
        
        $private_key = $this->config->get($this->pnameplus.$order_info['payment_code'] . '_password');
        $signature = base64_encode(sha1($private_key . base64_encode(json_encode($paydata)) . $private_key, 1));

        return $signature;
    }

    private function getOrderNumber($order_info, $method = 'order') {

        if ($method == 'order') {
           return $order_info['order_id'] . '-' . time();
        }

        if ($method == 'fpay') {
            if ($order_info['customer'] == 'NO_CUSTOMER'){
                $order_info['customer'] = $order_info['customer'].rand();
            }

            return $order_info['customer'] . ' ' . time();
        }

        if ($method == 'bpay') {
           return $order_info['customer'] . '-' . time();
        }

    }

    private function getUrls($method){

        $urls = array();

        $urls['server_url'] = htmlspecialchars_decode($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname.'/callback', '', $this->ssl));

        if ($method == 'order') {
            $first = $this->firstCheck();
            $urls['result_url'] = htmlspecialchars_decode($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname.'/status', 'code='.$this->request->get['code'].'&order_id=' . $this->request->get['order_id'] . $first, $this->ssl));
        }

        if ($method == 'fpay') {
            $urls['result_url'] = htmlspecialchars_decode($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname.'/fstatus', '', $this->ssl));
        }

        if ($method == 'bpay') {
            $urls['result_url'] = htmlspecialchars_decode($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname.'/bstatus', '', $this->ssl));
        }

        return $urls;

    }

    private function getLanguage(){

        $lang = $this->session->data['language'];

        if (isset($lang)){
            if ($lang == 'ru-ru' || $lang == 'russian'  || $lang == 'ru'){
                $lang = 'ru';
            }
            else if ($lang == 'en-gb' || $lang == 'english'  || $lang == 'en'){
                $lang = 'en';
            }
            else {
                $lang = 'uk';
            }
        }
        else{
            $lang = 'uk';
        }

        return $lang;
    }

    private function getFormComment ($order_info, $method) {

        $formcomment = '';
        
        if ($method == 'order') {

            if (!$this->config->get($this->pnameplus.$order_info['payment_code'] . '_formcomment_' . $this->config->get('config_language_id'))) {
                $formcomment = sprintf($this->language->get('pay_order_text'), $order_info['order_id']);
            }
            else{
                $formcomment = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, $this->config->get($this->pnameplus.$order_info['payment_code'] . '_formcomment_' . $this->config->get('config_language_id')));
            }

        }

        if ($method == 'fpay') {

            $formcomment = sprintf($this->language->get('addfpay_comment'), $order_info['customer']);

        }

        if ($method == 'bpay') {

            $formcomment = sprintf($this->language->get('addbalance_comment'), $this->customer->getEmail());

        }

        return $formcomment;

    }

    private function firstCheck() {
        if (isset($this->request->get['first'])) {$first = '&first=1';} else { $first = '';}
        return $first;
    }

    public function status() {
        
        if (isset($this->request->post)) {
            $order_info = $this->payLinkChecker($this->request->get);
            $request_data = json_decode(base64_decode($this->request->post['data']), true);
            $this->debuger($request_data, $order_info['payment_code'], 'BACK URL ORDER ', $order_info['order_id']);
            if ($request_data['status'] == 'failure' || $request_data['status'] == 'error'  || $request_data['status'] == 'try_again') {
                if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_card_returnpage')){
                    $this->response->redirect($this->url->link('checkout/failure', '', $this->ssl));
                }
                else{
                    $this->response->redirect(htmlspecialchars_decode($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname.'/fail', 'code='.$this->request->get['code'].'&order_id=' . $order_info['order_id'] . $this->firstCheck(), $this->ssl)));
                }
            } 
            else {
                if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_card_returnpage')){
                    $this->session->data['order_id'] = $order_info['order_id'];
                    $this->response->redirect($this->url->link('checkout/success', '', $this->ssl));
                }
                else{
                    $this->response->redirect(htmlspecialchars_decode($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname.'/success', 'code='.$this->request->get['code'].'&order_id=' . $order_info['order_id'] . $this->firstCheck(), $this->ssl)));
                }
            }
        }
        else{
            echo 'No DATA';
        }
    }

    public function debuger($request, $payment_code, $func, $order, $error = false) {
        if ($error) {
            $text = 'ERROR';
        }
        else{
            $text = 'DEBUG';
        }
        if ($this->config->get($this->pnameplus.$payment_code . '_debug') || $error) {
                $this->modLog('<------------------------------------------------------------------------------->');
                $this->modLog('<----------'.$text.' '.$func.' IN PAYMENT '.$order.'-------------->');
                $this->modLog($request);
                $this->modLog('<----------'.$text.' '.$func.' IN PAYMENT '.$order.' END---------->');
        }
    }

    private function modLog($texts) {

        if (!isset($modLog)) {
            $modLog = new Log($this->pname.'.log');
        }

        $modLog->write($texts);

    }

    public function callback() {

        if (!isset($this->request->post['data']) && !isset($this->request->post['signature'])) {
            echo 'CALLBACK It\'s HERE!';
            return;
        }

        $postdata = $this->request->post['data'];
        $request_data = json_decode(base64_decode($postdata), true);

        if (!isset($request_data['info']) && !isset($request_data['order_id']) && !isset($request_data['status']) && !isset($request_data['action'])){
            return;
        }

        $label = explode("~", $request_data['info']);

        if ($request_data['status'] == 'success' && $request_data['action'] == 'pay' || $request_data['status'] == 'hold_wait' || $request_data['status'] == 'wait_accept'){

            if ($request_data['status'] == 'hold_wait') {
                $status = 2;
            }
            else{
                $status = 1;
            }

            $sign = base64_encode(sha1($this->config->get($this->pnameplus.$label[1] . '_password') . $postdata . $this->config->get($this->pnameplus.$label[1] . '_password'), 1));
            if ($sign != $this->request->post['signature']) {
                $this->debuger('HASH NOT EQUAL', $label[1], 'CALLBACK '.$label[0].' ', $request_data['order_id'], true);
                return;
            }


            $this->debuger($request_data, $label[1], 'CALLBACK-'.$label[0], $request_data['order_id']);

            $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
            $this->language->load($this->ext_folder.$this->extclass.'/' . $label[1]);

            $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);

            if ($label[0] == 'order') {

                $this->orderCall($request_data, $status); 

            }

            if ($label[0] == 'fpay') {

                $this->fpayCall($request_data, $status, $label); 

            }

            if ($label[0] == 'bpay') {

                $this->bpayCall($request_data, $status, $label); 

            }

            echo 'OK';
            return;

        }

        else{
            $this->debuger($request_data, $label[1], 'CALLBACK', $request_data['order_id'], false);
        }

    }

    private function mailAlert($subject, $text, $email, $sender, $additional = false) {

        $mail                = new Mail();
        $mail->protocol      = $this->config->get('config_mail_protocol');
        $mail->parameter     = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port     = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');
        $mail->setTo($email);
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($sender);
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
        $mail->send();

        if ($additional) {
            $emails = explode(',', $this->config->get('config_alert_email'));

            foreach ($emails as $email) {
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
    }

    private function orderCall($request_data, $status) {

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($request_data['order_id']);

        $paystat = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getPaymentStatus($order_info['order_id']);
        if (!isset($paystat['status'])) {
            $paystat['status'] = 0;
        }
        if ($paystat['status'] > 0) {
            return;
        }

        $out_summ = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, 'paysum');
        if ($request_data['amount'] != $out_summ) {
             $this->debuger('AMOUNT NOT EQUAL', $order_info['payment_code'], 'CALLBACK', $order_info['order_id'], true);
            return;
        }

        $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->setPaymentStatus($order_info, $request_data['payment_id'], $status, $out_summ, $request_data['order_id'], 1, $order_info['payment_code']);

        if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_createorder_or_notcreate') && $order_info['order_status_id'] != $this->config->get($this->pnameplus.$order_info['payment_code'] . '_on_status_id')) {

            if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_mail_instruction_attach')) {

                $comment = $this->language->get('text_instruction') . "\n\n";
                $comment .= $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, $this->config->get($this->pnameplus.$order_info['payment_code'] . '_mail_instruction_' . $order_info['language_id']));
                $comment = htmlspecialchars_decode($comment);
                $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get($this->pnameplus.$order_info['payment_code'] . '_order_status_id'), $comment, true);
            } else {
                $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get($this->pnameplus.$order_info['payment_code'] . '_order_status_id'), '', true);
            }

            if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_success_alert_customer')) {
                if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_success_comment_attach')) {
                    $message = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, $this->config->get($this->pnameplus.$order_info['payment_code'] . '_success_comment_' . $order_info['language_id']));
                    $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get($this->pnameplus.$order_info['payment_code'] . '_order_status_id'), $message, true);
                } else {
                    $message = '';
                    $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get($this->pnameplus.$order_info['payment_code'] . '_order_status_id'), $message, true);
                }
            }

        } else {

            if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_success_alert_customer')) {
                if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_success_comment_attach')) {
                    $message = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, $this->config->get($this->pnameplus.$order_info['payment_code'] . '_success_comment_' . $order_info['language_id']));
                    $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get($this->pnameplus.$order_info['payment_code'] . '_order_status_id'), $message, true);
                } else {
                    $message = '';
                    $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get($this->pnameplus.$order_info['payment_code'] . '_order_status_id'), $message, true);
                }
            } else {
                $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get($this->pnameplus.$order_info['payment_code'] . '_order_status_id'), '', false);

            }

        }

        if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_success_alert_admin')) {

            $subject = sprintf(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $order_info['order_id']);
            $text    = sprintf($this->language->get('success_admin_alert'), $order_info['order_id']) . "\n";

            $this->mailAlert($subject, $text, $this->config->get('config_email'), $order_info['store_name'], true);

        }


    }

    public function success() {
    	
        if (isset($this->request->get['order_id'])) {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->request->get['order_id']);
        } else {
            echo 'No order';
            return;
        }

        if ($this->request->get['order_id'] != $order_info['order_id']) {
            echo "No data";
            return;
        }

        $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);

        $platp = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getSecureCode($order_info['order_id']);
        if ($this->request->get['code'] != $platp) {
            $this->response->redirect($this->url->link('error/not_found', '', $this->ssl));
        }


        $data['text_message'] = '';

        $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
        $this->language->load($this->ext_folder.$this->extclass.'/' . $order_info['payment_code']);
        $data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->language->get('heading_title'));
        $data['button_continue'] = $this->language->get('button_ok');

        if (isset($this->request->get['first']) && $order_info['order_status_id'] == $this->config->get($this->pnameplus.$order_info['payment_code'] . '_on_status_id')) {
            $data['text_message'] .= $this->language->get('success_text_first');
        }
        
        if ($order_info['order_status_id'] == $this->config->get($this->pnameplus.$order_info['payment_code'] . '_order_status_id')) {

            if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_createorder_or_notcreate') && isset($this->request->get['first'])) {

                $this->cart->clear();

                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
                unset($this->session->data['guest']);
                unset($this->session->data['comment']);
                unset($this->session->data['order_id']);
                unset($this->session->data['coupon']);
                unset($this->session->data['reward']);
                unset($this->session->data['voucher']);
                unset($this->session->data['vouchers']);
            }

            if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_returnpage')) {
                $this->session->data['order_id'] = $order_info['order_id'];
                $this->response->redirect($this->url->link('checkout/success', 'order_id='.$order_info['order_id'], $this->ssl));
            }

            if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_success_page_text_attach')) {

                $data['text_message'] .= $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, $this->config->get($this->pnameplus.$order_info['payment_code'] . '_success_page_text_' . $this->config->get('config_language_id')));
            } else {
                $data['text_message'] .= sprintf($this->language->get('success_text'), $order_info['order_id']);
            }
        } else {

            if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_waiting_page_text_attach')) {
                $data['text_message'] .= $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, $this->config->get($this->pnameplus.$order_info['payment_code'] . '_waiting_page_text_' . $this->config->get('config_language_id')));
            } else {

                $online_url = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, '~href~');

                if ($order_info['order_status_id'] == $this->config->get($this->pnameplus.$order_info['payment_code'] . '_on_status_id')) {
                    $data['text_message'] .= sprintf($this->language->get('success_text_wait'), $order_info['order_id'], $online_url);
                } else {
                    $data['text_message'] .= sprintf($this->language->get('success_text_wait_noorder'), $online_url);
                }
            }
        }
        

        if ($this->customer->isLogged()) {

            if (!$this->config->get($this->pnameplus.$order_info['payment_code'] . '_createorder_or_notcreate')) {
                $data['text_message'] .= sprintf($this->language->get('success_text_loged'), $this->url->link('account/order', '', $this->ssl), $this->url->link('account/order/info&order_id=' . $order_info['order_id'], '', $this->ssl));
            } else {
                if ($order_info['order_status_id'] == $this->config->get($this->pnameplus.$order_info['payment_code'] . '_order_status_id')) {
                    $data['text_message'] .= sprintf($this->language->get('success_text_loged'), $this->url->link('account/order', '', $this->ssl), $this->url->link('account/order/info&order_id=' . $order_info['order_id'], '', $this->ssl));
                }
            }
            if ($order_info['order_status_id'] != $this->config->get($this->pnameplus.$order_info['payment_code'] . '_order_status_id')) {
                if ($order_info['order_status_id'] == $this->config->get($this->pnameplus.$order_info['payment_code'] . '_on_status_id')) {
                    $data['text_message'] .= sprintf($this->language->get('waiting_text_loged'), $this->url->link('account/order', '', $this->ssl));
                }
            }

        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', $this->ssl),
        );

        if (isset($this->request->get['first'])) {
            $this->language->load('checkout/success');
            $data['breadcrumbs'][] = array(
                'href' => $this->url->link('checkout/cart', '', $this->ssl),
                'text' => $this->language->get('text_basket'),
            );

            $data['breadcrumbs'][] = array(
                'href' => $this->url->link('checkout/checkout', '', $this->ssl),
                'text' => $this->language->get('text_checkout'),
            );
            $data['continue'] = $this->url->link('common/home', '', $this->ssl);
        } else {
            if ($this->customer->isLogged()) {
                $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('lich'),
                    'href' => $this->url->link('account/account', '', $this->ssl),
                );

                $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('history'),
                    'href' => $this->url->link('account/order', '', $this->ssl),
                );
                $data['continue'] = $this->url->link('account/order', '', $this->ssl);
            } else {
                $data['continue'] = $this->url->link('common/home', '', $this->ssl);
            }
        }

        $data['column_left']    = $this->load->controller('common/column_left');
        $data['column_right']   = $this->load->controller('common/column_right');
        $data['content_top']    = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('common/success', $data));

    }

    public function fail() {
        
        if (isset($this->request->get['order_id'])) {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->request->get['order_id']);
        } else {
            echo 'No order';
            return;
        }

        if ($this->request->get['order_id'] != $order_info['order_id']) {
            echo "No data";
            return;
        }

        $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);

        $platp = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getSecureCode($order_info['order_id']);
        if ($this->request->get['code'] != $platp) {
            $this->response->redirect($this->url->link('error/not_found', '', $this->ssl));
        }

        $data['text_message'] = '';

        $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
        $this->language->load($this->ext_folder.$this->extclass.'/' . $order_info['payment_code']);
        $data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->language->get('heading_title'));
        $data['button_continue'] = $this->language->get('button_ok');

        if (isset($this->request->get['first']) && $order_info['order_status_id'] == $this->config->get($this->pnameplus.$order_info['payment_code'] . '_on_status_id')) {
            $data['text_message'] .= $this->language->get('success_text_first');
        }

        if ($this->config->get($this->pnameplus.$order_info['payment_code'] . '_fail_page_text_attach')) {
            $data['text_message'] .= $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, $this->config->get($this->pnameplus.$order_info['payment_code'] . '_fail_page_text_' . $this->config->get('config_language_id')));
        } else {

            $online_url = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, '~href~');

            if ($order_info['order_status_id'] == $this->config->get($this->pnameplus.$order_info['payment_code'] . '_on_status_id')) {
                $data['text_message'] .= sprintf($this->language->get('fail_text_wait'), $order_info['order_id'], $online_url);
            } else {
                $data['text_message'] .= sprintf($this->language->get('fail_text_wait_noorder'), $online_url);
            }
        } 

        if ($this->customer->isLogged()) {

            if (!$this->config->get($this->pnameplus.$order_info['payment_code'] . '_createorder_or_notcreate')) {
                $data['text_message'] .= sprintf($this->language->get('success_text_loged'), $this->url->link('account/order', '', $this->ssl), $this->url->link('account/order/info&order_id=' . $order_info['order_id'], '', $this->ssl));
            } 
        
            if ($order_info['order_status_id'] == $this->config->get($this->pnameplus.$order_info['payment_code'] . '_on_status_id')) {
                $data['text_message'] .= sprintf($this->language->get('fail_text_loged'), $this->url->link('account/order', '', $this->ssl));
            }
            

        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', $this->ssl),
        );

        if (isset($this->request->get['first'])) {
            $this->language->load('checkout/success');
            $data['breadcrumbs'][] = array(
                'href' => $this->url->link('checkout/cart', '', $this->ssl),
                'text' => $this->language->get('text_basket'),
            );

            $data['breadcrumbs'][] = array(
                'href' => $this->url->link('checkout/checkout', '', $this->ssl),
                'text' => $this->language->get('text_checkout'),
            );
            $data['continue'] = $this->url->link('common/home', '', $this->ssl);
        } else {
            if ($this->customer->isLogged()) {
                $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('lich'),
                    'href' => $this->url->link('account/account', '', $this->ssl),
                );

                $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('history'),
                    'href' => $this->url->link('account/order', '', $this->ssl),
                );
                $data['continue'] = $this->url->link('account/order', '', $this->ssl);
            } else {
                $data['continue'] = $this->url->link('common/home', '', $this->ssl);
            }
        }

        $data['column_left']    = $this->load->controller('common/column_left');
        $data['column_right']   = $this->load->controller('common/column_right');
        $data['content_top']    = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('common/success', $data));
        
    }

    public function fpay() {

        $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
        $this->document->setTitle($this->language->get('fpay_heading_title'));
        $data['heading_title'] = $this->language->get('fpay_heading_title');
        $data['button_continue'] = $this->language->get('fpay_button_continue');

        $this->load->model('localisation/currency');
        $currency = $this->model_localisation_currency->getCurrencyByCode($this->session->data['currency']);
        $data['fpay_currency_code'] = $currency['code'];
        $data['text_fpay_sum'] = sprintf($this->language->get('text_fpay_sum'), $currency['title']);
        $data['entry_fpay_sum'] = $this->language->get('entry_fpay_sum');
        $data['text_fpay_method'] = $this->language->get('text_fpay_method');
        $data['text_fpay_comment'] = $this->language->get('text_fpay_comment');
        $data['text_fpay_email'] = $this->language->get('text_fpay_email');
        $data['text_fpay_phone'] = $this->language->get('text_fpay_phone');
        $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);
        $methods = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getFpayMethods();
        $data['payment_methods'] = $methods;
        $data['text_loading'] = $this->language->get('text_loading');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view($this->ext_folder.$this->extclass.'/'.$this->pname.'_fpay', $data));
    }

    public function addfpay() {

        if (isset($this->request->post['payment_code']) && isset($this->request->post['payment_sum']) && isset($this->request->post['payment_method']) ) {

            $payment_code = $this->request->post['payment_code'];
            $payment_sum = $this->request->post['payment_sum'];
            $payment_method = $this->request->post['payment_method'];

        }

        if (isset($this->request->get['payment_code']) && isset($this->request->get['payment_sum']) && isset($this->request->get['payment_method']) ) {

            $payment_code = $this->request->get['payment_code'];
            $payment_sum = $this->request->get['payment_sum'];
            $payment_method = $this->request->get['payment_method'];

        }

        if (!isset($payment_code) && !isset($payment_sum) && !isset($payment_method) ) {

            echo 'No Data';
            return;

        }


        $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);
        $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
        $this->language->load($this->ext_folder.$this->extclass.'/' . $payment_method);

        $email = '';

        if (isset($this->request->post['email']) && $this->request->post['email'] != '') {
            $email = $this->request->post['email'];
        }

        if (isset($this->request->get['email']) && $this->request->get['email'] != '') {
            $email = $this->request->get['email'];
        }

        $phone = '';

        if (isset($this->request->post['phone']) && $this->request->post['phone'] != '') {
            $phone = $this->request->post['phone'];
        }

        if (isset($this->request->get['phone']) && $this->request->get['phone'] != '') {
            $phone = $this->request->get['phone'];
        }

        $out_summ = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->curConverter($payment_sum, $payment_code, $payment_method);

        if ($email){
            $customer = $email;
        }
        else if ($phone){
            $customer = $phone;
        }
        else{
            $customer = 'NO_CUSTOMER';
        }

        $comment = '';

        if (isset($this->request->post['comment']) && $this->request->post['comment'] != '') {
            $comment = $this->request->post['comment'];
        }

        $order_info = array(
            'payment_code' => $payment_method,
            'email'        => $email,
            'telephone'    => $phone,
            'customer'     => $customer,
            'comment'      => $comment,
        );

        $paydata = $this->payData($order_info, $out_summ, 'fpay');
        $order_info['order_id'] = $paydata['order_id'];
        $this->doPayRedirect($order_info, $paydata, 'fpay');
        
    }

    private function fpayCall($request_data, $status, $label) {

        $operation_id = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getPaymentTransFStatus($request_data['payment_id']);
        if ($operation_id == $request_data['payment_id']) {
            return;
        }

        if ($label[3]){
            $user = $label[3];
        }
        else{
            $user = $label[2];
        }


        $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->savePayment(0, $request_data['amount'], $user, $label[2], $status, $request_data['order_id'], $request_data['payment_id'], $label[3], 3, $request_data['currency_debit'], $label[1]);

        if ($label[2]) {
            $subject = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
            $text = sprintf($this->language->get('addfpay_mail_customer'), $this->db->escape($request_data['payment_id']), number_format($request_data['amount'], 2, '.', ''));

            $this->mailAlert($subject, $text, $label[2], $this->config->get('config_name'), false);
        }

        $comment = '';

        if (isset($label[4]) && $label[4] != '') {
            $comment = ', '.sprintf($this->language->get('addfpay_mail_admin_comment'), $label[4]);
        }

        $subject = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
        $text = sprintf($this->language->get('addfpay_mail_admin'), $this->db->escape($request_data['payment_id']), $label[2] . ' '. $label[3], number_format($request_data['amount'], 2, '.', ''). ' ' . $request_data['currency_debit'] . $comment);

        $this->mailAlert($subject, $text, $this->config->get('config_email'), $this->config->get('config_name'), true);

    }

    public function fstatus() {
        
        if (isset($this->request->post)) {
            $request_data = json_decode(base64_decode($this->request->post['data']), true);
            $label = explode("~", $request_data['info']);
            $this->debuger($request_data, $label[1], 'BACK URL-fpay ', $request_data['order_id']);
            if ($request_data['status'] == 'failure' || $request_data['status'] == 'error'  || $request_data['status'] == 'try_again') {
                
                $this->response->redirect(htmlspecialchars_decode($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname.'/ffail', '', $this->ssl)));
                
            } 
            else {
                
                $this->response->redirect(htmlspecialchars_decode($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname.'/fsuccess', '', $this->ssl)));
            }
        }
        else{
            echo 'No DATA';
        }
    }

    public function fsuccess() {

        $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
        $this->document->setTitle($this->language->get('fpay_heading_title'));
        $data['heading_title'] = $this->language->get('fpay_heading_title');
        $data['button_continue'] = $this->language->get('button_ok');
        $data['text_message'] = $this->language->get('addfpay_success_text_message');
        $data['continue'] = $this->url->link('common/home', '', $this->ssl);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view($this->ext_folder.$this->extclass.'/'.$this->pname.'_fpay_success', $data));
    }

    public function ffail() {

        $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
        $this->document->setTitle($this->language->get('fpay_heading_title'));
        $data['heading_title'] = $this->language->get('fpay_heading_title');
        $data['button_continue'] = $this->language->get('button_ok');
        $data['text_message'] = $this->language->get('addfpay_fail_text_message');
        $data['continue'] = $this->url->link('common/home', '', $this->ssl);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view($this->ext_folder.$this->extclass.'/'.$this->pname.'_fpay_success', $data));
    }

    public function balance() {

        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/balance', '', $this->ssl);

            $this->response->redirect($this->url->link('account/login', '', $this->ssl));
        }

        $this->load->language('account/transaction');
        $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
        $this->document->setTitle($this->language->get('balance_heading_title'));
        $data['heading_title'] = $this->language->get('balance_heading_title');
        $data['button_continue'] = $this->language->get('balance_button_continue');

        $this->load->model('localisation/currency');
        $currency = $this->model_localisation_currency->getCurrencyByCode($this->session->data['currency']);
        $data['balance_currency_code'] = $currency['code'];
        $data['text_balance_sum'] = sprintf($this->language->get('text_balance_sum'), $currency['title']);
        $data['entry_balance_sum'] = $this->language->get('entry_balance_sum');
        $data['text_balance_method'] = $this->language->get('text_balance_method');
        $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);
        $methods = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getBalanceMethods();
        $data['payment_methods'] = $methods;
        $data['text_loading'] = $this->language->get('text_loading');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_transaction'),
            'href' => $this->url->link('account/transaction', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_balance'),
            'href' => $this->url->link('extension/payment/qiwiw/balance', '', true)
        );

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view($this->ext_folder.$this->extclass.'/'.$this->pname.'_balance', $data));
    }

    public function addbalance() {

        if (isset($this->request->post['payment_code']) && isset($this->request->post['payment_sum']) && isset($this->request->post['payment_method']) ) {

            $payment_code = $this->request->post['payment_code'];
            $payment_sum = $this->request->post['payment_sum'];
            $payment_method = $this->request->post['payment_method'];

        }

        if (isset($this->request->get['payment_code']) && isset($this->request->get['payment_sum']) && isset($this->request->get['payment_method']) ) {

            $payment_code = $this->request->get['payment_code'];
            $payment_sum = $this->request->get['payment_sum'];
            $payment_method = $this->request->get['payment_method'];

        }

        if (isset($payment_code) && isset($payment_sum) && isset($payment_method) ) {

            if (!$this->customer->isLogged()) {
                $this->session->data['redirect'] = $this->url->link($this->ext_folder.$this->extclass.'/'.$this->pname.'/addbalance', 'payment_code='.$payment_code.'&payment_sum='.$payment_sum.'&payment_method='.$payment_method, true);

                $this->response->redirect($this->url->link('account/login', '', true));
            }

            $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);
            $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
            $this->language->load($this->ext_folder.$this->extclass.'/' . $payment_method);
            $customer_id = $this->customer->getId();

            $out_summ = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->curConverter($payment_sum, $payment_code, $payment_method);

            $order_info = array(
                'payment_code' => $payment_method,
                'customer'     => $customer_id,
            );

            $paydata = $this->payData($order_info, $out_summ, 'bpay');
            $order_info['order_id'] = $paydata['order_id'];
            $this->doPayRedirect($order_info, $paydata, 'bpay');

        }
    }

    public function bstatus() {
        
        if (isset($this->request->post)) {
            $request_data = json_decode(base64_decode($this->request->post['data']), true);
            $label = explode("~", $request_data['info']);
            $this->debuger($request_data, $label[1], 'BACK URL-bpay ', $request_data['order_id']);
            if ($request_data['status'] == 'failure' || $request_data['status'] == 'error'  || $request_data['status'] == 'try_again') {
                
                $this->response->redirect(htmlspecialchars_decode($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname.'/ffail', '', $this->ssl)));
                
            } 
            else {
                
                $this->response->redirect(htmlspecialchars_decode($this->url->link($this->ext_folder.$this->extclass.'/' . $this->pname.'/fsuccess', '', $this->ssl)));
            }
        }
        else{
            echo 'No DATA';
        }
    }

    private function bpayCall($request_data, $status, $label) {

        $operation_id = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getPaymentTransFStatus($request_data['payment_id']);
        if ($operation_id == $request_data['payment_id']) {
            return;
        }

        $this->load->model('account/customer');
        $customer = $this->model_account_customer->getCustomer($label[2]);

        $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->savePayment(0, $request_data['amount'], $customer['firstname'] . ' ' . $customer['lastname'], $customer['email'], $status, $request_data['order_id'], $request_data['payment_id'], $label[2], 2, $request_data['currency_debit'], $label[1]);

        $transsum = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->convertTransSum($request_data['amount'], $label[1]);

        $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->saveTrans($label[2], sprintf($this->language->get('addbalance_text_transaction'), $this->db->escape($request_data['payment_id'])), $transsum);

        $subject = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
        $balance = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getBalance($label[2]);
        $balance = $this->currency->format($balance, $this->config->get('config_currency'), '', true);
        $text = sprintf($this->language->get('addbalance_mail_customer'), $balance);

        $this->mailAlert($subject, $text, $customer['email'], $this->config->get('config_name'), false);

    }

    public function amail(&$route, &$args) {

        if (isset($args[0])) {
            $order_id = $args[0];
        } else {
            $order_id = 0;
        }

        if (isset($args[1])) {
            $order_status_id = $args[1];
        } else {
            $order_status_id = 0;
        }

        if (isset($args[3])) {
            $notify = $args[3];
        } else {
            $notify = '';
        }

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
            if ($order_info['order_status_id'] && $order_status_id) {
                $this->load->model($this->ext_folder.$this->extclass.'/'.$this->pname);
                if ($this->config->get($this->pnameplus.$order_info['payment_code'].'_on_status_id') == $order_status_id && strpos($order_info['payment_code'], $this->pname) !== false) {
                    $this->language->load($this->ext_folder.$this->extclass.'/' . $this->proname);
                    $this->language->load($this->ext_folder.$this->extclass.'/' . $order_info['payment_code']);
                    $merchant_url = $this->{'model_'.$this->ext_name.$this->extclass.'_'.$this->pname}->getCustomFields($order_info, 'href');
                    $merchant_url = "<a href=' " . $merchant_url . "'>" . $merchant_url . "</a>";
                    $merchant_url = strip_tags(html_entity_decode($merchant_url, ENT_QUOTES, 'UTF-8'));
                    $message      = sprintf($this->language->get('text_stat'), $merchant_url);

                    $args[2] = $message . $args[2];
                }
            }
        }

    }
}
?>