<?php
class ControllerInformationCompany extends Controller {
	private $error = array();

	
	public function upload() {
		$this->load->language('tool/upload');

		$json = array();

		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {

			// Sanitize the filename
			$filename = $this->request->files['file']['name'];

			// Validate the filename length
			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// Allowed file extension types
			$allowed = array();

			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

			$filetypes = explode("\n", $extension_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Allowed file mime types
			$allowed = array();

			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

			$filetypes = explode("\n", $mime_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Check to see if any PHP files are trying to be uploaded
			$content = file_get_contents($this->request->files['file']['tmp_name']);

			if (preg_match('/\<\?php/i', $content)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json) {
			$json['file'] = $file = $filename;

			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

			// Hide the uploaded file path so people can not link to it directly.
			$this->load->model('tool/upload');

			$json['code'] = $this->model_tool_upload->addUpload($filename, $file);

			$json['success'] = $this->language->get('text_upload');
		}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
            }
			
			
			
			
	
	
	public function index() {
		$this->load->language('information/company');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo('salesradioshop@gmail.com');
			$mail->setFrom($this->request->post['email']);
			$mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject')), ENT_QUOTES, 'UTF-8'));
			$mail->setHtml("<table style='border-collapse: collapse;width:500px;'><tr><td style='border: 1px solid #000;padding:10px;width: 200px;'><strong>"."". $this->language->get('email_company'). " </strong></td><td style='border: 1px solid #000;padding:10px;';>" .  $this->request->post['company']. "</td></tr><tr><td style='border: 1px solid #000;padding:10px;'><strong>". $this->language->get('entry_name') ."</strong></td><td style='border: 1px solid #000;padding:10px;'>"."".$this->request->post['name']."</td></tr><tr><td style='border: 1px solid #000;padding:10px;';><strong>". $this->language->get('email_mail') ."</strong></td><td style='border: 1px solid #000;padding:10px;';>".$this->request->post['email']."</td></tr><tr><td style='border: 1px solid #000;padding:10px;';><strong>"."". $this->language->get('email_phone') ."</strong></td><td style='border: 1px solid #000;padding:10px;';> "."". $this->request->post['phone']. "</td></tr><tr><td style='border: 1px solid #000;padding:10px;';><strong>" .$this->language->get('email_sposob')."</strong></td><td style='border: 1px solid #000;padding:10px;';> "."".$this->request->post['option']."</td></tr><tr><td colspan='2' style='border: 1px solid #000;padding:10px;';><strong>".$this->language->get('email_message'). "</strong></td></tr><tr><td colspan='2' style='border: 1px solid #000;padding:10px;';> " . $this->request->post['enquiry']."</td></tr></table>"."\n\n"."<strong>".$this->language->get('email_time')."</strong>".date('d/m/Y, H:i:s',strtotime('+2 hour')), ENT_QUOTES, 'UTF-8');
			
        if($this->request->post['file']){
                    $mail->addAttachment(DIR_UPLOAD.$this->request->post['file']);
                }		

			 if(isset($temp_name)){
     unlink( $temp_name );
    }				

			
			$mail->send();
			
			

			
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			
			
			
			$mail->setTo($this->request->post['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode(sprintf($this->language->get('name2')), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject2')), ENT_QUOTES, 'UTF-8'));
			/*$mail->setHtml(html_entity_decode(sprintf($this->language->get('thanks')), ENT_QUOTES, 'UTF-8'));*/
			$mail->setHtml("<a href='" . HTTP_SERVER . "'><img src='" . HTTP_SERVER . "image/catalog/radioshop-268.png'/></a>"."".$this->language->get('thanks').""."<table style='border-collapse: collapse;width:500px;'><tr><td colspan='2' style='border: 1px solid #5f87d1;padding:10px;';><strong>".$this->language->get('you_send')."</strong></td></tr><tr><td style='border: 1px solid #5f87d1;padding:10px;width: 200px;'><strong>"."". $this->language->get('email_company'). " </strong></td><td style='border: 1px solid #5f87d1;padding:10px;';>" .  $this->request->post['company']. "</td></tr><tr><td style='border: 1px solid #5f87d1;padding:10px;'><strong>". $this->language->get('entry_name') ."</strong></td><td style='border: 1px solid #5f87d1;padding:10px;'>"."".$this->request->post['name']."</td></tr><tr><td style='border: 1px solid #5f87d1;padding:10px;';><strong>". $this->language->get('email_mail') ."</strong></td><td style='border: 1px solid #5f87d1;padding:10px;';>".$this->request->post['email']."</td></tr><tr><td style='border: 1px solid #5f87d1;padding:10px;';><strong>"."". $this->language->get('email_phone') ."</strong></td><td style='border: 1px solid #5f87d1;padding:10px;';> "."". $this->request->post['phone']. "</td></tr><tr><td style='border: 1px solid #5f87d1;padding:10px;';><strong>" .$this->language->get('email_sposob')."</strong></td><td style='border: 1px solid #5f87d1;padding:10px;';> "."".$this->request->post['option']."</td></tr><tr><td colspan='2' style='border: 1px solid #5f87d1;padding:10px;';><strong>".$this->language->get('email_message'). "</strong></td></tr><tr><td colspan='2' style='border: 1px solid #5f87d1;padding:10px;';> " . $this->request->post['enquiry']."</td></tr></table>"."\n\n"."<strong>", ENT_QUOTES, 'UTF-8');
			
			
			if($this->request->post['file']){
                    $mail->addAttachment(DIR_UPLOAD.$this->request->post['file']);
                }		

			 if(isset($temp_name)){
     unlink( $temp_name );
    }			
			
			$mail->send();
			
			
			

			$this->response->redirect($this->url->link('information/company/success'));
		}
		

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/company')
		);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_location'] = $this->language->get('text_location');
		$data['text_store'] = $this->language->get('text_store');
		$data['text_contact'] = $this->language->get('text_contact');
		$data['text_address'] = $this->language->get('text_address');
		$data['text_telephone'] = $this->language->get('text_telephone');
		$data['text_fax'] = $this->language->get('text_fax');
		$data['text_open'] = $this->language->get('text_open');
		$data['text_comment'] = $this->language->get('text_comment');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_company'] = $this->language->get('entry_company');
		$data['entry_phone'] = $this->language->get('entry_phone');
		$data['text_one_click_mask'] = $this->language->get('text_one_click_mask');
		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_enquiry'] = $this->language->get('entry_enquiry');

		$data['button_map'] = $this->language->get('button_map');

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
		
		
				if (isset($this->error['company'])) {
			$data['error_company'] = $this->error['company'];
		} else {
			$data['error_company'] = '';
		}
		
						if (isset($this->error['phone'])) {
			$data['error_phone'] = $this->error['phone'];
		} else {
			$data['error_phone'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['enquiry'])) {
			$data['error_enquiry'] = $this->error['enquiry'];
		} else {
			$data['error_enquiry'] = '';
		}
		
		// CUSTOM CODE START: Challenge Error
		if (isset($this->error['ua_challenge'])) {
			$data['error_ua_challenge'] = $this->error['ua_challenge'];
		} else {
			$data['error_ua_challenge'] = '';
		}
		// CUSTOM CODE END

		$data['button_submit'] = $this->language->get('button_submit');

		// FIX: Use empty action to submit to current page (prevents 301 redirects dropping POST data)
		$data['action'] = ''; 

		$this->load->model('tool/image');

		if ($this->config->get('config_image')) {
			$data['image'] = $this->model_tool_image->resize($this->config->get('config_image'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
		} else {
			$data['image'] = false;
		}

		$data['store'] = $this->config->get('config_name');
		$data['address'] = nl2br($this->config->get('config_address'));
		$data['geocode'] = $this->config->get('config_geocode');
		$data['geocode_hl'] = $this->config->get('config_language');
		$data['telephone'] = $this->config->get('config_telephone');
		$data['fax'] = $this->config->get('config_fax');
		$data['open'] = nl2br($this->config->get('config_open'));
		$data['comment'] = $this->config->get('config_comment');

		$data['locations'] = array();

		$this->load->model('localisation/location');

		foreach((array)$this->config->get('config_location') as $location_id) {
			$location_info = $this->model_localisation_location->getLocation($location_id);

			if ($location_info) {
				if ($location_info['image']) {
					$image = $this->model_tool_image->resize($location_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
				} else {
					$image = false;
				}

				$data['locations'][] = array(
					'location_id' => $location_info['location_id'],
					'name'        => $location_info['name'],
					'address'     => nl2br($location_info['address']),
					'geocode'     => $location_info['geocode'],
					'telephone'   => $location_info['telephone'],
					'fax'         => $location_info['fax'],
					'image'       => $image,
					'open'        => nl2br($location_info['open']),
					'comment'     => $location_info['comment']
				);
			}
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} else {
			$data['name'] = $this->customer->getFirstName();
		}
		
				if (isset($this->request->post['company'])) {
			$data['company'] = $this->request->post['company'];
		} else {
			$data['company'] = '';
		}
		
						if (isset($this->request->post['phone'])) {
			$data['phone'] = $this->request->post['phone'];
		} else {
			$data['phone'] = '';
		}
		

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		}

		if (isset($this->request->post['enquiry'])) {
			$data['enquiry'] = $this->request->post['enquiry'];
		} else {
			$data['enquiry'] = '';
		}
		
		// CUSTOM CODE START: Persistence
		if (isset($this->request->post['ua_challenge'])) {
			$data['ua_challenge'] = $this->request->post['ua_challenge'];
		} else {
			$data['ua_challenge'] = '';
		}
		// CUSTOM CODE END

		// Captcha
		if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			

		$this->response->setOutput($this->load->view('information/company', $data));
	}

	protected function validate() {
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}
		
			
		if ((utf8_strlen($this->request->post['company']) < 3) || (utf8_strlen($this->request->post['company']) > 32)) {
			$this->error['company'] = $this->language->get('error_company');
		}
		
				if ((utf8_strlen($this->request->post['phone']) < 3) || (utf8_strlen($this->request->post['phone']) > 17)) {
			$this->error['phone'] = $this->language->get('error_phone');
		}

		if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['enquiry']) < 10) || (utf8_strlen($this->request->post['enquiry']) > 3000)) {
			$this->error['enquiry'] = $this->language->get('error_enquiry');
		}
		
		// CUSTOM CODE START: Challenge Validation (Bulletproof regex)
		// Decoded key is 'geroyam'
		$key = base64_decode('0LPQtdGA0L7Rj9C8'); 
		
		// Error message: 'Vi ne proishli perevirku' (in UA Cyrillic)
		$error_msg = base64_decode('0JLQuCDQvdC1INC/0YDQvtC50YjQu9C4INC/0LXRgNC10LLRltGA0LrRgw==');

		if (empty($this->request->post['ua_challenge'])) {
			$this->error['ua_challenge'] = $error_msg;
		} else {
			$input = trim($this->request->post['ua_challenge']);
			// Use preg_match with 'u' modifier for safe UTF-8 matching
			// ^ = start of string, u = utf-8, i = case insensitive
			if (!preg_match('/^' . $key . '/ui', $input)) {
				$this->error['ua_challenge'] = $error_msg;
			}
		}
		// CUSTOM CODE END

		// Captcha
		if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('captcha/' . $this->config->get('config_captcha') . '/validate');

			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}

		return !$this->error;
	}

	public function success() {
		$this->load->language('information/company');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/company')
		);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_message'] = $this->language->get('text_success');

		$data['button_continue'] = $this->language->get('button_continue');

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			

		$this->response->setOutput($this->load->view('common/success_company', $data));
	}
}
