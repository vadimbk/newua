<?php
class ControllerExtensionModuleimagemanager extends Controller {
	private $error = array();

	public function index() {
		
		$this->load->language('extension/module/imagemanager');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('imagemanager', $this->request->post);
			

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_margin_right'] = $this->language->get('entry_margin_right');
		$data['entry_margin_bottom'] = $this->language->get('entry_margin_bottom');
		$data['entry_transparency'] = $this->language->get('entry_transparency');
		$data['entry_allow_mime'] = $this->language->get('entry_allow_mime');
		
		$data['help_transparency'] = $this->language->get('help_transparency');
		$data['help_allow_mime'] = $this->language->get('help_allow_mime');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_watermark'] = $this->language->get('tab_watermark');

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
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/imagemanager', 'user_token=' . $this->session->data['user_token'], true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/imagemanager', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/imagemanager', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		if (isset($this->request->post['imagemanager_status'])) {
			$data['imagemanager_status'] = $this->request->post['imagemanager_status'];
		} else {
			$data['imagemanager_status'] = $this->config->get('imagemanager_status');
		}
		
		if (isset($this->request->post['imagemanager_allow_mime'])) {
			$data['imagemanager_allow_mime'] = $this->request->post['imagemanager_allow_mime'];
		} elseif ($this->config->get('imagemanager_allow_mime')) {
			$data['imagemanager_allow_mime'] = $this->config->get('imagemanager_allow_mime');
		}
		else {
			$data['imagemanager_allow_mime'] = 'image,text/plain';
		}
		
		if (isset($this->request->post['imagemanager_wm_status'])) {
			$data['imagemanager_wm_status'] = $this->request->post['imagemanager_wm_status'];
		} else {
			$data['imagemanager_wm_status'] = $this->config->get('imagemanager_wm_status');
		}
		
		if (isset($this->request->post['imagemanager_wm_image'])) {
			$data['imagemanager_wm_image'] = $this->request->post['imagemanager_wm_image'];
		} else {
			$data['imagemanager_wm_image'] = $this->config->get('imagemanager_wm_image');
		}
		
		if (isset($this->request->post['imagemanager_wm_mr'])) {
			$data['imagemanager_wm_mr'] = $this->request->post['imagemanager_wm_mr'];
		} elseif ($this->config->get('imagemanager_wm_mr')) {
			$data['imagemanager_wm_mr'] = $this->config->get('imagemanager_wm_mr');
		}
		else {
			$data['imagemanager_wm_mr'] = 5;
		}
		
		if (isset($this->request->post['imagemanager_wm_mb'])) {
			$data['imagemanager_wm_mb'] = $this->request->post['imagemanager_wm_mb'];
		} elseif ($this->config->get('imagemanager_wm_mb')) {
			$data['imagemanager_wm_mb'] = $this->config->get('imagemanager_wm_mb');
		}
		else {
			$data['imagemanager_wm_mb'] = 5;
		}
		
		if (isset($this->request->post['imagemanager_wm_trans'])) {
			$data['imagemanager_wm_trans'] = $this->request->post['imagemanager_wm_trans'];
		} elseif ($this->config->get('imagemanager_wm_trans')) {
			$data['imagemanager_wm_trans'] = $this->config->get('imagemanager_wm_trans');
		}
		else {
			$data['imagemanager_wm_trans'] = 70;
		}
		
		$this->load->model('tool/image');

		if (isset($this->request->post['imagemanager_wm_image']) && is_file(DIR_IMAGE . $this->request->post['imagemanager_wm_image'])) {
			$data['wm_thumb'] = $this->model_tool_image->resize($this->request->post['imagemanager_wm_image'], 100, 100);
		} elseif ($this->config->get('imagemanager_wm_image') && is_file(DIR_IMAGE . $this->config->get('imagemanager_wm_image'))) {
			$data['wm_thumb'] = $this->model_tool_image->resize($this->config->get('imagemanager_wm_image'), 100, 100);
		} else {
			$data['wm_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		$data['only_manager'] = false;
		//$data['fm'] = $this->load->controller('extension/module/imagemanager/returnfm');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/imagemanager', $data));
	}
	
	public function getthumb() {
		$this->load->model('tool/image');
		if (isset($this->request->get['thumb'])) {
			$image = urldecode($this->request->get['thumb']);
		}
		$thumb = $this->model_tool_image->resize($image, 100, 100);
		$this->response->addHeader('Content-Type: application/text');
		$this->response->setOutput($thumb);
	}
	
	public function connector() {
		require 'view/javascript/elfinder/php/autoload.php';
		elFinder::$netDrivers['ftp'] = 'FTP';
		$opts = array(
			'roots' => array(
				array(
					'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
					'path'          => '../image/catalog/',                 // path to files (REQUIRED)
					'URL'           => dirname($_SERVER['PHP_SELF']) . '/../image/catalog/', // URL to files (REQUIRED)
					'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
					'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
					'uploadAllow'   => explode(',', $this->config->get('imagemanager_allow_mime')),// Mimetype `image` and `text/plain` allowed to upload
					'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
					'accessControl' => 'access',                     // disable and hide dot starting files (OPTIONAL)
					'tmpPath'		=> DIR_APPLICATION . 'view/javascript/elfinder/php/.tmp'
				),
				
			)
		);
		if ($this->config->get('imagemanager_wm_status')) {
			$opts['bind'] = array(
				'upload.presave' => 'Plugin.Watermark.onUpLoadPreSave',
			);
			$opts['plugin'] = array(
				'Watermark' => array(
					'source' => '../image/' . $this->config->get('imagemanager_wm_image'),
					'marginRight' => $this->config->get('imagemanager_wm_mr'),
					'marginBottom' => $this->config->get('imagemanager_wm_mb'),
					'transparency' => $this->config->get('imagemanager_wm_trans')
				)
			);
		}

		// run elFinder
		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();
	}
	
	private function getfmdata() {
		$this->document->addStyle('view/javascript/elfinder/css/jquery-ui.css');
		$this->document->addStyle('view/javascript/elfinder/css/elfinder.min.css');
		$this->document->addStyle('view/javascript/elfinder/css/theme.css');
		$this->document->addStyle('view/javascript/elfinder/css/fixopencart.css');
		$this->document->addScript('view/javascript/elfinder/js/jquery-ui.min.js');
		$this->document->addScript('view/javascript/elfinder/js/elfinder.full.js');
		$data['connector'] = 'index.php?route=extension/module/imagemanager/connector&user_token=' . $this->session->data['user_token'];
		$data['only_manager'] = true;
		$data['multiple'] = 'false';
		
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		$this->load->language('extension/module/imagemanager');
		$data['heading_title'] = $this->language->get('heading_title');
		$data['button_remove'] = $this->language->get('button_remove');
		
		if (isset($this->request->get['target'])) {
			$data['target'] = $this->request->get['target'];
		} else {
			$data['target'] = '';
		}
		// CKEditor
		if (isset($this->request->get['cke'])) {
			$data['cke'] = $this->request->get['cke'];
		} else {
			$data['cke'] = '';
		}
		// Return the thumbnail for the file manager to show a thumbnail
		if (isset($this->request->get['thumb'])) {
			$data['thumb'] = $this->request->get['thumb'];
		} else {
			$data['thumb'] = '';
		}
		
		if (isset($this->request->get['editor'])) {
			$data['editor'] = $this->request->get['editor'];
		} else {
			$data['editor'] = '';
		}
		
		return $data;
	}
	
	public function showfm() {
		$data = $this->getfmdata();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$this->response->setOutput($this->load->view('extension/module/imagemanager', $data));
	}
	
	public function showfmmulti() {
		$data = $this->getfmdata();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['multiple'] = 'true';
		$this->response->setOutput($this->load->view('extension/module/imagemanager', $data));
	}
	
	public function returnfm() {
		$data = $this->getfmdata();
		return($this->load->view('extension/module/imagemanager', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/imagemanager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		return !$this->error;
	}
}