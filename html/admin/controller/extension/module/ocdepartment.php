<?php
class ControllerExtensionModuleOCDepartment extends Controller {
  private $error = array();
  private $module_info = array();

	public function index() {
		$data = $this->load->language('extension/module/ocdepartment');

		$this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('ocdepartment', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
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

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ocdepartment', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ocdepartment', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/ocdepartment', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/ocdepartment', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$this->module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['name'] = $this->getData('name', '');
		$data['collapse_parent'] = $this->getData('collapse_parent', 0);
    $data['collapse_parent_limit'] = $this->getData('collapse_parent_limit', 5);
		$data['collapse_child'] = $this->getData('collapse_child', 0);
		$data['collapse_child_limit'] = $this->getData('collapse_child_limit', 10);
		$data['link_to'] = $this->getData('link_to', 'auto'); // auto, self, category
		$data['show_total'] = $this->getData('show_total', 1);
		$data['status'] = $this->getData('status', 1);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ocdepartment', $data));
	}

  private function getData($key, $default = 0) {
    $module_info = $this->module_info;

    if (isset($this->request->post[$key])) {
			return $this->request->post[$key];
    } else if (isset($module_info[$key])) {
			return $module_info[$key];
		} else {
			return $default;
		}
  }

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ocdepartment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
