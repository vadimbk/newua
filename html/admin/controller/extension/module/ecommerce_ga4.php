<?php
class ControllerExtensionModuleEcommerceGa4 extends Controller {
    private $error      = array();
    private $eCode      = 'ecommerce_ga4';
    private $eName      = 'module_ecommerce_ga4';
    private $eId        = '44025';
    private $eModel     = 'model_extension_ecommerce_ecommerce_ga4';
    private $ePath      = 'extension/module/ecommerce_ga4';
    private $eModelPath = 'extension/ecommerce/ecommerce_ga4';
    private $eDir       = 'marketplace/extension';
    private $eToken     = 'user_token';
    private $eVersion   = '1.0.5';
    private $eSSL       = true;

    public function index() {
        if ($this->config->get($this->eName . '_dev_mode')) {
            $this->document->addStyle('view/stylesheet/' . $this->eCode . '.css?v=0.' . time());
            $this->document->addScript('view/javascript/' . $this->eCode . '.js?v=0.' . time());
        } else {
            $this->document->addStyle('view/stylesheet/' . $this->eCode . '.min.css?v=' . $this->eVersion);
            $this->document->addScript('view/javascript/' . $this->eCode . '.min.js?v=' . $this->eVersion);
        }

        $this->load->model('setting/setting');
        $this->load->model($this->eModelPath);

        $data = $this->loadLanguage();

        $data['e_name'] = $this->eName;
        $data['e_version'] = $this->eVersion;
        $data['oc_version'] = VERSION;
        $data['author_name'] = base64_decode('VmFuU3R1ZGlv');
        $data['author_link'] = base64_decode('aHR0cHM6Ly92YW5zdHVkaW8uY28udWE=');
        $data['redirect_link'] = $data['author_link'] . '/redirect?extension_id=' . $this->eId . '&action=';

        $data['store_id'] = isset($this->request->get['store_id']) ? $this->request->get['store_id'] : 0;
        $data['translator'] = isset($this->request->get['translator']) ? $this->request->get['translator'] : 0;

        $this->document->setTitle($this->language->get('text_title'));

        $url = $this->getUrl();

        $data['breadcrumbs'] = $this->getBreadcrumbs();

        $data['action'] = $this->link($this->ePath . '/apply' . $url);

        $module_info = $this->model_setting_setting->getSetting($this->eName, $data['store_id']);

        if ($module_info) {
            foreach ($module_info as $key => $item) {
                $data[str_replace($this->eName . '_', '', $key)] = $module_info[$key];
            }
        } else {
            $data['status'] = 0;
            $data['type'] = 1;
            $data['tax'] = $this->config->get('config_tax');
            $data['total_shipping'] = 1;
            $data['total_tax'] = 1;
            $data['timestamp'] = 1;
            $data['purchase_status'] = 1;
            $data['purchase_tracking_status'] = array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status'));
            $data['refund_tracking_status'] = array();
            $data['exclude_u_agent'] = 'bot|crawl|slurp|spider|mediapartners|google';

            $config_order_status_id = $this->{$this->eModel}->getSettingValue('config_order_status_id', $data['store_id']);
            $data['purchase_tracking_status'][] = $config_order_status_id;

            if (!in_array($this->config->get('config_fraud_status_id'), $data['purchase_tracking_status'])) {
                $data['refund_tracking_status'][] = $this->config->get('config_fraud_status_id');
            }

            if (!in_array(11, $data['purchase_tracking_status'])) {
                $data['refund_tracking_status'][] = 11;
            }
        }

        $this->load->model('setting/store');
        $data['stores'][] = array('store_id' => 0, 'name' => $this->language->get('text_default'));
        $data['stores'] = array_merge($data['stores'], $this->model_setting_store->getStores());

        /** General */
        $data['note_mp'] = html_entity_decode(sprintf($data['note_mp'], $data['author_link'] . '/redirect-google?action=developers&urn=analytics/devguides/collection/protocol/ga4'), ENT_QUOTES, 'UTF-8');
        $data['note_gtag'] = html_entity_decode(sprintf($data['note_gtag'], $data['author_link'] . '/redirect-google?action=developers&urn=tag-platform/gtagjs/install'), ENT_QUOTES, 'UTF-8');
        $data['note_gtm'] = html_entity_decode(sprintf($data['note_gtm'], $data['author_link'] . '/redirect-google?action=developers&urn=tag-platform/tag-manager/web', $data['author_link'] . '/redirect-google?action=tagmanager-answer&id=9442095&anchor=config', $data['author_link'] . '/redirect-google?action=tagmanager-answer&id=9442095&anchor=event', $data['author_link'] . '/redirect-google?action=developers&urn=analytics/devguides/collection/ga4/ecommerce?client_type=gtm#show-me-the-tag-configuration', $data['author_link'] . '/redirect-google?action=download&urn=gtm-import-file'), ENT_QUOTES, 'UTF-8');
        $data['note_measurement_id'] = html_entity_decode(sprintf($data['note_measurement_id'], $data['author_link'] . '/redirect-google?action=support-answer&id=9539598&anchor=find-G-ID'), ENT_QUOTES, 'UTF-8');
        $data['note_user_id'] = html_entity_decode(sprintf($data['note_user_id'], $data['author_link'] . '/redirect-google?action=support-answer&id=9213390'), ENT_QUOTES, 'UTF-8');
        $data['note_validation_mode'] = html_entity_decode(sprintf($data['note_validation_mode'], $data['author_link'] . '/redirect-google?action=developers&urn=analytics/devguides/collection/protocol/ga4/validating-events?client_type=gtag'), ENT_QUOTES, 'UTF-8');
        $data['note_debug_mode'] = html_entity_decode(sprintf($data['note_debug_mode'], $data['author_link'] . '/redirect-google?action=support-answer&id=7201382&anchor=reporting'), ENT_QUOTES, 'UTF-8');
        $data['note_debug_mode_gtm'] = html_entity_decode(sprintf($data['note_debug_mode_gtm'], $data['author_link'] . '/redirect-google?action=support-answer&id=7201382&anchor=zippy=%2Cgoogle-tag-manager-websites'), ENT_QUOTES, 'UTF-8');

        if (substr(VERSION, 0, 7) < '2.1.0.1') {
            $ga_setting_name = 'config_google_analytics';
        } elseif (substr(VERSION, 0, 7) < '3.0.0.0') {
            $ga_setting_name = 'google_analytics_code';
        } else {
            $ga_setting_name = 'analytics_google_code';
        }

        $analytics_google_code = $this->{$this->eModel}->getSettingValue($ga_setting_name, $data['store_id']);

        if ($analytics_google_code) {
            preg_match('/G-\w{4,11}/m', $analytics_google_code, $matches);
            if (isset($matches[0])) {
                $data['found_measurement_id'] = $matches[0];
            }
        }

        /** Advanced */
        $data['themes'] = array();
        $themes = glob(DIR_CONFIG . $this->eCode . '/theme/*');
        foreach ($themes as $theme) {
            $data['themes'][] = pathinfo($theme, PATHINFO_FILENAME);
        }

        $data['default_client_id'] = $this->getClientId();
        $data['config_name'] = $this->config->get('config_name');

        $this->load->model('localisation/language');
        $data['languages'][] = array(
            'language_id' => 0,
            'name' => $this->language->get('text_multilingual')
        );
        $data['languages'] = array_merge($data['languages'], $this->model_localisation_language->getLanguages());

        $this->load->model('localisation/currency');
        $data['currencies'][] = array(
            'code' => 0,
            'title' => $this->language->get('text_multicurrency')
        );
        $data['currencies'] = array_merge($data['currencies'], $this->model_localisation_currency->getCurrencies());

        $data['identifiers'] = array('sku', 'model', 'upc', 'ean', 'jan', 'isbn', 'mpn');

        $data['note_non_personalized_ads'] = $data['text_option_partially_available'] . '. ' . html_entity_decode(sprintf($data['note_non_personalized_ads'], $data['author_link'] . '/redirect-google?action=developers&urn=tag-platform/devguides/privacy#turn_off_advertising_personalization'), ENT_QUOTES, 'UTF-8');

        /** Checkout */
        $data['checkout_extensions'] = array();
        $checkout_extensions = glob(DIR_CONFIG . $this->eCode . '/checkout/*');

        foreach ($checkout_extensions as $checkout_extension) {
            $config_file_name = pathinfo($checkout_extension, PATHINFO_FILENAME);
            $this->load->config($this->eCode . '/checkout/' . $config_file_name);

            $data['checkout_extensions'][] = array(
                'name' => $this->config->get($this->eCode . '_name') ? $this->config->get($this->eCode . '_name') : ucwords(str_replace('_', ' ', $config_file_name)),
                'value' => $config_file_name
            );
        }

        /** Purchase */
        $data['order_list_action'] = $this->link($this->ePath . '/orders' . $url);

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $config_settings = $this->model_setting_setting->getSetting('config', $data['store_id']);

        if ($data['status'] && !in_array($config_settings['config_order_status_id'], $data['purchase_tracking_status']) && !array_intersect($this->config->get('config_processing_status'), $data['purchase_tracking_status']) && !array_intersect($this->config->get('config_complete_status'), $data['purchase_tracking_status'])) {
            $data['warning_purchase_tracking_status'] = sprintf($this->language->get('text_statuses_not_match'), $data['redirect_link'] . 'image&name=statuses-not-match.jpg');
        }

        /** Filter */
        $data['current_ip'] = $this->getCurrentIP();
        $data['note_traffic_type'] = $data['text_option_partially_available'] . '. ' . html_entity_decode(sprintf($data['note_traffic_type'], $data['author_link'] . '/redirect-google?action=support-answer&id=10104470'), ENT_QUOTES, 'UTF-8');

        /** Custom Definition */
        $data['product_columns'] = $this->{$this->eModel}->getTableColumns('product');
        $data['order_columns'] = $this->{$this->eModel}->getTableColumns('order', array('order_id', 'total', 'store_name'));
        $data['customer_columns'] = $this->{$this->eModel}->getTableColumns('customer');

        $data['legend_custom_definition'] = html_entity_decode(sprintf($data['legend_custom_definition'], $data['author_link'] . '/redirect-google?action=support-answer&id=10075209'), ENT_QUOTES, 'UTF-8');
        $data['text_alert_custom_definition'] = html_entity_decode(sprintf($data['text_alert_custom_definition'], $data['author_link'] . '/redirect-google?action=support-answer&id=12226705', $data['author_link'] . '/redirect-google?action=support-answer&id=9309767'), ENT_QUOTES, 'UTF-8');

        /** Logs */
        $data['refresh_log_action'] = $this->link($this->ePath . '/refreshLog' . $url);
        $data['clear_log_action'] = $this->link($this->ePath . '/clearLog' . $url);

        $data = array_merge($data, $this->getLogs($data['store_id']));

        /** Help */
        $data['installation_date'] = date ("d M, Y", filemtime(__FILE__));

        $support_data = array(
            'n' => $this->user->getUserName(),
            'm' => $this->config->get('config_email'),
            's' => $this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG
        );

        $data['support_data'] = '&' . http_build_query($support_data, '', '&');

        $support_data['id'] = $this->eId;
        $support_data['oc'] = VERSION;
        $support_data['v'] = $this->eVersion;
        $support_data['l'] = isset($data['license_id']) ? $data['license_id'] : '';

        $data['support'] = html_entity_decode($data['author_link'] . '/redirect?action=support&' .  http_build_query($support_data, '', '&'), ENT_QUOTES, 'UTF-8');

        $data['tooltip_support_data'] = sprintf($data['tooltip_support_data'], $this->user->getUserName(), $this->config->get('config_email'), ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG));

        if ($data['store_id']) {
            $data['multi_store_disabled'] = !$this->config->get($this->eName . '_multi_store');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        if (substr(VERSION, 0, 7) < '3.0.0.0' || substr(VERSION, 0, 7) > '3.0.3.8') {
            $data['heading_title'] = $data['text_title'];
            $data['text_not_found'] = $data['text_incompatible_version'];
            $this->ePath = 'error/not_found';
        }

        if (substr(VERSION, 0, 7) > '2.1.0.2') {
            $this->response->setOutput($this->load->view($this->ePath, $data));
        } else {
            $this->response->setOutput($this->load->view($this->ePath . '.tpl', $data));
        }
    }

    public function orders() {
        $data = $this->loadLanguage();

        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = '';
        }

        if (isset($this->request->get['filter_order_status'])) {
            $filter_order_status = $this->request->get['filter_order_status'];
        } else {
            $filter_order_status = '';
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $filter_order_status_id = $this->request->get['filter_order_status_id'];
        } else {
            $filter_order_status_id = '';
        }

        if (isset($this->request->get['filter_total'])) {
            $filter_total = $this->request->get['filter_total'];
        } else {
            $filter_total = '';
        }

        if (isset($this->request->get['filter_date_added'])) {
            $filter_date_added = $this->request->get['filter_date_added'];
        } else {
            $filter_date_added = '';
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $filter_date_modified = $this->request->get['filter_date_modified'];
        } else {
            $filter_date_modified = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'o.order_id';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }

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

        if (isset($this->request->get['store_id'])) {
            $url .= '&store_id=' . $this->request->get['store_id'];
        }

        $data['filter_action'] = $this->link($this->ePath . '/orders' . $url);

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['store_id'])) {
            $url .= '&store_id=' . $this->request->get['store_id'];
        }

        $data['action'] = $this->link($this->ePath . '/orders' . $url);

        $filter_data = array(
            'filter_order_id'   => $filter_order_id,
            'filter_order_status'    => $filter_order_status,
            'filter_order_status_id' => $filter_order_status_id,
            'filter_total'           => $filter_total,
            'filter_date_added'      => $filter_date_added,
            'filter_date_modified'   => $filter_date_modified,
            'sort'      => $sort,
            'order'     => $order,
            'start'     => ($page - 1) * 10,
            'limit'     => 10
        );

        $data['multi_store'] = $this->config->get($this->eName . '_multi_store') ? 1 : 0;
        if ($data['multi_store']) {
            $filter_data['store_id'] = $store_id;
        }

        $data['orders'] = array();

        $this->load->model($this->eModelPath);

        $results = $this->{$this->eModel}->getOrders($filter_data);

        foreach ($results as $result) {
            $order_item = array(
                'order_id'              => $result['order_id'],
                'purchase_status'       => $result['purchase_status'],
                'refund_status'         => 0,
                'tracking_status'       => $result['purchase_status'] ? $this->language->get('text_status_tracked') : $this->language->get('text_status_untracked'),
                'order_status'          => $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing'),
                'product_count'         => $result['product_count'],
                'customer'              => $result['customer'],
                'store_name'            => $result['store_name'],
                'total'                 => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
                'default_total'         => $this->currency->format($result['total'], $this->config->get('config_currency'), 0),
                'date_added'            => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
                'date_modified'         => $result['date_modified'] == '0000-00-00 00:00:00' ? '' : date($this->language->get('datetime_format'), strtotime($result['date_modified'])),
                'date_registration'     => $result['date_registration'] == '0000-00-00 00:00:00' || $result['date_registration'] == '' ? '' : date($this->language->get('datetime_format'), strtotime($result['date_registration'])),
                'date_tracking'         => $result['date_tracking'] == '0000-00-00 00:00:00' || $result['date_tracking'] == '' ? '' : date($this->language->get('datetime_format'), strtotime($result['date_tracking'])),
                'date_refund'           => $result['date_refund'] == '0000-00-00 00:00:00' || $result['date_refund'] == '' ? '' : date($this->language->get('datetime_format'), strtotime($result['date_refund'])),
                'href'                  => $this->link('sale/order/info', '&order_id='. $result['order_id'])
            );

            if ($result['refund_product_quantity']) {
                if ($result['product_quantity'] == $result['refund_product_quantity']) {
                    $order_item['tracking_status'] .= $this->language->get('text_status_fully_refunded');
                    $order_item['refund_status'] = 2;
                } else {
                    $order_item['tracking_status'] .= $this->language->get('text_status_partly_refunded');
                    $order_item['refund_status'] = 1;
                }
            }

            if ($result['date_registration'] == '') {
                $order_item['tracking_status'] = $this->language->get('text_status_unregistered');
                $order_item['action'] = 2;
            } elseif (!$result['client_id']) {
                $order_item['tracking_status'] = $this->language->get('text_status_client_id_not_set');
                $order_item['action'] = 3;
            } else {
                $order_item['action'] = $result['purchase_status'];
            }

            $data['orders'][] = $order_item;
        }

        $order_total = $this->{$this->eModel}->getTotalOrders($filter_data);

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['store_id'])) {
            $url .= '&store_id=' . $this->request->get['store_id'];
        }

        $data['sort_order'] = $this->link($this->ePath . '/orders', '&sort=o.order_id' . $url);
        $data['sort_purchase_status'] = $this->link($this->ePath . '/orders', '&sort=oe.purchase_status' . $url);
        $data['sort_order_status'] = $this->link($this->ePath . '/orders', '&sort=order_status' . $url);
        $data['sort_total'] = $this->link($this->ePath . '/orders', '&sort=o.total' . $url);
        $data['sort_date_added'] = $this->link($this->ePath . '/orders', '&sort=o.date_added' . $url);
        $data['sort_date_modified'] = $this->link($this->ePath . '/orders', '&sort=o.date_modified' . $url);

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['store_id'])) {
            $url .= '&store_id=' . $this->request->get['store_id'];
        }

        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->url = $this->link($this->ePath . '/orders', '&page={page}' . $url);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['filter_order_id'] = $filter_order_id;
        $data['filter_order_status'] = $filter_order_status;
        $data['filter_order_status_id'] = $filter_order_status_id;
        $data['filter_total'] = $filter_total;
        $data['filter_date_added'] = $filter_date_added;
        $data['filter_date_modified'] = $filter_date_modified;
        $data['store_id'] = $store_id;

        $data['refund_action'] = $this->link($this->ePath . '/refund', $url);
        $data['add_e_order_action'] = $this->link($this->ePath . '/addEOrder', $url);
        $data['edit_e_order_action'] = $this->link($this->ePath . '/editEOrder', $url);
        $data['default_client_id'] = $this->getClientId();

        $data['track_order_action'] = ($this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG) . 'index.php?route=' . $this->ePath . '/track';

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (substr(VERSION, 0, 7) > '2.1.0.2') {
            $this->response->setOutput($this->load->view($this->ePath . '_orders', $data));
        } else {
            $this->response->setOutput($this->load->view($this->ePath . '_orders.tpl', $data));
        }
    }

    public function addEOrder() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->language($this->ePath);

            $json = array();

            if ($this->validatePermission() && isset($this->request->post['order_id']) && isset($this->request->post['client_id'])) {
                if ((utf8_strlen($this->request->post['client_id']) < 1) || (utf8_strlen($this->request->post['client_id']) > 64)) {
                    $this->error = $this->language->get('error_client_id');
                }

                if (!$this->error) {
                    $this->load->model($this->eModelPath);

                    $this->{$this->eModel}->addEOrder($this->request->post['order_id'], $this->request->post);

                    $json['success'] = array(
                        'type' => 'success',
                        'text' => $this->language->get('text_success'),
                        'icon' => 'fa-check-circle'
                    );

                    $url = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

                    $url .= 'index.php?route=' . $this->ePath . '/track';

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_POST, 1);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->request->post, '', '&', PHP_QUERY_RFC3986));
                    curl_exec($curl);
                    curl_close($curl);
                }

            }

            if ($this->error) {
                $json['error'] = $this->error;
            }

            $this->response->addHeader('Content-Type: application/json');
            return $this->response->setOutput(json_encode($json));
        }
    }

    public function editEOrder() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->language($this->ePath);

            $json = array();

            if ($this->validatePermission() && isset($this->request->post['order_id']) && isset($this->request->post['client_id'])) {
                if ((utf8_strlen($this->request->post['client_id']) < 1) || (utf8_strlen($this->request->post['client_id']) > 64)) {
                    $this->error = $this->language->get('error_client_id');
                }

                if (!$this->error) {
                    $this->load->model($this->eModelPath);

                    $this->{$this->eModel}->editEOrder($this->request->post['order_id'], $this->request->post);

                    $json['success'] = array(
                        'type' => 'success',
                        'text' => $this->language->get('text_success'),
                        'icon' => 'fa-check-circle'
                    );

                    $url = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

                    $url .= 'index.php?route=' . $this->ePath . '/track';

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_POST, 1);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->request->post, '', '&', PHP_QUERY_RFC3986));
                    curl_exec($curl);
                    curl_close($curl);
                }

            }

            if ($this->error) {
                $json['error'] = $this->error;
            }

            $this->response->addHeader('Content-Type: application/json');
            return $this->response->setOutput(json_encode($json));
        }
    }

    public function refund() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['order_id'])) {
            $json = array();

            $this->load->model($this->eModelPath);

            $json['order_products'] = $this->{$this->eModel}->getOrderProducts($this->request->post['order_id']);

            $this->response->addHeader('Content-Type: application/json');

            return $this->response->setOutput(json_encode($json));
        }
    }

    public function apply() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->language($this->ePath);

            $json = array();

            if ($this->validate()) {
                $this->load->model('setting/setting');
                $this->load->model('setting/store');
                $this->load->model($this->eModelPath);

                if (isset($this->request->get['store_id'])){
                    $store_id = $this->request->get['store_id'];
                } else {
                    $store_id = 0;
                }

                if (substr(VERSION, 0, 7) >= '2.0.1.0') {
                    $prev_theme = $this->{$this->eModel}->getSettingValue($this->eName . '_theme', $store_id);

                    if ($prev_theme != $this->request->post['theme']) {
                        $this->installModification($this->request->post['theme'], 'theme');

                        if (substr(VERSION, 0, 7) >= '3.0.0.0') {
                            $this->installEvents($this->request->post['theme'], 'theme');
                        }
                    }

                    $prev_checkout_extension = $this->{$this->eModel}->getSettingValue($this->eName . '_checkout_extension', $store_id);

                    if ($prev_checkout_extension != $this->request->post['checkout_extension']) {
                        $this->installModification($this->request->post['checkout_extension'], 'checkout');

                        if (substr(VERSION, 0, 7) >= '3.0.0.0') {
                            $this->installEvents($this->request->post['checkout_extension'], 'checkout');
                        }
                    }
                } else {
                    $prev_theme = '';
                    $prev_checkout_extension = '';
                }

                foreach ($this->request->post as $key => $item) {
                    $this->request->post[$this->eName . '_' . $key] = $item;
                }

                $this->model_setting_setting->editSetting($this->eName, $this->request->post, $store_id);

                if (substr(VERSION, 0, 7) >= '2.0.1.0') {
                    if ($prev_theme && !$this->{$this->eModel}->checkSettingValueExist($this->eName . '_theme', $prev_theme)) {
                        $this->uninstallModification($this->eCode . '_theme_' . $prev_theme);

                        if (substr(VERSION, 0, 7) >= '3.0.0.0') {
                            $this->{$this->eModel}->uninstallEvents($this->eCode . '_' . $prev_theme);
                        }
                    }

                    if ($prev_checkout_extension && !$this->{$this->eModel}->checkSettingValueExist($this->eName . '_checkout_extension', $prev_checkout_extension)) {
                        $this->uninstallModification($this->eCode . '_checkout_' . $prev_checkout_extension);

                        if (substr(VERSION, 0, 7) >= '3.0.0.0') {
                            $this->{$this->eModel}->uninstallEvents($this->eCode . '_' . $prev_checkout_extension);
                        }
                    }
                }

                $config_settings = $this->model_setting_setting->getSetting('config', $store_id);

                if ($this->request->post['status'] && !in_array($config_settings['config_order_status_id'], $this->request->post['purchase_tracking_status']) && !array_intersect($this->config->get('config_processing_status'), $this->request->post['purchase_tracking_status'])  && !array_intersect($this->config->get('config_complete_status'), $this->request->post['purchase_tracking_status'])) {
                    $link = base64_decode('aHR0cHM6Ly92YW5zdHVkaW8uY28udWE=') . '/redirect?extension_id=' . $this->eId . '&action=image&name=statuses-not-match.jpg';

                    $json['warning']['purchase_tracking_status'] = sprintf($this->language->get('text_statuses_not_match'), $link);
                }

                $json['message'] = array(
                    'type' => 'success',
                    'text' => $this->language->get('text_success')
                );

                $this->cache->delete($this->eName . '.' . $store_id);
            } else {
                $json['error'] = $this->error;
            }

            $this->response->addHeader('Content-Type: application/json');

            return $this->response->setOutput(json_encode($json));
        }
    }

    private function installEvents($name, $type) {
        if (file_exists(DIR_CONFIG . $this->eCode . '/' . $type . '/' . $name . '.php')) {
            $this->load->model($this->eModelPath);

            $this->{$this->eModel}->uninstallEvents($this->eCode . '_' . $name);

            $this->load->config($this->eCode . '/' . $type . '/' . $name);

            $events = $this->config->get($this->eCode . '_event');

            if ($events) {
                $this->{$this->eModel}->installEvents($this->eCode . '_' . $name, $events);
            }
        }
    }

    private function installModification($file_name, $type) {
        if (file_exists(DIR_CONFIG . $this->eCode . '/' . $type . '/' . $file_name . '.php')) {

            $this->load->config($this->eCode . '/' . $type . '/' . $file_name);

            $xml = $this->config->get($this->eCode . '_xml');

            if ($xml) {
                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->loadXml($xml);

                $name = $dom->getElementsByTagName('name')->item(0);

                if ($name) {
                    $name = $name->nodeValue;
                } else {
                    $name = $this->config->get($this->eCode . '_name');
                }

                $code = $dom->getElementsByTagName('code')->item(0);

                if ($code) {
                    $code = $code->nodeValue;
                } else {
                    $code = $this->eCode . '_' . $type . '_' . $file_name;
                }

                $this->uninstallModification($code);

                $author = $dom->getElementsByTagName('author')->item(0);

                if ($author) {
                    $author = $author->nodeValue;
                } else {
                    $author = base64_decode('VmFuU3R1ZGlv');
                }

                $version = $dom->getElementsByTagName('version')->item(0);

                if ($version) {
                    $version = $version->nodeValue;
                } else {
                    $version = $this->eVersion;
                }

                $link = $dom->getElementsByTagName('link')->item(0);

                if ($link) {
                    $link = $link->nodeValue;
                } else {
                    $link = base64_decode('aHR0cHM6Ly92YW5zdHVkaW8uY28udWE=');
                }

                $modification_data = array(
                    'extension_install_id'  => 0,
                    'name'                  => $name,
                    'code'                  => $code,
                    'author'                => $author,
                    'version'               => $version,
                    'link'                  => $link,
                    'xml'                   => $xml,
                    'status'                => 1
                );

                if (substr(VERSION, 0, 7) >= '3.0.0.0') {
                    $this->load->model('setting/modification');

                    $this->model_setting_modification->addModification($modification_data);
                } else {
                    $this->load->model('extension/modification');

                    $this->model_extension_modification->addModification($modification_data);
                }
            }
        }
    }

    private function uninstallModification($code) {
        if (substr(VERSION, 0, 7) >= '3.0.0.0') {
            $this->load->model('setting/modification');

            $modification_info = $this->model_setting_modification->getModificationByCode($code);

            if ($modification_info) {
                $this->model_setting_modification->deleteModification($modification_info['modification_id']);
            }
        } else {
            $this->load->model('extension/modification');

            $modification_info = $this->model_extension_modification->getModificationByCode($code);

            if ($modification_info) {
                $this->model_extension_modification->deleteModification($modification_info['modification_id']);
            }
        }
    }

    private function loadLanguage() {
        $result = array();

        $this->load->language($this->ePath);

        $language_array = $this->load->language($this->ePath);

        foreach ($language_array as $key => $item) {
            if (strpos($key, 'error_') === false) {
                $result[$key] = $item;
            } else {
                $result[$key] = '';
            }
        }

        return $result;
    }

    private function getUrl() {
        $url = '';

        if (isset($this->request->get['store_id'])){
            $url .= '&store_id=' .  $this->request->get['store_id'];
        }
        if (isset($this->request->get['translator'])) {
            $url .= '&translator=' .  $this->request->get['translator'];
        }

        return $url;
    }

    private function getBreadcrumbs() {
        $result = array();

        $result[] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->link('common/dashboard')
        );

        $result[] = array(
            'text' => $this->language->get('text_extensions'),
            'href' => $this->link($this->eDir, '&type=module')
        );

        $result[] = array(
            'text' => $this->language->get('text_title'),
            'href' => $this->link($this->ePath, $this->getUrl())
        );

        return $result;
    }

    private function getLogs($store_id) {
        $result = array();

        $log_file = DIR_LOGS . $this->eName . '.' . $store_id .  '.log';

        $result['refresh_log_action'] = $this->link($this->ePath . '/refreshLog' . $this->getUrl());
        $result['clear_log_action'] = $this->link($this->ePath . '/clearLog' . $this->getUrl());

        if (file_exists($log_file)) {
            $result['install_date'] = date ("d M, Y", filemtime($log_file));

            if (filesize($log_file) > 10000000) {
                $handle = fopen($log_file, 'w+');
                fclose($handle);
            }

            $result['logs'] = html_entity_decode(file_get_contents($log_file, FILE_USE_INCLUDE_PATH, null), ENT_QUOTES, 'UTF-8');
        } else {
            $result['logs'] = '';
        }

        return $result;
    }

    public function refreshLog() {
        $json = array();

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $file = DIR_LOGS . $this->eName . '.' . (isset($this->request->get['store_id']) ? $this->request->get['store_id'] : 0) .  '.log';

            if (file_exists($file)) {
                $json['logs'] = html_entity_decode(file_get_contents($file, FILE_USE_INCLUDE_PATH, null), ENT_QUOTES, 'UTF-8');
            } else {
                $json['logs'] = '';
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function clearLog() {
        $this->load->language($this->ePath);

        $json = array();

        if ($this->validatePermission()) {
            if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
                $file = DIR_LOGS . $this->eName . '.' . (isset($this->request->get['store_id']) ? $this->request->get['store_id'] : 0) .  '.log';

                $handle = fopen($file, 'w+');
                fclose($handle);

                $json['success'] = array(
                    'type' => 'success',
                    'text' => $this->language->get('text_logs_cleared'),
                    'icon' => 'fa-check-circle'
                );
            }
        } else {
            $json['error'] = $this->error;
        }

        $this->response->setOutput(json_encode($json));
    }

    protected function link($path, $url = '') {
        return html_entity_decode($this->url->link($path, $this->eToken . '=' . $this->session->data[$this->eToken] . $url, $this->eSSL));
    }

    protected function validate() {
        if ($this->validatePermission() && $this->request->post['status']) {
            if (isset($this->request->get['store_id'])) {
                $store_id = $this->request->get['store_id'];
            } else {
                $store_id = 0;
            }

            if (!$store_id && (utf8_strlen(trim($this->request->post['license_id'])) < 6 || utf8_strlen(trim($this->request->post['license_id'])) > 8)) {
                $this->error['license_id'] = $this->language->get('error_license_id');
            }

            if (isset($this->request->post['measurement_id']) && utf8_strlen(trim($this->request->post['measurement_id']))) {
                $measurement_ids = explode(',', $this->request->post['measurement_id']);

                foreach ($measurement_ids as $measurement_id) {
                    if (substr(strtoupper(trim($measurement_id)), 0, 2 ) !== 'G-') {
                        $this->error['measurement_id'] = $this->language->get('error_measurement_id');
                    } else if (utf8_strlen(trim($measurement_id)) < 12 || utf8_strlen(trim($measurement_id)) > 14) {
                        $this->error['measurement_id'] = $this->language->get('error_measurement_id_length');
                    }
                }
            } else {
                $this->error['measurement_id'] = $this->language->get('error_measurement_id_empty');
            }

            if ($this->request->post['type'] == 0) {
                if (isset($this->request->post['measurement_secret']) && utf8_strlen(trim($this->request->post['measurement_secret']))) {
                    $measurement_secrets = explode(',', $this->request->post['measurement_secret']);

                    foreach ($measurement_secrets as $measurement_secret) {
                        if (utf8_strlen(trim($measurement_secret)) < 22 || utf8_strlen(trim($measurement_secret)) > 26) {
                            $this->error['measurement_secret'] = $this->language->get('error_measurement_secret_length');
                        }
                    }
                } else {
                    $this->error['measurement_secret'] = $this->language->get('error_measurement_secret_empty');
                }
            }

            if (isset($this->request->post['purchase_status']) && $this->request->post['purchase_status']) {
                if (!isset($this->request->post['purchase_tracking_status']) || !$this->request->post['purchase_tracking_status']) {
                    $this->error['purchase_tracking_status'] = $this->language->get('error_order_status');
                }
            }

            if (isset($this->request->post['refund_status']) && $this->request->post['refund_status']) {
                if (!isset($this->request->post['refund_tracking_status']) || !$this->request->post['refund_tracking_status']) {
                    $this->error['refund_tracking_status'] = $this->language->get('error_order_status');
                }
            }

            if ($this->error && !isset($this->error['warning'])) {
                $this->error['message'] = $this->language->get('error_warning');
            }
        }

        return !$this->error;
    }

    private function validatePermission() {
        if (!$this->user->hasPermission('modify', $this->ePath)) {
            $this->error['message'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install() {
        $this->load->model('setting/store');
        $this->load->model('setting/setting');
        $this->load->model($this->eModelPath);

        $result = $this->{$this->eModel}->install($this->eCode, $this->ePath);

        $general_setting = array(
            $this->eName . '_status' => 0,
            $this->eName . '_type' => 1,
            $this->eName . '_js_position' => 0,
            $this->eName . '_tax' => $this->config->get('config_tax'),
            $this->eName . '_total_shipping' => 1,
            $this->eName . '_total_tax' => 1,
            $this->eName . '_timestamp' => 1,
            $this->eName . '_purchase_status' => 1,
            $this->eName . '_purchase_tracking_status' => array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')),
            $this->eName . '_exclude_u_agent' => 'bot|crawl|slurp|spider|mediapartners|google'
        );

        $general_setting[$this->eName . '_purchase_tracking_status'][] = $this->config->get('config_order_status_id');

        $general_setting[$this->eName . '_theme'] = $this->installThemeSettings();

        if ($general_setting[$this->eName . '_theme'] === 'journal3.2') {
            $general_setting[$this->eName . '_js_position'] = 1;
        }

        $general_setting[$this->eName . '_checkout_extension'] = $this->installCheckoutSettings();

        if ($this->config->get('config_fraud_status_id') && !in_array($this->config->get('config_fraud_status_id'), $general_setting[$this->eName . '_purchase_tracking_status'])) {
            $general_setting[$this->eName . '_refund_tracking_status'][] = $this->config->get('config_fraud_status_id');
        }

        if (!in_array(11, $general_setting[$this->eName . '_purchase_tracking_status'])) {
            $general_setting[$this->eName . '_refund_tracking_status'][] = 11;
        }

        $stores = $this->model_setting_store->getStores();

        foreach ($stores as $store) {
            $multistore_setting = $general_setting;

            $config_order_status_id = $this->{$this->eModel}->getSettingValue('config_order_status_id', $store['store_id']);

            $multistore_setting[$this->eName . '_purchase_tracking_status'][] = $config_order_status_id;

            if ($config_order_status_id == 11 && ($key = array_search(11, $multistore_setting[$this->eName . '_refund_tracking_status'])) !== false) {
                unset($multistore_setting[$this->eName . '_refund_tracking_status'][$key]);
            }

            $multistore_setting[$this->eName . '_theme'] = $this->installThemeSettings($store['store_id']);

            $multistore_setting[$this->eName . '_checkout_extension'] = $this->installCheckoutSettings($store['store_id']);

            $this->model_setting_setting->editSetting($this->eName, $multistore_setting, $store['store_id']);
        }

        $this->model_setting_setting->editSetting($this->eName, $general_setting, 0);

        if ($result) {
            if (substr(VERSION, 0, 7) > '2.3.0.2') {
                $this->load->controller('marketplace/modification/refresh', array('redirect' => 'extension/extension/module'));
            } else {
                $this->load->controller('extension/modification/refresh');
            }
        }
    }

    private function installThemeSettings($store_id = 0) {
        $theme = 0;
        $supported_themes = array();

        $theme_files = glob(DIR_CONFIG . $this->eCode . '/theme/*');
        foreach ($theme_files as $theme_file) {
            $supported_themes[] = pathinfo($theme_file, PATHINFO_FILENAME);
        }

        if (substr(VERSION, 0, 7) < '2.2.0.0') {
            $theme_option_name = 'config_template';
        } else {
            $theme_option_name = 'config_theme';
        }

        $config_theme = $this->{$this->eModel}->getSettingValue($theme_option_name, $store_id);

        if ($config_theme == 'default' || $config_theme == 'theme_default') {
            $config_theme_key = 'theme_default_directory';
        } else {
            $config_theme_key = $theme_option_name;
        }

        $theme_dir = $this->{$this->eModel}->getSettingValue($config_theme_key, $store_id);

        if (in_array(str_replace('theme_', '', $theme_dir), $supported_themes)) {
            $theme = str_replace('theme_', '', $theme_dir);

            if ($theme == 'journal3' && defined('JOURNAL3_INSTALLED') && substr(JOURNAL3_INSTALLED, 0, 5) >= '3.2.0') {
                $theme = 'journal3.2';
            }

            if (substr(VERSION, 0, 7) >= '2.0.1.0') {
                $this->installModification($theme, 'theme');
            }

            if (substr(VERSION, 0, 7) >= '3.0.0.0') {
                $this->installEvents($theme, 'theme');
            }
        }

        return $theme;
    }

    private function installCheckoutSettings($store_id = 0) {
        $key_prefix = str_replace($this->eCode, '', $this->eName);
        $extension = 0;

        $checkout_extensions = glob(DIR_CONFIG . $this->eCode . '/checkout/*');

        foreach ($checkout_extensions as $checkout_extension) {
            $config_file_name = pathinfo($checkout_extension, PATHINFO_FILENAME);

            $this->load->config($this->eCode . '/checkout/' . $config_file_name);

            $table_exist = 0;
            if ($this->config->get($this->eCode . '_table_name')) {
                if ($this->{$this->eModel}->getTableExist($this->config->get($this->eCode . '_table_name'))) {
                    $table_exist = 1;

                    if ($this->config->get($this->eCode . '_table_name') == 'xtensions') {
                        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xtensions` WHERE `module` = 'xtensions_best_checkout' AND `key` = 'status' AND `value` = 1 AND `store_id` = '" . (int)$store_id . "'");

                        if ($query->row) {
                            $extension = 'xtensions';
                        }
                    }
                } else {
                    $this->config->set($this->eCode . '_setting_key', 0);
                }
                $this->config->set($this->eCode . '_table_name', 0);
            }

            if ($this->config->get($this->eCode . '_setting_key')) {
                $extension_status = $this->{$this->eModel}->getSettingValue($this->config->get($this->eCode . '_setting_key'), $store_id);

                if ($extension_status) {
                    $extension = $config_file_name;
                }

                if ($key_prefix) {
                    $extension_status = $this->{$this->eModel}->getSettingValue($key_prefix . $this->config->get($this->eCode . '_setting_key'), $store_id);

                    if ($extension_status) {
                        $extension = $config_file_name;
                    }
                }

                if ($extension && $table_exist) {
                    break;
                }

                $this->config->set($this->eCode . '_setting_key', 0);
            }
        }

        if ($extension) {
            if (substr(VERSION, 0, 7) >= '2.0.1.0') {
                $this->installModification($extension, 'checkout');
            }

            if (substr(VERSION, 0, 7) >= '3.0.0.0') {
                $this->installEvents($extension, 'checkout');
            }
        }

        return $extension;
    }

    public function uninstall() {
        $this->load->model('setting/store');
        $this->load->model('setting/setting');
        $this->load->model($this->eModelPath);

        $this->model_setting_setting->deleteSetting($this->eName, 0);
        $this->cache->delete($this->eName . '.0');
        $log_file = DIR_LOGS . $this->eName . '.0.log';
        if (file_exists($log_file)) {
            $handle = fopen($log_file, 'w+');
            fclose($handle);
        }

        $stores = $this->model_setting_store->getStores();

        foreach ($stores as $store) {
            $this->model_setting_setting->deleteSetting($this->eName, $store['store_id']);
            $this->cache->delete($this->eName . '.' . $store['store_id']);
            $log_file = DIR_LOGS . $this->eName . '.' . $store['store_id'] .  '.log';
            if (file_exists($log_file)) {
                $handle = fopen($log_file, 'w+');
                fclose($handle);
            }
        }

        $result = $this->{$this->eModel}->uninstall($this->eCode);

        if ($result) {
            if (substr(VERSION, 0, 7) > '2.3.0.2') {
                $this->load->controller('marketplace/modification/refresh', array('redirect' => 'extension/extension/module'));
            } else {
                $this->load->controller('extension/modification/refresh');
            }
        }
    }

    protected function getUserIP() {
        if (!empty($this->request->server['HTTP_CLIENT_IP'])) {
            $ip_address = $this->request->server['HTTP_CLIENT_IP'];
        } elseif (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_address = $this->request->server['REMOTE_ADDR'];
        }

        return $ip_address;
    }

    protected function getCurrentIP() {
        if (!empty($this->request->server['HTTP_CLIENT_IP'])) {
            $ip_address = $this->request->server['HTTP_CLIENT_IP'];
        } elseif (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_address = $this->request->server['REMOTE_ADDR'];
        }

        return $ip_address;
    }

    protected function getClientId() {
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

        return $client_id;
    }
}