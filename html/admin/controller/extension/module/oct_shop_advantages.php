<?php
/**************************************************************/
/*	@copyright	OCTemplates 2015-2019						  */
/*	@support	https://octemplates.net/					  */
/*	@license	LICENSE.txt									  */
/**************************************************************/

class ControllerExtensionModuleOctShopAdvantages extends Controller {
    private $error = [];

    public function index() {
        $this->load->language('octemplates/module/oct_shop_advantages');

        $this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('view/javascript/octemplates/bootstrap-notify/bootstrap-notify.min.js');
		$this->document->addScript('view/javascript/octemplates/oct_main.js');
		$this->document->addStyle('view/stylesheet/oct_ultrastore.css');
		
		//Add Spectrum
		$this->document->addStyle('view/javascript/octemplates/spectrum/spectrum.css');
		$this->document->addScript('view/javascript/octemplates/spectrum/spectrum.js');
		
        $this->load->model('setting/module');
        $this->load->model('localisation/language');
		$this->load->model('catalog/information');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('oct_shop_advantages', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }
		
		$errors = [
			'warning',
			'name',
			'width',
			'height',
			'limit',
			'icon_block1',
			'heading_block1',
			'text_block1',
			'color_icon_block1',
			'color_heading_block1',
			'color_text_block1',
			'background_block_hover_block1',
			'icon_block2',
			'heading_block2',
			'text_block2',
			'color_icon_block2',
			'color_heading_block2',
			'color_text_block2',
			'background_block_hover_block2',
			'icon_block3',
			'heading_block3',
			'text_block3',
			'color_icon_block3',
			'color_heading_block3',
			'color_text_block3',
			'background_block_hover_block3',
			'icon_block4',
			'heading_block4',
			'text_block4',
			'color_icon_block4',
			'color_heading_block4',
			'color_text_block4',
			'background_block_hover_block4',
		];
		
		foreach ($errors as $error) {
			if (isset($this->error[$error])) {
	            $data['error_'.$error] = $this->error[$error];
	        } else {
	            $data['error_'.$error] = '';
	        }
		}
		
		$data['informations'] = [];

		$filter_data = [
			'sort'  => 'id.title',
			'order' => 'ASC',
			'start' => 0,
			'limit' => 10000
		];

		$informations_info = $this->model_catalog_information->getInformations($filter_data);

		foreach ($informations_info as $result) {
			$data['informations'][] = [
				'information_id' => $result['information_id'],
				'title'          => $result['title']
			];
		}

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/oct_shop_advantages', 'user_token=' . $this->session->data['user_token'], true)
            ];
        } else {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/oct_shop_advantages', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
            ];
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/oct_shop_advantages', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/oct_shop_advantages', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
        }

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }
		
        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }
		
        if (isset($this->request->post['select-heading_indormation_id_block1'])) {
            $data['indormation_id_block1'] = $this->request->post['select-heading_indormation_id_block1'];
        } elseif (!empty($module_info)) {
            $data['indormation_id_block1'] = $module_info['select-heading_indormation_id_block1'];
        } else {
            $data['indormation_id_block1'] = 0;
        }
		
        if (isset($this->request->post['heading_block1'])) {
            $data['heading_block1'] = $this->request->post['heading_block1'];
        } elseif (!empty($module_info)) {
            $data['heading_block1'] = $module_info['heading_block1'];
        } else {
            $data['heading_block1'] = [];
        }
		
        if (isset($this->request->post['text_block1'])) {
            $data['text_block1'] = $this->request->post['text_block1'];
        } elseif (!empty($module_info)) {
            $data['text_block1'] = $module_info['text_block1'];
        } else {
            $data['text_block1'] = [];
        }

        if (isset($this->request->post['color_icon_block1'])) {
            $data['color_icon_block1'] = $this->request->post['color_icon_block1'];
        } elseif (!empty($module_info)) {
            $data['color_icon_block1'] = $module_info['color_icon_block1'];
        } else {
            $data['color_icon_block1'] = 'rgb(113, 190, 0)';
        }
		
        if (isset($this->request->post['tab_icon_block1'])) {
            $data['tab_icon_block1'] = $this->request->post['tab_icon_block1'];
        } elseif (!empty($module_info)) {
            $data['tab_icon_block1'] = $module_info['tab_icon_block1'];
        } else {
            $data['tab_icon_block1'] = 'fa fa-thumbs-up';
        }
		
        if (isset($this->request->post['color_heading_block1'])) {
            $data['color_heading_block1'] = $this->request->post['color_heading_block1'];
        } elseif (!empty($module_info)) {
            $data['color_heading_block1'] = $module_info['color_heading_block1'];
        } else {
            $data['color_heading_block1'] = 'rgb(48, 54, 61)';
        }
		
        if (isset($this->request->post['color_text_block1'])) {
            $data['color_text_block1'] = $this->request->post['color_text_block1'];
        } elseif (!empty($module_info)) {
            $data['color_text_block1'] = $module_info['color_text_block1'];
        } else {
            $data['color_text_block1'] = 'rgb(175, 175, 175)';
        }
		
        if (isset($this->request->post['background_block_hover_block1'])) {
            $data['background_block_hover_block1'] = $this->request->post['background_block_hover_block1'];
        } elseif (!empty($module_info)) {
            $data['background_block_hover_block1'] = $module_info['background_block_hover_block1'];
        } else {
            $data['background_block_hover_block1'] = 'rgb(113, 190, 0)';
        }
		
        if (isset($this->request->post['select-heading_indormation_id_block2'])) {
            $data['indormation_id_block2'] = $this->request->post['select-heading_indormation_id_block2'];
        } elseif (!empty($module_info)) {
            $data['indormation_id_block2'] = $module_info['select-heading_indormation_id_block2'];
        } else {
            $data['indormation_id_block2'] = 0;
        }
		
        if (isset($this->request->post['heading_block2'])) {
            $data['heading_block2'] = $this->request->post['heading_block2'];
        } elseif (!empty($module_info)) {
            $data['heading_block2'] = $module_info['heading_block2'];
        } else {
            $data['heading_block2'] = [];
        }
		
        if (isset($this->request->post['text_block2'])) {
            $data['text_block2'] = $this->request->post['text_block2'];
        } elseif (!empty($module_info)) {
            $data['text_block2'] = $module_info['text_block2'];
        } else {
            $data['text_block2'] = [];
        }
		
        if (isset($this->request->post['color_icon_block2'])) {
            $data['color_icon_block2'] = $this->request->post['color_icon_block2'];
        } elseif (!empty($module_info)) {
            $data['color_icon_block2'] = $module_info['color_icon_block2'];
        } else {
            $data['color_icon_block2'] = 'rgb(113, 190, 0)';
        }
		
        if (isset($this->request->post['tab_icon_block2'])) {
            $data['tab_icon_block2'] = $this->request->post['tab_icon_block2'];
        } elseif (!empty($module_info)) {
            $data['tab_icon_block2'] = $module_info['tab_icon_block2'];
        } else {
            $data['tab_icon_block2'] = 'fa fa-headphones';
        }
		
        if (isset($this->request->post['color_heading_block2'])) {
            $data['color_heading_block2'] = $this->request->post['color_heading_block2'];
        } elseif (!empty($module_info)) {
            $data['color_heading_block2'] = $module_info['color_heading_block2'];
        } else {
            $data['color_heading_block2'] = 'rgb(48, 54, 61)';
        }
		
        if (isset($this->request->post['color_text_block2'])) {
            $data['color_text_block2'] = $this->request->post['color_text_block2'];
        } elseif (!empty($module_info)) {
            $data['color_text_block2'] = $module_info['color_text_block2'];
        } else {
            $data['color_text_block2'] = 'rgb(175, 175, 175)';
        }
		
        if (isset($this->request->post['background_block_hover_block2'])) {
            $data['background_block_hover_block2'] = $this->request->post['background_block_hover_block2'];
        } elseif (!empty($module_info)) {
            $data['background_block_hover_block2'] = $module_info['background_block_hover_block2'];
        } else {
            $data['background_block_hover_block2'] = 'rgb(113, 190, 0)';
        }
		
        if (isset($this->request->post['select-heading_indormation_id_block3'])) {
            $data['indormation_id_block3'] = $this->request->post['select-heading_indormation_id_block3'];
        } elseif (!empty($module_info)) {
            $data['indormation_id_block3'] = $module_info['select-heading_indormation_id_block3'];
        } else {
            $data['indormation_id_block3'] = 0;
        }
		
        if (isset($this->request->post['heading_block3'])) {
            $data['heading_block3'] = $this->request->post['heading_block3'];
        } elseif (!empty($module_info)) {
            $data['heading_block3'] = $module_info['heading_block3'];
        } else {
            $data['heading_block3'] = [];
        }
		
        if (isset($this->request->post['text_block3'])) {
            $data['text_block3'] = $this->request->post['text_block3'];
        } elseif (!empty($module_info)) {
            $data['text_block3'] = $module_info['text_block3'];
        } else {
            $data['text_block3'] = [];
        }
		
        if (isset($this->request->post['color_icon_block3'])) {
            $data['color_icon_block3'] = $this->request->post['color_icon_block3'];
        } elseif (!empty($module_info)) {
            $data['color_icon_block3'] = $module_info['color_icon_block3'];
        } else {
            $data['color_icon_block3'] = 'rgb(113, 190, 0)';
        }
		
        if (isset($this->request->post['tab_icon_block3'])) {
            $data['tab_icon_block3'] = $this->request->post['tab_icon_block3'];
        } elseif (!empty($module_info)) {
            $data['tab_icon_block3'] = $module_info['tab_icon_block3'];
        } else {
            $data['tab_icon_block3'] = 'fa fa-share';
        }
		
        if (isset($this->request->post['color_heading_block3'])) {
            $data['color_heading_block3'] = $this->request->post['color_heading_block3'];
        } elseif (!empty($module_info)) {
            $data['color_heading_block3'] = $module_info['color_heading_block3'];
        } else {
            $data['color_heading_block3'] = 'rgb(48, 54, 61)';
        }
		
        if (isset($this->request->post['color_text_block3'])) {
            $data['color_text_block3'] = $this->request->post['color_text_block3'];
        } elseif (!empty($module_info)) {
            $data['color_text_block3'] = $module_info['color_text_block3'];
        } else {
            $data['color_text_block3'] = 'rgb(175, 175, 175)';
        }
		
        if (isset($this->request->post['background_block_hover_block3'])) {
            $data['background_block_hover_block3'] = $this->request->post['background_block_hover_block3'];
        } elseif (!empty($module_info)) {
            $data['background_block_hover_block3'] = $module_info['background_block_hover_block3'];
        } else {
            $data['background_block_hover_block3'] = 'rgb(113, 190, 0)';
        }
		
        if (isset($this->request->post['select-heading_indormation_id_block4'])) {
            $data['indormation_id_block4'] = $this->request->post['select-heading_indormation_id_block4'];
        } elseif (!empty($module_info)) {
            $data['indormation_id_block4'] = $module_info['select-heading_indormation_id_block4'];
        } else {
            $data['indormation_id_block4'] = 0;
        }
		
        if (isset($this->request->post['heading_block4'])) {
            $data['heading_block4'] = $this->request->post['heading_block4'];
        } elseif (!empty($module_info)) {
            $data['heading_block4'] = $module_info['heading_block4'];
        } else {
            $data['heading_block4'] = [];
        }
		
        if (isset($this->request->post['text_block4'])) {
            $data['text_block4'] = $this->request->post['text_block4'];
        } elseif (!empty($module_info)) {
            $data['text_block4'] = $module_info['text_block4'];
        } else {
            $data['text_block4'] = [];
        }
		
        if (isset($this->request->post['color_icon_block4'])) {
            $data['color_icon_block4'] = $this->request->post['color_icon_block4'];
        } elseif (!empty($module_info)) {
            $data['color_icon_block4'] = $module_info['color_icon_block4'];
        } else {
            $data['color_icon_block4'] = 'rgb(113, 190, 0)';
        }
		
        if (isset($this->request->post['tab_icon_block4'])) {
            $data['tab_icon_block4'] = $this->request->post['tab_icon_block4'];
        } elseif (!empty($module_info)) {
            $data['tab_icon_block4'] = $module_info['tab_icon_block4'];
        } else {
            $data['tab_icon_block4'] = 'fa fa-truck';
        }
		
        if (isset($this->request->post['color_heading_block4'])) {
            $data['color_heading_block4'] = $this->request->post['color_heading_block4'];
        } elseif (!empty($module_info)) {
            $data['color_heading_block4'] = $module_info['color_heading_block4'];
        } else {
            $data['color_heading_block4'] = 'rgb(48, 54, 61)';
        }
		
        if (isset($this->request->post['color_text_block4'])) {
            $data['color_text_block4'] = $this->request->post['color_text_block4'];
        } elseif (!empty($module_info)) {
            $data['color_text_block4'] = $module_info['color_text_block4'];
        } else {
            $data['color_text_block4'] = 'rgb(175, 175, 175)';
        }
		
        if (isset($this->request->post['background_block_hover_block4'])) {
            $data['background_block_hover_block4'] = $this->request->post['background_block_hover_block4'];
        } elseif (!empty($module_info)) {
            $data['background_block_hover_block4'] = $module_info['background_block_hover_block4'];
        } else {
            $data['background_block_hover_block4'] = 'rgb(113, 190, 0)';
        }


        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info) && isset($module_info['status'])) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = 1;
        }

        $data['languages'] = $this->model_localisation_language->getLanguages();

		$data['user_token'] = $this->session->data['user_token'];
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');
		
        $this->response->setOutput($this->load->view('octemplates/module/oct_shop_advantages', $data));
    }
	
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/oct_shop_advantages')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (!$this->request->post['tab_icon_block1']) {
            $this->error['icon_block1'] = $this->language->get('error_icon_block1');
        }
		
        if (!$this->request->post['color_icon_block1']) {
            $this->error['color_icon_block1'] = $this->language->get('error_color_icon_block1');
        }
		
        if (!$this->request->post['color_icon_block1']) {
            $this->error['color_icon_block1'] = $this->language->get('error_color_icon_block1');
        }
		
        if (!$this->request->post['color_heading_block1']) {
            $this->error['color_heading_block1'] = $this->language->get('error_color_heading_block1');
        }
		
        if (!$this->request->post['color_text_block1']) {
            $this->error['color_text_block1'] = $this->language->get('error_color_text_block1');
        }
		
        if (!$this->request->post['background_block_hover_block1']) {
            $this->error['background_block_hover_block1'] = $this->language->get('error_background_block_hover_block1');
        }
		
        if (is_array($this->request->post['text_block1'])) {
            foreach ($this->request->post['text_block1'] as $language_code => $text_block1) {
                if ((utf8_strlen($text_block1) < 1) || (utf8_strlen($text_block1) > 255)) {
                    $this->error['text_block1'][$language_code] = $this->language->get('error_text_block1');
                }
            }
        }
		
        if (is_array($this->request->post['heading_block1'])) {
            foreach ($this->request->post['heading_block1'] as $language_code => $heading_block1) {
                if ((utf8_strlen($heading_block1) < 1) || (utf8_strlen($heading_block1) > 255)) {
                    $this->error['heading_block1'][$language_code] = $this->language->get('error_heading_block1');
                }
            }
        }
		
        if (!$this->request->post['tab_icon_block2']) {
            $this->error['icon_block2'] = $this->language->get('error_icon_block2');
        }
		
        if (!$this->request->post['color_icon_block2']) {
            $this->error['color_icon_block2'] = $this->language->get('error_color_icon_block2');
        }
		
        if (!$this->request->post['color_icon_block2']) {
            $this->error['color_icon_block2'] = $this->language->get('error_color_icon_block2');
        }
		
        if (!$this->request->post['color_heading_block2']) {
            $this->error['color_heading_block2'] = $this->language->get('error_color_heading_block2');
        }
		
        if (!$this->request->post['color_text_block2']) {
            $this->error['color_text_block2'] = $this->language->get('error_color_text_block2');
        }
		
        if (!$this->request->post['background_block_hover_block2']) {
            $this->error['background_block_hover_block2'] = $this->language->get('error_background_block_hover_block2');
        }
		
        if (is_array($this->request->post['text_block2'])) {
            foreach ($this->request->post['text_block2'] as $language_code => $text_block2) {
                if ((utf8_strlen($text_block2) < 1) || (utf8_strlen($text_block2) > 255)) {
                    $this->error['text_block2'][$language_code] = $this->language->get('error_text_block2');
                }
            }
        }
		
        if (is_array($this->request->post['heading_block2'])) {
            foreach ($this->request->post['heading_block2'] as $language_code => $heading_block2) {
                if ((utf8_strlen($heading_block2) < 1) || (utf8_strlen($heading_block2) > 255)) {
                    $this->error['heading_block2'][$language_code] = $this->language->get('error_heading_block2');
                }
            }
        }
		
        if (!$this->request->post['tab_icon_block3']) {
            $this->error['icon_block3'] = $this->language->get('error_icon_block3');
        }
		
        if (!$this->request->post['color_icon_block3']) {
            $this->error['color_icon_block3'] = $this->language->get('error_color_icon_block3');
        }
		
        if (!$this->request->post['color_icon_block3']) {
            $this->error['color_icon_block3'] = $this->language->get('error_color_icon_block3');
        }
		
        if (!$this->request->post['color_heading_block3']) {
            $this->error['color_heading_block3'] = $this->language->get('error_color_heading_block3');
        }
		
        if (!$this->request->post['color_text_block3']) {
            $this->error['color_text_block3'] = $this->language->get('error_color_text_block3');
        }
		
        if (!$this->request->post['background_block_hover_block3']) {
            $this->error['background_block_hover_block3'] = $this->language->get('error_background_block_hover_block3');
        }
		
        if (is_array($this->request->post['text_block3'])) {
            foreach ($this->request->post['text_block3'] as $language_code => $text_block3) {
                if ((utf8_strlen($text_block3) < 1) || (utf8_strlen($text_block3) > 255)) {
                    $this->error['text_block3'][$language_code] = $this->language->get('error_text_block3');
                }
            }
        }
		
        if (is_array($this->request->post['heading_block3'])) {
            foreach ($this->request->post['heading_block3'] as $language_code => $heading_block3) {
                if ((utf8_strlen($heading_block3) < 1) || (utf8_strlen($heading_block3) > 255)) {
                    $this->error['heading_block3'][$language_code] = $this->language->get('error_heading_block3');
                }
            }
        }
		
        if (!$this->request->post['tab_icon_block4']) {
            $this->error['icon_block4'] = $this->language->get('error_icon_block4');
        }
		
        if (!$this->request->post['color_icon_block4']) {
            $this->error['color_icon_block4'] = $this->language->get('error_color_icon_block4');
        }
		
        if (!$this->request->post['color_icon_block4']) {
            $this->error['color_icon_block4'] = $this->language->get('error_color_icon_block4');
        }
		
        if (!$this->request->post['color_heading_block4']) {
            $this->error['color_heading_block4'] = $this->language->get('error_color_heading_block4');
        }
		
        if (!$this->request->post['color_text_block4']) {
            $this->error['color_text_block4'] = $this->language->get('error_color_text_block4');
        }
		
        if (!$this->request->post['background_block_hover_block4']) {
            $this->error['background_block_hover_block4'] = $this->language->get('error_background_block_hover_block4');
        }
		
        if (is_array($this->request->post['text_block4'])) {
            foreach ($this->request->post['text_block4'] as $language_code => $text_block4) {
                if ((utf8_strlen($text_block4) < 1) || (utf8_strlen($text_block4) > 255)) {
                    $this->error['text_block4'][$language_code] = $this->language->get('error_text_block4');
                }
            }
        }
		
        if (is_array($this->request->post['heading_block4'])) {
            foreach ($this->request->post['heading_block4'] as $language_code => $heading_block4) {
                if ((utf8_strlen($heading_block4) < 1) || (utf8_strlen($heading_block4) > 255)) {
                    $this->error['heading_block4'][$language_code] = $this->language->get('error_heading_block4');
                }
            }
        }
        
        return !$this->error;
    }
    
    public function install() {
	    $this->load->model('user/user_group');
        
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/oct_shop_advantages');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/oct_shop_advantages');
    }
	
	public function uninstall() {
	    $this->load->model('setting/setting');
	    $this->load->model('user/user_group');
        
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/oct_shop_advantages');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/oct_shop_advantages');
        
	    $this->model_setting_setting->deleteSetting('oct_shop_advantages');
    }
}