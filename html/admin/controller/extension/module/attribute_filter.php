<?php
class ControllerExtensionModuleAttributeFilter extends Controller{
	private $error = array();

	private $path_module = 'extension/module/attribute_filter';
	private $module_name ='attribute_filter';
	private $my_model ='model_extension_module_attribute_filter';
	private $extension_prefix ='';
	private $path_extension ='marketplace/extension&type=module';
	private $token = 'user_token';

	public function index() {
		$this->load->language($this->path_module);
		$this->load->language($this->path_module . '_lang');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title'] = $this->language->get('heading_title');

		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->makeUrl('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->makeUrl($this->path_extension)
		);

		$data['cancel'] = $this->makeUrl($this->path_extension);
	
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->path_module .'/attribute_filter', $data));
	}
	
	public function getFilter() {
		$url = '';
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_attribute_group_id'])) {
			$url .= '&filter_attribute_group_id=' . $this->request->get['filter_attribute_group_id'];
		}
		if (isset($this->request->get['limit'])) {		
			$url .= '&limit=' . $this->request->get['limit'];
		}

		return $url;
		
	}

	public function help() {
		$data = $this->load->language('extension/module/attribute_filter_lang');
		return $this->load->view($this->path_module . '/attribute_help', $data);
	}
	
	public function filter_form() {
		$data = $this->load->language('extension/module/attribute_filter_lang');
		$this->load->language('catalog/attribute');
		$data['filter'] = $this->makeUrlScript('catalog/attribute');
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
		$this->load->model('catalog/attribute_group');
		$data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups();
		$data['filter_name'] = $filter_name;
		$data['filter_attribute_group_id'] = $filter_attribute_group_id;
		
		$data['name'] = $this->makeUrlScript('catalog/attribute');
		
		return $this->load->view($this->path_module . '/attribute_filter_forrm', $data);
	}

	public function import_form() {
		$data = $this->load->language('extension/module/attribute_filter_lang');
		$this->load->language('catalog/attribute');
		$data['import'] = $this->makeUrl('catalog/attribute/attr_import');
		
		return $this->load->view($this->path_module . '/attribute_import_form', $data);
	}

	public function attr_import() {
		if ($this->validateProduct()) {
			$result = false;
			$errors = array();
			if (!isset($this->request->files['filename']) || $this->request->files['filename']['error'] != 0) {
				$errors[] = $this->language->get('error_uploadfile');
			} else {
				$delimiter = ',';
				$line = 1;
				$need_col = 0;
				$fp = fopen($this->request->files['filename']['tmp_name'], "r");

				if ($fp !== false) {
					$my_lang = [];
					$sql = "
						CREATE  TABLE IF NOT EXISTS  " . DB_PREFIX . "temp_product_attribute (
						`attribute_id` INT(11),
						`language_id` INT(11),
						`product_id` INT(11),
						`text` text NOT NULL
					)";
					$this->db->query($sql);
					$insert_count = 0;
					$insert_sql = "INSERT INTO " . DB_PREFIX . "temp_product_attribute
						(attribute_id, language_id, product_id, text) 
						VALUES ";
					$set_sql = [];
					while (($export = fgetcsv($fp, 1000, $delimiter)) !== false) {
						if ($line == 1) {
							$need_col = count($export);
							if ($need_col  < 2) {
								$errors[] = $this->language->get('error_uploadfile');
								break;
							}
							for ($i=2; $i < $need_col; $i++) {
								list($t,$language_id) = explode('_',$export[$i]);
								$my_lang[] = $language_id;
							}
						} else {
							if (count($export)  < $need_col) {
								$errors[] = sprintf($this->language->get('error_data'), $line);
							} else {
								$attribute_id = $export[0];
								$product_id = $export[1];
								$col = 2;
								foreach ($my_lang as $language_id) {
									$t_sql = [];
									$t_sql[] =  (int)$attribute_id;
									$t_sql[] =  (int)$language_id;
									$t_sql[] =  (int)$product_id;
									$t_sql[] =  "'" . $this->db->escape($export[$col]) . "'";
									$set_sql[] = "(" . implode(',', $t_sql) . ")";
									$col++;
								}
								$insert_count++;
								if ($insert_count > 100) {
									$sql = $insert_sql . implode(',',$set_sql);
									$set_sql = [];
									$this->db->query($sql);
									$insert_count = 0;
								}
							}
						}
						$line++;
					}
					if ($insert_count>0) {
						$sql = $insert_sql . implode(',',$set_sql);
						$this->db->query($sql);
					}
					$this->sql_update();
					$this->db->query("TRUNCATE TABLE " . DB_PREFIX . "temp_product_attribute");
				}
			}
		}
		$this->response->redirect($this->makeUrl('catalog/attribute'));
	}

	private function sql_update() {
		$sql = "UPDATE " . DB_PREFIX . "product_attribute pa
		JOIN " . DB_PREFIX . "temp_product_attribute tpa using(attribute_id,language_id, product_id)
		SET pa.text = tpa.text";
		$this->db->query($sql);
	}
	
	public function attr_export() {
		if (isset($this->request->get['attribute_id'])) {
			$this->createcsv($this->request->get['attribute_id']);
		}
	}

	private function createcsv($attribute_id) {
		$file_name = DIR_DOWNLOAD . 'attribute.' .  $attribute_id . '.csv';

		$fp = fopen($file_name, 'w+');

		$sql = "SELECT * FROM " . DB_PREFIX . "attribute_description
		WHERE language_id = " . (int)$this->config->get('config_language_id') . "
		AND attribute_id = " . (int)$attribute_id;
		$result = $this->db->query($sql);
		$attribute_name = $result->row['name'];
		
		$sql = "SELECT * FROM " . DB_PREFIX . "product_attribute pa
		WHERE attribute_id = " . (int)$attribute_id . "
		AND language_id = " . (int)$this->config->get('config_language_id');
		$results = $this->db->query($sql);
		
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		$my_lang = [];
		
		foreach ($languages as $language) {
			if ($language['language_id'] != $this->config->get('config_language_id')) {
				$my_lang[ $language['language_id']] = $language;
			}
		}

		$array_field = [];
		$array_field[] = 'ATTRIBUTE_ID';
		$array_field[] = 'PRODUCT_ID';
		$array_field[] = 'LANG_' . $this->config->get('config_language_id');
		
		foreach ($my_lang as $language_id => $l_val) {
		$array_field[] = 'LANG_' . $language_id;
		}
		$array_field[] = 'ATTRIBUTE_NAME';
		fputcsv($fp, $array_field);
		
		foreach ($results->rows as $result) {
			$value_lang = [];
			$value_lang[$result['language_id']] = $result['text'];
			foreach ($my_lang as $language_id => $l_val) {
				$sql = "SELECT * FROM " . DB_PREFIX . "product_attribute pa
					WHERE attribute_id = " . (int)$attribute_id . "
					AND language_id = " . (int)$language_id . "
					AND product_id = " . (int)$result['product_id'];
				$results2 = $this->db->query($sql);
				if ($results2->num_rows) {
					$value_lang[$language_id] = $results2->row['text'];
				} else {
					$value_lang[$language_id] = '';
				}
				
			}
			$array_field = [];
			$array_field[] = $result['attribute_id'];
			$array_field[] = $result['product_id'];
			foreach ($value_lang as $val) {
				$array_field[] = $val;
			}
			$array_field[] = $attribute_name;
			fputcsv($fp, $array_field);
		}
		fclose($fp);
		$this->download('attribute.' .  $attribute_id . '.csv');

	}

    private function download($file){
		$file_name = $file_name = DIR_DOWNLOAD . $file;
		if (!headers_sent()) {
			if (file_exists($file_name)) {
				$xtension = 'csv';
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . $file);
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize ($file_name));
				readfile($file_name);
			}
		}
		exit();
	}


	public function attr_delete() {
		if ($this->user->hasPermission('modify', 'catalog/attribute')) {
			$data_d = isset($this->request->get['attribute_id'])?$this->request->get['attribute_id']:0;
			unset($this->request->get['attribute_id']);
			$this->load->language('extension/module/attribute_filter_lang');
			$this->load->model('catalog/attribute');
			$result = $this->model_catalog_attribute->deleteAttribute($data_d);
			$this->session->data['success'] = sprintf($this->language->get('text_delete_attribute'), $result);
		}
		$this->response->redirect($this->makeUrl('catalog/attribute', $this->getFilter()));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->path_module)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateProduct() {
		if (!$this->user->hasPermission('modify', 'catalog/attribute')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function getMergeForm() {
		$data = $this->load->language('extension/module/attribute_filter_lang');
		$data['getMergeAttrInfo']      = $this->makeUrlScript('catalog/attribute/getMergeAttrInfo');
		$data['attributeautocomplete'] = $this->makeUrlScript('catalog/attribute/autocomplete');
		$data['addSelected']           = $this->makeUrlScript('catalog/attribute/addSelected');
		$data['startMerge']            = $this->makeUrlScript('catalog/attribute/startMerge');
		
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		$data['languages'] = array();
		foreach ($languages as $language) {
			$data['languages'][$language['language_id']] = array(
				'image' => 'language/' . $language['code'] . '/' . $language['code'] . '.png',
				'name' => $language['name']
			);
		}

		return $this->load->view($this->path_module . '/attribute_merge', $data);
	}

	public function getBulkForm() {
		$data = $this->load->language('extension/module/attribute_filter_lang');
		$this->load->model('catalog/attribute_group');
		$data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups();
		$data['bulkChangeGroup'] = $this->makeUrlScript('catalog/attribute/bulkChangeGroup');
		
		return $this->load->view($this->path_module . '/attribute_bulk', $data);
	}

	public function addSelected() {
		$json = [];
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['selected'])) {
			$ids = array_map(function($value) { return (int)$value; },  $this->request->post['selected']);
			
			$sql = "SELECT * FROM " . DB_PREFIX . "attribute a
		LEFT JOIN " . DB_PREFIX . "attribute_description ad ON a.attribute_id = ad.attribute_id
		WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'
		AND a.attribute_id IN ( " . implode(',',$ids) . ")";

			$result = $this->db->query($sql);
			foreach ($result->rows as $row) {
				$json[] = [
					'value' => $row['attribute_id'],
					'label' => $row['name']
				];
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getTabs(){
		$data = $this->load->language('extension/module/attribute_filter_lang');
		
		$data['getBulkForm']  = $this->load->controller($this->path_module . '/getBulkForm');
		$data['getMergeForm'] = $this->load->controller($this->path_module . '/getMergeForm');
		
		$data['tab_merge'] = $this->language->get('tab_merge');
		$data['tab_bulk']  = $this->language->get('tab_bulk');
		return $this->load->view($this->path_module . '/attribute_tab', $data);
		
	}

	public function startMerge(){
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'catalog/attribute')) {
			if (isset($this->request->post['merge_attr_default'])) {
				if (isset($this->request->post['merge_attr'])) {
					$ids = array_map(function($value) { return (int)$value; },  $this->request->post['merge_attr']);
					$ids = array_unique($ids);
					$key = array_search((int)$this->request->post['merge_attr_default'], $ids);
					if ($key !== false) {
						unset($ids[$key]);
					}

					if (count($ids)) {
						$sql = "
							DELETE pa 
							FROM " . DB_PREFIX . "product_attribute pa
							JOIN (
								SELECT pa1.product_id 
								FROM " . DB_PREFIX . "product_attribute pa1 
								WHERE pa1.`attribute_id` = " . (int)$this->request->post['merge_attr_default'] . "
								) pa1
							WHERE  pa.attribute_id IN (" . implode(',', $ids) . ")
							AND pa.product_id = pa1.product_id";
						$this->db->query($sql);
						$t_ids = $ids;
						foreach ($ids as $key=>$attribute_id) {
							$f_id = array_shift($t_ids);
							if ($t_ids) {
								$sql = "
									DELETE pa 
									FROM " . DB_PREFIX . "product_attribute pa
									JOIN (SELECT pa1.product_id FROM " . DB_PREFIX . "product_attribute pa1 WHERE pa1.`attribute_id` =" . (int)$f_id . ") pa1
									WHERE  pa.attribute_id IN (" . implode(',', $t_ids) . ")
									AND pa.product_id = pa1.product_id";
								$this->db->query($sql);
							}
						}
						
						$sql = "UPDATE " . DB_PREFIX . "product_attribute pa SET
							pa.attribute_id = " . (int)$this->request->post['merge_attr_default'] . "
							WHERE pa.attribute_id IN (" . implode(',', $ids) . ")";
						$this->db->query($sql);
					}
				}
			}
		}

		if (isset($this->request->post['merge_new']) && is_array($this->request->post['merge_new'])) {
			$this->load->model('localisation/language');
			$languages = $this->model_localisation_language->getLanguages();
			foreach ($languages as $language) {

				if (!empty($this->request->post['merge_new'][$language['language_id']])) {
					$sql = "UPDATE " . DB_PREFIX . "attribute_description SET
					name = '" . $this->db->escape($this->request->post['merge_new'][$language['language_id']]) . "'
					WHERE attribute_id = " . (int)$this->request->post['merge_attr_default'] . "
					AND language_id = " . (int)$language['language_id'];
					$this->db->query($sql);
				}
			}
		}

		$json['redirect'] = $this->makeUrlScript('catalog/attribute', $this->getFilter());
		//var_dump($json['redirect']);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	public function fastEdit() {
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'catalog/attribute')) {
			$attribute_id = 0; 
			$field = '';
			if (isset($this->request->post['pk'])) {
				$attribute_id = $this->request->post['pk'];
			}
			$allow_field = array (
				'name',
				'sort_order',
				'attribute_group_id'
			);
			$field = false;
			
			if (isset($this->request->post['name'])) {
				$field_ex = explode(':',$this->request->post['name']); 
				if (in_array($field_ex[0], $allow_field) && $attribute_id) {
					$field = $field_ex[0];
				}
			}

			if ($field) {
				$language_id = 0;
				if ($field == 'name') {
					$table = DB_PREFIX . 'attribute_description';
					if (isset($field_ex[1])) {
						$language_id = $field_ex[1];
					} else {
						$language_id = $this->config->get('config_language_id');
					}
				} else {
					$table = DB_PREFIX . 'attribute';
				}
				$sql = "UPDATE " . $table . " SET " .
				$field . " = '" . $this->db->escape($this->request->post['value']) . "'
				WHERE attribute_id = '" . (int)$attribute_id . "'";
				if ($language_id) {
					$sql .= " AND language_id = '" . (int)$language_id . "'";
				}
				$this->db->query($sql);
			}
		}
	}

	public function fastGetGroup() {
		$sql="
		SELECT * FROM " . DB_PREFIX . "attribute_group ag
		LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON ag.attribute_group_id = agd.attribute_group_id
		WHERE agd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$result = $this->db->query($sql);
		$json=array();
		foreach ($result->rows as $row) {
			$json[] = array('value'=> $row['attribute_group_id'], 'text' => $row['name']);
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function bulkChangeGroup() {
		$json = array();
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'catalog/attribute')) {
			$this->load->model($this->path_module);
			$this->model_extension_module_attribute_filter->changeAttributeGroup($this->request->post);
			$json['success'] = 'ok';
		} else {
			$json['error'] = 'access denied';
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(){

	}

	public function uninstall(){

	}

	private function makeUrl($route, $arg=''){
		if ($arg) {
			$arg = '&' . ltrim($arg,'&');
		}
		return $this->url->link($route, $this->token . '=' . $this->session->data[$this->token] . $arg, true);
	}

	private function makeUrlScript($route, $arg=''){
		return str_replace('&amp;','&',$this->makeUrl($route, $arg));
	}

}
