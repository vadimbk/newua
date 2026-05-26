<?php
class ControllerExtensionModuleCheapest extends Controller {
	// Brand domain for the price-table heading (single source of truth).
	const BRAND_DOMAIN = 'radio-shop.com.ua';

	public function index($setting) {
		$this->load->language('extension/module/cheapest');

		$data = array();

		$this->load->model('catalog/category');
		$this->load->model('catalog/product');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
			$category_id = (int)array_pop($parts);
		} else {
			$category_id = 0;
		}

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {
			if ($category_info['meta_h1']) {
				$data['heading_title'] = $category_info['meta_h1'];
			} else {
				$data['heading_title'] = $category_info['name'];
			}

			$data['brand_domain'] = self::BRAND_DOMAIN;

			$data['products'] = array();

			$filter_data = array(
				'filter_category_id' => $category_id,
				'sort'               => 'p.price',
				'order'              => 'ASC',
				'start'              => 0,
				'limit'              => $setting['limit']
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			if ($results) {
				foreach ($results as $result) {
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

					$data['products'][] = array(
						'product_id' => $result['product_id'],
						'name'       => $result['name'],
						'price'      => $price,
						'special'    => $special,
						'href'       => $this->url->link('product/product', 'product_id=' . $result['product_id'])
					);
				}

				$data['prices_tabl'] = 2;
			}
		}

		return $this->load->view('extension/module/cheapest', $data);
	}
}
