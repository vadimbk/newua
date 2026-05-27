<?php
class ControllerProductSearch extends Controller {

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
			
		$this->load->language('product/search');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

    /* Bulk Specials Editor */
    $this->load->model('extension/module/timer');
    $timer_exist = $this->model_extension_module_timer->checkExistenceExtension('module', 'timer');
    
    $hours_days = ($hours_days = $this->config->get('hours_and_days_settings')) ? $hours_days['module_status'] : false;

    $this->load->language('extension/module/timer');
    $data['text_timer_on_products_page'] = $this->language->get('text_timer_on_products_page');
    
    $timer_settings = $this->config->get('timer_general_settings');
    /* Bulk Specials Editor */
    

		$this->load->model('tool/image');

		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->get['tag'])) {
			$tag = $this->request->get['tag'];
		} elseif (isset($this->request->get['search'])) {
			$tag = $this->request->get['search'];
		} else {
			$tag = '';
		}

		if (isset($this->request->get['description'])) {
			$description = $this->request->get['description'];
		} else {
			$description = '';
		}

		if (isset($this->request->get['category_id'])) {
			$category_id = $this->request->get['category_id'];
		} else {
			$category_id = 0;
		}

		if (isset($this->request->get['sub_category'])) {
			$sub_category = $this->request->get['sub_category'];
		} else {
			$sub_category = '';
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
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		if (isset($this->request->get['search'])) {
			$this->document->setTitle($this->language->get('heading_title_second') .  ' - ' . $this->request->get['search']);

				$this->document->setRobots('noindex,nofollow');
			
		} elseif (isset($this->request->get['tag'])) {
			$this->document->setTitle($this->language->get('heading_title_second') .  ' - ' . $this->language->get('heading_tag') . $this->request->get['tag']);

				$this->document->setRobots('noindex,nofollow');
			
		} else {
			$this->document->setTitle($this->language->get('heading_title_second'));

				$this->document->setRobots('noindex,nofollow');
			
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$url = '';

		if (isset($this->request->get['search'])) {
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['tag'])) {
			$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['description'])) {
			$url .= '&description=' . $this->request->get['description'];
		}

		if (isset($this->request->get['category_id'])) {
			$url .= '&category_id=' . $this->request->get['category_id'];
		}

		if (isset($this->request->get['sub_category'])) {
			$url .= '&sub_category=' . $this->request->get['sub_category'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('product/search', $url)
		);

		if (isset($this->request->get['search'])) {
			$data['heading_title'] = $this->language->get('heading_title') .  ' - ' . $this->request->get['search'];
		} else {
			$data['heading_title'] = $this->language->get('heading_title');
		}

		$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

		$data['compare'] = $this->url->link('product/compare');

		$this->load->model('catalog/category');

		// 3 Level Category Search
		$data['categories'] = array();

		$categories_1 = $this->model_catalog_category->getCategories(0);

		foreach ($categories_1 as $category_1) {
			$level_2_data = array();

			$categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);

			foreach ($categories_2 as $category_2) {
				$level_3_data = array();

				$categories_3 = $this->model_catalog_category->getCategories($category_2['category_id']);

				foreach ($categories_3 as $category_3) {
					$level_3_data[] = array(
						'category_id' => $category_3['category_id'],
						'name'        => $category_3['name'],
					);
				}

				$level_2_data[] = array(
					'category_id' => $category_2['category_id'],
					'name'        => $category_2['name'],
					'children'    => $level_3_data
				);
			}

			$data['categories'][] = array(
				'category_id' => $category_1['category_id'],
				'name'        => $category_1['name'],
				'children'    => $level_2_data
			);
		}

		$data['products'] = array();

			$data['oct_popup_view_status'] = $this->config->get('oct_popup_view_status');
			

	        $oct_ultrastore_data_atributes = $this->config->get('theme_oct_ultrastore_data_atributes');
			

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$filter_data = array(
				'filter_name'         => $search,
				'filter_tag'          => $tag,
				'filter_description'  => $description,
				'filter_category_id'  => $category_id,
				'filter_sub_category' => $sub_category,
				'sort'                => $sort,
				'order'               => $order,
				'start'               => ($page - 1) * $limit,
				'limit'               => $limit
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
      if($timer_exist && isset($timer_settings['timer_search_page_status'])) {
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
			
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'] . $url)
				);
			}

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/search', 'sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/search', 'sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/search', 'sort=p.price&order=DESC' . $url)
			);

			if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/search', 'sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/search', 'sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/search', 'sort=p.model&order=DESC' . $url)
			);

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
						'href'  => $this->url->link('product/search', '&sort=' . $sort_order[0] . '&order='. $sort_order[1] . $url)
					);
				}
			}
			

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/search', $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}


			//begin_devos_attribute_ext
      		$this->load->model('catalog/devos_attribute_ext');
      		$data['products'] = $this->model_catalog_devos_attribute_ext->daeCatalog($data['products']);
      		//end_devos_attribute_ext
      
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/search', $url . '&page={page}');

			$data['pagination'] = $pagination->render();

	  // remarketing all in one  
	      $this->load->model('tool/remarketing');
	      if ($this->config->get('remarketing_status') && !$this->model_tool_remarketing->isBot() && !isset($filter_gr)) {
		  	  if (empty($data['heading_title'])) $data['heading_title'] = $this->language->get('heading_title');
		  	  $data = array_merge($data, $this->model_tool_remarketing->processCategory((!empty($category_info) ? $category_info : []), $data['heading_title'], $data['products']));
	      }  
	  

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			if (isset($this->request->get['search']) && $this->config->get('config_customer_search')) {
				$this->load->model('account/search');

				if ($this->customer->isLogged()) {
					$customer_id = $this->customer->getId();
				} else {
					$customer_id = 0;
				}

				if (isset($this->request->server['REMOTE_ADDR'])) {
					$ip = $this->request->server['REMOTE_ADDR'];
				} else {
					$ip = '';
				}

				$search_data = array(
					'keyword'       => $search,
					'category_id'   => $category_id,
					'sub_category'  => $sub_category,
					'description'   => $description,
					'products'      => $product_total,
					'customer_id'   => $customer_id,
					'ip'            => $ip
				);

				$this->model_account_search->addSearch($search_data);
			}
		}

		$data['search'] = $search;
		$data['description'] = $description;
		$data['category_id'] = $category_id;
		$data['sub_category'] = $sub_category;


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
			

		$this->response->setOutput($this->load->view('product/search', $data));
	}
}
