<?php
class ControllerProductManufacturer extends Controller {

    /* Bulk Specials Editor */
    private $total_timers = 0;
    /* Bulk Specials Editor */
    
	public function index() {

			$data['oct_ultrastore_data'] = $oct_ultrastore_data = $this->config->get('theme_oct_ultrastore_data');
			
			if (isset($oct_ultrastore_data['category_view_sort_oder']) && $oct_ultrastore_data['category_view_sort_oder']) {
				$oct_ultrastore_sort_data = $this->config->get('theme_oct_ultrastore_sort_data');
				
				if (isset($oct_ultrastore_sort_data['deff_sort']) && $oct_ultrastore_sort_data['deff_sort']) {
					$sort_order = explode('-', $oct_ultrastore_sort_data['deff_sort']);
				}
			}
			
		$this->load->language('product/manufacturer');

		$this->load->model('catalog/manufacturer');

		$this->load->model('tool/image');


			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_brand'),
			'href' => $this->url->link('product/manufacturer')
		);

		$data['categories'] = array();

		$results = $this->model_catalog_manufacturer->getManufacturers();

		foreach ($results as $result) {
			if (is_numeric(utf8_substr($result['name'], 0, 1))) {
				$key = '0 - 9';
			} else {
				$key = utf8_substr(utf8_strtoupper($result['name']), 0, 1);
			}

			if (!isset($data['categories'][$key])) {
				$data['categories'][$key]['name'] = $key;
			}


			if (isset($oct_ultrastore_data['man_logo']) && $oct_ultrastore_data['man_logo'] == 'on') {
				if ($result['image'] && is_file(DIR_IMAGE . $result['image'])) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_manufacturer_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_manufacturer_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_manufacturer_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_manufacturer_height'));
				}
			} else {
				$image = false;
			}
			
			$data['categories'][$key]['manufacturer'][] = array(
				'name' => $result['name'],

			'image' => $image,
			
				'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
			);
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');

// Full IndeX →
			$fx['data'] = $data;
			if (isset($product_total)) $fx['total'] = $product_total;
			$fx['name'] = isset($category_info['name']) ? $category_info['name'] : (isset($manufacturer_info['name']) ? $manufacturer_info['name'] : '');

			$out = $this->load->controller('extension/module/fx', $fx);
			$data = array_merge($data, $out['data']);
// ←  Full IndeX
			

    /* Bulk Specials Editor */
    if($this->total_timers > 0) {
      # Loading custom styles for timer 
      $data['timer_custom_css_styles'] = $this->model_extension_module_timer->getCustomCSSStyles();

      $this->document->addStyle('catalog/view/javascript/timer/css/timer.css');
      $this->document->addScript('catalog/view/javascript/timer/jquery.plugin.min.js');
      $this->document->addScript('catalog/view/javascript/timer/jquery.countdown.min.js');

      $lang = mb_strtolower($this->language->get('code'));

      if ($lang !== 'en') {
          $this->document->addScript('catalog/view/javascript/timer/jquery.countdown-' . $lang . '.js');
      }
    }
    /* Bulk Specials Editor */
    
		$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			

		$this->response->setOutput($this->load->view('product/manufacturer_list', $data));
	}

	public function info() {

			$data['oct_ultrastore_data'] = $oct_ultrastore_data = $this->config->get('theme_oct_ultrastore_data');
			
			if (isset($oct_ultrastore_data['category_view_sort_oder']) && $oct_ultrastore_data['category_view_sort_oder']) {
				$oct_ultrastore_sort_data = $this->config->get('theme_oct_ultrastore_sort_data');
				
				if (isset($oct_ultrastore_sort_data['deff_sort']) && $oct_ultrastore_sort_data['deff_sort']) {
					$sort_order = explode('-', $oct_ultrastore_sort_data['deff_sort']);
				}
			}
			
		$this->load->language('product/manufacturer');

		$this->load->model('catalog/manufacturer');

		$this->load->model('catalog/product');

    // OCDepartment start
		if (isset($this->request->get['filter_category_id'])) {
			$filter_category_id = $this->request->get['filter_category_id'];
		} else {
			$filter_category_id = 0;
		}
    // OCDepartment end
      

    /* Bulk Specials Editor */
    $this->load->model('extension/module/timer');
    $timer_exist = $this->model_extension_module_timer->checkExistenceExtension('module', 'timer');
    
    $hours_days = ($hours_days = $this->config->get('hours_and_days_settings')) ? $hours_days['module_status'] : false;

    $this->load->language('extension/module/timer');
    $data['text_timer_on_products_page'] = $this->language->get('text_timer_on_products_page');
    
    $timer_settings = $this->config->get('timer_general_settings');
    /* Bulk Specials Editor */
    

		$this->load->model('tool/image');


				if (isset($this->request->get['category_id'])) {
					$category_id = (int)$this->request->get['category_id'];
				} else {
					$category_id = 0;
				}
			
		if (isset($this->request->get['manufacturer_id'])) {
			$manufacturer_id = (int)$this->request->get['manufacturer_id'];
		} else {
			$manufacturer_id = 0;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			
			$sort = (isset($sort_order) && !empty($sort_order) && isset($sort_order[0])) ? $sort_order[0] : 'p.sort_order';
			
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			
			$order = (isset($sort_order) && !empty($sort_order) && isset($sort_order[1])) ? $sort_order[1] : 'ASC';
			
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = (int)$this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_brand'),
			'href' => $this->url->link('product/manufacturer')
		);

		$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

		if ($manufacturer_info) {

				  if ($this->request->server['HTTPS']) {
			        $server = $this->config->get('config_ssl');
		          } else {
		            $server = $this->config->get('config_url');
	              }
				if ($manufacturer_info['image']) {
				  $this->document->addOGMeta('property="og:image"', str_replace(' ', '%20', $server . 'image/' . $manufacturer_info['image']) );
				} else {
		    	  $this->document->addOGMeta('property="og:image"', str_replace(' ', '%20', $this->model_tool_image->resize($this->config->get('config_logo'), 300, 300)) );
				  $this->document->addOGMeta('property="og:image:width"', '300');
				  $this->document->addOGMeta('property="og:image:height"', '300');
				}
                

				$manufacturer_categories = $this->model_catalog_manufacturer->getManufacturerCategories($manufacturer_id);
				$categories = array();		  
				$data['manufacturer_categories'] = array();
				foreach ($manufacturer_categories as $category) {
					if (!isset($this->request->get['category_id'])) {
					  $data['manufacturer_categories'][] = array(
						  'category_id'	=> $category['category_id'],
						  'name'	=> $category['name'],
						  'href'	=> $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_id . '&category_id=' . $category['category_id'], 'SSL')
					  );
					}
					$categories[$category['category_id']] = $category['name'];
				}
			
			

			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
				$this->document->setTitle(isset($this->request->get['category_id']) ? $categories[$this->request->get['category_id']].' '.$manufacturer_info['name'] : $manufacturer_info['name']);
			

			$url = '';

    // OCDepartment start
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}
    // OCDepartment end
      

			/*if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}*/

			/*if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}*/

			/*if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}*/

			$data['breadcrumbs'][] = array(
				'text' => $manufacturer_info['name'],
				
				'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'])
			
			);


				if (isset($this->request->get['category_id'])) {
					$data['breadcrumbs'][] = array(
						'text' => $categories[$this->request->get['category_id']],
						'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&category_id=' . $this->request->get['category_id'] . $url, 'SSL')
					);
				}
			
			
				$data['heading_title'] = isset($this->request->get['category_id']) ? $categories[$this->request->get['category_id']].' '.$manufacturer_info['name'] : $manufacturer_info['name'];
			


			if ($this->config->get('theme_oct_ultrastore_seo_title_status')) {
				$oct_seo_title_data = $this->config->get('theme_oct_ultrastore_seo_title_data');
				
				if ((isset($oct_seo_title_data['manufacturer']['title_status']) && $oct_seo_title_data['manufacturer']['title_status']) && (isset($oct_seo_title_data['manufacturer']['title'][$this->config->get('config_language_id')]) && !empty($oct_seo_title_data['manufacturer']['title'][$this->config->get('config_language_id')]))) {
					$oct_replace = [
						'[name]' => strip_tags(html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8')),
						'[store]' => $this->config->get('config_name')
					];
					
					$oct_seo_title = str_replace(array_keys($oct_replace), array_values($oct_replace), $oct_seo_title_data['manufacturer']['title'][$this->config->get('config_language_id')]);
					

			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
					$this->document->setTitle($oct_seo_title);
				}
				
				if ((isset($oct_seo_title_data['manufacturer']['description_status']) && $oct_seo_title_data['manufacturer']['description_status']) && (isset($oct_seo_title_data['manufacturer']['description'][$this->config->get('config_language_id')]) && !empty($oct_seo_title_data['manufacturer']['description'][$this->config->get('config_language_id')]))) {
					$oct_replace = [
						'[name]' => strip_tags(html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8')),
						'[store]' => $this->config->get('config_name')
					];
					
					$oct_seo_description = str_replace(array_keys($oct_replace), array_values($oct_replace), $oct_seo_title_data['manufacturer']['description'][$this->config->get('config_language_id')]);
					
					$this->document->setDescription($oct_seo_description);
				}
			}
			
			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

			$data['compare'] = $this->url->link('product/compare');

			$data['products'] = array();

			$data['oct_popup_view_status'] = $this->config->get('oct_popup_view_status');
			

	        $oct_ultrastore_data_atributes = $this->config->get('theme_oct_ultrastore_data_atributes');
			

			$filter_data = array(

        // OCDepartment start
        'filter_category_id' => $filter_category_id,
        // OCDepartment end
      
				'filter_manufacturer_id' => $manufacturer_id,

				'filter_category_id' => $category_id,
				'filter_sub_category' => true,
			
				'sort'                   => $sort,
				'order'                  => $order,
				'start'                  => ($page - 1) * $limit,
				'limit'                  => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			/** EET Module */
			if (isset($page) && isset($limit)) {
				$ee_position = ($page - 1) * $limit + 1;
			} else {
				$ee_position = 1;
			}
			$data['ee_tracking'] = $this->config->get('module_ee_tracking_status');
			if ($data['ee_tracking'] && $results) {
				$data['ee_impression'] = $this->config->get('module_ee_tracking_impression_status');
				$data['ee_impression_log'] = $this->config->get('module_ee_tracking_log') ? $this->config->get('module_ee_tracking_impression_log') : false;
				$data['ee_click'] = $this->config->get('module_ee_tracking_click_status');
				$data['ee_cart'] = $this->config->get('module_ee_tracking_cart_status');
				$data['ee_ga_callback'] = $this->config->get('module_ee_tracking_advanced_settings') ? $this->config->get('module_ee_tracking_ga_callback') : 0;
				$data['ee_generate_cid'] = $this->config->get('module_ee_tracking_advanced_settings') ? $this->config->get('module_ee_tracking_generate_cid') : 0;
				$ee_class_array = preg_split('/(?=[A-Z])/', get_class($this));
				$data['ee_type'] = strtolower(array_pop($ee_class_array));
				$ee_data = array('type' => $data['ee_type']);
				$ee_data['position'] = $ee_position;
				foreach ($results as $item) {
					$ee_data['products'][] = $item['product_id'];
				}
				$data['ee_impression_data'] = json_encode($ee_data);
			}
			/** EET Module */
            

			$oct_product_stickers = [];
			$data['sticker_colors'] = [];
			
			if ($this->config->get('oct_stickers_status')) {
				$oct_stickers = $this->config->get('oct_stickers_data');
				
				$data['oct_sticker_you_save'] = false;
				
				if ($oct_stickers) {
					$data['oct_sticker_you_save'] = isset($oct_stickers['stickers']['special']['persent']) ? true : false;
				}
				
				$this->load->model('octemplates/stickers/oct_stickers');
			}
			

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				
    /* Bulk Specials Editor */
    $timer = false;

    if ((float)$result['special']) {
      if($timer_exist && isset($timer_settings['timer_manufacturer_page_status'])) {
        $timer = $result['timer'];

        $result['date_end'] = ($hours_days && isset($result['datetime_end'])) ? $result['datetime_end'] : $result['date_end'];

        $special_date_diff   = $this->model_extension_module_timer->getSpecialDateDiff($result['date_end']);
        $percentage_discount = $this->model_extension_module_timer->calculateTotalDiscount($result['price'], $result['special']);

        $this->total_timers++;
      } else {
        $timer = false;
      }
    /* Bulk Specials Editor */
    
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}


			$oct_atributes = false;
				
			if (isset($oct_ultrastore_data_atributes) && $oct_ultrastore_data_atributes) {
				$limit_attr  = $this->config->get('theme_oct_ultrastore_data_cat_atr_limit') ? $this->config->get('theme_oct_ultrastore_data_cat_atr_limit') : 5;
				
				$oct_atributes = $this->model_catalog_product->getOctProductAttributes($result['product_id'], $limit_attr);
			}
			

				if ($result['quantity'] <= 0) {
					$stock = $result['stock_status'];
				} else {
					$stock = false;
				}

				$can_buy = true;

				if ($result['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
					$can_buy = false;
				} elseif ($result['quantity'] <= 0 && $this->config->get('config_stock_checkout')) {
					$can_buy = true;
				}
			

			if (isset($oct_stickers) && $oct_stickers) {
				$oct_stickers_data = $this->model_octemplates_stickers_oct_stickers->getOCTStickers($result);
				
				$oct_product_stickers = [];
				
				if (isset($oct_stickers_data) && $oct_stickers_data) {
					$oct_product_stickers = $oct_stickers_data['stickers'];
					$data['sticker_colors'][] = $oct_stickers_data['sticker_colors'];
				}
			}
			

           if(!empty($result['product_id'])){
            $AvailArray = Array(
                'quantity' => $result['quantity'],
                'stock_status_id' => $result['stock_status_id'],
                'product_id' => $result['product_id'],
                );
            } else if(!empty($product_info['product_id'])){
             $AvailArray = Array(
                'quantity' => $product_info['quantity'],
                'stock_status_id' => $product_info['stock_status_id'],
                'product_id' => $product_info['product_id'],
                );
            } else if(!empty($product['product_id'])){
            $AvailArray = Array(
                'quantity' => $product['quantity'],
                'stock_status_id' => $product['stock_status_id'],
                'product_id' => $product['product_id'],
                );
            } else {
            $AvailArray = false;
            }


           if($AvailArray) {
                $avail_product_quantity =  $this->load->controller('extension/module/avail/GetProductStatus',$AvailArray);
           }  else {
               $avail_product_quantity = false;
           }
        
				$data['products'][] = array(
					'ee_position' => isset($ee_position) ? $ee_position++ : '',
 'avail_product_quantity'	  => $avail_product_quantity,

	 'manufacturer'    => !empty($result['manufacturer']) ? $result['manufacturer'] : '',
	 'model'           => $result['model'],
	 'google_price'    => $this->currency->format($result['special'] ? $result['special'] : $result['price'], $this->config->get('remarketing_google_currency'), '', false),
	 'facebook_price'  => $this->currency->format($result['special'] ? $result['special'] : $result['price'], $this->config->get('remarketing_facebook_currency'), '', false),
	 'ecommerce_price' => $this->currency->format($result['special'] ? $result['special'] : $result['price'], $this->config->get('remarketing_ecommerce_currency'), '', false),
	 'tiktok_price'    => $this->currency->format($result['special'] ? $result['special'] : $result['price'], $this->config->get('remarketing_tiktok_currency'), '', false),
	  
					'product_id'  => $result['product_id'],

			'oct_stickers'  => $oct_product_stickers,
			'you_save'	  	=> $result['you_save'],
			
					'thumb'       => $image,

			'oct_atributes'       => $oct_atributes,
			
					'name'        => $result['name'],
					
			'description' => (isset($oct_ultrastore_data['category_product_desc']) && $oct_ultrastore_data['category_product_desc'] == 'on') ? utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..' : false,
			
					'price'       => $price,

    /* Bulk Specials Editor */
    'special_date_diff'  => $timer == 1 ? $special_date_diff : '',
    'percentage_discount'=> $timer == 1 ? $percentage_discount : '',
    'timer'              => $timer,
    /* Bulk Specials Editor */
    
					'sku'         => $result['sku'],
					'stock_status'=> $result['stock_status'],
					'quantity' 	  => $result['quantity'],
					'special'     => $special,

					'stock'     => $stock,
					'can_buy'   => $can_buy,
			
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => 
			$this->config->get('config_review_status') ? $result['rating'] : false,
			'oct_model'	  => $this->config->get('theme_oct_ultrastore_data_model') ? $result['model'] : '',
			'reviews'	  => $result['reviews'],
			'quantity'	  => $result['quantity'] <= 0 ? true : false,
			
					'href'        => $this->url->link('product/product', 'manufacturer_id=' . $result['manufacturer_id'] . '&product_id=' . $result['product_id'] . $url)
				);
			}

			$url = '';

    // OCDepartment start
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}
    // OCDepartment end
      

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			/*$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.sort_order&order=ASC' . $url)
			);*/
			
			$data['sorts'][] = array(
					'text'  => $this->language->get('text_bestseller'),
					'value' => 'bestseller-DESC',
					'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=bestseller&order=DESC' . $url)
			);
			
			$data['sorts'][] = array(
					'text'  => $this->language->get('text_special'),
					'value' => 'special-DESC',
					'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=special&order=DESC' . $url)
			);
			
			
			$data['sorts'][] = array(
					'text'  => $this->language->get('text_review'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=rating&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.price&order=DESC' . $url)
			);

			/*if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.model&order=DESC' . $url)
			);*/

			if ((isset($oct_ultrastore_sort_data) && !empty($oct_ultrastore_sort_data)) && (isset($oct_ultrastore_sort_data['sort']) && !empty($oct_ultrastore_sort_data['sort']))) {
				$data['sorts'] = [];
				
				foreach ($oct_ultrastore_sort_data['sort'] as $oct_sort) {
					$sort_order = explode('-', $oct_sort);
					
					$sort_name = str_replace(['.','-'], ['_', '_'], $oct_sort);
					
					if (!$this->config->get('config_review_status') && $sort_order[0] == 'rating') {
						continue;
					}
					
					$data['sorts'][] = array(
						'text'  => $this->language->get('text_' . $sort_name),
						'value' => $oct_sort,
						'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=' . $sort_order[0] . '&order='. $sort_order[1] . $url)
					);
				}
			}
			

			$url = '';

    // OCDepartment start
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}
    // OCDepartment end
      

			/*if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}*/

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&limit=' . $value)
				);
			}

			$url = '';

    // OCDepartment start
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}
    // OCDepartment end
      

			/*if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}*/


			//begin_devos_attribute_ext
      		$this->load->model('catalog/devos_attribute_ext');
      		$data['products'] = $this->model_catalog_devos_attribute_ext->daeCatalog($data['products']);
      		//end_devos_attribute_ext
      

				$this->document->addOGMeta('property="og:url"', $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . ( ($page != 1) ? '&page='. $page : '' ), true) );
                
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] .  $url . '&page={page}');


		if ($this->config->get('sp_auto_seo_faq_status')) {
			$this->load->model('extension/module/sp_auto_seo_faq');
			$data['faq_output'] = $this->model_extension_module_sp_auto_seo_faq->getManufacturerFaq($manufacturer_info, $data, $page);
		}
		
			$data['pagination'] = $pagination->render();

    // OCDepartment start
		$ocd_url = '';

		if (isset($this->request->get['filter_category_id'])) {
			$ocd_url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}
    // OCDepartment end
      

	  // remarketing all in one  
	      $this->load->model('tool/remarketing');
	      if ($this->config->get('remarketing_status') && !$this->model_tool_remarketing->isBot() && !isset($filter_gr)) {
		  	  if (empty($data['heading_title'])) $data['heading_title'] = $this->language->get('heading_title');
		  	  $data = array_merge($data, $this->model_tool_remarketing->processCategory((!empty($category_info) ? $category_info : []), $data['heading_title'], $data['products']));
	      }  
	  

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
			if ($page == 1) {
			    $this->document->addLink($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $ocd_url, true), 'canonical');
			} else {
				$this->document->addLink($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&page='. $page, true), 'canonical');
			}
			
			if ($page > 1) {
			    $this->document->addLink($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&page='. (($page - 2) ? '&page='. ($page - 1) : ''), true), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
			    $this->document->addLink($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&page='. ($page + 1), true), 'next');
			}


			if (isset($data['sticker_colors']) && $data['sticker_colors']) {
				$oct_color_stickers = [];
				
				foreach ($data['sticker_colors'] as $sticker_colors) {
					foreach ($sticker_colors as $key=>$sticker_color) {
						$oct_color_stickers[$key] = $sticker_color;
					}
				}
				
				$data['sticker_colors'] = $oct_color_stickers;
			}
			
			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

    // OCDepartment start
    $this->load->model('catalog/category');

    $category_info = $this->model_catalog_category->getCategory($filter_category_id);

    if ($category_info) {

			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
      $this->document->setTitle($category_info['name'] . ' ' . $this->document->getTitle());

      $data['heading_title'] = $category_info['name'] . ' ' . $data['heading_title'];
    }
    // OCDepartment end
      

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');

// Full IndeX →
			$fx['data'] = $data;
			if (isset($product_total)) $fx['total'] = $product_total;
			$fx['name'] = isset($category_info['name']) ? $category_info['name'] : (isset($manufacturer_info['name']) ? $manufacturer_info['name'] : '');

			$out = $this->load->controller('extension/module/fx', $fx);
			$data = array_merge($data, $out['data']);
// ←  Full IndeX
			

    /* Bulk Specials Editor */
    if($this->total_timers > 0) {
      # Loading custom styles for timer 
      $data['timer_custom_css_styles'] = $this->model_extension_module_timer->getCustomCSSStyles();

      $this->document->addStyle('catalog/view/javascript/timer/css/timer.css');
      $this->document->addScript('catalog/view/javascript/timer/jquery.plugin.min.js');
      $this->document->addScript('catalog/view/javascript/timer/jquery.countdown.min.js');

      $lang = mb_strtolower($this->language->get('code'));

      if ($lang !== 'en') {
          $this->document->addScript('catalog/view/javascript/timer/jquery.countdown-' . $lang . '.js');
      }
    }
    /* Bulk Specials Editor */
    
			$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			

			$this->response->setOutput($this->load->view('product/manufacturer_info', $data));
		} else {
			$url = '';

    // OCDepartment start
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}
    // OCDepartment end
      


				if (isset($this->request->get['category_id'])) {
					$category_id = (int)$this->request->get['category_id'];
				} else {
					$category_id = 0;
				}
			
			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			/*if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}*/

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			/*if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}*/

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/manufacturer/info', $url)
			);


			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');


	        $oct_404_page_status = $this->config->get('oct_404_page_status');
			
	        if ($oct_404_page_status) {
		        $oct_404_page_data = $this->config->get('oct_404_page_data');
		        
	            if (isset($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['title']) && !empty($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['title'])) {
	                $data['heading_title'] = $oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['title'];

			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
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


// Full IndeX →
			$fx['data'] = $data;
			if (isset($product_total)) $fx['total'] = $product_total;
			$fx['name'] = isset($category_info['name']) ? $category_info['name'] : (isset($manufacturer_info['name']) ? $manufacturer_info['name'] : '');

			$out = $this->load->controller('extension/module/fx', $fx);
			$data = array_merge($data, $out['data']);
// ←  Full IndeX
			

    /* Bulk Specials Editor */
    if($this->total_timers > 0) {
      # Loading custom styles for timer 
      $data['timer_custom_css_styles'] = $this->model_extension_module_timer->getCustomCSSStyles();

      $this->document->addStyle('catalog/view/javascript/timer/css/timer.css');
      $this->document->addScript('catalog/view/javascript/timer/jquery.plugin.min.js');
      $this->document->addScript('catalog/view/javascript/timer/jquery.countdown.min.js');

      $lang = mb_strtolower($this->language->get('code'));

      if ($lang !== 'en') {
          $this->document->addScript('catalog/view/javascript/timer/jquery.countdown-' . $lang . '.js');
      }
    }
    /* Bulk Specials Editor */
    
			$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			
			$data['footer'] = $this->load->controller('common/footer');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
