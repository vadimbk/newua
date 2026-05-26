<?php
class ControllerInformationInformation extends Controller {
	public function index() {
		$this->load->language('information/information');

		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {

				$this->document->addOGMeta('property="og:url"', $this->url->link('information/information', 'information_id=' .  $information_id) );
                
			$this->document->setTitle($information_info['meta_title']);
			$this->document->setDescription($information_info['meta_description']);
			$this->document->setKeywords($information_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $information_info['title'],
				'href' => $this->url->link('information/information', 'information_id=' .  $information_id)
			);

			$data['heading_title'] = $information_info['title'];

			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

		if ($this->config->get('sp_auto_seo_faq_status')) {
			$this->load->model('extension/module/sp_auto_seo_faq');
			$data['faq_output'] = $this->model_extension_module_sp_auto_seo_faq->getInformationFaq($information_info, $data);
		}
		

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			

			$this->response->setOutput($this->load->view('information/information', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'information_id=' . $information_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');


	        $oct_404_page_status = $this->config->get('oct_404_page_status');
			
	        if ($oct_404_page_status) {
		        $oct_404_page_data = $this->config->get('oct_404_page_data');
		        
	            if (isset($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['title']) && !empty($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['title'])) {
	                $data['heading_title'] = $oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['title'];
	                $this->document->setTitle($data['heading_title']);
	            }
				
				$data['oct_404_image'] = '';
				
	            if (isset($oct_404_page_data['image']) && !empty($oct_404_page_data['image'])) {
	                if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
	        			$data['oct_404_image'] = $this->config->get('config_ssl') . 'image/' . $oct_404_page_data['image'];
	        		} else {
	        			$data['oct_404_image'] = $this->config->get('config_url') . 'image/' . $oct_404_page_data['image'];
	        		}
	            }
	            
	            if (isset($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['text']) && !empty($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['text'])) {
	            	$data['text_error'] = html_entity_decode($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['text'], ENT_QUOTES, 'UTF-8');
				}
	        }
			
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function agree() {
		$this->load->model('catalog/information');

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$output = '';

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->setOutput($output);
	}
}