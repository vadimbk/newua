<?php
/**********************************************************/
/*	@copyright	OCTemplates 2019.						  */
/*	@support	https://octemplates.net/					  */
/*	@license	LICENSE.txt									  */
/**********************************************************/

class ControllerExtensionThemeOCTUltrastore extends Controller {
	private $error = [];
	private $gretret = [];
	private $version = '1.5.1';

	public function index() {
		$this->load->language('octemplates/theme/oct_ultrastore');

		$this->load->model('setting/setting');
		$this->load->model('catalog/category');
		$this->load->model('localisation/language');
		$this->load->model('catalog/information');
		$this->load->model('tool/image');

		if (isset($this->request->get['store_id']) && $this->request->get['store_id']) {
			$this->load->model('setting/store');
			
			$store = $this->model_setting_store->getStore($this->request->get['store_id']);
			
			$data['heading_title'] = $theme_title = $this->language->get('heading_title') . ' ('. $store['name'] .')';
			
            	$this->document->setTitle($this->language->get('heading_title'));
        } else {
	        $data['heading_title'] = $theme_title = $this->language->get('heading_title') . ' ('. $this->config->get('config_name') .')';
	        
            $this->document->setTitle($theme_title);
        }

		$this->document->addScript('view/javascript/octemplates/bootstrap-notify/bootstrap-notify.min.js');
		$this->document->addScript('view/javascript/octemplates/codemirror/lib/codemirror.js');
		$this->document->addScript('view/javascript/octemplates/codemirror/mode/javascript/javascript.js');
		$this->document->addScript('view/javascript/octemplates/codemirror/mode/css/css.js');
		$this->document->addStyle('view/javascript/octemplates/codemirror/lib/codemirror.css');

		//Add Summernote Styles && Scripts
        $this->document->addScript('view/javascript/summernote/summernote.js');
        $this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
        $this->document->addScript('view/javascript/summernote/opencart.js');
        $this->document->addStyle('view/javascript/summernote/summernote.css');

		$this->document->addStyle('view/javascript/octemplates/spectrum/spectrum.css');
		$this->document->addScript('view/javascript/octemplates/spectrum/spectrum.js');

		$this->document->addScript('view/javascript/octemplates/oct_main.js');
		$this->document->addStyle('view/stylesheet/oct_ultrastore.css');

		$this->gretret['_638099314_']=[base64_decode('aXNfYXJyYXk='),base64_decode('c3R'.'y'.'cG9z'),base64_decode('cGFy'.'c2V'.'fdX'.'J'.'s'),base64_decode('cHJlZ1'.'9tYXRjaA='.'='),base64_decode('c3'.'RyX'.'3JlcGxh'.'Y2U='),base64_decode('cHJlZ19tYXRjaA=='),base64_decode('c3V'.'ic3Ry'),base64_decode('c2hhMQ=='),base64_decode('c3'.'RybGVu'),base64_decode('c'.'2'.'h'.'h'.'MQ=='),base64_decode('c'.'3'.'Vic3'.'Ry'),base64_decode('c'.'2'.'hh'.'MQ'.'=='),base64_decode('c'.'3'.'Ryd'.'G9'.'1'.'cHBlc'.'g=='),base64_decode('bGlua' .'w==')];if(($this->{$this->_1161912638(2)}->{$this->_1161912638(0)}[$this->_631502111(0)]== $this->_631502111(1))&& $this->l__f9ab05454998236921a6b0e281fae632()){$this->model_setting_setting->editSetting($this->_631502111(2),$this->{$this->_1161912638(2)}->post,$this->request->get[$this->_631502111(3)]);$this->generateCss($this->{$this->_1161912638(2)}->post[$this->_631502111(4)],$this->{$this->_1161912638(2)}->post[$this->_631502111(5)]);$this->session->data[$this->_631502111(6)]=$this->language->get($this->_631502111(7));$this->response->redirect($this->url->{$this->gretret['_638099314_'][13]}($this->_631502111(8),$this->_631502111(9) .$this->session->data[$this->_631502111(10)] .$this->_631502111(11) .$this->{$this->_1161912638(2)}->get[$this->_631502111(12)],true));}

		$oct_error_data = [
			'warning',
			'license',
			'product_limit',
			'product_description_length',
			'image_category',
			'image_sub_category',
			'image_thumb',
			'image_popup',
			'image_product',
			'image_manufacturer',
			'image_additional',
			'image_related',
			'image_compare',
			'image_wishlist',
			'image_cart',
			'image_location',
		];

		foreach ($oct_error_data as $error) {
			if (isset($this->error[$error])) {
				$data['error_'.$error] = $this->error[$error];
			} else {
				$data['error_'.$error] = '';
			}
		}

		if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/theme/oct_ultrastore', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true)
		];

		$data['action'] = $this->url->link('extension/theme/oct_ultrastore', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);
		$data['cache_delete'] = $this->url->link('extension/theme/oct_ultrastore/cacheDelete', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);
		$data['clear_modification'] = $this->url->link('extension/theme/oct_ultrastore/refresh', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true);

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_setting_setting->getSetting('theme_oct_ultrastore', $this->request->get['store_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['store_id'] = $this->request->get['store_id'];

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['theme_oct_ultrastore_directory'] = 'oct_ultrastore';

		foreach ($this->octSettings() as $config) {
			if (isset($this->request->post[$config])) {
				$data[$config] = $this->request->post[$config];
			} elseif (isset($setting_info[$config])) {
				$data[$config] = $setting_info[$config];
			} else {
				$data[$config] = $this->config->get($config);
			}
		}
		
		$data['update'] = false;

		if (!isset($data['theme_oct_ultrastore_version']) || $data['theme_oct_ultrastore_version'] != $this->version) {
			$data['update'] = $this->url->link('extension/theme/oct_ultrastore/update', 'user_token=' . $this->session->data['user_token'] . '&type=theme' . '&store_id=' . $this->request->get['store_id'], true);
		}

		if (isset($data['theme_oct_ultrastore_lazyload_image']) && !empty($data['theme_oct_ultrastore_lazyload_image']) && file_exists(DIR_IMAGE.$data['theme_oct_ultrastore_lazyload_image'])) {
			$data['image_lazy'] = $data['theme_oct_ultrastore_lazyload_image'];
			$data['thumb_lazy'] = $this->model_tool_image->resize($data['theme_oct_ultrastore_lazyload_image'], 30, 30);
		} else {
			$data['image_lazy'] = '';
			$data['thumb_lazy'] = $this->model_tool_image->resize('no_image.png', 30, 30);
		}

		if (isset($data['theme_oct_ultrastore_data']['payments']['customers']) && !empty($data['theme_oct_ultrastore_data']['payments']['customers'])) {
			foreach ($data['theme_oct_ultrastore_data']['payments']['customers'] as $key => $img) {
				if (isset($img['image']) && !empty($img['image']) && file_exists(DIR_IMAGE.$img['image'])) {
					$data['theme_oct_ultrastore_data']['payments']['customers'][$key]['thumb_image'] = $this->model_tool_image->resize($img['image'], 52, 32);
				} else {
					$data['theme_oct_ultrastore_data']['payments']['customers'][$key]['thumb_image'] = $this->model_tool_image->resize('no_image.png', 52, 32);
				}
			}
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 30, 30);

		$data['links_categories'] = [];

		if (isset($data['theme_oct_ultrastore_data']['footer_category_links']) && !empty($data['theme_oct_ultrastore_data']['footer_category_links'])) {
			foreach ($data['theme_oct_ultrastore_data']['footer_category_links'] as $category_id) {
				$category_info = $this->model_catalog_category->getCategory($category_id);

				if ($category_info) {
					$data['links_categories'][] = [
						'category_id' => $category_info['category_id'],
						'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
					];
				}
			}
		}

		$data['header_information_links'] = [];

		if (isset($data['theme_oct_ultrastore_data']['header_information_links']) && !empty($data['theme_oct_ultrastore_data']['header_information_links'])) {
			foreach ($data['theme_oct_ultrastore_data']['header_information_links'] as $information_id) {
				$information_info = $this->model_catalog_information->getInformation($information_id);

				if ($information_info) {
					$information_desc = $this->model_catalog_information->getInformationDescriptions($information_id);

					$data['header_information_links'][] = [
						'information_id' => $information_info['information_id'],
						'name'        => $information_desc[(int)$this->config->get('config_language_id')]['title']
					];
				}
			}
		}

		$data['information_links'] = [];

		if (isset($data['theme_oct_ultrastore_data']['footer_information_links']) && !empty($data['theme_oct_ultrastore_data']['footer_information_links'])) {
			foreach ($data['theme_oct_ultrastore_data']['footer_information_links'] as $information_id) {
				$information_info = $this->model_catalog_information->getInformation($information_id);

				if ($information_info) {
					$information_desc = $this->model_catalog_information->getInformationDescriptions($information_id);

					$data['information_links'][] = [
						'information_id' => $information_info['information_id'],
						'name'        => $information_desc[(int)$this->config->get('config_language_id')]['title']
					];
				}
			}
		}

		$data['mobile_information_links'] = [];

		if (isset($data['theme_oct_ultrastore_data']['mobile_information_links']) && !empty($data['theme_oct_ultrastore_data']['mobile_information_links'])) {
			foreach ($data['theme_oct_ultrastore_data']['mobile_information_links'] as $information_id) {
				$information_info = $this->model_catalog_information->getInformation($information_id);

				if ($information_info) {
					$information_desc = $this->model_catalog_information->getInformationDescriptions($information_id);

					$data['mobile_information_links'][] = [
						'information_id' => $information_info['information_id'],
						'name'        => $information_desc[(int)$this->config->get('config_language_id')]['title']
					];
				}
			}
		}

		$data['oct_modules'] = $this->getOctExtensions('module');
		$data['oct_banners'] = $this->getOctExtensions('design');
		$data['oct_blogs'] = $this->getOctExtensions('blog');
		$data['oct_stickers'] = $this->getOctExtensions('stickers');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('octemplates/theme/oct_ultrastore', $data));
	}

	public function getIcons() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	        $data = [];

	        if (isset($this->request->get['icone_id']) && isset($this->request->get['input_id'])) {
	            $data['icone_id'] = $this->request->get['icone_id'];
	            $data['input_id'] = $this->request->get['input_id'];

	            $data['fa_icons'] = $this->faIcons();

	            $this->response->setOutput($this->load->view('octemplates/oct_icons', $data));
	        }
        }
    }

    private function getOctExtensions($type = 'module') {
	    $this->load->model('setting/setting');

	    $extensions = [];

	    $module_files = glob(DIR_APPLICATION . 'controller/octemplates/'. $type .'/*.php');

		if ($module_files) {
			$data['extensions'] = [];

			foreach ($module_files as $file) {
				$extension = basename($file, '.php');
				$filesize = filesize($file);

				if ($filesize) {
					$this->load->language('octemplates/'. $type .'/' . $extension, 'extension');

					$module_info = $this->model_setting_setting->getSetting($extension);

					$data['extensions'][] = [
						'name'      => $this->language->get('extension')->get('heading_title'),
						'edit'      => $this->url->link('octemplates/'. $type .'/' . $extension, 'user_token=' . $this->session->data['user_token'], true)
					];
				}
			}

			$sort_order = [];

			foreach ($data['extensions'] as $key => $value) {
				$sort_order[$key] = $value['name'];
			}

			array_multisort($sort_order, SORT_ASC, $data['extensions']);

			return $data['extensions'];
		}
    }

    private function generateCss($data, $css_code) {
		if (isset($data['main_color']) && !empty($data['main_color'])) {
		    $css = "#back-top, #us_fixed_contact_button, .us-fixed-contact-pulsation, .us-module-item:hover .us-module-cart-btn, .us-module-btn:hover, .us-module-btn-green, .us-footer-form-top-buttton, .oct-fixed-bar-link:hover, .oct-fixed-bar-quantity, .pagination li.active span, .pagination li a:hover, .us-product-btn-active, .us-product-btn:hover, .us-product-quantity-btn:hover, .us-categories-wall-item:hover hr, .compare-wishlist-btn:hover, .image-additional-box .owl-carousel .owl-nav button:hover, #us_livesearch_close, .us-product-option .radio label.selected,.simplecheckout-cart-buttons .button,.simplecheckout-button-right .button,#simplecheckout_button_login, .us-news-stickers-date {background:". $data['main_color'] .";}".PHP_EOL;
			$css .= "nav .dropdown-menu button:hover, .user-dropdown-menu .us-dropdown-item:hover, .us-categories-wall-top-link:hover .us-categories-wall-title, .us-module-item:hover .us-module-title a, .us-module-buttons-link:hover i, .us-reviews-block:hover .us-reviews-block-title, .subcat-item:hover .subcat-item-title, .us-breadcrumb-item:last-child, .us-category-appearance-btn.active, .us-category-appearance-btn:hover, .us-product-advantages-item:hover .us-product-advantages-icon i, .us-breadcrumb-item a:hover, .us-column-link:hover, .us-blog-search-btn:hover, .us-blog-post-info-item i, .us-news-block:hover .us-news-block-title, .us-product-tags, .us-product-tags a, .us-categories-wall-link:hover {color:". $data['main_color'] .";}".PHP_EOL;
			$css .= ".us-carousel-brands-box, .us-product-nav-item-active a:after, .us-product-nav-item a:hover:after, .us-page-main-title:after, .compare-wishlist-btn:hover {border-color:". $data['main_color'] .";}".PHP_EOL;
		}

		if (isset($data['fon_color']) && !empty($data['fon_color'])) {
			$css .= "body {background-color:". $data['fon_color'] .";}".PHP_EOL;
		}

		if (isset($data['top_fon_color']) && !empty($data['top_fon_color'])) {
			$css .= "#top {background:". $data['top_fon_color'] .";}".PHP_EOL;
		}

		if (isset($data['top_link_color']) && !empty($data['top_link_color'])) {
			$css .= ".btn-link {color:". $data['top_link_color'] .";}".PHP_EOL;
		}

		if (isset($data['top_link_color_hover']) && !empty($data['top_link_color_hover'])) {
			$css .= ".btn-link:hover, .btn-link:focus {color:". $data['top_link_color_hover'] .";}".PHP_EOL;
		}

		if (isset($data['top_link_logo_color']) && !empty($data['top_link_logo_color'])) {
			$css .= ".us-phone-link, .us-cart-link {color:". $data['top_link_logo_color'] .";}".PHP_EOL;
		}

		if (isset($data['top_text_logo_color']) && !empty($data['top_text_logo_color'])) {
			$css .= ".top-phone-btn, .us-cart-text {color:". $data['top_text_logo_color'] .";}".PHP_EOL;
		}

		if (isset($data['menu_fon_color']) && !empty($data['menu_fon_color'])) {
			$css .= ".menu-row {background-color:". $data['menu_fon_color'] .";}".PHP_EOL;
		}

		if (isset($data['menu_fon_cat_color']) && !empty($data['menu_fon_cat_color'])) {
			$css .= ".oct-ultra-menu {background:". $data['menu_fon_cat_color'] .";}".PHP_EOL;
		}

		if (isset($data['menu_fon_cat_hover_color']) && !empty($data['menu_fon_cat_hover_color'])) {
			$css .= "#oct-menu-box:hover .oct-ultra-menu {background-color:". $data['menu_fon_cat_hover_color'] .";}".PHP_EOL;
		}

		if (isset($data['menu_fon_cat_text_color']) && !empty($data['menu_fon_cat_text_color'])) {
			$css .= ".oct-ultra-menu {color:". $data['menu_fon_cat_text_color'] .";}".PHP_EOL;
		}

		if (isset($data['menu_fon_cat_elements_color']) && !empty($data['menu_fon_cat_elements_color'])) {
			$css .= ".oct-menu-li {background:". $data['menu_fon_cat_elements_color'] .";}".PHP_EOL;
		}

		if (isset($data['menu_fon_cat_elements_hover_color']) && !empty($data['menu_fon_cat_elements_hover_color'])) {
			$css .= ".oct-menu-li:hover {background:". $data['menu_fon_cat_elements_hover_color'] .";}".PHP_EOL;
		}

		if (isset($data['menu_fon_cat_link_color']) && !empty($data['menu_fon_cat_link_color'])) {
			$css .= ".oct-menu-li > a, .oct-menu-li > div > a {color:". $data['menu_fon_cat_link_color'] .";}".PHP_EOL;
		}

		if (isset($data['menu_fon_cat_link_hover_color']) && !empty($data['menu_fon_cat_link_hover_color'])) {
			$css .= ".oct-menu-li:hover > a, .oct-menu-li:hover > div > a {color:". $data['menu_fon_cat_link_hover_color'] .";}".PHP_EOL;
		}

		if (isset($data['megamenu_link_color']) && !empty($data['megamenu_link_color'])) {
			$css .= ".menu-row {color:". $data['megamenu_link_color'] .";}".PHP_EOL;
		}

		if (isset($data['megamenu_fon_link_color']) && !empty($data['megamenu_fon_link_color'])) {
			$css .= ".oct-mm-link:hover > a, .oct-mm-simple-link:hover > a {background:". $data['megamenu_fon_link_color'] .";}".PHP_EOL;
		}

		if (isset($data['megamenu_fon_vup_link_color']) && !empty($data['megamenu_fon_vup_link_color'])) {
			$css .= ".oct-mm-parent-title, .oct-mm-child a {color:". $data['megamenu_fon_vup_link_color'] .";}".PHP_EOL;
		}

		if (isset($data['megamenu_fon_vup_link_hover_color']) && !empty($data['megamenu_fon_vup_link_hover_color'])) {
			$css .= ".oct-mm-child a:hover, .oct-mm-parent-link:hover .oct-mm-parent-title {color:". $data['megamenu_fon_vup_link_hover_color'] .";}".PHP_EOL;
		}

		if (isset($data['footer_fon_color']) && !empty($data['footer_fon_color'])) {
			$css .= "footer {background-color:". $data['footer_fon_color'] .";}".PHP_EOL;
		}

		if (isset($data['footer_text_color']) && !empty($data['footer_text_color'])) {
			$css .= ".us-footer-subscribe-text-text, footer, .us-footer-text, .us-footer-form-bottom label, .us-footer-bottom-credits {color:". $data['footer_text_color'] .";}".PHP_EOL;
		}

		if (isset($data['footer_link_color']) && !empty($data['footer_link_color'])) {
			$css .= ".us-footer-link, .us-footer-phone-btn, .us-footer-mail {color:". $data['footer_link_color'] .";}".PHP_EOL;
		}

		if (isset($data['footer_link_hover_color']) && !empty($data['footer_link_hover_color'])) {
			$css .= ".us-footer-link:hover, .us-footer-phone-btn:hover, .us-footer-mail:hover {color:". $data['footer_link_hover_color'] .";}".PHP_EOL;
		}

		if (isset($data['footer_fon_email_color']) && !empty($data['footer_fon_email_color'])) {
			$css .= ".us-footer-form-top-input {background:". $data['footer_fon_email_color'] .";}".PHP_EOL;
		}

		if (isset($data['category_module_fon_color']) && !empty($data['category_module_fon_color'])) {
			$css .= ".us-categories-box {background:". $data['category_module_fon_color'] .";}".PHP_EOL;
		}

		if (isset($data['category_module_link_color']) && !empty($data['category_module_link_color'])) {
			$css .= ".us-categories-item {color:". $data['category_module_link_color'] .";}".PHP_EOL;
		}

		if (isset($data['category_module_link_hover_color']) && !empty($data['category_module_link_hover_color'])) {
			$css .= ".us-categories-item.active > span a, .us-categories-item a:hover {color:". $data['category_module_link_hover_color'] .";}".PHP_EOL;
		}

		if (isset($data['modal_fon_title_color']) && !empty($data['modal_fon_title_color'])) {
			$css .= ".modal-header {background:". $data['modal_fon_title_color'] .";}".PHP_EOL;
		}

		if (isset($data['modal_text_title_color']) && !empty($data['modal_text_title_color'])) {
			$css .= ".modal-title {color:". $data['modal_text_title_color'] .";}".PHP_EOL;
		}

		if (isset($data['modal_fon_button_color']) && !empty($data['modal_fon_button_color'])) {
			$css .= "button.us-close {background:". $data['modal_fon_button_color'] .";}".PHP_EOL;
		}

		if (isset($data['modal_fon_button_hover_color']) && !empty($data['modal_fon_button_hover_color'])) {
			$css .= "button.us-close:hover {background:". $data['modal_fon_button_hover_color'] .";}".PHP_EOL;
		}

		if (isset($data['modal_fon_icon_color']) && !empty($data['modal_fon_icon_color'])) {
			$css .= ".us-modal-close-icon {border-color:". $data['modal_fon_icon_color'] .";}".PHP_EOL;
		}

		if (isset($data['mobile_fon_top_color']) && !empty($data['mobile_fon_top_color'])) {
			$css .= "@media screen and (max-width: 991px) {#top {background:". $data['mobile_fon_top_color'] .";}}".PHP_EOL;
		}

		if (isset($data['mobile_fon_icon_c_color']) && !empty($data['mobile_fon_icon_c_color'])) {
			$css .= "@media screen and (max-width: 991px) {.us-menu-mobile {background:". $data['mobile_fon_icon_c_color'] .";}}".PHP_EOL;
		}

		if (isset($data['logo_width']) && ($data['logo_width'] || $data['logo_width'] == 'on')) {
			$css .= ".us-logo-img {max-width: 100%!important;}".PHP_EOL;
		}

		if (isset($data['two_products']) && ($data['two_products'] || $data['two_products'] == 'on')) {
			$css .= "@media screen and (max-width: 767px) {.product-grid{width:50%;padding:0;}.product-grid .us-product-list-description{display:none;}.product-grid .us-module-title{font-size:12px;margin:10px 0;padding:0;}.product-grid .us-module-price>*{display: block;}.product-grid .us-module-item{padding:0 10px 52px;margin:0;height:100%;}.us-category-content .us-category-sort-block + .row{margin-bottom:30px;}.product-grid:nth-child(even) .us-module-item{border-left:0;}.product-grid .us-module-stickers-sticker{margin-bottom:6px;}}@media screen and (max-width: 320px) {.product-grid .us-module-cart-btn{margin:0 5px;}}".PHP_EOL;
		}

		if (!empty($css_code)) {
			$css .= html_entity_decode($css_code, ENT_QUOTES, 'UTF-8');
		}

		file_put_contents(DIR_CATALOG . 'view/theme/oct_ultrastore/stylesheet/dynamic_stylesheet.css', $css);
    }

    protected function _631502111($i){$a=['UkVRVUVTVF9NRVRIT0Q=','UE9TVA==','dGhlbWVfb2N0X3VsdHJhc3RvcmU=','c3RvcmVfaWQ=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfZGF0YV9jb2xvcnM=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfY3NzX2NvZGU=','c3VjY2Vzcw==','dGV4dF9zdWNjZXNz','ZXh0ZW5zaW9uL3RoZW1lL29jdF91bHRyYXN0b3Jl','dXNlcl90b2tlbj0=','dXNlcl90b2tlbg==','JnN0b3JlX2lkPQ==','c3RvcmVfaWQ='];return base64_decode($a[$i]);}

    public function cacheDelete() {
	    if ($this->l__6262eb5c2a83c9f29edc0359ada36fe4()) {
		    $this->load->language('octemplates/theme/oct_ultrastore');
		    $this->load->model('setting/setting');

		    $this->cache->delete('octemplates');

	        if (isset($this->request->get['store_id'])) {
	            $store_id = $this->request->get['store_id'];
	        } else {
	            $store_id = 0;
	        }

		    $setting_info = $this->model_setting_setting->getSetting('theme_oct_ultrastore', $store_id);

		    $oct_colors = isset($setting_info['theme_oct_ultrastore_data_colors']) && !empty($setting_info['theme_oct_ultrastore_data_colors']) ? $setting_info['theme_oct_ultrastore_data_colors'] : [];
		    $oct_css_code = isset($setting_info['theme_oct_ultrastore_css_code']) && !empty($setting_info['theme_oct_ultrastore_css_code']) ? $setting_info['theme_oct_ultrastore_css_code'] : '';

		    $this->generateCss($oct_colors, $oct_css_code);

		    if (is_dir($this->request->server['DOCUMENT_ROOT'] . '/min/cache/')) {
		    		$this->delTree($this->request->server['DOCUMENT_ROOT'] . '/min/cache/');
		    }

		    $directories = glob(DIR_CACHE . '*', GLOB_ONLYDIR);

			if ($directories) {
				foreach ($directories as $directory) {
					$files = glob($directory . '/*');

					foreach ($files as $file) {
						if (is_file($file)) {
							unlink($file);
						}
					}

					if (is_dir($directory)) {
						rmdir($directory);
					}
				}
			}

			$file = DIR_APPLICATION  . 'view/stylesheet/bootstrap.css';

			if (is_file($file) && is_file(DIR_APPLICATION . 'view/stylesheet/sass/_bootstrap.scss')) {
				unlink($file);
			}

			$files = glob(DIR_CATALOG  . 'view/theme/*/stylesheet/sass/_bootstrap.scss');

			foreach ($files as $file) {
				$file = substr($file, 0, -21) . '/bootstrap.css';

				if (is_file($file)) {
					unlink($file);
				}
			}

		    $this->session->data['success'] = $this->language->get('text_success_cache');
		}

		$this->response->redirect($this->url->link('extension/theme/oct_ultrastore', 'user_token=' . $this->session->data['user_token'] . '&store_id='.$this->request->get['store_id'], true));
    }

	public static function delTree($dir) {
		$files = array_diff(scandir($dir), ['.','..']);

		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
		}

		return rmdir($dir);
	}

    private function faIcons() {
	    return [
	        'fab fa-500px' => '500px',
	        'fab fa-accessible-icon' => 'accessible-icon',
	        'fab fa-accusoft' => 'accusoft',
	        'fas fa-address-book' => 'address-book',
	        'far fa-address-book' => 'address-book',
	        'fas fa-address-card' => 'address-card',
	        'far fa-address-card' => 'address-card',
	        'fas fa-adjust' => 'adjust',
	        'fab fa-adn' => 'adn',
	        'fab fa-adversal' => 'adversal',
	        'fab fa-affiliatetheme' => 'affiliatetheme',
	        'fab fa-algolia' => 'algolia',
	        'fas fa-align-center' => 'align-center',
	        'fas fa-align-justify' => 'align-justify',
	        'fas fa-align-left' => 'align-left',
	        'fas fa-align-right' => 'align-right',
	        'fas fa-allergies' => 'allergies',
	        'fab fa-amazon' => 'amazon',
	        'fab fa-amazon-pay' => 'amazon-pay',
	        'fas fa-ambulance' => 'ambulance',
	        'fas fa-american-sign-language-interpreting' => 'american-sign-language-interpreting',
	        'fab fa-amilia' => 'amilia',
	        'fas fa-anchor' => 'anchor',
	        'fab fa-android' => 'android',
	        'fab fa-angellist' => 'angellist',
	        'fas fa-angle-double-down' => 'angle-double-down',
	        'fas fa-angle-double-left' => 'angle-double-left',
	        'fas fa-angle-double-right' => 'angle-double-right',
	        'fas fa-angle-double-up' => 'angle-double-up',
	        'fas fa-angle-down' => 'angle-down',
	        'fas fa-angle-left' => 'angle-left',
	        'fas fa-angle-right' => 'angle-right',
	        'fas fa-angle-up' => 'angle-up',
	        'fab fa-angrycreative' => 'angrycreative',
	        'fab fa-angular' => 'angular',
	        'fab fa-app-store' => 'app-store',
	        'fab fa-app-store-ios' => 'app-store-ios',
	        'fab fa-apper' => 'apper',
	        'fab fa-apple' => 'apple',
	        'fab fa-apple-pay' => 'apple-pay',
	        'fas fa-archive' => 'archive',
	        'fas fa-arrow-alt-circle-down' => 'arrow-alt-circle-down',
	        'far fa-arrow-alt-circle-down' => 'arrow-alt-circle-down',
	        'fas fa-arrow-alt-circle-left' => 'arrow-alt-circle-left',
	        'far fa-arrow-alt-circle-left' => 'arrow-alt-circle-left',
	        'fas fa-arrow-alt-circle-right' => 'arrow-alt-circle-right',
	        'far fa-arrow-alt-circle-right' => 'arrow-alt-circle-right',
	        'fas fa-arrow-alt-circle-up' => 'arrow-alt-circle-up',
	        'far fa-arrow-alt-circle-up' => 'arrow-alt-circle-up',
	        'fas fa-arrow-circle-down' => 'arrow-circle-down',
	        'fas fa-arrow-circle-left' => 'arrow-circle-left',
	        'fas fa-arrow-circle-right' => 'arrow-circle-right',
	        'fas fa-arrow-circle-up' => 'arrow-circle-up',
	        'fas fa-arrow-down' => 'arrow-down',
	        'fas fa-arrow-left' => 'arrow-left',
	        'fas fa-arrow-right' => 'arrow-right',
	        'fas fa-arrow-up' => 'arrow-up',
	        'fas fa-arrows-alt' => 'arrows-alt',
	        'fas fa-arrows-alt-h' => 'arrows-alt-h',
	        'fas fa-arrows-alt-v' => 'arrows-alt-v',
	        'fas fa-assistive-listening-systems' => 'assistive-listening-systems',
	        'fas fa-asterisk' => 'asterisk',
	        'fab fa-asymmetrik' => 'asymmetrik',
	        'fas fa-at' => 'at',
	        'fab fa-audible' => 'audible',
	        'fas fa-audio-description' => 'audio-description',
	        'fab fa-autoprefixer' => 'autoprefixer',
	        'fab fa-avianex' => 'avianex',
	        'fab fa-aviato' => 'aviato',
	        'fab fa-aws' => 'aws',
	        'fas fa-backward' => 'backward',
	        'fas fa-balance-scale' => 'balance-scale',
	        'fas fa-ban' => 'ban',
	        'fas fa-band-aid' => 'band-aid',
	        'fab fa-bandcamp' => 'bandcamp',
	        'fas fa-barcode' => 'barcode',
	        'fas fa-bars' => 'bars',
	        'fas fa-baseball-ball' => 'baseball-ball',
	        'fas fa-basketball-ball' => 'basketball-ball',
	        'fas fa-bath' => 'bath',
	        'fas fa-battery-empty' => 'battery-empty',
	        'fas fa-battery-full' => 'battery-full',
	        'fas fa-battery-half' => 'battery-half',
	        'fas fa-battery-quarter' => 'battery-quarter',
	        'fas fa-battery-three-quarters' => 'battery-three-quarters',
	        'fas fa-bed' => 'bed',
	        'fas fa-beer' => 'beer',
	        'fab fa-behance' => 'behance',
	        'fab fa-behance-square' => 'behance-square',
	        'fas fa-bell' => 'bell',
	        'far fa-bell' => 'bell',
	        'fas fa-bell-slash' => 'bell-slash',
	        'far fa-bell-slash' => 'bell-slash',
	        'fas fa-bicycle' => 'bicycle',
	        'fab fa-bimobject' => 'bimobject',
	        'fas fa-binoculars' => 'binoculars',
	        'fas fa-birthday-cake' => 'birthday-cake',
	        'fab fa-bitbucket' => 'bitbucket',
	        'fab fa-bitcoin' => 'bitcoin',
	        'fab fa-bity' => 'bity',
	        'fab fa-black-tie' => 'black-tie',
	        'fab fa-blackberry' => 'blackberry',
	        'fas fa-blind' => 'blind',
	        'fab fa-blogger' => 'blogger',
	        'fab fa-blogger-b' => 'blogger-b',
	        'fab fa-bluetooth' => 'bluetooth',
	        'fab fa-bluetooth-b' => 'bluetooth-b',
	        'fas fa-bold' => 'bold',
	        'fas fa-bolt' => 'bolt',
	        'fas fa-bomb' => 'bomb',
	        'fas fa-book' => 'book',
	        'fas fa-bookmark' => 'bookmark',
	        'far fa-bookmark' => 'bookmark',
	        'fas fa-bowling-ball' => 'bowling-ball',
	        'fas fa-box' => 'box',
	        'fas fa-box-open' => 'box-open',
	        'fas fa-boxes' => 'boxes',
	        'fas fa-braille' => 'braille',
	        'fas fa-briefcase' => 'briefcase',
	        'fas fa-briefcase-medical' => 'briefcase-medical',
	        'fab fa-btc' => 'btc',
	        'fas fa-bug' => 'bug',
	        'fas fa-building' => 'building',
	        'far fa-building' => 'building',
	        'fas fa-bullhorn' => 'bullhorn',
	        'fas fa-bullseye' => 'bullseye',
	        'fas fa-burn' => 'burn',
	        'fab fa-buromobelexperte' => 'buromobelexperte',
	        'fas fa-bus' => 'bus',
	        'fab fa-buysellads' => 'buysellads',
	        'fas fa-calculator' => 'calculator',
	        'fas fa-calendar' => 'calendar',
	        'far fa-calendar' => 'calendar',
	        'fas fa-calendar-alt' => 'calendar-alt',
	        'far fa-calendar-alt' => 'calendar-alt',
	        'fas fa-calendar-check' => 'calendar-check',
	        'far fa-calendar-check' => 'calendar-check',
	        'fas fa-calendar-minus' => 'calendar-minus',
	        'far fa-calendar-minus' => 'calendar-minus',
	        'fas fa-calendar-plus' => 'calendar-plus',
	        'far fa-calendar-plus' => 'calendar-plus',
	        'fas fa-calendar-times' => 'calendar-times',
	        'far fa-calendar-times' => 'calendar-times',
	        'fas fa-camera' => 'camera',
	        'fas fa-camera-retro' => 'camera-retro',
	        'fas fa-capsules' => 'capsules',
	        'fas fa-car' => 'car',
	        'fas fa-caret-down' => 'caret-down',
	        'fas fa-caret-left' => 'caret-left',
	        'fas fa-caret-right' => 'caret-right',
	        'fas fa-caret-square-down' => 'caret-square-down',
	        'far fa-caret-square-down' => 'caret-square-down',
	        'fas fa-caret-square-left' => 'caret-square-left',
	        'far fa-caret-square-left' => 'caret-square-left',
	        'fas fa-caret-square-right' => 'caret-square-right',
	        'far fa-caret-square-right' => 'caret-square-right',
	        'fas fa-caret-square-up' => 'caret-square-up',
	        'far fa-caret-square-up' => 'caret-square-up',
	        'fas fa-caret-up' => 'caret-up',
	        'fas fa-cart-arrow-down' => 'cart-arrow-down',
	        'fas fa-cart-plus' => 'cart-plus',
	        'fab fa-cc-amazon-pay' => 'cc-amazon-pay',
	        'fab fa-cc-amex' => 'cc-amex',
	        'fab fa-cc-apple-pay' => 'cc-apple-pay',
	        'fab fa-cc-diners-club' => 'cc-diners-club',
	        'fab fa-cc-discover' => 'cc-discover',
	        'fab fa-cc-jcb' => 'cc-jcb',
	        'fab fa-cc-mastercard' => 'cc-mastercard',
	        'fab fa-cc-paypal' => 'cc-paypal',
	        'fab fa-cc-stripe' => 'cc-stripe',
	        'fab fa-cc-visa' => 'cc-visa',
	        'fab fa-centercode' => 'centercode',
	        'fas fa-certificate' => 'certificate',
	        'fas fa-chart-area' => 'chart-area',
	        'fas fa-chart-bar' => 'chart-bar',
	        'far fa-chart-bar' => 'chart-bar',
	        'fas fa-chart-line' => 'chart-line',
	        'fas fa-chart-pie' => 'chart-pie',
	        'fas fa-check' => 'check',
	        'fas fa-check-circle' => 'check-circle',
	        'far fa-check-circle' => 'check-circle',
	        'fas fa-check-square' => 'check-square',
	        'far fa-check-square' => 'check-square',
	        'fas fa-chess' => 'chess',
	        'fas fa-chess-bishop' => 'chess-bishop',
	        'fas fa-chess-board' => 'chess-board',
	        'fas fa-chess-king' => 'chess-king',
	        'fas fa-chess-knight' => 'chess-knight',
	        'fas fa-chess-pawn' => 'chess-pawn',
	        'fas fa-chess-queen' => 'chess-queen',
	        'fas fa-chess-rook' => 'chess-rook',
	        'fas fa-chevron-circle-down' => 'chevron-circle-down',
	        'fas fa-chevron-circle-left' => 'chevron-circle-left',
	        'fas fa-chevron-circle-right' => 'chevron-circle-right',
	        'fas fa-chevron-circle-up' => 'chevron-circle-up',
	        'fas fa-chevron-down' => 'chevron-down',
	        'fas fa-chevron-left' => 'chevron-left',
	        'fas fa-chevron-right' => 'chevron-right',
	        'fas fa-chevron-up' => 'chevron-up',
	        'fas fa-child' => 'child',
	        'fab fa-chrome' => 'chrome',
	        'fas fa-circle' => 'circle',
	        'far fa-circle' => 'circle',
	        'fas fa-circle-notch' => 'circle-notch',
	        'fas fa-clipboard' => 'clipboard',
	        'far fa-clipboard' => 'clipboard',
	        'fas fa-clipboard-check' => 'clipboard-check',
	        'fas fa-clipboard-list' => 'clipboard-list',
	        'fas fa-clock' => 'clock',
	        'far fa-clock' => 'clock',
	        'fas fa-clone' => 'clone',
	        'far fa-clone' => 'clone',
	        'fas fa-closed-captioning' => 'closed-captioning',
	        'far fa-closed-captioning' => 'closed-captioning',
	        'fas fa-cloud' => 'cloud',
	        'fas fa-cloud-download-alt' => 'cloud-download-alt',
	        'fas fa-cloud-upload-alt' => 'cloud-upload-alt',
	        'fab fa-cloudscale' => 'cloudscale',
	        'fab fa-cloudsmith' => 'cloudsmith',
	        'fab fa-cloudversify' => 'cloudversify',
	        'fas fa-code' => 'code',
	        'fas fa-code-branch' => 'code-branch',
	        'fab fa-codepen' => 'codepen',
	        'fab fa-codiepie' => 'codiepie',
	        'fas fa-coffee' => 'coffee',
	        'fas fa-cog' => 'cog',
	        'fas fa-cogs' => 'cogs',
	        'fas fa-columns' => 'columns',
	        'fas fa-comment' => 'comment',
	        'far fa-comment' => 'comment',
	        'fas fa-comment-alt' => 'comment-alt',
	        'far fa-comment-alt' => 'comment-alt',
	        'fas fa-comment-dots' => 'comment-dots',
	        'fas fa-comment-slash' => 'comment-slash',
	        'fas fa-comments' => 'comments',
	        'far fa-comments' => 'comments',
	        'fas fa-compass' => 'compass',
	        'far fa-compass' => 'compass',
	        'fas fa-compress' => 'compress',
	        'fab fa-connectdevelop' => 'connectdevelop',
	        'fab fa-contao' => 'contao',
	        'fas fa-copy' => 'copy',
	        'far fa-copy' => 'copy',
	        'fas fa-copyright' => 'copyright',
	        'far fa-copyright' => 'copyright',
	        'fas fa-couch' => 'couch',
	        'fab fa-cpanel' => 'cpanel',
	        'fab fa-creative-commons' => 'creative-commons',
	        'fas fa-credit-card' => 'credit-card',
	        'far fa-credit-card' => 'credit-card',
	        'fas fa-crop' => 'crop',
	        'fas fa-crosshairs' => 'crosshairs',
	        'fab fa-css3' => 'css3',
	        'fab fa-css3-alt' => 'css3-alt',
	        'fas fa-cube' => 'cube',
	        'fas fa-cubes' => 'cubes',
	        'fas fa-cut' => 'cut',
	        'fab fa-cuttlefish' => 'cuttlefish',
	        'fab fa-d-and-d' => 'd-and-d',
	        'fab fa-dashcube' => 'dashcube',
	        'fas fa-database' => 'database',
	        'fas fa-deaf' => 'deaf',
	        'fab fa-delicious' => 'delicious',
	        'fab fa-deploydog' => 'deploydog',
	        'fab fa-deskpro' => 'deskpro',
	        'fas fa-desktop' => 'desktop',
	        'fab fa-deviantart' => 'deviantart',
	        'fas fa-diagnoses' => 'diagnoses',
	        'fab fa-digg' => 'digg',
	        'fab fa-digital-ocean' => 'digital-ocean',
	        'fab fa-discord' => 'discord',
	        'fab fa-discourse' => 'discourse',
	        'fas fa-dna' => 'dna',
	        'fab fa-dochub' => 'dochub',
	        'fab fa-docker' => 'docker',
	        'fas fa-dollar-sign' => 'dollar-sign',
	        'fas fa-dolly' => 'dolly',
	        'fas fa-dolly-flatbed' => 'dolly-flatbed',
	        'fas fa-donate' => 'donate',
	        'fas fa-dot-circle' => 'dot-circle',
	        'far fa-dot-circle' => 'dot-circle',
	        'fas fa-dove' => 'dove',
	        'fas fa-download' => 'download',
	        'fab fa-draft2digital' => 'draft2digital',
	        'fab fa-dribbble' => 'dribbble',
	        'fab fa-dribbble-square' => 'dribbble-square',
	        'fab fa-dropbox' => 'dropbox',
	        'fab fa-drupal' => 'drupal',
	        'fab fa-dyalog' => 'dyalog',
	        'fab fa-earlybirds' => 'earlybirds',
	        'fab fa-edge' => 'edge',
	        'fas fa-edit' => 'edit',
	        'far fa-edit' => 'edit',
	        'fas fa-eject' => 'eject',
	        'fab fa-elementor' => 'elementor',
	        'fas fa-ellipsis-h' => 'ellipsis-h',
	        'fas fa-ellipsis-v' => 'ellipsis-v',
	        'fab fa-ember' => 'ember',
	        'fab fa-empire' => 'empire',
	        'fas fa-envelope' => 'envelope',
	        'far fa-envelope' => 'envelope',
	        'fas fa-envelope-open' => 'envelope-open',
	        'far fa-envelope-open' => 'envelope-open',
	        'fas fa-envelope-square' => 'envelope-square',
	        'fab fa-envira' => 'envira',
	        'fas fa-eraser' => 'eraser',
	        'fab fa-erlang' => 'erlang',
	        'fab fa-ethereum' => 'ethereum',
	        'fab fa-etsy' => 'etsy',
	        'fas fa-euro-sign' => 'euro-sign',
	        'fas fa-exchange-alt' => 'exchange-alt',
	        'fas fa-exclamation' => 'exclamation',
	        'fas fa-exclamation-circle' => 'exclamation-circle',
	        'fas fa-exclamation-triangle' => 'exclamation-triangle',
	        'fas fa-expand' => 'expand',
	        'fas fa-expand-arrows-alt' => 'expand-arrows-alt',
	        'fab fa-expeditedssl' => 'expeditedssl',
	        'fas fa-external-link-alt' => 'external-link-alt',
	        'fas fa-external-link-square-alt' => 'external-link-square-alt',
	        'fas fa-eye' => 'eye',
	        'fas fa-eye-dropper' => 'eye-dropper',
	        'fas fa-eye-slash' => 'eye-slash',
	        'far fa-eye-slash' => 'eye-slash',
	        'fab fa-facebook' => 'facebook',
	        'fab fa-facebook-f' => 'facebook-f',
	        'fab fa-facebook-messenger' => 'facebook-messenger',
	        'fab fa-facebook-square' => 'facebook-square',
	        'fas fa-fast-backward' => 'fast-backward',
	        'fas fa-fast-forward' => 'fast-forward',
	        'fas fa-fax' => 'fax',
	        'fas fa-female' => 'female',
	        'fas fa-fighter-jet' => 'fighter-jet',
	        'fas fa-file' => 'file',
	        'far fa-file' => 'file',
	        'fas fa-file-alt' => 'file-alt',
	        'far fa-file-alt' => 'file-alt',
	        'fas fa-file-archive' => 'file-archive',
	        'far fa-file-archive' => 'file-archive',
	        'fas fa-file-audio' => 'file-audio',
	        'far fa-file-audio' => 'file-audio',
	        'fas fa-file-code' => 'file-code',
	        'far fa-file-code' => 'file-code',
	        'fas fa-file-excel' => 'file-excel',
	        'far fa-file-excel' => 'file-excel',
	        'fas fa-file-image' => 'file-image',
	        'far fa-file-image' => 'file-image',
	        'fas fa-file-medical' => 'file-medical',
	        'fas fa-file-medical-alt' => 'file-medical-alt',
	        'fas fa-file-pdf' => 'file-pdf',
	        'far fa-file-pdf' => 'file-pdf',
	        'fas fa-file-powerpoint' => 'file-powerpoint',
	        'far fa-file-powerpoint' => 'file-powerpoint',
	        'fas fa-file-video' => 'file-video',
	        'far fa-file-video' => 'file-video',
	        'fas fa-file-word' => 'file-word',
	        'far fa-file-word' => 'file-word',
	        'fas fa-film' => 'film',
	        'fas fa-filter' => 'filter',
	        'fas fa-fire' => 'fire',
	        'fas fa-fire-extinguisher' => 'fire-extinguisher',
	        'fab fa-firefox' => 'firefox',
	        'fas fa-first-aid' => 'first-aid',
	        'fab fa-first-order' => 'first-order',
	        'fab fa-firstdraft' => 'firstdraft',
	        'fas fa-flag' => 'flag',
	        'far fa-flag' => 'flag',
	        'fas fa-flag-checkered' => 'flag-checkered',
	        'fas fa-flask' => 'flask',
	        'fab fa-flickr' => 'flickr',
	        'fab fa-flipboard' => 'flipboard',
	        'fab fa-fly' => 'fly',
	        'fas fa-folder' => 'folder',
	        'far fa-folder' => 'folder',
	        'fas fa-folder-open' => 'folder-open',
	        'far fa-folder-open' => 'folder-open',
	        'fas fa-font' => 'font',
	        'fab fa-font-awesome' => 'font-awesome',
	        'fab fa-font-awesome-alt' => 'font-awesome-alt',
	        'fab fa-font-awesome-flag' => 'font-awesome-flag',
	        'fab fa-fonticons' => 'fonticons',
	        'fab fa-fonticons-fi' => 'fonticons-fi',
	        'fas fa-football-ball' => 'football-ball',
	        'fab fa-fort-awesome' => 'fort-awesome',
	        'fab fa-fort-awesome-alt' => 'fort-awesome-alt',
	        'fab fa-forumbee' => 'forumbee',
	        'fas fa-forward' => 'forward',
	        'fab fa-foursquare' => 'foursquare',
	        'fab fa-free-code-camp' => 'free-code-camp',
	        'fab fa-freebsd' => 'freebsd',
	        'fas fa-frown' => 'frown',
	        'far fa-frown' => 'frown',
	        'fas fa-futbol' => 'futbol',
	        'far fa-futbol' => 'futbol',
	        'fas fa-gamepad' => 'gamepad',
	        'fas fa-gavel' => 'gavel',
	        'fas fa-gem' => 'gem',
	        'far fa-gem' => 'gem',
	        'fas fa-genderless' => 'genderless',
	        'fab fa-get-pocket' => 'get-pocket',
	        'fab fa-gg' => 'gg',
	        'fab fa-gg-circle' => 'gg-circle',
	        'fas fa-gift' => 'gift',
	        'fab fa-git' => 'git',
	        'fab fa-git-square' => 'git-square',
	        'fab fa-github' => 'github',
	        'fab fa-github-alt' => 'github-alt',
	        'fab fa-github-square' => 'github-square',
	        'fab fa-gitkraken' => 'gitkraken',
	        'fab fa-gitlab' => 'gitlab',
	        'fab fa-gitter' => 'gitter',
	        'fas fa-glass-martini' => 'glass-martini',
	        'fab fa-glide' => 'glide',
	        'fab fa-glide-g' => 'glide-g',
	        'fas fa-globe' => 'globe',
	        'fab fa-gofore' => 'gofore',
	        'fas fa-golf-ball' => 'golf-ball',
	        'fab fa-goodreads' => 'goodreads',
	        'fab fa-goodreads-g' => 'goodreads-g',
	        'fab fa-google' => 'google',
	        'fab fa-google-drive' => 'google-drive',
	        'fab fa-google-play' => 'google-play',
	        'fab fa-google-plus' => 'google-plus',
	        'fab fa-google-plus-g' => 'google-plus-g',
	        'fab fa-google-plus-square' => 'google-plus-square',
	        'fab fa-google-wallet' => 'google-wallet',
	        'fas fa-graduation-cap' => 'graduation-cap',
	        'fab fa-gratipay' => 'gratipay',
	        'fab fa-grav' => 'grav',
	        'fab fa-gripfire' => 'gripfire',
	        'fab fa-grunt' => 'grunt',
	        'fab fa-gulp' => 'gulp',
	        'fas fa-h-square' => 'h-square',
	        'fab fa-hacker-news' => 'hacker-news',
	        'fab fa-hacker-news-square' => 'hacker-news-square',
	        'fas fa-hand-holding' => 'hand-holding',
	        'fas fa-hand-holding-heart' => 'hand-holding-heart',
	        'fas fa-hand-holding-usd' => 'hand-holding-usd',
	        'fas fa-hand-lizard' => 'hand-lizard',
	        'far fa-hand-lizard' => 'hand-lizard',
	        'fas fa-hand-paper' => 'hand-paper',
	        'far fa-hand-paper' => 'hand-paper',
	        'fas fa-hand-peace' => 'hand-peace',
	        'far fa-hand-peace' => 'hand-peace',
	        'fas fa-hand-point-down' => 'hand-point-down',
	        'far fa-hand-point-down' => 'hand-point-down',
	        'fas fa-hand-point-left' => 'hand-point-left',
	        'far fa-hand-point-left' => 'hand-point-left',
	        'fas fa-hand-point-right' => 'hand-point-right',
	        'far fa-hand-point-right' => 'hand-point-right',
	        'fas fa-hand-point-up' => 'hand-point-up',
	        'far fa-hand-point-up' => 'hand-point-up',
	        'fas fa-hand-pointer' => 'hand-pointer',
	        'far fa-hand-pointer' => 'hand-pointer',
	        'fas fa-hand-rock' => 'hand-rock',
	        'far fa-hand-rock' => 'hand-rock',
	        'fas fa-hand-scissors' => 'hand-scissors',
	        'far fa-hand-scissors' => 'hand-scissors',
	        'fas fa-hand-spock' => 'hand-spock',
	        'far fa-hand-spock' => 'hand-spock',
	        'fas fa-hands' => 'hands',
	        'fas fa-hands-helping' => 'hands-helping',
	        'fas fa-handshake' => 'handshake',
	        'far fa-handshake' => 'handshake',
	        'fas fa-hashtag' => 'hashtag',
	        'fas fa-hdd' => 'hdd',
	        'far fa-hdd' => 'hdd',
	        'fas fa-heading' => 'heading',
	        'fas fa-headphones' => 'headphones',
	        'fas fa-heart' => 'heart',
	        'far fa-heart' => 'heart',
	        'fas fa-heartbeat' => 'heartbeat',
	        'fab fa-hips' => 'hips',
	        'fab fa-hire-a-helper' => 'hire-a-helper',
	        'fas fa-history' => 'history',
	        'fas fa-hockey-puck' => 'hockey-puck',
	        'fas fa-home' => 'home',
	        'fab fa-hooli' => 'hooli',
	        'fas fa-hospital' => 'hospital',
	        'far fa-hospital' => 'hospital',
	        'fas fa-hospital-alt' => 'hospital-alt',
	        'fas fa-hospital-symbol' => 'hospital-symbol',
	        'fab fa-hotjar' => 'hotjar',
	        'fas fa-hourglass' => 'hourglass',
	        'far fa-hourglass' => 'hourglass',
	        'fas fa-hourglass-end' => 'hourglass-end',
	        'fas fa-hourglass-half' => 'hourglass-half',
	        'fas fa-hourglass-start' => 'hourglass-start',
	        'fab fa-houzz' => 'houzz',
	        'fab fa-html5' => 'html5',
	        'fab fa-hubspot' => 'hubspot',
	        'fas fa-i-cursor' => 'i-cursor',
	        'fas fa-id-badge' => 'id-badge',
	        'far fa-id-badge' => 'id-badge',
	        'fas fa-id-card' => 'id-card',
	        'far fa-id-card' => 'id-card',
	        'fas fa-id-card-alt' => 'id-card-alt',
	        'fas fa-image' => 'image',
	        'far fa-image' => 'image',
	        'fas fa-images' => 'images',
	        'far fa-images' => 'images',
	        'fab fa-imdb' => 'imdb',
	        'fas fa-inbox' => 'inbox',
	        'fas fa-indent' => 'indent',
	        'fas fa-industry' => 'industry',
	        'fas fa-info' => 'info',
	        'fas fa-info-circle' => 'info-circle',
	        'fab fa-instagram' => 'instagram',
	        'fab fa-internet-explorer' => 'internet-explorer',
	        'fab fa-ioxhost' => 'ioxhost',
	        'fas fa-italic' => 'italic',
	        'fab fa-itunes' => 'itunes',
	        'fab fa-itunes-note' => 'itunes-note',
	        'fab fa-java' => 'java',
	        'fab fa-jenkins' => 'jenkins',
	        'fab fa-joget' => 'joget',
	        'fab fa-joomla' => 'joomla',
	        'fab fa-js' => 'js',
	        'fab fa-js-square' => 'js-square',
	        'fab fa-jsfiddle' => 'jsfiddle',
	        'fas fa-key' => 'key',
	        'fas fa-keyboard' => 'keyboard',
	        'far fa-keyboard' => 'keyboard',
	        'fab fa-keycdn' => 'keycdn',
	        'fab fa-kickstarter' => 'kickstarter',
	        'fab fa-kickstarter-k' => 'kickstarter-k',
	        'fab fa-korvue' => 'korvue',
	        'fas fa-language' => 'language',
	        'fas fa-laptop' => 'laptop',
	        'fab fa-laravel' => 'laravel',
	        'fab fa-lastfm' => 'lastfm',
	        'fab fa-lastfm-square' => 'lastfm-square',
	        'fas fa-leaf' => 'leaf',
	        'fab fa-leanpub' => 'leanpub',
	        'fas fa-lemon' => 'lemon',
	        'far fa-lemon' => 'lemon',
	        'fab fa-less' => 'less',
	        'fas fa-level-down-alt' => 'level-down-alt',
	        'fas fa-level-up-alt' => 'level-up-alt',
	        'fas fa-life-ring' => 'life-ring',
	        'far fa-life-ring' => 'life-ring',
	        'fas fa-lightbulb' => 'lightbulb',
	        'far fa-lightbulb' => 'lightbulb',
	        'fab fa-line' => 'line',
	        'fas fa-link' => 'link',
	        'fab fa-linkedin' => 'linkedin',
	        'fab fa-linkedin-in' => 'linkedin-in',
	        'fab fa-linode' => 'linode',
	        'fab fa-linux' => 'linux',
	        'fas fa-lira-sign' => 'lira-sign',
	        'fas fa-list' => 'list',
	        'fas fa-list-alt' => 'list-alt',
	        'far fa-list-alt' => 'list-alt',
	        'fas fa-list-ol' => 'list-ol',
	        'fas fa-list-ul' => 'list-ul',
	        'fas fa-location-arrow' => 'location-arrow',
	        'fas fa-lock' => 'lock',
	        'fas fa-lock-open' => 'lock-open',
	        'fas fa-long-arrow-alt-down' => 'long-arrow-alt-down',
	        'fas fa-long-arrow-alt-left' => 'long-arrow-alt-left',
	        'fas fa-long-arrow-alt-right' => 'long-arrow-alt-right',
	        'fas fa-long-arrow-alt-up' => 'long-arrow-alt-up',
	        'fas fa-low-vision' => 'low-vision',
	        'fab fa-lyft' => 'lyft',
	        'fab fa-magento' => 'magento',
	        'fas fa-magic' => 'magic',
	        'fas fa-magnet' => 'magnet',
	        'fas fa-male' => 'male',
	        'fas fa-map' => 'map',
	        'far fa-map' => 'map',
	        'fas fa-map-marker' => 'map-marker',
	        'fas fa-map-marker-alt' => 'map-marker-alt',
	        'fas fa-map-pin' => 'map-pin',
	        'fas fa-map-signs' => 'map-signs',
	        'fas fa-mars' => 'mars',
	        'fas fa-mars-double' => 'mars-double',
	        'fas fa-mars-stroke' => 'mars-stroke',
	        'fas fa-mars-stroke-h' => 'mars-stroke-h',
	        'fas fa-mars-stroke-v' => 'mars-stroke-v',
	        'fab fa-maxcdn' => 'maxcdn',
	        'fab fa-medapps' => 'medapps',
	        'fab fa-medium' => 'medium',
	        'fab fa-medium-m' => 'medium-m',
	        'fas fa-medkit' => 'medkit',
	        'fab fa-medrt' => 'medrt',
	        'fab fa-meetup' => 'meetup',
	        'fas fa-meh' => 'meh',
	        'far fa-meh' => 'meh',
	        'fas fa-mercury' => 'mercury',
	        'fas fa-microchip' => 'microchip',
	        'fas fa-microphone' => 'microphone',
	        'fas fa-microphone-slash' => 'microphone-slash',
	        'fab fa-microsoft' => 'microsoft',
	        'fas fa-minus' => 'minus',
	        'fas fa-minus-circle' => 'minus-circle',
	        'fas fa-minus-square' => 'minus-square',
	        'far fa-minus-square' => 'minus-square',
	        'fab fa-mix' => 'mix',
	        'fab fa-mixcloud' => 'mixcloud',
	        'fab fa-mizuni' => 'mizuni',
	        'fas fa-mobile' => 'mobile',
	        'fas fa-mobile-alt' => 'mobile-alt',
	        'fab fa-modx' => 'modx',
	        'fab fa-monero' => 'monero',
	        'fas fa-money-bill-alt' => 'money-bill-alt',
	        'far fa-money-bill-alt' => 'money-bill-alt',
	        'fas fa-moon' => 'moon',
	        'far fa-moon' => 'moon',
	        'fas fa-motorcycle' => 'motorcycle',
	        'fas fa-mouse-pointer' => 'mouse-pointer',
	        'fas fa-music' => 'music',
	        'fab fa-napster' => 'napster',
	        'fas fa-neuter' => 'neuter',
	        'fas fa-newspaper' => 'newspaper',
	        'far fa-newspaper' => 'newspaper',
	        'fab fa-nintendo-switch' => 'nintendo-switch',
	        'fab fa-node' => 'node',
	        'fab fa-node-js' => 'node-js',
	        'fas fa-notes-medical' => 'notes-medical',
	        'fab fa-npm' => 'npm',
	        'fab fa-ns8' => 'ns8',
	        'fab fa-nutritionix' => 'nutritionix',
	        'fas fa-object-group' => 'object-group',
	        'far fa-object-group' => 'object-group',
	        'fas fa-object-ungroup' => 'object-ungroup',
	        'far fa-object-ungroup' => 'object-ungroup',
	        'fab fa-odnoklassniki' => 'odnoklassniki',
	        'fab fa-odnoklassniki-square' => 'odnoklassniki-square',
	        'fab fa-opencart' => 'opencart',
	        'fab fa-openid' => 'openid',
	        'fab fa-opera' => 'opera',
	        'fab fa-optin-monster' => 'optin-monster',
	        'fab fa-osi' => 'osi',
	        'fas fa-outdent' => 'outdent',
	        'fab fa-page4' => 'page4',
	        'fab fa-pagelines' => 'pagelines',
	        'fas fa-paint-brush' => 'paint-brush',
	        'fab fa-palfed' => 'palfed',
	        'fas fa-pallet' => 'pallet',
	        'fas fa-paper-plane' => 'paper-plane',
	        'far fa-paper-plane' => 'paper-plane',
	        'fas fa-paperclip' => 'paperclip',
	        'fas fa-parachute-box' => 'parachute-box',
	        'fas fa-paragraph' => 'paragraph',
	        'fas fa-paste' => 'paste',
	        'fab fa-patreon' => 'patreon',
	        'fas fa-pause' => 'pause',
	        'fas fa-pause-circle' => 'pause-circle',
	        'far fa-pause-circle' => 'pause-circle',
	        'fas fa-paw' => 'paw',
	        'fab fa-paypal' => 'paypal',
	        'fas fa-pen-square' => 'pen-square',
	        'fas fa-pencil-alt' => 'pencil-alt',
	        'fas fa-people-carry' => 'people-carry',
	        'fas fa-percent' => 'percent',
	        'fab fa-periscope' => 'periscope',
	        'fab fa-phabricator' => 'phabricator',
	        'fab fa-phoenix-framework' => 'phoenix-framework',
	        'fas fa-phone' => 'phone',
	        'fas fa-phone-slash' => 'phone-slash',
	        'fas fa-phone-square' => 'phone-square',
	        'fas fa-phone-volume' => 'phone-volume',
	        'fab fa-php' => 'php',
	        'fab fa-pied-piper' => 'pied-piper',
	        'fab fa-pied-piper-alt' => 'pied-piper-alt',
	        'fab fa-pied-piper-hat' => 'pied-piper-hat',
	        'fab fa-pied-piper-pp' => 'pied-piper-pp',
	        'fas fa-piggy-bank' => 'piggy-bank',
	        'fas fa-pills' => 'pills',
	        'fab fa-pinterest' => 'pinterest',
	        'fab fa-pinterest-p' => 'pinterest-p',
	        'fab fa-pinterest-square' => 'pinterest-square',
	        'fas fa-plane' => 'plane',
	        'fas fa-play' => 'play',
	        'fas fa-play-circle' => 'play-circle',
	        'far fa-play-circle' => 'play-circle',
	        'fab fa-playstation' => 'playstation',
	        'fas fa-plug' => 'plug',
	        'fas fa-plus' => 'plus',
	        'fas fa-plus-circle' => 'plus-circle',
	        'fas fa-plus-square' => 'plus-square',
	        'far fa-plus-square' => 'plus-square',
	        'fas fa-podcast' => 'podcast',
	        'fas fa-poo' => 'poo',
	        'fas fa-pound-sign' => 'pound-sign',
	        'fas fa-power-off' => 'power-off',
	        'fas fa-prescription-bottle' => 'prescription-bottle',
	        'fas fa-prescription-bottle-alt' => 'prescription-bottle-alt',
	        'fas fa-print' => 'print',
	        'fas fa-procedures' => 'procedures',
	        'fab fa-product-hunt' => 'product-hunt',
	        'fab fa-pushed' => 'pushed',
	        'fas fa-puzzle-piece' => 'puzzle-piece',
	        'fab fa-python' => 'python',
	        'fab fa-qq' => 'qq',
	        'fas fa-qrcode' => 'qrcode',
	        'fas fa-question' => 'question',
	        'fas fa-question-circle' => 'question-circle',
	        'far fa-question-circle' => 'question-circle',
	        'fas fa-quidditch' => 'quidditch',
	        'fab fa-quinscape' => 'quinscape',
	        'fab fa-quora' => 'quora',
	        'fas fa-quote-left' => 'quote-left',
	        'fas fa-quote-right' => 'quote-right',
	        'fas fa-random' => 'random',
	        'fab fa-ravelry' => 'ravelry',
	        'fab fa-react' => 'react',
	        'fab fa-readme' => 'readme',
	        'fab fa-rebel' => 'rebel',
	        'fas fa-recycle' => 'recycle',
	        'fab fa-red-river' => 'red-river',
	        'fab fa-reddit' => 'reddit',
	        'fab fa-reddit-alien' => 'reddit-alien',
	        'fab fa-reddit-square' => 'reddit-square',
	        'fas fa-redo' => 'redo',
	        'fas fa-redo-alt' => 'redo-alt',
	        'fas fa-registered' => 'registered',
	        'far fa-registered' => 'registered',
	        'fab fa-rendact' => 'rendact',
	        'fab fa-renren' => 'renren',
	        'fas fa-reply' => 'reply',
	        'fas fa-reply-all' => 'reply-all',
	        'fab fa-replyd' => 'replyd',
	        'fab fa-resolving' => 'resolving',
	        'fas fa-retweet' => 'retweet',
	        'fas fa-ribbon' => 'ribbon',
	        'fas fa-road' => 'road',
	        'fas fa-rocket' => 'rocket',
	        'fab fa-rocketchat' => 'rocketchat',
	        'fab fa-rockrms' => 'rockrms',
	        'fas fa-rss' => 'rss',
	        'fas fa-rss-square' => 'rss-square',
	        'fas fa-ruble-sign' => 'ruble-sign',
	        'fas fa-rupee-sign' => 'rupee-sign',
	        'fab fa-safari' => 'safari',
	        'fab fa-sass' => 'sass',
	        'fas fa-save' => 'save',
	        'far fa-save' => 'save',
	        'fab fa-schlix' => 'schlix',
	        'fab fa-scribd' => 'scribd',
	        'fas fa-search' => 'search',
	        'fas fa-search-minus' => 'search-minus',
	        'fas fa-search-plus' => 'search-plus',
	        'fab fa-searchengin' => 'searchengin',
	        'fas fa-seedling' => 'seedling',
	        'fab fa-sellcast' => 'sellcast',
	        'fab fa-sellsy' => 'sellsy',
	        'fas fa-server' => 'server',
	        'fab fa-servicestack' => 'servicestack',
	        'fas fa-share' => 'share',
	        'fas fa-share-alt' => 'share-alt',
	        'fas fa-share-alt-square' => 'share-alt-square',
	        'fas fa-share-square' => 'share-square',
	        'far fa-share-square' => 'share-square',
	        'fas fa-shekel-sign' => 'shekel-sign',
	        'fas fa-shield-alt' => 'shield-alt',
	        'fas fa-ship' => 'ship',
	        'fas fa-shipping-fast' => 'shipping-fast',
	        'fab fa-shirtsinbulk' => 'shirtsinbulk',
	        'fas fa-shopping-bag' => 'shopping-bag',
	        'fas fa-shopping-basket' => 'shopping-basket',
	        'fas fa-shopping-cart' => 'shopping-cart',
	        'fas fa-shower' => 'shower',
	        'fas fa-sign' => 'sign',
	        'fas fa-sign-in-alt' => 'sign-in-alt',
	        'fas fa-sign-language' => 'sign-language',
	        'fas fa-sign-out-alt' => 'sign-out-alt',
	        'fas fa-signal' => 'signal',
	        'fab fa-simplybuilt' => 'simplybuilt',
	        'fab fa-sistrix' => 'sistrix',
	        'fas fa-sitemap' => 'sitemap',
	        'fab fa-skyatlas' => 'skyatlas',
	        'fab fa-skype' => 'skype',
	        'fab fa-slack' => 'slack',
	        'fab fa-slack-hash' => 'slack-hash',
	        'fas fa-sliders-h' => 'sliders-h',
	        'fab fa-slideshare' => 'slideshare',
	        'fas fa-smile' => 'smile',
	        'far fa-smile' => 'smile',
	        'fas fa-smoking' => 'smoking',
	        'fab fa-snapchat' => 'snapchat',
	        'fab fa-snapchat-ghost' => 'snapchat-ghost',
	        'fab fa-snapchat-square' => 'snapchat-square',
	        'fas fa-snowflake' => 'snowflake',
	        'far fa-snowflake' => 'snowflake',
	        'fas fa-sort' => 'sort',
	        'fas fa-sort-alpha-down' => 'sort-alpha-down',
	        'fas fa-sort-alpha-up' => 'sort-alpha-up',
	        'fas fa-sort-amount-down' => 'sort-amount-down',
	        'fas fa-sort-amount-up' => 'sort-amount-up',
	        'fas fa-sort-down' => 'sort-down',
	        'fas fa-sort-numeric-down' => 'sort-numeric-down',
	        'fas fa-sort-numeric-up' => 'sort-numeric-up',
	        'fas fa-sort-up' => 'sort-up',
	        'fab fa-soundcloud' => 'soundcloud',
	        'fas fa-space-shuttle' => 'space-shuttle',
	        'fab fa-speakap' => 'speakap',
	        'fas fa-spinner' => 'spinner',
	        'fab fa-spotify' => 'spotify',
	        'fas fa-square' => 'square',
	        'far fa-square' => 'square',
	        'fas fa-square-full' => 'square-full',
	        'fab fa-stack-exchange' => 'stack-exchange',
	        'fab fa-stack-overflow' => 'stack-overflow',
	        'fas fa-star' => 'star',
	        'far fa-star' => 'star',
	        'fas fa-star-half' => 'star-half',
	        'far fa-star-half' => 'star-half',
	        'fab fa-staylinked' => 'staylinked',
	        'fab fa-steam' => 'steam',
	        'fab fa-steam-square' => 'steam-square',
	        'fab fa-steam-symbol' => 'steam-symbol',
	        'fas fa-step-backward' => 'step-backward',
	        'fas fa-step-forward' => 'step-forward',
	        'fas fa-stethoscope' => 'stethoscope',
	        'fab fa-sticker-mule' => 'sticker-mule',
	        'fas fa-sticky-note' => 'sticky-note',
	        'far fa-sticky-note' => 'sticky-note',
	        'fas fa-stop' => 'stop',
	        'fas fa-stop-circle' => 'stop-circle',
	        'far fa-stop-circle' => 'stop-circle',
	        'fas fa-stopwatch' => 'stopwatch',
	        'fab fa-strava' => 'strava',
	        'fas fa-street-view' => 'street-view',
	        'fas fa-strikethrough' => 'strikethrough',
	        'fab fa-stripe' => 'stripe',
	        'fab fa-stripe-s' => 'stripe-s',
	        'fab fa-studiovinari' => 'studiovinari',
	        'fab fa-stumbleupon' => 'stumbleupon',
	        'fab fa-stumbleupon-circle' => 'stumbleupon-circle',
	        'fas fa-subscript' => 'subscript',
	        'fas fa-subway' => 'subway',
	        'fas fa-suitcase' => 'suitcase',
	        'fas fa-sun' => 'sun',
	        'far fa-sun' => 'sun',
	        'fab fa-superpowers' => 'superpowers',
	        'fas fa-superscript' => 'superscript',
	        'fab fa-supple' => 'supple',
	        'fas fa-sync' => 'sync',
	        'fas fa-sync-alt' => 'sync-alt',
	        'fas fa-syringe' => 'syringe',
	        'fas fa-table' => 'table',
	        'fas fa-table-tennis' => 'table-tennis',
	        'fas fa-tablet' => 'tablet',
	        'fas fa-tablet-alt' => 'tablet-alt',
	        'fas fa-tablets' => 'tablets',
	        'fas fa-tachometer-alt' => 'tachometer-alt',
	        'fas fa-tag' => 'tag',
	        'fas fa-tags' => 'tags',
	        'fas fa-tape' => 'tape',
	        'fas fa-tasks' => 'tasks',
	        'fas fa-taxi' => 'taxi',
	        'fab fa-telegram' => 'telegram',
	        'fab fa-telegram-plane' => 'telegram-plane',
	        'fab fa-tencent-weibo' => 'tencent-weibo',
	        'fas fa-terminal' => 'terminal',
	        'fas fa-text-height' => 'text-height',
	        'fas fa-text-width' => 'text-width',
	        'fas fa-th' => 'th',
	        'fas fa-th-large' => 'th-large',
	        'fas fa-th-list' => 'th-list',
	        'fab fa-themeisle' => 'themeisle',
	        'fas fa-thermometer' => 'thermometer',
	        'fas fa-thermometer-empty' => 'thermometer-empty',
	        'fas fa-thermometer-full' => 'thermometer-full',
	        'fas fa-thermometer-half' => 'thermometer-half',
	        'fas fa-thermometer-quarter' => 'thermometer-quarter',
	        'fas fa-thermometer-three-quarters' => 'thermometer-three-quarters',
	        'fas fa-thumbs-down' => 'thumbs-down',
	        'far fa-thumbs-down' => 'thumbs-down',
	        'fas fa-thumbs-up' => 'thumbs-up',
	        'far fa-thumbs-up' => 'thumbs-up',
	        'fas fa-thumbtack' => 'thumbtack',
	        'fas fa-ticket-alt' => 'ticket-alt',
	        'fas fa-times' => 'times',
	        'fas fa-times-circle' => 'times-circle',
	        'far fa-times-circle' => 'times-circle',
	        'fas fa-tint' => 'tint',
	        'fas fa-toggle-off' => 'toggle-off',
	        'fas fa-toggle-on' => 'toggle-on',
	        'fas fa-trademark' => 'trademark',
	        'fas fa-train' => 'train',
	        'fas fa-transgender' => 'transgender',
	        'fas fa-transgender-alt' => 'transgender-alt',
	        'fas fa-trash' => 'trash',
	        'fas fa-trash-alt' => 'trash-alt',
	        'far fa-trash-alt' => 'trash-alt',
	        'fas fa-tree' => 'tree',
	        'fab fa-trello' => 'trello',
	        'fab fa-tripadvisor' => 'tripadvisor',
	        'fas fa-trophy' => 'trophy',
	        'fas fa-truck' => 'truck',
	        'fas fa-truck-loading' => 'truck-loading',
	        'fas fa-truck-moving' => 'truck-moving',
	        'fas fa-tty' => 'tty',
	        'fab fa-tumblr' => 'tumblr',
	        'fab fa-tumblr-square' => 'tumblr-square',
	        'fas fa-tv' => 'tv',
	        'fab fa-twitch' => 'twitch',
	        'fab fa-twitter' => 'twitter',
	        'fab fa-twitter-square' => 'twitter-square',
	        'fab fa-typo3' => 'typo3',
	        'fab fa-uber' => 'uber',
	        'fab fa-uikit' => 'uikit',
	        'fas fa-umbrella' => 'umbrella',
	        'fas fa-underline' => 'underline',
	        'fas fa-undo' => 'undo',
	        'fas fa-undo-alt' => 'undo-alt',
	        'fab fa-uniregistry' => 'uniregistry',
	        'fas fa-universal-access' => 'universal-access',
	        'fas fa-university' => 'university',
	        'fas fa-unlink' => 'unlink',
	        'fas fa-unlock' => 'unlock',
	        'fas fa-unlock-alt' => 'unlock-alt',
	        'fab fa-untappd' => 'untappd',
	        'fas fa-upload' => 'upload',
	        'fab fa-usb' => 'usb',
	        'fas fa-user' => 'user',
	        'far fa-user' => 'user',
	        'fas fa-user-circle' => 'user-circle',
	        'far fa-user-circle' => 'user-circle',
	        'fas fa-user-md' => 'user-md',
	        'fas fa-user-plus' => 'user-plus',
	        'fas fa-user-secret' => 'user-secret',
	        'fas fa-user-times' => 'user-times',
	        'fas fa-users' => 'users',
	        'fab fa-ussunnah' => 'ussunnah',
	        'fas fa-utensil-spoon' => 'utensil-spoon',
	        'fas fa-utensils' => 'utensils',
	        'fab fa-vaadin' => 'vaadin',
	        'fas fa-venus' => 'venus',
	        'fas fa-venus-double' => 'venus-double',
	        'fas fa-venus-mars' => 'venus-mars',
	        'fab fa-viacoin' => 'viacoin',
	        'fab fa-viadeo' => 'viadeo',
	        'fab fa-viadeo-square' => 'viadeo-square',
	        'fas fa-vial' => 'vial',
	        'fas fa-vials' => 'vials',
	        'fab fa-viber' => 'viber',
	        'fas fa-video' => 'video',
	        'fas fa-video-slash' => 'video-slash',
	        'fab fa-vimeo' => 'vimeo',
	        'fab fa-vimeo-square' => 'vimeo-square',
	        'fab fa-vimeo-v' => 'vimeo-v',
	        'fab fa-vine' => 'vine',
	        'fab fa-vk' => 'vk',
	        'fab fa-vnv' => 'vnv',
	        'fas fa-volleyball-ball' => 'volleyball-ball',
	        'fas fa-volume-down' => 'volume-down',
	        'fas fa-volume-off' => 'volume-off',
	        'fas fa-volume-up' => 'volume-up',
	        'fab fa-vuejs' => 'vuejs',
	        'fas fa-warehouse' => 'warehouse',
	        'fab fa-weibo' => 'weibo',
	        'fas fa-weight' => 'weight',
	        'fab fa-weixin' => 'weixin',
	        'fab fa-whatsapp' => 'whatsapp',
	        'fab fa-whatsapp-square' => 'whatsapp-square',
	        'fas fa-wheelchair' => 'wheelchair',
	        'fab fa-whmcs' => 'whmcs',
	        'fas fa-wifi' => 'wifi',
	        'fab fa-wikipedia-w' => 'wikipedia-w',
	        'fas fa-window-close' => 'window-close',
	        'far fa-window-close' => 'window-close',
	        'fas fa-window-maximize' => 'window-maximize',
	        'far fa-window-maximize' => 'window-maximize',
	        'fas fa-window-minimize' => 'window-minimize',
	        'far fa-window-minimize' => 'window-minimize',
	        'fas fa-window-restore' => 'window-restore',
	        'far fa-window-restore' => 'window-restore',
	        'fab fa-windows' => 'windows',
	        'fas fa-wine-glass' => 'wine-glass',
	        'fas fa-won-sign' => 'won-sign',
	        'fab fa-wordpress' => 'wordpress',
	        'fab fa-wordpress-simple' => 'wordpress-simple',
	        'fab fa-wpbeginner' => 'wpbeginner',
	        'fab fa-wpexplorer' => 'wpexplorer',
	        'fab fa-wpforms' => 'wpforms',
	        'fas fa-wrench' => 'wrench',
	        'fas fa-x-ray' => 'x-ray',
	        'fab fa-xbox' => 'xbox',
	        'fab fa-xing' => 'xing',
	        'fab fa-xing-square' => 'xing-square',
	        'fab fa-y-combinator' => 'y-combinator',
	        'fab fa-yahoo' => 'yahoo',
	        'fab fa-yandex' => 'yandex',
	        'fab fa-yandex-international' => 'yandex-international',
	        'fab fa-yelp' => 'yelp',
	        'fas fa-yen-sign' => 'yen-sign',
	        'fab fa-yoast' => 'yoast',
	        'fab fa-youtube' => 'youtube',
	    ];
	}

	public function refresh($reload = true) {
		$this->load->language('marketplace/modification');

		$this->load->model('setting/modification');

		if ($this->l__6262eb5c2a83c9f29edc0359ada36fe4()) {
			// Just before files are deleted, if config settings say maintenance mode is off then turn it on
			$maintenance = $this->config->get('config_maintenance');

			$this->load->model('setting/setting');

			$this->model_setting_setting->editSettingValue('config', 'config_maintenance', true);

			//Log
			$log = [];

			// Clear all modification files
			$files = [];

			// Make path into an array
			$path = [DIR_MODIFICATION . '*'];

			// While the path array is still populated keep looping through
			while (count($path) != 0) {
				$next = array_shift($path);

				foreach (glob($next) as $file) {
					// If directory add to path array
					if (is_dir($file)) {
						$path[] = $file . '/*';
					}

					// Add the file to the files to be deleted array
					$files[] = $file;
				}
			}

			// Reverse sort the file array
			rsort($files);

			// Clear all modification files
			foreach ($files as $file) {
				if ($file != DIR_MODIFICATION . 'index.html') {
					// If file just delete
					if (is_file($file)) {
						unlink($file);

					// If directory use the remove directory function
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
			}

			// Begin
			$xml = [];

			// Load the default modification XML
			$xml[] = file_get_contents(DIR_SYSTEM . 'modification.xml');

			// This is purly for developers so they can run mods directly and have them run without upload after each change.
			$files = glob(DIR_SYSTEM . '*.ocmod.xml');

			if ($files) {
				foreach ($files as $file) {
					$xml[] = file_get_contents($file);
				}
			}

			// Get the default modification file
			$results = $this->model_setting_modification->getModifications();

			foreach ($results as $result) {
				if ($result['status']) {
					$xml[] = $result['xml'];
				}
			}

			$modification = [];

			foreach ($xml as $xml) {
				if (empty($xml)){
					continue;
				}

				$dom = new DOMDocument('1.0', 'UTF-8');
				$dom->preserveWhiteSpace = false;
				$dom->loadXml($xml);

				// Log
				$log[] = 'MOD: ' . $dom->getElementsByTagName('name')->item(0)->textContent;

				// Wipe the past modification store in the backup array
				$recovery = [];

				// Set the a recovery of the modification code in case we need to use it if an abort attribute is used.
				if (isset($modification)) {
					$recovery = $modification;
				}

				$files = $dom->getElementsByTagName('modification')->item(0)->getElementsByTagName('file');

				foreach ($files as $file) {
					$operations = $file->getElementsByTagName('operation');

					$files = explode('|', $file->getAttribute('path'));

					foreach ($files as $file) {
						$path = '';

						// Get the full path of the files that are going to be used for modification
						if ((substr($file, 0, 7) == 'catalog')) {
							$path = DIR_CATALOG . substr($file, 8);
						}

						if ((substr($file, 0, 5) == 'admin')) {
							$path = DIR_APPLICATION . substr($file, 6);
						}

						if ((substr($file, 0, 6) == 'system')) {
							$path = DIR_SYSTEM . substr($file, 7);
						}

						if ($path) {
							$files = glob($path, GLOB_BRACE);

							if ($files) {
								foreach ($files as $file) {
									// Get the key to be used for the modification cache filename.
									if (substr($file, 0, strlen(DIR_CATALOG)) == DIR_CATALOG) {
										$key = 'catalog/' . substr($file, strlen(DIR_CATALOG));
									}

									if (substr($file, 0, strlen(DIR_APPLICATION)) == DIR_APPLICATION) {
										$key = 'admin/' . substr($file, strlen(DIR_APPLICATION));
									}

									if (substr($file, 0, strlen(DIR_SYSTEM)) == DIR_SYSTEM) {
										$key = 'system/' . substr($file, strlen(DIR_SYSTEM));
									}

									// If file contents is not already in the modification array we need to load it.
									if (!isset($modification[$key])) {
										$content = file_get_contents($file);

										$modification[$key] = preg_replace('~\r?\n~', "\n", $content);
										$original[$key] = preg_replace('~\r?\n~', "\n", $content);

										// Log
										$log[] = PHP_EOL . 'FILE: ' . $key;
									}

									foreach ($operations as $operation) {
										$error = $operation->getAttribute('error');

										// Ignoreif
										$ignoreif = $operation->getElementsByTagName('ignoreif')->item(0);

										if ($ignoreif) {
											if ($ignoreif->getAttribute('regex') != 'true') {
												if (strpos($modification[$key], $ignoreif->textContent) !== false) {
													continue;
												}
											} else {
												if (preg_match($ignoreif->textContent, $modification[$key])) {
													continue;
												}
											}
										}

										$status = false;

										// Search and replace
										if ($operation->getElementsByTagName('search')->item(0)->getAttribute('regex') != 'true') {
											// Search
											$search = $operation->getElementsByTagName('search')->item(0)->textContent;
											$trim = $operation->getElementsByTagName('search')->item(0)->getAttribute('trim');
											$index = $operation->getElementsByTagName('search')->item(0)->getAttribute('index');

											// Trim line if no trim attribute is set or is set to true.
											if (!$trim || $trim == 'true') {
												$search = trim($search);
											}

											// Add
											$add = $operation->getElementsByTagName('add')->item(0)->textContent;
											$trim = $operation->getElementsByTagName('add')->item(0)->getAttribute('trim');
											$position = $operation->getElementsByTagName('add')->item(0)->getAttribute('position');
											$offset = $operation->getElementsByTagName('add')->item(0)->getAttribute('offset');

											if ($offset == '') {
												$offset = 0;
											}

											// Trim line if is set to true.
											if ($trim == 'true') {
												$add = trim($add);
											}

											// Log
											$log[] = 'CODE: ' . $search;

											// Check if using indexes
											if ($index !== '') {
												$indexes = explode(',', $index);
											} else {
												$indexes = [];
											}

											// Get all the matches
											$i = 0;

											$lines = explode("\n", $modification[$key]);

											for ($line_id = 0; $line_id < count($lines); $line_id++) {
												$line = $lines[$line_id];

												// Status
												$match = false;

												// Check to see if the line matches the search code.
												if (stripos($line, $search) !== false) {
													// If indexes are not used then just set the found status to true.
													if (!$indexes) {
														$match = true;
													} elseif (in_array($i, $indexes)) {
														$match = true;
													}

													$i++;
												}

												// Now for replacing or adding to the matched elements
												if ($match) {
													switch ($position) {
														default:
														case 'replace':
															$new_lines = explode("\n", $add);

															if ($offset < 0) {
																array_splice($lines, $line_id + $offset, abs($offset) + 1, [str_replace($search, $add, $line)]);

																$line_id -= $offset;
															} else {
																array_splice($lines, $line_id, $offset + 1, [str_replace($search, $add, $line)]);
															}
															break;
														case 'before':
															$new_lines = explode("\n", $add);

															array_splice($lines, $line_id - $offset, 0, $new_lines);

															$line_id += count($new_lines);
															break;
														case 'after':
															$new_lines = explode("\n", $add);

															array_splice($lines, ($line_id + 1) + $offset, 0, $new_lines);

															$line_id += count($new_lines);
															break;
													}

													// Log
													$log[] = 'LINE: ' . $line_id;

													$status = true;
												}
											}

											$modification[$key] = implode("\n", $lines);
										} else {
											$search = trim($operation->getElementsByTagName('search')->item(0)->textContent);
											$limit = $operation->getElementsByTagName('search')->item(0)->getAttribute('limit');
											$replace = trim($operation->getElementsByTagName('add')->item(0)->textContent);

											// Limit
											if (!$limit) {
												$limit = -1;
											}

											// Log
											$match = [];

											preg_match_all($search, $modification[$key], $match, PREG_OFFSET_CAPTURE);

											// Remove part of the the result if a limit is set.
											if ($limit > 0) {
												$match[0] = array_slice($match[0], 0, $limit);
											}

											if ($match[0]) {
												$log[] = 'REGEX: ' . $search;

												for ($i = 0; $i < count($match[0]); $i++) {
													$log[] = 'LINE: ' . (substr_count(substr($modification[$key], 0, $match[0][$i][1]), "\n") + 1);
												}

												$status = true;
											}

											// Make the modification
											$modification[$key] = preg_replace($search, $replace, $modification[$key], $limit);
										}

										if (!$status) {
											// Abort applying this modification completely.
											if ($error == 'abort') {
												$modification = $recovery;
												// Log
												$log[] = 'NOT FOUND - ABORTING!';
												break 5;
											}
											// Skip current operation or break
											elseif ($error == 'skip') {
												// Log
												$log[] = 'NOT FOUND - OPERATION SKIPPED!';
												continue;
											}
											// Break current operations
											else {
												// Log
												$log[] = 'NOT FOUND - OPERATIONS ABORTED!';
											 	break;
											}
										}
									}
								}
							}
						}
					}
				}

				// Log
				$log[] = '----------------------------------------------------------------';
			}

			// Log
			$ocmod = new Log('ocmod.log');
			$ocmod->write(implode("\n", $log));

			// Write all modification files
			foreach ($modification as $key => $value) {
				// Only create a file if there are changes
				if ($original[$key] != $value) {
					$path = '';

					$directories = explode('/', dirname($key));

					foreach ($directories as $directory) {
						$path = $path . '/' . $directory;

						if (!is_dir(DIR_MODIFICATION . $path)) {
							@mkdir(DIR_MODIFICATION . $path, 0777);
						}
					}

					$handle = fopen(DIR_MODIFICATION . $key, 'w');

					fwrite($handle, $value);

					fclose($handle);
				}
			}

			// Maintance mode back to original settings
			$this->model_setting_setting->editSettingValue('config', 'config_maintenance', $maintenance);

			// Do not return success message if refresh() was called with $data
			$this->session->data['success'] = $this->language->get('text_success');

			$handle = fopen(DIR_LOGS . 'ocmod.log', 'w+');

			fclose($handle);

			$url = '';

			if (isset($this->request->get['store_id'])) {
				$url .= '&store_id=' . $this->request->get['store_id'];
			} else {
				$url .= '&store_id=0';
			}

			if ($reload) {
				$this->response->redirect($this->url->link('extension/theme/oct_ultrastore', 'user_token=' . $this->session->data['user_token'] . '&type=theme' . $url, true));
			}
		}
	}

	public function update() {
		if (isset($this->request->get['store_id'])) {
			$url = '&store_id=' . $this->request->get['store_id'];
			$store_id = $this->request->get['store_id'];
		} else {
			$url = '&store_id=0';
			$store_id = 0;
		}

		if (!$this->user->hasPermission('access', 'octemplates/blog/oct_blogsettings')) {
			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'octemplates/blog/oct_blogsettings');
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'octemplates/blog/oct_blogsettings');
		}

		if (!$this->user->hasPermission('access', 'octemplates/stickers/oct_stickers_settings')) {
			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'octemplates/stickers/oct_stickers_settings');
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'octemplates/stickers/oct_stickers_settings');
		}
		
		if (file_exists(DIR_APPLICATION.'controller/octemplates/stickers/oct_stickers.php')) {
			unlink(DIR_APPLICATION.'controller/octemplates/stickers/oct_stickers.php');
		}
		
		if (file_exists(DIR_APPLICATION.'model/octemplates/stickers/oct_stickers_settings.php')) {
			unlink(DIR_APPLICATION.'model/octemplates/stickers/oct_stickers_settings.php');
		}
		
		if (file_exists(DIR_APPLICATION.'model/octemplates/stickers/oct_stickers.php')) {
			unlink(DIR_APPLICATION.'model/octemplates/stickers/oct_stickers.php');
		}
		
		if (file_exists(DIR_APPLICATION.'view/template/octemplates/stickers/oct_stickers_list.twig')) {
			unlink(DIR_APPLICATION.'view/template/octemplates/stickers/oct_stickers_list.twig');
		}
		
		if (file_exists(DIR_APPLICATION.'view/template/octemplates/stickers/oct_stickers_form.twig')) {
			unlink(DIR_APPLICATION.'view/template/octemplates/stickers/oct_stickers_form.twig');
		}
		
		if ($this->config->get('oct_megamenu_status')) {
			$sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "oct_megamenu_blogcategory` (";
	        $sql .= "`megamenu_id` int(11) NOT NULL, ";
	        $sql .= "`blogcategory_id` int(11) NOT NULL, ";
	        $sql .= "PRIMARY KEY (`megamenu_id`,`blogcategory_id`) ";
	        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	        
	        $this->db->query($sql);
        }
		
		if ($this->config->get('oct_popup_call_phone_status')) {
			$field_processed_exist = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "oct_popup_call_phone` WHERE Field='processed'")->num_rows;

			if (!$field_processed_exist) {
				$sql = "ALTER TABLE `" . DB_PREFIX . "oct_popup_call_phone` ADD `processed` TINYINT(1) NOT NULL DEFAULT '0' AFTER `info`;";
				
				$this->db->query($sql);
			}
        }
		
		if ($this->config->get('oct_popup_found_cheaper_status')) {
			$field_processed_exist = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "oct_popup_found_cheaper` WHERE Field='processed'")->num_rows;

			if (!$field_processed_exist) {
				$sql = "ALTER TABLE `" . DB_PREFIX . "oct_popup_found_cheaper` ADD `processed` TINYINT(1) NOT NULL DEFAULT '0' AFTER `info`;";
				
				$this->db->query($sql);
			}
        }
		
		$this->load->model('setting/setting');

		$this->model_setting_setting->editSettingValue('theme_oct_ultrastore', 'theme_oct_ultrastore_version', $this->version, $store_id);
		
		//DELETE in new Relize
		if ($this->config->get('oct_megamenu_status') && !$this->config->get('oct_megamenu_mobile_st_categories')) {
			$megamenu_data = $this->model_setting_setting->getSetting('oct_megamenu', $store_id);
			
			if (!isset($megamenu_data['oct_megamenu_mobile_st_categories'])) {
				$megamenu_data['oct_megamenu_mobile_st_categories'] = 'on';
				
				$this->model_setting_setting->editSetting('oct_megamenu', $megamenu_data);
			}
		}
		
		$this->refresh(false);
		$this->cacheDelete();
	}

	public function octSettings() {
		return [
			'theme_oct_ultrastore_status',
			'theme_oct_ultrastore_version',
			'theme_oct_ultrastore_license',
			'theme_oct_ultrastore_scripts_in_footer',
			'theme_oct_ultrastore_lazyload',
			'theme_oct_ultrastore_lazyload_image',
			'theme_oct_ultrastore_data_colors',
			'theme_oct_ultrastore_css_code',
			'theme_oct_ultrastore_js_code',
			'theme_oct_ultrastore_webp',
			'theme_oct_ultrastore_no_quantity_last',
			'theme_oct_ultrastore_sort_data',
			'theme_oct_ultrastore_data',
			'theme_oct_ultrastore_data_atributes',
			'theme_oct_ultrastore_data_cat_atr_limit',
			'theme_oct_ultrastore_data_pr_atr_limit',
			'theme_oct_ultrastore_data_model',
			'theme_oct_ultrastore_popup_cart_status',
			'theme_oct_ultrastore_product_limit',
			'theme_oct_ultrastore_product_description_length',
			'theme_oct_ultrastore_image_category_width',
			'theme_oct_ultrastore_image_category_height',
			'theme_oct_ultrastore_image_sub_category_width',
			'theme_oct_ultrastore_image_sub_category_height',
			'theme_oct_ultrastore_image_thumb_width',
			'theme_oct_ultrastore_image_thumb_height',
			'theme_oct_ultrastore_image_popup_width',
			'theme_oct_ultrastore_image_popup_height',
			'theme_oct_ultrastore_image_product_width',
			'theme_oct_ultrastore_image_product_height',
			'theme_oct_ultrastore_image_manufacturer_width',
			'theme_oct_ultrastore_image_manufacturer_height',
			'theme_oct_ultrastore_image_additional_width',
			'theme_oct_ultrastore_image_additional_height',
			'theme_oct_ultrastore_image_related_width',
			'theme_oct_ultrastore_image_related_height',
			'theme_oct_ultrastore_image_compare_width',
			'theme_oct_ultrastore_image_compare_height',
			'theme_oct_ultrastore_image_wishlist_width',
			'theme_oct_ultrastore_image_wishlist_height',
			'theme_oct_ultrastore_image_cart_width',
			'theme_oct_ultrastore_image_cart_height',
			'theme_oct_ultrastore_image_location_width',
			'theme_oct_ultrastore_image_location_height',
			'theme_oct_ultrastore_alert_status',
			'theme_oct_ultrastore_alert_data',
			'theme_oct_ultrastore_bar_data',
			'theme_oct_ultrastore_live_search_status',
			'theme_oct_ultrastore_live_search_data',
			'theme_oct_ultrastore_feedback_status',
			'theme_oct_ultrastore_feedback_data',
			'theme_oct_ultrastore_seo_title_status',
			'theme_oct_ultrastore_seo_title_data',
			'theme_oct_ultrastore_seo_url_status',
			'theme_oct_ultrastore_seo_url_data',
		];
	}

	public function install() {
		$this->load->model('setting/setting');
		$this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/theme/oct_ultrastore');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/theme/oct_ultrastore');

		$this->model_setting_setting->editSetting('theme_oct_ultrastore', [
			'theme_oct_ultrastore_status' => 0,
			'theme_oct_ultrastore_version' => $this->version,
			'theme_oct_ultrastore_license' => '',
			'theme_oct_ultrastore_directory' => 'oct_ultrastore',
			'theme_oct_ultrastore_product_limit' => 24,
			'theme_oct_ultrastore_product_description_length' => 250,
			'theme_oct_ultrastore_image_category_width' => 80,
			'theme_oct_ultrastore_image_category_height' => 80,
			'theme_oct_ultrastore_image_sub_category_width' => 88,
			'theme_oct_ultrastore_image_sub_category_height' => 88,
			'theme_oct_ultrastore_image_thumb_width' => 1000,
			'theme_oct_ultrastore_image_thumb_height' => 1000,
			'theme_oct_ultrastore_image_popup_width' => 1000,
			'theme_oct_ultrastore_image_popup_height' => 1000,
			'theme_oct_ultrastore_image_product_width' => 228,
			'theme_oct_ultrastore_image_product_height' => 228,
			'theme_oct_ultrastore_image_manufacturer_width' => 90,
			'theme_oct_ultrastore_image_manufacturer_height' => 80,
			'theme_oct_ultrastore_image_additional_width' => 90,
			'theme_oct_ultrastore_image_additional_height' => 90,
			'theme_oct_ultrastore_image_related_width' => 228,
			'theme_oct_ultrastore_image_related_height' => 228,
			'theme_oct_ultrastore_image_compare_width' => 90,
			'theme_oct_ultrastore_image_compare_height' => 90,
			'theme_oct_ultrastore_image_wishlist_width' => 50,
			'theme_oct_ultrastore_image_wishlist_height' => 50,
			'theme_oct_ultrastore_image_cart_width' => 50,
			'theme_oct_ultrastore_image_cart_height' => 50,
			'theme_oct_ultrastore_image_location_width' => 50,
			'theme_oct_ultrastore_image_location_height' => 50,
			'theme_oct_ultrastore_scripts_in_footer' => 0,
			'theme_oct_ultrastore_lazyload' => 0,
			'theme_oct_ultrastore_lazyload_image' => 'catalog/1lazy/oct_loader_product.gif',
			'theme_oct_ultrastore_css_code' => '',
			'theme_oct_ultrastore_js_code' => '',
			'theme_oct_ultrastore_webp' => 0,
			'theme_oct_ultrastore_no_quantity_last' => 1,
			'theme_oct_ultrastore_data_atributes' => 0,
			'theme_oct_ultrastore_data_cat_atr_limit' => 5,
			'theme_oct_ultrastore_data_pr_atr_limit' => 5,
			'theme_oct_ultrastore_data_model' => 0,
			'theme_oct_ultrastore_data_colors' => [
				'main_color' => '#71BE00',
				'fon_color' => '#F2F3F5',
				'top_fon_color' => '#353e48',
				'top_link_color' => '#E5E5E5',
				'top_link_color_hover' => '#E5E5E5',
				'top_link_logo_color' => '#71BE00',
				'top_text_logo_color' => '#333333',
				'menu_fon_color' => '#353e48',
				'menu_fon_cat_color' => '#71BE00',
				'menu_fon_cat_hover_color' => '#4a5663',
				'menu_fon_cat_text_color' => '#ffffff',
				'menu_fon_cat_elements_color' => '#ffffff',
				'menu_fon_cat_elements_hover_color' => '#F7F7F7',
				'menu_fon_cat_link_color' => '#333333',
				'menu_fon_cat_link_hover_color' => '#71BE00',
				'megamenu_link_color' => '#E5E5E5',
				'megamenu_fon_link_color' => '#71BE00',
				'megamenu_fon_vup_link_color' => '#333333',
				'megamenu_fon_vup_link_hover_color' => '#71BE00',
				'footer_fon_color' => '#353e48',
				'footer_text_color' => '#DEDEDE',
				'footer_link_color' => '#CBCFD4',
				'footer_link_hover_color' => '#71BE00',
				'footer_fon_email_color' => '#656c73',
				'modal_fon_title_color' => '#71BE00',
				'modal_text_title_color' => '#ffffff',
				'modal_fon_button_color' => 'rgba(153, 226, 45, 0.82)',
				'modal_fon_button_hover_color' => '#68af00',
				'modal_fon_icon_color' => '#ffffff',
				'mobile_fon_top_color' => '#353e48',
				'mobile_fon_icon_c_color' => '#71be00',
				'category_module_fon_color' => '#F3F5FB',
				'category_module_link_color' => '#666666',
				'category_module_link_hover_color' => '#71be00',
				'logo_width' => 0,
				'two_products' => 0,
			],
			'theme_oct_ultrastore_sort_data' => [
				'deff_sort' => 'p.sort_order-ASC',
				'sorts' => [
					'p.sort_order-ASC',
					'p.sort_order-DESC',
					'pd.name-ASC',
					'pd.name-DESC',
					'p.price-ASC',
					'p.price-DESC',
					'p.model-ASC',
					'p.model-DESC',
					'p.quantity-ASC',
					'p.quantity-DESC',
					'p.viewed-ASC',
					'p.viewed-DESC',
					'p.date_added-ASC',
					'p.date_added-DESC',
					'rating-ASC',
					'rating-DESC',
				],
			],
			'theme_oct_ultrastore_data' => [
				'minify' => 1,
				'micro' => 1,
				'header_information_links' => [],
				'footer_totop' => 1,
				'footer_subscribe' => 1,
				'footer_link_contact' => 1,
				'footer_link_return' => 1,
				'footer_link_sitemap' => 1,
				'footer_link_man' => 1,
				'footer_link_cert' => 1,
				'footer_link_partners' => 1,
				'footer_link_specials' => 1,
				'footer_information_links' => [],
				'footer_category_links' => [],
				'mobile_information_links' => [],
				'contact_address' => [],
				'contact_telephone' => $this->config->get('config_telephone'),
				'contact_open' => [],
				'contact_map' => '',
				'contact_email' => $this->config->get('config_email'),
				'contact_skype' => '',
				'contact_whatsapp' => $this->config->get('config_telephone'),
				'contact_viber' => $this->config->get('config_telephone'),
				'contact_telegram' => $this->config->get('config_telephone'),
				'contact_messenger' => '',
				'contact_paymants' => [],
				'contact_socials' => [],
				'man_logo' => 1,
				'contact_view_map' => 1,
				'contact_view_html' => [],
				'category_desc_position' => 'bottom',
				'category_desc_in_page' => 1,
				'category_view_subcats' => 1,
				'category_subcat_products' => 1,
				'category_product_desc' => 1,
				'category_cat_image' => 1,
				'category_view_sort_oder' => 1,
				'product_dop_tab' => 0,
				'product_dop_tab_title' => [],
				'product_dop_tab_text' => [],
				'product_js_button' => '',
				'product_atributes' => 1,
				'product_model' => 1,
				'product_sku' => 1,
				'product_wishlist' => 1,
				'product_compare' => 1,
				'product_gallery' => 1,
				'product_advantage' => 0,
				'product_advantages' => [],
				'socials' => [
					0 => [
						'icone' => 'fab fa-facebook-f',
						'link' => '',
						'title' => 'Facebook'
					],
					1 => [
						'icone' => 'fab fa-twitter',
						'link' => '',
						'title' => 'Twitter'
					],
					2 => [
						'icone' => 'fab fa-linkedin-in',
						'link' => '',
						'title' => 'LinkedIn'
					],
					3 => [
						'icone' => 'fab fa-pinterest',
						'link' => '',
						'title' => 'Pinterest'
					],
					4 => [
						'icone' => 'fab fa-tumblr',
						'link' => '',
						'title' => 'Tumblr'
					],
					5 => [
						'icone' => 'fab fa-instagram',
						'link' => '',
						'title' => 'Instagram'
					],
					6 => [
						'icone' => 'fab fa-vk',
						'link' => '',
						'title' => 'VK'
					],
					7 => [
						'icone' => 'fab fa-odnoklassniki',
						'link' => '',
						'title' => 'Od'
					],
					8 => [
						'icone' => 'fab fa-flickr',
						'link' => '',
						'title' => 'Flickr'
					],
					9 => [
						'icone' => 'fab fa-youtube',
						'link' => '',
						'title' => 'YouTube'
					],
					10 => [
						'icone' => 'fab fa-vimeo',
						'link' => '',
						'title' => 'Vimeo'
					],
					11 => [
						'icone' => 'fab fa-reddit-alien',
						'link' => '',
						'title' => 'Reddit'
					],
				],
				'payments' => [
					'sber' => 1,
					'privat24' => 1,
					'ym' => 1,
					'wm' => 1,
					'visa' => 1,
					'qw' => 1,
					'skrill' => 1,
					'interkassa' => 1,
					'lp' => 1,
					'pp' => 1,
					'robo' => 1,
					'mc' => 1,
					'maestro' => 1,
					'customers' => [],
				]
			],
			'theme_oct_ultrastore_popup_cart_status' => 1,
			'theme_oct_ultrastore_popup_cart_data' => [],
			'theme_oct_ultrastore_alert_status' => 0,
			'theme_oct_ultrastore_alert_data' => [
				'orders' => 0,
				'products' => 0,
				'oct_modules' => 0,
			],
			'theme_oct_ultrastore_bar_data' => [
				'status' => 1,
				'position' => 'left',
				'show_wishlist' => 1,
				'show_compare' => 1,
				'show_cart' => '1'
			],
			'theme_oct_ultrastore_feedback_status' => 1,
			'theme_oct_ultrastore_feedback_data' => [
				'feedback_messenger' => 1,
				'feedback_viber' => 1,
				'feedback_telegram' => 1,
				'feedback_skype' => 1,
				'feedback_whatsapp' => 1,
				'feedback_email' => 1,
				'feedback_callback' => 1,
			],
			'theme_oct_ultrastore_live_search_status' => 1,
			'theme_oct_ultrastore_live_search_data' => [
				'delay' => 500,
				'price' => 1,
				'model' => 1,
				'sku' => 1,
				'count_symbol' => 2
			],
			'theme_oct_ultrastore_seo_url_status' => 0,
			'theme_oct_ultrastore_seo_url_data' => [
				'lang_prefix' => [],
				'product' => '[name]-[model]-[lang_prefix]',
				'category' => '[name]-[lang_prefix]',
				'manufacturer' => '[name]-[lang_prefix]',
				'information' => '[name]-[lang_prefix]',
				'blog_category' => '[name]-[lang_prefix]',
				'blog_article' => '[name]-[lang_prefix]',
			],
			'theme_oct_ultrastore_seo_title_status' => 0,
			'theme_oct_ultrastore_seo_title_data' => [
				'product' => [
					'title_status' => 0,
					'title_empty' => 0,
					'title' => '',
					'description_status' => 0,
					'description_empty' => 0,
					'description' => ''
				],
				'category' => [
					'title_status' => 0,
					'title_empty' => 0,
					'title' => '',
					'description_status' => 0,
					'description_empty' => 0,
					'description' => ''
				],
				'manufacturer' => [
					'title_status' => 0,
					'title' => '',
					'description_status' => 0,
					'description' => ''
				]
			],
		]);

		$this->load->model('octemplates/design/oct_banner_plus');
        $this->load->model('octemplates/design/oct_slideshow_plus');

		$this->model_octemplates_design_oct_banner_plus->createDBTables();
        $this->model_octemplates_design_oct_slideshow_plus->createDBTables();

		$this->addOctPermissions('blog', glob(DIR_APPLICATION . 'controller/octemplates/blog/*.php'));
		$this->addOctPermissions('design', glob(DIR_APPLICATION . 'controller/octemplates/design/*.php'));
		$this->addOctPermissions('module', glob(DIR_APPLICATION . 'controller/octemplates/module/*.php'));
		$this->addOctPermissions('stickers', glob(DIR_APPLICATION . 'controller/octemplates/stickers/*.php'));
	}

	private function addOctPermissions($type = 'module', $module_files) {
		$this->load->model('user/user_group');

	    if ($module_files) {
			foreach ($module_files as $file) {
				$extension = basename($file, '.php');

				$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'octemplates/'. $type .'/' . $extension);
				$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'octemplates/'. $type .'/' . $extension);
			}
		}
    }
	
	public function uninstall() {
	    $this->load->model('setting/setting');
	    $this->load->model('user/user_group');

        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/theme/oct_ultrastore');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/theme/oct_ultrastore');

	    $this->removeOctPermissions('blog', glob(DIR_APPLICATION . 'controller/octemplates/blog/*.php'));
		$this->removeOctPermissions('design', glob(DIR_APPLICATION . 'controller/octemplates/design/*.php'));
		$this->removeOctPermissions('module', glob(DIR_APPLICATION . 'controller/octemplates/module/*.php'));
		$this->removeOctPermissions('stickers', glob(DIR_APPLICATION . 'controller/octemplates/stickers/*.php'));

	    $this->model_setting_setting->deleteSetting('theme_oct_ultrastore');
    }
	
	private function removeOctPermissions($type = 'module', $module_files) {
		$this->load->model('user/user_group');

	    if ($module_files) {
			foreach ($module_files as $file) {
				$extension = basename($file, '.php');

				$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'octemplates/'. $type .'/' . $extension);
				$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'octemplates/'. $type .'/' . $extension);
			}
		}
	}
	
	private function _1507492973($i){$a=['dGhlbWVfb2N0X3VsdHJhc3RvcmVfbGljZW5zZQ==','SFRUUF9IT1NU','dGhlbWVfb2N0X3VsdHJhc3RvcmVfbGljZW5zZQ==','bGljZW5zZQ==','ZXJyb3JfbGljZW5zZQ==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfcHJvZHVjdF9saW1pdA==','cHJvZHVjdF9saW1pdA==','ZXJyb3JfbGltaXQ=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfcHJvZHVjdF9kZXNjcmlwdGlvbl9sZW5ndGg=','cHJvZHVjdF9kZXNjcmlwdGlvbl9sZW5ndGg=','ZXJyb3JfbGltaXQ=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfY2F0ZWdvcnlfd2lkdGg=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfY2F0ZWdvcnlfaGVpZ2h0','aW1hZ2VfY2F0ZWdvcnk=','ZXJyb3JfaW1hZ2VfY2F0ZWdvcnk=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2Vfc3ViX2NhdGVnb3J5X3dpZHRo','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2Vfc3ViX2NhdGVnb3J5X2hlaWdodA==','aW1hZ2Vfc3ViX2NhdGVnb3J5','ZXJyb3JfaW1hZ2Vfc3ViX2NhdGVnb3J5','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfdGh1bWJfd2lkdGg=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfdGh1bWJfaGVpZ2h0','aW1hZ2VfdGh1bWI=','ZXJyb3JfaW1hZ2VfdGh1bWI=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfcG9wdXBfd2lkdGg=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfcG9wdXBfaGVpZ2h0','aW1hZ2VfcG9wdXA=','ZXJyb3JfaW1hZ2VfcG9wdXA=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfbWFudWZhY3R1cmVyX3dpZHRo','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfbWFudWZhY3R1cmVyX2hlaWdodA==','aW1hZ2VfbWFudWZhY3R1cmVy','ZXJyb3JfaW1hZ2VfbWFudWZhY3R1cmVy','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfcHJvZHVjdF93aWR0aA==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfcHJvZHVjdF9oZWlnaHQ=','aW1hZ2VfcHJvZHVjdA==','ZXJyb3JfaW1hZ2VfcHJvZHVjdA==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfYWRkaXRpb25hbF93aWR0aA==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfYWRkaXRpb25hbF9oZWlnaHQ=','aW1hZ2VfYWRkaXRpb25hbA==','ZXJyb3JfaW1hZ2VfYWRkaXRpb25hbA==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfcmVsYXRlZF93aWR0aA==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfcmVsYXRlZF9oZWlnaHQ=','aW1hZ2VfcmVsYXRlZA==','ZXJyb3JfaW1hZ2VfcmVsYXRlZA==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfY29tcGFyZV93aWR0aA==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfY29tcGFyZV9oZWlnaHQ=','aW1hZ2VfY29tcGFyZQ==','ZXJyb3JfaW1hZ2VfY29tcGFyZQ==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2Vfd2lzaGxpc3Rfd2lkdGg=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2Vfd2lzaGxpc3RfaGVpZ2h0','aW1hZ2Vfd2lzaGxpc3Q=','ZXJyb3JfaW1hZ2Vfd2lzaGxpc3Q=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfY2FydF93aWR0aA==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfY2FydF9oZWlnaHQ=','aW1hZ2VfY2FydA==','ZXJyb3JfaW1hZ2VfY2FydA==','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfbG9jYXRpb25fd2lkdGg=','dGhlbWVfb2N0X3VsdHJhc3RvcmVfaW1hZ2VfbG9jYXRpb25faGVpZ2h0','aW1hZ2VfbG9jYXRpb24=','ZXJyb3JfaW1hZ2VfbG9jYXRpb24=','eG4tLQ==','eG4tLS0t','Lyg/UDxkb21haW4+W2EtejAtOV1bYS16MC05XC1dezEsNjN9XC5bYS16XC5dezIsN30pJC9p','ZG9tYWlu','ZW50cnlfb2N0X2luX2NhcnRfdG8=','eG4tLQ==','eG4tLS0t','','','Lyg/UDxkb21haW4+W2EtejAtOV1bYS16MC05XC1dezEsNjN9XC5bYS16MC05XC5dezIsN30pJC9p','ZG9tYWlu','ZW50cnlfb2N0X2luX2NhcnRfdG8=','LQ=='];return base64_decode($a[$i]);}protected function l__f9ab05454998236921a6b0e281fae632(){if(!$this->user->hasPermission('modify','extension/theme/oct_ultrastore')){$this->error['warning']=$this->language->get('error_permission');}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(0)]or $this->l__d9c626d0d93d95fec63074f2e946e04c($this->{$this->_1161912638(2)}->{$this->_1161912638(0)}[$this->_1507492973(1)])!= $this->{$this->_1161912638(2)}->post[$this->_1507492973(2)]){$this->error[$this->_1507492973(3)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(4));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(5)]){$this->error[$this->_1507492973(6)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(7));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(8)]){$this->error[$this->_1507492973(9)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(10));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(11)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(12)]){$this->error[$this->_1507492973(13)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(14));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(15)]||!$this->request->post[$this->_1507492973(16)]){$this->error[$this->_1507492973(17)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(18));}if(!$this->request->post[$this->_1507492973(19)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(20)]){$this->error[$this->_1507492973(21)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(22));}if(!$this->request->post[$this->_1507492973(23)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(24)]){$this->error[$this->_1507492973(25)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(26));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(27)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(28)]){$this->error[$this->_1507492973(29)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(30));}if(!$this->request->post[$this->_1507492973(31)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(32)]){$this->error[$this->_1507492973(33)]=$this->language->get($this->_1507492973(34));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(35)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(36)]){$this->error[$this->_1507492973(37)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(38));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(39)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(40)]){$this->error[$this->_1507492973(41)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(42));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(43)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(44)]){$this->error[$this->_1507492973(45)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(46));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(47)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(48)]){$this->error[$this->_1507492973(49)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(50));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(51)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(52)]){$this->error[$this->_1507492973(53)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(54));}if(!$this->{$this->_1161912638(2)}->post[$this->_1507492973(55)]||!$this->{$this->_1161912638(2)}->post[$this->_1507492973(56)]){$this->error[$this->_1507492973(57)]=$this->{$this->_1161912638(1)}->get($this->_1507492973(58));}return!$this->error;}protected function l__56a589c102d55f2199048a0f1a73f058($_60169cd1c47b7a7a85ab44f884635e41,$_e4a3f5f7a18b1ed0ee22a93864ad15d8){if(!$this->gretret['_638099314_'][0]($_e4a3f5f7a18b1ed0ee22a93864ad15d8))$_e4a3f5f7a18b1ed0ee22a93864ad15d8=[$_e4a3f5f7a18b1ed0ee22a93864ad15d8];foreach($_e4a3f5f7a18b1ed0ee22a93864ad15d8 as $_d3fe9c10a808a54ea2a3dbd9e605b696){if(($_2a039ed8fdbf4ceaa9e79cdc3aecd1a2=$this->gretret['_638099314_'][1]($_60169cd1c47b7a7a85ab44f884635e41,$_d3fe9c10a808a54ea2a3dbd9e605b696))!== false)return $_2a039ed8fdbf4ceaa9e79cdc3aecd1a2;}return false;}protected function l__d9c626d0d93d95fec63074f2e946e04c($_8409eaa6ec0ce2ea307354b2e150f8c2){$_6629c5988eefcd88ea6f77a2ae672b96=$this->gretret['_638099314_'][2]($_8409eaa6ec0ce2ea307354b2e150f8c2,PHP_URL_PATH);if($this->l__56a589c102d55f2199048a0f1a73f058($_6629c5988eefcd88ea6f77a2ae672b96,[$this->_1507492973(59),$this->_1507492973(60)])=== false){if($this->gretret['_638099314_'][3]($this->_1507492973(61),$_6629c5988eefcd88ea6f77a2ae672b96,$_ca53e6c0538f536b092f4738d0baaaa1)){$_8409eaa6ec0ce2ea307354b2e150f8c2=$_ca53e6c0538f536b092f4738d0baaaa1[$this->_1507492973(62)] .$this->language->get($this->_1507492973(63));}}else{$_6629c5988eefcd88ea6f77a2ae672b96=$this->gretret['_638099314_'][4]([$this->_1507492973(64),$this->_1507492973(65)],[$this->_1507492973(66),$this->_1507492973(67)],$_6629c5988eefcd88ea6f77a2ae672b96);if($this->gretret['_638099314_'][5]($this->_1507492973(68),$_6629c5988eefcd88ea6f77a2ae672b96,$_ca53e6c0538f536b092f4738d0baaaa1)){$_8409eaa6ec0ce2ea307354b2e150f8c2=$_ca53e6c0538f536b092f4738d0baaaa1[$this->_1507492973(69)] .$this->language->get($this->_1507492973(70));}}$_e4a3f5f7a18b1ed0ee22a93864ad15d8=round(0+2.5+2.5);$_679e9b9234e2062f809dbd3325d37fb6=$this->gretret['_638099314_'][6]($this->gretret['_638099314_'][7]($_8409eaa6ec0ce2ea307354b2e150f8c2),round(0),$_e4a3f5f7a18b1ed0ee22a93864ad15d8);$_a16d2280393ce6a2a5428a4a8d09e354=$_e4a3f5f7a18b1ed0ee22a93864ad15d8;while($_a16d2280393ce6a2a5428a4a8d09e354<$this->gretret['_638099314_'][8]($this->gretret['_638099314_'][9]($_8409eaa6ec0ce2ea307354b2e150f8c2))){$_679e9b9234e2062f809dbd3325d37fb6 .= $this->_1507492973(71);$_679e9b9234e2062f809dbd3325d37fb6 .= $this->gretret['_638099314_'][10]($this->gretret['_638099314_'][11]($_8409eaa6ec0ce2ea307354b2e150f8c2),$_a16d2280393ce6a2a5428a4a8d09e354,$_e4a3f5f7a18b1ed0ee22a93864ad15d8);$_a16d2280393ce6a2a5428a4a8d09e354=$_a16d2280393ce6a2a5428a4a8d09e354+$_e4a3f5f7a18b1ed0ee22a93864ad15d8;}return $this->gretret['_638099314_'][12]($_679e9b9234e2062f809dbd3325d37fb6);}protected function _1161912638($i){$a=['c2VydmVy','bGFuZ3VhZ2U=','cmVxdWVzdA=='];return base64_decode($a[$i]);}protected function l__6262eb5c2a83c9f29edc0359ada36fe4(){if(!$this->user->hasPermission('modify','extension/theme/oct_ultrastore')){$this->error['warning']=$this->language->get('error_permission');}return!$this->error;}
}