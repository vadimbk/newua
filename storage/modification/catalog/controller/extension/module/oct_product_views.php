<?php
/**********************************************************/
/*	@copyright	OCTemplates 2015-2019.					  */
/*	@support		https://octemplates.net/				  */
/*	@license		LICENSE.txt								  */
/**********************************************************/

class ControllerExtensionModuleOctProductViews extends Controller {
    public function index($setting) {
        static $module = 0;
		
		$this->load->language('octemplates/module/oct_product_views');
		
        $data['heading_title'] = (isset($setting['heading'][(int)$this->config->get('config_language_id')]) && !empty($setting['heading'][(int)$this->config->get('config_language_id')])) ? $setting['heading'][(int)$this->config->get('config_language_id')] : $this->language->get('heading_title');

        $data['position'] = isset($setting['position']) ? $setting['position'] : '';
		
		if (empty($setting['limit'])) {
            $setting['limit'] = 8;
        }
		
        $this->load->model('catalog/product');

			$data['position'] = isset($setting['position']) ? $setting['position'] : '';
			
        $this->load->model('tool/image');


			$data['oct_popup_view_status'] = $this->config->get('oct_popup_view_status');
			
        $data['products'] = [];
        $products = [];
		
		if (isset($this->request->cookie['oct_product_views'])) {
            $products = explode(',', $this->request->cookie['oct_product_views']);
        } elseif (isset($this->session->data['oct_product_views'])) {
            $products = $this->session->data['oct_product_views'];
        }
        
        if (isset($this->request->cookie['viewed'])) {
            $products = array_merge($products, explode(',', $this->request->cookie['viewed']));
        } elseif (isset($this->session->data['viewed'])) {
            $products = array_merge($products, $this->session->data['viewed']);
        }
		
		$products = array_slice($products, 0, (int)$setting['limit']);
		
        if (!empty($products)) {

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
			
	                $width = (isset($setting['width']) && !empty($setting['width'])) ? $setting['width'] : $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
	                $height = (isset($setting['height']) && !empty($setting['height'])) ? $setting['height'] : $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');

                    if ($product_info['image'] && file_exists(DIR_IMAGE.$product_info['image'])) {
						$image = $this->model_tool_image->resize($product_info['image'], $width, $height);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $width, $height);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
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
						$rating = (int)$product_info['rating'];
					} else {
						$rating = false;
					}


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
			
					$data['products'][] = [
						'product_id'  => $product_info['product_id'],

			'oct_stickers'  => $oct_product_stickers,
			'you_save'  	=> $product_info['you_save'],
			
						'thumb'       => $image,
						'name'        => $product_info['name'],
						'description' => utf8_substr(trim(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,

					'stock'     => $stock,
					'can_buy'   => $can_buy,
			
						'tax'         => $tax,
						'minimum'     => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
						'rating'      => $rating,

			'reviews'	  => $product_info['reviews'],
			
						'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					];
                }
            }
        }

        $data['module'] = $module++;

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
			
            return $this->load->view('octemplates/module/oct_product_views', $data);
        }
    }
}