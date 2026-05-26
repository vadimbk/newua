<?php
class ControllerExtensionModuleCustomPopup extends Controller {
	private $error = array();
    private $version = '3.0';

	public function index() {
		$this->load->language('extension/module/custom_popup');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('custom_popup', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->cache->delete('product');
						
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title') . ' v' . $this->version;

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
        
        if (isset($this->error['update'])) {
			$data['update'] = $this->error['update'];
		} else {
			$data['update'] = '';
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
				'href' => $this->url->link('extension/module/custom_popup', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/custom_popup', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/custom_popup', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/custom_popup', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}
		
		$data['user_token'] = $this->session->data['user_token'];
        
        // $this->update_check();

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}
        
        if (isset($this->request->post['module_description'])) {
			$data['module_description'] = $this->request->post['module_description'];
		} elseif (!empty($module_info)) {
			$data['module_description'] = $module_info['module_description'];
		} else {
			$data['module_description'] = '';
		}
        
        if (isset($this->request->post['css'])) {
			$data['css'] = $this->request->post['css'];
		} elseif (!empty($module_info)) {
			$data['css'] = $module_info['css'];
		} else {
			$data['css'] = '';
		}
        
        if (isset($this->request->post['display_times'])) {
			$data['display_times'] = $this->request->post['display_times'];
		} elseif (!empty($module_info)) {
			$data['display_times'] = $module_info['display_times'];
		} else {
			$data['display_times'] = '';
		}
        
        if (isset($this->request->post['seconds_to_close'])) {
			$data['seconds_to_close'] = $this->request->post['seconds_to_close'];
		} elseif (!empty($module_info)) {
			$data['seconds_to_close'] = $module_info['seconds_to_close'];
		} else {
			$data['seconds_to_close'] = '';
		}
        
        if (isset($this->request->post['uid'])) {
			$data['uid'] = $this->request->post['uid'];
		} elseif (!empty($module_info)) {
			$data['uid'] = $module_info['uid'];
		} else {
			$data['uid'] = date('YmdHis');
		}
        
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();		
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}	

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/custom_popup', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/custom_popup')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}
		
		return !$this->error;
	}
    
    private function update_check() {
		if (extension_loaded('curl')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_URL, 'https://www.oc-extensions.com/api/v1/update_check');
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'v='.$this->version.'&ex=9&e='.urlencode($this->config->get('config_email')));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'OCX-Adaptor: curl'));
			curl_setopt($ch, CURLOPT_REFERER, HTTP_CATALOG);
			if (function_exists('gzinflate')) {
				curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
			}	
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$result = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			if ($http_code == 200) {
				$result = json_decode($result);
				
				if ( isset($result->version) && ($result->version > $this->version) ) {
						$this->error['update'] = 'A new version of ' . $this->language->get('heading_title') . ' is available: v' . $result->version . '. You can go to <a target="_blank" href="' . $result->url . '">extension page</a> to see the Changelog.';
				}
			}
		} else {
			if (!$fp = @fsockopen('ssl://www.oc-extensions.com', 443, $errno, $errstr, 20)) {
				return false;
			}

			socket_set_timeout($fp, 20);
			
			$data = 'v='.$this->version.'&ex=9&e='.$this->config->get('config_email');
			
			$headers = array();
			$headers[] = "POST /api/v1/update_check HTTP/1.0";
			$headers[] = "Host: www.oc-extensions.com";
			$headers[] = "Referer: " . HTTP_CATALOG;
			$headers[] = "OCX-Adaptor: socket";
			if (function_exists('gzinflate')) {
				$headers[] = "Accept-encoding: gzip";
			}	
			$headers[] = "Content-Type: application/x-www-form-urlencoded";
			$headers[] = "Accept: application/json";
			$headers[] = 'Content-Length: '.strlen($data);
			$request = implode("\r\n", $headers)."\r\n\r\n".$data;
			fwrite($fp, $request);
			$response = $http_code = null;
			$in_headers = $at_start = true;
			$gzip = false;
			
			while (!feof($fp)) {
				$line = fgets($fp, 4096);
				
				if ($at_start) {
					$at_start = false;
					
					if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
						return false;
					}
					
					$http_code = $m[2];
					continue;
				}
				
				if ($in_headers) {

					if (trim($line) == '') {
						$in_headers = false;
						continue;
					}

					if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
						continue;
					}
					
					if ( strtolower(trim($m[1])) == 'content-encoding' && trim($m[2]) == 'gzip') {
						$gzip = true;
					}
					
					continue;
				}
				
                $response .= $line;
			}
					
			fclose($fp);
			
			if ($http_code == 200) {
				if ($gzip && function_exists('gzinflate')) {
					$response = substr($response, 10);
					$response = gzinflate($response);
				}
				
				$result = json_decode($response);
				
				if ( isset($result->version) && ($result->version > $this->version) ) {
						$this->error['update'] = 'A new version of ' . $this->language->get('heading_title') . ' is available: v' . $result->version . '. You can go to <a target="_blank" href="' . $result->url . '">extension page</a> to see the Changelog.';
				}
			}
		}
	}
}