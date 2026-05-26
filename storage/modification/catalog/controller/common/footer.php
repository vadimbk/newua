<?php
class ControllerCommonFooter extends Controller {

			public function getOctPolicy() {
				if(isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $this->config->get('config_maintenance') == 0) {
					$data = [];

			        $data['oct_policy_accept'] = $this->language->get('oct_policy_accept');
			        $data['oct_policy_more'] = $this->language->get('oct_policy_more');

			        $data['text_oct_policy'] = false;
			        $data['oct_max_day'] = 365;
			        $data['oct_policy_value'] = 'oct_policy';
			        $data['oct_policy_day_now'] = date("Y-m-d H:i:s");

			        $oct_policy_status = $this->config->get('oct_policy_status');
					$oct_policy_data = $this->config->get('oct_policy_data');

			        if (isset($oct_policy_data['value']) && $oct_policy_data['value'] && !empty($oct_policy_data['value'])) {
		            	$data['oct_policy_value'] = $oct_policy_value = $oct_policy_data['value'];
		        	}

			        if ($oct_policy_status && (!isset($this->request->cookie[$oct_policy_value]) || !$this->request->cookie[$oct_policy_value])) {
			            if (isset($oct_policy_data['module_text'][(int)$this->config->get('config_language_id')]) && !empty($oct_policy_data['module_text'][(int)$this->config->get('config_language_id')])) {
			            	$data['text_oct_policy'] = strip_tags(html_entity_decode($oct_policy_data['module_text'][(int)$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8'));

			            	if (isset($oct_policy_data['indormation_id']) && $oct_policy_data['indormation_id']) {
				            	$data['text_oct_policy'] .= ' <a target="_blank" href="'. $this->url->link('information/information', 'information_id=' . $oct_policy_data['indormation_id']) . '">' . $data['oct_policy_more'] . '</a>';
			            	}

			            	if (isset($oct_policy_data['max_day']) && $oct_policy_data['max_day'] && !empty($oct_policy_data['max_day'])) {
				            	$data['oct_max_day'] = (int)$oct_policy_data['max_day'];
			            	}
						}
			        }

			        $this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($data));
				} else {
					$this->response->redirect($this->url->link('error/not_found', '', true));
				}
			}
			
	public function index() {
		$data['ee_js_position'] = $this->config->get('module_ee_tracking_js_position');
		$data['ee_js_version'] = $this->config->get('module_ee_tracking_js_version');

			$data['oct_ultrastore_data'] = $oct_ultrastore_data = $this->config->get('theme_oct_ultrastore_data');

			$data['oct_lang_id'] = (int)$this->config->get('config_language_id');

			$data['oct_jscode'] = html_entity_decode($this->config->get('theme_oct_ultrastore_js_code'), ENT_QUOTES, 'UTF-8');

			$this->load->model('tool/image');

			$data['oct_customer_paymets'] = [];

			if (isset($oct_ultrastore_data['payments']['customers']) && !empty($oct_ultrastore_data['payments']['customers'])) {
				foreach ($oct_ultrastore_data['payments']['customers'] as $oct_c_payment) {
					if ((isset($oct_c_payment['status']) && $oct_c_payment['status'] == 'on') && isset($oct_c_payment['image']) && !empty($oct_c_payment['image']) && file_exists(DIR_IMAGE.$oct_c_payment['image'])) {
						$data['oct_customer_paymets'][] = $this->model_tool_image->resize($oct_c_payment['image'], 52, 32);
					}
				}
			}
			
		$this->load->language('common/footer');

			$data['oct_subscribe_form_data'] = $this->config->get('oct_subscribe_form_data');
			$data['oct_subscribe_status'] = $this->config->get('oct_subscribe_status');
			$data['oct_subscribe_day_now'] = date("Y-m-d H:i:s");
			
			if (isset($data['oct_ultrastore_data']['footer_subscribe']) && $data['oct_ultrastore_data']['footer_subscribe'] == 'on') {
				$data['oct_subscribe'] = $this->load->controller('octemplates/module/oct_subscribe');
			}
			

			if ($this->config->get('theme_oct_ultrastore_feedback_status')) {
				$data['oct_feedback_data'] = $this->config->get('theme_oct_ultrastore_feedback_data');
				$data['oct_popup_call_phone_status'] = $this->config->get('oct_popup_call_phone_status');
			}
			

		$this->load->model('catalog/information');
$data['avail_config_google_captcha_status'] = $this->config->get('avail_config_google_captcha_status');
					$data['google_captcha_key'] = $this->config->get('avail_config_google_captcha_public');
					$data['google_captcha_secret'] = $this->config->get('avail_config_google_captcha_secret');
			

	        // start: oct_policy
	        $data['oct_policy_value'] = false;

	        $oct_policy_status = $this->config->get('oct_policy_status');
			$oct_policy_data = $this->config->get('oct_policy_data');

	        if (isset($oct_policy_data['value']) && $oct_policy_data['value'] && !empty($oct_policy_data['value']) && ($oct_policy_status && (!isset($this->request->cookie[$oct_policy_data['value']]) || !$this->request->cookie[$oct_policy_data['value']])) && $this->config->get('config_maintenance') == 0) {
            	$data['oct_policy_value'] = $oct_policy_data['value'];
        	}
			// end: oct_policy
			

		$data['informations'] = array();

		
			if (isset($data['oct_ultrastore_data']['footer_information_links']) && !empty($data['oct_ultrastore_data']['footer_information_links'])) {
				foreach ($data['oct_ultrastore_data']['footer_information_links'] as $information_id) {
					$information_info = $this->model_catalog_information->getInformation($information_id);

					if ($information_info) {
						$data['informations'][] = array(
							'title' => $information_info['title'],
							'href'  => $this->url->link('information/information', 'information_id=' . $information_id, true)
						);
					}
				}
			} else {
				foreach ($this->model_catalog_information->getInformations() as $result) {
					if ($result['bottom']) {
						$data['informations'][] = array(
							'title' => $result['title'],
							'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
						);
					}
				}
			}
			
		
		//esputnik
		if($this->customer->isLogged()){
		    $data['telephone']=$this->customer->getTelephone();
		    $data['email']=$this->customer->getEmail();
        }else{
		    $data['telephone']=false;
		    $data['email']=false;
        }
        //esputhnik

		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);

			if (isset($data['oct_ultrastore_data']['footer_link_contact']) && $data['oct_ultrastore_data']['footer_link_contact'] == 'on') {
				$data['informations'][] = array(
					'title' => $this->language->get('text_contact'),
					'href'  => $this->url->link('information/contact')
				);
			}

			if (isset($data['oct_ultrastore_data']['footer_link_return']) && $data['oct_ultrastore_data']['footer_link_return'] == 'on') {
				$data['informations'][] = array(
					'title' => $this->language->get('text_return'),
					'href'  => $this->url->link('account/return/add', '', true)
				);
			}

			if (isset($data['oct_ultrastore_data']['footer_link_sitemap']) && $data['oct_ultrastore_data']['footer_link_sitemap'] == 'on') {
				$data['informations'][] = array(
					'title' => $this->language->get('text_sitemap'),
					'href'  => $this->url->link('information/sitemap')
				);
			}

			if (isset($data['oct_ultrastore_data']['footer_link_man']) && $data['oct_ultrastore_data']['footer_link_man'] == 'on') {
				$data['informations'][] = array(
					'title' => $this->language->get('text_manufacturer'),
					'href'  => $this->url->link('product/manufacturer')
				);
			}

			if (isset($data['oct_ultrastore_data']['footer_link_cert']) && $data['oct_ultrastore_data']['footer_link_cert'] == 'on') {
				$data['informations'][] = array(
					'title' => $this->language->get('text_voucher'),
					'href'  => $this->url->link('account/voucher', '', true)
				);
			}

			if (isset($data['oct_ultrastore_data']['footer_link_specials']) && $data['oct_ultrastore_data']['footer_link_specials'] == 'on') {
				$data['informations'][] = array(
					'title' => $this->language->get('text_special'),
					'href'  => $this->url->link('product/special')
				);
			}

			if (isset($data['oct_ultrastore_data']['footer_category_links']) && !empty($data['oct_ultrastore_data']['footer_category_links'])) {
				$this->load->model('catalog/category');

				foreach ($data['oct_ultrastore_data']['footer_category_links'] as $category_id) {
					$category_info = $this->model_catalog_category->getOCTCategory($category_id);

					if ($category_info) {
						$path = ($category_info['path']) ? $category_info['path'] . '_' . $category_info['category_id'] : $category_info['category_id'];

						$data['categories'][] = array(
							'name' => $category_info['name'],
							'href'  => $this->url->link('product/category', 'path=' . $path, true)
						);
					}
				}
			}

			if (isset($data['oct_ultrastore_data']['contact_open'][(int)$this->config->get('config_language_id')])){
				$oct_contact_opens = explode(PHP_EOL, $data['oct_ultrastore_data']['contact_open'][(int)$this->config->get('config_language_id')]);

				foreach ($oct_contact_opens as $oct_contact_open) {
					if (!empty($oct_contact_open)) {
						$data['oct_contact_opens'][] = $oct_contact_open;
					}
				}
			}

			$oct_contact_telephones = explode(PHP_EOL, $data['oct_ultrastore_data']['contact_telephone']);

			foreach ($oct_contact_telephones as $oct_contact_telephone) {
				if (!empty($oct_contact_telephone)) {
					$data['oct_contact_telephones'][] = $oct_contact_telephone;
				}
			}
			

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));


			// remarketing all in one 
			$this->load->model('tool/remarketing');
			if ($this->config->get('remarketing_status') && !$this->model_tool_remarketing->isBot()) {
				$data['remarketing_footer'] = $this->load->controller('common/remarketing/footer');
				$data['remarketing_status'] = $this->config->get('remarketing_status');	
			}
			
		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		$data['scripts'] = $this->document->getScripts('footer');
		

            if ($this->config->get('analytics_oct_analytics_status') && $this->config->get('analytics_oct_analytics_position') == 1) {
				$data['analytics'] = $this->load->controller('extension/analytics/oct_analytics', $this->config->get('analytics_oct_analytics_status'));
			}
			
		return $this->load->view('common/footer', $data);
	}
}
