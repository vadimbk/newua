<?php
class ControllerCheckoutShippingMethod extends Controller {
	public function index() {
		$this->load->language('checkout/checkout');

		if (isset($this->session->data['shipping_address'])) {
			// Shipping Methods
			$method_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('shipping');

			foreach ($results as $result) {
				if ($this->config->get('shipping_' . $result['code'] . '_status')) {
					$this->load->model('extension/shipping/' . $result['code']);

					$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

					if ($quote) {

            			/** EET Module */
						if ($this->config->get('module_ee_tracking_status') && $this->config->get('module_ee_tracking_checkout_status') && $this->config->get('module_ee_tracking_advanced_settings') && $this->config->get('module_ee_tracking_language_id') && isset($this->session->data['language'])) {
							$this->load->model('localisation/language');
							$ee_languages = $this->model_localisation_language->getLanguages();
							$ee_start_language = $this->session->data['language'];
							foreach ($ee_languages as $ee_item) {
								if ($ee_item['language_id'] == $this->config->get('module_ee_tracking_language_id') && $ee_start_language != $ee_item['code']) {
									$ee_language = new Language($ee_item['code']);
									$this->registry->set('language', $ee_language);
									$ee_quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);
									foreach ($quote['quote'] as $key => $item2) {
										if (isset($ee_quote['quote'][$key]['title'])) {
											$quote['quote'][$key]['ee_title'] = $ee_quote['quote'][$key]['title'];
										}
									}
									$ee_language2 = new Language($ee_start_language);
									$ee_language2->load($ee_start_language);
									$ee_language2->load('checkout/checkout');
									$this->registry->set('language', $ee_language2);
								}
							}
						}
						/** EET Module */
            
						$method_data[$result['code']] = array(
							'title'      => $quote['title'],
							'quote'      => $quote['quote'],
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->session->data['shipping_methods'] = $method_data;
		}

		if (empty($this->session->data['shipping_methods'])) {
			$data['error_warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['shipping_methods'])) {
			$data['shipping_methods'] = $this->session->data['shipping_methods'];
		} else {
			$data['shipping_methods'] = array();
		}

		if (isset($this->session->data['shipping_method']['code'])) {
			$data['code'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->session->data['comment'])) {
			$data['comment'] = $this->session->data['comment'];
		} else {
			$data['comment'] = '';
		}
		

		/** EET Module */
		$data['ee_tracking'] = $this->config->get('module_ee_tracking_status');
		if ($data['ee_tracking']) {
			$data['ee_checkout'] = $this->config->get('module_ee_tracking_checkout_status');
			$data['ee_checkout_log'] = $this->config->get('module_ee_tracking_log') ? $this->config->get('module_ee_tracking_checkout_log') : false;
			$ee_data = array('step_option' => 'No shipping methods available');
			if (isset($this->session->data['shipping_method']['code'])) {
				$ee_code = $this->session->data['shipping_method']['code'];
			} else {
				$ee_code = '';
			}
			if (isset($this->session->data['shipping_methods'])) {
				foreach ($this->session->data['shipping_methods'] as $key => $shipping_method) {
					if (!$shipping_method['error']) {
						foreach ($shipping_method['quote'] as $quote) {
							if ($quote['code'] == $ee_code || !$ee_code) {
								$ee_code = $quote['code'];
								if (isset($quote['ee_title'])) {
									$ee_data['step_option'] = htmlspecialchars($quote['ee_title'], ENT_QUOTES, 'UTF-8');
								} else {
									$ee_data['step_option'] = htmlspecialchars($quote['title'], ENT_QUOTES, 'UTF-8');
								}
							}
						}
					}
				}
			}
			$ee_data['step'] = 4;
			$data['ee_data'] = json_encode($ee_data);
		}
		/** EET Module */
            
		$this->response->setOutput($this->load->view('checkout/shipping_method', $data));
	}

	public function save() {
		$this->load->language('checkout/checkout');

		$json = array();

		// Validate if shipping is required. If not the customer should not have reached this page.
		if (!$this->cart->hasShipping()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		// Validate if shipping address has been set.
		if (!isset($this->session->data['shipping_address'])) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')) || (($this->cart->getTotal() < $this->config->get('config_order_min')) && $this->cart->hasProducts() )) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');

				break;
			}
		}

		if (!isset($this->request->post['shipping_method'])) {
			$json['error']['warning'] = $this->language->get('error_shipping');
		} else {
			$shipping = explode('.', $this->request->post['shipping_method']);

			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
				$json['error']['warning'] = $this->language->get('error_shipping');
			}
		}

		if (!$json) {
			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];

			$this->session->data['comment'] = strip_tags($this->request->post['comment']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}