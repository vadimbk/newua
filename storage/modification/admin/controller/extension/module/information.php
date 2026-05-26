<?php
class ControllerExtensionModuleInformation extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/information');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_information', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/information', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/information', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['column_faq_name'] = $this->language->get('column_faq_name');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_question'] = $this->language->get('column_question');
		$data['column_faq'] = $this->language->get('column_faq');
		$data['column_link'] = $this->language->get('column_link');
		$data['tab_faq'] = $this->language->get('tab_faq');
		$data['faq_name'] = $this->language->get('faq_name');
		$data['button_remove'] = $this->language->get('button_remove');
		$data['text_auto_category_help'] = $this->language->get('text_auto_category_help');
		$data['text_auto_blog_help'] = $this->language->get('text_auto_blog_help');
		$data['text_auto_category_products'] = $this->language->get('text_auto_category_products');
		$data['text_auto_manufacturer_help'] = $this->language->get('text_auto_manufacturer_help');
		$data['text_auto_manufacturer_products'] = $this->language->get('text_auto_manufacturer_products');
		$data['text_auto_product_help'] = $this->language->get('text_auto_product_help');
		$data['text_auto_information_help'] = $this->language->get('text_auto_information_help');
		$data['sp_auto_seo_faq_status'] = $this->config->get('sp_auto_seo_faq_status');
		

		if (isset($this->request->post['module_information_status'])) {
			$data['module_information_status'] = $this->request->post['module_information_status'];
		} else {
			$data['module_information_status'] = $this->config->get('module_information_status');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/information', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/information')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}