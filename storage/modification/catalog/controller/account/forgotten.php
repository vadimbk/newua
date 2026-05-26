<?php
class ControllerAccountForgotten extends Controller {
	private $error = array();

	public function index() {
		if ($this->customer->isLogged()) {
			$this->response->redirect($this->url->link('account/account', '', true));
		}

		$this->load->language('account/forgotten');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/customer');


		if(isset($this->request->post['email'])){
			$telephone = preg_replace('/[^0-9]/','',$this->request->post['email']);
			$telephone = preg_replace('/^(7|8)([0-9]{10})$/','7$2',$telephone);
			$telephone = preg_replace('/^(380)([0-9]{9})$/','380$2',$telephone);

			if(!preg_match('/^(7)([0-9]{10})$/',$telephone) && !preg_match('/^(380)([0-9]{9})$/',$telephone)){
				$telephone = '';
			}
			if ($telephone) {
				$emails = $this->model_account_customer->getLoginTele($telephone);
				if($emails){
					$this->request->post['email'] = $emails;
				}
			}
		}
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			if($emails && ($this->config->get('config_sms_alert') || $this->config->get('alertclient_status'))){
				$username = $this->config->get('alertclient_login_sms');
				$password = $this->config->get('alertclient_pass_sms');
				$from = $this->config->get('alertclient_name_sms');
				$options = array(
					'to'       => $telephone,
					'from'       => ($username ? $from : $this->config->get('config_sms_from')),
					'username' => ($username ? $username : $this->config->get('config_sms_gate_username')),
					'password' => ($username ? $password : $this->config->get('config_sms_gate_password')),
					'message'  => $this->language->get('text_change') . ' ' . $this->url->link('account/reset', 'code=' . $code, true)
				);
				$gatename = $this->config->get('alertclient_sms_gatename');
				$sms = new Sms(($gatename ? $gatename : $this->config->get('config_sms_gatename')), $options);
				$sms->send();
				$this->session->data['restore_pass'] = date('Y-m-d H:i:s');
			}


			$this->model_account_customer->editCode($this->request->post['email'], token(40));

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_forgotten'),
			'href' => $this->url->link('account/forgotten', '', true)
		);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('account/forgotten', '', true);

		$data['back'] = $this->url->link('account/login', '', true);

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			

		$this->response->setOutput($this->load->view('account/forgotten', $data));
	}

	protected function validate() {
		if (!isset($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		} elseif (!$this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		}
		
		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

		if ($customer_info && !$customer_info['status']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}


		$time = strtotime('-2 minutes');
		if(isset($this->session->data['restore_pass']) && date('Y-m-d H:i:s',$time) < $this->session->data['restore_pass']){
			$this->error['warning'] = 'Пожалуйста дождитесь смс, или попробуйте через 2 минуты после предыдущей попытки!';
			$this->request->post['email'] = '';
		}

		return !$this->error;
	}
}
