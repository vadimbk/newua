<?php
class ControllerCatalogAttribute extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/attribute');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/attribute');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/attribute');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/attribute');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_attribute->addAttribute($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';


			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));;
			}
			if (isset($this->request->get['filter_attribute_group_id'])) {
				$url .= '&filter_attribute_group_id=' . $this->request->get['filter_attribute_group_id'];
			}
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/attribute');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/attribute');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_attribute->editAttribute($this->request->get['attribute_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';


			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));;
			}
			if (isset($this->request->get['filter_attribute_group_id'])) {
				$url .= '&filter_attribute_group_id=' . $this->request->get['filter_attribute_group_id'];
			}
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/attribute');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/attribute');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $attribute_id) {
				$this->model_catalog_attribute->deleteAttribute($attribute_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';


			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));;
			}
			if (isset($this->request->get['filter_attribute_group_id'])) {
				$url .= '&filter_attribute_group_id=' . $this->request->get['filter_attribute_group_id'];
			}
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {

		$this->document->addStyle('view/javascript/bootstrap3-editable/css/bootstrap-editable.css');
		$this->document->addScript('view/javascript/bootstrap3-editable/js/bootstrap-editable.min.js');

		$data['url_fast_edit'] = $this->url->link('catalog/attribute/fastEdit','user_token=' . $this->session->data['user_token'], true);
		$data['url_fast_get_group'] = str_replace('&amp;', '&',$this->url->link('catalog/attribute/fastGetGroup','user_token=' . $this->session->data['user_token'], true));

		$this->load->language('extension/module/attribute_filter_lang');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_group'] = $this->language->get('entry_group');
		$data['button_filter'] = $this->language->get('button_filter');
		$data['title_form_group'] = $this->language->get('title_form_group');
		$data['text_show'] = $this->language->get('text_show');
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = NULL;
		}
		if (isset($this->request->get['filter_attribute_group_id'])) {
			$filter_attribute_group_id = $this->request->get['filter_attribute_group_id'];
		} else {
			$filter_attribute_group_id = NULL;
		}
		$data['user_token'] = $this->session->data['user_token'];
		$data['getTabs'] = $this->load->controller('extension/module/attribute_filter/getTabs');
		$data['filter_form'] = $this->load->controller('extension/module/attribute_filter/filter_form');
		$data['import_form'] = $this->load->controller('extension/module/attribute_filter/import_form');
		$data['help'] = $this->load->controller('extension/module/attribute_filter/help');

		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'ad.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';


			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));;
			}
			if (isset($this->request->get['filter_attribute_group_id'])) {
				$url .= '&filter_attribute_group_id=' . $this->request->get['filter_attribute_group_id'];
			}
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/attribute/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/attribute/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);


			$old_limit = $this->config->get('config_limit_admin');
			if (isset($this->request->get['limit']) && (int)$this->request->get['limit']) {
				$this->config->set('config_limit_admin', (int)$this->request->get['limit']);
			}
		$data['attributes'] = array();

		$filter_data = array(
			'sort'  => $sort,

			'filter_attribute_group_id'  => $filter_attribute_group_id,
			'filter_name'  => $filter_name,
			
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$attribute_total = $this->model_catalog_attribute->getTotalAttributes($filter_data);
			$this->load->model('localisation/language');
			$languages = $this->model_localisation_language->getLanguages();
			$data['languages'] = array();
			foreach ($languages as $language) {
				$data['languages'][$language['language_id']] = array(
					'image' => 'language/' . $language['code'] . '/' . $language['code'] . '.png',
					'name' => $language['name']
				);
			}
			

		$results = $this->model_catalog_attribute->getAttributes($filter_data);

		foreach ($results as $result) {
			
			if (!class_exists('model_extension_module_attribute_filter')) {
				$this->load->model('extension/module/attribute_filter');
			}

			$data_name = $this->model_extension_module_attribute_filter->getNames($result['attribute_id']);
			$totals = $this->model_extension_module_attribute_filter->getTotalValues($result['attribute_id']);
			
			$data['attributes'][] = array(
				'names' => $data_name,
				'totals' => $totals,
				'attribute_group_id' => $result['attribute_group_id'],
			
				'attribute_id'    => $result['attribute_id'],
				'name'            => $result['name'],
				'attribute_group' => $result['attribute_group'],
				'sort_order'      => $result['sort_order'],
'attr_delete' => $this->url->link('catalog/attribute/attr_delete', 'user_token=' . $this->session->data['user_token'] . '&attribute_id=' . $result['attribute_id'] . $url, true),
			'attr_export' => $this->url->link('catalog/attribute/attr_export', 'user_token=' . $this->session->data['user_token'] . '&attribute_id=' . $result['attribute_id'] . $url, true),
				'edit'            => $this->url->link('catalog/attribute/edit', 'user_token=' . $this->session->data['user_token'] . '&attribute_id=' . $result['attribute_id'] . $url, true)
			);
		}
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}


			$data['filter_attribute_group_id'] = $filter_attribute_group_id;
			$data['filter_name'] = $filter_name;
			
		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));;
			}
			if (isset($this->request->get['filter_attribute_group_id'])) {
				$url .= '&filter_attribute_group_id=' . $this->request->get['filter_attribute_group_id'];
			}
			

		$data['sort_name'] = $this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'] . '&sort=ad.name' . $url, true);
		$data['sort_attribute_group'] = $this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'] . '&sort=attribute_group' . $url, true);
		$data['sort_sort_order'] = $this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'] . '&sort=a.sort_order' . $url, true);

		$url = '';


			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));;
			}
			if (isset($this->request->get['filter_attribute_group_id'])) {
				$url .= '&filter_attribute_group_id=' . $this->request->get['filter_attribute_group_id'];
			}
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $attribute_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($attribute_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($attribute_total - $this->config->get('config_limit_admin'))) ? $attribute_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $attribute_total, ceil($attribute_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


			$data['limit'] = '';
			if (isset($this->request->get['limit'])) {
				$data['limit'] = $this->request->get['limit'];
			}
			$data['limits'] = array();
			for ($i = 1; $i <= 5; $i++) {
				$data['limits'][] = $old_limit * $i;
			}
		$this->response->setOutput($this->load->view('catalog/attribute_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['attribute_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['attribute_group'])) {
			$data['error_attribute_group'] = $this->error['attribute_group'];
		} else {
			$data['error_attribute_group'] = '';
		}

		$url = '';


			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));;
			}
			if (isset($this->request->get['filter_attribute_group_id'])) {
				$url .= '&filter_attribute_group_id=' . $this->request->get['filter_attribute_group_id'];
			}
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['attribute_id'])) {
			$data['action'] = $this->url->link('catalog/attribute/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/attribute/edit', 'user_token=' . $this->session->data['user_token'] . '&attribute_id=' . $this->request->get['attribute_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['attribute_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$attribute_info = $this->model_catalog_attribute->getAttribute($this->request->get['attribute_id']);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['attribute_description'])) {
			$data['attribute_description'] = $this->request->post['attribute_description'];
		} elseif (isset($this->request->get['attribute_id'])) {
			$data['attribute_description'] = $this->model_catalog_attribute->getAttributeDescriptions($this->request->get['attribute_id']);
		} else {
			$data['attribute_description'] = array();
		}

		if (isset($this->request->post['attribute_group_id'])) {
			$data['attribute_group_id'] = $this->request->post['attribute_group_id'];
		} elseif (!empty($attribute_info)) {
			$data['attribute_group_id'] = $attribute_info['attribute_group_id'];
		} else {
			$data['attribute_group_id'] = '';
		}

		$this->load->model('catalog/attribute_group');

		$data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups();

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($attribute_info)) {
			$data['sort_order'] = $attribute_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/attribute_form', $data));
	}


	public function attr_delete() {
		$this->load->controller('extension/module/attribute_filter/attr_delete');
	}

	public function attr_export() {
		$this->load->controller('extension/module/attribute_filter/attr_export');
	}

	public function attr_import() {
		$this->load->controller('extension/module/attribute_filter/attr_import');
	}
	
	public function bulkChangeGroup() {
		$this->load->controller('extension/module/attribute_filter/bulkChangeGroup');
	}

	public function fastGetGroup() {
		$this->load->controller('extension/module/attribute_filter/fastGetGroup');
	}
			
	public function fastEdit() {
		$this->load->controller('extension/module/attribute_filter/fastEdit');
	}

	public function addSelected() {
		$this->load->controller('extension/module/attribute_filter/addSelected');
	}

	public function startMerge() {
		$this->load->controller('extension/module/attribute_filter/startMerge');
	}
			
	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/attribute')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['attribute_group_id']) {
			$this->error['attribute_group'] = $this->language->get('error_attribute_group');
		}

		foreach ($this->request->post['attribute_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 64)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/attribute')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('catalog/product');

		foreach ($this->request->post['selected'] as $attribute_id) {
			$product_total = $this->model_catalog_product->getTotalProductsByAttributeId($attribute_id);

			if ($product_total) {
				$this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
			}
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/attribute');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_catalog_attribute->getAttributes($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'attribute_id'    => $result['attribute_id'],
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'attribute_group' => $result['attribute_group']
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
