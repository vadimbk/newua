<?php
/*
@author    Dmitriy Kubarev
@link    http://www.simpleopencart.com
@link    http://www.opencart.com/index.php?route=extension/extension/info&extension_id=4811
*/

include_once(DIR_SYSTEM . 'library/simple/simple_controller.php');

class ControllerCheckoutSimpleCheckoutCart extends SimpleController {
    static $error = array();
    static $updated = false;

    private $_templateData = array();

    private function init() {
		

		
        $this->loadLibrary('simple/simplecheckout');
		

        $this->simplecheckout = SimpleCheckout::getInstance($this->registry);

        $this->language->load('checkout/cart');
        $this->language->load('checkout/simplecheckout');

        $get_route = isset($_GET['route']) ? $_GET['route'] : (isset($_GET['_route_']) ? $_GET['_route_'] : '');

        if ($get_route == 'checkout/simplecheckout_cart') {
            $this->simplecheckout->init('customer');
        }
    }

    public function index() {
		
		/*if ((isset($_POST['payment_method']))&&($_POST['payment_method']=='privat_bank')) {
$data['peremenaya'] = true;
} else {
$data['peremenaya'] = false;
}*/
		
		
        if (!self::$updated) {
            $this->update();
        }

        $this->init();

        $version = $this->simplecheckout->getOpencartVersion();

        $this->_templateData['attention'] = '';

        if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
            $this->_templateData['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/simpleregister'));
            $this->simplecheckout->addError('cart');
            $this->simplecheckout->blockOrder();
        }

        $this->_templateData['error_warning'] = '';

        if (isset(self::$error['warning'])) {
            $this->_templateData['error_warning'] = self::$error['warning'];
        }

        if (!$this->cart->hasStock()) {
            if ($this->config->get('config_stock_warning')) {
                $this->_templateData['error_warning'] = $this->language->get('error_stock');
            }
            if (!$this->config->get('config_stock_checkout')) {
                $this->_templateData['error_warning'] = $this->language->get('error_stock');
                $this->simplecheckout->addError('cart');
                $this->simplecheckout->blockOrder();
            }
        }

        $customerGroupId = isset($this->session->data['simple']) && isset($this->session->data['simple']['customer']) && isset($this->session->data['simple']['customer']['customer_group_id']) ? $this->session->data['simple']['customer']['customer_group_id'] : $this->config->get('config_customer_group_id');

        $useTotal    = $this->simplecheckout->getSettingValue('useTotal', 'cart');

        $tmp = $this->simplecheckout->getSettingValue('minAmount', 'cart');
        $minAmount = !empty($tmp[$customerGroupId]) ? $tmp[$customerGroupId] : 0;

        $tmp = $this->simplecheckout->getSettingValue('maxAmount', 'cart');
        $maxAmount = !empty($tmp[$customerGroupId]) ? $tmp[$customerGroupId] : 0;

        $tmp = $this->simplecheckout->getSettingValue('minQuantity', 'cart');
        $minQuantity = !empty($tmp[$customerGroupId]) ? $tmp[$customerGroupId] : 0;

        $tmp = $this->simplecheckout->getSettingValue('maxQuantity', 'cart');
        $maxQuantity = !empty($tmp[$customerGroupId]) ? $tmp[$customerGroupId] : 0;

        $tmp = $this->simplecheckout->getSettingValue('minWeight', 'cart');
        $minWeight = !empty($tmp[$customerGroupId]) ? $tmp[$customerGroupId] : 0;

        $tmp = $this->simplecheckout->getSettingValue('maxWeight', 'cart');
        $maxWeight = !empty($tmp[$customerGroupId]) ? $tmp[$customerGroupId] : 0;

        $cartSubtotal = 0;

        if (!empty($minAmount) || !empty($maxAmount)) {
            if ($useTotal) {
                $cartSubtotal = $this->cart->getTotal();
            } else {
                $cartSubtotal = $this->cart->getSubTotal();
            }
        }

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $key => $voucher) {
                $cartSubtotal += $voucher['amount'];
            }
        }

        $cartQuantity = $this->cart->countProducts();
        $cartWeight = $this->cart->getWeight();

        $this->_templateData['quantity'] = $cartQuantity;

        if (!empty($minAmount) && $minAmount > $cartSubtotal) {
            $this->simplecheckout->addError('cart');
            $this->simplecheckout->blockOrder();
            $this->_templateData['error_warning'] = sprintf($this->language->get('error_min_amount'),$this->simplecheckout->formatCurrency($minAmount));
        }

        if (!empty($maxAmount) && $maxAmount < $cartSubtotal) {
            $this->simplecheckout->blockOrder();
            $this->simplecheckout->addError('cart');
            $this->_templateData['error_warning'] = sprintf($this->language->get('error_max_amount'),$this->simplecheckout->formatCurrency($maxAmount));
        }

        if (!empty($minQuantity) && $minQuantity > $cartQuantity) {
            $this->simplecheckout->blockOrder();
            $this->simplecheckout->addError('cart');
            $this->_templateData['error_warning'] = sprintf($this->language->get('error_min_quantity'), $minQuantity);
        }

        if (!empty($maxQuantity) && $maxQuantity < $cartQuantity) {
            $this->simplecheckout->addError('cart');
            $this->simplecheckout->blockOrder();
            $this->_templateData['error_warning'] = sprintf($this->language->get('error_max_quantity'), $maxQuantity);
        }

        if (!empty($minWeight) && !empty($cartWeight) && $minWeight > $cartWeight) {
            $this->simplecheckout->blockOrder();
            $this->simplecheckout->addError('cart');
            $this->_templateData['error_warning'] = sprintf($this->language->get('error_min_weight'), $minWeight);
        }

        if (!empty($maxWeight) && !empty($cartWeight) && $maxWeight < $cartWeight) {
            $this->simplecheckout->addError('cart');
            $this->simplecheckout->blockOrder();
            $this->_templateData['error_warning'] = sprintf($this->language->get('error_max_weight'), $maxWeight);
        }

        $this->load->model('tool/image');

        if ($version >= 200) {
            $this->load->model('tool/upload');
        }

        if ($version < 210) {
            $this->loadLibrary('encryption');
        }

        $this->_templateData['column_image']         = $this->language->get('column_image');
        $this->_templateData['column_name']          = $this->language->get('column_name');
        $this->_templateData['column_model']         = $this->language->get('column_model');
        $this->_templateData['column_quantity']      = $this->language->get('column_quantity');
        $this->_templateData['column_price']         = $this->language->get('column_price');
        $this->_templateData['column_total']         = $this->language->get('column_total');
        $this->_templateData['text_until_cancelled'] = $this->language->get('text_until_cancelled');
        $this->_templateData['text_freq_day']        = $this->language->get('text_freq_day');
        $this->_templateData['text_freq_week']       = $this->language->get('text_freq_week');
        $this->_templateData['text_freq_month']      = $this->language->get('text_freq_month');
        $this->_templateData['text_freq_bi_month']   = $this->language->get('text_freq_bi_month');
        $this->_templateData['text_freq_year']       = $this->language->get('text_freq_year');
        $this->_templateData['text_trial']           = $this->language->get('text_trial');
        $this->_templateData['text_recurring']       = $this->language->get('text_recurring');
        $this->_templateData['text_length']          = $this->language->get('text_length');
        $this->_templateData['text_recurring_item']  = $this->language->get('text_recurring_item');
        $this->_templateData['text_payment_profile'] = $this->language->get('text_payment_profile');
        $this->_templateData['text_cart']            = $this->language->get('text_cart');

        $this->_templateData['button_update'] = $this->language->get('button_update');
        $this->_templateData['button_remove'] = $this->language->get('button_remove');

        $this->_templateData['products'] = array();

        $this->_templateData['config_stock_warning'] = $this->config->get('config_stock_warning');
        $this->_templateData['config_stock_checkout'] = $this->config->get('config_stock_checkout');

        $products = $this->cart->getProducts();

        $points_total = 0;

        foreach ($products as $product) {

            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                $this->_templateData['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
                $this->simplecheckout->addError('cart');
                $this->simplecheckout->blockOrder();
            }

            $option_data = array();

            foreach ($product['option'] as $option) {
                if ($version >= 200) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                        if ($upload_info) {
                            $value = $upload_info['name'];
                        } else {
                            $value = '';
                        }
                    }
                } else {
                    if ($option['type'] != 'file') {
                        $value = $option['option_value'];
                    } else {
                        $encryption = new Encryption($this->config->get('config_encryption'));
                        $option_value = $encryption->decrypt($option['option_value']);
                        $filename = substr($option_value, 0, strrpos($option_value, '.'));
                        $value = $filename;
                    }
                }

                $option_data[] = array(
                    'name'  => $option['name'],
                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                );
            }

            if ($product['image']) {
                if ($version < 220) {
                    $image_cart_width = $this->config->get('config_image_cart_width');
                    $image_cart_width = $image_cart_width ? $image_cart_width : 40;
                    $image_cart_height = $this->config->get('config_image_cart_height');
                    $image_cart_height = $image_cart_height ? $image_cart_height : 40;
                } elseif ($version < 300) {
                    $image_cart_width = $this->config->get($this->config->get('config_theme') . '_image_cart_width');
                    $image_cart_width = $image_cart_width ? $image_cart_width : 40;
                    $image_cart_height = $this->config->get($this->config->get('config_theme') . '_image_cart_height');
                    $image_cart_height = $image_cart_height ? $image_cart_height : 40;
                } else {
                    $image_cart_width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width');
                    $image_cart_width = $image_cart_width ? $image_cart_width : 40;
                    $image_cart_height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height');
                    $image_cart_height = $image_cart_height ? $image_cart_height : 40;
                }
                
                $image = $this->model_tool_image->resize($product['image'], $image_cart_width, $image_cart_height);
            } else {
                $image = '';
            }

            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $price = $this->simplecheckout->formatCurrency($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
            } else {
                $price = false;
            }

            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $total = $this->simplecheckout->formatCurrency($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity']);
            } else {
                $total = false;
            }

            if ($version >= 200) {
                $recurring = '';

                if ($product['recurring']) {
                    $frequencies = array(
                        'day'        => $this->language->get('text_day'),
                        'week'       => $this->language->get('text_week'),
                        'semi_month' => $this->language->get('text_semi_month'),
                        'month'      => $this->language->get('text_month'),
                        'year'       => $this->language->get('text_year'),
                    );

                    if ($product['recurring']['trial']) {
                        $recurring = sprintf($this->language->get('text_trial_description'), $this->simplecheckout->formatCurrency($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax'))), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
                    }

                    if ($product['recurring']['duration']) {
                        $recurring .= sprintf($this->language->get('text_payment_description'), $this->simplecheckout->formatCurrency($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax'))), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                    } else {
                        $recurring .= sprintf($this->language->get('text_payment_until_canceled_description'), $this->simplecheckout->formatCurrency($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax'))), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                    }
                }

                $this->_templateData['products'][] = array(
                    'key'       => isset($product['key']) ? $product['key'] : '',
                    'cart_id'   => isset($product['cart_id']) ? $product['cart_id'] : '',
                    'thumb'     => $image,
                    'name'      => $product['name'],
					'product_id'     => $product['product_id'],
                    'model'     => $product['model'],
                    'minimum'   => $product['minimum'],
                    'option'    => $option_data,
                    'recurring' => $recurring,
                    'quantity'  => $product['quantity'],
                    'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                    'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
                    'price'     => $price,
                    'prices'     =>round($product['price']*45),
                    
                    'total'     => $total,
                    'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
                );
            } elseif ($version >= 156) {
                $profile_description = '';

                if ($product['recurring']) {
                    $frequencies = array(
                        'day'        => $this->language->get('text_day'),
                        'week'       => $this->language->get('text_week'),
                        'semi_month' => $this->language->get('text_semi_month'),
                        'month'      => $this->language->get('text_month'),
                        'year'       => $this->language->get('text_year'),
                    );

                    if ($product['recurring_trial']) {
                        $recurring_price = $this->simplecheckout->formatCurrency($this->tax->calculate($product['recurring_trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')));
                        $profile_description = sprintf($this->language->get('text_trial_description'), $recurring_price, $product['recurring_trial_cycle'], $frequencies[$product['recurring_trial_frequency']], $product['recurring_trial_duration']) . ' ';
                    }

                    $recurring_price = $this->simplecheckout->formatCurrency($this->tax->calculate($product['recurring_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')));

                    if ($product['recurring_duration']) {
                        $profile_description .= sprintf($this->language->get('text_payment_description'), $recurring_price, $product['recurring_cycle'], $frequencies[$product['recurring_frequency']], $product['recurring_duration']);
                    } else {
                        $profile_description .= sprintf($this->language->get('text_payment_until_canceled_description'), $recurring_price, $product['recurring_cycle'], $frequencies[$product['recurring_frequency']], $product['recurring_duration']);
                    }
                }

                $this->_templateData['products'][] = array(
                    'key'                 => $product['key'],
                    'thumb'               => $image,
                    'name'                => $product['name'],
					'product_id'     => $product['product_id'],
                    'model'               => $product['model'],
                    'option'              => $option_data,
                    'quantity'            => $product['quantity'],
                    'stock'               => $product['stock'],
                    'reward'              => ($product['reward'] ? sprintf($this->language->get('text_reward'), $product['reward']) : ''),
                    'price'               => $price,
                    'total'               => $total,
                    'href'                => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                    'recurring'           => $product['recurring'],
                    'profile_name'        => isset($product['profile_name']) ? $product['profile_name'] : '',
                    'profile_description' => $profile_description,
                );
            } else {
                $this->_templateData['products'][] = array(
                    'key'      => $product['key'],
                    'thumb'    => $image,
                    'name'     => $product['name'],
					'product_id'     => $product['product_id'],
                    'model'    => $product['model'],
                    'option'   => $option_data,
                    'quantity' => $product['quantity'],
                    'stock'    => $product['stock'],
                    'reward'   => ($product['reward'] ? sprintf($this->language->get('text_reward'), $product['reward']) : ''),
                    'price'    => $price,
                    'total'    => $total,
                    'href'     => $this->url->link('product/product', 'product_id=' . $product['product_id'])
                );
            }

            if ($product['points']) {
                $points_total += $product['points'];
            }
        }	

        // Gift Voucher
        $this->_templateData['vouchers'] = array();

	// remarketing all in one
	$this->load->model('tool/remarketing');
	if ($this->config->get('remarketing_status') && !$this->model_tool_remarketing->isBot()) {
	$cart_total = $this->cart->getTotal();
	$this->load->model('catalog/product');
	$facebook_currency = $this->config->get('remarketing_facebook_currency');
	$ecommerce_currency = $this->config->get('remarketing_ecommerce_currency');
	$tiktok_currency = $this->config->get('remarketing_tiktok_currency');
	$facebook_totalvalue = $this->currency->format($cart_total, $facebook_currency, '', false); 
	$tiktok_totalvalue = $this->currency->format($cart_total, $tiktok_currency, '', false); 
	$ga4_totalvalue = $this->currency->format($cart_total, $ecommerce_currency, '', false); 
	$facebook_id = $this->config->get('remarketing_facebook_id') == 'id' ? 'product_id' : 'model';
	$tiktok_id = $this->config->get('remarketing_tiktok_id') == 'id' ? 'product_id' : 'model';
			
	$data['facebook_ids'] = [];
	$data['tiktok_ids'] = [];
	foreach ($products as $product) {
		$data['facebook_ids'][] = $product;
		$data['tiktok_ids'][] = $product;
	} 
	$this->_templateData['facebook_output'] = '';
	$this->_templateData['gtag_output'] = '';
	$this->_templateData['ga4_output'] = '';
	$this->_templateData['tiktok_output'] = '';
	if ($this->config->get('remarketing_facebook_status') && $this->config->get('remarketing_facebook_identifier') && $this->config->get('remarketing_facebook_pixel_status')) {
		if (!empty($data['facebook_ids'])) {
			$this->_templateData['facebook_output'] .= "<script>" . "\n";
			$this->_templateData['facebook_output'] .= "facebook_payment_data = {" . "\n";	
			$this->_templateData['facebook_output'] .= "content_type: 'product'," . "\n";
			$num_items = 0;
			foreach ($data['facebook_ids'] as $product) {
				$num_items += $product['quantity'];
			}
			$this->_templateData['facebook_output'] .= "num_items: " . $num_items . "," . "\n";
			$this->_templateData['facebook_output'] .= "contents: [" . "\n";
			foreach ($data['facebook_ids'] as $product) {
				$this->_templateData['facebook_output'] .= "{" . "'id': '" . $product[$facebook_id] . "', 'price': " . $this->currency->format($product['price'], $facebook_currency, '', false) . ", 'quantity': " . $product['quantity'] . "},";
			}
			$this->_templateData['facebook_output'] = rtrim($this->_templateData['facebook_output'], ',');
			$this->_templateData['facebook_output'] .= "],\n";
			$this->_templateData['facebook_output'] .= 'value: ' . $facebook_totalvalue . ',' . "\n";
			$this->_templateData['facebook_output'] .= "currency: '" .  $facebook_currency . "'" . "\n";
			$this->_templateData['facebook_output'] .= "}</script>";
		}
	}
	
	if ($this->config->get('remarketing_tiktok_status') && $this->config->get('remarketing_tiktok_identifier') && $this->config->get('remarketing_tiktok_pixel_status')) {
		if (!empty($data['tiktok_ids'])) {
			$this->_templateData['tiktok_output'] .= "<script>" . "\n";
			$this->_templateData['tiktok_output'] .= "tiktok_payment_data = {" . "\n";	
			$this->_templateData['tiktok_output'] .= "content_type: 'product'," . "\n";
			$this->_templateData['tiktok_output'] .= "contents: [" . "\n";
			foreach ($data['facebook_ids'] as $product) {
				$this->_templateData['tiktok_output'] .= "{" . "'content_id': '" . $product[$tiktok_id] . "', 'price': " . $this->currency->format($product['price'], $tiktok_currency, '', false) . ", 'quantity': " . $product['quantity'] . ", 'content_type': 'product'},";
			}
			$this->_templateData['tiktok_output'] = rtrim($this->_templateData['tiktok_output'], ',');
			$this->_templateData['tiktok_output'] .= "],\n";
			$this->_templateData['tiktok_output'] .= 'value: ' . $tiktok_totalvalue . ',' . "\n";
			$this->_templateData['tiktok_output'] .= "currency: '" .  $tiktok_currency . "'" . "\n";
			$this->_templateData['tiktok_output'] .= "}</script>";
		}
	}
	
	if ($this->config->get('remarketing_ecommerce_ga4_status')) {
		$ga4_event = [];
		$ga4_event['currency'] = $ecommerce_currency;
		$ga4_event['send_to'] = $this->config->get('remarketing_ecommerce_ga4_identifier');
		$ga4_event['value'] = $ga4_totalvalue;
		if (!empty($this->session->data['coupon'])) $ga4_event['coupon'] = $this->session->data['coupon'];
		if (!empty($this->session->data['payment_method']['title'])) $ga4_event['payment_type'] = $this->session->data['payment_method']['title'];
		$list_products = [];
		foreach ($products as $product) {
			$product_info = $this->model_catalog_product->getProduct($product['product_id']);
			$ga4_categories = $this->model_tool_remarketing->getRemarketingCategoriesGa4($product['product_id']); 
			
			$ga4_product = [
				'item_id' => $this->config->get('remarketing_ecommerce_ga4_id') == 'id' ? $product_info['product_id'] : $product_info['model'],
				'item_name' => addslashes($product['name']),
				'quantity' => $product['quantity'],
				'price' => $this->currency->format($product['price'], $ecommerce_currency, '', false),
				'currency' => $ecommerce_currency
			];
			
			if (!empty($ga4_categories[0])) $ga4_product['item_category'] = $ga4_categories[0];
			if (!empty($ga4_categories[1])) $ga4_product['item_category2'] = $ga4_categories[1];
			if (!empty($ga4_categories[2])) $ga4_product['item_category3'] = $ga4_categories[2];
			if (!empty($ga4_categories[3])) $ga4_product['item_category4'] = $ga4_categories[3];
			if (!empty($product_info['manufacturer'])) $ga4_product['item_brand'] = $product_info['manufacturer'];
			$list_products[] = $ga4_product;
		}
		
		$ga4_event['items'] = $list_products; 
		
		if (!empty($ga4_event)) {
			$this->_templateData['ga4_output'] .= "<script>" . "\n";
			$this->_templateData['ga4_output'] .= "ga4_payment_data = " . json_encode($ga4_event) . ";\n";	
			$this->_templateData['ga4_output'] .= "</script>";
		}
	}
} 
	

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $key => $voucher) {
                $this->_templateData['vouchers'][] = array(
                    'key'         => $key,
                    'description' => $voucher['description'],
                    'amount'      => $this->simplecheckout->formatCurrency($voucher['amount'])
                );
            }
        }

        $totals = array();
        $total = 0;
        $taxes = $this->cart->getTaxes();
		

		

        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );


		


        $this->_templateData['modules'] = array();

        if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
            $sort_order = array();

            if ($version < 200 || $version >= 300) {
                $this->load->model('setting/extension');

                $results = $this->model_setting_extension->getExtensions('total');
            } else {
                $this->load->model('extension/extension');

                $results = $this->model_extension_extension->getExtensions('total');
            }

            foreach ($results as $key => $result) {
                if ($version < 300) {
                    $sort_order[$key] = $this->config->get($result['code'] . '_sort_order');
                } else {
                    $sort_order[$key] = $this->config->get('total_' . $result['code'] . '_sort_order');
                }                
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($version < 300) {
                    $status = $this->config->get($result['code'] . '_status');
                } else {
                    $status = $this->config->get('total_' . $result['code'] . '_status');
                }

                if ($status) {
                    $this->simplecheckout->loadModel('total/' . $result['code']);

                    if ($version < 220) {
                        $this->{'model_total_' . $result['code']}->getTotal($totals, $total, $taxes);
                    } else {
                        $this->{'model_total_' . $result['code']}->getTotal($total_data);
                    }

                    $this->_templateData['modules'][$result['code']] = true;
                }
            }

            $sort_order = array();

            foreach ($totals as $key => $value) {
                $sort_order[$key] = $value['sort_order'];

                if (!isset($value['text'])) {
                    $totals[$key]['text'] = $this->simplecheckout->formatCurrency($value['value']);
                }
				
				
				
				
            }

            array_multisort($sort_order, SORT_ASC, $totals);
        }

        $this->_templateData['totals'] = $totals;
		
	
		
		

        $this->_templateData['entry_coupon'] = $this->language->get('entry_coupon');
        $this->_templateData['entry_voucher'] = $this->language->get('entry_voucher');

        $points = $this->customer->getRewardPoints();
        $points_to_use = $points > $points_total ? $points_total : $points;
        $this->_templateData['points'] = $points_to_use;

        $this->_templateData['entry_reward'] = sprintf($this->language->get('entry_reward'), $points_to_use);

        $this->_templateData['reward']  = isset($this->session->data['reward']) ? $this->session->data['reward'] : '';
        $this->_templateData['voucher'] = isset($this->session->data['voucher']) ? $this->session->data['voucher'] : '';
        $this->_templateData['coupon']  = isset($this->session->data['coupon']) ? $this->session->data['coupon'] : '';

        $this->_templateData['display_weight'] = $this->simplecheckout->displayWeight();

        if ($this->_templateData['display_weight']) {
            $this->_templateData['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
        }

        $this->_templateData['additional_path'] = $this->simplecheckout->getAdditionalPath();
        $this->_templateData['hide'] = $this->simplecheckout->isBlockHidden('cart');

        $currentTheme = $this->config->get('config_template');

        if ($currentTheme == 'shoppica' || $currentTheme == 'shoppica2') {
            $this->_templateData['cart_total'] = $this->simplecheckout->formatCurrency($total);
        } else {
            $minicart = $this->simplecheckout->getSettingValue('minicartText', 'cart');
            
            $text_items = '';
            $language_code = $this->simplecheckout->getCurrentLanguageCode();

            if ($minicart && !empty($minicart[$language_code])) {
                $text_items = $minicart[$language_code];
            }

            if (!$text_items) {
                $this->language->load('checkout/cart');
                $text_items = $this->language->get('text_items');
                $this->language->load('checkout/simplecheckout');
            }

            if (strpos($text_items, '{quantity}') !== false || strpos($text_items, '{total}') !== false) {
                $find = array(
                    '{quantity}', 
                    '{total}'
                );

                $replace = array(
                    '{quantity}' => $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), 
                    '{total}' => $this->simplecheckout->formatCurrency($total)
                );

                $this->_templateData['cart_total'] = str_replace($find, $replace, $text_items);
            } else {
                $this->_templateData['cart_total'] = sprintf($text_items, $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->simplecheckout->formatCurrency($total));
            } 
        }

        $this->_templateData['display_header']           = $this->simplecheckout->getSettingValue('displayHeader', 'cart');
        $this->_templateData['display_model']            = $this->simplecheckout->getSettingValue('displayModel', 'cart');
        $this->_templateData['quantity_step_as_minimum'] = $this->simplecheckout->getSettingValue('quantityStepAsMinimum', 'cart');
        $this->_templateData['has_error']                = $this->simplecheckout->hasError('cart');

        $this->setOutputContent($this->renderPage('checkout/simplecheckout_cart', $this->_templateData));
    }

    public function update() {
        self::$updated = true;

        $this->init();

        /*if (!isset($this->session->data['vouchers'])) {
            $this->session->data['vouchers'] = array();
        }*/

        // Update
        if (!empty($this->request->post['quantity'])) {
            //$keys =  isset($this->session->data['cart']) ? $this->session->data['cart'] : array();
            foreach ($this->request->post['quantity'] as $key => $value) {
                //if (!empty($keys) && array_key_exists($key, $keys)) {
                    $this->cart->update($key, $value);
                //}
            }
        }

        // Remove
        if (!empty($this->request->post['remove'])) {
            $this->cart->remove($this->request->post['remove']);
            unset($this->session->data['vouchers'][$this->request->post['remove']]);
        }

        // Coupon
        if (isset($this->request->post['coupon']) && $this->validateCoupon()) {			
            $this->session->data['coupon'] = trim($this->request->post['coupon']);
            if ($this->session->data['coupon'] == '') {
                unset($this->session->data['coupon']);
            }
        }
		
		$data['coupon_value']=trim($this->session->data['coupon']);
		//echo $data['coupon_value'];

        // Voucher
        if (isset($this->request->post['voucher']) && $this->validateVoucher()) {
            $this->session->data['voucher'] = trim($this->request->post['voucher']);
            if ($this->session->data['voucher'] == '') {
                unset($this->session->data['voucher']);
            }
        }

        if (!empty($this->request->post['quantity']) || !empty($this->request->post['remove']) || !empty($this->request->post['voucher'])) {
            unset($this->session->data['reward']);
        }

        // Reward
        if (isset($this->request->post['reward']) && $this->validateReward()) {
            $this->session->data['reward'] = $this->request->post['reward'];
        }
    }

    private function validateCoupon() {
        $version = $this->simplecheckout->getOpencartVersion();

        if ($version < 210) {
            $this->load->model('checkout/coupon');
        } else {
            $this->simplecheckout->loadModel('total/coupon');
        }

        $error = false;

        if (!empty($this->request->post['coupon'])) {
            if ($version < 210) {
                $coupon_info = $this->model_checkout_coupon->getCoupon($this->request->post['coupon']);
            } else {
                $coupon_info = $this->model_total_coupon->getCoupon($this->request->post['coupon']);
            }

            if (!$coupon_info) {
                self::$error['warning'] = $this->language->get('error_coupon');
                $error = true;
            }
        }

        return !$error;
    }

    private function validateVoucher() {
        $version = $this->simplecheckout->getOpencartVersion();

        if ($version < 210) {
            $this->load->model('checkout/voucher');
        } else {
            $this->simplecheckout->loadModel('total/voucher');
        }

        $error = false;

        if (!empty($this->request->post['voucher'])) {
            if ($version < 210) {
                $voucher_info = $this->model_checkout_voucher->getVoucher($this->request->post['voucher']);
            } else {
                $voucher_info = $this->model_total_voucher->getVoucher($this->request->post['voucher']);
            }

            if (!$voucher_info) {
                self::$error['warning'] = $this->language->get('error_voucher');
                $error = true;
            }
        }

        return !$error;
    }

    private function validateReward() {
        $error = false;

        if (!empty($this->request->post['reward'])) {
            $points = $this->customer->getRewardPoints();

            $points_total = 0;

            foreach ($this->cart->getProducts() as $product) {
                if ($product['points']) {
                    $points_total += $product['points'];
                }
            }

            if ($this->request->post['reward'] > $points) {
                self::$error['warning'] = sprintf($this->language->get('error_points'), $this->request->post['reward']);
                $error = true;
            }

            if ($this->request->post['reward'] > $points_total) {
                self::$error['warning'] = sprintf($this->language->get('error_maximum'), $points_total);
                $error = true;
            }
        } else {
            $error = true;
        }

        return !$error;
    }
}
?>