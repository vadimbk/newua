<?php
class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {

			$data['oct_ultrastore_data'] = $oct_ultrastore_data = $this->config->get('theme_oct_ultrastore_data');

			if (isset($oct_ultrastore_data['product_js_button']) && !empty($oct_ultrastore_data['product_js_button'])) {
				$data['product_js_button'] = html_entity_decode($oct_ultrastore_data['product_js_button'], ENT_QUOTES, 'UTF-8');
			}

			if (isset($oct_ultrastore_data['product_dop_tab']) && !empty($oct_ultrastore_data['product_dop_tab'])) {
				$data['dop_tab'] = [
					'title' => isset($oct_ultrastore_data['product_dop_tab_title'][(int)$this->config->get('config_language_id')]) ? html_entity_decode($oct_ultrastore_data['product_dop_tab_title'][(int)$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8') : '',
					'text' => isset($oct_ultrastore_data['product_dop_tab_text'][(int)$this->config->get('config_language_id')]) ? html_entity_decode($oct_ultrastore_data['product_dop_tab_text'][(int)$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8') : '',
				];
			}

			if ((isset($oct_ultrastore_data['product_advantage']) && $oct_ultrastore_data['product_advantage'] == 'on') && (isset($oct_ultrastore_data['product_advantages']) && !empty($oct_ultrastore_data['product_advantages']))) {
				foreach ($oct_ultrastore_data['product_advantages'] as $product_advantage) {
					if (isset($product_advantage[(int)$this->config->get('config_language_id')]['title']) && !empty($product_advantage[(int)$this->config->get('config_language_id')]['title'])) {
						if (isset($product_advantage[(int)$this->config->get('config_language_id')]['link'])) {
							if ($product_advantage[(int)$this->config->get('config_language_id')]['link'] == "#" || empty($product_advantage[(int)$this->config->get('config_language_id')]['link'])) {
								$link = "javascript:;";
							} else {
								$link = $product_advantage[(int)$this->config->get('config_language_id')]['link'];
							}
						} else {
							$link = "javascript:;";
						}

						$data['oct_product_advantages'][] = [
							'information_id' => isset($product_advantage['information_id']) && !empty($product_advantage['information_id']) ? (int)$product_advantage['information_id'] : 0,
							'popup' => (isset($product_advantage['popup']) && !empty($product_advantage['popup'])) && (isset($product_advantage['information_id']) && !empty($product_advantage['information_id'])) && (isset($product_advantage['information_id']) && !empty($product_advantage['information_id'])) ? 1 : 0,
							'icone' => strip_tags(html_entity_decode($product_advantage['icone'], ENT_QUOTES, 'UTF-8')),
							'title' => strip_tags(html_entity_decode($product_advantage[(int)$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8')),
							'text' => isset($product_advantage[(int)$this->config->get('config_language_id')]['text']) ? strip_tags(html_entity_decode($product_advantage[(int)$this->config->get('config_language_id')]['text'], ENT_QUOTES, 'UTF-8')) : '',
							'link' => $link,
						];
					}
				}
			}
			

			$data['oct_popup_found_cheaper_status'] = $this->config->get('oct_popup_found_cheaper_status');
			
		$this->load->language('product/product');
$this->load->model('setting/setting');

			$data['out_of_stock'] = false;
			

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][$this->language->get('text_home')] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$this->load->model('catalog/category');
$categoriesProduct = $this->model_catalog_product->getCategories($this->request->get['product_id']);
if (!empty($categoriesProduct)) {
    foreach ($categoriesProduct as $category) {

        $category_info = $this->model_catalog_category->getCategory($category['category_id']);

        if (!empty($category_info['parent_id'])) {

            $category_info1 = $this->model_catalog_category->getCategory($category_info['parent_id']);

            if (!empty($category_info1['parent_id'])) {
                $category_info2 = $this->model_catalog_category->getCategory($category_info1['parent_id']);
                $data['breadcrumbs'][$category_info2['name']] = array(
                    'text' => $category_info2['name'],
                    'href' => $this->url->link('product/category', 'path=' . $category_info2['category_id'])
                );
            }
            $data['breadcrumbs'][$category_info1['name']] = array(
                'text' => $category_info1['name'],
                'href' => $this->url->link('product/category', 'path=' . $category_info1['category_id'])
            );
        }

        if ($category_info) {
            $data['breadcrumbs'][$category_info['name']] = array(
                'text' => $category_info['name'],
                'href' => $this->url->link('product/category', 'path=' . $category['category_id'])
            );
        }
    }
}
		
		$data['customer_group_id']=$this->customer->getGroupId();

		if (isset($this->request->get['path'])) {

header("Location: " . $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']),TRUE,301);
exit();
      	
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path)
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

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
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
				);
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

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

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
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
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);
$data['avail_status'] = $this->config->get('avail_status');
                                      $AvailArray = Array(
                                            'quantity' => $product_info['quantity'],
                                            'stock_status_id' => $product_info['stock_status_id'],
                                            'product_id' => $product_info['product_id'],
                                            );

                                         $avail_product_quantity =  $this->load->controller('extension/module/avail/GetProductStatus', $AvailArray);
										$data['avail_product_quantity'] = $avail_product_quantity;
										$data['language_id'] = (int)$this->config->get('config_language_id');
										$avail_text = $this->config->get('avail_text');
										$data['text_button_avail'] = $avail_text[$data['language_id']]['button_avail']?$avail_text[$data['language_id']]['button_avail']:$this->language->get('notify_me');
										$data['avail_button_cart_productpage'] = $this->config->get('avail_button_cart_productpage');//avail
										$data['avail_options_status'] = $this->config->get('avail_options_status')?$this->config->get('avail_options_status'):'0';//avail
										$data['change_buttom'] = $this->config->get('avail_status')?$this->config->get('avail_status'):'0';
										$data['avail_default'] = $this->config->get('avail_default');
			

		if ($product_info) {

$data['statuses'] =  $product_info['statuses']['product'];
$data['stickers'] =  $product_info['statuses']['product_stickers'];        
      

$this->load->model('extension/module/promotion');
$promotions         = $this->model_extension_module_promotion->getHTMLProductPromotions($product_id);                
$data['promotion']  = $promotions['product'];
      

			$data['oct_product_stickers'] = [];
			$data['product_sticker_colors'] = [];
			$data['you_save'] = $product_info['you_save'];
			
			if ($this->config->get('oct_stickers_status')) {
				$oct_stickers = $this->config->get('oct_stickers_data');
				
				$data['oct_sticker_you_save'] = false;
				
				if ($oct_stickers) {
					$data['oct_sticker_you_save'] = isset($oct_stickers['stickers']['special']['persent']) ? true : false;
				}
				
				$this->load->model('octemplates/stickers/oct_stickers');
				
				$oct_stickers_data = $this->model_octemplates_stickers_oct_stickers->getOCTStickers($product_info);
				
				if ($oct_stickers_data) {
					$data['oct_product_stickers'] = $oct_stickers_data['stickers'];
					$data['product_sticker_colors'] = $oct_stickers_data['sticker_colors'];
				}
			}
			
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
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
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
			);

			$this->document->setTitle($product_info['meta_title']);
			$this->document->setDescription($product_info['meta_description']);
			$this->document->setKeywords($product_info['meta_keyword']);
			$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');

		    	$this->document->addOGMeta('property="og:url"', $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']) );
                
			
			//$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			
			
			//$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			
			
			//$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			
			
			//$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			
			
			//$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			
			
			//$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
			

			$data['heading_title'] = $product_info['name'];
                        $data['heading_title1'] = str_replace('\'',"",$product_info['name']) ;


			if ($this->config->get('theme_oct_ultrastore_seo_title_status')) {
				$oct_seo_title_data = $this->config->get('theme_oct_ultrastore_seo_title_data');
				
				$oct_price = ($this->customer->isLogged() || !$this->config->get('config_customer_price')) ? $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) : '';
				$oct_special = ((float)$product_info['special']) ? $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) : '';
				
				if ((isset($oct_seo_title_data['product']['title_status']) && $oct_seo_title_data['product']['title_status']) && (isset($oct_seo_title_data['product']['title'][$this->config->get('config_language_id')]) && !empty($oct_seo_title_data['product']['title'][$this->config->get('config_language_id')]))) {
					$oct_replace = [
						'[name]' => strip_tags(html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8')),
						'[price]' => $oct_price ? $oct_special ? $oct_special : $oct_price : '',
						'[model]' => !empty($product_info['model']) ? strip_tags(html_entity_decode($product_info['model'], ENT_QUOTES, 'UTF-8')) : '',
						'[sku]' => !empty($product_info['sku']) ? strip_tags(html_entity_decode($product_info['sku'], ENT_QUOTES, 'UTF-8')) : '',
						'[category]' => (isset($category_info) && $category_info) ? strip_tags(html_entity_decode($category_info['name'], ENT_QUOTES, 'UTF-8')) : '',
						'[manufacturer]' => !empty($product_info['manufacturer']) ? strip_tags(html_entity_decode($product_info['manufacturer'], ENT_QUOTES, 'UTF-8')) : '',
						'[store]' => $this->config->get('config_name')
					];
					
					$oct_seo_title = str_replace(array_keys($oct_replace), array_values($oct_replace), $oct_seo_title_data['product']['title'][$this->config->get('config_language_id')]);
					
					if ((isset($oct_seo_title_data['product']['title_empty']) && $oct_seo_title_data['product']['title_empty']) && empty($product_info['meta_title'])) {
						$this->document->setTitle($oct_seo_title);
					} elseif (!isset($oct_seo_title_data['product']['title_empty'])) {
						$this->document->setTitle($oct_seo_title);
					}
				}
				
				if ((isset($oct_seo_title_data['product']['description_status']) && $oct_seo_title_data['product']['description_status']) && (isset($oct_seo_title_data['product']['description'][$this->config->get('config_language_id')]) && !empty($oct_seo_title_data['product']['description'][$this->config->get('config_language_id')]))) {
					$oct_replace = [
						'[name]' => strip_tags(html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8')),
						'[price]' => $oct_price ? $oct_special ? $oct_special : $oct_price : '',
						'[model]' => !empty($product_info['model']) ? strip_tags(html_entity_decode($product_info['model'], ENT_QUOTES, 'UTF-8')) : '',
						'[sku]' => !empty($product_info['sku']) ? strip_tags(html_entity_decode($product_info['sku'], ENT_QUOTES, 'UTF-8')) : '',
						'[category]' => (isset($category_info) && $category_info) ? strip_tags(html_entity_decode($category_info['name'], ENT_QUOTES, 'UTF-8')) : '',
						'[manufacturer]' => !empty($product_info['manufacturer']) ? strip_tags(html_entity_decode($product_info['manufacturer'], ENT_QUOTES, 'UTF-8')) : '',
						'[store]' => $this->config->get('config_name')
					];
					
					$oct_seo_description = str_replace(array_keys($oct_replace), array_values($oct_replace), $oct_seo_title_data['product']['description'][$this->config->get('config_language_id')]);
					
					if ((isset($oct_seo_title_data['product']['description_empty']) && $oct_seo_title_data['product']['description_empty']) && empty($product_info['meta_description'])) {
						$this->document->setDescription($oct_seo_description);
					} elseif (!isset($oct_seo_title_data['product']['description_empty'])) {
						$this->document->setDescription($oct_seo_description);
					}
				}
			}
			
			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$this->load->model('catalog/review');


			$oct_cat_info = [];
			$oct_product_categories_name = '';
			$data['oct_reviews_all'] = [];
			$data['oct_price_currency'] = '';
			$data['oct_description_microdata'] = '';
			
			if (isset($oct_ultrastore_data['micro']) && $oct_ultrastore_data['micro'] = 'on') {
				$data['oct_micro_heading_title'] = addslashes($data['heading_title']);
				
				$oct_product_categories = $this->model_catalog_product->getCategories($product_id);
				
				foreach ($oct_product_categories as $product_category) {
					$cat_info = $this->model_catalog_category->getCategory($product_category['category_id']);
					
					if ($cat_info) {
						$oct_cat_info[] = $cat_info;
					}
				}
			
				$i = 1;
				
				foreach ($oct_cat_info as $cat_info_name) {
					$oct_product_categories_name .= $cat_info_name['name'];
					
					if ($i < count($oct_cat_info)){
						$oct_product_categories_name .= ", ";
					}
					
					$i++;
				}
			
	
				$data['oct_product_categories'] = $oct_product_categories_name;
				
				$data['oct_price_microdata'] = (float)rtrim($product_info['price'], ".");
				
				
    /* Bulk Specials Editor */
    $this->load->model('extension/module/timer');
    $timer_exist = $this->model_extension_module_timer->checkExistenceExtension('module', 'timer');

    $hours_days = ($hours_days = $this->config->get('hours_and_days_settings')) ? $hours_days['module_status'] : false;

    $data['timer_custom_css_styles'] = $this->model_extension_module_timer->getCustomCSSStyles();

    $this->load->language('extension/module/timer');
    $data['text_timer_heading'] = $this->language->get('text_timer_heading');

    $data['timer'] = false; 
    $timer_settings = $this->config->get('timer_general_settings');

    $data['discount_label'] = isset($timer_settings['timer_product_page_discount_label_status']) ? 1 : 0;

    if ((float)$product_info['special']) {
      if($timer_exist && isset($timer_settings['timer_product_page_status'])){
        $product_info['date_end'] = ($hours_days && isset($product_info['datetime_end'])) ? $product_info['datetime_end'] : $product_info['date_end'];

        $data['special_date_diff'] = $this->model_extension_module_timer->getSpecialDateDiff($product_info['date_end']);
        $data['percentage_discount'] = $this->model_extension_module_timer->calculateTotalDiscount($product_info['price'], $product_info['special']);
        $data['timer'] = $product_info['timer'];

        // Load .js files and .css if we need it 
        $this->document->addStyle('catalog/view/javascript/timer/css/timer.css');
        $this->document->addScript('catalog/view/javascript/timer/jquery.plugin.min.js');
        $this->document->addScript('catalog/view/javascript/timer/jquery.countdown.min.js');

        // $lang = mb_strtolower($this->language->get('code'));
        $lang = mb_strtolower($this->config->get('config_language'));
        $lang = explode('-', $lang)[0];

        if ($lang !== 'en') {
            $this->document->addScript('catalog/view/javascript/timer/jquery.countdown-' . $lang . '.js');
        }
      } else {
        $data['timer'] = false;
      }
    /* Bulk Specials Editor */
    
					$data['oct_special_microdata'] = (float)rtrim($product_info['special'], ".");
				} else {
					$data['oct_special_microdata'] = false;
				}
				
				$data['oct_price_currency'] = $this->session->data['currency'];
				
				$data['oct_description_microdata'] = addslashes(strip_tags(str_replace("\r", "", str_replace("\n", "", html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')))));
					
				$oct_reviews_all = $this->model_catalog_review->getReviewsByProductId($product_id);
				
				foreach ($oct_reviews_all as $result) {
					$data['oct_reviews_all'][] = [
						'author'     => addslashes($result['author']),
						'text'       => addslashes(strip_tags(str_replace("\r", "", str_replace("\n", "", str_replace("\\", "/", str_replace("\"", "", $result['text'])))))),
						'rating'     => (int)$result['rating'],
						'date_added' => date($this->language->get('Y-m-d'), strtotime($result['date_added']))
					];
				}
			}
			
			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] = $product_info['model'];

			$data['sku'] = $product_info['sku'];
			$data['upc'] = $product_info['upc'];
			$data['ean'] = $product_info['ean'];
			$data['mpn'] = $product_info['mpn'];
			
			$data['warranty'] = $product_info['warranty'];
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];
			
			$data['description'] = str_replace("<img", "<img class='img-fluid'", html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'));
			


			$data['text_oct_popup_found_cheaper'] = $this->language->get('oct_product_cheaper');
			

        $oct_product_tabs_status = $this->config->get('oct_product_tabs_status');
        $data['oct_product_extra_tabs'] = [];

        if (isset($oct_product_tabs_status) && $oct_product_tabs_status) {
          $this->load->model('octemplates/module/oct_product_tabs');

          $oct_product_extra_tabs = $this->model_octemplates_module_oct_product_tabs->getProductTabs($product_id);

          if ($oct_product_extra_tabs) {
            foreach ($oct_product_extra_tabs as $extra_tab) {
              $data['oct_product_extra_tabs'][] = [
                'title' => $extra_tab['title'],
                'text'  => html_entity_decode($extra_tab['text'], ENT_QUOTES, 'UTF-8')
              ];
            }
          }
        }
      

			if (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')) {
				$data['max_quantity'] = $product_info['quantity'];
			}
			
			if ($product_info['quantity'] <= 0) {

			$data['out_of_stock'] = true;
			
				$data['stock'] = $product_info['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				$data['stock'] = $product_info['quantity'];
			} else {
				$data['stock'] = $this->language->get('text_instock');
			}

			$this->load->model('tool/image');

			if ($product_info['image']) {
				$data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
			} else {
				
			$data['popup'] = $this->model_tool_image->resize('no-thumb.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			
			}

			if ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				
			$data['thumb'] = $this->model_tool_image->resize('no-thumb.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			
			}

			$data['images'] = array();

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

				if ($product_info['image']) {
					$this->document->addOGMeta('property="og:image"', str_replace(' ', '%20', $this->model_tool_image->resize($product_info['image'], 600, 315)) );
					$this->document->addOGMeta('property="og:image:width"', '600');
					$this->document->addOGMeta('property="og:image:height"', '315');
			    } else {
		    		$this->document->addOGMeta( 'property="og:image"', str_replace(' ', '%20', $this->model_tool_image->resize($this->config->get('config_logo'), 300, 300)) );
					$this->document->addOGMeta('property="og:image:width"', '300');
					$this->document->addOGMeta('property="og:image:height"', '300');
	     		}
				foreach ($results as $result) {
			    	$this->document->addOGMeta( 'property="og:image"', str_replace(' ', '%20', $this->model_tool_image->resize($result['image'], 600, 315)) );
					$this->document->addOGMeta('property="og:image:width"', '600');
					$this->document->addOGMeta('property="og:image:height"', '315');
       			}
                

			if ($data['popup'] && $data['thumb'] && !empty($results)) {
				$data['images'][0] = array(
					'popup' => $data['popup'],
					'thumb' => $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
				);
			}
			

			foreach ($results as $result) {
				$data['images'][] = array(
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
				);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			        $data['price1'] =  str_replace(iconv("Windows-1251", "UTF-8"," ãðí"),"",$data['price']) ;
			        $data['price1'] =  str_replace(' ',"",$data['price1'] ) ;
		
                        } else {
				$data['price'] = false;
				$data['price1'] = false;
			}

			
    /* Bulk Specials Editor */
    $this->load->model('extension/module/timer');
    $timer_exist = $this->model_extension_module_timer->checkExistenceExtension('module', 'timer');

    $hours_days = ($hours_days = $this->config->get('hours_and_days_settings')) ? $hours_days['module_status'] : false;

    $data['timer_custom_css_styles'] = $this->model_extension_module_timer->getCustomCSSStyles();

    $this->load->language('extension/module/timer');
    $data['text_timer_heading'] = $this->language->get('text_timer_heading');

    $data['timer'] = false; 
    $timer_settings = $this->config->get('timer_general_settings');

    $data['discount_label'] = isset($timer_settings['timer_product_page_discount_label_status']) ? 1 : 0;

    if ((float)$product_info['special']) {
      if($timer_exist && isset($timer_settings['timer_product_page_status'])){
        $product_info['date_end'] = ($hours_days && isset($product_info['datetime_end'])) ? $product_info['datetime_end'] : $product_info['date_end'];

        $data['special_date_diff'] = $this->model_extension_module_timer->getSpecialDateDiff($product_info['date_end']);
        $data['percentage_discount'] = $this->model_extension_module_timer->calculateTotalDiscount($product_info['price'], $product_info['special']);
        $data['timer'] = $product_info['timer'];

        // Load .js files and .css if we need it 
        $this->document->addStyle('catalog/view/javascript/timer/css/timer.css');
        $this->document->addScript('catalog/view/javascript/timer/jquery.plugin.min.js');
        $this->document->addScript('catalog/view/javascript/timer/jquery.countdown.min.js');

        // $lang = mb_strtolower($this->language->get('code'));
        $lang = mb_strtolower($this->config->get('config_language'));
        $lang = explode('-', $lang)[0];

        if ($lang !== 'en') {
            $this->document->addScript('catalog/view/javascript/timer/jquery.countdown-' . $lang . '.js');
        }
      } else {
        $data['timer'] = false;
      }
    /* Bulk Specials Editor */
    
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			        $data['special1'] =  str_replace(iconv("Windows-1251", "UTF-8"," ãðí"),"",$data['special']) ;
                                $data['special1'] =  str_replace(' ',"",$data['special1'] ) ;
		
                        } else {
				$data['special'] = false;
				$data['special1'] = false;
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}
			
			$data['price_nds']= $this->currency->format((float)$product_info['special']*1.2 ? $product_info['special']*1.2 : $product_info['price']*1.2, $this->session->data['currency']);



			$data['text_oct_popup_found_cheaper'] = $this->language->get('oct_product_cheaper');
			

        $oct_product_tabs_status = $this->config->get('oct_product_tabs_status');
        $data['oct_product_extra_tabs'] = [];

        if (isset($oct_product_tabs_status) && $oct_product_tabs_status) {
          $this->load->model('octemplates/module/oct_product_tabs');

          $oct_product_extra_tabs = $this->model_octemplates_module_oct_product_tabs->getProductTabs($product_id);

          if ($oct_product_extra_tabs) {
            foreach ($oct_product_extra_tabs as $extra_tab) {
              $data['oct_product_extra_tabs'][] = [
                'title' => $extra_tab['title'],
                'text'  => html_entity_decode($extra_tab['text'], ENT_QUOTES, 'UTF-8')
              ];
            }
          }
        }
      

			if (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')) {
				$data['max_quantity'] = $product_info['quantity'];
			}
			
				if ($product_info['quantity'] <= 0) {

			$data['out_of_stock'] = true;
			
					$data['is_stock'] = $product_info['stock_status'];
				} else {
					$data['is_stock'] = false;
				}

				$data['can_buy'] = true;

				if ($product_info['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
					$data['can_buy'] = false;
				} elseif ($product_info['quantity'] <= 0 && $this->config->get('config_stock_checkout')) {
					$data['can_buy'] = true;
				}
			
			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$data['options'] = array();

        // start: OCdevWizard In Stock Alert
        $this->load->model('extension/ocdevwizard/helper');

        $ocdw_in_stock_alert_form_data = $this->model_extension_ocdevwizard_helper->getSettingData('in_stock_alert_form_data',(int)$this->config->get('config_store_id'));

        $data['ocdw_in_stock_alert_options']                 = [];
        $data['ocdw_in_stock_alert_option_related_disabled'] = 0;
        $data['ocdw_in_stock_alert_option_button_class']     = '';
        $data['ocdw_in_stock_alert_display_type']            = 0;
        $ocdw_in_stock_alert_options_status                  = 0;
        $ocdw_in_stock_alert_option_call_button              = '';
        $ocdw_in_stock_alert_option_related                  = [];

        if (isset($ocdw_in_stock_alert_form_data['activate']) && $ocdw_in_stock_alert_form_data['activate']) {
          if ($ocdw_in_stock_alert_form_data['related_option_status']) {
            $this->load->model('extension/ocdevwizard/in_stock_alert');

            $ocdw_in_stock_alert_options_status                  = 1;
            $data['ocdw_in_stock_alert_option_related_disabled'] = $ocdw_in_stock_alert_form_data['option_related_disabled'];
            $data['ocdw_in_stock_alert_option_button_class']     = $ocdw_in_stock_alert_form_data['option_button_class_product_page'];
            $data['ocdw_in_stock_alert_display_type']            = $ocdw_in_stock_alert_form_data['display_type'];

            if ($ocdw_in_stock_alert_form_data['related_option_status'] == 1) {
              $ocdw_in_stock_alert_option_related = $this->model_extension_ocdevwizard_in_stock_alert->getConfigRelatedOption((int)$this->config->get('config_store_id'));
            }

            $ocdw_in_stock_alert_text_data = $this->model_extension_ocdevwizard_helper->getSettingData('in_stock_alert_text_data',(int)$this->config->get('config_store_id'));

            $ocdw_in_stock_alert_language_id = $this->model_extension_ocdevwizard_in_stock_alert->getLanguageIdByCode($this->session->data['language']);
          }
        }
        // end: OCdevWizard In Stock Alert
      

			$oct_add_datetimepicker = false;
			

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();


        // start: OCdevWizard In Stock Alert
        if ($ocdw_in_stock_alert_options_status) {
          if ($ocdw_in_stock_alert_form_data['related_option_status'] == 1) {
            if (in_array($option['option_id'],$ocdw_in_stock_alert_option_related)) {
              foreach ($option['product_option_value'] as $ocdw_in_stock_alert_option_value) {
                if ($ocdw_in_stock_alert_option_value['quantity'] <= 0) {
                  if (isset($ocdw_in_stock_alert_text_data[$ocdw_in_stock_alert_language_id])) {
                    $ocdw_in_stock_alert_option_call_button = html_entity_decode(str_replace(['{value_name}'],[$ocdw_in_stock_alert_option_value['name']],$ocdw_in_stock_alert_text_data[$ocdw_in_stock_alert_language_id]['option_call_button_product_page']),ENT_QUOTES,'UTF-8');
                  }

                  $data['ocdw_in_stock_alert_options'][$option['product_option_id']][] = [
                    'id'   => $ocdw_in_stock_alert_option_value['product_option_value_id'],
                    'text' => $ocdw_in_stock_alert_option_call_button
                  ];
                }
              }
            }
          } else if ($ocdw_in_stock_alert_form_data['related_option_status'] == 2) {
            if (in_array($option['type'],['select','radio','checkbox','image'])) {
              foreach ($option['product_option_value'] as $ocdw_in_stock_alert_option_value) {
                if ($ocdw_in_stock_alert_option_value['quantity'] <= 0) {
                  if (isset($ocdw_in_stock_alert_text_data[$ocdw_in_stock_alert_language_id])) {
                    $ocdw_in_stock_alert_option_call_button = html_entity_decode(str_replace(['{value_name}'],[$ocdw_in_stock_alert_option_value['name']],$ocdw_in_stock_alert_text_data[$ocdw_in_stock_alert_language_id]['option_call_button_product_page']),ENT_QUOTES,'UTF-8');
                  }

                  $data['ocdw_in_stock_alert_options'][$option['product_option_id']][] = [
                    'id'   => $ocdw_in_stock_alert_option_value['product_option_value_id'],
                    'text' => $ocdw_in_stock_alert_option_call_button
                  ];
                }
              }
            }
          }
        }
        // end: OCdevWizard In Stock Alert
      
				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($this->config->get('module_avail_status')?$option_value['quantity'] >= 0 : $option_value['quantity'] > 0 )) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			}


        // start: OCdevWizard In Stock Alert
        $data['ocdw_in_stock_alert_options'] = ($ocdw_in_stock_alert_options_status) ? json_encode($data['ocdw_in_stock_alert_options']) : [];
        // end: OCdevWizard In Stock Alert
      

				$meta_price = ( $data['special'] != false) ? $data['special'] : $data['price'] ;
				$meta_price = trim(trim(($data['special'] != false) ? $data['special'] : $data['price'], $this->currency->getSymbolLeft($this->session->data['currency'])), $this->currency->getSymbolRight($this->session->data['currency']));
				$decimal_point_meta_price = $this->language->get('decimal_point') ? $this->language->get('decimal_point') : '.';
                $thousand_point_meta_price = $this->language->get('thousand_point')? $this->language->get('thousand_point') : ' ';
                $meta_price = str_replace($thousand_point_meta_price, '', $meta_price);
                if ( $decimal_point_meta_price != '.' ) {
                  $meta_price = str_replace($decimal_point_meta_price, '.', $meta_price);
                }
                $meta_price = number_format((float)$meta_price, 2, '.', '');
				
				$this->document->addOGMeta('property="product:price:amount"', $meta_price);
				$this->document->addOGMeta('property="product:price:currency"', $this->session->data['currency']);
                
			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			$data['review_status'] = $this->config->get('config_review_status');

			$data['oct_reviews_list'] = $data['review_status'] ? $this->review() : '';
			

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$data['rating'] = (int)$product_info['rating'];

			$data['total_reviews'] = (int)$product_info['reviews'];
			

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

	        if (!isset($oct_ultrastore_data['product_gallery'])) {
	            $this->document->addScript('catalog/view/theme/oct_ultrastore/js/fancybox/jquery.fancybox.min.js');
	            $this->document->addStyle('catalog/view/theme/oct_ultrastore/js/fancybox/jquery.fancybox.min.css');
            	}
            


			foreach ($data['options'] as $option) {
				if ($option['type'] == 'date' || $option['type'] == 'time' || $option['type'] == 'datetime') {
					$data['oct_datetimepicker'] = $oct_add_datetimepicker = true;

					break;
				}
			}

			if ($oct_add_datetimepicker) {
				$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
				$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
				$this->document->addScript('catalog/view/theme/oct_ultrastore/js/bootstrap-datetimepicker.min.js');
				$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
			}

			$this->document->addStyle('catalog/view/theme/oct_ultrastore/stylesheet/owl.carousel.min.css');
			$this->document->addScript('catalog/view/theme/oct_ultrastore/js/owl.carousel.min.js');
			
			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

			$limit_attr  = $this->config->get('theme_oct_ultrastore_data_pr_atr_limit') ? $this->config->get('theme_oct_ultrastore_data_pr_atr_limit') : 5;
				
			$data['oct_attributs'] = (isset($oct_ultrastore_data['product_atributes']) && $oct_ultrastore_data['product_atributes']) ? $this->model_catalog_product->getOctProductAttributes($this->request->get['product_id'], $limit_attr) : '';
			


			if ($this->config->get('config_checkout_guest') && $this->config->get('oct_popup_purchase_status')) {
				$data['oct_popup_purchase_status'] = $this->config->get('oct_popup_purchase_status');
			}

			if ($this->config->get('config_checkout_guest') && $this->config->get('oct_popup_purchase_byoneclick_status')) {
				$oct_byoneclick_data = $this->config->get('oct_popup_purchase_byoneclick_data');
				$oct_data['oct_byoneclick_status'] = isset($oct_byoneclick_data['product']) ? 1 : 0;
				$oct_data['oct_byoneclick_mask'] = $oct_byoneclick_data['mask'];
				$oct_data['oct_byoneclick_product_id'] = $this->request->get['product_id'];
				$oct_data['oct_byoneclick_page'] = '_product';
				$data['oct_byoneclick'] = $this->load->controller('octemplates/module/oct_popup_purchase/byoneclick', $oct_data);
			}
			

      //begin_devos_attribute_ext
		$this->load->model('catalog/devos_attribute_ext');
		$data['dae_attribute_view'] = $this->model_catalog_devos_attribute_ext->daeProduct((int)$this->request->get['product_id']);
		$data['attribute_groups'] = $this->model_catalog_devos_attribute_ext->daeProductTab((int)$this->request->get['product_id'], array('attributes' => $data['attribute_groups']));
      //end_devos_attribute_ext
      
			$data['products'] = array();

			$data['oct_popup_view_status'] = $this->config->get('oct_popup_view_status');
			

			$data['oct_popup_view_status'] = $this->config->get('oct_popup_view_status');
			

			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

			/** EET Module */
			$ee_position = 1;
			$data['ee_tracking'] = $this->config->get('module_ee_tracking_status');
			if ($data['ee_tracking']) {
				$data['ee_detail'] = $this->config->get('module_ee_tracking_detail_status');
				$data['ee_detail_log'] = $this->config->get('module_ee_tracking_log') ? $this->config->get('module_ee_tracking_detail_log') : false;
				$data['ee_click'] = $this->config->get('module_ee_tracking_click_status');
				$data['ee_click_log'] = $this->config->get('module_ee_tracking_click_log') ? $this->config->get('module_ee_tracking_click_log') : false;
				$data['ee_cart'] = $this->config->get('module_ee_tracking_cart_status');
				$data['ee_ga_callback'] = $this->config->get('module_ee_tracking_advanced_settings') ? $this->config->get('module_ee_tracking_ga_callback') : 0;
				$data['ee_generate_cid'] = $this->config->get('module_ee_tracking_advanced_settings') ? $this->config->get('module_ee_tracking_generate_cid') : 0;
				$data['ee_type'] = 'related';
				$ee_data = array('type' => $data['ee_type']);
				if ($results) {
					$data['ee_impression'] = $this->config->get('module_ee_tracking_impression_status');
					$data['ee_impression_log'] = $this->config->get('module_ee_tracking_log') ? $this->config->get('module_ee_tracking_impression_log') : false;
					$ee_data['position'] = $ee_position;
					foreach ($results as $item) {
						$ee_data['products'][] = $item['product_id'];
					}
					$data['ee_impression_data'] = json_encode($ee_data);
				} else {
					$data['ee_impression'] = false;
				}
				$data['ee_create_click'] = false;
				if ($data['ee_click'] && $this->config->get('module_ee_tracking_advanced_settings') && $this->config->get('module_ee_tracking_compatibility') && isset($this->request->server['HTTP_REFERER']) && $this->request->server['HTTP_REFERER']) {
					$data['ee_create_click'] = true;
					$data['ee_create_click_data'] = json_encode(array(
						'product_id' => $this->request->get['product_id'],
						'url'    => html_entity_decode($this->request->server['HTTP_REFERER'], ENT_QUOTES, 'UTF-8'),
					));
					if(isset($this->request->server['REQUEST_URI']) && strpos($this->request->server['HTTP_REFERER'], $this->request->server['REQUEST_URI']) !== false) {
						$data['ee_create_click'] = false;
					}
				}
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
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
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
				
				/*$price_nds=(float)$price*1.2;*/


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
					'product_id'  => $result['product_id'],

			'oct_stickers'  => $oct_product_stickers,
			'you_save'	  	=> $result['you_save'],
			
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					/*'price_nds'       => $price_nds,*/
					'special'     => $special,

					'stock'     => $stock,
					'can_buy'   => $can_buy,
			
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,

			'reviews'	  => $result['reviews'],
			
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
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
			
			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}

			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

            if($this->config->get('advtags_gtag_status') && $this->config->get('advtags_gtag_events') ) {
                // send gtag product view
                if(!isset($this->session->data['gtag_events'])) {
                    $this->session->data['gtag_events'] = array();
                }
                $this->session->data['gtag_events'][] = array(
                    'type' => 'view_item',
                    'params' => array(
                        'value' => $this->currency->format((float)$product_info['price'],'UAH'),
                        'items' => array(
                            array(
                              'id' => $this->request->get['product_id'],
                              'google_business_vertical' => 'retail'
                            )
                            )
                    )
                );
            } // end if
            


	    // remarketing all in one
		$this->load->model('tool/remarketing');
		if ($this->config->get('remarketing_status') && !$this->model_tool_remarketing->isBot()) {
			if (empty($category_info)) $category_info = [];
			$data = array_merge($data, $this->model_tool_remarketing->processProduct($product_info, $category_info));
		}   
	  

		if ($this->config->get('sp_auto_seo_faq_status')) {
			$this->load->model('extension/module/sp_auto_seo_faq');
			$data['faq_output'] = $this->model_extension_module_sp_auto_seo_faq->getProductFaq($product_info, $data, !empty($category_info) ? $category_info : []);
		}
		
			$this->model_catalog_product->updateViewed($this->request->get['product_id']);

            if(isset($this->request->get['product_id']) && $this->config->get('analytics_oct_analytics_yandex_ecommerce')) {
                $data['oct_analytics_yandex_ecommerce'] = $this->config->get('analytics_oct_analytics_yandex_ecommerce');
                $data['oct_analytics_yandex_container'] = $this->config->get('analytics_oct_analytics_yandex_container');

                $data['oct_analytics_yandex_product_name'] = $product_info['name'];
                $data['oct_analytics_yandex_product_special'] = str_replace(' ','', $data['special']);
                $data['oct_analytics_yandex_product_price'] = str_replace(' ','', $data['price']);
                $data['oct_analytics_yandex_product_category'] = (isset($category_info) && $category_info) ? $category_info['name'] : "";
            }
            
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');

// Full IndeX â†’
			$fx['data'] = $data;
			if (isset($product_total)) $fx['total'] = $product_total;
			$fx['name'] = $product_info['name'];

			$out = $this->load->controller('extension/module/fx', $fx);
			$data = array_merge($data, $out['data']);
// â†  Full IndeX
			
			$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			



      	$query = $this->db->query("SELECT default_meta FROM ".DB_PREFIX."product_description WHERE product_id = '".(int)$this->request->get['product_id']."' AND language_id = '".$this->config->get('config_language_id')."'");
      	if(isset($query->row['default_meta']) && $query->row['default_meta']) {
      		$this->document->setTitle($product_info['meta_title'] ? $product_info['meta_title'] : $product_info['name']);
      		$this->document->setDescription($product_info['meta_description']);
					$this->document->setKeywords($product_info['meta_keyword']);
      	} 

      
			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
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
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

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

// Full IndeX â†’
			$fx['data'] = $data;
			if (isset($product_total)) $fx['total'] = $product_total;
			$fx['name'] = $product_info['name'];

			$out = $this->load->controller('extension/module/fx', $fx);
			$data = array_merge($data, $out['data']);
// â†  Full IndeX
			
			$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}


			public function octGallery() {
				if ((isset($this->request->post['product_id']) && !empty($this->request->post['product_id'])) && isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
					$this->load->model('catalog/product');



					if (isset($this->request->post['product_id']) && !empty($this->request->post['product_id'])) {
			            $data['product_id'] = $product_id = (int) $this->request->post['product_id'];
			        } else {
			            $data['product_id'] = $product_id = 0;
			        }

					if (isset($this->request->post['goto']) && !empty($this->request->post['goto'])) {
			            $data['goto'] = (int)$this->request->post['goto'];
			        } else {
			            $data['goto'] = 0;
			        }

			        $product_info = $this->model_catalog_product->getProduct($product_id);
$data['avail_status'] = $this->config->get('avail_status');
                                      $AvailArray = Array(
                                            'quantity' => $product_info['quantity'],
                                            'stock_status_id' => $product_info['stock_status_id'],
                                            'product_id' => $product_info['product_id'],
                                            );

                                         $avail_product_quantity =  $this->load->controller('extension/module/avail/GetProductStatus', $AvailArray);
										$data['avail_product_quantity'] = $avail_product_quantity;
										$data['language_id'] = (int)$this->config->get('config_language_id');
										$avail_text = $this->config->get('avail_text');
										$data['text_button_avail'] = $avail_text[$data['language_id']]['button_avail']?$avail_text[$data['language_id']]['button_avail']:$this->language->get('notify_me');
										$data['avail_button_cart_productpage'] = $this->config->get('avail_button_cart_productpage');//avail
										$data['avail_options_status'] = $this->config->get('avail_options_status')?$this->config->get('avail_options_status'):'0';//avail
										$data['change_buttom'] = $this->config->get('avail_status')?$this->config->get('avail_status'):'0';
										$data['avail_default'] = $this->config->get('avail_default');
			

					$data['oct_popup_purchase_status'] = false;

			        if ($product_info) {

$data['statuses'] =  $product_info['statuses']['product'];
$data['stickers'] =  $product_info['statuses']['product_stickers'];        
      

$this->load->model('extension/module/promotion');
$promotions         = $this->model_extension_module_promotion->getHTMLProductPromotions($product_id);                
$data['promotion']  = $promotions['product'];
      

			$data['oct_product_stickers'] = [];
			$data['product_sticker_colors'] = [];
			$data['you_save'] = $product_info['you_save'];
			
			if ($this->config->get('oct_stickers_status')) {
				$oct_stickers = $this->config->get('oct_stickers_data');
				
				$data['oct_sticker_you_save'] = false;
				
				if ($oct_stickers) {
					$data['oct_sticker_you_save'] = isset($oct_stickers['stickers']['special']['persent']) ? true : false;
				}
				
				$this->load->model('octemplates/stickers/oct_stickers');
				
				$oct_stickers_data = $this->model_octemplates_stickers_oct_stickers->getOCTStickers($product_info);
				
				if ($oct_stickers_data) {
					$data['oct_product_stickers'] = $oct_stickers_data['stickers'];
					$data['product_sticker_colors'] = $oct_stickers_data['sticker_colors'];
				}
			}
			
						if ($product_info['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
							$data['oct_popup_purchase_status'] = false;
						} elseif ($product_info['quantity'] <= 0 && $this->config->get('config_stock_checkout')) {
							if ($this->config->get('config_checkout_guest') && $this->config->get('oct_popup_purchase_status')) {
								$data['oct_popup_purchase_status'] = true;
							}
						} else {
							if ($this->config->get('config_checkout_guest') && $this->config->get('oct_popup_purchase_status')) {
								$data['oct_popup_purchase_status'] = true;
							}
						}

				        $data['heading_title'] = $product_info['name'];

				        $this->load->model('tool/image');

						$data['images'] = [];

						$results = $this->model_catalog_product->getProductImages($product_id);

						if ($product_info['image']) {
							$data['images'][0]['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
						} else {
							$data['images'][0]['popup'] = $this->model_tool_image->resize('no-thumb.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
						}

						if ($product_info['image']) {
							$data['images'][0]['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'));
						} else {
							$data['images'][0]['thumb'] = $this->model_tool_image->resize('no-thumb.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'));
						}

						foreach ($results as $result) {
							$data['images'][] = array(
								'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height')),
								'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
							);
						}

						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$data['price'] = false;
						}

						
    /* Bulk Specials Editor */
    $this->load->model('extension/module/timer');
    $timer_exist = $this->model_extension_module_timer->checkExistenceExtension('module', 'timer');

    $hours_days = ($hours_days = $this->config->get('hours_and_days_settings')) ? $hours_days['module_status'] : false;

    $data['timer_custom_css_styles'] = $this->model_extension_module_timer->getCustomCSSStyles();

    $this->load->language('extension/module/timer');
    $data['text_timer_heading'] = $this->language->get('text_timer_heading');

    $data['timer'] = false; 
    $timer_settings = $this->config->get('timer_general_settings');

    $data['discount_label'] = isset($timer_settings['timer_product_page_discount_label_status']) ? 1 : 0;

    if ((float)$product_info['special']) {
      if($timer_exist && isset($timer_settings['timer_product_page_status'])){
        $product_info['date_end'] = ($hours_days && isset($product_info['datetime_end'])) ? $product_info['datetime_end'] : $product_info['date_end'];

        $data['special_date_diff'] = $this->model_extension_module_timer->getSpecialDateDiff($product_info['date_end']);
        $data['percentage_discount'] = $this->model_extension_module_timer->calculateTotalDiscount($product_info['price'], $product_info['special']);
        $data['timer'] = $product_info['timer'];

        // Load .js files and .css if we need it 
        $this->document->addStyle('catalog/view/javascript/timer/css/timer.css');
        $this->document->addScript('catalog/view/javascript/timer/jquery.plugin.min.js');
        $this->document->addScript('catalog/view/javascript/timer/jquery.countdown.min.js');

        // $lang = mb_strtolower($this->language->get('code'));
        $lang = mb_strtolower($this->config->get('config_language'));
        $lang = explode('-', $lang)[0];

        if ($lang !== 'en') {
            $this->document->addScript('catalog/view/javascript/timer/jquery.countdown-' . $lang . '.js');
        }
      } else {
        $data['timer'] = false;
      }
    /* Bulk Specials Editor */
    
							$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$data['special'] = false;
						}

				        $this->response->setOutput($this->load->view('octemplates/module/oct_product_gallery', $data));
			        } else {
				        $this->response->redirect($this->url->link('error/not_found', '', true));
			        }
				} else {
					$this->response->redirect($this->url->link('error/not_found', '', true));
				}
			}
			

			public function updatePrices() {
				if ((isset($this->request->post['product_id']) && isset($this->request->post['quantity'])) && isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			        $json = [];

					if ($this->request->post['product_id'] && $this->request->post['quantity']) {
						$this->load->model('catalog/product');

						$json['special'] = false;
						$json['you_save'] = false;

						$option_price = 0;

						$product_id = (int)$this->request->post['product_id'];
						$quantity = (int)$this->request->post['quantity'];

						$product_info = $this->model_catalog_product->getOCTProductPrice($product_id, $quantity);
						$product_options = $this->model_catalog_product->getProductOptions($product_id);

						if (!empty($this->request->post['option'])) {
							$options = $this->request->post['option'];
						} else {
							$options = [];
						}

			            foreach ($product_options as $product_option) {
			              	if (is_array($product_option['product_option_value'])) {
			                	foreach ($product_option['product_option_value'] as $option_value) {
									if (isset($options[$product_option['product_option_id']])) {
										if (($options[$product_option['product_option_id']] == $option_value['product_option_value_id']) || ((is_array($options[$product_option['product_option_id']])) && (in_array($option_value['product_option_value_id'], $options[$product_option['product_option_id']])))) {
											if ($option_value['price_prefix'] == '+') {
												$option_price += $option_value['price'];
											} elseif ($option_value['price_prefix'] == '-') {
												$option_price -= $option_value['price'];
											}
										}
									}
								}
							}
			            }

						$price = (float)$product_info['discount'] ? (float)$product_info['discount'] * (int)$quantity + (float)$option_price * (int)$quantity : (float)$product_info['price'] * (int)$quantity + (float)$option_price * (int)$quantity;

						$special = (float)$product_info['special'] ? (float)$product_info['special'] * (int)$quantity + (float)$option_price * (int)$quantity : 0;

						if ($special) {
							$json['special'] = $this->currency->format($this->tax->calculate($special, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							$json['you_save'] = '-' . number_format(((float)$price - (float)$special) / (float)$price * 100, 0) . '%';
						}

						$json['price'] = $this->currency->format($this->tax->calculate($price, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

						$json['tax'] = $this->currency->format((float)$special ? $special : $price, $this->session->data['currency']);
					}

					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));
				} else {
					$this->response->redirect($this->url->link('error/not_found', '', true));
				}
			}
			
	public function review() {

			if (isset($this->request->post['product_id']) && !empty($this->request->post['product_id'])) {
				$this->request->get['product_id'] = $this->request->post['product_id'];
			}
			
		$this->load->language('product/product');
$this->load->model('setting/setting');

			$data['out_of_stock'] = false;
			

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;

    		    $pagination->limit = $this->load->controller('extension/module/fx/rewiews'); // Full IndeX
    		
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		
			if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				if (isset($this->request->post['product_id']) && !empty($this->request->post['product_id'])) {
					return $this->load->view('product/review', $data);
				} else {
					$this->response->setOutput($this->load->view('product/review', $data));
				}
			} else {
				return $this->load->view('product/review', $data);
			}
			
	}

	public function write() {
		$this->load->language('product/product');
$this->load->model('setting/setting');

			$data['out_of_stock'] = false;
			

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error']['name'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error']['text'] = $this->language->get('error_text');
			}

			if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
				$json['error']['rating'] = $this->language->get('error_rating');
			}

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error']['captcha'] = $captcha;
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('catalog/review');

				$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
$this->load->model('setting/setting');

			$data['out_of_stock'] = false;
			
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);
$data['avail_status'] = $this->config->get('avail_status');
                                      $AvailArray = Array(
                                            'quantity' => $product_info['quantity'],
                                            'stock_status_id' => $product_info['stock_status_id'],
                                            'product_id' => $product_info['product_id'],
                                            );

                                         $avail_product_quantity =  $this->load->controller('extension/module/avail/GetProductStatus', $AvailArray);
										$data['avail_product_quantity'] = $avail_product_quantity;
										$data['language_id'] = (int)$this->config->get('config_language_id');
										$avail_text = $this->config->get('avail_text');
										$data['text_button_avail'] = $avail_text[$data['language_id']]['button_avail']?$avail_text[$data['language_id']]['button_avail']:$this->language->get('notify_me');
										$data['avail_button_cart_productpage'] = $this->config->get('avail_button_cart_productpage');//avail
										$data['avail_options_status'] = $this->config->get('avail_options_status')?$this->config->get('avail_options_status'):'0';//avail
										$data['change_buttom'] = $this->config->get('avail_status')?$this->config->get('avail_status'):'0';
										$data['avail_default'] = $this->config->get('avail_default');
			
		
		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
