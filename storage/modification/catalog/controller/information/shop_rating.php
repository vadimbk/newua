<?php
class ControllerInformationShopRating extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('information/shop_rating');

        $this->load->model('catalog/shop_rating');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle('catalog/view/theme/default/stylesheet/remodal/remodal.css');
        $this->document->addStyle('catalog/view/theme/default/stylesheet/remodal/remodal-default-theme.css');
        $this->document->addStyle('catalog/view/theme/default/stylesheet/shop_rate.css');
        $this->document->addScript('https://www.google.com/recaptcha/api.js');
        if ($this->config->get('config_google_captcha_status')) {
            $this->document->addScript('https://www.google.com/recaptcha/api.js');
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $rating_id = $this->model_catalog_shop_rating->addRating($this->request->post);


            if($rating_id > 0){
                if($this->config->get('shop_rating_moderate')){
                    $this->session->data['shop_rating_success_text'] = $this->language->get('shop_rating_success_text_moderate');
                }else{
                    $this->session->data['shop_rating_success_text'] = $this->language->get('shop_rating_success_text');
                }
                $this->session->data['rating_send'] = true;
            }
            $this->response->redirect($this->url->link('information/shop_rating','',true));


        }
        //$this->session->data['rating_send'] = false;


        if(isset($this->session->data['shop_rating_success_text'])){
            $data['success'] = $this->session->data['shop_rating_success_text'];
            unset($this->session->data['shop_rating_success_text']);
        }else{
            $data['success'] = '';
        }
        if(isset($this->session->data['rating_send'])){
            $data['rating_send'] = $this->session->data['rating_send'];
        }else{
            $data['rating_send'] = false;
        }
        //$data['rating_send'] = false;

        $data['heading_title'] = $this->language->get('heading_title');
        $data['module_description'] = $this->language->get('module_description');
        $data['send_rating'] = $this->language->get('send_rating');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home','',true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/shop_rating','',true)
        );

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			



        $data['text_rate'] = $this->language->get('text_rate');
        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_email'] = $this->language->get('entry_email');
        $data['entry_comment'] = $this->language->get('entry_comment');
        $data['entry_good'] = $this->language->get('entry_good');
        $data['entry_bad'] = $this->language->get('entry_bad');
        $data['entry_rate'] = $this->language->get('entry_rate');
        $data['entry_site_rate'] = $this->language->get('entry_site_rate');
        $data['button_submit'] = $this->language->get('button_send');
        $data['button_close'] = $this->language->get('button_close');
        $data['god_bad_desc'] = $this->language->get('god_bad_desc');
        $data['registered_customer_text'] = $this->language->get('registered_customer_text');
        $data['answer'] = $this->language->get('answer');
        $data['text_summary'] = $this->language->get('text_summary');
        $data['text_count'] = $this->language->get('text_count');
        $data['text_will_send'] = $this->language->get('text_will_send');
        $data['text_email_desc'] = $this->language->get('text_email_desc');

        $data['action'] = $this->url->link('information/shop_rating', '', true);
        $url = '';
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $filter_count = 10;
        if($this->config->get('shop_rating_count')){
            $filter_count = $this->config->get('shop_rating_count');
        }
        $filter_data = array(
            'start'             => ($page - 1) * $filter_count,
            'limit'             => $filter_count
        );

        $total = $this->model_catalog_shop_rating->getStoreRatingsTotal($filter_data);
        $data['ratings'] = $this->model_catalog_shop_rating->getStoreRatings($filter_data);

        foreach($data['ratings'] as $key=>$rating_item){
            $data['ratings'][$key]['customs'] = $this->model_catalog_shop_rating->getRateCustomRatings($rating_item['rate_id']);
        }

        $data['general']['count'] = 0;
        $data['general']['1'] = 0;
        $data['general']['2'] = 0;
        $data['general']['3'] = 0;
        $data['general']['4'] = 0;
        $data['general']['5'] = 0;
        $x = 0;
        $summ = 0;
        foreach($this->model_catalog_shop_rating->getStoreRatingsAll() as $rate){
            if(isset($rate['shop_rate']) && $rate['shop_rate'] > 0){
                $data['general'][$rate['shop_rate']]++;
                $summ = $summ + $rate['shop_rate'];
                $x++;
            }
        }

        $data['general']['count'] = $x;
        if($x > 0 ){
            $data['general']['summ'] = str_replace('.', ',', round($summ/$x, 1));
            $data['general']['summ_perc'] = round($summ/$x, 1)*100/5;
        }else{
            $data['general']['summ'] = 0;
            $data['general']['summ_perc'] = 0;
        }

        $data['rating_answers'] = $this->model_catalog_shop_rating->getRatingAnswers();


        $data['shop_rating_moderate'] = $this->config->get('shop_rating_moderate');
        $data['shop_rating_authorized'] = $this->config->get('shop_rating_authorized');
        $data['shop_rating_summary'] = $this->config->get('shop_rating_summary');
        $data['shop_rating_shop_rating'] = $this->config->get('shop_rating_shop_rating');
        $data['shop_rating_site_rating'] = $this->config->get('shop_rating_site_rating');
        $data['shop_rating_good_bad'] = $this->config->get('shop_rating_good_bad');

        $data['form_custom_types'] = $this->model_catalog_shop_rating->getCustomTypes();

        $data['text_login'] = sprintf($this->language->get('text_login_error'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

        $data['customer_id'] = $this->customer->getId();


        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['email'])) {
            $data['error_email'] = $this->error['email'];
        } else {
            $data['error_email'] = '';
        }
        if (isset($this->error['comment'])) {
            $data['error_comment'] = $this->error['comment'];
        } else {
            $data['error_comment'] = '';
        }
        if (isset($this->error['captcha'])) {
            $data['error_captcha'] = $this->error['captcha'];
        } else {
            $data['error_captcha'] = '';
        }


        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } else {
            $data['name'] = $this->customer->getFirstName();
        }

        if (isset($this->request->post['email'])) {
            $data['email'] = $this->request->post['email'];
        } else {
            $data['email'] = $this->customer->getEmail();
        }
        if (isset($this->request->post['comment'])) {
            $data['comment'] = $this->request->post['comment'];
        } else {
            $data['comment'] = '';
        }
        if (isset($this->request->post['good'])) {
            $data['good'] = $this->request->post['good'];
        } else {
            $data['good'] = '';
        }
        if (isset($this->request->post['bad'])) {
            $data['bad'] = $this->request->post['bad'];
        } else {
            $data['bad'] = '';
        }
        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } else {
            $data['name'] = $this->customer->getFirstName();
        }

        if (isset($this->request->post['email'])) {
            $data['email'] = $this->request->post['email'];
        } else {
            $data['email'] = $this->customer->getEmail();
        }

        if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
					
						
						if ( VERSION < '2.3.0.0' ) { // old
							$data['captcha'] = $this->load->controller('captcha/' . $this->config->get('config_captcha'), $this->error);
						} else { // new
							$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
						}
						
	  

        } elseif ($this->config->get('config_google_captcha_status')) {
					
						$data['site_key'] = $this->config->get('config_google_captcha_public');
						$data['captcha'] = '<div class="form-group">
																		<div class="col-sm-offset-2 col-sm-10">
																				<div class="g-recaptcha" data-sitekey="'.$data['site_key'].'"></div>';
						if ($data['error_captcha']){
								$data['captcha'] .=' <div class="text-danger">'.$data['error_captcha'].'</div>';
						}
						$data['captcha'] .='</div></div>';
				} else {
						$data['captcha'] = '';
				}



        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $filter_count;
        $pagination->url = $this->url->link('information/shop_rating', $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $this->response->setOutput($this->load->view('information/shop_rating', $data));

    }
    protected function validate() {
        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
            $this->error['email'] = $this->language->get('error_email');
        }

        // Captcha
        if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
					
						// tnkr
						if ( VERSION < '2.3.0.0' ) { // old
							$captcha = $this->load->controller('captcha/' . $this->config->get('config_captcha') . '/validate');
						} else { // new
							$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');
						}
					
            //$captcha = $this->load->controller('captcha/' . $this->config->get('config_captcha') . '/validate');

            if ($captcha) {
                $this->error['captcha'] = $captcha;
            }
        }elseif ($this->config->get('config_google_captcha_status')) {
            $recaptcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->config->get('config_google_captcha_secret')) . '&response=' . $this->request->post['g-recaptcha-response'] . '&remoteip=' . $this->request->server['REMOTE_ADDR']);

            $recaptcha = json_decode($recaptcha, true);

            if (!$recaptcha['success']) {
                $this->error['captcha'] = $this->language->get('error_captcha');
            }
            }


        return !$this->error;
    }

    public function request_mail($route, $output) {
        $this->load->language('information/shop_rating');

        $this->load->model('checkout/order');
        $this->load->model('catalog/shop_rating');

        $order_id = $output[0];
        $order_info = $this->model_checkout_order->getOrder($order_id);


        $store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
        $store_url = '<a href="'.$order_info['store_url'].'">'.$store_name.'</a>';
        $customer_name = $order_info['firstname'];
        $ratings_link = '<a href="'.$this->url->link('information/shop_rating').'">"'.$this->language->get('sr_title').'"</a>';

        if(isset($order_info['customer_id']) && $order_info['customer_id'] > 0){
            $count = $this->model_catalog_shop_rating->customerRatingsCount($order_info['customer_id']);
            if($count && $count > 0){
                $send = false;
            }else{
                $send = true;
            }
        }else{
            $send = true;
        }


        if($order_info['order_status_id'] == $this->config->get('shop_rating_request_status') && $send == true) {

            if($this->config->get('shop_rating_request_subject')){
                $subject = html_entity_decode($this->config->get('shop_rating_request_subject'));
            }else{
                $subject = sprintf($this->language->get('text_request_mail_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
            }

            if($this->config->get('shop_rating_request_text') && $this->config->get('shop_rating_request_text') != ''){
                $mail_text = html_entity_decode($this->config->get('shop_rating_request_text'), ENT_QUOTES, 'UTF-8');
            }else{
                $mail_text = html_entity_decode($this->language->get('text_request_mail_text'), ENT_QUOTES, 'UTF-8');
            }
            $mail_text = str_replace('[store_name]', $store_name, $mail_text);
            $mail_text = str_replace('[store_name_link]', $store_url, $mail_text);
            $mail_text = str_replace('[customer_name]', $customer_name, $mail_text);
            $mail_text = str_replace('[ratings_link]', $ratings_link, $mail_text);

            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';

            $message .= $mail_text;
            $message .= '</body></html>';

            $r_mail = new Mail();
            $r_mail->protocol = $this->config->get('config_mail_protocol');
            $r_mail->parameter = $this->config->get('config_mail_parameter');
            $r_mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $r_mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $r_mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $r_mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $r_mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');


            $r_mail->setTo($order_info['email']);
            $r_mail->setFrom($this->config->get('config_email'));

            $r_mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
            $r_mail->setSubject($subject);
            $r_mail->setHtml($message);
            $r_mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
            $r_mail->send();

        }

    }

}
?>