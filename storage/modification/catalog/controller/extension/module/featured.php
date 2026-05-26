<?php
class ControllerExtensionModuleFeatured extends Controller {

    /* Bulk Specials Editor */
    private $total_timers = 0;
    /* Bulk Specials Editor */
    
	public function index($setting) {

			static $module = 0;
			
		$this->load->language('extension/module/featured');

		$this->load->model('catalog/product');

    /* Bulk Specials Editor */
    $this->load->model('extension/module/timer');
    $timer_exist = $this->model_extension_module_timer->checkExistenceExtension('module', 'timer');
    
    $hours_days = ($hours_days = $this->config->get('hours_and_days_settings')) ? $hours_days['module_status'] : false;

    $this->load->language('extension/module/timer');
    $data['text_timer_on_products_page'] = $this->language->get('text_timer_on_products_page');
    
    $timer_settings = $this->config->get('timer_general_settings');
    /* Bulk Specials Editor */
    

			$data['position'] = isset($setting['position']) ? $setting['position'] : '';
			

		$this->load->model('tool/image');

				
			$this->load->model('module/ukrcredits');
			

			$data['oct_popup_view_status'] = $this->config->get('oct_popup_view_status');
			
		$data['products'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (!empty($setting['product'])) {
			$products = array_slice($setting['product'], 0, (int)$setting['limit']);


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
			

            /** EET Module */
			$ee_position = 1;
			$data['ee_tracking'] = $this->config->get('module_ee_tracking_status');
			if ($data['ee_tracking']) {
				$data['ee_impression'] = $this->config->get('module_ee_tracking_impression_status');
				$data['ee_impression_log'] = $this->config->get('module_ee_tracking_log') ? $this->config->get('module_ee_tracking_impression_log') : false;
				$data['ee_click'] = $this->config->get('module_ee_tracking_click_status');
				$data['ee_cart'] = $this->config->get('module_ee_tracking_cart_status');
				$data['ee_ga_callback'] = $this->config->get('module_ee_tracking_advanced_settings') ? $this->config->get('module_ee_tracking_ga_callback') : 0;
				$data['ee_generate_cid'] = $this->config->get('module_ee_tracking_advanced_settings') ? $this->config->get('module_ee_tracking_generate_cid') : 0;
				$data['ee_type'] = 'module_featured';
				$ee_data = array('type' => $data['ee_type']);
				foreach ($products as $product_id) {
					$ee_data['products'][] = $product_id;
				}
				$data['ee_impression_data'] = json_encode($ee_data);
			}
			/** EET Module */
            
			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {

			if (isset($oct_stickers) && $oct_stickers) {
				$oct_stickers_data = $this->model_octemplates_stickers_oct_stickers->getOCTStickers($product_info);
				
				$oct_product_stickers = [];
				
				if ($oct_stickers_data) {
					$oct_product_stickers = $oct_stickers_data['stickers'];
					$data['sticker_colors'][] = $oct_stickers_data['sticker_colors'];
				}
			}
			
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					
    /* Bulk Specials Editor */
    $timer = false;

    if ((float)$product_info['special']) {
      if($timer_exist && isset($timer_settings['timer_in_featured_module_status'])) {
        $timer = $product_info['timer'];

        $product_info['date_end'] = ($hours_days && isset($product_info['datetime_end'])) ? $product_info['datetime_end'] : $product_info['date_end'];

        $special_date_diff   = $this->model_extension_module_timer->getSpecialDateDiff($product_info['date_end']);
        $percentage_discount = $this->model_extension_module_timer->calculateTotalDiscount($product_info['price'], $product_info['special']);

        $this->total_timers++;
      } else {
        $timer = false;
      }
    /* Bulk Specials Editor */
    
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
					} else {
						$rating = false;
					}

				
				$ukrcredits_stickers = $this->model_module_ukrcredits->checkproduct($product_info);
			

				if ($product_info['quantity'] <= 0) {
					$stock = $product_info['stock_status'];
				} else {
					$stock = false;
				}

				$can_buy = true;

				if ($product_info['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
					$can_buy = false;
				} elseif ($product_info['quantity'] <= 0 && $this->config->get('config_stock_checkout')) {
					$can_buy = true;
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
						'product_id'  => $product_info['product_id'],

			'oct_stickers'  => $oct_product_stickers,
			'you_save'  	=> $product_info['you_save'],
			
						'thumb'       => $image,
'ukrcredits_stickers' => isset($ukrcredits_stickers)?$ukrcredits_stickers:array(),
						'name'        => $product_info['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,

    /* Bulk Specials Editor */
    'special_date_diff'  => $timer == 1 ? $special_date_diff : '',
    'percentage_discount'=> $timer == 1 ? $percentage_discount : '',
    'timer'              => $timer,
    /* Bulk Specials Editor */
    
						'special'     => $special,

					'stock'     => $stock,
					'can_buy'   => $can_buy,
			
						'tax'         => $tax,
						'rating'      => $rating,

			'reviews'	  => $product_info['reviews'],
			
						'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					);
				}
			}
		}

		if ($data['products']) {

			if (isset($data['sticker_colors']) && $data['sticker_colors']) {
				$oct_color_stickers = [];
				
				foreach ($data['sticker_colors'] as $sticker_colors) {
					foreach ($sticker_colors as $key=>$sticker_color) {
						$oct_color_stickers[$key] = $sticker_color;
					}
				}
				
				$data['sticker_colors'] = $oct_color_stickers;
			}
			

			$data['module'] = $module++;
			

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
    

			//begin_devos_attribute_ext
      		$this->load->model('catalog/devos_attribute_ext');
      		$data['products'] = $this->model_catalog_devos_attribute_ext->daeCatalog($data['products']);
      		//end_devos_attribute_ext
      
			return $this->load->view('extension/module/featured', $data);
		}
	}
}