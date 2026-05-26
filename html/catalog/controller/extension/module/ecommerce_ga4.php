<?php
class ControllerExtensionModuleEcommerceGa4 extends Controller {

    private $eCode      = 'ecommerce_ga4';
    private $eName      = 'module_ecommerce_ga4';
    private $eModel     = 'model_extension_module_ecommerce_ga4';
    private $ePath      = 'extension/module/ecommerce_ga4';
    private $eVersion   = '1.0.5';

    public function index() {
        $json = array();
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['event']) && in_array($this->request->post['event'], array('view_item_list', 'view_search_results', 'view_promotion', 'view_item', 'view_cart', 'select_item', 'add_to_cart', 'remove_from_cart', 'cart_edit', 'add_to_wishlist', 'select_promotion', 'begin_checkout', 'add_payment_info', 'add_shipping_info', 'purchase', 'error'))) {
            $this->load->model($this->ePath);
            $json = $this->{$this->eModel}->{$this->request->post['event']}($this->request->post);
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function event_launcher($data) {
        if (isset($data[3])) {
            $position = $data[3];
        } else {
            $position = 'after';
        }
        $this->{str_replace('/', '_', $data[0]) . '_' . $position}($data[0], $data[1], $data[2]);
        return $data[2];
    }

    public function product_list($data) {
        $this->product_list_after($data[0], $data[1], $data[2]);
        return $data[2];
    }

    public function promotion_list($data) {
        $this->promotion_list_after($data[0], $data[1], $data[2]);
        return $data[2];
    }

    public function journal3($data) {
        switch ($data[0]) {
            case 'journal3/module/products':
            case 'journal3/module/side_products':
                $this->journal3_product_list_after($data[0], $data[1], $data[2]);
                break;
            case 'journal3/module/banners':
            case 'journal3/module/master_slider':
                $this->journal3_promotion_list_after($data[0], $data[1], $data[2]);
                break;
            case 'journal3/checkout/checkout':
                $this->checkout_checkout_after($data[0], $data[1], $data[2]);
                break;
            case 'journal3/products':
            case 'journal3/side_products':
                $this->journal32_product_list_after($data[0], $data[1], $data[2]);
                break;
        }
        return $data[2];
    }

    public function journal2($data) {
        $data[0] = str_replace('journal2/template/journal2', 'journal2',$data[0]);
        switch ($data[0]) {
            case 'journal2/module/side_products':
            case 'journal2/module/carousel_product':
            case 'journal2/module/custom_sections_product':
                $this->journal2_product_list_after($data[0], $data[1], $data[2]);
                break;
            case 'journal2/module/slider_simple':
            case 'journal2/module/slider_advanced':
            case 'journal2/module/static_banners':
                $this->journal2_promotion_list_after($data[0], $data[1], $data[2]);
                break;
        }
        return $data[2];
    }

    public function common_header_before(&$route, &$data) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && $settings['js_position'] == 1) {
            if ($settings['dev_mode']) {
                $data['scripts'][] = 'catalog/view/javascript/' . $this->eCode . '.js?v=0.' . time();
            } else {
                $data['scripts'][] = 'catalog/view/javascript/' . $this->eCode . '.min.js?v=' . $this->eVersion;
            }
        }
    }

    public function common_footer_before(&$route, &$data) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && $settings['js_position'] == 2) {
            if ($settings['dev_mode']) {
                $data['scripts'][] = 'catalog/view/javascript/' . $this->eCode . '.js?v=0.' . time();
            } else {
                $data['scripts'][] = 'catalog/view/javascript/' . $this->eCode . '.min.js?v=' . $this->eVersion;
            }
        }
    }

    public function common_header_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings)) {
            if ($settings['js_position'] == 0) {
                if ($settings['dev_mode']) {
                    $js_src = 'catalog/view/javascript/' . $this->eCode . '.js?v=0.' . time();
                } else {
                    $js_src = 'catalog/view/javascript/' . $this->eCode . '.min.js?v=' . $this->eVersion;
                }
                $search_setting = array(
                    'search_value' => '</head>',
                    'add_value' => '<script src="' . $js_src .'" type="text/javascript"></script>' . "\n" . '</head>',
                );
                $theme_settings = $this->themeSettings($settings);
                if (isset($theme_setting['script']['header'])) {
                    $search_setting = array_replace($search_setting, $theme_settings['script']['header']);
                    $search_setting['add_value'] = sprintf($search_setting['add_value'], $js_src);
                }
                $this->apply($output, $search_setting);
            }
            if ($settings['type']) {
                $user_id_added = 0;
                if ($settings['user_id']) {
                    $user_id = $this->{$this->eModel}->getUserId($settings);
                    if ($settings['type'] == 1) {
                        $search_setting = array(
                            'add_value' => 'gtag(\'config\', \'' . $settings['measurement_id'] . '\', { \'user_id\': \'' . $user_id . '\' });',
                            'search_value' => 'gtag(\'config\', \'' . $settings['measurement_id'] . '\');'
                        );
                    } else {
                        $search_setting = array(
                            'search_value' => '<!-- End Google Tag Manager -->',
                            'add_value' => '<!-- End Google Tag Manager -->' . "\n" . '<script>dataLayer.push({ \'user_id\': \'' . $user_id . '\' });</script>'
                        );
                    }
                    if ($user_id) {
                        $user_id_added = $this->apply($output, $search_setting);
                    }
                }

                if ($this->customer->isLogged() && isset($settings['custom_definition']) && in_array(0, array_column($settings['custom_definition'], 'object'))) {
                    $customer_info = $this->{$this->eModel}->getCustomer($this->customer->getId());
                    $custom_definition_html = '';
                    foreach ($settings['custom_definition'] as $custom_definition) {
                        if ($custom_definition['object'] == 0 && isset($customer_info[$custom_definition['value']])) {
                            $custom_definition_html .=  '    \'' . $custom_definition['name'] . '\': \'' . $customer_info[$custom_definition['value']] . '\',';
                        }
                    }
                    if ($settings['type'] == 1) {
                        if ($user_id_added) {
                            $search_setting = array(
                                'add_value' => 'gtag(\'config\', \'' . $settings['measurement_id'] . '\', { \'user_id\': \'' . $user_id . '\', \'user_properties\': {' . $custom_definition_html . '}});',
                                'search_value' => 'gtag(\'config\', \'' . $settings['measurement_id'] . '\', { \'user_id\': \'' . $user_id . '\' });'
                            );
                        } else {
                            $search_setting = array(
                                'add_value' => 'gtag(\'config\', \'' . $settings['measurement_id'] . '\', { \'user_properties\': {' . $custom_definition_html . '}});',
                                'search_value' => 'gtag(\'config\', \'' . $settings['measurement_id'] . '\');'
                            );
                        }

                    } else {
                        $search_setting = array(
                            'add_value' => '<!-- End Google Tag Manager -->' . "\n" . '<script>dataLayer.push({ \'user_properties\': {' . $custom_definition_html . '}});</script>',
                            'search_value' => '<!-- End Google Tag Manager -->'
                        );
                    }
                    $this->apply($output, $search_setting);
                }
            }
        }
    }

    public function common_footer_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings)) {
            $theme_settings = $this->themeSettings($settings);
            if ($settings['js_position'] == 3) {
                if ($settings['dev_mode']) {
                    $js_src = 'catalog/view/javascript/' . $this->eCode . '.js?v=0.' . time();
                } else {
                    $js_src = 'catalog/view/javascript/' . $this->eCode . '.min.js?v=' . $this->eVersion;
                }
                $search_setting = array(
                    'search_value' => '</body>',
                    'add_value' => '<script src="' . $js_src .'" type="text/javascript"></script>' . "\n" . '</body>'
                );
                if ($theme_settings && isset($theme_settings['script']['footer'])) {
                    $search_setting = array_replace($search_setting, $theme_settings['script']['footer']);
                    $search_setting['add_value'] = sprintf($search_setting['add_value'], $js_src);
                }
                $this->apply($output, $search_setting);
            }
            if ($theme_settings && isset($theme_settings['checkout_custom']) && (!isset($settings['checkout_custom']) || !$settings['checkout_custom'])) {
                $settings['checkout_custom'] = array_map('trim', explode(',', $theme_settings['checkout_custom']));
            }
            if ($settings['checkout_status'] && $settings['checkout_custom'] &&
                (isset($this->request->get['route']) && in_array((string)$this->request->get['route'], $settings['checkout_custom']))
                    || (isset($this->request->get['_route_']) && in_array((string)$this->request->get['_route_'], $settings['checkout_custom']))) {
                $this->checkout_checkout_after($route, $data, $output);
            }
        }
    }

    public function product_list_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings)) {
            $this->load->language($this->ePath);
            $base_name = basename($route);
            $list_name = '';
            $item_id = 0;
            $index = 1;
            if (strpos($route, 'module/' . $base_name) !== false) {
                /** Journal Theme Module */
                if (isset($data['title']) && in_array($base_name, array('products', 'side_products', 'custom_sections_product', 'carousel_product'))) {
                    $list_name = $data['title'];
                    if (isset($data['module_id'])) {
                        $item_id = $data['module_id'];
                    }
                }
                /** Journal Theme Module */
                $base_name = 'module_' . $base_name;
            } elseif (isset($this->request->get['page'])) {
                if (isset($this->request->get['limit'])) {
                    $limit = (int)$this->request->get['limit'];
                } else {
                    $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
                }
                $index = ((int)$this->request->get['page'] - 1) * $limit + 1;
            }
            if (isset($data['heading_title'])) {
                $heading_title = $data['heading_title'];
            } else {
                $heading_title = '';
            }
            $list_id = $base_name;
            if ($route == 'product/category' && isset($this->request->get['path'])) {
                $parts = explode('_', (string)$this->request->get['path']);
                $item_id = (int)array_pop($parts);
                if (isset($settings['extended_list']) && $settings['extended_list']) {
                    if (!$settings['view_simple'] && !$settings['active_language']) {
                        $list_name = $this->{$this->eModel}->getCategoryName($item_id, $settings['language_id']);
                    } else {
                        $list_name = $heading_title;
                    }
                    $list_id .= '_id_' . $item_id;
                }
            } elseif ($route == 'product/manufacturer_info' && isset($this->request->get['manufacturer_id'])) {
                $item_id = $this->request->get['manufacturer_id'];
                if ($settings['extended_list']) {
                    $list_name = $heading_title;
                    $list_id .= '_id_' . $item_id;
                }
            } elseif ($route == 'product/search' && isset($this->request->get['search'])) {
                $list_name = $this->language->get('text_search');
                if ($settings['extended_list']) {
                    $item_id = $this->request->get['search'];
                    $list_id = $this->request->get['search'];
                }
            }
            if (!$list_name) {
                $list_name = $this->language->get('text_' . $base_name);
                if ($list_name == 'text_' . $base_name) {
                    $list_name = str_replace('_', ' ', ucwords($base_name, '_'));
                }
            }
            if (isset($data['products'])) {
                $products = $data['products'];
            } else {
                $products = array();
            }
            $error_code = '';
            $theme_settings = $this->themeSettings($settings);
            if (!empty($theme_settings) && isset($theme_settings['list_var'][$route])) {
                $theme_var = $theme_settings['list_var'][$route];
                if (isset($theme_var['products']) && isset($data[$theme_var['products']])) {
                    $products = $data[$theme_var['products']];
                }
                foreach ($products as $key => $item) {
                    if (isset($theme_var['product_id']) && isset($item[$theme_var['product_id']])) {
                        $products[$key]['product_id'] = $item[$theme_var['product_id']];
                    } elseif (!isset($item['product_id'])) {
                        $error_code = 'product_id_not_found';
                        break;
                    }
                    if (isset($theme_var['href']) && isset($item[$theme_var['href']])) {
                        $products[$key]['href'] = $item[$theme_var['href']];
                    } elseif (!isset($item['href'])) {
                        $products[$key]['href'] = '';
                    }
                    if (isset($theme_var['manufacturer']) && isset($item[$theme_var['manufacturer']])) {
                        $products[$key]['manufacturer'] = $item[$theme_var['manufacturer']];
                    }
                }
            } elseif (isset($data['products'])) {
                if (is_array($products)) {
                    foreach ($products as $key => $item) {
                        if (!isset($item['product_id'])) {
                            $error_code = 'product_id_not_found';
                            break;
                        }
                        if (!isset($item['href'])) {
                            $products[$key]['href'] = '';
                        }
                    }
                }
            } else if ($settings['view_status'] || $settings['select_status'] || $settings['cart_status']) {
                $error_code = 'product_list_not_found';
            }
            if ($products && is_array($products) && !$error_code) {
                if ($settings['view_status'] ) {
                    $js_data = array(
                        'event' => 'view_item_list',
                        'item_list_name' => $list_name,
                        'item_list_id' => $list_id,
                        'index' => $index,
                        'items' => array()
                    );
                    if ($settings['view_simple']) { /** View in Simple Mode */
                        foreach ($products as $item) {
                            if ($item['special']) {
                                $price = preg_replace('/[^0-9' . $this->language->get('decimal_point') . ']/', '', $item['special']);
                            } else {
                                $price = preg_replace('/[^0-9' . $this->language->get('decimal_point') . ']/', '', $item['price']);
                            }
                            if (!$settings['active_tax'] && isset($item['tax']) && $item['tax'] && !$settings['tax']) {
                                $price = preg_replace('/[^0-9' . $this->language->get('decimal_point') . ']/', '', $item['tax']);
                            }
                            $price = str_replace(',', '.', $price);
                            if (!$settings['active_currency']) {
                                $price = $this->currency->convert($price, $this->session->data['currency'], $settings['currency']);
                                $price = $this->currency->format($price, $settings['currency'], 1, false);
                            }
                            if (isset($item['minimum']) && $item['minimum'] > 0) {
                                $quantity = $item['minimum'];
                            } else {
                                $quantity = 1;
                            }
                            $js_product_data = array(
                                'item_id' => $item['product_id'],
                                'item_name' => $item['name'],
                                'index' => $index++,
                                'affiliation' => $settings['affiliation'],
                                'price' => $price,
                                'currency' => $settings['currency'],
                                'quantity' => $quantity
                            );
                            if ($route == 'product/category' && isset($data['breadcrumbs'])) {
                                foreach ($data['breadcrumbs'] as $breadcrumb_key => $breadcrumb) {
                                    if (!$breadcrumb_key) {
                                        continue;
                                    } elseif ($breadcrumb_key == 1) {
                                        $js_product_data['item_category'] = $breadcrumb['text'];
                                    } else {
                                        $js_product_data['item_category_' . $breadcrumb_key] = $breadcrumb['text'];
                                    }
                                }
                            } elseif ($route == 'product/manufacturer_info') {
                                $js_product_data['item_brand'] = $heading_title;
                            }
                            if (isset($item['manufacturer'])) {
                                $js_product_data['item_brand'] = $item['manufacturer'];
                            }
                            $js_data['items'][] = $js_product_data;
                        }
                    } elseif ($settings['type']) { /** GTAG & GTM */
                        foreach ($products as $item) {
                            $js_data['items'][] = $this->{$this->eModel}->getItemParameters($item['product_id'], $settings, array('index' => $index));
                            $index++;
                        }
                    } else { /** Measurement Protocol */
                        foreach ($products as $item) {
                            $js_data['items'][] = $item['product_id'];
                        }
                    }
                    $apply_data = array();
                    if (strpos($route, 'module/' . basename($route)) === false) {
                        $apply_data = array(
                            'search_value' => '</body>',
                            'add_value' => $this->createCodeSnippet($js_data, $settings) . '</body>'
                        );
                    } else {
                        $apply_data['add_value'] = $this->createCodeSnippet($js_data, $settings);
                    }
                    if ($theme_settings && isset($theme_settings['list'][$route])) {
                        $apply_data = array_replace($apply_data, $theme_settings['list'][$route]);
                    }
                    $this->apply($output, $apply_data);
                }

                if ($settings['select_status']) {
                    if ($theme_settings && isset($theme_settings['select'][$route])) {
                        $default_apply_data = array(
                            'search_value' => 'href="[href]"',
                            'search_index' => ''
                        );
                        if ($item_id) {
                            $default_apply_data['add_value'] = 'href="[href]" onclick="e4_item.select([item_id], \'[list]\', [index], [list_id])"';
                        } else {
                            $default_apply_data['add_value'] = 'href="[href]" onclick="e4_item.select([item_id], \'[list]\', [index])"';
                        }
                        $default_apply_data = array_replace($default_apply_data, $theme_settings['select'][$route]);
                        $index = 0;
                        foreach ($products as $item) {
                            $apply_data = $default_apply_data;
                            $apply_data['search_value'] = str_replace(array('[item_id]', '[href]'), array($item['product_id'], $item['href']), $apply_data['search_value']);
                            $apply_data['add_value'] = str_replace(array('[item_id]', '[href]', '[list]', '[index]', '[list_id]'), array($item['product_id'], $item['href'], $base_name, (++$index), $item_id), $apply_data['add_value']);
                            $apply_data['search_index'] = sprintf($apply_data['search_index'], $index);
                            $this->apply($output, $apply_data);
                        }
                    } else {
                        $index = 0;
                        foreach ($products as $item) {
                            $apply_data = array(
                                'search_value' => 'href="' . $item['href'] . '"'
                            );
                            if ($item_id) {
                                $apply_data['add_value'] = 'href="' . $item['href'] . '" onclick="e4_item.select(' . $item['product_id'] . ', \'' . $base_name . '\', ' . (++$index) . ', \'' . $item_id . '\')"';
                            } else {
                                $apply_data['add_value'] = 'href="' . $item['href'] . '" onclick="e4_item.select(' . $item['product_id'] . ', \'' . $base_name . '\', ' . (++$index) . ')"';
                            }
                            $this->apply($output, $apply_data);
                        }
                    }
                }

                if ($settings['cart_status']) {
                    if ($theme_settings && isset($theme_settings['add_to_cart'][$route])) {
                        $default_apply_data = array(
                            'search_value' => 'onclick="cart.add(\'[item_id]\'',
                            'search_index' => ''
                        );
                        if ($item_id) {
                            $default_apply_data['add_value'] = 'data-e4-index="[index]" data-e4-list="[list]" data-e4-list-id="[list_id]" onclick="cart.add(\'[item_id]\'';
                        } else {
                            $default_apply_data['add_value'] = 'data-e4-index="[index]" data-e4-list="[list]" onclick="cart.add(\'[item_id]\'';
                        }
                        $default_apply_data = array_replace($default_apply_data, $theme_settings['add_to_cart'][$route]);
                        $index = 0;
                        foreach ($products as $item) {
                            $apply_data = $default_apply_data;
                            $apply_data['search_value'] = str_replace(array('[item_id]', '[href]'), array($item['product_id'], $item['href']), $apply_data['search_value']);
                            $apply_data['add_value'] = str_replace(array('[item_id]', '[href]', '[list]', '[index]', '[list_id]'), array($item['product_id'], $item['href'], $base_name, (++$index), $item_id), $apply_data['add_value']);
                            $apply_data['search_index'] = sprintf($apply_data['search_index'], $index);
                            $this->apply($output, $apply_data);
                        }
                    } else {
                        $index = 0;
                        foreach ($products as $item) {
                            $apply_data = array(
                                'search_value' => 'onclick="cart.add(\'' . $item['product_id'] . '\''
                            );
                            if ($item_id) {
                                $apply_data['add_value'] = 'data-e4-index="' . (++$index) . '" data-e4-list="' . $base_name . '" data-e4-list-id="' . $item_id . '" onclick="cart.add(\'' . $item['product_id'] . '\'';
                            } else {
                                $apply_data['add_value'] = 'data-e4-index="' . (++$index) . '" data-e4-list="' . $base_name . '" onclick="cart.add(\'' . $item['product_id'] . '\'';
                            }
                            $this->apply($output, $apply_data);
                        }
                    }
                }

                if ($settings['wish_status']) {
                    if ($theme_settings && isset($theme_settings['add_to_wishlist'][$route])) {
                        $default_apply_data = array(
                            'search_value' => 'onclick="wishlist.add(\'[item_id]\'',
                            'search_index' => ''
                        );
                        if ($item_id) {
                            $default_apply_data['add_value'] = 'data-e4-index="[index]" data-e4-list="[list]" data-e4-list-id="[list_id]" onclick="wishlist.add(\'[item_id]\'';
                        } else {
                            $default_apply_data['add_value'] = 'data-e4-index="[index]" data-e4-list="[list]" onclick="wishlist.add(\'[item_id]\'';
                        }
                        $default_apply_data = array_replace($default_apply_data, $theme_settings['add_to_wishlist'][$route]);
                        $index = 0;
                        foreach ($products as $item) {
                            $apply_data = $default_apply_data;
                            $apply_data['search_value'] = str_replace(array('[item_id]', '[href]'), array($item['product_id'], $item['href']), $apply_data['search_value']);
                            $apply_data['add_value'] = str_replace(array('[item_id]', '[href]', '[list]', '[index]', '[list_id]'), array($item['product_id'], $item['href'], $base_name, (++$index), $item_id), $apply_data['add_value']);
                            $apply_data['search_index'] = sprintf($apply_data['search_index'], $index);
                            $this->apply($output, $apply_data);
                        }
                    } else {
                        $index = 0;
                        foreach ($products as $item) {
                            $apply_data = array(
                                'search_value' => 'onclick="wishlist.add(\'' . $item['product_id'] . '\''
                            );
                            if ($item_id) {
                                $apply_data['add_value'] = 'data-e4-index="' . (++$index) . '" data-e4-list="' . $base_name . '" data-e4-list-id="' . $item_id . '" onclick="wishlist.add(\'' . $item['product_id'] . '\'';
                            } else {
                                $apply_data['add_value'] = 'data-e4-index="' . (++$index) . '" data-e4-list="' . $base_name . '" onclick="wishlist.add(\'' . $item['product_id'] . '\'';
                            }
                            $this->apply($output, $apply_data);
                        }
                    }
                }
            } elseif ($error_code) {
                $this->applyError($output, $error_code, $route);
            }
        }
    }

    public function product_tabs_after(&$route, &$data, &$output) {
        if (isset($data['tabs'])) {
            $tabs = $data['tabs'];
        } else {
            $tabs = array();
        }
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        $theme_settings = $this->themeSettings($settings);
        if (!empty($theme_settings) && isset($theme_settings['tab_var'][$route])) {
            $theme_var = $theme_settings['tab_var'][$route];
            if (isset($theme_var['tabs']) && isset($data[$theme_var['tabs']])) {
                $tabs = $data[$theme_var['tabs']];
            }
        }
        foreach ($tabs as $key => $tab) {
            $this->product_list_after($route, $tab, $output);
        }
    }

    public function journal2_product_list_after(&$route, &$data, &$output) {
        if (isset($data['sections']) && isset($data['module_id']) && isset($data['module_type']) && $data['module_type'] == 'product') {
            $this->load->model($this->ePath);
            $settings = $this->{$this->eModel}->getSettings();
            $module_data = array();
            if (!$settings['active_language'] && !$settings['view_simple']) {
                $this->load->model('journal2/module');
                $module_data = $this->model_journal2_module->getModule($data['module_id']);
            }
            foreach ($data['sections'] as $key => $section) {
                if (isset($module_data['module_data']['product_sections'])) {
                    foreach ($module_data['module_data']['product_sections'] as $item) {
                        if (isset($item['section_title']['value'][$settings['language_id']]) && in_array($section['section_name'], $item['section_title']['value'])) {
                            $section['section_name'] = $item['section_title']['value'][$settings['language_id']];
                        }
                    }
                }
                $section['title'] = $section['section_name'];
                $section['module_id'] = $data['module_id'] . '_' . $key;
                $this->product_list_after($route, $section, $output);
            }
        }
    }

    public function journal3_product_list_after(&$route, &$data, &$output) {
        if (isset($data['items']) && isset($data['module_id'])) {
            $this->load->model($this->ePath);
            $settings = $this->{$this->eModel}->getSettings();
            $module_data = array();
            if (!$settings['active_language'] && !$settings['view_simple']) {
                $this->load->model('journal3/module');
                $module_data = $this->model_journal3_module->get($data['module_id'], basename($route));
            }
            foreach ($data['items'] as $key => $item) {
                if (isset($item['title']) && isset($module_data['items'])) {
                    foreach ($module_data['items'] as $module_item) {
                        if (in_array($item['title'], $module_item['title']) && isset($module_item['title']['lang_' . $settings['language_id']]) && $module_item['title']['lang_' . $settings['language_id']]) {
                            $item['title'] = $module_item['title']['lang_' . $settings['language_id']];
                        }
                    }
                }
                $item['module_id'] = $data['module_id'] . '_' . ($key - 1);
                $this->product_list_after($route, $item, $output);
            }
        }
    }

    public function journal32_product_list_after(&$route, &$data, &$output) {
        $status = true;
        if (isset($data['product']) && isset($data['products'])) { /** rc60 */
            $i = 1;
            foreach ($data['products'] as $key => $item) {
                if ($i == count($data['products']) && $key == $data['product']['product_id']) {
                    $status = true;
                } else {
                    $status = false;
                }
                $i++;
            }
        }
        if ($status) {
            if (isset($data['module_id'])) {
                $this->load->model($this->ePath);
                $settings = $this->{$this->eModel}->getSettings();
                $module_data = array();
                if (!$settings['active_language'] && !$settings['view_simple']) {
                    $this->load->model('journal3/module');
                    $module_data = $this->model_journal3_module->get($data['module_id'], basename($route));
                }
                $new_route = 'journal3/module/' . basename($route);
                $new_data = $data;
                if (isset($new_data['title']) && isset($module_data['items'])) {
                    foreach ($module_data['items'] as $key => $module_item) {
                        if (in_array($new_data['title'], $module_item['title']) && isset($module_item['title']['lang_' . $settings['language_id']])) {
                            $new_data['title'] = $module_item['title']['lang_' . $settings['language_id']];
                        }
                    }
                }
                $new_data['module_id'] = $new_data['module_id'] . '_' . ($new_data['index'] - 1);
                $this->product_list_after($new_route, $new_data, $output);
            } else {
                $new_route = $route;
                if (isset($this->request->get['route'])) {
                    $new_route = $this->request->get['route'];
                }
                $this->product_list_after($new_route, $data, $output);
            }
        }
    }

    public function promotion_list_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && isset($data['banners']) && $data['banners'] && $settings['promotion_status']) {
            if ($settings['promotion_simple']) {
                $banners = $data['banners'];
            } elseif (substr(VERSION, 0, 7) < '3.0.0.0') {
                $banners = $this->{$this->eModel}->getBanner($data['banner_id'], $settings['language_id']);
            } else {
                $banners = $this->{$this->eModel}->getBannerByData($data['banners'][0], $settings['language_id']);
            }
            $promotions = array();
            $index = 1;
            foreach ($banners as $key => $item) {
                if ($item['link']) {
                    $url_info = parse_url(str_replace('&amp;', '&', $item['link']));
                    if (isset($url_info['query']) && strpos($url_info['query'], 'product_id=') !== false) {
                        $link_data = array();
                        parse_str($url_info['query'], $link_data);
                        $promotions[] = array(
                            'item_id' => $link_data['product_id'],
                            'creative_name' => $item['title'],
                            'creative_slot' => isset($item['banner_image_id']) ? $item['banner_image_id'] : 0,
                            'link' => $item['link']
                        );
                    } elseif (isset($url_info['path'])) {
                        $keyword = trim(basename($url_info['path']), '/');
                        $seo_url_data =  $this->{$this->eModel}->getSeoUrl($keyword);
                        if (!empty($seo_url_data)) {
                            $url = explode('=', $seo_url_data['query']);
                            if ($url[0] == 'product_id') {
                                $promotions[] = array(
                                    'item_id' => $url[1],
                                    'creative_name' => $item['title'],
                                    'creative_slot' => isset($item['banner_image_id']) ? $item['banner_image_id'] : 0,
                                    'link' => $item['link']
                                );
                            }
                        }
                    }
                }
            }
            if ($promotions) {
                $theme_settings = $this->themeSettings($settings);
                /** view promotion */
                $js_data = array(
                    'event'         => 'view_promotion',
                    'affiliation'   => $settings['affiliation'],
                    'currency'      => $settings['currency'],
                    'index'         => $index,
                    'items'         => array(),
                    'promotion_id'  => basename($route),
                    'promotion_name'=> isset($banners[0]['name']) ? $banners[0]['name'] : (isset($data['name']) ? $data['name'] : 'Banner'),
                    'location_id'   => 'banner_id' . '_' . (isset($banners[0]['banner_id']) ? $banners[0]['banner_id'] : (isset($data['banner_id']) ? $data['banner_id'] : 0))
                );
                foreach ($promotions as $promotion) {
                    if ($settings['promotion_simple']) {
                        $product_data = array(
                            'item_id' => $promotion['item_id'],
                            'index' => $index,
                            'affiliation' => $settings['affiliation'],
                            'currency' => $settings['currency']
                        );
                    } else {
                        $product_data = $this->{$this->eModel}->getItemParameters($promotion['item_id'], $settings, array('index' => $index));
                    }
                    unset($promotion['link']);
                    $js_data['items'][] = array_merge($promotion, $product_data);
                    $index++;
                }
                $apply_data = array();
                $apply_data['add_value'] = $this->createCodeSnippet($js_data, $settings);
                if (!empty($theme_settings) && isset($theme_settings['list'][$route])) {
                    $apply_data = array_replace($apply_data, $theme_settings['list'][$route]);
                }
                $this->apply($output, $apply_data);
                /** view promotion */
                /** select promotion */
                $apply_data = array();
                if (!empty($theme_settings) && isset($theme_settings['select'][$route])) {
                    $default_apply_data = array(
                        'search_value' => 'href="[href]"',
                        'search_index' => ''
                    );
                    $default_apply_data['add_value'] = 'href="[href]" onclick="e4_promotion.select([item_id], \'[list]\', [index], [image_id])"';
                    $default_apply_data = array_replace($default_apply_data, $theme_settings['select'][$route]);
                    $index = 0;
                    foreach ($promotions as $item) {
                        $apply_data = $default_apply_data;
                        $apply_data['search_value'] = str_replace(array('[item_id]', '[href]'), array($item['item_id'], $item['link']), $apply_data['search_value']);
                        $apply_data['add_value'] = str_replace(array('[item_id]', '[href]', '[list]', '[index]', '[image_id]'), array($item['item_id'], $item['link'], basename($route), (++$index), $item['creative_slot']), $apply_data['add_value']);
                        $apply_data['search_index'] = sprintf($apply_data['search_index'], $index);
                        $this->apply($output, $apply_data);
                    }
                } else {
                    $index = 0;
                    foreach ($promotions as $item) {
                        $apply_data['add_value'] = 'href="' . $item['link'] . '" onclick="e4_promotion.select(' . $item['item_id'] . ', \'' . basename($route) . '\', ' . (++$index) . ', ' . $item['creative_slot'] . ')"';
                        $apply_data['search_value'] = 'href="' . $item['link'] . '"';
                        $this->apply($output, $apply_data);
                    }
                }
                /** select promotion */
            }
        }
    }

    public function journal2_promotion_list_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && $settings['promotion_status'] && isset($data['module_id'])) {
            $module_data = array();
            if (!$settings['promotion_simple']) {
                $this->load->model('journal2/module');
                $module_data = $this->model_journal2_module->getModule($data['module_id']);
            }
            $list_key = '';
            switch ($route) {
                case 'journal2/module/carousel_product':
                case 'journal2/module/static_banners':
                    $list_key = 'sections';
                    break;
                case 'journal2/module/slider_advanced':
                case 'journal2/module/slider_simple':
                    $list_key = 'slides';
                    break;
            }
            if ($list_key && isset($data[$list_key])) {
                $promotions = array();
                foreach ($data[$list_key] as $key => $slide) {
                    $link = '';
                    if (isset($slide['data'])) {
                        $xmlEl = (array)simplexml_load_string('<a ' . $slide['data'] . ' />');
                        if (isset($xmlEl['@attributes']['data-link'])) {
                            $link = $xmlEl['@attributes']['data-link'];
                        }
                    } elseif (isset($slide['link'])) {
                        $link = $slide['link'];
                    }
                    parse_str(parse_url(html_entity_decode($link, ENT_QUOTES, 'UTF-8'), PHP_URL_QUERY), $params);
                    if (isset($params['product_id'])) {
                        $promotions[] = array(
                            'item_id' => $params['product_id'],
                            'creative_name' => isset($slide['name']) ? $slide['name'] : 'journal2_' . basename($route),
                            'creative_slot' => $data['module_id'] . '-' . $key,
                            'link' => $link
                        );
                    } elseif (isset($module_data[$list_key][$key]['link']['menu_type']) && $module_data['sections'][$key]['link']['menu_type'] == 'product') {
                        $promotions[] = array(
                            'item_id' => $slide['link']['menu_item']['id'],
                            'creative_name' => isset($slide['name']) ? $slide['name'] : $module_data['module_type'],
                            'creative_slot' => $data['module_id'] . '-' . $key,
                            'link' => $link
                        );
                    }
                }
                $index = 1;
                if ($promotions) {
                    $theme_settings = $this->themeSettings($settings);
                    /** view promotion */
                    $js_data = array(
                        'event'         => 'view_promotion',
                        'affiliation'   => $settings['affiliation'],
                        'currency'      => $settings['currency'],
                        'index'         => $index,
                        'items'         => array(),
                        'promotion_id'  => basename($route),
                        'promotion_name'=> isset($module_data['module_data']['module_name']) ? $module_data['module_data']['module_name'] : basename($route),
                        'location_id'   => $data['module_id']
                    );

                    foreach ($promotions as $promotion) {
                        $product_data = $this->{$this->eModel}->getItemParameters($promotion['item_id'], $settings, array('index' => $index));
                        unset($promotion['link']);
                        $js_data['items'][] = array_merge($product_data, $promotion);
                        $index++;
                    }
                    $apply_data = array();
                    $apply_data['add_value'] = $this->createCodeSnippet($js_data, $settings);
                    if (!empty($theme_settings) && isset($theme_settings['list'][$route])) {
                        $apply_data = array_replace($apply_data, $theme_settings['list'][$route]);
                    }
                    $this->apply($output, $apply_data);
                    /** view promotion */
                    /** select promotion */
                    $apply_data = array();
                    if (!empty($theme_settings) && isset($theme_settings['select'][$route])) {
                        $default_apply_data = array(
                            'search_value' => 'href="[href]"',
                            'search_index' => ''
                        );
                        $default_apply_data['add_value'] = 'href="[href]" onclick="e4_promotion.select([item_id], \'[list]\', [index], [image_id])"';
                        $default_apply_data = array_replace($default_apply_data, $theme_settings['select'][$route]);
                        $index = 0;
                        foreach ($promotions as $item) {
                            $apply_data = $default_apply_data;
                            $apply_data['search_value'] = str_replace(array('[item_id]', '[href]'), array($item['item_id'], $item['link']), $apply_data['search_value']);
                            $apply_data['add_value'] = str_replace(array('[item_id]', '[href]', '[list]', '[index]', '[image_id]'), array($item['item_id'], $item['link'], basename($route), (++$index), $item['creative_slot']), $apply_data['add_value']);
                            $apply_data['search_index'] = sprintf($apply_data['search_index'], $index);
                            $this->apply($output, $apply_data);
                        }
                    } else {
                        $index = 0;
                        foreach ($promotions as $item) {
                            $apply_data['add_value'] = 'href="' . $item['link'] . '" onclick="e4_promotion.select(' . $item['item_id'] . ', \'' . basename($route) . '\', ' . (++$index) . ', \'' . $item['creative_slot'] . '\')"';
                            $apply_data['search_value'] = 'href="' . $item['link'] . '"';

                            $this->apply($output, $apply_data);
                        }
                    }
                    /** select promotion */
                }
            }
        }
    }

    public function journal3_promotion_list_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && $settings['promotion_status'] && isset($data['module_id']) && isset($data['items'])) {
            $module_data = array();
            if (!$settings['promotion_simple']) {
                $this->load->model('journal3/module');
                $module_data = $this->model_journal3_module->get($data['module_id'], basename($route));
            }
            $promotions = array();
            $item_index = 0;
            foreach ($data['items'] as $key => $item) {
                if (isset($item['link']['type']) && $item['link']['type'] == 'product') {
                    $promotion = array(
                        'item_id' => $item['link']['id'],
                        'creative_slot' => $item['id'],
                        'link' => $item['link']['href']
                    );
                    if (isset($module_data['items'][$item_index])) {
                        $promotion['creative_name'] = $module_data['items'][$item_index]['name'];
                    }
                    $promotions[] = $promotion;
                    $item_index++;
                }
            }
            $index = 1;
            if ($promotions) {
                $theme_settings = $this->themeSettings($settings);
                /** view promotion */
                $js_data = array(
                    'event'         => 'view_promotion',
                    'affiliation'   => $settings['affiliation'],
                    'currency'      => $settings['currency'],
                    'index'         => $index,
                    'items'         => array(),
                    'promotion_id'  => basename($route),
                    'promotion_name'=> isset($module_data['general']['name']) ? $module_data['general']['name'] : basename($route) . '_' . $data['module_id'],
                    'location_id'   => $data['module_id']
                );
                foreach ($promotions as $promotion) {
                    $product_data = $this->{$this->eModel}->getItemParameters($promotion['item_id'], $settings, array('index' => $index));
                    unset($promotion['link']);
                    $js_data['items'][] = array_merge($product_data, $promotion);
                    $index++;
                }
                $apply_data = array();
                $apply_data['add_value'] = $this->createCodeSnippet($js_data, $settings);
                if (!empty($theme_settings) && isset($theme_settings['list'][$route])) {
                    $apply_data = array_replace($apply_data, $theme_settings['list'][$route]);
                }
                $this->apply($output, $apply_data);
                /** view promotion */
                /** select promotion */
                $apply_data = array();
                if (!empty($theme_settings) && isset($theme_settings['select'][$route])) {
                    $default_apply_data = array(
                        'search_value' => 'href="[href]"',
                        'search_index' => ''
                    );
                    $default_apply_data['add_value'] = 'href="[href]" onclick="e4_promotion.select([item_id], \'[list]\', [index], [image_id])"';
                    $default_apply_data = array_replace($default_apply_data, $theme_settings['select'][$route]);
                    $index = 0;
                    foreach ($promotions as $item) {
                        $apply_data = $default_apply_data;
                        $apply_data['search_value'] = str_replace(array('[item_id]', '[href]'), array($item['item_id'], $item['link']), $apply_data['search_value']);
                        $apply_data['add_value'] = str_replace(array('[item_id]', '[href]', '[list]', '[index]', '[image_id]'), array($item['item_id'], $item['link'], basename($route), (++$index), $item['creative_slot']), $apply_data['add_value']);
                        $apply_data['search_index'] = sprintf($apply_data['search_index'], $index);
                        $this->apply($output, $apply_data);
                    }
                } else {
                    $index = 0;
                    foreach ($promotions as $item) {
                        $apply_data['add_value'] = 'href="' . $item['link'] . '" onclick="e4_promotion.select(' . $item['item_id'] . ', \'' . basename($route) . '\', ' . (++$index) . ', \'' . $item['creative_slot'] . '\')"';
                        $apply_data['search_value'] = 'href="' . $item['link'] . '"';
                        $this->apply($output, $apply_data);
                    }
                }
                /** select promotion */
            }
        }
    }

    public function product_product_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings)) {
            $related_route = 'product/product/related';
            $this->product_list_after($related_route, $data, $output);
            $theme_settings = $this->themeSettings($settings);

            if (!isset($data['product_id']) && ($settings['view_status'] || $settings['cart_status'])) {
                if ($theme_settings && isset($theme_settings['view_item_data']['product_id']) && isset($data[$theme_settings['view_item_data']['product_id']])) {
                    $data['product_id'] = $data[$theme_settings['view_item_data']['product_id']];
                } else {
                    $this->applyError($output, 'product_id_not_found', $route);
                    return false;
                }
            }

            if ($settings['view_status']) {
                $js_data = array(
                    'event'     => 'view_item',
                    'currency'  => $settings['currency']
                );
                if ($settings['view_simple']) { /** View in Simple Mode */
                    if (isset($data['special']) && $data['special']) {
                        $price = preg_replace('/[^0-9.' . $this->language->get('decimal_point') . ']/', '', $data['special']);
                    } elseif (isset($data['price'])) {
                        $price = preg_replace('/[^0-9.' . $this->language->get('decimal_point') . ']/', '', $data['price']);
                    } else {
                        $this->applyError($output, 'product_price_not_found', $route);
                        return false;
                    }
                    if (!$settings['active_tax'] && !$settings['tax'] && isset($data['tax']) && $data['tax']) {
                        $price = preg_replace('/[^0-9' . $this->language->get('decimal_point') . ']/', '', $data['tax']);
                    }
                    $price = str_replace(',', '.', $price);
                    if (!$settings['active_currency']) {
                        $price = $this->currency->convert($price, $this->session->data['currency'], $settings['currency']);
                        $price = $this->currency->format($price, $settings['currency'], 1, false);
                    }
                    if (isset($data['minimum']) && $data['minimum'] > 0) {
                        $quantity = $data['minimum'];
                    } else {
                        $quantity = 1;
                    }
                    $item_parameters = array(
                        'item_id' => $data['product_id'],
                        'item_name' => isset($data['heading_title']) ? $data['heading_title'] : '',
                        'affiliation' => $settings['affiliation'],
                        'price' => $price,
                        'currency' => $settings['currency'],
                        'quantity' => $quantity
                    );
                    if (isset($data['manufacturer']) && $data['manufacturer']) {
                        $item_parameters['item_brand'] = $data['manufacturer'];
                    }
                    if (isset($data['breadcrumbs'])) {
                        foreach ($data['breadcrumbs'] as $breadcrumb_key => $breadcrumb) {
                            if (!$breadcrumb_key || count($data['breadcrumbs']) == $breadcrumb_key + 1) {
                                continue;
                            } elseif ($breadcrumb_key == 1) {
                                $item_parameters['item_category'] = $breadcrumb['text'];
                            } else {
                                $item_parameters['item_category_' . $breadcrumb_key] = $breadcrumb['text'];
                            }
                        }
                    }
                    $js_data['items'][] = $item_parameters;
                    $js_data['value'] = $price;
                } else {
                    $item_parameters = $this->{$this->eModel}->getItemParameters($data['product_id'], $settings, $js_data);
                    if ($item_parameters) {
                        $js_data['items'][] = $item_parameters;
                        $js_data['value'] = $item_parameters['price'];
                    } else {
                        $this->applyError($output, 'product_language_not_found', $route);
                        return false;
                    }
                }
                $apply_data = array(
                    'search_value' => '</body>',
                    'add_value' => $this->createCodeSnippet($js_data, $settings) . '</body>'
                );
                if ($theme_settings && isset($theme_settings['view_item'])) {
                    $apply_data = array_replace($apply_data, $theme_settings['view_item']);
                }
                $this->apply($output, $apply_data);
            }

            if (isset($settings['cart_status']) && $settings['cart_status'] && $theme_settings && isset($theme_settings['add_to_cart'][$route])) {
                $apply_data = array(
                    'search_value' => 'id="button-cart"',
                    'add_value' => 'id="button-cart" data-e4-item-id="input[name=product_id]" data-e4-quantity="input[name=quantity]" data-e4-option="[name^=option]"'
                );
                $apply_data = array_replace($apply_data, $theme_settings['add_to_cart'][$route]);
                $apply_data['search_value'] = str_replace('[product_id]', $data['product_id'], $apply_data['search_value']);
                $apply_data['add_value'] = str_replace('[product_id]', $data['product_id'], $apply_data['add_value']);
                $this->apply($output, $apply_data);
            }
        }
    }

    public function account_wishlist_add_before(&$route, &$data) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && !$settings['type'] && $settings['wish_status'] && $settings['wish_add'] && isset($this->request->post['product_id']) && $this->request->post['product_id']) {
            $e4_params = array(
                'event' => 'add_to_wishlist',
                'item_id' => (int)$this->request->post['product_id']
            );
            $this->{$this->eModel}->add_to_wishlist($e4_params, 1);
        }
    }

    public function common_cart_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && $settings['cart_status']) {
            $cart_products = $this->cart->getProducts();
            if (substr(VERSION, 0, 7) < '2.1.0.1') {
                $cart_key_name = 'key';
            } else {
                $cart_key_name = 'cart_id';
            }
            $theme_settings = $this->themeSettings($settings);
            if ($cart_products && $settings['type'] || !$settings['cart_edit']) {
                $apply_data = array();
                if ($theme_settings && isset($theme_settings['update_cart'][$route])) {
                    $default_apply_data = array(
                        'search_value' => 'name="quantity[[cart_id]]"',
                        'search_index' => '',
                        'add_value' => 'data-e4-item-id="[item_id]" data-e4-quantity="[quantity]" data-e4-variant=\'[variant]\' name="quantity[[cart_id]]"'
                    );
                    $default_apply_data = array_replace($default_apply_data, $theme_settings['update_cart'][$route]);
                    foreach ($cart_products as $key => $item) {
                        $options = array();
                        foreach ($item['option'] as $option) {
                            if ($option['type'] == 'select' || $option['type'] == 'checkbox' || $option['type'] == 'radio') {
                                $options[$option['product_option_id']] = $option['product_option_value_id'];
                            }
                        }
                        $apply_data = $default_apply_data;
                        $apply_data['search_value'] = str_replace('[cart_id]', $item[$cart_key_name], $apply_data['search_value']);
                        if ($options) {
                            $apply_data['add_value'] = str_replace(array('[cart_id]', '[item_id]', '[quantity]', '[variant]'), array($item[$cart_key_name], $item['product_id'], $item['quantity'], json_encode($options)), $apply_data['add_value']);
                        } else {
                            $apply_data['add_value'] = str_replace(array('[cart_id]', '[item_id]', '[quantity]', '[variant]'), array($item[$cart_key_name], $item['product_id'], $item['quantity'], ''), $apply_data['add_value']);
                        }
                        $apply_data['search_index'] = sprintf($apply_data['search_index'], $key);
                        $this->apply($output, $apply_data);
                    }
                } else {
                    foreach ($cart_products as $key => $item) {
                        $options = array();
                        foreach ($item['option'] as $option) {
                            if ($option['type'] == 'select' || $option['type'] == 'checkbox' || $option['type'] == 'radio') {
                                $options[$option['product_option_id']] = $option['product_option_value_id'];
                            }
                        }
                        if ($options) {
                            $apply_data['add_value'] = 'data-e4-item-id="' . $item['product_id'] . '" data-e4-quantity="' . $item['quantity'] . '" data-e4-variant=\'' . json_encode($options) . '\' name="quantity[' . $item[$cart_key_name] . ']';
                        } else {
                            $apply_data['add_value'] = 'data-e4-item-id="' . $item['product_id'] . '" data-e4-quantity="' . $item['quantity'] . '" name="quantity[' . $item[$cart_key_name] . ']';
                        }
                        $apply_data['search_value'] = 'name="quantity[' . $item[$cart_key_name] . ']';
                        $this->apply($output, $apply_data);
                    }
                }
            }

            if ($cart_products && ($settings['type'] || !$settings['cart_remove'])) {
                $apply_data = array();
                if ($theme_settings && isset($theme_settings['remove_from_cart'][$route])) {
                    $default_apply_data = array(
                        'search_value' => 'onclick="cart.remove(\'[cart_id]\'',
                        'search_index' => '',
                        'add_value' => 'data-e4-item-id="[item_id]" data-e4-quantity="[quantity]" data-e4-variant=\'[variant]\' onclick="cart.remove(\'[cart_id]\''
                    );
                    $default_apply_data = array_replace($default_apply_data, $theme_settings['remove_from_cart'][$route]);
                    foreach ($cart_products as $key => $item) {
                        $options = array();
                        foreach ($item['option'] as $option) {
                            if ($option['type'] == 'select' || $option['type'] == 'checkbox' || $option['type'] == 'radio') {
                                $options[$option['product_option_id']] = $option['product_option_value_id'];
                            }
                        }
                        $apply_data = $default_apply_data;
                        $apply_data['search_value'] = str_replace('[cart_id]', $item[$cart_key_name], $apply_data['search_value']);
                        if ($options) {
                            $apply_data['add_value'] = str_replace(array('[cart_id]', '[item_id]', '[quantity]', '[variant]'), array($item[$cart_key_name], $item['product_id'], $item['quantity'], json_encode($options)), $apply_data['add_value']);
                        } else {
                            $apply_data['add_value'] = str_replace(array('[cart_id]', '[item_id]', '[quantity]', '[variant]'), array($item[$cart_key_name], $item['product_id'], $item['quantity'], ''), $apply_data['add_value']);
                        }
                        $apply_data['search_index'] = sprintf($apply_data['search_index'], $key);
                        $this->apply($output, $apply_data);
                    }
                } else {
                    foreach ($cart_products as $item) {
                        $options = array();
                        foreach ($item['option'] as $option) {
                            if ($option['type'] == 'select' || $option['type'] == 'checkbox' || $option['type'] == 'radio') {
                                $options[$option['product_option_id']] = $option['product_option_value_id'];
                            }
                        }
                        if ($options) {
                            $apply_data['add_value'] = 'data-e4-item-id="' . $item['product_id'] . '" data-e4-quantity="' . $item['quantity'] . '" data-e4-variant=\'' . json_encode($options) . '\' onclick="cart.remove(\'' . $item[$cart_key_name] . '\'';
                        } else {
                            $apply_data['add_value'] = 'data-e4-item-id="' . $item['product_id'] . '" data-e4-quantity="' . $item['quantity'] . '" onclick="cart.remove(\'' . $item[$cart_key_name] . '\'';
                        }
                        $apply_data['search_value'] = 'onclick="cart.remove(\'' . $item[$cart_key_name] . '\'';
                        $this->apply($output, $apply_data);
                    }
                }
            }
        }
    }

    public function checkout_cart_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings)) {
            $base_name = basename($route);
            $cart_products = $this->cart->getProducts();
            if (substr(VERSION, 0, 7) < '2.1.0.1') {
                $cart_key_name = 'key';
            } else {
                $cart_key_name = 'cart_id';
            }
            $theme_settings = $this->themeSettings($settings);
            if ($settings['view_status'] && $cart_products) {
                $js_data = $this->{$this->eModel}->getCartProducts($cart_products);
                $js_data['event'] = 'view_cart';
                $apply_data = array(
                    'search_value' => '</body>',
                    'add_value' => $this->createCodeSnippet($js_data, $settings) . '</body>'
                );
                if ($theme_settings && isset($theme_settings['view_cart'])) {
                    $apply_data = array_replace($apply_data, $theme_settings['view_cart']);
                }
                $this->apply($output, $apply_data);
            }

            if ($settings['select_status'] && $cart_products) {
                $apply_data = array();
                foreach ($data['products'] as $key => $item) {
                    foreach ($cart_products as $cart_product) {
                        if ($item[$cart_key_name] == $cart_product[$cart_key_name]) {
                            $data['products'][$key]['product_id'] = $cart_product['product_id'];
                        }
                    }
                }
                if ($theme_settings && isset($theme_settings['select'][$route])) {
                    $default_apply_data = array(
                        'search_value' => 'href="[href]"',
                        'search_index' => ''
                    );
                    $default_apply_data['add_value'] = 'href="[href]" onclick="e4_item.select([item_id], \'[list]\', [position])"';
                    $default_apply_data = array_replace($default_apply_data, $theme_settings['select'][$route]);
                    foreach ($data['products'] as $key => $item) {
                        $apply_data = $default_apply_data;
                        $apply_data['search_value'] = str_replace(array('item_id]', '[href]'), array($item['product_id'], $item['href']), $apply_data['search_value']);
                        $apply_data['add_value'] = str_replace(array('[item_id]', '[href]', '[list]', '[index]'), array($item['product_id'], $item['href'], $base_name, ($key + 1)), $apply_data['add_value']);
                        $apply_data['search_index'] = sprintf($apply_data['search_index'], $key);
                        $this->apply($output, $apply_data);
                    }
                } else {
                    foreach ($data['products'] as $key => $item) {
                        $apply_data['add_value'] = 'href="' . $item['href'] . '" onclick="e4_item.select(' . $item['product_id'] . ', \'' . $base_name . '\', ' . ($key + 1) . ')"';
                        $apply_data['search_value'] = 'href="' . $item['href'] . '"';
                        $this->apply($output, $apply_data);
                    }
                }
            }

            if ($settings['cart_status'] && $cart_products) {
                $apply_data = array();
                if ($settings['type'] || !$settings['cart_edit']) {
                    if ($theme_settings && isset($theme_settings['update_cart'][$route])) {
                        $default_apply_data = array(
                            'search_value' => 'name="quantity[[cart_id]]"',
                            'search_index' => '',
                            'add_value' => 'data-e4-list="checkout_cart" data-e4-item-id="[item_id]" data-e4-quantity="[quantity]" data-e4-variant=\'[variant]\' name="quantity[[cart_id]]"'
                        );
                        $default_apply_data = array_replace($default_apply_data, $theme_settings['update_cart'][$route]);
                        foreach ($cart_products as $key => $item) {
                            $options = array();
                            foreach ($item['option'] as $option) {
                                if ($option['type'] == 'select' || $option['type'] == 'checkbox' || $option['type'] == 'radio') {
                                    $options[$option['product_option_id']] = $option['product_option_value_id'];
                                }
                            }
                            $apply_data = $default_apply_data;
                            $apply_data['search_value'] = str_replace('[cart_id]', $item[$cart_key_name], $apply_data['search_value']);
                            if ($options) {
                                $apply_data['add_value'] = str_replace(array('[cart_id]', '[item_id]', '[quantity]', '[variant]'), array($item[$cart_key_name], $item['product_id'], $item['quantity'], json_encode($options)), $apply_data['add_value']);
                            } else {
                                $apply_data['add_value'] = str_replace(array('[cart_id]', '[item_id]', '[quantity]', '[variant]'), array($item[$cart_key_name], $item['product_id'], $item['quantity'], ''), $apply_data['add_value']);
                            }
                            $apply_data['search_index'] = sprintf($apply_data['search_index'], $key);
                            $this->apply($output, $apply_data);
                        }
                    } else {
                        foreach ($cart_products as $key => $item) {
                            $options = array();
                            foreach ($item['option'] as $option) {
                                if ($option['type'] == 'select' || $option['type'] == 'checkbox' || $option['type'] == 'radio') {
                                    $options[$option['product_option_id']] = $option['product_option_value_id'];
                                }
                            }
                            if ($options) {
                                $apply_data['add_value'] = 'data-e4-list="checkout_cart" data-e4-item-id="' . $item['product_id'] . '" data-e4-quantity="' . $item['quantity'] . '" data-e4-variant=\'' . json_encode($options) . '\' name="quantity[' . $item[$cart_key_name] . ']';
                            } else {
                                $apply_data['add_value'] = 'data-e4-list="checkout_cart" data-e4-item-id="' . $item['product_id'] . '" data-e4-quantity="' . $item['quantity'] . '" name="quantity[' . $item[$cart_key_name] . ']';
                            }
                            $apply_data['search_value'] = 'name="quantity[' . $item[$cart_key_name] . ']';
                            $this->apply($output, $apply_data);
                        }
                    }
                }

                if ($settings['type'] || !$settings['cart_remove']) {
                    if ($theme_settings && isset($theme_settings['remove_from_cart'][$route])) {
                        $default_apply_data = array(
                            'search_value' => 'onclick="cart.remove(\'[cart_id]\'',
                            'search_index' => '',
                            'add_value' => 'data-e4-list="checkout_cart" data-e4-item-id="[item_id]" data-e4-quantity="[quantity]" data-e4-variant=\'[variant]\' onclick="cart.remove(\'[cart_id]\''
                        );
                        $default_apply_data = array_replace($default_apply_data, $theme_settings['remove_from_cart'][$route]);
                        foreach ($cart_products as $key => $item) {
                            $options = array();
                            foreach ($item['option'] as $option) {
                                if ($option['type'] == 'select' || $option['type'] == 'checkbox' || $option['type'] == 'radio') {
                                    $options[$option['product_option_id']] = $option['product_option_value_id'];
                                }
                            }
                            $apply_data = $default_apply_data;
                            $apply_data['search_value'] = str_replace('[cart_id]', $item[$cart_key_name], $apply_data['search_value']);
                            if ($options) {
                                $apply_data['add_value'] = str_replace(array('[cart_id]', '[item_id]', '[quantity]', '[variant]'), array($item[$cart_key_name], $item['product_id'], $item['quantity'], json_encode($options)), $apply_data['add_value']);
                            } else {
                                $apply_data['add_value'] = str_replace(array('[cart_id]', '[item_id]', '[quantity]', '[variant]'), array($item[$cart_key_name], $item['product_id'], $item['quantity'], ''), $apply_data['add_value']);
                            }
                            $apply_data['search_index'] = sprintf($apply_data['search_index'], $key);
                            $this->apply($output, $apply_data);
                        }
                    } else {
                        foreach ($cart_products as $item) {
                            $options = array();
                            foreach ($item['option'] as $option) {
                                if ($option['type'] == 'select' || $option['type'] == 'checkbox' || $option['type'] == 'radio') {
                                    $options[$option['product_option_id']] = $option['product_option_value_id'];
                                }
                            }
                            if ($options) {
                                $apply_data['add_value'] = 'data-e4-list="checkout_cart" data-e4-item-id="' . $item['product_id'] . '" data-e4-quantity="' . $item['quantity'] . '" data-e4-variant=\'' . json_encode($options) . '\' onclick="cart.remove(\'' . $item[$cart_key_name] . '\'';
                            } else {
                                $apply_data['add_value'] = 'data-e4-list="checkout_cart" data-e4-item-id="' . $item['product_id'] . '" data-e4-quantity="' . $item['quantity'] . '" onclick="cart.remove(\'' . $item[$cart_key_name] . '\'';
                            }
                            $apply_data['search_value'] = 'onclick="cart.remove(\'' . $item[$cart_key_name] . '\'';
                            $this->apply($output, $apply_data);
                        }
                    }
                }
            }
        }
    }

    public function checkout_cart_add_before(&$route, &$data) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && !$settings['type'] && $settings['cart_status'] && $settings['cart_add'] && isset($this->request->post['product_id']) && $this->request->post['product_id']) {
            $e4_params = array(
                'event' => 'add_to_cart',
                'item_id' => (int)$this->request->post['product_id'],
                'quantity' => isset($this->request->post['quantity']) ? (int)$this->request->post['quantity'] : 1
            );
            if (isset($this->request->post['option'])) {
                $e4_params['variant'] = array_filter($this->request->post['option']);
            }
            $this->{$this->eModel}->cart($e4_params, 1);
        }
    }

    public function checkout_cart_edit_before(&$route, &$data) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && !$settings['type'] && $settings['cart_status'] && $settings['cart_edit'] && isset($this->request->post['quantity']) && $this->request->post['quantity']) {
            foreach ($this->request->post['quantity'] as $key => $value) {
                $cart_info = $this->{$this->eModel}->getCart($key);
                if ($cart_info) {
                    $e4_params = array(
                        'item_id' => $cart_info['product_id'],
                        'variant' => json_decode($cart_info['option']) ? get_object_vars(json_decode($cart_info['option'])) : array()
                    );
                    if ($value > $cart_info['quantity']) {
                        $e4_params['event'] = 'add_to_cart';
                        $e4_params['quantity'] = $value - $cart_info['quantity'];
                        $this->{$this->eModel}->cart($e4_params, 1);
                    } elseif ($value < $cart_info['quantity']) {
                        $e4_params['event'] = 'remove_from_cart';
                        $e4_params['quantity'] = $cart_info['quantity'] - $value;
                        $this->{$this->eModel}->cart($e4_params, 1);
                    }
                }
            }
        }
    }

    public function checkout_cart_remove_before(&$route, &$data) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && !$settings['type'] && $settings['cart_status'] && $settings['cart_remove'] && isset($this->request->post['key']) && $this->request->post['key']) {
            $cart_info = $this->{$this->eModel}->getCart($this->request->post['key']);
            if ($cart_info) {
                $e4_params = array(
                    'event' => 'remove_from_cart',
                    'item_id' => $cart_info['product_id'],
                    'quantity' => $cart_info['quantity'],
                    'variant' => json_decode($cart_info['option']) ? get_object_vars(json_decode($cart_info['option'])) : array()
                );
                $this->{$this->eModel}->cart($e4_params, 1);
            }
        }
    }

    public function checkout_checkout_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && $settings['checkout_status']) {
            $js_data = $this->{$this->eModel}->getCartProducts();
            if ($js_data) {
                $js_data['event'] = 'begin_checkout';
                $apply_data = array();
                if (strpos($output, '</body>') !== false) {
                    $apply_data['search_value'] = '</body>';
                }
                $theme_settings = $this->themeSettings($settings);
                if ($theme_settings && isset($theme_settings['begin_checkout'])) {
                    if (!isset($theme_settings['begin_checkout']['add_value'])) {
                        $apply_data['add_value'] = $this->createCodeSnippet($js_data, $settings);
                    }
                    if (!isset($theme_settings['begin_checkout']['search_value']) && strpos($output, '</body>') !== false) {
                        $apply_data['add_value'] .= "\n" . '</body>';
                    }
                    $apply_data = array_replace($apply_data, $theme_settings['begin_checkout']);
                } else {
                    $apply_data['add_value'] = $this->createCodeSnippet($js_data, $settings);
                    if (strpos($output, '</body>') !== false) {
                        $apply_data['add_value'] .= "\n" . '</body>';
                    }
                }
                $this->apply($output, $apply_data);

                if ($theme_settings && isset($theme_settings['checkout_shipping']) && (!isset($settings['checkout_shipping']) || !$settings['checkout_shipping'])) {
                    $settings['checkout_shipping'] = $theme_settings['checkout_shipping'];
                    if (isset($theme_settings['checkout_shipping_ajax'])) {
                        $settings['checkout_shipping_ajax'] = $theme_settings['checkout_shipping_ajax'];
                    }
                }
                $apply_data = array();
                if (isset($settings['checkout_shipping']) && $settings['checkout_shipping']) {
                    if (isset($settings['checkout_shipping_ajax'])) {
                        $html = 'var e4_shipping_status = true;' . "\n";
                        $html .= '$(document).ajaxStop(function() {' . "\n";
                        $html .= '  $(\'' . $settings['checkout_shipping'] . '\').on(\'click\', function () {' . "\n";
                        $html .= '    if (e4_shipping_status) {' . "\n";
                        $html .= '      e4_shipping_status = false;' . "\n";
                        $html .= '      e4_checkout.add_shipping_info();' . "\n";
                        $html .= '    }' . "\n";
                        $html .= '  });' . "\n";
                        $html .= '});' . "\n";

                        $html .= 'setTimeout(function() {' . "\n";
                        $html .= '  $(\'' . $settings['checkout_shipping'] . '\').on(\'click\', function () {' . "\n";
                        $html .= '    if (e4_shipping_status) {' . "\n";
                        $html .= '      e4_shipping_status = false;' . "\n";
                        $html .= '      e4_checkout.add_shipping_info();' . "\n";
                        $html .= '    }' . "\n";
                        $html .= '  });' . "\n";
                        $html .= '}, 2000);' . "\n";
                    } else {
                        $html = '$(document).delegate(\'' . $settings['checkout_shipping'] . '\', \'click\', function() {' . "\n";
                        $html .= '  e4_checkout.add_shipping_info();' . "\n";
                        $html .= '});' . "\n";
                    }
                    $apply_data['add_value'] = $this->wrapJsTag($html);
                    if (strpos($output, '</body>') !== false) {
                        $apply_data['search_value'] = '</body>';
                    }
                    if ($theme_settings && isset($theme_settings['checkout_shipping_custom'])) {
                        $apply_data = array_replace($apply_data, $theme_settings['checkout_shipping_custom']);
                    }
                    if (strpos($output, '</body>') !== false) {
                        $apply_data['add_value'] .= "\n" . '</body>';
                    }
                    $this->apply($output, $apply_data);
                } elseif ($theme_settings && isset($theme_settings['checkout_shipping_custom'])) {
                    if (strpos($output, '</body>') !== false) {
                        $apply_data['search_value'] = '</body>';
                    }
                    $apply_data = array_replace($apply_data, $theme_settings['checkout_shipping_custom']);
                    if (!isset($theme_settings['checkout_shipping_custom']['search_value']) && strpos($output, '</body>') !== false) {
                        $apply_data['add_value'] .= "\n" . '</body>';
                    }
                    $this->apply($output, $apply_data);
                }

                if ($theme_settings && isset($theme_settings['checkout_payment']) && (!isset($settings['checkout_payment']) || !$settings['checkout_payment'])) {
                    $settings['checkout_payment'] = $theme_settings['checkout_payment'];
                    if (isset($theme_settings['checkout_payment_ajax'])) {
                        $settings['checkout_payment_ajax'] = $theme_settings['checkout_payment_ajax'];
                    }
                }
                $apply_data = array();
                if (isset($settings['checkout_payment']) && $settings['checkout_payment']) {
                    if (isset($settings['checkout_payment_ajax'])) {
                        $html = 'var e4_payment_status = true;' . "\n";
                        $html .= '$(document).ajaxStop(function() {' . "\n";
                        $html .= '  $(\'' . $settings['checkout_payment'] . '\').on(\'click\', function () {' . "\n";
                        $html .= '    if (e4_payment_status) {' . "\n";
                        $html .= '      e4_payment_status = false;' . "\n";
                        $html .= '      e4_checkout.add_payment_info();' . "\n";
                        $html .= '    }' . "\n";
                        $html .= '  });' . "\n";
                        $html .= '});' . "\n";

                        $html .= 'setTimeout(function() {' . "\n";
                        $html .= '  $(\'' . $settings['checkout_payment'] . '\').on(\'click\', function () {' . "\n";
                        $html .= '    if (e4_payment_status) {' . "\n";
                        $html .= '      e4_payment_status = false;' . "\n";
                        $html .= '      e4_checkout.add_payment_info();' . "\n";
                        $html .= '    }' . "\n";
                        $html .= '  });' . "\n";
                        $html .= '}, 2000);' . "\n";
                    } else {
                        $html = '$(document).delegate(\'' . $settings['checkout_payment'] . '\', \'click\', function() {' . "\n";
                        $html .= '  e4_checkout.add_payment_info();' . "\n";
                        $html .= '});' . "\n";
                    }
                    $apply_data['add_value'] = $this->wrapJsTag($html);
                    if (strpos($output, '</body>') !== false) {
                        $apply_data['search_value'] = '</body>';
                    }
                    if ($theme_settings && isset($theme_settings['checkout_payment_custom'])) {
                        $apply_data = array_replace($apply_data, $theme_settings['checkout_payment_custom']);
                    }
                    if (strpos($output, '</body>') !== false) {
                        $apply_data['add_value'] .= "\n" . '</body>';
                    }
                    $this->apply($output, $apply_data);
                } elseif ($theme_settings && isset($theme_settings['checkout_payment_custom'])) {
                    if (strpos($output, '</body>') !== false) {
                        $apply_data['search_value'] = '</body>';
                    }
                    $apply_data = array_replace($apply_data, $theme_settings['checkout_payment_custom']);
                    if (!isset($theme_settings['checkout_payment_custom']['search_value']) && strpos($output, '</body>') !== false) {
                        $apply_data['add_value'] .= "\n" . '</body>';
                    }
                    $this->apply($output, $apply_data);
                }
            }
        }
    }

    public function checkout_confirm_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && $settings['checkout_status']) {
            $js_data = $this->{$this->eModel}->getCartProducts();
            if ($js_data) {
                $js_data['event'] = 'add_payment_info';
                if (isset($this->session->data['payment_method']['code'])) {
                    $code = $this->session->data['payment_method']['code'];
                } else {
                    $code = '';
                }
                $js_data['payment_type'] = $this->{$this->eModel}->getPaymentType($code);
                $apply_data = array();
                $theme_settings = $this->themeSettings($settings);
                if ($theme_settings && isset($theme_settings['add_payment_info'])) {
                    if (!isset($theme_settings['add_payment_info']['add_value'])) {
                        $apply_data['add_value'] = $this->createCodeSnippet($js_data, $settings);
                    }
                    $apply_data = array_replace($apply_data, $theme_settings['add_payment_info']);
                } else {
                    $apply_data['add_value'] = $this->createCodeSnippet($js_data, $settings);
                }
                $this->apply($output, $apply_data);
                if ($theme_settings && isset($theme_settings['add_payment_info_custom']) && isset($this->session->data['payment_methods'])) {
                    $index = 0;
                    foreach ($this->session->data['payment_methods'] as $payment_methods) {
                        $apply_data = $theme_settings['add_payment_info_custom'];
                        $apply_data['search_value'] = str_replace(array('[code]', '[title]'), array($payment_methods['code'], $payment_methods['title']), $apply_data['search_value']);
                        $apply_data['add_value'] = str_replace(array('[code]', '[title]'), array($payment_methods['code'], $payment_methods['title']), $apply_data['add_value']);
                        if (isset($apply_data['search_index'])) {
                            $apply_data['search_index'] = sprintf($apply_data['search_index'], $index);
                        }
                        $this->apply($output, $apply_data);
                        $index++;
                    }
                }
            }
        }
    }

    public function checkout_payment_method_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && $settings['checkout_status']) {
            $js_data = $this->{$this->eModel}->getCartProducts();
            if ($js_data) {
                $js_data['event'] = 'add_shipping_info';
                if (isset($this->session->data['shipping_method']['code'])) {
                    $code = $this->session->data['shipping_method']['code'];
                } else {
                    $code = '';
                }
                $js_data['shipping_tier'] = $this->{$this->eModel}->getShippingTier($code);
                $apply_data = array();
                $theme_settings = $this->themeSettings($settings);
                if ($theme_settings && isset($theme_settings['add_shipping_info'])) {
                    if (!isset($theme_settings['add_shipping_info']['add_value'])) {
                        $apply_data['add_value'] = $this->createCodeSnippet($js_data, $settings);
                    }
                    $apply_data = array_replace($apply_data, $theme_settings['add_shipping_info']);
                } else {
                    $apply_data['add_value'] = $this->createCodeSnippet($js_data, $settings);
                }
                $this->apply($output, $apply_data);
                if ($theme_settings && isset($theme_settings['add_shipping_info_custom']) && isset($this->session->data['shipping_methods'])) {
                    $index = 0;
                    foreach ($this->session->data['shipping_methods'] as $shipping_method_item) {
                        foreach ($shipping_method_item['quote'] as $quote_key => $quote) {
                            $apply_data = $theme_settings['add_shipping_info_custom'];
                            $apply_data['search_value'] = str_replace(array('[code]', '[title]', '[key]'), array($quote['code'], $quote['title'], $quote_key), $apply_data['search_value']);
                            $apply_data['add_value'] = str_replace(array('[code]', '[title]', '[key]'), array($quote['code'], $quote['title'], $quote_key), $apply_data['add_value']);
                            if (isset($apply_data['search_index'])) {
                                $apply_data['search_index'] = sprintf($apply_data['search_index'], $index);
                            }
                            $this->apply($output, $apply_data);
                            $index++;
                        }
                    }
                }
            }
        }
    }

    public function common_success_after(&$route, &$data, &$output) {
        if (isset($this->session->data['e4_order_id']) && $e4_order_id = $this->session->data['e4_order_id']) {
            unset($this->session->data['e4_order_id']);
            $this->load->model($this->ePath);
            $settings = $this->{$this->eModel}->getSettings();
            if ($this->validate($settings) && $settings['purchase_status'] && $settings['purchase_simple']) {
                $order_parameters = $this->{$this->eModel}->getOrderParameters($e4_order_id, $settings);
                if ($order_parameters) {
                    $e_order_info = $this->{$this->eModel}->getEOrder($e4_order_id);
                    if (!$e_order_info) {
                        $this->{$this->eModel}->addEOrder($e4_order_id, array('tracking_type' => $settings['type']));
                        $e_order_info = $this->{$this->eModel}->getEOrder($e4_order_id);
                    }
                    if (!$e_order_info['purchase_status']) {
                        foreach ($order_parameters['items'] as $key => $item) {
                            unset($order_parameters['items'][$key]['order_product_id']);
                            unset($order_parameters['items'][$key]['product_id']);
                            unset($order_parameters['items'][$key]['refund_quantity']);
                            unset($order_parameters['items'][$key]['options']);
                        }
                        $js_data = $order_parameters;
                        $js_data['event'] = 'purchase';
                        $js_data['client_id'] = $e_order_info['client_id'];
                        $apply_data = array(
                            'search_value' => '</body>',
                            'add_value' => $this->createCodeSnippet($js_data, $settings) . '</body>'
                        );
                        $theme_settings = $this->themeSettings($settings);
                        if ($theme_settings && isset($theme_settings['purchase'])) {
                            $apply_data = array_replace($apply_data, $theme_settings['purchase']);
                        }
                        $this->apply($output, $apply_data);

                        $e_order_info['purchase_status'] = 1;
                        $this->{$this->eModel}->editEOrder($e4_order_id, $e_order_info);
                    }
                }
            }
        }
    }

    public function add_order_after(&$route, &$data, &$output) {
        $this->load->model($this->ePath);
        $settings = $this->{$this->eModel}->getSettings();
        if ($this->validate($settings) && ($output || isset($this->session->data['order_id']))) {
            $order_id = 0;
            if ($output) {
                $order_id = $output;
            } elseif (isset($this->session->data['order_id'])) {
                $order_id = $this->session->data['order_id'];
            }
            if ($order_id) {
                $order_parameters = $this->{$this->eModel}->getOrderParameters($order_id, $settings);
                $order_parameters['tracking_type'] = $settings['type'];
                $this->{$this->eModel}->addEOrder($order_id, $order_parameters);
                if ($settings['purchase_simple']) {
                    $this->session->data['e4_order_id'] = $order_id;
                }
            }
        }
    }

    public function add_order_history_after(&$route, &$data, &$output) {
        if (isset($data[0])) {
            $this->load->model($this->ePath);
            $order_info = $this->{$this->eModel}->getOrder($data[0]);
            if ($order_info) {
                $settings = $this->{$this->eModel}->getSettings($order_info['store_id']);
                if ($this->validate($settings)) {
                    if ($settings['purchase_status'] && isset($settings['purchase_tracking_status']) && in_array($order_info['order_status_id'], $settings['purchase_tracking_status'])) {
                        $this->purchase($order_info['order_id'], $settings);
                    } elseif ($settings['refund_status'] && isset($settings['refund_tracking_status']) && in_array($order_info['order_status_id'], $settings['refund_tracking_status'])) {
                        $this->refund($order_info['order_id'], $settings);
                    }
                }
            } else {
                $this->load->language($this->ePath);
                $this->{$this->eModel}->writeLog(sprintf($this->language->get('error_order_not_found'), (int)$data[0]), $this->config->get('config_store_id'));
            }
        }
    }

    public function track() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['order_id'])) {
            $this->load->model($this->ePath);
            $order_info = $this->{$this->eModel}->getOrder($this->request->post['order_id']);
            $settings = $this->{$this->eModel}->getSettings($order_info['store_id']);
            if (isset($this->request->post['refund']) && $this->request->post['refund']) {
                $order_product_id = isset($this->request->post['order_product_id']) ? $this->request->post['order_product_id'] : 0;
                $quantity = isset($this->request->post['quantity']) ? $this->request->post['quantity'] : 0;
                $this->refund($this->request->post['order_id'], $settings, $order_product_id, $quantity);
            } else {
                $this->purchase($this->request->post['order_id'], $settings);
            }
        }
    }

    public function purchase($order_id, $settings = array()) {
        $result = array();
        $this->load->model($this->ePath);
        $order_info = $this->{$this->eModel}->getOrder($order_id);
        if ($order_info && !isset($this->session->data['e4_order_id'])) {
            if (!$settings) {
                $settings = $this->{$this->eModel}->getSettings($order_info['store_id']);
            }
            $e_order_info = $this->{$this->eModel}->getEOrder($order_id);

            if (!$e_order_info) {
                $this->{$this->eModel}->addEOrder($order_id, array('tracking_type' => $settings['type']));
                $e_order_info = $this->{$this->eModel}->getEOrder($order_id);
            }
            if ($e_order_info && !$e_order_info['purchase_status']) {
                $order_parameters = $this->{$this->eModel}->getOrderParameters($order_id, $settings);
                $data = array(
                    'event' => 'purchase',
                    'client_id' => $e_order_info['client_id']
                );
                foreach ($order_parameters['items'] as $key => $item) {
                    unset($order_parameters['items'][$key]['order_product_id']);
                    unset($order_parameters['items'][$key]['product_id']);
                    unset($order_parameters['items'][$key]['refund_quantity']);
                    unset($order_parameters['items'][$key]['options']);
                }
                $data['date_added'] = $order_info['date_added'];
                $data['customer_id'] = $order_info['customer_id'];
                $params = $this->{$this->eModel}->getDefaultParameters($data, $settings, $order_parameters);
                $log_data = $params;
                if ($settings['type']) {
                    $params = $this->{$this->eModel}->convertDefaultParameters($params);
                }
                $log_data['log'] = $this->{$this->eModel}->createRequest($params, $settings);
                if (($settings['type'] && !$log_data['log']) || (!$settings['type'] && !$settings['validation_mode'])) {
                    $e_order_info['purchase_status'] = 1;
                    $this->{$this->eModel}->editEOrder($order_id, $e_order_info);
                }
                $result['log'] = $this->{$this->eModel}->addLog($log_data, $settings);
            }
        }
        return $result;
    }

    public function refund($order_id, $settings = array(), $order_product_id = 0, $quantity = 0) {
        $result = array();
        $this->load->model($this->ePath);
        $order_info = $this->{$this->eModel}->getOrder($order_id);
        if (!$settings) {
            $settings = $this->{$this->eModel}->getSettings($order_info['store_id']);
        }
        $e_order_info = $this->{$this->eModel}->getEOrder($order_id);

        if ($e_order_info && ($e_order_info['purchase_status'] == 1 || (!$settings['type'] && $settings['validation_mode']))) {
            $order_parameters = $this->{$this->eModel}->getOrderParameters($order_id, $settings);
            $refund_products = array();
            if ($order_product_id) {
                foreach ($order_parameters['items'] as $item_key => $item) {
                    if ($item['order_product_id'] == $order_product_id) {
                        if ($item['refund_quantity']) {
                            if ($item['refund_quantity'] < $item['quantity']) {
                                if ($quantity && ($item['refund_quantity'] + $quantity) <= $item['quantity']) {
                                    $order_parameters['items'][$item_key]['quantity'] = $quantity;
                                } else {
                                    $order_parameters['items'][$item_key]['quantity'] = $item['quantity'] - $item['refund_quantity'];
                                }
                                $refund_products[] = $order_parameters['items'][$item_key];
                            } else {
                                return false; /** product fully refunded */
                            }
                        } else {
                            if ($quantity && $quantity < $item['quantity']) {
                                $order_parameters['items'][$item_key]['quantity'] = $quantity;
                            }
                            $refund_products[] = $order_parameters['items'][$item_key];
                        }
                    } else {
                        unset($order_parameters['items'][$item_key]);
                    }
                }
            } else {
                $refunded_products = $this->{$this->eModel}->getERefundProducts($order_id);
                $refund_products = $order_parameters['items'];
                if ($refunded_products) {
                    foreach ($order_parameters['items'] as $item_key => $item) {
                        if ($item['refund_quantity']) {
                            if ($item['refund_quantity'] < $item['quantity']) {
                                $order_parameters['items'][$item_key]['quantity'] = $item['quantity'] - $item['refund_quantity'];
                            } else {
                                unset($order_parameters['items'][$item_key]);
                            }
                        }
                    }
                } else {
                    unset($order_parameters['items']);
                }
            }
            $data = array(
                'event' => 'refund',
                'client_id' => $e_order_info['client_id']
            );
            if ($order_info['customer_id']) {
                $data['customer_id'] = $order_info['customer_id'];
            }
            if (isset($order_parameters['items'])) {
                foreach ($order_parameters['items'] as $key => $item) {
                    unset($order_parameters['items'][$key]['order_product_id']);
                    unset($order_parameters['items'][$key]['product_id']);
                    unset($order_parameters['items'][$key]['refund_quantity']);
                    unset($order_parameters['items'][$key]['options']);
                }
                $order_parameters['items'] = array_values($order_parameters['items']);
            }
            $params = $this->{$this->eModel}->getDefaultParameters($data, $settings, $order_parameters);
            $log_data = $params;
            if ($settings['type']) {
                $params = $this->{$this->eModel}->convertDefaultParameters($params);
            } else {
                unset($params['order_id']);
            }
            $log_data['log'] = $this->{$this->eModel}->createRequest($params, $settings);
            if (($settings['type'] && !$log_data['log']) || (!$settings['type'] && !$settings['validation_mode'])) {
                $this->{$this->eModel}->editERefundProducts($order_id, $refund_products, $settings['type']);
            }
            $result['log'] = $this->{$this->eModel}->addLog($log_data, $settings);
        }
        return $result;
    }

    protected function createCodeSnippet($data, $settings, $js_params = array(), $element = array()) {
        switch ($settings['type']) {
            case 1:
                return $this->createGTag($data, $settings);
                break;
            case 2:
                return $this->createGTM($data, $settings);
                break;
            default:
                return $this->createGMP($data, $settings, $js_params, $element);
        }
    }

    public function createGMP($data, $settings, $js_params, $element) {
        $this->load->model($this->ePath);
        $client_id = $this->{$this->eModel}->getClientId(1);
        $html = '';
        $html_pre = '';
        $html_after = '';
        if ($client_id) {
            $data['client_id'] = $client_id;
            $log = $this->{$this->eModel}->{$data['event']}($data);
            if ($log['log'] && $settings['log']) {
                $html_pre .= '$(document).ready(function() {' . "\n";
                $html .= $this->getLogJs($log['log']);
                $html_after .= '});' . "\n";
                return $this->wrapJsTag($html, $html_pre, $html_after);
            }
        } else {
            $json_data = json_encode($data);
            foreach ($js_params as $key => $value) {
                if ($json_data != '[]' || !$json_data) {
                    $json_data = rtrim($json_data, '}') . ', \'' . $key . '\': ' . $value . ' }';
                } else {
                    $json_data = '{ \'' . $key . '\': ' . $value . ' }';
                }
            }
            if ($element && is_array($element)) {
                switch ($element['type']) {
                    case 'select': {
                        $html_pre .= '  $(\'' . $element['selector'] . '\').on(\'click\', function() {';
                        $html_after .= '  });' . "\n";
                        break;
                    }
                    case 'change': {
                        $html_pre .= '  $(document).on(\'change\', \'' . $element['selector'] . '\', function() {' . "\n";
                        $html_after .= '  });' . "\n";
                        break;
                    }
                    case 'page_load': {
                        $html_pre .= '$(document).ready(function() {' . "\n";
                        $html_pre .= ' setE4Interval(function() {' . "\n";
                        $html_after .= ' }, 0);' . "\n";
                        $html_after .= '});' . "\n";
                        break;
                    }
                }
            } else {
                $html_pre .= '$(document).ready(function() {' . "\n";
                $html_pre .= ' setE4Interval(function() {' . "\n";
                $html_after .= ' }, 0);' . "\n";
                $html_after .= '});' . "\n";
            }

            $html .= '  $.ajax({' . "\n";
            $html .= '    url: \'index.php?route=' . $this->ePath . '\',' . "\n";
            $html .= '    type: \'post\',' . "\n";
            $html .= '    data: ' . $json_data . ',' . "\n";
            $html .= '    dataType: \'json\',' . "\n";
            if ($settings['log']) {
                $html .= '    success: function(json) {' . "\n";
                $html .= '      if (json[\'log\']) {' . "\n";
                $html .= '        console.log(json[\'log\'][\'text\']);' . "\n";
                $html .= '        if (json[\'log\'][\'show\']) {' . "\n";
                $html .= '          if (typeof showE4Log !== \'undefined\') {' . "\n";
                $html .= '            showE4Log(json[\'log\'][\'text\']);' . "\n";
                $html .= '          } else {' . "\n";
                $html .= '            $.post(\'index.php?route=' . $this->ePath . '\', { event: \'error\', code: \'js_not_found\' }, function( data ) {' . "\n";
                $html .= '              console.log(data[\'text\']);' . "\n";
                $html .= '            });' . "\n";
                $html .= '          }' . "\n";
                $html .= '        }' . "\n";
                $html .= '      }' . "\n";
                $html .= '    },' . "\n";
                $html .= '    error: function(xhr, ajaxOptions, thrownError) {' . "\n";
                $html .= '      console.log(xhr.status + \' - \' + xhr.statusText);' . "\n";
                $html .= '    }' . "\n";
            }
            $html .= '  });' . "\n";
            return $this->wrapJsTag($html, $html_pre, $html_after);
        }
    }

    public function createGTag($data, $settings) {
        ini_set('serialize_precision', -1);
        $this->load->model($this->ePath);
        $html = '';
        $sub_html = '';
        if ($settings['log']) {
            $html .= '  if (typeof gtag === \'undefined\') {' . "\n";
            $html .= '    $.post(\'index.php?route=' . $this->ePath . '\', { event: \'error\', code: \'gtag_not_defined\' }, function( data ) {' . "\n";
            $html .= '      if (typeof showE4Log !== \'undefined\') {' . "\n";
            $html .= '        showE4Log(data[\'text\']);' . "\n";
            $html .= '      } else {' . "\n";
            $html .= '        console.log(data[\'text\']);' . "\n";
            $html .= '      }' . "\n";
            $html .= '    });' . "\n";
            $html .= '  }' . "\n";
        }
        switch ($data['event']) {
            case 'view_item_list':
                $sub_html .= '    \'item_list_name\': \'' . $data['item_list_name'] . "',\n";
                $sub_html .= '    \'item_list_id\': \'' . $data['item_list_id'] . "',\n";
                break;
            case 'view_cart':
            case 'view_item':
                $sub_html .= '    \'currency\': \'' . $data['currency'] . '\',' . "\n";
                $sub_html .= '    \'value\': ' . $data['value'] . ',' . "\n";
                break;
            case 'view_promotion':
                $sub_html .= '    \'location_id\': \'' . $data['location_id'] . '\',' . "\n";
                $sub_html .= '    \'promotion_id\': \'' . $data['promotion_id'] . '\',' . "\n";
                $sub_html .= '    \'promotion_name\': \'' . $data['promotion_name'] . '\',' . "\n";
                break;
            case 'begin_checkout':
            case 'add_payment_info':
            case 'add_shipping_info':
                $sub_html .= '    \'currency\': \'' . $data['currency'] . "',\n";
                $sub_html .= '    \'value\': ' . $data['value'] . ',' . "\n";
                if (isset($data['shipping_tier'])) {
                    $sub_html .= '     \'shipping_tier\': \'' . $data['shipping_tier'] . '\',' . "\n";
                } elseif (isset($data['payment_type'])) {
                    $sub_html .= '     \'payment_type\': \'' . $data['payment_type'] . '\',' . "\n";
                }
                if (isset($data['coupon'])) {
                    $sub_html .= '    \'coupon\': \'' . $data['coupon'] . "',\n";
                }
                break;
            case 'purchase':
            case 'refund':
                $sub_html .= '    \'currency\': \'' . $data['currency'] . "',\n";
                $sub_html .= '    \'transaction_id\': \'' . $data['transaction_id'] . "',\n";
                $sub_html .= '    \'value\': ' . $data['value'] . ',' . "\n";
                $sub_html .= '    \'affiliation\': \'' . $data['affiliation'] . "',\n";
                if (isset($data['coupon'])) {
                    $sub_html .= '    \'coupon\': \'' . $data['coupon'] . "',\n";
                }
                $sub_html .= '    \'shipping\': ' . $data['shipping'] . ",\n";
                $sub_html .= '    \'tax\': ' . $data['tax'] . ",\n";
                break;
        }
        $html .= '  gtag(\'event\', \'' . $data['event'] . '\', {' . "\n";
        $html .= $sub_html;
        if ($data['items']) {
            $html .= '    \'items\': ' . json_encode($data['items']) . ",\n";
        }
        if ($settings['debug_mode']) {
            $data['debug_mode'] = true;
            $html .= '    \'debug_mode\': true,' . "\n";
        }
        $html .= '  });' . "\n";
        if ($settings['log']) {
            $log = $this->{$this->eModel}->addLog($data, $settings);
            $html .= $this->getLogJs($log);
        }
        return $this->wrapJsTag($html);
    }

    public function createGTM($data, $settings) {
        ini_set('serialize_precision', -1);
        $html = '';
        $sub_html = '';
        if ($settings['log']) {
            $html .= '  if (typeof dataLayer === \'undefined\') {' . "\n";
            $html .= '    $.post(\'index.php?route=' . $this->ePath . '\', { event: \'error\', code: \'datalayer_not_defined\' }, function( data ) {' . "\n";
            $html .= '      if (typeof showE4Log !== \'undefined\') {' . "\n";
            $html .= '        showE4Log(data[\'text\']);' . "\n";
            $html .= '      } else {' . "\n";
            $html .= '        console.log(data[\'text\']);' . "\n";
            $html .= '      }' . "\n";
            $html .= '    });' . "\n";
            $html .= '  }' . "\n";
        }
        $html .= '  dataLayer.push({ ecommerce: null });' . "\n";
        $html .= '  dataLayer.push({' . "\n";
        $html .= '    \'event\': \'' . $data['event'] . '\',' . "\n";
        switch ($data['event']) {
            case 'view_item_list':
                $sub_html .= '    \'item_list_name\': \'' . $data['item_list_name'] . "',\n";
                $sub_html .= '    \'item_list_id\': \'' . $data['item_list_id'] . "',\n";
                break;
            case 'view_cart':
            case 'view_item':
                $sub_html .= '    \'currency\': \'' . $data['currency'] . '\',' . "\n";
                $sub_html .= '    \'value\': ' . $data['value'] . ',' . "\n";
                break;
            case 'begin_checkout':
            case 'add_payment_info':
            case 'add_shipping_info':
                $sub_html .= '     \'currency\': \'' . $data['currency'] . '\',' . "\n";
                $sub_html .= '     \'value\': ' . $data['value'] . ',' . "\n";
                if (isset($data['shipping_tier'])) {
                    $sub_html .= '     \'shipping_tier\': \'' . $data['shipping_tier'] . '\',' . "\n";
                } elseif (isset($data['payment_type'])) {
                    $sub_html .= '     \'payment_type\': \'' . $data['payment_type'] . '\',' . "\n";
                }
                if (isset($data['coupon'])) {
                    $sub_html .= '     \'coupon\': \'' . $data['coupon'] . '\',' . "\n";
                }
                break;
            case 'purchase':
                if (isset($data['custom_definition'])) {
                    $html .= '    \'user\': ' . json_encode($data['custom_definition']) . ',' . "\n";
                }
                $sub_html .= '     \'transaction_id\': \'' . $data['transaction_id'] . '\',' . "\n";
                $sub_html .= '     \'affiliation\': \'' . $data['affiliation'] . '\',' . "\n";
                $sub_html .= '     \'value\': ' . $data['value'] . ',' . "\n";
                $sub_html .= '     \'tax\': ' . $data['tax'] . ',' . "\n";
                $sub_html .= '     \'shipping\': ' . $data['shipping'] . ',' . "\n";
                $sub_html .= '     \'currency\': \'' . $data['currency'] . '\',' . "\n";
                if (isset($data['coupon'])) {
                    $sub_html .= '     \'coupon\': \'' . $data['coupon'] . '\',' . "\n";
                }
                break;
            case 'refund':
                $sub_html .= '     \'transaction_id\': \'' . $data['transaction_id'] . '\',' . "\n";
                break;
        }
        $html .= '    \'ecommerce\': {' . "\n" . $sub_html;
        if ($data['items']) {
            if ($data['event'] == 'view_item_list') {
                foreach ($data['items'] as $key => $item) {
                    $data['items'][$key]['item_list_name'] = $data['item_list_name'];
                    $data['items'][$key]['item_list_id'] = $data['item_list_id'];
                }
            }
            $html .= '    \'items\': ' . json_encode($data['items']) . "\n";
        }
        $html .= '    }' . "\n";
        $html .= '  });' . "\n";
        if ($settings['log']) {
            $this->load->model($this->ePath);
            $log = $this->{$this->eModel}->addLog($data, $settings);
            $html .= $this->getLogJs($log);
        }
        return $this->wrapJsTag($html);
    }

    public function getLogJs($log) {
        $html = '';
        $html .= '  console.log(' . json_encode($log['text']) . ');' . "\n";
        if ($log['show']) {
            $html .= ' $(document).ready(function() {';
            $html .= '  if (typeof showE4Log !== \'undefined\') {' . "\n";
            $html .= '    showE4Log(' . json_encode($log['text']) . ');' . "\n";
            $html .= '  } else {' . "\n";
            $html .= '    $.post(\'index.php?route=' . $this->ePath . '\', { event: \'error\', code: \'js_not_found\' }, function( data ) {' . "\n";
            $html .= '      console.log(data[\'text\']);' . "\n";
            $html .= '    });' . "\n";
            $html .= '  }' . "\n";
            $html .= '  });';
        }
        return $html;
    }

    protected function apply(&$output, $data) {
        $original_output = $output;
        $default_setting = array(
            'search_value' => '',
            'search_trim' => false,
            'search_regex' => false,
            'search_index' => '',
            'search_limit' => '',
            'add_value' => '',
            'add_trim' => false,
            'add_position' => '',
            'add_offset' => '',
            'ignoreif_value' => '',
            'ignoreif_regex' => false
        );
        $data = array_replace($default_setting, $data);
        if (!$data['search_value']) {
            if ($data['add_position'] == 'before') {
                $output = $data['add_value'] . "\n" . $output;
            } else if ($data['add_position'] == 'replace') {
                $output = $data['add_value'];
            } else {
                $output .= "\n" . $data['add_value'];
            }
            return true;
        }
        if ($data['ignoreif_value']) {
            if ($data['ignoreif_regex'] != 'true') {
                if (strpos($output, $data['ignoreif_value']) !== false) {
                    return false;
                }
            } else {
                if (preg_match($data['ignoreif_value'], $output)) {
                    return false;
                }
            }
        }
        if ($data['search_regex'] != 'true') {
            if (!$data['search_trim'] || $data['search_trim'] == 'true') {
                $data['search_value'] = trim($data['search_value']);
            }
            if ($data['add_offset'] == '') {
                $data['add_offset'] = 0;
            }
            if ($data['add_trim'] == 'true') {
                $data['add_value'] = trim($data['add_value']);
            }
            if ($data['search_index'] !== '') {
                $indexes = explode(',', $data['search_index']);
            } else {
                $indexes = array();
            }
            $i = 0;
            $lines = explode("\n", $output);
            for ($line_id = 0; $line_id < count($lines); $line_id++) {
                $line = $lines[$line_id];
                $match = false;
                if (stripos($line, $data['search_value']) !== false) {
                    if (!$indexes) {
                        $match = true;
                    } elseif (in_array($i, $indexes)) {
                        $match = true;
                    }
                    $i++;
                }
                if ($match) {
                    switch ($data['add_position']) {
                        default:
                        case 'replace':
                            if ($data['add_offset'] < 0) {
                                array_splice($lines, $line_id + $data['add_offset'], abs($data['add_offset']) + 1, array(str_replace($data['search_value'], $data['add_value'], $line)));
                                $line_id -= $data['search_offset'];
                            } else {
                                array_splice($lines, $line_id, $data['add_offset'] + 1, array(str_replace($data['search_value'], $data['add_value'], $line)));
                            }
                            break;
                        case 'before':
                            $new_lines = explode("\n", $data['add_value']);
                            array_splice($lines, $line_id - $data['add_offset'], 0, $new_lines);
                            $line_id += count($new_lines);
                            break;
                        case 'after':
                            $new_lines = explode("\n", $data['add_value']);
                            array_splice($lines, ($line_id + 1) + $data['add_offset'], 0, $new_lines);
                            $line_id += count($new_lines);
                            break;
                    }
                }
            }
            $output = implode("\n", $lines);
        } else {
            if (!$data['search_limit']) {
                $data['search_limit'] = -1;
            }
            $output = preg_replace($data['search_value'], $data['add_value'], $output, $data['search_limit']);
        }
        if ($original_output == $output) {
            return false;
        } else {
            return true;
        }
    }

    protected function wrapJsTag($code, $pre_code = '', $after_code = '') {
        $html = '<script type="text/javascript"><!--' . "\n";
        $html .= $pre_code . $code . $after_code;
        $html .= '//--></script>' . "\n";
        return $html;
    }

    protected function applyError(&$output, $code, $sprintf = '') {
        $this->load->model($this->ePath);
        $log = $this->{$this->eModel}->error(array('code' => $code, 'sprintf' => $sprintf));
        if ($log) {
            $html = '  $(document).ready(function() {' . "\n";
            $html .= '    console.log(' . json_encode($log['text']) . ');' . "\n";
            if ($log['show']) {
                $html .= '    showE4Log(' . json_encode($log['text']). ');' . "\n";
            }
            $html .= '  });' . "\n";
            $search_setting = array(
                'add_position' => 'after',
                'add_value' => $this->wrapJsTag($html)
            );
            $this->apply($output, $search_setting);
        }
    }

    protected function themeSettings($settings) {
        $result = array();
        if ($settings['theme'] && file_exists(DIR_CONFIG . $this->eCode . '/theme/' . $settings['theme'] . '.php')) {
            $this->load->config($this->eCode . '/theme/' . $settings['theme']);
            if ($this->config->get($this->eCode . '_setting')) {
                $result = $this->config->get($this->eCode . '_setting');
            }
        }
        if ($settings['checkout_extension'] && file_exists(DIR_CONFIG . $this->eCode . '/checkout/' . $settings['checkout_extension'] . '.php')) {
            $this->load->config($this->eCode . '/checkout/' . $settings['checkout_extension']);
            if ($this->config->get($this->eCode . '_setting')) {
                $result = array_merge($result, $this->config->get($this->eCode . '_setting'));
            }
        }
        return $result;
    }

    public function validate($settings) {
        if (!$settings['status']) {
            return false;
        }
        if (!utf8_strlen($settings['traffic_type'])) {
            if (utf8_strlen($settings['exclude_u_agent']) > 1 && isset($this->request->server['HTTP_USER_AGENT']) && preg_match('/' . trim($settings['exclude_u_agent'], '| ') . '/i', $this->request->server['HTTP_USER_AGENT'])) {
                return false;
            }
            if (isset($settings['exclude_admin']) && $settings['exclude_admin'] && isset($this->session->data['user_id']) && $this->session->data['user_id']) {
                return false;
            }
            if (utf8_strlen($settings['exclude_ip']) > 1) {
                $ip_list = preg_replace('~\r?\n~', "\n", $settings['exclude_ip']);
                $ip_arr = explode("\n", $ip_list);
                if (!empty($this->request->server['HTTP_CLIENT_IP'])) {
                    $ip_address = $this->request->server['HTTP_CLIENT_IP'];
                } elseif (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
                    $ip_address = $this->request->server['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip_address = $this->request->server['REMOTE_ADDR'];
                }
                foreach ($ip_arr as $ip) {
                    $ip = trim($ip);
                    if ($ip_address == $ip) {
                        return false;
                    }
                    $mask = str_replace('.*', '', $ip);
                    if (strpos($ip_address, $mask) !== false) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}