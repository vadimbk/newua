<?php
class ModelCatalogCategory extends Model {
	public function addCategory($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

		$category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");

				if(isset($value['default_meta'])) {
					$this->db->query("UPDATE " . DB_PREFIX . "category_description SET default_meta = '".(int)$value['default_meta']."' WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$language_id . "'");
				}
			
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}


		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq WHERE category_id = '" . (int)$category_id . "' AND type = 'category'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq_description WHERE category_id = '" . (int)$category_id . "' AND type = 'category'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq WHERE category_id = '" . (int)$category_id . "' AND type = 'category_products'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq_description WHERE category_id = '" . (int)$category_id . "' AND type = 'category_products'");
		if (isset($data['faq'])) {
			foreach ($data['faq'] as $category_faq) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq SET category_id = '" . (int)$category_id . "', `link` = '" . $this->db->escape($category_faq['link']) . "', `sort_order` = '" . (int)$category_faq['sort_order'] . "', type = 'category'");
				$faq_id = $this->db->getLastId();
				foreach ($category_faq['faq_data'] as $language_id => $value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq_description SET faq_id = '" . (int)$faq_id . "', category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', answer = '" . $this->db->escape($value['answer']) . "', type = 'category'");
				}
			}
		}
		if (isset($data['faq_products'])) {
			foreach ($data['faq_products'] as $category_faq) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq SET category_id = '" . (int)$category_id . "', `link` = '', `sort_order` = '" . (int)$category_faq['sort_order'] . "', type = 'category_products'");
				$faq_id = $this->db->getLastId();
				foreach ($category_faq['faq_data'] as $language_id => $value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq_description SET faq_id = '" . (int)$faq_id . "', category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', answer = '" . $this->db->escape($value['answer']) . "', type = 'category_products'");
				}
			}
		}
		
		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
		// SEO URL Generator . begin
		// category_id not exist in controller when add() category
		
		$this->load->model('extension/module/seo_url_generator');

		$sug_log = new StdeLog('seo_url_generator');
		$sug_log->setDebug($this->config->get('module_seo_url_generator_debug'));

		$sug_log->write(4, 'model/category.php :: addCategory() is called');

		$sug_log->write(4, $data['category_seo_url'], 'model/category.php :: addCategory() : $data["category_seo_url"] BEFORE SUG');
		
		#
		# SETTING
		#

		$sug_data['setting'] = array();

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('setting/setting');
		
		$data['store_setting'] = array();
		
		foreach ($data['category_seo_url'] as $store_id => $store) {
			$store_setting = $this->model_setting_setting->getSetting('module_seo_url_generator', $store_id);

			$data['store_setting'][$store_id]['language']              = $store_setting['module_seo_url_generator_language'];
			$data['store_setting'][$store_id]['translit_function']     = $store_setting['module_seo_url_generator_translit_function'];
			$data['store_setting'][$store_id]['formula']               = $store_setting['module_seo_url_generator_category_formula'];	
			$data['store_setting'][$store_id]['delimiter_char']        = $store_setting['module_seo_url_generator_delimiter_char'];			
			$data['store_setting'][$store_id]['change_delimiter_char'] = $store_setting['module_seo_url_generator_change_delimiter_char'];
			$data['store_setting'][$store_id]['rewrite_on_save']       = $store_setting['module_seo_url_generator_rewrite_on_save'];
			$data['store_setting'][$store_id]['custom_replace_from']   = $store_setting['module_seo_url_generator_custom_replace_from'];
			$data['store_setting'][$store_id]['custom_replace_to']     = $store_setting['module_seo_url_generator_custom_replace_to'];
		}

		foreach ($data['category_seo_url'] as $store_id => $store) {
			foreach ($store as $language_id => $url) {
				if (empty($data['category_seo_url'][$store_id][$language_id])) {
					$sug_data = array(
						'name'            => $data['category_description'][$data['store_setting'][$store_id]['language'][$language_id]]['name'],
						'primary_key'     => 'category_id',
						'essence'         => 'category',
						'essence_id'      => $category_id,
					);
					
					$sug_data['setting'] = array(
						'translit_function'     =>$data['store_setting'][$store_id]['translit_function'][$language_id],
						'formula'               =>$data['store_setting'][$store_id]['formula'][$language_id],
						'delimiter_char'        =>$data['store_setting'][$store_id]['delimiter_char'][$language_id],
						'change_delimiter_char' =>$data['store_setting'][$store_id]['change_delimiter_char'][$language_id],
						'rewrite_on_save'       =>$data['store_setting'][$store_id]['rewrite_on_save'][$language_id],
						'custom_replace_from'   =>$data['store_setting'][$store_id]['custom_replace_from'][$language_id],
						'custom_replace_to'     =>$data['store_setting'][$store_id]['custom_replace_to'][$language_id],
					);
        
          $sug_data['store_id'] = $store_id;

					$data['category_seo_url'][$store_id][$language_id] = $this->load->controller('extension/module/seo_url_generator/generateSeoUrl', $sug_data);

					$sug_log->write(4, $data['category_seo_url'][$store_id][$language_id], 'model/category.php :: addCategory() : $data["category_seo_url"][$store_id][$language_id]');
				}
			}	
			
		}

		$sug_log->write(4, $data['category_seo_url'], 'model/category.php :: addCategory() : $data["category_seo_url"] AFTER SUG');
		// SEO URL Generator . end

		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}

			else {
				if ($this->config->get('theme_oct_ultrastore_seo_url_status')) {
					$this->load->model('octemplates/widgets/oct_seogeneration');
					
					$this->model_octemplates_widgets_oct_seogeneration->seoUrlGenerator('category', (int)$language_id, (int)$store_id, $data, (int)$category_id);
				}
			}
			
				}
			}
		}
		
		// Set which layout to use with this category
		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
				// blog articles relation
if (isset($data['oct_blogarticle']) && is_array($data['oct_blogarticle'])) {
    $this->setCategoryBlogArticles($category_id, $data['oct_blogarticle']);
} else {
    $this->setCategoryBlogArticles($category_id, []);
}


		$this->cache->delete('category');

		return $category_id;
	}

	public function editCategory($category_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");

				if(isset($value['default_meta'])) {
					$this->db->query("UPDATE " . DB_PREFIX . "category_description SET default_meta = '".(int)$value['default_meta']."' WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$language_id . "'");
				}
			
		}
		
		// blog articles relation
if (isset($data['oct_blogarticle']) && is_array($data['oct_blogarticle'])) {
    $this->setCategoryBlogArticles((int)$category_id, $data['oct_blogarticle']);
} else {
    $this->setCategoryBlogArticles((int)$category_id, []);
}						   

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE path_id = '" . (int)$category_id . "' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

				$path = array();

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_path['category_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");


		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq WHERE category_id = '" . (int)$category_id . "' AND type = 'category'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq_description WHERE category_id = '" . (int)$category_id . "' AND type = 'category'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq WHERE category_id = '" . (int)$category_id . "' AND type = 'category_products'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq_description WHERE category_id = '" . (int)$category_id . "' AND type = 'category_products'");
		if (isset($data['faq'])) {
			foreach ($data['faq'] as $category_faq) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq SET category_id = '" . (int)$category_id . "', `link` = '" . $this->db->escape($category_faq['link']) . "', `sort_order` = '" . (int)$category_faq['sort_order'] . "', type = 'category'");
				$faq_id = $this->db->getLastId();
				foreach ($category_faq['faq_data'] as $language_id => $value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq_description SET faq_id = '" . (int)$faq_id . "', category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', answer = '" . $this->db->escape($value['answer']) . "', type = 'category'");
				}
			}
		}
		if (isset($data['faq_products'])) {
			foreach ($data['faq_products'] as $category_faq) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq SET category_id = '" . (int)$category_id . "', `link` = '', `sort_order` = '" . (int)$category_faq['sort_order'] . "', type = 'category_products'");
				$faq_id = $this->db->getLastId();
				foreach ($category_faq['faq_data'] as $language_id => $value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq_description SET faq_id = '" . (int)$faq_id . "', category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', answer = '" . $this->db->escape($value['answer']) . "', type = 'category_products'");
				}
			}
		}
		
		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		/* fixed darkedhart */
		if (isset($data['product_related'])) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "' ");
			foreach ($data['product_related'] as $product_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}elseif ((int)$category_id == 812){
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "' ");
		}
		/* / fixed darkedhart */
		// SEO URL

		/* SEO URL Generator . begin
		------------------------------------------------------------------------- */	
		// В случае редактирования из админки мы имеем сразу 3 ЧПУ
		// 1 - $keyword_old - тот, который есть в базе на момент до редактирования - именно для него нужен редирект (!)
		// 2 - $data['category_seo_url'][$store_id][$language_id] - тот, который введен в форму - он может совпадать с $keyword_old, быть введенным вручную или вообще отсутствовать
		// 3 - $keyword_new - тот, который генерируется автоматически, в случае, когда это необходимо
		
		$this->load->model('extension/module/seo_url_generator');

		$sug_log = new StdeLog('seo_url_generator');
		$sug_log->setDebug($this->config->get('module_seo_url_generator_debug'));

		$sug_log->write(2, 'model/category.php :: editCategory() is called');

		$sug_log->write(4, $data['category_seo_url'], 'model/category.php :: editCategory() : $data["category_seo_url"] BEFORE SUG');

		if (isset($data['seo_url_generator_redirects'])) {
			$sug_log->write(4, $data['seo_url_generator_redirects'], 'model/category.php :: editCategory() : $data["seo_url_generator_redirects"]');
		}

		if (isset($data['seo_url_generator_front_works'])) {
			$sug_front_ok = true;
			$sug_log->write(4, 'ISSET', 'model/category.php :: editCategory() : $data["seo_url_generator_front_works"]');
		} else {
			$sug_front_ok = false;
			$sug_log->write(4, 'NULL', 'model/category.php :: editCategory() : $data["seo_url_generator_front_works"]');
		}


		#
		# SETTING
		#

		$sug_data['setting'] = array();

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('setting/setting');
		
		$data['store_setting'] = array();
		
		foreach ($data['category_seo_url'] as $store_id => $store) {
			$store_setting = $this->model_setting_setting->getSetting('module_seo_url_generator', $store_id);

			$data['store_setting'][$store_id]['language']              = $store_setting['module_seo_url_generator_language'];
			$data['store_setting'][$store_id]['translit_function']     = $store_setting['module_seo_url_generator_translit_function'];
			$data['store_setting'][$store_id]['formula']               = $store_setting['module_seo_url_generator_category_formula'];	
			$data['store_setting'][$store_id]['delimiter_char']        = $store_setting['module_seo_url_generator_delimiter_char'];			
			$data['store_setting'][$store_id]['change_delimiter_char'] = $store_setting['module_seo_url_generator_change_delimiter_char'];
			$data['store_setting'][$store_id]['rewrite_on_save']       = $store_setting['module_seo_url_generator_rewrite_on_save'];
			$data['store_setting'][$store_id]['custom_replace_from']   = $store_setting['module_seo_url_generator_custom_replace_from'];
			$data['store_setting'][$store_id]['custom_replace_to']     = $store_setting['module_seo_url_generator_custom_replace_to'];
		}

		$keywords_old = $this->model_extension_module_seo_url_generator->getURLs('category_id', $this->request->get['category_id']);

		$sug_log->write(4, $keywords_old, 'model/category.php :: editCategory() : $keywords_old');

		$redirects_exist = $this->model_extension_module_seo_url_generator->getRedirects('category_id', $this->request->get['category_id']);

		$sug_log->write(4, $redirects_exist, 'model/category.php :: editCategory() : $redirects_exist');


		#
		# ITTERATIONS
		#

		foreach ($data['category_seo_url'] as $store_id => $store) {
			foreach ($store as $language_id => $url) {				

				# SEO URL PROCESSING

				$sug_data = array(
					'name'            => $data['category_description'][$data['store_setting'][$store_id]['language'][$language_id]]['name'],
					'primary_key'     => 'category_id',
					'essence'         => 'category',
					'essence_id'      => $this->request->get['category_id'],
				);

				$sug_data['setting'] = array(
					'translit_function'     =>$data['store_setting'][$store_id]['translit_function'][$language_id],
					'formula'               =>$data['store_setting'][$store_id]['formula'][$language_id],
					'delimiter_char'        =>$data['store_setting'][$store_id]['delimiter_char'][$language_id],
					'change_delimiter_char' =>$data['store_setting'][$store_id]['change_delimiter_char'][$language_id],
					'rewrite_on_save'       =>$data['store_setting'][$store_id]['rewrite_on_save'][$language_id],
					'custom_replace_from'   =>$data['store_setting'][$store_id]['custom_replace_from'][$language_id],
					'custom_replace_to'     =>$data['store_setting'][$store_id]['custom_replace_to'][$language_id],
				);
        
        $sug_data['store_id'] = $store_id;

				$data['category_seo_url'][$store_id][$language_id] = trim($data['category_seo_url'][$store_id][$language_id]);

				$sug_log->write(2, $data['category_seo_url'][$store_id][$language_id], 'model/category.php :: editCategory() : $data["category_seo_url"][$store_id][$language_id]');

				$sug_set_backend_autoredirects = false;

				$keyword_new = false;

				$keyword_old = count($keywords_old) > 0 && isset($keywords_old[$store_id][$language_id]) ? $keywords_old[$store_id][$language_id] : '';

				$sug_log->write(4, $keyword_old, 'model/category.php :: editCategory() : $keyword_old');

				$sug_log->write(4, $sug_data, 'model/category.php :: editCategory() : $sug_data');

				if (!$keyword_old && !$data['category_seo_url'][$store_id][$language_id]) {
					// Все понятно: старого ЧПУ нет, просто генерим новый. Редиректы не нужны.
					$sug_log->write(4, $keyword_old, 'model/category.php :: editCategory() : EMPTY $keyword_old & $data["category_seo_url"][$store_id][$language_id]');

					$data['category_seo_url'][$store_id][$language_id] = $this->load->controller('extension/module/seo_url_generator/generateSeoUrl', $sug_data);
					//$this->cache->delete('seo_pro');
					continue;
				}

				if (!$keyword_old && $data['category_seo_url'][$store_id][$language_id]) {
					// Снова все понятно: старого ЧПУ в базе нет, соглашаемся с ЧПУ из формы
					// Q?
					// А транлитировать этот ЧПУ надо или нет?..

					//$this->cache->delete('seo_pro');
					continue;
				}

				if ($keyword_old && !$data['category_seo_url'][$store_id][$language_id]) {
					// Просто используем существующий ЧПУ из базы
					$data['category_seo_url'][$store_id][$language_id] = $keyword_old;
				}

				if ($keyword_old && $data['category_seo_url'][$store_id][$language_id] && $keyword_old != $data['category_seo_url'][$store_id][$language_id]) {
					// Запускаем механизм редиректов, только если на фронте не работает
					if (!$sug_front_ok) {
						$sug_set_backend_autoredirects = true;
					}

					$keyword_new = $data['category_seo_url'][$store_id][$language_id];

					// Q?
					// А транлитировать этот ЧПУ надо или нет?..
					// Минуем актуализацию, итак понятно, что $keyword_old != $data['category_seo_url'][$store_id][$language_id]
					goto sug_edit_end;
				}

        if ($keyword_old && $data['category_seo_url'][$store_id][$language_id] && $keyword_old == $data['category_seo_url'][$store_id][$language_id] && $sug_data['setting']['rewrite_on_save']) {
					// Актуализация по данным сущности - название, другая формула
					// ставить ли редирект, будет понятно лишь после сравнения старого и нового ЧПУ

					$keyword_new = $this->load->controller('extension/module/seo_url_generator/generateSeoUrl', $sug_data);
				}

				// Make unique
				if ($keyword_new && !$this->model_extension_module_seo_url_generator->isUnique($keyword_new, $sug_data['primary_key'], $sug_data['essence_id'], $store_id)) {
					$keyword_new = $this->model_extension_module_seo_url_generator->makeUniqueUrl($keyword_new, $store_id);
				}

				// Актуализация по разделителю
				if ($keyword_new && $sug_data['setting']['rewrite_on_save']) {
					$sug_log->write(4, 'model/category.php :: editCategory() : Actualization by delimeter BEGIN');

					if ('donot' != $sug_data['setting']['change_delimiter_char']) {
						// Compare without delimiters
						$keyword_old_without_delimiters = preg_replace(array('|_+|', '|-+|'), array('', ''), $keyword_old);
						$keyword_new_without_delimiters = preg_replace(array('|_+|', '|-+|'), array('', ''), $keyword_new);

						$sug_log->write(3, $keyword_old_without_delimiters, 'generateSeoUrl() : $keyword_old_without_delimiters');
						$sug_log->write(3, $keyword_new_without_delimiters, 'generateSeoUrl() : $keyword_new_without_delimiters');

						if ($keyword_old_without_delimiters != $keyword_new_without_delimiters) {
							$sug_set_backend_autoredirects = true;
							$data['category_seo_url'][$store_id][$language_id] = $keyword_new;
						}
					} else {
						// Compare with delimiters
						if ($keyword_old != $keyword_new) {
							$sug_set_backend_autoredirects = true;
							$data['category_seo_url'][$store_id][$language_id] = $keyword_new;
						}
					}
				}

				// Write Redirect
				sug_edit_end:

				$sug_log->write(4, 'model/category.php :: editCategory() : sug_redirects BEGIN');

				$sug_log->write(4, $sug_set_backend_autoredirects, 'model/category.php :: editCategory() : $sug_set_backend_autoredirects');

				$sug_log->write(4, $keyword_new, 'model/category.php :: editCategory() : $keyword_new');

				// Удаляем все существующие редиректы из базы для данной сущности - на фронте предупреждение было выдано, чтобы не убирали из формы
				$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url_generator_redirects WHERE query = 'category_id=" . (int)$category_id . "' AND `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "'");

				// Обрабатываем редиректы из формы - все управление редриректами у пользователя на фронте!
				if (isset($data['seo_url_generator_redirects'][$store_id][$language_id]) && count($data['seo_url_generator_redirects'][$store_id][$language_id]) > 0) {
					$sug_log->write(4, $data['seo_url_generator_redirects'][$store_id][$language_id], 'model/category.php :: editCategory() : $data["seo_url_generator_redirects"][$store_id][$language_id]');

					$data['seo_url_generator_redirects'][$store_id][$language_id] = array_unique($data['seo_url_generator_redirects'][$store_id][$language_id]); // на всякий случай...

					foreach ($data['seo_url_generator_redirects'][$store_id][$language_id] as $redirect) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url_generator_redirects SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', seo_url_old = '" . $this->db->escape($redirect) . "', seo_url_actual = '" . $this->db->escape($data['category_seo_url'][$store_id][$language_id]) . "', query = 'category_id=" . (int)$category_id . "'");
							}
						}

				if ($sug_set_backend_autoredirects) {
					$sug_log->write(3, 'model/category.php :: editCategory() : Autoredirect was created on backend');

					// setRedirect() кроме того, что просто записывет текущий редирект, также обновляет новый ЧПУ для всех старый редиректов
					$this->model_extension_module_seo_url_generator->setRedirect($keyword_new, $keyword_old, $sug_data['primary_key'], $sug_data['essence_id'], $store_id, $language_id);
				}

			}
		}

		/* SEO URL Generator . end
		------------------------------------------------------------------------- */

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'category_id=" . (int)$category_id . "'");

		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}

			else {
				if ($this->config->get('theme_oct_ultrastore_seo_url_status')) {
					$this->load->model('octemplates/widgets/oct_seogeneration');
					
					$this->model_octemplates_widgets_oct_seogeneration->seoUrlGenerator('category', (int)$language_id, (int)$store_id, $data, (int)$category_id);
				}
			}
			
				}
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('category');
	}

	public function deleteCategory($category_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "'");

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_path WHERE path_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['category_id']);
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "'");
		// SEO URL Generator . begin
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url_generator_redirects WHERE query = 'category_id=" . (int)$category_id . "'");
		// SEO URL Generator . end
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'category_id=" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE category_id = '" . (int)$category_id . "'");

		$this->cache->delete('category');
	}

	public function repairCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category WHERE parent_id = '" . (int)$parent_id . "'");

		foreach ($query->rows as $category) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category['category_id'] . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$category['category_id'] . "', level = '" . (int)$level . "'");

			$this->repairCategories($category['category_id']);
		}
	}

	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.category_id) AS path FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row;
	}

	public function getCategories($data = array()) {
		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY cp.category_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}


		public function getCategoryFaq($category_id, $products = false) {
			$faq = [];
			$type = 'category';
			if ($products) {
				$type = 'category_products';
			}
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_faq WHERE category_id = '" . (int)$category_id . "' AND type = '" . $type . "' ORDER BY sort_order ASC");
			foreach ($query->rows as $row) {
				$faq_data = [];
				$description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_faq_description WHERE faq_id = '" . (int)$row['faq_id'] . "'");
					foreach ($description_query->rows as $result) {
						$faq_data[$result['language_id']] = [
							'question' => $result['question'],
							'answer' => $result['answer']
						];
					}
				$faq[] = [ 
					'sort_order' => $row['sort_order'],
					'link' => $row['link'],
					'data' => $row,
					'faq_data' => $faq_data
				];
			}
		 
			return $faq;
		}
		
	public function getCategoryDescriptions($category_id) {
		$category_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_description_data[$result['language_id']] = array(
				'name'             => $result['name'],

				'default_meta'             => $result['default_meta'],
			
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			);
		}

		return $category_description_data;
	}
	
	public function getCategoryPath($category_id) {
		$query = $this->db->query("SELECT category_id, path_id, level FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "'");

		return $query->rows;
	}
	
	public function getCategoryFilters($category_id) {
		$category_filter_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_filter_data[] = $result['filter_id'];
		}

		return $category_filter_data;
	}

	public function getCategoryStores($category_id) {
		$category_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_store_data[] = $result['store_id'];
		}

		return $category_store_data;
	}
	
	public function getCategorySeoUrls($category_id) {
		$category_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'category_id=" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $category_seo_url_data;
	}
	
	public function getCategoryLayouts($category_id) {
		$category_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $category_layout_data;
	}

	public function getTotalCategories() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category");

		return $query->row['total'];
	}
	
	public function getTotalCategoriesByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}	
public function setCategoryBlogArticles($category_id, $articles) {
    $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_oct_blogarticle
                      WHERE category_id = " . (int)$category_id);

    foreach ((array)$articles as $article_id) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_oct_blogarticle
                          SET category_id = " . (int)$category_id . ",
                              article_id = " . (int)$article_id);
    }
}

public function getCategoryBlogArticles($category_id) {
    $query = $this->db->query("SELECT article_id FROM " . DB_PREFIX . "category_to_oct_blogarticle
                               WHERE category_id = " . (int)$category_id);
    return array_column($query->rows, 'article_id');
}



private function getOctBlogDescTable() {
    $candidates = [
        DB_PREFIX . "oct_blogarticle_description",
        DB_PREFIX . "oct_blog_article_description",
        // Ukrainian comment from developers below
        DB_PREFIX . "oct_blogarticle_description_store", // рідше, але буває
    ];

    foreach ($candidates as $table) {
        $q = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape($table) . "'");
        if ($q->num_rows) {
            return $table;
        }
    }

    return '';
}

public function getOctBlogarticlesByIds($article_ids) {
    $article_ids = array_map('intval', (array)$article_ids);
    $article_ids = array_values(array_filter($article_ids));
    if (!$article_ids) return [];

    $desc_table = $this->getOctBlogDescTable();
    if (!$desc_table) return [];

    $in = implode(',', $article_ids);

    // Ukrainian comment from developers below
    // ВАЖЛИВО: blogarticle_id AS article_id
    $sql = "SELECT blogarticle_id AS article_id, name
            FROM `" . $desc_table . "`
            WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'
              AND blogarticle_id IN (" . $in . ")
            ORDER BY name ASC";

    return $this->db->query($sql)->rows;
}

public function getOctBlogarticlesAutocomplete($filter_name, $limit = 20) {
    $desc_table = $this->getOctBlogDescTable();
    if (!$desc_table) return [];

    $filter_name = trim((string)$filter_name);
    $limit = (int)$limit; if ($limit < 1) $limit = 20;

    // Ukrainian comment from developers below
    // ВАЖЛИВО: blogarticle_id AS article_id
    $sql = "SELECT blogarticle_id AS article_id, name
            FROM `" . $desc_table . "`
            WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'
              AND name LIKE '%" . $this->db->escape($filter_name) . "%'
            ORDER BY name ASC
            LIMIT " . (int)$limit;

    return $this->db->query($sql)->rows;
}


public function getOctBlogArticlesByName($filter_name) {
    $sql = "SELECT ba.article_id, bad.name
            FROM " . DB_PREFIX . "oct_blogarticle ba
            LEFT JOIN " . DB_PREFIX . "oct_blogarticle_description bad
              ON (ba.article_id = bad.article_id)
            WHERE bad.language_id = " . (int)$this->config->get('config_language_id') . "
              AND bad.name LIKE '%" . $this->db->escape($filter_name) . "%'
            ORDER BY bad.name ASC
            LIMIT 20";

    return $this->db->query($sql)->rows;
}
}