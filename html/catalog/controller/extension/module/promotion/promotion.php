<?php

class ControllerExtensionModulePromotionPromotion extends Controller {

	public function index() {

		$this->load->model('extension/module/promotion');

		$options = $this->model_extension_module_promotion->getOptions();

		$data = $this->model_extension_module_promotion->getLanguage();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_promotions'),
			'href' => $this->url->link('extension/module/promotion/category')
		);

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $options['promotion']['products'];
		}

		if (isset($this->request->get['promotion_id'])) {
			$promotion_id = (int) $this->request->get['promotion_id'];
		} else {
			$promotion_id = 0;
		}

		$filter_data = array(
			'promotion_id' => $promotion_id,
			'get_products' => true,
			'product' => array(
				'start' => ($page - 1) * $limit,
				'limit' => $limit
			)
		);

		$promotion_info = $this->model_extension_module_promotion->getPromotion($filter_data);

		if (!empty($promotion_info['promotion_id'])) {

			$data['breadcrumbs'][] = array(
				'text' => $promotion_info['name'],
				'href' => $this->url->link('extension/module/promotion/promotion', 'promotion_id=' . $this->request->get['promotion_id'])
			);

			$this->document->setTitle($promotion_info['name']);
      $this->document->setDescription(strip_tags($promotion_info['description']));
			$this->document->addLink($this->url->link('extension/module/promotion/promotion', 'promotion_id=' . $this->request->get['promotion_id']), 'canonical');

			$data['heading_title'] = $promotion_info['name'];

			$data['promotion_id'] = (int) $this->request->get['promotion_id'];

			if ($promotion_info['image_promotion']) {
				$promotion_info['thumb'] = $this->model_extension_module_promotion->getThumb($promotion_info['image_promotion'], 'promotion');
			} elseif ($promotion_info['image'] && !empty($options['promotion']['main_image'])) {
				$promotion_info['thumb'] = $this->model_extension_module_promotion->getThumb($promotion_info['image'], 'promotion');
			} else {
				$promotion_info['thumb'] = "";
			}

			$promotion_info['href'] = $this->url->link('extension/module/promotion/promotion', 'promotion_id=' . $promotion_info['promotion_id']);

			if ($promotion_info['finished']) {
				$promotion_info['name'] = $this->language->get('promotion_finished');
			}

			$products_data = $data;
			$products_data['heading_title'] = $data['text_products_title'];

			if ($promotion_info['finished']) {
				$promotion_info['products'] = $promotion_info['products_pagination'] = '';
			} else {
				if (!empty($promotion_info['products']) && !$promotion_info['finished']) {
					foreach ($promotion_info['products'] as $result) {
            
            if (empty($result['product_id'])) {
              continue;
            }

						$image = $this->model_extension_module_promotion->getThumb($result['image'], 'promotion_product');

						if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$price = false;
						}

						if ((float) $result['special']) {
							$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$special = false;
						}

						if ($this->config->get('config_tax')) {
							$tax = $this->currency->format((float) $result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
						} else {
							$tax = false;
						}

						if ($this->config->get('config_review_status')) {
							$rating = $result['rating'];
						} else {
							$rating = false;
						}

						$products_data['products'][] = array(
							'product_id' => $result['product_id'],
							'thumb' => $image,
							'name' => $result['name'],
							'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
							'price' => $price,
							'special' => $special,
							'tax' => $tax,
							'rating' => $rating,
							'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
						);
					}
				}

				if (!empty($products_data['products'])) {
					$promotion_info['products'] = $this->model_extension_module_promotion->getView('products', $products_data);
				}

				$url = '';

				if (isset($this->request->get['limit'])) {
					$url .= '&limit=' . $this->request->get['limit'];
				}

				$pagination = new Pagination();
				$pagination->total = $promotion_info['products_total'];
				$pagination->page = $page;
				$pagination->limit = $limit;
				$pagination->url = $this->url->link('extension/module/promotion/promotion', 'promotion_id=' . $promotion_info['promotion_id'] . $url . '&page={page}#products');

				$promotion_info['products_pagination'] = $pagination->render();
			}

			$data['promotion'] = $promotion_info;

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->model_extension_module_promotion->getView('promotion', $data));
		} else {

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('extension/module/promotion/promotion', 'promotion_id=' . $promotion_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('extension/module/promotion/category');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->model_extension_module_promotion->getView('not_found', $data, 'error'));
		}
	}

}
