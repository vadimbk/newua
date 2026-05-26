<?php
class ModelCatalogManufacturer extends Model {
	public function addManufacturer($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$manufacturer_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		}


		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND type = 'manufacturer'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND type = 'manufacturer'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND type = 'manufacturer_products'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND type = 'manufacturer_products'");
		if (isset($data['faq'])) {
			foreach ($data['faq'] as $manufacturer_faq) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq SET manufacturer_id = '" . (int)$manufacturer_id . "', `sort_order` = '" . (int)$manufacturer_faq['sort_order'] . "', type = 'manufacturer'");
				$faq_id = $this->db->getLastId(); 
				foreach ($manufacturer_faq['faq_data'] as $language_id => $value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq_description SET faq_id = '" . (int)$faq_id . "', manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', answer = '" . $this->db->escape($value['answer']) . "', type = 'manufacturer'");
				}
			}
		} 
		if (isset($data['faq_products'])) {
			foreach ($data['faq_products'] as $manufacturer_faq) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq SET manufacturer_id = '" . (int)$manufacturer_id . "', `sort_order` = '" . (int)$manufacturer_faq['sort_order'] . "', type = 'manufacturer_products'");
				$faq_id = $this->db->getLastId(); 
				foreach ($manufacturer_faq['faq_data'] as $language_id => $value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq_description SET faq_id = '" . (int)$faq_id . "', manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', answer = '" . $this->db->escape($value['answer']) . "', type = 'manufacturer_products'");
				}
			}
		}
		
		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
				
		// SEO URL
		// SEO URL Generator . begin
		// manufacturer_id not exist in controller when add() manufacturer
		
		$this->load->model('extension/module/seo_url_generator');

		$sug_log = new StdeLog('seo_url_generator');
		$sug_log->setDebug($this->config->get('module_seo_url_generator_debug'));

		$sug_log->write(4, 'model/manufacturer.php :: addManufacturer() is called');

		$sug_log->write(4, $data['manufacturer_seo_url'], 'model/manufacturer.php :: addManufacturer() : $data["manufacturer_seo_url"] BEFORE SUG');
		
		#
		# SETTING
		#

		$sug_data['setting'] = array();

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('setting/setting');
		
		$data['store_setting'] = array();
		
		foreach ($data['manufacturer_seo_url'] as $store_id => $store) {
			$store_setting = $this->model_setting_setting->getSetting('module_seo_url_generator', $store_id);

			$data['store_setting'][$store_id]['language']              = $store_setting['module_seo_url_generator_language'];
			$data['store_setting'][$store_id]['translit_function']     = $store_setting['module_seo_url_generator_translit_function'];
			$data['store_setting'][$store_id]['formula']               = $store_setting['module_seo_url_generator_manufacturer_formula'];	
			$data['store_setting'][$store_id]['delimiter_char']        = $store_setting['module_seo_url_generator_delimiter_char'];			
			$data['store_setting'][$store_id]['change_delimiter_char'] = $store_setting['module_seo_url_generator_change_delimiter_char'];
			$data['store_setting'][$store_id]['rewrite_on_save']       = $store_setting['module_seo_url_generator_rewrite_on_save'];
			$data['store_setting'][$store_id]['custom_replace_from']   = $store_setting['module_seo_url_generator_custom_replace_from'];
			$data['store_setting'][$store_id]['custom_replace_to']     = $store_setting['module_seo_url_generator_custom_replace_to'];
		}

		foreach ($data['manufacturer_seo_url'] as $store_id => $store) {
			foreach ($store as $language_id => $url) {
				if (empty($data['manufacturer_seo_url'][$store_id][$language_id])) {
					$sug_data = array(
						'name'            => $data['name'],
						'primary_key'     => 'manufacturer_id',
						'essence'         => 'manufacturer',
						'essence_id'      => $manufacturer_id,
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

					$data['manufacturer_seo_url'][$store_id][$language_id] = $this->load->controller('extension/module/seo_url_generator/generateSeoUrl', $sug_data);

					$sug_log->write(4, $data['manufacturer_seo_url'][$store_id][$language_id], 'model/manufacturer.php :: addManufacturer() : $data["manufacturer_seo_url"][$store_id][$language_id]');
				}
			}	
			
		}

		$sug_log->write(4, $data['manufacturer_seo_url'], 'model/manufacturer.php :: addManufacturer() : $data["manufacturer_seo_url"] AFTER SUG');
		// SEO URL Generator . end

		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}

			else {
				if ($this->config->get('theme_oct_ultrastore_seo_url_status')) {
					$this->load->model('octemplates/widgets/oct_seogeneration');
					
					$this->model_octemplates_widgets_oct_seogeneration->seoUrlGenerator('manufacturer', (int)$language_id, (int)$store_id, $data, (int)$manufacturer_id);
				}
			}
			
				}
			}
		}
		
		$this->cache->delete('manufacturer');

		return $manufacturer_id;
	}

	public function editManufacturer($manufacturer_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");


		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND type = 'manufacturer'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND type = 'manufacturer'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND type = 'manufacturer_products'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND type = 'manufacturer_products'");
		if (isset($data['faq'])) {
			foreach ($data['faq'] as $manufacturer_faq) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq SET manufacturer_id = '" . (int)$manufacturer_id . "', `sort_order` = '" . (int)$manufacturer_faq['sort_order'] . "', type = 'manufacturer'");
				$faq_id = $this->db->getLastId(); 
				foreach ($manufacturer_faq['faq_data'] as $language_id => $value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq_description SET faq_id = '" . (int)$faq_id . "', manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', answer = '" . $this->db->escape($value['answer']) . "', type = 'manufacturer'");
				}
			}
		} 
		if (isset($data['faq_products'])) {
			foreach ($data['faq_products'] as $manufacturer_faq) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq SET manufacturer_id = '" . (int)$manufacturer_id . "', `sort_order` = '" . (int)$manufacturer_faq['sort_order'] . "', type = 'manufacturer_products'");
				$faq_id = $this->db->getLastId(); 
				foreach ($manufacturer_faq['faq_data'] as $language_id => $value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq_description SET faq_id = '" . (int)$faq_id . "', manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', answer = '" . $this->db->escape($value['answer']) . "', type = 'manufacturer_products'");
				}
			}
		}
		
		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
			}
		}



		/* SEO URL Generator . begin
		------------------------------------------------------------------------- */	
		// В случае редактирования из админки мы имеем сразу 3 ЧПУ
		// 1 - $keyword_old - тот, который есть в базе на момент до редактирования - именно для него нужен редирект (!)
		// 2 - $data['manufacturer_seo_url'][$store_id][$language_id] - тот, который введен в форму - он может совпадать с $keyword_old, быть введенным вручную или вообще отсутствовать
		// 3 - $keyword_new - тот, который генерируется автоматически, в случае, когда это необходимо
		
		$this->load->model('extension/module/seo_url_generator');

		$sug_log = new StdeLog('seo_url_generator');
		$sug_log->setDebug($this->config->get('module_seo_url_generator_debug'));

		$sug_log->write(2, 'model/manufacturer.php :: editManufacturer() is called');

		$sug_log->write(4, $data['manufacturer_seo_url'], 'model/manufacturer.php :: editManufacturer() : $data["manufacturer_seo_url"] BEFORE SUG');

		if (isset($data['seo_url_generator_redirects'])) {
			$sug_log->write(4, $data['seo_url_generator_redirects'], 'model/manufacturer.php :: editManufacturer() : $data["seo_url_generator_redirects"]');
		}

		if (isset($data['seo_url_generator_front_works'])) {
			$sug_front_ok = true;
			$sug_log->write(4, 'ISSET', 'model/manufacturer.php :: editManufacturer() : $data["seo_url_generator_front_works"]');
		} else {
			$sug_front_ok = false;
			$sug_log->write(4, 'NULL', 'model/manufacturer.php :: editManufacturer() : $data["seo_url_generator_front_works"]');
		}


		#
		# SETTING
		#

		$sug_data['setting'] = array();

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('setting/setting');
		
		$data['store_setting'] = array();
		
		foreach ($data['manufacturer_seo_url'] as $store_id => $store) {
			$store_setting = $this->model_setting_setting->getSetting('module_seo_url_generator', $store_id);

			$data['store_setting'][$store_id]['language']              = $store_setting['module_seo_url_generator_language'];
			$data['store_setting'][$store_id]['translit_function']     = $store_setting['module_seo_url_generator_translit_function'];
			$data['store_setting'][$store_id]['formula']               = $store_setting['module_seo_url_generator_manufacturer_formula'];	
			$data['store_setting'][$store_id]['delimiter_char']        = $store_setting['module_seo_url_generator_delimiter_char'];			
			$data['store_setting'][$store_id]['change_delimiter_char'] = $store_setting['module_seo_url_generator_change_delimiter_char'];
			$data['store_setting'][$store_id]['rewrite_on_save']       = $store_setting['module_seo_url_generator_rewrite_on_save'];
			$data['store_setting'][$store_id]['custom_replace_from']   = $store_setting['module_seo_url_generator_custom_replace_from'];
			$data['store_setting'][$store_id]['custom_replace_to']     = $store_setting['module_seo_url_generator_custom_replace_to'];
		}

		$keywords_old = $this->model_extension_module_seo_url_generator->getURLs('manufacturer_id', $this->request->get['manufacturer_id']);

		$sug_log->write(4, $keywords_old, 'model/manufacturer.php :: editManufacturer() : $keywords_old');

		$redirects_exist = $this->model_extension_module_seo_url_generator->getRedirects('manufacturer_id', $this->request->get['manufacturer_id']);

		$sug_log->write(4, $redirects_exist, 'model/manufacturer.php :: editManufacturer() : $redirects_exist');


		#
		# ITTERATIONS
		#

		foreach ($data['manufacturer_seo_url'] as $store_id => $store) {
			foreach ($store as $language_id => $url) {				

				# SEO URL PROCESSING

				$sug_data = array(
					'name'            => $data['name'],
					'primary_key'     => 'manufacturer_id',
					'essence'         => 'manufacturer',
					'essence_id'      => $this->request->get['manufacturer_id'],
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

				$data['manufacturer_seo_url'][$store_id][$language_id] = trim($data['manufacturer_seo_url'][$store_id][$language_id]);

				$sug_log->write(2, $data['manufacturer_seo_url'][$store_id][$language_id], 'model/manufacturer.php :: editManufacturer() : $data["manufacturer_seo_url"][$store_id][$language_id]');

				$sug_set_backend_autoredirects = false;

				$keyword_new = false;

				$keyword_old = count($keywords_old) > 0 && isset($keywords_old[$store_id][$language_id]) ? $keywords_old[$store_id][$language_id] : '';

				$sug_log->write(4, $keyword_old, 'model/manufacturer.php :: editManufacturer() : $keyword_old');

				$sug_log->write(4, $sug_data, 'model/manufacturer.php :: editManufacturer() : $sug_data');

				if (!$keyword_old && !$data['manufacturer_seo_url'][$store_id][$language_id]) {
					// Все понятно: старого ЧПУ нет, просто генерим новый. Редиректы не нужны.
					$sug_log->write(4, $keyword_old, 'model/manufacturer.php :: editManufacturer() : EMPTY $keyword_old & $data["manufacturer_seo_url"][$store_id][$language_id]');

					$data['manufacturer_seo_url'][$store_id][$language_id] = $this->load->controller('extension/module/seo_url_generator/generateSeoUrl', $sug_data);
					//$this->cache->delete('seo_pro');
					continue;
				}

				if (!$keyword_old && $data['manufacturer_seo_url'][$store_id][$language_id]) {
					// Снова все понятно: старого ЧПУ в базе нет, соглашаемся с ЧПУ из формы
					// Q?
					// А транлитировать этот ЧПУ надо или нет?..

					//$this->cache->delete('seo_pro');
					continue;
				}

				if ($keyword_old && !$data['manufacturer_seo_url'][$store_id][$language_id]) {
					// Просто используем существующий ЧПУ из базы
					$data['manufacturer_seo_url'][$store_id][$language_id] = $keyword_old;
				}

				if ($keyword_old && $data['manufacturer_seo_url'][$store_id][$language_id] && $keyword_old != $data['manufacturer_seo_url'][$store_id][$language_id]) {
					// Запускаем механизм редиректов, только если на фронте не работает
					if (!$sug_front_ok) {
						$sug_set_backend_autoredirects = true;
					}

					$keyword_new = $data['manufacturer_seo_url'][$store_id][$language_id];

					// Q?
					// А транлитировать этот ЧПУ надо или нет?..
					// Минуем актуализацию, итак понятно, что $keyword_old != $data['manufacturer_seo_url'][$store_id][$language_id]
					goto sug_edit_end;
				}

        if ($keyword_old && $data['manufacturer_seo_url'][$store_id][$language_id] && $keyword_old == $data['manufacturer_seo_url'][$store_id][$language_id] && $sug_data['setting']['rewrite_on_save']) {
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
					$sug_log->write(4, 'model/manufacturer.php :: editManufacturer() : Actualization by delimeter BEGIN');

					if ('donot' != $sug_data['setting']['change_delimiter_char']) {
						// Compare without delimiters
						$keyword_old_without_delimiters = preg_replace(array('|_+|', '|-+|'), array('', ''), $keyword_old);
						$keyword_new_without_delimiters = preg_replace(array('|_+|', '|-+|'), array('', ''), $keyword_new);

						$sug_log->write(3, $keyword_old_without_delimiters, 'generateSeoUrl() : $keyword_old_without_delimiters');
						$sug_log->write(3, $keyword_new_without_delimiters, 'generateSeoUrl() : $keyword_new_without_delimiters');

						if ($keyword_old_without_delimiters != $keyword_new_without_delimiters) {
							$sug_set_backend_autoredirects = true;
							$data['manufacturer_seo_url'][$store_id][$language_id] = $keyword_new;
						}
					} else {
						// Compare with delimiters
						if ($keyword_old != $keyword_new) {
							$sug_set_backend_autoredirects = true;
							$data['manufacturer_seo_url'][$store_id][$language_id] = $keyword_new;
						}
					}
				}

				// Write Redirect
				sug_edit_end:

				$sug_log->write(4, 'model/manufacturer.php :: editManufacturer() : sug_redirects BEGIN');

				$sug_log->write(4, $sug_set_backend_autoredirects, 'model/manufacturer.php :: editManufacturer() : $sug_set_backend_autoredirects');

				$sug_log->write(4, $keyword_new, 'model/manufacturer.php :: editManufacturer() : $keyword_new');

				// Удаляем все существующие редиректы из базы для данной сущности - на фронте предупреждение было выдано, чтобы не убирали из формы
				$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url_generator_redirects WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "' AND `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "'");

				// Обрабатываем редиректы из формы - все управление редриректами у пользователя на фронте!
				if (isset($data['seo_url_generator_redirects'][$store_id][$language_id]) && count($data['seo_url_generator_redirects'][$store_id][$language_id]) > 0) {
					$sug_log->write(4, $data['seo_url_generator_redirects'][$store_id][$language_id], 'model/manufacturer.php :: editManufacturer() : $data["seo_url_generator_redirects"][$store_id][$language_id]');

					$data['seo_url_generator_redirects'][$store_id][$language_id] = array_unique($data['seo_url_generator_redirects'][$store_id][$language_id]); // на всякий случай...

					foreach ($data['seo_url_generator_redirects'][$store_id][$language_id] as $redirect) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url_generator_redirects SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', seo_url_old = '" . $this->db->escape($redirect) . "', seo_url_actual = '" . $this->db->escape($data['manufacturer_seo_url'][$store_id][$language_id]) . "', query = 'manufacturer_id=" . (int)$manufacturer_id . "'");
							}
						}

				if ($sug_set_backend_autoredirects) {
					$sug_log->write(3, 'model/manufacturer.php :: editManufacturer() : Autoredirect was created on backend');

					// setRedirect() кроме того, что просто записывет текущий редирект, также обновляет новый ЧПУ для всех старый редиректов
					$this->model_extension_module_seo_url_generator->setRedirect($keyword_new, $keyword_old, $sug_data['primary_key'], $sug_data['essence_id'], $store_id, $language_id);
				}

			}
		}
		/* SEO URL Generator . end
		------------------------------------------------------------------------- */

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}

			else {
				if ($this->config->get('theme_oct_ultrastore_seo_url_status')) {
					$this->load->model('octemplates/widgets/oct_seogeneration');
					
					$this->model_octemplates_widgets_oct_seogeneration->seoUrlGenerator('manufacturer', (int)$language_id, (int)$store_id, $data, (int)$manufacturer_id);
				}
			}
			
				}
			}
		}

		$this->cache->delete('manufacturer');
	}

	public function deleteManufacturer($manufacturer_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		$this->cache->delete('manufacturer');
	}

	public function getManufacturer($manufacturer_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row;
	}

	public function getManufacturers($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "manufacturer";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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


		public function getManufacturerFaq($manufacturer_id, $products = false) {
			$faq = [];
			$type = 'manufacturer';
			if ($products) {
				$type = 'manufacturer_products';
			}
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_faq WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND type = '" . $type . "' ORDER BY sort_order ASC");
			foreach($query->rows as $row) {
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
					'data' => $row,
					'faq_data' => $faq_data,
				];	
			}
			return $faq;
		}
		
	public function getManufacturerStores($manufacturer_id) {
		$manufacturer_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_store_data[] = $result['store_id'];
		}

		return $manufacturer_store_data;
	}
	
	public function getManufacturerSeoUrls($manufacturer_id) {
		$manufacturer_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $manufacturer_seo_url_data;
	}
	
	public function getTotalManufacturers() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer");

		return $query->row['total'];
	}
}
