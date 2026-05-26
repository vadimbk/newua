<?php
/**********************************************************/
/*	@copyright	OCTemplates 2015-2019.					  */
/*	@support		https://octemplates.net/				  */
/*	@license		LICENSE.txt								  */
/**********************************************************/

class ControllerExtensionModuleOctProductsFromCategory extends Controller {
    public function index($setting) {
        static $module = 0;
		
		$this->load->language('octemplates/module/oct_products_from_category');
		
        $data['heading_title'] = (isset($setting['heading'][(int)$this->config->get('config_language_id')]) && !empty($setting['heading'][(int)$this->config->get('config_language_id')])) ? $setting['heading'][(int)$this->config->get('config_language_id')] : $this->language->get('heading_title');
        $data['link']          = (isset($setting['link'][(int)$this->config->get('config_language_id')]) && !empty($setting['link'][(int)$this->config->get('config_language_id')])) ? $setting['link'][(int)$this->config->get('config_language_id')] : false;

        $data['position'] = isset($setting['position']) ? $setting['position'] : '';

        $this->load->model('catalog/product');
        $this->load->model('octemplates/module/oct_products_from_category');
        $this->load->model('tool/image');

        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string) $this->request->get['path']);
        } else {
            $parts = [];
        }

        $category_id = (int)array_pop($parts);

        $products = [];

		if (empty($setting['limit'])) {
            $setting['limit'] = 25;
        }

        if (isset($setting['module_categories']) && $setting['module_categories']) {
            $filter_data = [
                'filter_category_id' 	=> $setting['module_categories'],
                'filter_sub_category' 	=> true,
                'sort'               	=> (isset($setting['sort']) && !empty($setting['sort'])) ? $setting['sort'] : 'pd.name',
				'order'              	=> (isset($setting['order']) && !empty($setting['order'])) ? $setting['order'] : 'ASC',
				'quantity_view'         => (isset($setting['quantity_view']) && !empty($setting['quantity_view'])) ? false : true,
                'start'					=> 0,
                'end'					=> (int)$setting['limit']
            ];

			if (isset($setting['module_show_in_categories']) && $setting['module_show_in_categories']) {
	            if (in_array($category_id, $setting['module_show_in_categories'])) {
	                $products = $this->model_octemplates_module_oct_products_from_category->getProducts($filter_data);
	            }
	        } else {
	            $products = $this->model_octemplates_module_oct_products_from_category->getProducts($filter_data);
	        }
        }

        $data['products'] = [];

        if (!empty($products)) {
            foreach ($products as $product_id) {
                $product_info = $this->model_catalog_product->getProduct($product_id);

                if ($product_info) {
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

					$data['products'][] = [
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
						'name'        => $product_info['name'],
						'description' => utf8_substr(trim(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'minimum'     => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					];
                }
            }
        }

        $data['module'] = $module++;

        if ($data['products']) {
            return $this->load->view('octemplates/module/oct_products_from_category', $data);
        }
    }
}