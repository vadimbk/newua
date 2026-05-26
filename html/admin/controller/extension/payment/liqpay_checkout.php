<?php
class ControllerExtensionPaymentLiqPayCheckout extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/liqpay_checkout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_liqpay_checkout', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['merchant'])) {
			$data['error_merchant'] = $this->error['merchant'];
		} else {
			$data['error_merchant'] = '';
		}

		if (isset($this->error['signature'])) {
			$data['error_signature'] = $this->error['signature'];
		} else {
			$data['error_signature'] = '';
		}

		if (isset($this->error['api'])) {
			$data['error_api'] = $this->error['api'];
		} else {
			$data['error_api'] = '';
		}

		if (isset($this->error['action'])) {
			$data['error_action'] = $this->error['action'];
		} else {
			$data['error_action'] = '';
		}

		if (isset($this->error['type'])) {
			$data['error_type'] = $this->error['type'];
		} else {
			$data['error_type'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/liqpay_checkout', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/liqpay_checkout', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_liqpay_checkout_merchant'])) {
			$data['payment_liqpay_checkout_merchant'] = $this->request->post['payment_liqpay_checkout_merchant'];
		} else {
			$data['payment_liqpay_checkout_merchant'] = $this->config->get('payment_liqpay_checkout_merchant');
		}

		if (isset($this->request->post['payment_liqpay_checkout_signature'])) {
			$data['payment_liqpay_checkout_signature'] = $this->request->post['payment_liqpay_checkout_signature'];
		} else {
			$data['payment_liqpay_checkout_signature'] = $this->config->get('payment_liqpay_checkout_signature');
		}

		if (isset($this->request->post['payment_liqpay_checkout_api'])) {
			$data['payment_liqpay_checkout_api'] = $this->request->post['payment_liqpay_checkout_api'];
		} else {
			$data['payment_liqpay_checkout_api'] = $this->config->get('payment_liqpay_checkout_api');
		}

		if (isset($this->request->post['payment_liqpay_checkout_api'])) {
			$data['payment_liqpay_checkout_api'] = $this->request->post['payment_liqpay_checkout_api'];
		} elseif (!empty($this->config->get('payment_liqpay_checkout_api'))) {
			$data['payment_liqpay_checkout_api'] = $this->config->get('payment_liqpay_checkout_api');
		} else {
			$data['payment_liqpay_checkout_api'] = 'https://www.liqpay.ua/api/3/checkout';
		}		

		if (isset($this->request->post['payment_liqpay_checkout_type'])) {
			$data['payment_liqpay_checkout_type'] = $this->request->post['payment_liqpay_checkout_type'];
		} else {
			$data['payment_liqpay_checkout_type'] = $this->config->get('payment_liqpay_checkout_type');
		}

		if (isset($this->request->post['payment_liqpay_checkout_total'])) {
			$data['payment_liqpay_checkout_total'] = $this->request->post['payment_liqpay_checkout_total'];
		} else {
			$data['payment_liqpay_checkout_total'] = $this->config->get('payment_liqpay_checkout_total');
		}

		if (isset($this->request->post['payment_liqpay_checkout_order_status_id'])) {
			$data['payment_liqpay_checkout_order_status_id'] = $this->request->post['payment_liqpay_checkout_order_status_id'];
		} else {
			$data['payment_liqpay_checkout_order_status_id'] = $this->config->get('payment_liqpay_checkout_order_status_id');
		}

		if (isset($this->request->post['payment_liqpay_checkout_order_status_failed_id'])) {
			$data['payment_liqpay_checkout_order_status_failed_id'] = $this->request->post['payment_liqpay_checkout_order_status_failed_id'];
		} else {
			$data['payment_liqpay_checkout_order_status_failed_id'] = $this->config->get('payment_liqpay_checkout_order_status_failed_id');
		}

		if (isset($this->request->post['payment_liqpay_checkout_order_status_wait_id'])) {
			$data['payment_liqpay_checkout_order_status_wait_id'] = $this->request->post['payment_liqpay_checkout_order_status_wait_id'];
		} else {
			$data['payment_liqpay_checkout_order_status_wait_id'] = $this->config->get('payment_liqpay_checkout_order_status_wait_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_liqpay_checkout_geo_zone_id'])) {
			$data['payment_liqpay_checkout_geo_zone_id'] = $this->request->post['payment_liqpay_checkout_geo_zone_id'];
		} else {
			$data['payment_liqpay_checkout_geo_zone_id'] = $this->config->get('payment_liqpay_checkout_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_liqpay_checkout_status'])) {
			$data['payment_liqpay_checkout_status'] = $this->request->post['payment_liqpay_checkout_status'];
		} else {
			$data['payment_liqpay_checkout_status'] = $this->config->get('payment_liqpay_checkout_status');
		}

		if (isset($this->request->post['payment_liqpay_checkout_sort_order'])) {
			$data['payment_liqpay_checkout_sort_order'] = $this->request->post['payment_liqpay_checkout_sort_order'];
		} else {
			$data['payment_liqpay_checkout_sort_order'] = $this->config->get('payment_liqpay_checkout_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/liqpay_checkout', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/liqpay_checkout')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_liqpay_checkout_merchant']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}

		if (!$this->request->post['payment_liqpay_checkout_signature']) {
			$this->error['signature'] = $this->language->get('error_signature');
		}

		if (!$this->request->post['payment_liqpay_checkout_api']) {
			$this->error['api'] = $this->language->get('error_api');
		}

		return !$this->error;
	}
}