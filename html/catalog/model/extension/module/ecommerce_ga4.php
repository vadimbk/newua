<?php
class ModelExtensionModuleEcommerceGa4 extends Model {
    private $eCode      = 'ecommerce_ga4';
    private $eName      = 'module_ecommerce_ga4';
    private $eModel     = 'model_extension_module_ecommerce_ga4';
    private $ePath      = 'extension/module/ecommerce_ga4';
    private $eVersion   = '1.0.5';

    public function view_item_list($data) {
        $return = array();
        $settings = $this->getSettings();
        if ($data['event'] == 'view_search_results') {
            $event_params = array(
                'search_term' => $data['search_term']
            );
        } elseif ($data['event'] == 'view_cart') {
            $event_params = [];
        } else {
            $event_params = array(
                'item_list_name' => $data['item_list_name'],
                'item_list_id' => $data['item_list_id']
            );
        }
        $params = $this->getDefaultParameters($data, $settings, $event_params);
        if ($settings['list_limit']) {
            $limit_list = $settings['list_limit'];
        } else {
            $limit_list = 26;
        }
        if (count($data['items']) > $limit_list) {
            $batches = array_chunk($data['items'], $limit_list);
            unset($data['items']);
            foreach ($batches as $batch_key => $batch) {
                $batch_params = $params;
                if ($settings['list_limit_skip']) {
                    $log = 'items skipped / ';
                    if ($batch_key) {
                        break;
                    }
                } else {
                    $log = 'multi-event / ';
                }
                foreach ($batch as $key => $item) {
                    if (is_array($item)) {
                        $batch_params['events']['params']['items'][] = $item;
                        $data['items'][$key] = $item;
                    } else {
                        $product_data = $this->getItemParameters($item, $settings, $data);
                        $batch_params['events']['params']['items'][] = $product_data;
                        $data['items'][$key] = $product_data;
                        $data['index']++;
                    }
                }
                $log .= $this->createRequest($batch_params, $settings);
                $batch_params['log'] = $log;
                $batch_result = $this->addLog($batch_params, $settings);
                if ($return) {
                    $return['log']['text'] .= "\n" . $batch_result['text'];
                } else {
                    $return['log'] = array(
                        'text' => $batch_result['text'],
                        'show' => $batch_result['show']
                    );
                }
            }
        } else {
            foreach ($data['items'] as $key => $item) {
                if (is_array($item)) {
                    $params['events']['params']['items'][] = $item;
                } else {
                    $product_data = $this->getItemParameters($item, $settings, array('index' => $data['index']++));
                    $params['events']['params']['items'][] = $product_data;
                    $data['items'][$key] = $product_data;
                }
            }
            $params['log'] = $this->createRequest($params, $settings);
            $return['log'] = $this->addLog($params, $settings);
        }
        return $return;
    }

    public function view_search_results($data) {
        return $this->view_item_list($data);
    }

    public function view_promotion($data) {
        $return = array();
        $settings = $this->getSettings();
        $event_params = array(
            'location_id' => $data['location_id'],
            'promotion_id' => $data['promotion_id'],
            'promotion_name' => $data['promotion_name']
        );
        foreach ($data['items'] as $key => $item) {
            if (is_array($item)) {
                $event_params['items'][] = $item;
            } else {
                $product_data = $this->getItemParameters($item, $settings, $data);
                $event_params['items'][] = $product_data;
                $data['items'][$key] = $product_data;
                $data['index']++;
            }
        }
        $params = $this->getDefaultParameters($data, $settings, $event_params);
        $params['log'] = $this->createRequest($params, $settings);
        $return['log'] = $this->addLog($params, $settings);
        return $return;
    }

    public function view_item($data) {
        $return = array();
        $settings = $this->getSettings();
        $event_params = array(
            'value' => $data['value'],
            'currency' => $settings['currency'],
            'items' => array($data['items'][0])
        );
        $params = $this->getDefaultParameters($data, $settings, $event_params);
        $params['log'] = $this->createRequest($params, $settings);
        $return['log'] = $this->addLog($params, $settings);
        return $return;
    }

    public function view_cart($data) {
        return $this->view_item_list($data);
    }

    public function select_item($data) {
        ini_set('serialize_precision', -1);
        $return = array();
        $settings = $this->getSettings();
        $this->load->language($this->ePath);
        $return['type'] = $settings['type'];
        if (!isset($data['list']) && isset($data['url'])) {
            $list_data = $this->getListByUrl($data['url'], $data['item_id']);
            $data = array_merge($data, $list_data);
        }
        $event_params = array();
        if (isset($data['list'])) {
            $data['item_list_name'] = $this->language->get('text_' . $data['list']);
            if ($data['item_list_name'] == 'text_' . $data['list']) {
                $data['item_list_name'] = str_replace('_', ' ', ucwords($data['list'], '_'));
            }
            $data['item_list_id'] = $data['list'];
            if (isset($data['list_id'])) {
                if ($data['list'] == 'category' && $settings['extended_list']) {
                    $data['item_list_name'] = $this->getCategoryName($data['list_id'], $settings['language_id']);
                    $data['item_list_id'] = 'category_id_' . $data['list_id'];
                } elseif ($data['list'] == 'manufacturer_info' && $settings['extended_list']) {
                    $data['item_list_name'] = $this->getManufacturerName($data['list_id'], $settings['language_id']);
                    $data['item_list_id'] = 'manufacturer_id_' . $data['list_id'];
                } elseif ($data['list'] == 'search' && $settings['extended_list']) {
                    $data['item_list_id'] = $data['list_id'];
                } elseif ($data['list'] == 'module_products' || $data['list'] == 'module_side_products') {
                    $this->load->model('journal3/module');
                    $module_id = explode('_', $data['list_id']);
                    $module_data = $this->model_journal3_module->get($module_id[0], str_replace('module_', '', $data['list']));
                    if (isset($module_data['items'])) {
                        foreach ($module_data['items'] as $key => $module_item) {
                            if (isset($module_id[1]) && $module_id[1] == $key) {
                                if (isset($module_item['title']['lang_' . $settings['language_id']]) && $module_item['title']['lang_' . $settings['language_id']]) {
                                    $data['item_list_name'] = $module_item['title']['lang_' . $settings['language_id']];
                                } else {
                                    $data['item_list_name'] = $module_item['name'];
                                }
                            }
                        }
                    }
                } elseif ($data['list'] == 'module_custom_sections_product' || $data['list'] == 'module_carousel_product') {
                    $this->load->model('journal2/module');
                    $module_id = explode('_', $data['list_id']);
                    $module_data = $this->model_journal2_module->getModule($module_id[0]);
                    if (isset($module_data['module_data']['product_sections'])) {
                        foreach ($module_data['module_data']['product_sections'] as $key => $module_item) {
                            if (isset($module_id[1]) && $module_id[1] == $key && isset($module_item['section_title']['value'][$settings['language_id']])) {
                                $data['item_list_name'] = $module_item['section_title']['value'][$settings['language_id']];
                            }
                        }
                    }
                }
            }
            $event_params['item_list_name'] = $data['item_list_name'];
            $event_params['item_list_id'] = $data['item_list_id'];
        }
        $product_params = $this->getItemParameters($data['item_id'], $settings, $data);
        if ($product_params) {
            $data['items'][] = $product_params;
            $event_params['items'] = array($product_params);
            $params = array();
            switch ($return['type']) {
                case 0: /** gmp */
                    $params = $this->getDefaultParameters($data, $settings, $event_params);
                    $params['log'] = $this->createRequest($params, $settings);
                    break;
                case 1: /** gtag */
                    $return['event'] = $data['event'];
                    if ($settings['debug_mode']) {
                        $return['params']['debug_mode'] = true;
                    }
                    $return['params']['items'] = $data['items'];
                    if (isset($data['list'])) {
                        $return['params']['item_list_name'] = $data['item_list_name'];
                        $return['params']['item_list_id'] = $data['item_list_id'];
                    }
                    break;
                case 2: /** gtm */
                    $return['params'] = array(
                        'event' => $data['event'],
                        'ecommerce' => array(
                            'items' => $data['items']
                        )
                    );
                    break;
            }
            if ($settings['log']) {
                if (!$params) {
                    $params = $this->getDefaultParameters($data, $settings, $event_params);
                }
                $return['log'] = $this->addLog($params, $settings);
            }
        } else {
            $return['log'] = $this->error('product_not_found', $data['event']);
        }
        return $return;
    }

    public function select_promotion($data) {
        $return = array();
        $settings = $this->getSettings();
        $return['type'] = $settings['type'];
        $product_params = $this->getItemParameters($data['item_id'], $settings, $data);
        if ($product_params) {
            $event_params = array();
            $journal_theme = strpos($data['image_id'], '-');
            if ($journal_theme !== false) {
                $module_id = explode('-', $data['image_id']);
                if (isset($module_id[0])) {
                    $event_params = array(
                        'location_id' => $module_id[0],
                        'promotion_id' => $data['promotion_id']
                    );
                    $product_params['creative_slot'] = $data['image_id'];
                    switch ($data['promotion_id']) {
                        case 'slider_simple':
                        case 'slider_advanced':
                        case 'static_banners':
                        $this->load->model('journal2/module');
                        $module_data = $this->model_journal2_module->getModule($module_id[0]);
                        if ($module_data) {
                            if (!$settings['promotion_simple'] && isset($module_data['module_data']['module_name'])) {
                                $event_params['promotion_name'] = $module_data['module_data']['module_name'];
                            } else {
                                $event_params['promotion_name'] = $data['promotion_id'];
                            }
                            if (isset($module_data['module_data']['slides'][$module_id[1]]['slide_name'])) {
                                $product_params['creative_name'] = $module_data['module_data']['slides'][$module_id[1]]['slide_name'];
                            } elseif (isset($module_data['module_data']['sections'][$module_id[1]]['slide_name'])) {
                                $product_params['creative_name'] = $module_data['module_data']['sections'][$module_id[1]]['slide_name'];
                            } else {
                                $product_params['creative_name'] = $module_data['module_type'];
                            }
                        }
                            break;
                        case 'banners':
                        case 'master_slider':
                            if (!$settings['promotion_simple']) {
                                $this->load->model('journal3/module');
                                $module_data = $this->model_journal3_module->get($module_id[0], $data['promotion_id']);
                                if ($module_data) {
                                    $event_params['promotion_name'] = isset($module_data['general']['name']) ? $module_data['general']['name'] : '';
                                    $product_params['creative_name'] = isset($module_data['items'][$data['index'] - 1]) ? $module_data['items'][$data['index'] - 1]['name'] : '';
                                }
                            } else {
                                $event_params['promotion_name'] = $data['promotion_id'] . '_' . $module_id[0];
                            }
                        break;
                    }
                }
            } else {
                $banner_image = $this->getBannerImage($data['image_id'], $settings['language_id']);
                if ($banner_image) {
                    $product_params['creative_name'] = $banner_image['title'];
                    $product_params['creative_slot'] = $data['image_id'];
                    $event_params['location_id'] = 'banner_id' . '_' . $banner_image['banner_id'];
                    $event_params['promotion_name'] = $banner_image['name'];
                    if (isset($data['promotion_id'])) {
                        $event_params['promotion_id'] = $data['promotion_id'];
                    }
                } else {
                    $return['log'] = $this->error('banner_not_found', $data['event']);
                    return $return;
                }
            }
            $params = array();
            $data['items'][] = $product_params;
            $event_params['items'] = $data['items'];
            switch ($return['type']) {
                case 0: { /** gmp */
                    $params = $this->getDefaultParameters($data, $settings, $event_params);
                    $params['log'] = $this->createRequest($params, $settings);
                    break;
                }
                case 1: { /** gtag */
                    $return['event'] = $data['event'];
                    if ($settings['debug_mode']) {
                        $return['params']['debug_mode'] = true;
                    }
                    if (isset($event_params['promotion_id'])) {
                        $return['params']['promotion_id'] = $event_params['promotion_id'];
                    }
                    $return['params']['promotion_name'] = $event_params['promotion_name'];
                    $return['params']['location_id'] = $event_params['location_id'];
                    $return['params']['items'] = $data['items'];
                    break;
                }
                case 2: { /** gtm */
                    $return['params'] = array(
                        'event' => $data['event'],
                        'ecommerce' => array(
                            'items' => $data['items']
                        )
                    );
                    break;
                }
            }
            if ($settings['log']) {
                if (!$params) {
                    $params = $this->getDefaultParameters($data, $settings, $event_params);
                }
                $return['log'] = $this->addLog($params, $settings);
            }
        } else {
            $return['log'] = $this->error('product_not_found', $data['event']);
        }
        return $return;
    }

    public function add_to_wishlist($data, $server_side = 0) {
        ini_set('serialize_precision', -1);
        $return = array();
        $settings = $this->getSettings();
        $this->load->language($this->ePath);
        if ($settings['wish_status']) {
            $event_name = strtoupper(str_replace('_', ' ', $data['event']));
            if (!isset($data['item_id']) || !is_numeric($data['item_id'])) {
                $return['log'] = $this->error('product_id_not_found', $event_name);
                return $return;
            }
            if (!isset($data['list']) && isset($data['url'])) {
                $list_data = $this->getListByUrl($data['url'], $data['item_id']);
                $data = array_merge($data, $list_data);
            }
            if (isset($data['list'])) {
                $data['item_list_name'] = $this->language->get('text_' . $data['list']);
                if ($data['item_list_name'] == 'text_' . $data['list']) {
                    $data['item_list_name'] = str_replace('_', ' ', ucwords($data['list'], '_'));
                }
                $data['item_list_id'] = $data['list'];
                if (isset($data['list_id'])) {
                    if ($data['list'] == 'category' && $settings['extended_list']) {
                        $data['item_list_name'] = $this->getCategoryName($data['list_id'], $settings['language_id']);
                        $data['item_list_id'] = 'category_id_' . $data['list_id'];
                    } elseif ($data['list'] == 'manufacturer_info' && $settings['extended_list']) {
                        $data['item_list_name'] = $this->getManufacturerName($data['list_id'], $settings['language_id']);
                        $data['item_list_id'] = 'manufacturer_id_' . $data['list_id'];
                    } elseif ($data['list'] == 'search' && $settings['extended_list']) {
                        $data['item_list_id'] = $data['list_id'];
                    } elseif ($data['list'] == 'module_products' || $data['list'] == 'module_side_products') {
                        $this->load->model('journal3/module');
                        $module_id = explode('_', $data['list_id']);
                        $module_data = $this->model_journal3_module->get($module_id[0], str_replace('module_', '', $data['list']));
                        if (isset($module_data['items'])) {
                            foreach ($module_data['items'] as $key => $module_item) {
                                if (isset($module_id[1]) && $module_id[1] == $key) {
                                    if (isset($module_item['title']['lang_' . $settings['language_id']]) && $module_item['title']['lang_' . $settings['language_id']]) {
                                        $data['item_list_name'] = $module_item['title']['lang_' . $settings['language_id']];
                                    } else {
                                        $data['item_list_name'] = $module_item['name'];
                                    }
                                }
                            }
                        }
                    } elseif ($data['list'] == 'module_custom_sections_product' || $data['list'] == 'module_carousel_product') {
                        $this->load->model('journal2/module');
                        $module_id = explode('_', $data['list_id']);
                        $module_data = $this->model_journal2_module->getModule($module_id[0]);
                        if (isset($module_data['module_data']['product_sections'])) {
                            foreach ($module_data['module_data']['product_sections'] as $key => $module_item) {
                                if (isset($module_id[1]) && $module_id[1] == $key && isset($module_item['section_title']['value'][$settings['language_id']])) {
                                    $data['item_list_name'] = $module_item['section_title']['value'][$settings['language_id']];
                                }
                            }
                        }
                    }
                }
            }
            $return['type'] = $settings['type'];
            if ($settings['type'] == 0 && !$server_side && $data['event'] == 'add_to_wishlist' && $settings['wish_add']) {
                if ($settings['log']) {
                    $return['log'] = array(
                        'text' => $event_name . ': ' . $this->language->get('text_server_side_tracking'),
                        'show' => isset($this->session->data['user_id'])
                    );
                } else {
                    $return = $this->language->get('text_server_side_tracking');
                }
                return $return;
            }
            $product_params = $this->getItemParameters($data['item_id'], $settings, $data);
            if ($product_params) {
                $params = array();
                $data['items'][] = $product_params;
                $event_params = array(
                    'currency' => $product_params['currency'],
                    'items' => $data['items'],
                    'value' => $product_params['price']
                );
                switch ($return['type']) {
                    case 0: /** gmp */
                        $params = $this->getDefaultParameters($data, $settings, $event_params);
                        $params['log'] = $this->createRequest($params, $settings);
                        break;
                    case 1: /** gtag */
                        $return['event'] = $data['event'];
                        if ($settings['debug_mode']) {
                            $return['params']['debug_mode'] = true;
                        }
                        $return['params']['currency'] = $product_params['currency'];
                        $return['params']['items'] = $data['items'];
                        $return['params']['value'] = $product_params['price'];
                        break;
                    case 2: /** gtm */
                        $return['params'] = array(
                            'event' => $data['event'],
                            'ecommerce' => array(
                                'items' => $data['items']
                            )
                        );
                        break;
                }
                if ($settings['log']) {
                    if (!$params) {
                        $params = $this->getDefaultParameters($data, $settings, $event_params);
                    }
                    $return['log'] = $this->addLog($params, $settings);
                }
            } else {
                $return['log'] = $this->error('product_not_found', $event_name);
            }
        } else {
            $return = $this->language->get('text_cart_event_disabled');
        }
        return $return;
    }

    public function add_to_cart($data, $server_side = 0) {
        return $this->cart($data, $server_side);
    }

    public function remove_from_cart($data, $server_side = 0) {
        return $this->cart($data, $server_side);
    }

    public function cart($data, $server_side = 0) {
        ini_set('serialize_precision', -1);
        $return = array();
        $settings = $this->getSettings();
        $this->load->language($this->ePath);
        if ($settings['cart_status']) {
            $event_name = strtoupper(str_replace('_', ' ', $data['event']));
            if (!isset($data['item_id']) || !is_numeric($data['item_id'])) {
                $return['log'] = $this->error('product_id_not_found', $event_name);
                return $return;
            }
            if (!isset($data['list']) && isset($data['url'])) {
                $list_data = $this->getListByUrl($data['url'], $data['item_id']);
                $data = array_merge($data, $list_data);
            }
            if (isset($data['list']) && ($data['event'] == 'add_to_cart' || $data['list'] == 'checkout_cart')) {
                $data['item_list_name'] = $this->language->get('text_' . $data['list']);
                if ($data['item_list_name'] == 'text_' . $data['list']) {
                    $data['item_list_name'] = str_replace('_', ' ', ucwords($data['list'], '_'));
                }
                $data['item_list_id'] = $data['list'];
                if (isset($data['list_id'])) {
                    if ($data['list'] == 'category' && $settings['extended_list']) {
                        $data['item_list_name'] = $this->getCategoryName($data['list_id'], $settings['language_id']);
                        $data['item_list_id'] = 'category_id_' . $data['list_id'];
                    } elseif ($data['list'] == 'manufacturer_info' && $settings['extended_list']) {
                        $data['item_list_name'] = $this->getManufacturerName($data['list_id'], $settings['language_id']);
                        $data['item_list_id'] = 'manufacturer_id_' . $data['list_id'];
                    } elseif ($data['list'] == 'search' && $settings['extended_list']) {
                        $data['item_list_id'] = $data['list_id'];
                    } elseif ($data['list'] == 'module_products' || $data['list'] == 'module_side_products') {
                        $this->load->model('journal3/module');
                        $module_id = explode('_', $data['list_id']);
                        $module_data = $this->model_journal3_module->get($module_id[0], str_replace('module_', '', $data['list']));
                        if (isset($module_data['items'])) {
                            foreach ($module_data['items'] as $key => $module_item) {
                                if (isset($module_id[1]) && $module_id[1] == $key) {
                                    if (isset($module_item['title']['lang_' . $settings['language_id']]) && $module_item['title']['lang_' . $settings['language_id']]) {
                                        $data['item_list_name'] = $module_item['title']['lang_' . $settings['language_id']];
                                    } else {
                                        $data['item_list_name'] = $module_item['name'];
                                    }
                                }
                            }
                        }
                    } elseif ($data['list'] == 'module_custom_sections_product' || $data['list'] == 'module_carousel_product') {
                        $this->load->model('journal2/module');
                        $module_id = explode('_', $data['list_id']);
                        $module_data = $this->model_journal2_module->getModule($module_id[0]);
                        if (isset($module_data['module_data']['product_sections'])) {
                            foreach ($module_data['module_data']['product_sections'] as $key => $module_item) {
                                if (isset($module_id[1]) && $module_id[1] == $key && isset($module_item['section_title']['value'][$settings['language_id']])) {
                                    $data['item_list_name'] = $module_item['section_title']['value'][$settings['language_id']];
                                }
                            }
                        }
                    }
                }
            }
            if (isset($data['option'])) {
                $options = array();
                foreach ($data['option'] as $item) {
                    preg_match('/\d+/', $item['name'], $matches);
                    if (isset($matches[0])) {
                        $options[$matches[0]] = $item['value'];
                    }
                }
                $data['option'] = $options;
            } elseif (isset($data['variant'])) {
                $data['option'] = $data['variant'];
            }
            if ($data['event'] == 'add_to_cart') {
                $product_options = $this->getProductRequiredOptions($data['item_id']);
                foreach ($product_options as $product_option) {
                    if (!isset($data['option']) || (!isset($data['variant']) && empty($data['option'][$product_option['product_option_id']]))) {
                        if (isset($data['list']) && $settings['select_status']) {
                            $data['event'] = 'select_item';
                            return $this->select_item($data);
                        } else {
                            if ($settings['log']) {
                                $return['log'] = array(
                                    'text' => $event_name . ': ' . $this->language->get('text_option_not_selected'),
                                    'show' => isset($this->session->data['user_id'])
                                );
                            } else {
                                $return = $this->language->get('text_option_not_selected');
                            }
                            return $return;
                        }
                    }
                }
            }
            $return['type'] = $settings['type'];
            if ($settings['type'] == 0 && !$server_side) {
                if (isset($data['cart_edit']) && $data['cart_edit']) {
                    if ($settings['cart_edit']) {
                        if ($settings['log']) {
                            $return['log'] = array(
                                'text' => $event_name . ': ' . $this->language->get('text_server_side_tracking'),
                                'show' => isset($this->session->data['user_id'])
                            );
                        } else {
                            $return = $this->language->get('text_server_site_tracking');
                        }
                        return $return;
                    }
                } else {
                    if ($data['event'] == 'add_to_cart' && $settings['cart_add'] || $data['event'] == 'remove_from_cart' && $settings['cart_remove']) {
                        if ($settings['log']) {
                            $return['log'] = array(
                                'text' => $event_name . ': ' . $this->language->get('text_server_side_tracking'),
                                'show' => isset($this->session->data['user_id'])
                            );
                        } else {
                            $return = $this->language->get('text_server_side_tracking');
                        }
                        return $return;
                    }
                }
            }
            $product_params = $this->getItemParameters($data['item_id'], $settings, $data);
            if ($product_params) {
                $params = array();
                $data['items'][] = $product_params;
                $event_params = array(
                    'currency' => $product_params['currency'],
                    'items' => $data['items'],
                    'value' => $product_params['price']
                );
                switch ($return['type']) {
                    case 0: /** gmp */
                        $params = $this->getDefaultParameters($data, $settings, $event_params);
                        $params['log'] = $this->createRequest($params, $settings);
                        break;
                    case 1: /** gtag */
                        $return['event'] = $data['event'];
                        if ($settings['debug_mode']) {
                            $return['params']['debug_mode'] = true;
                        }
                        $return['params']['currency'] = $product_params['currency'];
                        $return['params']['items'] = $data['items'];
                        $return['params']['value'] = $product_params['price'];
                        break;
                    case 2: /** gtm */
                        $return['params'] = array(
                            'event' => $data['event'],
                            'ecommerce' => array(
                                'currency' => $product_params['currency'],
                                'value' => $product_params['price'],
                                'items' => $data['items']
                            )
                        );
                        break;
                }
                if ($settings['log']) {
                    if (!$params) {
                        $params = $this->getDefaultParameters($data, $settings, $event_params);
                    }
                    $return['log'] = $this->addLog($params, $settings);
                }
            } else {
                $return['log'] = $this->error('product_not_found', $event_name);
            }
        } else {
            $return = $this->language->get('text_cart_event_disabled');
        }
        return $return;
    }

    public function begin_checkout($data) {
        return $this->checkout($data);
    }

    public function add_shipping_info($data) {
        return $this->checkout($data);
    }

    public function add_payment_info($data) {
        return $this->checkout($data);
    }

    public function checkout($data) {
        $return = array();
        $settings = $this->getSettings();
        if ($settings['checkout_status']) {
            if (isset($data['items'])) {
                $cart_data = array(
                    'currency' => $data['currency'],
                    'value' => $data['value'],
                    'items' => $data['items']
                );
                if (isset($data['coupon'])) {
                    $cart_data['coupon'] = $data['coupon'];
                }
                if (isset($data['shipping_tier'])) {
                    $cart_data['shipping_tier'] = $data['shipping_tier'];
                } elseif (isset($data['payment_type'])) {
                    $cart_data['payment_type'] = $data['payment_type'];
                }
            } else {
                $cart_data = $this->getCartProducts();
                if ($data['event'] == 'add_payment_info') {
                    $cart_data['payment_type'] = $this->getPaymentType($data['code']);
                } elseif ($data['event'] == 'add_shipping_info') {
                    $cart_data['shipping_tier'] = $this->getShippingTier($data['code']);
                }
            }
            if ($cart_data) {
                $params = array();
                switch ($settings['type']) {
                    case 0: { /** gmp */
                        $params = $this->getDefaultParameters($data, $settings, $cart_data);
                        $params['log'] = $this->createRequest($params, $settings);
                        break;
                    }
                    case 1: { /** gtag */
                        if ($settings['debug_mode']) {
                            $cart_data['debug_mode'] = true;
                        }
                        $return = array(
                            'event' => $data['event'],
                            'params' => $cart_data
                        );
                        break;
                    }
                    case 2: { /** gtm */
                        $return['params'] = array(
                            'event' => $data['event'],
                            'ecommerce' => $cart_data
                        );
                        break;
                    }
                }
                $return['type'] = $settings['type'];
                if ($settings['log']) {
                    if (!$params) {
                        $params = $this->getDefaultParameters($data, $settings, $cart_data);
                    }
                    $return['log'] = $this->addLog($params, $settings);
                }
            }
        }
        return $return;
    }

    public function purchase($data) {
        $return = array();
        if (isset($data['order_id'])) {
            $order_info = $this->getOrder($data['order_id']);
            if ($order_info) {
                $data['date_added'] = $order_info['date_added'];
                $data['customer_id'] = $order_info['customer_id'];
                $settings = $this->getSettings($order_info['store_id']);
                if ($settings['purchase_status']) {
                    if (isset($data['items'])) {
                        $purchase_data = array(
                            'transaction_id' => $data['transaction_id'],
                            'affiliation' => $data['affiliation'],
                            'value' => $data['value'],
                            'tax' => $data['tax'],
                            'shipping' => $data['shipping'],
                            'currency' => $data['currency'],
                            'items' => $data['items']
                        );
                        if (isset($data['coupon'])) {
                            $purchase_data['coupon'] = $data['coupon'];
                        }
                    } else {
                        $purchase_data = $this->getOrderParameters($data['order_id'], $settings);
                        unset($purchase_data['order_id']);
                        foreach ($purchase_data['items'] as $key => $item) {
                            unset($purchase_data['items'][$key]['order_product_id']);
                            unset($purchase_data['items'][$key]['product_id']);
                            unset($purchase_data['items'][$key]['refund_quantity']);
                            unset($purchase_data['items'][$key]['options']);
                        }
                    }
                    if ($purchase_data) {
                        $params = array();
                        switch ($settings['type']) {
                            case 0: { /** gmp */
                                $params = $this->getDefaultParameters($data, $settings, $purchase_data);
                                $params['log'] = $this->createRequest($params, $settings);
                                break;
                            }
                            case 1: { /** gtag */
                                if ($settings['debug_mode']) {
                                    $cart_data['debug_mode'] = true;
                                }
                                $return = array(
                                    'event' => $data['event'],
                                    'params' => $purchase_data
                                );
                                break;
                            }
                            case 2: { /** gtm */
                                $return['params'] = array(
                                    'event' => $data['event'],
                                    'ecommerce' => $purchase_data
                                );
                                break;
                            }
                        }
                        $return['type'] = $settings['type'];
                        if ($settings['log']) {
                            if (!$params) {
                                $params = $this->getDefaultParameters($data, $settings, $purchase_data);
                            }
                            $return['log'] = $this->addLog($params, $settings);
                        }
                    }
                }
            }
        }
        return $return;
    }

    public function error($data, $prefix = '') {
        $return = array();
        $settings = $this->getSettings();
        if ($settings['log'] && $data) {
            $record = '';
            $this->load->language($this->ePath);
            if (is_array($data)) {
                if (isset($data['code'])) {
                    $record = $this->language->get('error_' . $data['code']);
                    if (isset($data['sprintf'])) {
                        $record = sprintf($record, $data['sprintf']);
                    }
                }
                if (isset($data['prefix'])) {
                    $prefix = $data['prefix'];
                }
            } else {
                $record = $this->language->get('error_' . $data);
            }
            if (!$prefix) {
                $prefix = $this->language->get('error_e_tracking_missed');
            }
            $record = strtoupper(str_replace('_', ' ', $prefix)) . ' / ' . $record;
            $return = $this->writeLog($record, $settings['store_id']);
        }
        return $return;
    }


    public function getSettings($store_id = -1, $data = array()) {
        if ($store_id == -1) {
            $store_id = $this->config->get('config_store_id');
        }
        if ($this->config->get($this->eName . '_multi_store')) {
            $settings_store_id = $store_id;
        } else {
            $settings_store_id = 0;
        }
        $store_settings = $this->cache->get($this->eName . '.' . $settings_store_id);
        if (!$store_settings) {
            $this->load->model('setting/setting');
            $settings = $this->model_setting_setting->getSetting($this->eName, $settings_store_id);
            if ($settings) {
                foreach ($settings as $key => $value) {
                    $store_settings[str_replace($this->eName . '_', '', $key)] = $value;
                }
                $this->cache->set($this->eName . '.' . $settings_store_id, $store_settings);
            } else {
                $store_settings = array(
                    'status' => 0,
                    'tax' => $this->config->get('config_tax')
                );
            }
        }
        if (($this->config->get($this->eName . '_multi_store') && (!isset($store_settings['affiliation']) || !$store_settings['affiliation'])) || (!$this->config->get($this->eName . '_multi_store') && ($store_id || (!$store_id && (!isset($store_settings['affiliation']) || !$store_settings['affiliation']))))) {
            $store_settings['affiliation'] = $this->config->get('config_name');
        }
        if (!isset($store_settings['language_id']) || !$store_settings['language_id']) {
            if (isset($data['language_id'])) {
                $store_settings['language_id'] = $data['language_id'];
            } else {
                $store_settings['language_id'] = $this->config->get('config_language_id');
            }
        }
        if ($store_settings['language_id'] == $this->config->get('config_language_id')) {
            $store_settings['active_language'] = true;
        } else {
            $store_settings['active_language'] = false;
        }
        if (!isset($store_settings['currency']) || !$store_settings['currency']) {
            if (isset($data['currency_code'])) {
                $store_settings['currency'] = $data['currency_code'];
            } else {
                $store_settings['currency'] = $this->session->data['currency'];
            }
            if (isset($data['currency_value'])) {
                $store_settings['currency_value'] = $data['currency_value'];
            }
        } elseif (isset($data['currency_value']) && isset($data['currency_code']) && $store_settings['currency'] == $data['currency_code']) {
            $store_settings['currency_value'] = $data['currency_value'];
        }
        if (!isset($store_settings['currency_value'])) {
            $store_settings['currency_value'] = 0;
        }
        if ($store_settings['currency'] == $this->session->data['currency']) {
            $store_settings['active_currency'] = true;
        } else {
            $store_settings['active_currency'] = false;
        }
        if ($store_settings['tax'] == $this->config->get('config_tax')) {
            $store_settings['active_tax'] = true;
        } else {
            $store_settings['active_tax'] = false;
        }
        $store_settings['store_id'] = $settings_store_id;
        if (!isset($store_settings['purchase_simple'])) {
            $store_settings['purchase_simple'] = 0;
        }
        if (isset($store_settings['checkout_custom'])) {
            $store_settings['checkout_custom'] = trim($store_settings['checkout_custom']) ? array_map('trim', explode(',', $store_settings['checkout_custom'])) : array();
        }
        return $store_settings;
    }

    public function getDefaultParameters($data, $settings, $params = array()) {
        $result = array();
        if (!isset($data['client_id']) || !$data['client_id']) {
            $data['client_id'] = $this->getClientId();
        }
        $result['client_id'] = $data['client_id'];
        if ($settings['timestamp'] && $data['event'] == 'purchase') {
            $query = $this->db->query("SELECT NOW() as time");
            if (isset($query->row['time'])) {
                $time_now = strtotime($query->row['time']);
            } else {
                $time_now = time();
            }
            $queue_time = $time_now - strtotime($data['date_added']);
            if ($queue_time > 10 && $queue_time < 250000) {
                $result['timestamp_micros'] = strtotime($data['date_added']) * 1000000;
            }
        }
        if (isset($settings['non_personalized_ads']) && $settings['non_personalized_ads']) {
            $result['non_personalized_ads'] = true;
        }
        if ($settings['debug_mode'] && ($settings['type'] || ($settings['type'] == 0 && $settings['validation_mode'] == 0))) {
            $params['debug_mode'] = true;
        }
        if ($this->internalTraffic($settings)) {
            $params['traffic_type'] = $settings['traffic_type'];
        }
        if (!isset($data['customer_id']) || !$data['customer_id']) {
            $data['customer_id'] = $this->customer->isLogged() ? $this->customer->getId() : 0;
        }
        if ($data['customer_id']) {
            $customer_info = array();
            if ($settings['user_id']) {
                if ($settings['user_id'] == 'customer_id') {
                    $result['user_id'] = $data['customer_id'];
                } else {
                    $customer_info = $this->getCustomer($data['customer_id']);
                    if (isset($customer_info[$settings['user_id']]) && $customer_info[$settings['user_id']]) {
                        $result['user_id'] = $customer_info[$settings['user_id']];
                    }
                }
            }
            if (isset($settings['custom_definition']) && in_array(0, array_column($settings['custom_definition'], 'object'))) {
                if (!$customer_info) {
                    $customer_info = $this->getCustomer($data['customer_id']);
                }
                foreach ($settings['custom_definition'] as $custom_definition) {
                    if ($custom_definition['object'] == 0 && isset($customer_info[$custom_definition['value']])) {
                        $result['user_properties'][$custom_definition['name']]['value'] = $customer_info[$custom_definition['value']];
                    }
                }
            }
        }
        $result['events'] = array(
            'name' => $data['event'],
            'params' => $params
        );
        return $result;
    }

    public function convertDefaultParameters($data) {
        $result = array(
            'v' => 2,
            'tid' => '',
            'cid' => $data['client_id'],
            'en' => $data['events']['name']
        );
        $params = $this->convertOrderParameters($data['events']['params']);
        $result = array_merge($result, $params);

        if (isset($data['user_id'])) {
            $result['uid'] = $data['user_id'];
        }
        if (isset($data['timestamp_micros'])) {
            $result['epn.timestamp_micros'] = $data['timestamp_micros'];
        }
        if (isset($data['non_personalized_ads'])) {
            $result['ep.non_personalized_ads'] = $data['non_personalized_ads'];
        }
        if (isset($data['events']['params']['debug_mode'])) {
            $result['ep.debug_mode'] = true;
            $result['_dbg'] = 1;
        }
        if (isset($data['user_properties'])) {
            foreach ($data['user_properties'] as $name => $item) {
                $result['up.' . $name] = $item['value'];
            }
        }
        if (isset($data['events']['params']['traffic_type'])) {
            $result['tt'] = $data['events']['params']['traffic_type'];
        }
        return $result;
    }

    public function getItemParameters($product_id, $settings = array(), $data = array()) {
        if (!$settings) {
            $settings = $this->getSettings();
        }
        $return = array();
        $product_info = $this->getProduct($product_id, $settings['language_id']);
        if ($product_info) {
            $return = array(
                'item_id' => $product_id,
                'item_name' => $product_info['name'],
                'quantity' => $product_info['minimum'] > 1 ? $product_info['minimum'] : 1,
                'affiliation' => $settings['affiliation'],
                'currency' => $settings['currency']
            );
            if ($settings['product_id'] && isset($product_info[$settings['product_id']]) && $product_info[$settings['product_id']]) {
                $return['item_id'] = $product_info[$settings['product_id']];
            }
            if (isset($data['quantity']) && (int)$data['quantity']) {
                $return['quantity'] = (int)$data['quantity'];
            }
            if (isset($data['coupon'])) {
                $return['coupon'] = $data['coupon'];
            }
            if (isset($data['discount'])) {
                $return['discount'] = $data['discount'];
            }
            if (isset($data['item_list_id'])) {
                $return['item_list_id'] = $data['item_list_id'];
            }
            if (isset($data['item_list_name'])) {
                $return['item_list_name'] = $data['item_list_name'];
            }
            if (isset($data['index'])) {
                $return['index'] = $data['index'];
            }
            if ($product_info['manufacturer']) {
                $return['item_brand'] = $product_info['manufacturer'];
            }
            if (isset($data['category']) && $data['category']) {
                $return['item_category'] = $data['category'];
            } else {
                $categories = $this->getProductCategories($product_id, $settings);

                foreach ($categories as $key => $category) {
                    if ($key) {
                        $return['item_category' . ($key + 1)] = $category;
                    } else {
                        $return['item_category'] = $category;
                    }
                }
            }
            if (isset($data['price'])) {
                if ($settings['tax']) {
                    if (isset($data['tax'])) {
                        $data['price'] = $data['price'] + $data['tax'];
                    } elseif (isset($data['tax_class_id'])) {
                        $data['price'] = $this->tax->calculate($data['price'], $data['tax_class_id']);
                    }
                }
                $return['price'] = $this->currency->format($data['price'], $settings['currency'], 0, false);
            } elseif ((float)$product_info['special']) {
                if ($settings['tax']) {
                    $product_info['special'] = $this->tax->calculate($product_info['special'], $product_info['tax_class_id']);
                }
                $return['price'] = $this->currency->format($product_info['special'], $settings['currency'], 0, false);
            } else {
                if ($settings['tax']) {
                    $product_info['price'] = $this->tax->calculate($product_info['price'], $product_info['tax_class_id']);
                }
                $return['price'] = $this->currency->format($product_info['price'], $settings['currency'], 0, false);
            }
            if (isset($data['option'])) {
                $product_options = $this->getProductOptions($product_id, $settings['language_id']);
                $options = array();
                $option_price = 0;
                foreach ($product_options as $product_option) {
                    if (isset($product_option['product_option_value']) && is_array($product_option['product_option_value'])) {
                        foreach ($product_option['product_option_value'] as $option_value) {
                            if (isset($data['option'][$product_option['product_option_id']])) {
                                if (($option_value['product_option_value_id'] == $data['option'][$product_option['product_option_id']]) || is_array($data['option'][$product_option['product_option_id']]) && in_array($option_value['product_option_value_id'], $data['option'][$product_option['product_option_id']])) {
                                    if (isset($options[$product_option['product_option_id']])) {
                                        $options[$product_option['product_option_id']] .= ',' . $option_value['name'];
                                    } else {
                                        $options[$product_option['product_option_id']] = $product_option['name'] . ':' . $option_value['name'];
                                    }
                                    if ($option_value['price_prefix'] == '+') {
                                        $option_price = $option_price + $option_value['price'];
                                    } else if ($option_value['price_prefix'] == '-') {
                                        $option_price = $option_price - $option_value['price'];
                                    }
                                }
                            }
                        }
                    }
                }
                if ($options) {
                    $return['item_variant'] = implode("/", $options);
                    if ($settings['tax']) {
                        $option_price = $this->tax->calculate($option_price, $product_info['tax_class_id']);
                    }
                    $option_price = $this->currency->format($option_price, $settings['currency'], 0, false);
                    $return['price'] = (float)$return['price'] + (float)$option_price;
                }
            }
        }
        if (isset($settings['custom_definition']) && in_array(2, array_column($settings['custom_definition'], 'object'))) {
            foreach ($settings['custom_definition'] as $custom_definition) {
                if ($custom_definition['object'] == 2 && isset($product_info[$custom_definition['value']])) {
                    if ($settings['type']) {
                        $return['custom_definition'][$custom_definition['name']] = $product_info[$custom_definition['value']];
                    } else {
                        $return[$custom_definition['name']] = $product_info[$custom_definition['value']];
                    }
                }
            }
        }
        $return['price'] = number_format($return['price'], 2, '.', '');
        return $return;
    }

    public function getOrderParameters($order_id, $settings) {
        $order_values = $this->getOrderValues($order_id, $settings);
        $result = array(
            'order_id'       => $order_id,
            'transaction_id' => $order_id,
            'affiliation'    => $settings['affiliation'],
            'value'          => $order_values['value'],
            'tax'            => $order_values['tax'],
            'shipping'       => $order_values['shipping'],
            'currency'       => $settings['currency'],
            'items'          => array()
        );
        $order_products = $this->getOrderProducts($order_id);
        /** Order Coupon */
        if (isset($order_values['coupon'])) {
            $result['coupon'] = $order_values['coupon'];

            if (preg_match_all("/\(([^\)]*)\)/", $order_values['coupon'], $matches)) {
                $coupon_info = $this->getCoupon(array_shift($matches[1]), $order_products);

                if ($coupon_info) {
                    $result['coupon'] = $coupon_info['coupon'];
                    $settings['coupon_product_count'] = 0;
                    foreach ($order_products as $key => $order_product) {
                        if (!$coupon_info['product'] || in_array($order_product['product_id'], $coupon_info['product'])) {
                            $settings['coupon_product_count'] += $order_product['quantity'];
                            $order_products[$key]['coupon'] = $coupon_info;
                        }
                    }
                }
            }

        }
        /** Order Coupon */
        /** Custom Definitions Order */
        if (isset($settings['custom_definition']) && in_array(1, array_column($settings['custom_definition'], 'object'))) {
            $order_info = $this->getOrder($order_id);
            foreach ($settings['custom_definition'] as $custom_definition) {
                if ($custom_definition['object'] == 1 && isset($order_info[$custom_definition['value']])) {
                    if ($settings['type']) {
                        $result['custom_definition'][$custom_definition['name']] = $order_info[$custom_definition['value']];
                    } else {
                        $result[$custom_definition['name']] = $order_info[$custom_definition['value']];
                    }
                }
            }
        }
        /** Custom Definitions Order */
        /** Order Products */
        foreach ($order_products as $order_product) {
            $item_data = array(
                'order_product_id' => $order_product['order_product_id'],
                'product_id' => $order_product['product_id'],
                'refund_quantity' => $order_product['refund_quantity'],
                'item_id' => $order_product['product_id'],
                'item_name' => $order_product['name'],
                'quantity' => (int)$order_product['quantity'],
                'affiliation' => $settings['affiliation'],
                'currency' => $settings['currency']
            );
            $product_info = $this->getProduct($order_product['product_id'], $settings['language_id']);
            if ($product_info) {
                $item_data['item_name'] = $product_info['name'];
                if ($settings['product_id'] && isset($product_info[$settings['product_id']]) && $product_info[$settings['product_id']]) {
                    $item_data['item_id'] = $product_info[$settings['product_id']];
                }
                if ($product_info['manufacturer']) {
                    $item_data['item_brand'] = $product_info['manufacturer'];
                }
                $categories = $this->getProductCategories($order_product['product_id'], $settings);
                foreach ($categories as $category_key => $category) {
                    if ($category_key) {
                        $item_data['item_category' . ($category_key + 1)] = $category;
                    } else {
                        $item_data['item_category'] = $category;
                    }
                }
                /** Custom Definitions Product */
                if (isset($settings['custom_definition']) && in_array(2, array_column($settings['custom_definition'], 'object'))) {
                    foreach ($settings['custom_definition'] as $custom_definition) {
                        if ($custom_definition['object'] == 2 && isset($product_info[$custom_definition['value']])) {
                            if ($settings['type']) {
                                $item_data['custom_definition'][$custom_definition['name']] = $product_info[$custom_definition['value']];
                            } else {
                                $item_data[$custom_definition['name']] = $product_info[$custom_definition['value']];
                            }
                        }
                    }
                }
                /** Custom Definitions Product */
            } else {
                if ($settings['product_id'] == 'model' && $order_product['model']) {
                    $item_data['item_id'] = $order_product['model'];
                }
            }
            if (isset($order_product['coupon'])) {
                $item_data['coupon'] = $order_product['coupon']['coupon'];
                $item_data['discount'] = 0;
                if ($order_product['coupon']['type'] == 'P') {
                    $item_data['discount'] = ($order_product['price'] / 100) * $order_product['coupon']['discount'];
                } elseif ($order_product['coupon']['type'] == 'F') {
                    $item_data['discount'] = $order_product['coupon']['discount'] / $settings['coupon_product_count'];
                }
                $item_data['discount'] = $this->currency->format($item_data['discount'], $settings['currency'], $settings['currency_value'], false);
                $item_data['discount'] = number_format($item_data['discount'], 2, '.', '');
            }
            if ($settings['tax']) {
                $order_product['price'] = $order_product['price'] + $order_product['tax'];
            }
            $item_data['price'] = $this->currency->format($order_product['price'], $settings['currency'], $settings['currency_value'], false);
            $item_data['price'] = number_format($item_data['price'], 2, '.', '');
            $order_options = $this->getOrderOptions($order_product['order_id'], $order_product['order_product_id']);
            if ($order_options) {
                $item_data['item_variant'] = '';
                foreach ($order_options as $order_option) {
                    $item_data['options'][] = array(
                        'product_option_id' => $order_option['product_option_id'],
                        'product_option_value_id' => $order_option['product_option_value_id']
                    );
                    $product_option_value = $this->getProductOptionValue($order_option['product_option_value_id'], $settings['language_id']);
                    if ($product_option_value) {
                        $item_data['item_variant'] .= $product_option_value['name'] . ':' . $product_option_value['value'] . ',';
                    } else {
                        $item_data['item_variant'] .= $order_option['name'] . ':' . $order_option['value'] . ',';
                    }
                }
                $item_data['item_variant'] = trim($item_data['item_variant']);
            }
            $result['items'][] = $item_data;
        }
        /** Order Products */
        return $result;
    }

    public function convertOrderParameters($data) {
        $result = array(
            'order_id'          => $data['order_id'],
            'ep.transaction_id' => $data['transaction_id'],
            'ep.affiliation'    => $data['affiliation'],
            'epn.value'         => $data['value'],
            'epn.tax'           => $data['tax'],
            'epn.shipping'      => $data['shipping'],
            'cu'                => $data['currency']
        );
        if (isset($data['coupon'])) {
            $result['ep.coupon'] = $data['coupon'];
        }
        if (isset($data['custom_definition'])) {
            foreach ($data['custom_definition'] as $key => $value) {
                $result['ep.' . $key] = $value;
            }
        }
        if (isset($data['items'])) {
            $item_index = 0;
            foreach ($data['items'] as $product_id => $item) {
                $item_data = array(
                    'id' => $item['item_id'],
                    'nm' => $item['item_name'],
                    'qt' => (int)$item['quantity'],
                    'af' => $item['affiliation'],
                    'cu' => $item['currency'],
                    'pr' => $item['price'],
                    'lp' => $item_index
                );
                if (isset($item['item_brand'])) {
                    $item_data['br'] = $item['item_brand'];
                }
                if (isset($item['item_category'])) {
                    $item_data['ca'] = $item['item_category'];
                }
                if (isset($item['item_category2'])) {
                    $item_data['ca2'] = $item['item_category2'];
                }
                if (isset($item['item_category3'])) {
                    $item_data['ca3'] = $item['item_category3'];
                }
                if (isset($item['item_category4'])) {
                    $item_data['ca4'] = $item['item_category4'];
                }
                if (isset($item['item_category5'])) {
                    $item_data['ca5'] = $item['item_category5'];
                }
                if (isset($item['coupon'])) {
                    $item_data['cp'] = $item['coupon'];
                }
                if (isset($item['item_variant'])) {
                    $item_data['va'] = $item['item_variant'];
                }
                if (isset($item['custom_definition'])) {
                    $i = 1;
                    foreach ($item['custom_definition'] as $key => $value) {
                        $item_data['k' . $i] = $key;
                        $item_data['v' . $i] = $value;
                        $i++;
                    }
                }
                $item_str = '';
                foreach ($item_data as $item_key => $item_val) {
                    $item_str .= $item_key . $item_val . '~';
                }
                $result['pr' . ($item_index + 1)] = rtrim($item_str, '~');
                $item_index++;
            }
        }
        return $result;
    }

    public function createRequest($params, $settings) {
        $measurement_ids = explode(',', $settings['measurement_id'], 3);
        $api_secrets = explode(',', $settings['measurement_secret'], 3);
        $log = '';
        if ($settings['type'] && ($params['en'] == 'purchase' || $params['en'] == 'refund')) {
            $sessions = $this->getEOrderSessions($params['order_id']);
            unset($params['order_id']);
        } else {
            $sessions = array();
        }
        foreach ($measurement_ids as $key => $measurement_id) {
            if ($settings['type']) {
                $params['tid'] = $measurement_id;
                foreach ($sessions as $session) {
                    if ('G-' . $session['measurement_id'] == $measurement_id) {
                        $params['sid'] = $session['session_id'];
                        $params['sct'] = $session['session_number'];
                    }
                }
                $url = 'https://www.google-analytics.com/g/collect?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: text/plain'));
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                $response = curl_exec($curl);
                curl_close($curl);
                if ($response) {
                    $res = preg_match("/<title>(.*)<\/title>/siU", $response, $title_matches);
                    if (!$res)
                        return null;

                    $title = preg_replace('/\s+/', ' ', $title_matches[1]);
                    $log .= 'server response: ' . trim($title);
                }
            } else {
                $params_string = json_encode($params);
                $params_string = preg_replace('/"([^"]+)"\s*:\s*(""|null),/', '', $params_string);
                if ($settings['validation_mode']) {
                    $url = 'https://www.google-analytics.com/debug/mp/collect';
                } else {
                    $url = 'https://www.google-analytics.com/mp/collect';
                }
                $url .= '?measurement_id=' . $measurement_id . '&api_secret=' . (isset($api_secrets[$key]) ? $api_secrets[$key] : $api_secrets[0]);
                $curl = curl_init();
                if (isset($this->request->server['HTTP_USER_AGENT'])) {
                    curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
                }
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params_string);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                $response = curl_exec($curl);
                curl_close($curl);
                if ($settings['validation_mode'] && !$log) {
                    $response_array = json_decode($response, true);
                    if (isset($response_array['validationMessages']) && $response_array['validationMessages']) {
                        $debug_response = $response_array['validationMessages'][0];
                        $log = 'validation: false';
                        if (isset($debug_response['description'])) {
                            $log .= 'error: ' . $debug_response['description'];
                        }
                        if (isset($debug_response['validationCode'])) {
                            $log .= 'code: ' . $debug_response['validationCode'];
                        }
                        if (isset($debug_response['fieldPath'])) {
                            $log .= 'path: ' . $debug_response['fieldPath'];
                        }
                    } else {
                        $log = 'validation: true';
                    }
                }
            }
        }
        return $log;
    }

    public function addLog($data, $settings) {
        if ($settings['log']) {
            $log = array();
            $ext_log = array();
            $ext_log_items = array();
            if (isset($data['event'])) {
                $temp = $data;
                $data = array();
                $data['events']['name'] = $temp['event'];
                $data['events']['params'] = $temp;
            }
            $log['event'] = strtoupper(str_replace('_', ' ', $data['events']['name']));
            switch ($settings['type']) {
                case 0:
                    $log['type'] = 'mp';
                    if ($settings['validation_mode']) {
                        $log['type'] .= ' / TRACKING IS DISABLED IN VALIDATION MODE';
                    }
                    break;
                case 1:
                    $log['type'] = 'gtag';
                    break;
                case 2:
                    $log['type'] = 'gtm';
                    break;
            }
            foreach ($data as $key => $item) {
                if (is_array($item)) {
                    if ($key == 'events') {
                        foreach ($item['params'] as $param_key => $param_value) {
                            if (is_array($param_value) && $param_key == 'items') {
                                $ext_log_items = $param_value;
                                if (isset($param_value[0]['affiliation'])) {
                                    $ext_log['affiliation'] = $param_value[0]['affiliation'];
                                }
                                if (isset($param_value[0]['currency'])) {
                                    $ext_log['currency'] = $param_value[0]['currency'];
                                }
                            } else if (!is_array($param_value)) {
                                switch ($param_key) {
                                    case 'order_id':
                                    case 'index':
                                        break;
                                    case 'debug_mode':
                                        if ($settings['type'] < 2 || ($data['events']['name'] == 'purchase' || $data['events']['name'] == 'refund')) {
                                            $log['type'] .= ' (debug view)';
                                        }
                                        break;
                                    case 'value':
                                    case 'transaction_id':
                                    case 'item_list_name':
                                    case 'promotion_name':
                                    case 'shipping_tier':
                                    case 'payment_type':
                                    case 'traffic_type':
                                    case 'search_term':
                                        $log[$param_key] = $param_value;
                                        break;
                                    default:
                                        $ext_log[$param_key] = $param_value;
                                        break;
                                }
                            }
                        }
                    } else if ($key == 'user_properties') {
                        foreach ($item as $property_name => $property) {
                            $ext_log[$property_name] = $property['value'];
                        }
                    } else if ($key == 'items') {
                        $ext_log_items = $item;
                    }
                } else if ($key == 'log') {
                    $log[$key] = $item;
                } else {
                    $ext_log[$key] = $item;
                }
            }
            if ($settings['view_simple'] && ($data['events']['name'] == 'view_item' || $data['events']['name'] == 'view_item_list' || $data['events']['name'] == 'view_search_results')) {
                $log['type'] .= ' / simple mode';
            }
            if ($settings['promotion_simple'] && $data['events']['name'] == 'view_promotion') {
                $log['type'] .= ' / simple mode';
            }
            if ($settings['purchase_simple'] && $data['events']['name'] == 'purchase') {
                $log['type'] .= ' / simple mode';
            }
            $log_str = '';
            foreach ($log as $log_key => $log_value) {
                if ($log_key == 'event' || $log_key == 'log') {
                    $log_str .= $log_value . ' / ';
                } else {
                    $log_str .= $log_key . ': ' . $log_value . ' / ';
                }
            }
            $log_str = rtrim($log_str, ' / ');
            if ($settings['extended_log']) {
                $log_str .= "\n  ";
                foreach ($ext_log as $ext_log_key => $ext_log_value) {
                    if (!array_key_exists($ext_log_key, $log)) {
                        $log_str .= $ext_log_key . ': ' . $ext_log_value . ' / ';
                    }
                }
                $log_str = rtrim($log_str, ' / ');
                foreach ($ext_log_items as $item) {
                    $log_str .= "\n  ";
                    foreach ($item as $item_key => $item_value) {
                        if (!array_key_exists($item_key, $ext_log) && !array_key_exists($item_key, $log)) {
                            $log_str .= $item_key . ': ' . $item_value . ' / ';
                        }
                    }
                    $log_str = rtrim($log_str, ' / ');
                }
            }
            return $this->writeLog($log_str, $settings['store_id']);
        }
    }

    public function writeLog($log, $store_id) {
        $log_obj = new Log($this->eName . '.' . $store_id . '.log');
        $log_obj->write($log);
        return array(
            'text' => $log,
            'show' => isset($this->session->data['user_id'])
        );
    }

    public function internalTraffic($settings) {
        if (utf8_strlen($settings['traffic_type'])) {
            if (utf8_strlen($settings['exclude_u_agent']) > 1 && isset($this->request->server['HTTP_USER_AGENT']) && preg_match('/' . trim($settings['exclude_u_agent'], '| ') . '/i', $this->request->server['HTTP_USER_AGENT'])) {
                return true;
            }
            if (isset($settings['exclude_admin']) && $settings['exclude_admin'] && isset($this->session->data['user_id']) && $this->session->data['user_id']) {
                return true;
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
                        return true;
                    }
                    $mask = str_replace('.*', '', $ip);
                    if (strpos($ip_address, $mask) !== false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getClientId($disable_config_id = 0) {
        $client_id = '';
        if (isset($_COOKIE['_ga'])) {
            $ga_array = explode('.', $_COOKIE['_ga']);
            if (isset($ga_array['2']) && isset($ga_array['3'])) {
                $client_id = $ga_array['2'] . '.' . $ga_array['3'];
            }
        }
        if (!$client_id && isset($_COOKIE['__utma'])) {
            $utma_array = explode('.', $_COOKIE['__utma']);
            if (isset($utma_array['1']) && isset($utma_array['2'])) {
                $client_id = $utma_array['1'] . '.' . $utma_array['2'];
            }
        }
        if (!$client_id && !$disable_config_id) {
            $settings = $this->getSettings();

            if ($settings['client_id']) {
                $client_id = $settings['client_id'];
            } else if ($settings['log']) {
                $this->load->language($this->ePath);
                $this->writeLog($this->language->get('error_client_id_not_found'), $settings['store_id']);
            }
        }
        return $client_id;
    }

    public function getUserId($settings, $customer_id = 0) {
        $user_id = 0;
        if (!$customer_id) {
            $customer_id = $this->customer->isLogged() ? $this->customer->getId() : 0;
        }
        if ($customer_id && $settings['user_id']) {
            if ($settings['user_id'] == 'customer_id') {
                $user_id = $customer_id;
            } else {
                $customer_info = $this->getCustomer($customer_id);
                if (isset($customer_info[$settings['user_id']]) && $customer_info[$settings['user_id']]) {
                    $user_id = $customer_info[$settings['user_id']];
                }
            }
        }
        return $user_id;
    }

    protected function getListByUrl($url, $product_id = 0) {
        $return = array();
        $url_data = parse_url(html_entity_decode($url, ENT_QUOTES, 'UTF-8'));
        $query = array();
        if (isset($url_data['query'])) {
            parse_str($url_data['query'], $query);
            if (isset($query['route'])) {
                switch ($query['route']) {
                    case 'product/category':
                        $return['list'] = 'category';
                        if (isset($query['path'])) {
                            $parts = explode('_', (string)$query['path']);
                            $return['list_id'] = (int)array_pop($parts);
                        }
                        break;
                    case 'product/search':
                        $return['list'] = 'search';
                        break;
                    case 'product/manufacturer/info':
                        $return['list'] = 'manufacturer_info';
                        if (isset($query['manufacturer_id'])) {
                            $return['list_id'] = $query['manufacturer_id'];
                        }
                        break;
                    case 'product/special':
                        $return['list'] = 'special';
                        break;
                    case 'product/product':
                        if (isset($query['product_id'])) {
                            if ($product_id != $query['product_id']) {
                                $return['list'] = 'related';
                            }
                        }
                        break;
                    case 'product/compare':
                        $return['list'] = 'compare';
                        break;
                    case 'checkout/cart':
                        $return['list'] = 'checkout_cart';
                        break;
                }
            } elseif (isset($query['product_id'])) {
                if ($product_id != $query['product_id']) {
                    $return['list'] = 'related';
                }
            }
        } else {
            $parts = explode('/', trim($url_data['path'], '/'));
            if (utf8_strlen(end($parts)) == 0) {
                array_pop($parts);
            }
            if (strpos(end($parts), 'page=') !== false || strpos(end($parts), 'page-') !== false) {
                $keyword = prev($parts);
            } else {
                $keyword = end($parts);
            }
            $seo_url_data = $this->getSeoUrl($keyword);
            if ($seo_url_data) {
                $url = explode('=', $seo_url_data['query']);
                $return = $this->getListByQuery($url, $product_id);
                if (!$return && isset($url[1])) {
                    $url[0] = $url[1];
                    $url[1] = '';
                    $return = $this->getListByQuery($url, $product_id);
                }
            }
        }
        return $return;
    }

    protected function getListByQuery($url, $product_id) {
        $return = array();
        switch ($url[0]) {
            case 'category_id':
                $return['list'] = 'category';
                if (isset($url[1])) {
                    $return['list_id'] = $url[1];
                }
                break;
            case 'product/search':
                $return['list'] = 'search';
                break;
            case 'manufacturer_id':
                $return['list'] = 'manufacturer_info';
                if (isset($url[1])) {
                    $return['list_id'] = $url[1];
                }
                break;
            case 'product/special':
                $return['list'] = 'special';
                break;
            case 'product_id':
                if (isset($url[1])) {
                    if ($product_id != $url[1]) {
                        $return['list'] = 'related';
                    }
                } else {
                    $return['list'] = 'related';
                }
                break;
            case 'product/compare':
                $return['list'] = 'compare';
                break;
            case 'checkout/cart':
                $return['list'] = 'checkout_cart';
                break;
        }
        return $return;
    }

    protected function getCoupon($code, $products = array()) {
        $status = true;
        $coupon_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon` WHERE code = '" . $this->db->escape($code) . "'");
        if ($coupon_query->num_rows) {
            /** Products */
            $coupon_product_data = array();
            $coupon_product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_product` WHERE coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");
            foreach ($coupon_product_query->rows as $product) {
                $coupon_product_data[] = $product['product_id'];
            }
            /** Categories */
            $coupon_category_data = array();
            $coupon_category_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_category` cc LEFT JOIN `" . DB_PREFIX . "category_path` cp ON (cc.category_id = cp.path_id) WHERE cc.coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");
            foreach ($coupon_category_query->rows as $category) {
                $coupon_category_data[] = $category['category_id'];
            }
            $product_data = array();
            if ($coupon_product_data || $coupon_category_data) {
                foreach ($products as $product) {
                    if (in_array($product['product_id'], $coupon_product_data)) {
                        $product_data[] = $product['product_id'];
                        continue;
                    }
                    foreach ($coupon_category_data as $category_id) {
                        $coupon_category_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '" . (int)$product['product_id'] . "' AND category_id = '" . (int)$category_id . "'");
                        if ($coupon_category_query->row['total']) {
                            $product_data[] = $product['product_id'];
                            continue;
                        }
                    }
                }
                if (!$product_data) {
                    $status = false;
                }
            }
        } else {
            $status = false;
        }
        if ($status) {
            return array(
                'coupon_id'     => $coupon_query->row['coupon_id'],
                'code'          => $coupon_query->row['code'],
                'name'          => $coupon_query->row['name'],
                'type'          => $coupon_query->row['type'],
                'discount'      => $coupon_query->row['discount'],
                'shipping'      => $coupon_query->row['shipping'],
                'total'         => $coupon_query->row['total'],
                'product'       => $product_data,
                'coupon'        => $coupon_query->row['name'] . ' (' . $coupon_query->row['code'] . ')'
            );
        }
    }

    public function getCartProducts($cart_products = array()) {
        $return = array();
        if (!$cart_products) {
            $cart_products = $this->cart->getProducts();
        }
        if ($cart_products) {
            $setting = $this->getSettings();
            $return['currency'] = $setting['currency'];
            $coupon_info = array();
            $product_count = 0;
            if (isset($this->session->data['coupon'])) {
                $this->load->model('extension/total/coupon');
                $coupon_info = $this->model_extension_total_coupon->getCoupon($this->session->data['coupon']);
                if ($coupon_info) {
                    $return['coupon'] = $coupon_info['name'] . ' (' . $coupon_info['code'] . ')';
                    foreach ($cart_products as $key => $cart_product) {
                        if (!$coupon_info['product'] || in_array($cart_product['product_id'], $coupon_info['product'])) {
                            $product_count += $cart_product['quantity'];
                            $cart_products[$key]['coupon_status'] = true;
                        }
                    }
                }
            }
            $return['value'] = 0;
            $return['items'] = array();
            $index = 1;
            foreach ($cart_products as $cart_product) {
                $options = array();
                foreach ($cart_product['option'] as $option) {
                    if ($option['type'] == 'select' || $option['type'] == 'checkbox' || $option['type'] == 'radio') {
                        $options[$option['product_option_id']] = $option['product_option_value_id'];
                    }
                }
                $cart_product['option'] = $options;
                $cart_product['index'] = $index;
                $product_data = $this->getItemParameters($cart_product['product_id'], $setting, $cart_product);
                if (isset($cart_product['coupon_status'])) {
                    $product_data['coupon'] = $return['coupon'];
                    $product_data['discount'] = 0;
                    if ($coupon_info['type'] == 'P') {
                        $product_data['discount'] = number_format(($cart_product['price'] / 100) * $coupon_info['discount'], 2, '.', '');
                        $return['value'] += ($product_data['price'] - $product_data['discount']) * $product_data['quantity'];
                    } else if ($coupon_info['type'] == 'F') {
                        $product_data['discount'] = number_format($coupon_info['discount'] / $product_count, 2, '.', '');
                        $return['value'] += $product_data['price'] * $product_data['quantity'];
                    } else {
                        $return['value'] += $product_data['price'] * $product_data['quantity'];
                    }
                } else {
                    $return['value'] += $product_data['price'] * $product_data['quantity'];
                }
                $return['items'][] = $product_data;
                $index++;
            }
            if (isset($coupon_info['type']) && $coupon_info['type'] == 'F') {
                $return['value'] = $return['value'] - $coupon_info['discount'];
            }
            $return['value'] = number_format($return['value'], 2, '.', '');
        }
        return $return;
    }

    public function getShippingTier($shipping_code = '') {
        if (!isset($this->session->data['shipping_methods'])) {
            return htmlspecialchars(strip_tags($shipping_code), ENT_QUOTES, 'UTF-8');
        }
        $shipping_tier = '';
        if ($this->config->get($this->eName . '_language_id') && $this->config->get($this->eName . '_language_id') != $this->config->get('config_language_id')) {
            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();
            foreach ($languages as $language_item) {
                if ($language_item['language_id'] == $this->config->get($this->eName . '_language_id')) {
                    $new_language = new Language($language_item['code']);
                    foreach ($this->session->data['shipping_methods'] as $shipping_method_key => $shipping_method_item) {
                        foreach ($shipping_method_item['quote'] as $quote_key => $quote) {
                            $new_language->load('extension/shipping/' . $shipping_method_key);
                            if ($shipping_code == '' || $quote['code'] == $shipping_code) {
                                $shipping_tier = htmlspecialchars(strip_tags($new_language->get('text_title')), ENT_QUOTES, 'UTF-8');
                                break 3;
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($this->session->data['shipping_methods'] as $shipping_method) {
                foreach ($shipping_method['quote'] as $quote_key => $quote) {
                    if ($shipping_code == '' || $quote['code'] == $shipping_code) {
                        $shipping_tier = htmlspecialchars(strip_tags($quote['title']), ENT_QUOTES, 'UTF-8');
                        break 2;
                    }
                }
            }
        }
        return $shipping_tier ? $shipping_tier : htmlspecialchars(strip_tags($shipping_code), ENT_QUOTES, 'UTF-8');
    }

    public function getPaymentType($payment_code = '') {
        if (!isset($this->session->data['payment_methods'])) {
            return htmlspecialchars(strip_tags($payment_code), ENT_QUOTES, 'UTF-8');
        }
        $payment_type = '';
        if ($this->config->get($this->eName . '_language_id') && $this->config->get($this->eName . '_language_id') != $this->config->get('config_language_id')) {
            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();
            foreach ($languages as $language_item) {
                if ($language_item['language_id'] == $this->config->get($this->eName . '_language_id')) {
                    $new_language = new Language($language_item['code']);
                    foreach ($this->session->data['payment_methods'] as $payment_method) {
                        $new_language->load('extension/payment/' . $payment_method['code']);
                        if ($payment_code == '' || $payment_method['code'] == $payment_code) {
                            $payment_type = htmlspecialchars(strip_tags($new_language->get('text_title')), ENT_QUOTES, 'UTF-8');
                            break 2;
                        }
                    }
                }
            }
        } else {
            foreach ($this->session->data['payment_methods'] as $payment_method) {
                if ($payment_code == '' || $payment_method['code'] == $payment_code) {
                    $payment_type = htmlspecialchars(strip_tags($payment_method['title']), ENT_QUOTES, 'UTF-8');
                    break;
                }
            }
        }
        return $payment_type ? $payment_type : htmlspecialchars(strip_tags($payment_code), ENT_QUOTES, 'UTF-8');
    }

    public function getOrderProducts($order_id) {
        $sql = "SELECT op.*, erp.quantity AS refund_quantity FROM `" . DB_PREFIX . "order_product` op";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "ga4_ecommerce_refund_product` erp ON op.order_product_id = erp.order_product_id";
        $sql .= " WHERE op.order_id = '" . (int)$order_id . "'";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getOrderProductById($order_product_id) {
        $sql = "SELECT op.*, erp.quantity as refund_quantity FROM " . DB_PREFIX . "order_product op";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "ga4_ecommerce_refund_product` erp ON op.order_product_id = erp.order_product_id";
        $sql .= " WHERE op.order_product_id = '" . (int)$order_product_id . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    private function getOrderValues($order_id, $settings) {
        $result = array(
            'value'     => 0,
            'tax'       => 0,
            'shipping'  => 0
        );
        $totals = $this->getOrderTotals($order_id);
        $sub_total = 0;
        if ($totals) {
            foreach ($totals as $item) {
                switch ($item['code']) {
                    case 'shipping':
                        $result['shipping'] += $item['value'];
                        break;
                    case 'tax':
                        $result['tax'] += $item['value'];
                        break;
                    case 'total':
                        $result['value'] += $item['value'];
                        break;
                    case 'sub_total':
                        $sub_total += $item['value'];
                        break;
                    case 'coupon':
                        $result['coupon'] = $item['title'];
                        break;
                }
            }
        }
        if (!$result['value'] && $sub_total) {
            $result['value'] = $sub_total;
        }
        if (!$settings['total_shipping']) {
            $result['value'] -= $result['shipping'];
        }
        if (!$settings['total_tax']) {
            $result['value'] -= $result['tax'];
        }
        $result['value'] = $this->currency->format($result['value'], $settings['currency'], $settings['currency_value'], false);
        $result['shipping'] = $this->currency->format($result['shipping'], $settings['currency'], $settings['currency_value'], false);
        $result['tax'] = $this->currency->format($result['tax'], $settings['currency'], $settings['currency_value'], false);
        return $result;
    }

    /** Extension DB */
    public function getEOrder($order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ga4_ecommerce_order` WHERE order_id = '" . $this->db->escape($order_id) . "'");
        return $query->row;
    }

    public function addEOrder($order_id, $data = array()) {
        if (!isset($data['client_id']) || !$data['client_id']) {
            $data['client_id'] = $this->getClientId();
        }
        if (!isset($data['purchase_status'])) {
            $data['purchase_status'] = 0;
        }
        $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "ga4_ecommerce_order` SET order_id = '" . (int)$order_id . "', client_id = '" . $this->db->escape($data['client_id']) . "', `tracking_type` = '" . (int)$data['tracking_type'] . "', purchase_status = '" . (int)$data['purchase_status'] . "', date_registration = NOW()");
        if (isset($_COOKIE)) {
            foreach ($_COOKIE as $key => $value) {
                if (substr( $key, 0, 4 ) === '_ga_') {
                    $session_data = explode('.', $value);
                    if (isset($session_data['2']) && isset($session_data['3'])) {
                        $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "ga4_ecommerce_order_session SET order_id = '" . (int)$order_id . "', measurement_id = '" . $this->db->escape(ltrim($key, '_ga_')) . "', session_id = '" . (int)$session_data[2] . "', session_number = '" . (int)$session_data[3] . "'");
                    }
                }
            }
        }
    }

    public function editEOrder($order_id, $data = array()) {
        $sql = "UPDATE `" . DB_PREFIX . "ga4_ecommerce_order` SET";
        if (isset($data['tracking_type'])) {
            $sql .= " tracking_type = '" . (int)$data['tracking_type'] . "',";
        }
        if (isset($data['purchase_status'])) {
            $sql .= " purchase_status = '" . (int)$data['purchase_status'] . "',";
            $sql .= " date_tracking = NOW(),";
        }
        $sql = rtrim($sql, ',') . " WHERE order_id = '" . (int)$order_id . "'";
        $this->db->query($sql);
    }

    public function getERefundProducts($order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ga4_ecommerce_refund_product` WHERE order_id = '" . $this->db->escape($order_id) . "'");
        return $query->rows;
    }

    public function editERefundProducts($order_id, $refund_products, $tracking_type = 0) {
        if (count($refund_products) == 1) {
            foreach ($refund_products as $refund_product) {
                $sql = "INSERT INTO `" . DB_PREFIX . "ga4_ecommerce_refund_product`";
                $sql .= " SET order_product_id = '" . (int)$refund_product['order_product_id'] . "', order_id = '" . (int)$order_id . "', product_id = '" . (int)$refund_product['product_id'] . "', quantity = '" . (int)$refund_product['quantity'] . "', tracking_type = '" . (int)$tracking_type . "', date_refund = NOW()";
                $sql .= " ON DUPLICATE KEY UPDATE quantity = quantity + '" . (int)$refund_product['quantity'] . "', tracking_type ='" . (int)$tracking_type . "'";
                $this->db->query($sql);
            }
        } else {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "ga4_ecommerce_refund_product` WHERE order_id = '" . (int)$order_id . "'");
            foreach ($refund_products as $refund_product) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "ga4_ecommerce_refund_product` SET order_product_id = '" . (int)$refund_product['order_product_id'] . "', order_id = '" . (int)$order_id . "', product_id = '" . (int)$refund_product['product_id'] . "', quantity = '" . (int)$refund_product['quantity'] . "', tracking_type = '" . (int)$tracking_type . "', date_refund = NOW()");
            }
        }

    }

    public function getEOrderSessions($order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ga4_ecommerce_order_session` WHERE order_id = '" . $this->db->escape($order_id) . "'");
        return $query->rows;
    }
    /** Extension DB */

    /** OpenCart DB */
    public function getOrder($order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
        return $query->row;
    }

    protected function getOrderTotals($order_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");
        return $query->rows;
    }

    protected function getOrderOptions($order_id, $order_product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "' AND type IN ('select', 'radio', 'checkbox')");
        return $query->rows;
    }

    public function getProduct($product_id, $language_id) {
        $query = $this->db->query("SELECT DISTINCT p.*, pd.name AS name, pd.meta_title AS meta_title, m.name AS manufacturer,
        (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, 
        (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special 
        FROM " . DB_PREFIX . "product p 
        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
        LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) 
        WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$language_id . "'");
        if ($query->num_rows) {
            if ($query->row['discount']) {
                $query->row['price'] = $query->row['discount'];
            }
            return $query->row;
        } else {
            return false;
        }
    }

    protected function getProductCategories($product_id, $settings) {
        switch ($settings['product_category']) {
            default:
            case 0:
                $query = $this->db->query("SELECT cd.name FROM " . DB_PREFIX . "product_to_category pc 
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (pc.category_id = cd.category_id) 
            WHERE pc.product_id = '" . (int)$product_id . "' AND cd.language_id = '" . (int)$settings['language_id'] . "'");
                break;
            case 1:
                $query = $this->db->query("SELECT (SELECT GROUP_CONCAT(cd.name ORDER BY cp.level SEPARATOR ' > ') 
            FROM " . DB_PREFIX . "category_path cp 
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (cp.path_id = cd.category_id)
            WHERE cp.category_id = pc.category_id AND cd.language_id = '" . (int)$settings['language_id'] . "') as name 
            FROM " . DB_PREFIX . "product_to_category pc 
            WHERE pc.product_id = '" . (int)$product_id . "'");
                break;
            case 2:
                $query = $this->db->query("SELECT cd.name FROM " . DB_PREFIX . "category_path cp 
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (cp.path_id = cd.category_id)
            WHERE cp.category_id = (SELECT pc.category_id FROM " . DB_PREFIX . "product_to_category pc
            LEFT JOIN " . DB_PREFIX . "category_path cp ON (cp.category_id = pc.category_id) 
            WHERE pc.product_id = '" . (int)$product_id . "' ORDER BY cp.level DESC LIMIT 1) 
            AND cd.language_id = '" . (int)$settings['language_id'] . "' ORDER BY cp.level");
                break;
            case 3:
                $query = $this->db->query("SELECT cd.name FROM " . DB_PREFIX . "product_to_category pc 
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (pc.category_id = cd.category_id) 
            WHERE pc.product_id = '" . (int)$product_id . "' AND cd.language_id = '" . (int)$settings['language_id'] . "' LIMIT 1");
                break;
        }
        if (function_exists('array_column')) {
            return array_column($query->rows, 'name');
        } else {
            return $this->getArrayColumn($query->rows, 'name');
        }
    }

    function getArrayColumn(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if (!array_key_exists($columnKey, $value)) {
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if (!array_key_exists($indexKey, $value)) {
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }

    protected function getProductOptions($product_id, $language_id) {
        $product_option_data = array();
        $product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po 
        LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) 
        LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) 
        WHERE po.product_id = '" . (int)$product_id . "' 
        AND od.language_id = '" . (int)$language_id . "'
        AND o.type IN ('select', 'radio', 'checkbox', 'image') 
        ORDER BY o.sort_order");
        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_data = array();
            $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov 
            LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) 
            LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) 
            WHERE pov.product_id = '" . (int)$product_id . "' 
            AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' 
            AND ovd.language_id = '" . (int)$language_id . "'
            ORDER BY ov.sort_order");
            foreach ($product_option_value_query->rows as $product_option_value) {
                $product_option_value_data[] = array(
                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                    'option_value_id' => $product_option_value['option_value_id'],
                    'name' => $product_option_value['name'],
                    'price' => $product_option_value['price'],
                    'price_prefix' => $product_option_value['price_prefix'],
                );
            }
            $product_option_data[] = array(
                'product_option_id' => $product_option['product_option_id'],
                'product_option_value' => $product_option_value_data,
                'option_id' => $product_option['option_id'],
                'name' => $product_option['name']
            );
        }
        return $product_option_data;
    }

    protected function getProductOptionValue($product_option_value_id, $language_id) {
        $query = $this->db->query("SELECT od.name, pov.price, pov.price_prefix, ovd.name as value FROM " . DB_PREFIX . "product_option_value pov 
        LEFT JOIN `" . DB_PREFIX . "option` o ON (pov.option_id = o.option_id) 
        LEFT JOIN " . DB_PREFIX . "option_description od ON (pov.option_id = od.option_id) 
        LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (pov.option_value_id = ovd.option_value_id) 
        WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' 
        AND o.type IN ('select', 'radio', 'checkbox') 
        AND od.language_id = '" . (int)$language_id . "'
        AND ovd.language_id = '" . (int)$language_id . "'
        ORDER BY o.sort_order");
        return $query->row;
    }

    protected function getProductRequiredOptions($product_id) {
        $query = $this->db->query("SELECT product_option_id FROM " . DB_PREFIX . "product_option 
        WHERE product_id = '" . (int)$product_id . "' AND required = 1");
        return $query->rows;
    }

    public function getCart($cart_id) {
        if (substr(VERSION, 0, 7) < '2.1.0.1') {
            $product = unserialize(base64_decode($cart_id));
            if ($product) {
                return array(
                    'product_id' => isset($product['product_id']) ? $product['product_id'] : 0,
                    'option' => isset($product['option']) ? json_encode($product['option']) : '',
                    'quantity' => isset($this->session->data['cart'][$cart_id]) ? (int)$this->session->data['cart'][$cart_id] : ''
                );
            }
        } else {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE cart_id = '" . (int)$cart_id . "'");
            return $query->row;
        }
    }

    public function getCustomer($customer_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
        return $query->row;
    }

    public function getCategoryName($category_id, $language_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$language_id . "'");
        return $query->num_rows ? $query->row['name'] : '';
    }

    public function getManufacturerName($manufacturer_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        return $query->num_rows ? $query->row['name'] : '';
    }

    public function getBanner($banner_id, $language_id) {
        if (substr(VERSION, 0, 7) > '2.2.0.0') {
            $sql = "SELECT * FROM " . DB_PREFIX . "banner_image bi LEFT JOIN " . DB_PREFIX . "banner b ON (b.banner_id = bi.banner_id) WHERE bi.banner_id = '" . (int)$banner_id . "' AND bi.language_id = '" . (int)$language_id . "' ORDER BY bi.sort_order ASC";
        } else {
            $sql = "SELECT * FROM " . DB_PREFIX . "banner_image bi LEFT JOIN " . DB_PREFIX . "banner_image_description bid ON (bi.banner_image_id = bid.banner_image_id) LEFT JOIN " . DB_PREFIX . "banner b ON (b.banner_id = bi.banner_id) WHERE bi.banner_id = '" . (int)$banner_id . "' AND bid.language_id = '" . (int)$language_id . "' ORDER BY bi.sort_order ASC";
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

    protected function getBannerImage($banner_image_id, $language_id) {
        if (substr(VERSION, 0, 7) > '2.2.0.0') {
            $sql = "SELECT * FROM " . DB_PREFIX . "banner_image bi LEFT JOIN " . DB_PREFIX . "banner b ON (bi.banner_id = b.banner_id) WHERE bi.banner_image_id = '" . (int)$banner_image_id . "'";
        } else {
            $sql = "SELECT * FROM " . DB_PREFIX . "banner_image bi LEFT JOIN " . DB_PREFIX . "banner b ON (bi.banner_id = b.banner_id) LEFT JOIN " . DB_PREFIX . "banner_image_description bid ON (bi.banner_image_id = bid.banner_image_id) WHERE bi.banner_image_id = '" . (int)$banner_image_id . "' AND bid.language_id = '" . (int)$language_id . "'";
        }
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getBannerByData($banner_data, $language_id) {
        if (isset($banner_data['title']) && isset($banner_data['link'])) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "banner b LEFT JOIN " . DB_PREFIX . "banner_image bi ON (b.banner_id = bi.banner_id) WHERE b.banner_id = (SELECT banner_id FROM " . DB_PREFIX . "banner_image WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'AND title = '" . $this->db->escape($banner_data['title']) . "' AND link = '" . $this->db->escape($banner_data['link']) . "' LIMIT 0,1) AND b.status = '1' AND bi.language_id = '" . (int)$language_id . "' ORDER BY bi.sort_order ASC");
            return $query->rows;
        } else {
            return array();
        }
    }

    public function getSeoUrl($keyword) {
        if (substr(VERSION, 0, 7) < '3.0.0.0') {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE keyword = '" . $this->db->escape($keyword) . "'");
        } else {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($keyword) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");
        }
        return $query->row;
    }
    /** OpenCart DB */
}