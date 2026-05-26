<?php
class ModelCatalogInformation extends Model {
	public function addInformation($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "information SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "'");

		$information_id = $this->db->getLastId();

		foreach ($data['information_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_description SET information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}


		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq WHERE information_id = '" . (int)$information_id . "' AND type = 'information'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq_description WHERE information_id = '" . (int)$information_id . "' AND type = 'information'");
		if (isset($data['faq'])) {
			foreach ($data['faq'] as $information_faq) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq SET information_id = '" . (int)$information_id . "', `sort_order` = '" . (int)$information_faq['sort_order'] . "', type = 'information'");
				$faq_id = $this->db->getLastId();
				foreach ($information_faq['faq_data'] as $language_id => $value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq_description SET faq_id = '" . (int)$faq_id . "', information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', answer = '" . $this->db->escape($value['answer']) . "', type = 'information'");
				}
			}
		} 
		
		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_store SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		// SEO URL Generator . begin
		// information_id not exist in controller when add() information
		
		$this->load->model('extension/module/seo_url_generator');

		$sug_log = new StdeLog('seo_url_generator');
		$sug_log->setDebug($this->config->get('module_seo_url_generator_debug'));

		$sug_log->write(4, 'model/information.php :: addInformation() is called');

		$sug_log->write(4, $data['information_seo_url'], 'model/information.php :: addInformation() : $data["information_seo_url"] BEFORE SUG');
		
		#
		# SETTING
		#

		$sug_data['setting'] = array();

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('setting/setting');
		
		$data['store_setting'] = array();
		
		foreach ($data['information_seo_url'] as $store_id => $store) {
			$store_setting = $this->model_setting_setting->getSetting('module_seo_url_generator', $store_id);

			$data['store_setting'][$store_id]['language']              = $store_setting['module_seo_url_generator_language'];
			$data['store_setting'][$store_id]['translit_function']     = $store_setting['module_seo_url_generator_translit_function'];
			$data['store_setting'][$store_id]['formula']               = $store_setting['module_seo_url_generator_information_formula'];	
			$data['store_setting'][$store_id]['delimiter_char']        = $store_setting['module_seo_url_generator_delimiter_char'];			
			$data['store_setting'][$store_id]['change_delimiter_char'] = $store_setting['module_seo_url_generator_change_delimiter_char'];
			$data['store_setting'][$store_id]['rewrite_on_save']       = $store_setting['module_seo_url_generator_rewrite_on_save'];
			$data['store_setting'][$store_id]['custom_replace_from']   = $store_setting['module_seo_url_generator_custom_replace_from'];
			$data['store_setting'][$store_id]['custom_replace_to']     = $store_setting['module_seo_url_generator_custom_replace_to'];
		}

		foreach ($data['information_seo_url'] as $store_id => $store) {
			foreach ($store as $language_id => $url) {
				if (empty($data['information_seo_url'][$store_id][$language_id])) {
					$sug_data = array(
						'name'            => $data['information_description'][$data['store_setting'][$store_id]['language'][$language_id]]['title'],
						'primary_key'     => 'information_id',
						'essence'         => 'information',
						'essence_id'      => $information_id,
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

					$data['information_seo_url'][$store_id][$language_id] = $this->load->controller('extension/module/seo_url_generator/generateSeoUrl', $sug_data);

					$sug_log->write(4, $data['information_seo_url'][$store_id][$language_id], 'model/information.php :: addInformation() : $data["information_seo_url"][$store_id][$language_id]');
				}
			}	
			
		}

		$sug_log->write(4, $data['information_seo_url'], 'model/information.php :: addInformation() : $data["information_seo_url"] AFTER SUG');
		// SEO URL Generator . end

		if (isset($data['information_seo_url'])) {
			foreach ($data['information_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'information_id=" . (int)$information_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}

			else {
				if ($this->config->get('theme_oct_ultrastore_seo_url_status')) {
					$this->load->model('octemplates/widgets/oct_seogeneration');
					
					$this->model_octemplates_widgets_oct_seogeneration->seoUrlGenerator('information', (int)$language_id, (int)$store_id, $data, (int)$information_id);
				}
			}
			
				}
			}
		}
		
		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_layout SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('information');

		return $information_id;
	}

	public function editInformation($information_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "information SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "' WHERE information_id = '" . (int)$information_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "'");

		foreach ($data['information_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_description SET information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "information_to_store WHERE information_id = '" . (int)$information_id . "'");


		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq WHERE information_id = '" . (int)$information_id . "' AND type = 'information'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_faq_description WHERE information_id = '" . (int)$information_id . "' AND type = 'information'");
		if (isset($data['faq'])) {
			foreach ($data['faq'] as $information_faq) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq SET information_id = '" . (int)$information_id . "', `sort_order` = '" . (int)$information_faq['sort_order'] . "', type = 'information'");
				$faq_id = $this->db->getLastId();
				foreach ($information_faq['faq_data'] as $language_id => $value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_faq_description SET faq_id = '" . (int)$faq_id . "', information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', answer = '" . $this->db->escape($value['answer']) . "', type = 'information'");
				}
			}
		} 
		
		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_store SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "'");
			}
		}



		/* SEO URL Generator . begin
		------------------------------------------------------------------------- */	
		// В случае редактирования из админки мы имеем сразу 3 ЧПУ
		// 1 - $keyword_old - тот, который есть в базе на момент до редактирования - именно для него нужен редирект (!)
		// 2 - $data['information_seo_url'][$store_id][$language_id] - тот, который введен в форму - он может совпадать с $keyword_old, быть введенным вручную или вообще отсутствовать
		// 3 - $keyword_new - тот, который генерируется автоматически, в случае, когда это необходимо
		
		$this->load->model('extension/module/seo_url_generator');

		$sug_log = new StdeLog('seo_url_generator');
		$sug_log->setDebug($this->config->get('module_seo_url_generator_debug'));

		$sug_log->write(2, 'model/information.php :: editInformation() is called');

		$sug_log->write(4, $data['information_seo_url'], 'model/information.php :: editInformation() : $data["information_seo_url"] BEFORE SUG');

		if (isset($data['seo_url_generator_redirects'])) {
			$sug_log->write(4, $data['seo_url_generator_redirects'], 'model/information.php :: editInformation() : $data["seo_url_generator_redirects"]');
		}

		if (isset($data['seo_url_generator_front_works'])) {
			$sug_front_ok = true;
			$sug_log->write(4, 'ISSET', 'model/information.php :: editInformation() : $data["seo_url_generator_front_works"]');
		} else {
			$sug_front_ok = false;
			$sug_log->write(4, 'NULL', 'model/information.php :: editInformation() : $data["seo_url_generator_front_works"]');
		}


		#
		# SETTING
		#

		$sug_data['setting'] = array();

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('setting/setting');
		
		$data['store_setting'] = array();
		
		foreach ($data['information_seo_url'] as $store_id => $store) {
			$store_setting = $this->model_setting_setting->getSetting('module_seo_url_generator', $store_id);

			$data['store_setting'][$store_id]['language']              = $store_setting['module_seo_url_generator_language'];
			$data['store_setting'][$store_id]['translit_function']     = $store_setting['module_seo_url_generator_translit_function'];
			$data['store_setting'][$store_id]['formula']               = $store_setting['module_seo_url_generator_information_formula'];	
			$data['store_setting'][$store_id]['delimiter_char']        = $store_setting['module_seo_url_generator_delimiter_char'];			
			$data['store_setting'][$store_id]['change_delimiter_char'] = $store_setting['module_seo_url_generator_change_delimiter_char'];
			$data['store_setting'][$store_id]['rewrite_on_save']       = $store_setting['module_seo_url_generator_rewrite_on_save'];
			$data['store_setting'][$store_id]['custom_replace_from']   = $store_setting['module_seo_url_generator_custom_replace_from'];
			$data['store_setting'][$store_id]['custom_replace_to']     = $store_setting['module_seo_url_generator_custom_replace_to'];
		}

		$keywords_old = $this->model_extension_module_seo_url_generator->getURLs('information_id', $this->request->get['information_id']);

		$sug_log->write(4, $keywords_old, 'model/information.php :: editInformation() : $keywords_old');

		$redirects_exist = $this->model_extension_module_seo_url_generator->getRedirects('information_id', $this->request->get['information_id']);

		$sug_log->write(4, $redirects_exist, 'model/information.php :: editInformation() : $redirects_exist');


		#
		# ITTERATIONS
		#

		foreach ($data['information_seo_url'] as $store_id => $store) {
			foreach ($store as $language_id => $url) {				

				# SEO URL PROCESSING

				$sug_data = array(
					'name'            => $data['information_description'][$data['store_setting'][$store_id]['language'][$language_id]]['title'],
					'primary_key'     => 'information_id',
					'essence'         => 'information',
					'essence_id'      => $this->request->get['information_id'],
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

				$data['information_seo_url'][$store_id][$language_id] = trim($data['information_seo_url'][$store_id][$language_id]);

				$sug_log->write(2, $data['information_seo_url'][$store_id][$language_id], 'model/information.php :: editInformation() : $data["information_seo_url"][$store_id][$language_id]');

				$sug_set_backend_autoredirects = false;

				$keyword_new = false;

				$keyword_old = count($keywords_old) > 0 && isset($keywords_old[$store_id][$language_id]) ? $keywords_old[$store_id][$language_id] : '';

				$sug_log->write(4, $keyword_old, 'model/information.php :: editInformation() : $keyword_old');

				$sug_log->write(4, $sug_data, 'model/information.php :: editInformation() : $sug_data');

				if (!$keyword_old && !$data['information_seo_url'][$store_id][$language_id]) {
					// Все понятно: старого ЧПУ нет, просто генерим новый. Редиректы не нужны.
					$sug_log->write(4, $keyword_old, 'model/information.php :: editInformation() : EMPTY $keyword_old & $data["information_seo_url"][$store_id][$language_id]');

					$data['information_seo_url'][$store_id][$language_id] = $this->load->controller('extension/module/seo_url_generator/generateSeoUrl', $sug_data);
					//$this->cache->delete('seo_pro');
					continue;
				}

				if (!$keyword_old && $data['information_seo_url'][$store_id][$language_id]) {
					// Снова все понятно: старого ЧПУ в базе нет, соглашаемся с ЧПУ из формы
					// Q?
					// А транлитировать этот ЧПУ надо или нет?..

					//$this->cache->delete('seo_pro');
					continue;
				}

				if ($keyword_old && !$data['information_seo_url'][$store_id][$language_id]) {
					// Просто используем существующий ЧПУ из базы
					$data['information_seo_url'][$store_id][$language_id] = $keyword_old;
				}

				if ($keyword_old && $data['information_seo_url'][$store_id][$language_id] && $keyword_old != $data['information_seo_url'][$store_id][$language_id]) {
					// Запускаем механизм редиректов, только если на фронте не работает
					if (!$sug_front_ok) {
						$sug_set_backend_autoredirects = true;
					}

					$keyword_new = $data['information_seo_url'][$store_id][$language_id];

					// Q?
					// А транлитировать этот ЧПУ надо или нет?..
					// Минуем актуализацию, итак понятно, что $keyword_old != $data['information_seo_url'][$store_id][$language_id]
					goto sug_edit_end;
				}

        if ($keyword_old && $data['information_seo_url'][$store_id][$language_id] && $keyword_old == $data['information_seo_url'][$store_id][$language_id] && $sug_data['setting']['rewrite_on_save']) {
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
					$sug_log->write(4, 'model/information.php :: editInformation() : Actualization by delimeter BEGIN');

					if ('donot' != $sug_data['setting']['change_delimiter_char']) {
						// Compare without delimiters
						$keyword_old_without_delimiters = preg_replace(array('|_+|', '|-+|'), array('', ''), $keyword_old);
						$keyword_new_without_delimiters = preg_replace(array('|_+|', '|-+|'), array('', ''), $keyword_new);

						$sug_log->write(3, $keyword_old_without_delimiters, 'generateSeoUrl() : $keyword_old_without_delimiters');
						$sug_log->write(3, $keyword_new_without_delimiters, 'generateSeoUrl() : $keyword_new_without_delimiters');

						if ($keyword_old_without_delimiters != $keyword_new_without_delimiters) {
							$sug_set_backend_autoredirects = true;
							$data['information_seo_url'][$store_id][$language_id] = $keyword_new;
						}
					} else {
						// Compare with delimiters
						if ($keyword_old != $keyword_new) {
							$sug_set_backend_autoredirects = true;
							$data['information_seo_url'][$store_id][$language_id] = $keyword_new;
						}
					}
				}

				// Write Redirect
				sug_edit_end:

				$sug_log->write(4, 'model/information.php :: editInformation() : sug_redirects BEGIN');

				$sug_log->write(4, $sug_set_backend_autoredirects, 'model/information.php :: editInformation() : $sug_set_backend_autoredirects');

				$sug_log->write(4, $keyword_new, 'model/information.php :: editInformation() : $keyword_new');

				// Удаляем все существующие редиректы из базы для данной сущности - на фронте предупреждение было выдано, чтобы не убирали из формы
				$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url_generator_redirects WHERE query = 'information_id=" . (int)$information_id . "' AND `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "'");

				// Обрабатываем редиректы из формы - все управление редриректами у пользователя на фронте!
				if (isset($data['seo_url_generator_redirects'][$store_id][$language_id]) && count($data['seo_url_generator_redirects'][$store_id][$language_id]) > 0) {
					$sug_log->write(4, $data['seo_url_generator_redirects'][$store_id][$language_id], 'model/information.php :: editInformation() : $data["seo_url_generator_redirects"][$store_id][$language_id]');

					$data['seo_url_generator_redirects'][$store_id][$language_id] = array_unique($data['seo_url_generator_redirects'][$store_id][$language_id]); // на всякий случай...

					foreach ($data['seo_url_generator_redirects'][$store_id][$language_id] as $redirect) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url_generator_redirects SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', seo_url_old = '" . $this->db->escape($redirect) . "', seo_url_actual = '" . $this->db->escape($data['information_seo_url'][$store_id][$language_id]) . "', query = 'information_id=" . (int)$information_id . "'");
							}
						}

				if ($sug_set_backend_autoredirects) {
					$sug_log->write(3, 'model/information.php :: editInformation() : Autoredirect was created on backend');

					// setRedirect() кроме того, что просто записывет текущий редирект, также обновляет новый ЧПУ для всех старый редиректов
					$this->model_extension_module_seo_url_generator->setRedirect($keyword_new, $keyword_old, $sug_data['primary_key'], $sug_data['essence_id'], $store_id, $language_id);
				}
			}
		}
		/* SEO URL Generator . end
		------------------------------------------------------------------------- */

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'information_id=" . (int)$information_id . "'");

		if (isset($data['information_seo_url'])) {
			foreach ($data['information_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'information_id=" . (int)$information_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}

			else {
				if ($this->config->get('theme_oct_ultrastore_seo_url_status')) {
					$this->load->model('octemplates/widgets/oct_seogeneration');
					
					$this->model_octemplates_widgets_oct_seogeneration->seoUrlGenerator('information', (int)$language_id, (int)$store_id, $data, (int)$information_id);
				}
			}
			
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE information_id = '" . (int)$information_id . "'");

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "information_to_layout` SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('information');
	}

	public function deleteInformation($information_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_description` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_store` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'information_id=" . (int)$information_id . "'");

		$this->cache->delete('information');
	}

	public function getInformation($information_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "information WHERE information_id = '" . (int)$information_id . "'");

		return $query->row;
	}

	public function getInformations($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";


			if (!empty($data['filter_name'])) {
				$sql .= " AND id.title LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
			}

			$sql .= " GROUP BY i.information_id";
			
			$sort_data = array(
				'id.title',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.title";
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
		} else {
			$information_data = $this->cache->get('information.' . (int)$this->config->get('config_language_id'));

			if (!$information_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.title");

				$information_data = $query->rows;

				$this->cache->set('information.' . (int)$this->config->get('config_language_id'), $information_data);
			}

			return $information_data;
		}
	}

	public function getInformationDescriptions($information_id) {
		$information_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $information_description_data;
	}


		public function getInformationFaq($information_id) {
			$faq = [];
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_faq WHERE information_id = '" . (int)$information_id . "' AND type = 'information' ORDER BY sort_order ASC");
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
		
	public function getInformationStores($information_id) {
		$information_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_store WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_store_data[] = $result['store_id'];
		}

		return $information_store_data;
	}

	public function getInformationSeoUrls($information_id) {
		$information_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'information_id=" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $information_seo_url_data;
	}

	public function getInformationLayouts($information_id) {
		$information_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $information_layout_data;
	}

	public function getTotalInformations() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information");

		return $query->row['total'];
	}

	public function getTotalInformationsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
}