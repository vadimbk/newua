<?php
/*
 *******************************************************************************
 *  Module: Bulk specials editor + the countdown timer
 *
 *  Web-site: http://opencart-modules.com
 *  Email: dev.dashko@gmail.com
 *  © Leonid Dashko
 *
 *  Below source-code or any part of the source-code cannot be resold or distributed.
 ******************************************************************************
 */

class ControllerExtensionModuleTimer extends Controller
{
    private $errors             = array();
    private $_module_name       = 'module/timer';
    private $_module_model_name = 'extension/module/timer';
    private $_module_model_path = 'model_extension_module_timer';

    private $_model_name_customer_group = 'sale/customer_group';
    private $_model_path_customer_group = 'model_sale_customer_group';

    private $_model_name_module_hours_and_days = 'module/hours_and_days';
    private $_model_path_module_hours_and_days = 'model_module_hours_and_days';

    public function __construct($registry)
    {
        parent::__construct($registry);

        if (version_compare(VERSION, '2.1.0.0', '>=')) {
            $this->_model_name_customer_group = "customer/customer_group";
            $this->_model_path_customer_group = "model_customer_customer_group";
        }

        if (version_compare(VERSION, '2.3.0.0', '>=')) {
            $this->_module_name = 'extension/module/timer';

            $this->_model_name_module_hours_and_days = 'extension/module/hours_and_days';
            $this->_model_path_module_hours_and_days = 'model_extension_module_hours_and_days';
        }

        $this->load->model('setting/setting');
        $this->load->model($this->_model_name_customer_group);
        $this->load->model($this->_module_model_name);

        if ($this->{$this->_module_model_path}->getHoursDaysStatus()) {
            $this->load->model($this->_model_name_module_hours_and_days);
        }

        $this->_loadLang();
    }

    public function install()
    {
        # Check for the existance of the column "timer"
        $column_exist = $this->db->query("SELECT column_name from information_schema.columns where table_schema='" . DB_DATABASE . "' and table_name='" . DB_PREFIX . "product_special' and column_name='timer'")->rows;

        if (!isset($column_exist[0]['column_name'])) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "product_special ADD timer int(1) NOT NULL Default 1");
        }

        # Check for the existance of the column "product_special_group_id"
        $column_exist = $this->db->query("SELECT column_name from information_schema.columns where table_schema='" . DB_DATABASE . "' and table_name='" . DB_PREFIX . "product_special' and column_name='product_special_group_id'")->rows;

        if (!isset($column_exist[0]['column_name'])) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "product_special ADD product_special_group_id int(11) NOT NULL");
        }

        # Turn on default options
        $settings = array('show_column_photo', 'show_column_category', 'show_column_manufacturer', 'show_column_status', 'show_column_quantity', 'show_column_old_price', 'show_filter_category', 'show_filter_manufacturer', 'show_filter_customer_groups', 'show_filter_status', 'show_filter_old_price', 'show_filter_old_price', 'show_filter_special_price', 'show_filter_special_date', 'show_filter_quantity');

        $settings = array_fill_keys($settings, 1);

        $this->model_setting_setting->editSetting('timer', array(
            'timer_additional_admin_settings' => $settings,
        ));

        // Creating the table for product special groups
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_special_group` (
              `product_special_group_id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              PRIMARY KEY (`product_special_group_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
    }

    public function uninstall()
    {
        # Delete additional columns that were created earlier
        $column_exist = $this->db->query("SELECT column_name from information_schema.columns where table_schema='" . DB_DATABASE . "' and table_name='" . DB_PREFIX . "product_special' and column_name='timer'")->rows;

        if (isset($column_exist[0]['column_name'])) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "product_special DROP COLUMN timer");
        }

        $column_exist = $this->db->query("SELECT column_name from information_schema.columns where table_schema='" . DB_DATABASE . "' and table_name='" . DB_PREFIX . "product_special' and column_name='product_special_group_id'")->rows;

        if (isset($column_exist[0]['column_name'])) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "product_special DROP COLUMN product_special_group_id");
        }
    }

    public function index()
    {
        $data = $this->_loadLang();

        $data['text_weekdays'] = $this->language->get('weekdays');
        $data['text_hours'] = $this->language->get('hours');

        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');

        $this->document->setTitle(str_replace(array("<b>", "</b>"), "", $this->language->get('heading_title')));

        $this->document->addStyle('view/stylesheet/timer.css');
        $this->document->addStyle('view/stylesheet/colorpicker.css');
        $this->document->addStyle('view/javascript/multiselect/multiple-select.css');

        $this->document->addScript('view/javascript/timer/jquery.onoff.js');
        $this->document->addScript('view/javascript/timer/colorpicker.js');
        $this->document->addScript('view/javascript/multiselect/multiple-select.js');

        $timer = array();

        $data['url_path_to_module'] = "index.php?route=" . $this->_module_name;
        $data['customer_groups']    = $this->_getCustomerGroups();

        # Loading General Settings
        $general_settings = $this->config->get('timer_general_settings');
        if (!empty($general_settings)) {
            foreach ($general_settings as $key => $value) {
                $data['general_settings'][$key] = $value;
            }
        }

        # Loading Additional Settings For admin panel
        $additional_admin_settings = $this->config->get('timer_additional_admin_settings');
        if (!empty($additional_admin_settings)) {
            foreach ($additional_admin_settings as $key => $value) {
                $data['additional_admin_settings'][$key] = $value;
            }
        }

        // Loading Additional Settings for Catalog section
        $additional_catalog_settings = $this->config->get('timer_additional_catalog_settings');
        if (!empty($additional_catalog_settings)) {
            foreach ($additional_catalog_settings as $key => $value) {
                $data['additional_catalog_settings'][$key] = $value;
            }
        }

        # Show Success msgs
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        # Show Errors msgs
        if (!empty($this->errors)) {
            $this->session->data['errors'] = $this->errors;
        }

        if (isset($this->session->data['errors'])) {
            $data['errors'] = $this->session->data['errors'];
            unset($this->session->data['errors']);
        } else {
            $data['errors'] = '';
        }

        # Start Filtering
        $url = '';

        $data['hours_days_status'] = $this->{$this->_module_model_path}->getHoursDaysStatus();

        // Check if the module "Hours and days" exists and enabled
        if ($this->{$this->_module_model_path}->getHoursDaysStatus()) {

            # Filter by Product Special weekdays
            if (isset($this->request->get['filter_weekdays'])) {
                $url .= '&filter_weekdays=' . $this->request->get['filter_weekdays'];
                $filter_weekdays = trim($this->request->get['filter_weekdays']);
            } else {
                $filter_weekdays = null;
            }

            # Filter by Product Special hours
            if (isset($this->request->get['filter_hours'])) {
                $url .= '&filter_hours=' . $this->request->get['filter_hours'];
                $filter_hours = trim($this->request->get['filter_hours']);
            } else {
                $filter_hours = null;
            }

            # Loading weekdays and hours labels
            $data['weekdays'] = $this->{$this->_model_path_module_hours_and_days}->getWeekdays();
            $data['hours']    = $this->{$this->_model_path_module_hours_and_days}->getHours();
        }

        # Filter by Product Name
        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = null;
        }

        # Filter by Product Model
        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
            $filter_model = $this->request->get['filter_model'];
        } else {
            $filter_model = null;
        }

        # Filter by Product Special date
        if (isset($this->request->get['filter_special_date_from'])) {
            $url .= '&filter_special_date_from=' . $this->request->get['filter_special_date_from'];
            $filter_special_date_from = trim($this->request->get['filter_special_date_from']);
        } else {
            $filter_special_date_from = null;
        }

        if (isset($this->request->get['filter_special_date_to'])) {
            $url .= '&filter_special_date_to=' . $this->request->get['filter_special_date_to'];
            $filter_special_date_to = trim($this->request->get['filter_special_date_to']);
        } else {
            $filter_special_date_to = null;
        }

        # Filter by Ordinary Price
        if (isset($this->request->get['filter_price_from'])) {
            $url .= '&filter_price_from=' . (float) $this->request->get['filter_price_from'];
            $filter_price_from = (float) $this->request->get['filter_price_from'];
        } else {
            $filter_price_from = null;
        }

        if (isset($this->request->get['filter_price_to'])) {
            $url .= '&filter_price_to=' . (float) $this->request->get['filter_price_to'];
            $filter_price_to = (float) $this->request->get['filter_price_to'];
        } else {
            $filter_price_to = null;
        }

        # Filter by Special Price
        if (isset($this->request->get['filter_special_price_from'])) {
            $url .= '&filter_special_price_from=' . (float) $this->request->get['filter_special_price_from'];
            $filter_special_price_from = (float) $this->request->get['filter_special_price_from'];
        } else {
            $filter_special_price_from = null;
        }

        if (isset($this->request->get['filter_special_price_to'])) {
            $url .= '&filter_special_price_to=' . (float) $this->request->get['filter_special_price_to'];
            $filter_special_price_to = (float) $this->request->get['filter_special_price_to'];
        } else {
            $filter_special_price_to = null;
        }

        # Filter by Quantity
        if (isset($this->request->get['filter_quantity_from'])) {
            $url .= '&filter_quantity_from=' . (float) $this->request->get['filter_quantity_from'];
            $filter_quantity_from = (float) $this->request->get['filter_quantity_from'];
        } else {
            $filter_quantity_from = null;
        }

        if (isset($this->request->get['filter_quantity_to'])) {
            $url .= '&filter_quantity_to=' . (float) $this->request->get['filter_quantity_to'];
            $filter_quantity_to = (float) $this->request->get['filter_quantity_to'];
        } else {
            $filter_quantity_to = null;
        }

        # Filter by Category
        if (isset($this->request->get['filter_category'])) {
            $url .= '&filter_category=' . (float) $this->request->get['filter_category'];
            $filter_category = $this->request->get['filter_category'];
        } else {
            $filter_category = null;
        }

        # Filter by Manufacturer
        if (isset($this->request->get['filter_manufacturer'])) {
            $url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
            $filter_manufacturer = $this->request->get['filter_manufacturer'];
        } else {
            $filter_manufacturer = null;
        }

        # Filter by Customer Group
        if (isset($this->request->get['filter_customer_groups'])) {
            $url .= '&filter_customer_groups=' . $this->request->get['filter_customer_groups'];
            $filter_customer_groups = $this->db->escape($this->request->get['filter_customer_groups']);
        } else {
            $filter_customer_groups = null;
        }

        # Filter by Special Groups
        if (isset($this->request->get['filter_special_group'])) {
            $url .= '&filter_special_group=' . $this->request->get['filter_special_group'];
            $filter_special_group = $this->request->get['filter_special_group'];
        } else {
            $filter_special_group = null;
        }

        # Filter by Product Status
        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = null;
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $filter_data = array(
            'filter_name'               => $filter_name,
            'filter_model'              => $filter_model,
            'filter_weekdays'           => ($this->{$this->_module_model_path}->getHoursDaysStatus()) ? $filter_weekdays : null,
            'filter_hours'              => ($this->{$this->_module_model_path}->getHoursDaysStatus()) ? $filter_hours : null,
            'filter_special_date_from'  => $filter_special_date_from,
            'filter_special_date_to'    => $filter_special_date_to,
            'filter_price_from'         => $filter_price_from,
            'filter_price_to'           => $filter_price_to,
            'filter_special_price_from' => $filter_special_price_from,
            'filter_special_price_to'   => $filter_special_price_to,
            'filter_quantity_from'      => $filter_quantity_from,
            'filter_quantity_to'        => $filter_quantity_to,
            'filter_category'           => $filter_category,
            'filter_manufacturer'       => $filter_manufacturer,
            'filter_customer_groups'    => $filter_customer_groups,
            'filter_special_group'      => $filter_special_group,
            'filter_status'             => $filter_status,
            'start'                     => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'                     => $this->config->get('config_limit_admin'),
        );

        $this->load->model('tool/image');

        # Output all special products
        $data['product_specials'] = array();
        $results                  = $this->{$this->_module_model_path}->getProductsSpecials($filter_data);

        foreach ($results as $result) {

            # Filter by Categories and Manufacturers
            $category     = $this->model_catalog_product->getProductCategories($result['product_id']);
            $manufacturer = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);

            if (is_file(DIR_IMAGE . $result['image'])) {
                $image = $this->model_tool_image->resize($result['image'], 40, 40);
            } else {
                $image = $this->model_tool_image->resize('no_image.png', 40, 40);
            }

            $prod_special_info = array(
                'product_special_id'  => $result['product_special_id'],
                'image'               => $image,
                'name'                => $result['name'],
                'category'            => $category,
                'manufacturer'        => $manufacturer,
                'status'              => ($result['status']) ? $this->language->get('on') : $this->language->get('off'),
                'quantity'            => $result['quantity'],
                'old_price'           => $this->_price_round($result['old_price']),
                'customer_group_id'   => $result['customer_group_id'],
                'customer_group_name' => $this->_getCustomerGroupName($result['customer_group_id']),
                'special_group_id'    => $result['special_group_id'],
                'special_group_name'  => $this->_getSpecialGroupName($result['special_group_id']),
                'priority'            => $result['priority'],
                'special_price'       => $this->_price_round($result['special_price']),
                'special_date_end'    => $result['special_date_end'],
                'special_date_start'  => $result['special_date_start'],
                'timer_status'        => $result['timer_status'],
            );

            if ($this->{$this->_module_model_path}->getHoursDaysStatus()) {
                $prod_special_info = array_merge($prod_special_info, array(
                    'special_weekdays'         => $result['special_weekdays'],
                    'special_weekdays_tooltip' => $this->_prepareTooltipInfo($result['special_weekdays'], $data['weekdays']),
                    'special_hours'            => $result['special_hours'],
                    'special_hours_tooltip'    => $this->_prepareTooltipInfo($result['special_hours'], $data['hours']),
                ));
            }

            $data['product_specials'][] = $prod_special_info;
        }

        # Breadcrumbs
        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );

        // In OpenCart 2.3.x the link to the modules is a bit different
        if (version_compare(VERSION, '2.3.0.0', '<')) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_module'),
                'href' => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_module'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL'),
            );
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->_module_name, 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );

        # Actions
        $data['action'] = $this->url->link($this->_module_name, 'user_token=' . $this->session->data['user_token'], 'SSL');

        if (version_compare(VERSION, '2.3.0.0', '<')) {
            $data['cancel'] = $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL');
        } else {
            $data['cancel_link'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL');
        }

        $data['action_edit_selected_specials'] = $this->url->link($this->_module_name . '/selected_specials', 'user_token=' . $this->session->data['user_token'] . '&edit=1', 'SSL');
        $data['action_delete_selected']        = $this->url->link($this->_module_name . '/selected_specials', 'user_token=' . $this->session->data['user_token'] . '&delete=1', 'SSL');
        $data['action_delete_all_specials']    = $this->url->link($this->_module_name . '/delete_all_specials', 'user_token=' . $this->session->data['user_token'], 'SSL');

        $data['link_to_support'] = 'http://opencart-modules.com/tab-modules?lang=' . trim($this->config->get('config_admin_language'));

        # Output the template
        $this->template = $this->_module_name . '/main';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        # Start Pagination process
        $product_total = $this->{$this->_module_model_path}->getTotalProductsSpecials($filter_data);

        $pagination        = new Pagination();
        $pagination->total = $product_total;
        $pagination->page  = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->text  = $this->language->get('text_pagination');

        $pagination->url = $this->url->link($this->_module_name, 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');

        $data['pagination'] = $pagination->render();
        $data['results']    = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));
        # End Pagination process

        $data['filter_name']  = $filter_name;
        $data['filter_model'] = $filter_model;

        if ($this->{$this->_module_model_path}->getHoursDaysStatus()) {
            $data['filter_weekdays'] = $filter_weekdays;
            $data['filter_hours']    = $filter_hours;
        }

        $data['filter_special_date_from']  = $filter_special_date_from;
        $data['filter_special_date_to']    = $filter_special_date_to;
        $data['filter_price_from']         = $filter_price_from;
        $data['filter_price_to']           = $filter_price_to;
        $data['filter_special_price_from'] = $filter_special_price_from;
        $data['filter_special_price_to']   = $filter_special_price_to;
        $data['filter_quantity_from']      = $filter_quantity_from;
        $data['filter_quantity_to']        = $filter_quantity_to;
        $data['filter_category']           = $filter_category;
        $data['filter_manufacturer']       = $filter_manufacturer;
        $data['filter_customer_groups']    = is_null($filter_customer_groups) ? array() : explode('_', $filter_customer_groups);
        $data['filter_special_group']      = $filter_special_group;
        $data['filter_status']             = $filter_status;

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $data['user_token'] = $this->session->data['user_token'];

        # Loading Category_list.tpl
        $data['categories']    = $this->{$this->_module_model_path}->getCategories(0);
        $data['category_list'] = $this->load->view($this->_module_name . '/part_category_list', $data);

        # Loading Manufacturer_list.tpl
        $this->load->model('catalog/manufacturer');
        $data['manufacturers']     = $this->model_catalog_manufacturer->getManufacturers(0);
        $data['manufacturer_list'] = $this->load->view($this->_module_name . '/part_manufacturer_list', $data);

        # Loading Specials groups
        $data['special_groups'] = $this->{$this->_module_model_path}->getSpecialGroups();

        # Loading Special_params.tpl
        $data['special_params'] = $this->load->view($this->_module_name . '/part_special_params', $data);

        $data['overwrite']                   = true;
        $data['special_params_for_selected'] = $this->load->view($this->_module_name . '/part_special_params', $data);

        # Loading TAB Additional settings on the site (additional_catalog_settings.tpl)
        $data['additional_catalog_settings_form']  = $this->load->view($this->_module_name . '/part_additional_catalog_settings', $data);
        $data['include_products_html']             = $this->load->view($this->_module_name . '/part_include_products', $data);
        $data['filters_edit_delete_specials_html'] = $this->load->view($this->_module_name . '/part_filters_edit_delete_specials', $data);

        # Loading JS_part.tpl
        $data['js_part'] = $this->load->view($this->_module_name . '/js_part', $data);

        $this->_loadLang();
    
        $this->response->setOutput($this->load->view($this->_module_name . '/main', $data));
    }

    # Save General Settings
    public function save_general_settings()
    {
        $post = $this->request->post;

        $data['result'] = array();
        $data['errors'] = array();

        if (!$this->user->hasPermission('modify', $this->_module_name)) {
            $data['errors'][] = $this->language->get('error_permission');
        }

        if (empty($data['errors']) && isset($post)) {
            $settings = $this->model_setting_setting->getSetting('timer');

            $this->model_setting_setting->editSetting('timer', array(
                'timer_general_settings'            => $post,
                'timer_additional_admin_settings'   => $settings['timer_additional_admin_settings'],
                'timer_additional_catalog_settings' => $settings['timer_additional_catalog_settings'],
            ));

            $data['result'][] = $this->language->get('settings_saved_successfully');
        }

        $this->response->setOutput(json_encode($data));
    }

    # Save Additional Settings
    public function save_additional_settings()
    {
        $post = $this->request->post;

        $data['result'] = array();
        $data['errors'] = array();

        if (!$this->user->hasPermission('modify', $this->_module_name)) {
            $data['errors'][] = $this->language->get('error_permission');
        }

        if (empty($data['errors']) && isset($post)) {
            $settings = $this->model_setting_setting->getSetting('timer');

            # if we are saving admin settings
            if (isset($post['admin'])) {
                unset($post['admin']);
                $this->model_setting_setting->editSetting('timer', array(
                    'timer_general_settings'            => $settings['timer_general_settings'],
                    'timer_additional_admin_settings'   => $post,
                    'timer_additional_catalog_settings' => $settings['timer_additional_catalog_settings'],
                ));
            }

            # if we are saving catalog settings
            if (isset($post['catalog'])) {
                unset($post['catalog']);
                $this->model_setting_setting->editSetting('timer', array(
                    'timer_general_settings'            => $settings['timer_general_settings'],
                    'timer_additional_admin_settings'   => $settings['timer_additional_admin_settings'],
                    'timer_additional_catalog_settings' => $post,
                ));
            }

            $data['result'][] = $this->language->get('settings_saved_successfully');
        }

        $this->response->setOutput(json_encode($data));
    }

    # Check saved special params
    public function ajax_update_special()
    {
        $errors = array();
        $post   = $this->request->post;

        $post['date_start'] = ($post['date_start'] != '') ? $post['date_start'] : '0000-00-00';
        $post['date_end']   = ($post['date_end'] != '') ? $post['date_end'] : '0000-00-00';
        $post['timer']      = (isset($post['timer'])) ? $post['timer'] : 0;

        if ($this->{$this->_module_model_path}->getHoursDaysStatus()) {
            if (isset($post['weekdays']) && !empty($post['weekdays']) && is_array($post['weekdays'])) {
                $post['weekdays'] = implode(',', $post['weekdays']);
            } else {
                $post['weekdays'] = '';
            }

            if (isset($post['hours']) && !empty($post['hours']) && is_array($post['hours'])) {
                $post['hours'] = implode(',', $post['hours']);
            } else {
                $post['hours'] = '';
            }
        }

        $post['price']     = str_replace(' ', '', $post['price']);
        $post['price']     = (float) $post['price'];
        $post['old_price'] = (float) $post['old_price'];

        # START Checking for errors
        if ($post['price'] == 0) {
            $errors['special_price'] = $this->language->get('error_price');
        }

        if ($post['price'] > $post['old_price']) {
            $errors['special_price'] = $this->language->get('error_exceeding_price_limit');
        }

        if (strtotime($post['date_start']) > strtotime($post['date_end']) && $post['date_end'] != '0000-00-00') {
            $errors['date_start'] = $this->language->get('error_exceeding_date_period');
        }

        if (!$this->user->hasPermission('modify', $this->_module_name)) {
            $errors['error_permission'] = $this->language->get('error_permission');
        }
        # END Checking for errors

        if (empty($errors)) {
            $result = $this->{$this->_module_model_path}->updateProductSpecialBySpecialId($post);

            if ($result !== false) {
                $result          = $result[0];
                $result['price'] = $this->_price_round($result['price']);

                # Show timer on or off
                $result['timer_status'] = ($result['timer'] == 1) ? $this->language->get('on') : $this->language->get('off');

                $result['customer_group_name'] = $this->_getCustomerGroupName($result['customer_group_id']);
                // $result['customer_group_name'] = $result['customer_group_name']['name'];

                $result['special_group_name'] = $this->_getSpecialGroupName($result['special_group_id']);

                // Return tooltips icons for weekdays and hours
                if ($this->{$this->_module_model_path}->getHoursDaysStatus()) {
                    $result['weekdays_tooltip'] = $this->_prepareTooltipInfo($result['weekdays'], $this->{$this->_model_path_module_hours_and_days}->getWeekdays());
                    $result['hours_tooltip']    = $this->_prepareTooltipInfo($result['hours'], $this->{$this->_model_path_module_hours_and_days}->getHours());
                }

                $result['status'] = 'success';
            } else {
                $result['status'] = 'error';
            }
        } else {
            $result['errors'] = $errors;
            $result['status'] = 'error';
        }

        echo json_encode($result);
    }

    # Set specials by Products, Categories, Manufacturers
    public function ajax_set_specials()
    {
        $post = $this->request->post;

        # Validate Specials Params
        // $products                 = array();
        $price_exceeding_products = array();

        $data['result'] = array();
        $data['errors'] = $this->validateSpecialsParams($post);

        $products = $this->{$this->_module_model_path}->getPossibleProducts($post);
        $products = $products["products"];

        if (empty($data['errors']) && isset($post) && !empty($post) && !empty($products)) {
            // For multiple customer groups
            $customer_groups = array_map('intval', explode('_', $post['customer_group_id']));
            $specials_info   = array(
                'date_start'                        => $post['date_start'],
                'date_end'                          => $post['date_end'],
                'timer'                             => (isset($post['timer_status']) ? 1 : 0),
                // 'customer_group_id'                 => $post['customer_group_id'],
                'special_group_id'                  => (int) $post['special_group_id'],
                'priority'                          => (int) $post['priority'],
                'price'                             => 0.0,
                'ignore_creation_if_special_exists' => (isset($post['ignore_creation_if_special_exists']) ? 1 : 0),
            );

            if ($this->{$this->_module_model_path}->getHoursDaysStatus()) {
                $post['weekdays'] = isset($post['weekdays']) ? $post['weekdays'] : '';
                $post['hours']    = isset($post['hours']) ? $post['hours'] : '';

                if (!empty($post['weekdays']) && is_array($post['weekdays'])) {
                    $post['weekdays'] = implode(',', $post['weekdays']);
                }

                if (!empty($post['hours']) && is_array($post['hours'])) {
                    $post['hours'] = implode(',', $post['hours']);
                }

                $specials_info = array_merge($specials_info, array(
                    'weekdays' => $post['weekdays'],
                    'hours'    => $post['hours'],
                ));
            }

            # process products specials adding
            foreach ($products as $product) {
                $exceed_price = false;

                # check on the excess of the price
                # if user chose percentage discount
                if ($post['discount_type'] == 'percentage') {
                    $specials_info['price'] = (float) ((100 - ($post['total_discount'])) / 100) * $product['price'];
                } else if ($post['discount_type'] == 'currency') {
                    # if user chose currency discount
                    if ($product['price'] <= $post['total_discount']) {
                        $exceed_price = true;
                    } else {
                        $specials_info['price'] = (float) ($product['price'] - $post['total_discount']);
                    }
                }

                if ($exceed_price === false) {
                    // Add special for every customer group
                    foreach ($customer_groups as $customer_group_id) {
                        $specials_info['customer_group_id'] = $customer_group_id;

                        $result = $this->{$this->_module_model_path}->setNewProductSpecial($product['product_id'], $specials_info);
                    }
                } else {
                    $price_exceeding_products[] = $product['name'];
                    $result                     = false;
                }

                # Write down -un/successfully added products
                if ($result) {
                    $successfully_added_special[] = $product['name'];
                }

            }

            # Write -un/successfully added products in messages
            # Output result of operation
            if (!empty($successfully_added_special)) {
                $data['result'][] = $this->language->get('successfully_added_specials') . ' <br><b>' . implode(", ", $successfully_added_special) . '</b>';
            }

            if (!empty($price_exceeding_products)) {
                $data['errors'][] = $this->language->get('error_price_exceeding_products') . ' <br><b>' . implode(", ", $price_exceeding_products) . '</b>';
            }

            if (empty($successfully_added_special) && empty($price_exceeding_products)) {
                $data['errors'][] = $this->language->get('error_products_not_found');
            }

        }

        $this->response->setOutput(json_encode($data));
    }

    # Validate Specials transmitted by AJAX
    private function validateSpecialsParams($post)
    {
        if (!empty($post)) {
            # Check user's permission
            if (!$this->user->hasPermission('modify', $this->_module_name)) {
                $this->errors[] = $this->language->get('error_permission');
            } else {
                if ($post['discount_type'] == 'percentage') {
                    if ((int) $post['total_discount'] >= 100 || (int) $post['total_discount'] == 0) {
                        $this->errors[] = $this->language->get('error_total_discount');
                    }
                }
            }

            // For multiple customer groups
            $customer_groups = isset($post['customer_group_id']) ? explode('_', $this->db->escape($post['customer_group_id'])) : array();

            if (count($customer_groups) == 0) {
                $this->errors[] = $this->language->get('error_empty_customer_groups');
            }

            return $this->errors;
        }
    }

    # Edit/Delete selected specials
    public function selected_specials()
    {
        $post   = $this->request->post;
        $errors = $this->_checkPermissions('modify');

        if ($errors) {
            $this->session->data['errors'] = $errors;
            $this->response->redirect($this->url->link($this->_module_name, 'user_token=' . $this->session->data['user_token'] . '#all_products_specials', 'SSL'));
            exit();
        }

        if (empty($post) && isset($this->request->get['delete']) && $this->request->get['delete'] !== '1') {
            exit();
        }

        # Delete specials in Product List
        if (isset($this->request->get['delete']) && $this->request->get['delete'] === '1') {
            if (count($post)) {
                foreach ($post['selected'] as $special_id) {
                    $this->{$this->_module_model_path}->deleteSpecialById($special_id);
                }

                $this->session->data['success'] = $this->language->get('successfully_deleted_chosen_specials');
            }

        } elseif (!empty($post) && isset($this->request->get['edit']) && $this->request->get['edit'] === '1') {
            # Edit Selected Specials
            $specials_ids = explode(',', $post['specials']);

            $result = $this->_edit_specials($post, $specials_ids);

            if (empty($result['errors'])) {
                # Add msg about successfully edited specials
                if (!empty($result['successfully_updated_product_specials'])) {
                    $this->session->data['success'] = $this->language->get('successfully_edited_specials');
                    $this->session->data['success'] .= "<br><b>" . implode(", ", $result['successfully_updated_product_specials']) . "</b>";
                }

                # Add msg about -unsuccessfully edited specials
                if (!empty($result['price_exceeding_products'])) {
                    $this->session->data['errors']['price_exceeding'] = $this->language->get('error_price_exceeding_for_selected_specials');
                    $this->session->data['errors']['price_exceeding'] .= "<br><b>" . implode(", ", $result['price_exceeding_products']) . "</b>";
                }
            } else {
                $this->session->data['errors'] = $result['errors'];
            }
        }

        $this->response->redirect($this->url->link($this->_module_name, 'user_token=' . $this->session->data['user_token'] . '#all_products_specials', 'SSL'));
    }

    private function _edit_specials($post, $specials_ids)
    {
        /* Save either selected specials or found specials by filters (tab 1,6) */
        $errors = $this->_checkPermissions('modify');
        $price_exceeding_products              = array();
        $successfully_updated_product_specials = array();

        # START Checking for errors
        if (strtotime($post['date_start']) > strtotime($post['date_end']) && $post['date_end'] != '0000-00-00') {
            $errors['date_start'] = $this->language->get('error_exceeding_date_period');
        }

        // If specials IDs are empty, show erro
        if (empty($specials_ids)) {
            $errors['error_permission'] = $this->language->get('error_no_selected_specials');
        }
        # END Checking for errors

        $priority   = (int) $post['priority'];
        $date_start = $post['date_start'];
        $date_end   = $post['date_end'];

        $post['timer'] = (isset($post['timer_status']) ? 1 : 0);

        $post['weekdays'] = isset($post['weekdays']) ? $post['weekdays'] : '';
        $post['hours']    = isset($post['hours']) ? $post['hours'] : '';

        if (isset($post['overwrite'])) {
            foreach ($post['overwrite'] as $key => $value) {
                $post['overwrite'][$key] = (int) $value;
            }
        }

        # Perform the update for every product special
        if (empty($errors)) {
            foreach ($specials_ids as $product_special_id) {
                $exceed_price               = false;
                $post['product_special_id'] = $product_special_id;
                $product                    = $this->{$this->_module_model_path}->getProductPriceByProductSpecialId($product_special_id);

                # check on the excess of the price (if user wants to overwrite the current value)
                if (isset($post['overwrite']['price'])) {
                    if ($post['discount_type'] == 'percentage') {
                        $post['price'] = (float) ((100 - ($post['total_discount'])) / 100) * $product['price'];
                    } elseif ($post['discount_type'] == 'currency') {
                        # if user chose currency discount
                        if ($product['price'] <= $post['total_discount']) {
                            $exceed_price = true;
                        } else {
                            $post['price'] = (float) ($product['price'] - $post['total_discount']);
                        }
                    }
                }

                if ($priority == 0) {
                    $post['priority'] = $product['priority'];
                }

                if ($date_start == '') {
                    $post['date_start'] = $product['date_start'];
                }

                if ($date_end == '') {
                    $post['date_end'] = $product['date_end'];
                }

                # Formatting weekdays and hours in needed format
                if ($this->hours_days_status) {
                    if (!empty($post['weekdays']) && is_array($post['weekdays'])) {
                        $post['weekdays'] = implode(',', $post['weekdays']);
                    }

                    if (!empty($post['hours']) && is_array($post['hours'])) {
                        $post['hours'] = implode(',', $post['hours']);
                    }
                }

                # -un/successfully updated products
                if ($exceed_price === false) {
                    $this->{$this->_module_model_path}->updateProductSpecialBySpecialId($post);
                    $successfully_updated_product_specials[] = $product['name'];
                } else {
                    $price_exceeding_products[] = $product['name'];
                }
            }
        }

        return array(
            'errors'                                => $errors,
            'successfully_updated_product_specials' => $successfully_updated_product_specials,
            'price_exceeding_products'              => $price_exceeding_products,
        );
    }

    // Ajax method. Return options by params
    public function getOptionsByText($value = '')
    {
        $json = array();
        $errors = $this->_checkPermissions('access');

        if ($errors) {
            $json['errors'] = $errors;
        
        } else {
            $args = array(
                'option_name'     => "",
                'option_value'    => "",
                'extra_conditons' => "",
            );

            // Split option text by separator, if we need to search also for option value
            if (isset($this->request->get['option_text'])) {
                $option_text = $this->db->escape($this->request->get['option_text']);
                $option_text = explode("|", $option_text);

                $args['option_name']  = $option_text[0];
                $args['option_value'] = isset($option_text[1]) ? $option_text[1] : "";
            }

            // Exclude chosen option ids and values
            if (isset($this->request->get['ids']) && isset($this->request->get['values_ids'])) {
                $ids = $this->db->escape($this->request->get['ids']);
                $ids = explode("_", $ids);

                $values = $this->db->escape($this->request->get['values_ids']);
                $values = explode("_", $values);

                foreach ($ids as $key => $attribute_id) {
                    $args["extra_conditons"] .= " AND NOT (ovd.option_id = '" . $attribute_id . "' AND ovd.option_value_id = '" . $values[$key] . "')";
                }
            }

            $json = $this->{$this->_module_model_path}->getOptions($args);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Ajax method. Return attributes by params
    public function getAttibutesByText()
    {
        $json = array();
        $errors = $this->_checkPermissions('access');
 
        if ($errors) {
            $json['errors'] = $errors;
        
        } else {
            $args = array(
                'attribute_name'  => "",
                'attribute_value' => "",
                'extra_conditons' => "",
            );

            // Split attribute text by separator, if we need to search also for attribute value
            if (isset($this->request->get['attribute_text'])) {
                $attribute_text = $this->db->escape($this->request->get['attribute_text']);
                $attribute_text = explode("|", $attribute_text);

                $args['attribute_name']  = $attribute_text[0];
                $args['attribute_value'] = isset($attribute_text[1]) ? $attribute_text[1] : "";
            }

            // Exclude chosen attribute ids and values
            if (isset($this->request->get['ids']) && isset($this->request->get['values'])) {
                $ids = $this->db->escape($this->request->get['ids']);
                $ids = explode("_", $ids);

                $values = $this->db->escape($this->request->get['values']);
                $values = explode("_", $values);

                foreach ($ids as $key => $attribute_id) {
                    $args["extra_conditons"] .= " AND NOT (pa.attribute_id = '" . $attribute_id . "' AND pa.text = '" . $values[$key] . "')";
                }
            }

            $json = $this->{$this->_module_model_path}->getAttributes($args);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getPossibleProducts()
    {
        $json = array();
        $errors = $this->_checkPermissions('access');
        $post = $this->request->post;
 
        $this->load->model('tool/image');

        if ($errors) {
            $json['errors'] = $errors;
        
        } else if (isset($post) && !empty($post)) {
            $json = $this->{$this->_module_model_path}->getPossibleProducts($post);

            // Show only 100 products
            $products_limit = 100;
            $products       = array_slice($json['products'], 0, $products_limit);

            foreach ($products as $key => $product) {
                if (is_file(DIR_IMAGE . $product['image'])) {
                    $image = $this->model_tool_image->resize($product['image'], 40, 40);
                } else {
                    $image = $this->model_tool_image->resize('no_image.png', 40, 40);
                }

                $products[$key]['image'] = $image;
            }

            $json['products'] = $products;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getPossibleSpecials()
    {
        $json = array(
            'result'   => array(),
            'errors'   => array(),
            'specials' => array(),
        );

        $errors = $this->_checkPermissions('access');
        $post = $this->request->post;

        $this->load->model('tool/image');

        if ($errors) {
            $json['errors'] = $errors;

        } else if (isset($post['form']) && !empty($post['form'])) {
            $start_limit = isset($post['start_limit']) ? (int) $post['start_limit'] : 0;

            // Ajax can't process several serialiaze(), that's why we need to parse raw string to fetch the params
            parse_str(html_entity_decode(urldecode($post['form'])), $post);

            $json = $this->{$this->_module_model_path}->getPossibleSpecials($post, $start_limit);

            // Check if the module "Hours and days" exists and enabled
            if ($this->{$this->_module_model_path}->getHoursDaysStatus()) {
                # Loading weekdays and hours labels
                $weekdays = $this->{$this->_model_path_module_hours_and_days}->getWeekdays();
                $hours    = $this->{$this->_model_path_module_hours_and_days}->getHours();
            }

            foreach ($json['specials'] as $key => $special) {
                if (is_file(DIR_IMAGE . $special['image'])) {
                    $image = $this->model_tool_image->resize($special['image'], 40, 40);
                } else {
                    $image = $this->model_tool_image->resize('no_image.png', 40, 40);
                }

                $json['specials'][$key]['image']  = $image;
                $json['specials'][$key]['status'] = $special['status'] ? $this->language->get('on') : $this->language->get('off');

                if ($this->{$this->_module_model_path}->getHoursDaysStatus()) {
                    $json['specials'][$key]['weekdays_tooltip'] = $this->_prepareTooltipInfo($special['weekdays'], $weekdays);
                    $json['specials'][$key]['hours_tooltip']    = $this->_prepareTooltipInfo($special['hours'], $hours);
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function ajaxUpdateSpecials()
    {
        $json = array();
        $post = $this->request->post;

        if ($errors) {
            $json['errors'] = $errors;
        
        } else if (isset($post) && !empty($post)) {
            // Get all specials by given filters
            $result       = $this->{$this->_module_model_path}->getPossibleSpecials($post, null, true); // $post, $start_limit, $load_all
            $specials_ids = array();

            foreach ($result['specials'] as $special) {
                $specials_ids[] = $special['product_special_id'];
            }

            // Update specials parameters
            $result = $this->_edit_specials($post, $specials_ids);

            if (empty($result['errors'])) {
                # Add msg about successfully edited specials
                if (!empty($result['successfully_updated_product_specials'])) {
                    $json['result'][] = $this->language->get('successfully_edited_specials');
                    $json['result'][] .= "<br><b>" . implode(", ", $result['successfully_updated_product_specials']) . "</b>";
                }

                # Add msg about -unsuccessfully edited specials
                if (!empty($result['price_exceeding_products'])) {
                    $json['errors']['price_exceeding'] = $this->language->get('error_price_exceeding_for_selected_specials');
                    $json['errors']['price_exceeding'] .= "<br><b>" . implode(", ", $result['price_exceeding_products']) . "</b>";
                }
            } else {
                $json['errors'] = $result['errors'];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function ajaxDeleteSpecials()
    {
        $json = array(
            'result' => array(),
            'errors' => array()
        );
        $errors = $this->_checkPermissions('modify');
        $post = $this->request->post;

        if ($errors) {
            $json['errors'] = $errors;

        } else if (isset($post) && !empty($post)) {
            // Get all specials by given filters
            $result = $this->{$this->_module_model_path}->getPossibleSpecials($post, null, true); // $post, $start_limit, $load_all

            foreach ($result['specials'] as $special) {
                $this->{$this->_module_model_path}->deleteSpecialById($special['product_special_id']);
            }

            $json['result'][] = $this->language->get('successfully_deleted_specials');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    # Actions for Special Groups
    public function add_new_special_group()
    {
        $json = array(
            'result' => array(),
            'errors' => array()
        );
        $errors = $this->_checkPermissions('modify');

        if ($errors) {
            $json['errors'] = $errors;

        } else {
            $special_group_name = isset($this->request->get['name']) ? $this->db->escape($this->request->get['name']) : '';

            if ($special_group_name && empty($json['errors'])) {
                $json['special_group_id'] = $this->{$this->_module_model_path}->addNewSpecialGroup($special_group_name);

                $json['result'][] = sprintf($this->language->get('new_special_group_successfully_added'), $special_group_name);
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function rename_special_group()
    {
        $data['result'] = array();
        $data['errors'] = array();

        $special_group_id   = isset($this->request->get['id']) ? (int) $this->request->get['id'] : 0;
        $special_group_name = isset($this->request->get['name']) ? $this->db->escape($this->request->get['name']) : '';

        $errors = $this->_checkPermissions('modify');
        $data['errors'] = array_merge($data['errors'], $errors);

        if (mb_strlen($special_group_name) == 0 || mb_strlen($special_group_name) > 100) {
            $data['errors'][] = $this->language->get('error_special_group_name');
        }

        if ($special_group_id > 0 && $special_group_name && empty($data['errors'])) {
            $this->{$this->_module_model_path}->renameSpecialGroup($special_group_id, $special_group_name);

            $data['result'][] = sprintf($this->language->get('special_group_successfully_renamed'), $special_group_name);
        }

        $this->response->setOutput(json_encode($data));
    }

    public function delete_special_group_by_id()
    {
        $data['result'] = array();
        $data['errors'] = $this->_checkPermissions('modify');

        $special_group_id   = isset($this->request->get['id']) ? (int) $this->request->get['id'] : 0;
        $special_group_name = isset($this->request->get['name']) ? $this->db->escape($this->request->get['name']) : '';

        if ($special_group_id > 0 && empty($data['errors'])) {
            $this->{$this->_module_model_path}->deleteSpecialGroupById($special_group_id);

            $data['result'][] = sprintf($this->language->get('special_group_successfully_deleted'), $special_group_name);
        }

        $this->response->setOutput(json_encode($data));
    }

    public function delete_all_specials()
    {
        if ($this->user->hasPermission('modify', $this->_module_name)) {

            if ($this->{$this->_module_model_path}->deleteAllProductsSpecials()) {
                $this->session->data['success'] = $this->language->get('successfully_deleted_all_specials');
            } else {
                $this->session->data['errors']['fail_delete_all'] = $this->language->get('failed_deleted_all_specials');
            }

        } else {
            $this->session->data['errors']['warning'] = $this->language->get('error_permission');
        }

        $this->response->redirect($this->url->link($this->_module_name, 'user_token=' . $this->session->data['user_token'], 'SSL'));
    }

    private function _prepareTooltipInfo($text, $array)
    {
        $new_array = array();
        $output    = '';

        foreach ($array as $item) {
            if (strpos($text, $item['id']) !== false) {
                $new_array[] = $item['name'] . " - <strong>&#10004;</strong>";
            } else {
                $new_array[] = $item['name'];
            }
        }

        // return tooltip for weekdays
        if (count($array) <= 7) {
            $output = implode("<br>", $new_array);

            // return tooltip for hours
        } else {
            $output = "<div class='tooltip-hint'><div>";

            for ($i = 1; $i < count($new_array) + 1; $i++) {
                // Write the name of element
                $output .= $new_array[$i - 1];

                // new line for every item except 12, 24 hours
                if ($i % 12 != 0) {
                    $output .= "<br>";
                }

                if ($i == 12) {
                    $output .= "</div><div>";
                }
            }

            $output .= "</div></div>";
        }

        return $output;
    }

    private function _getSpecialGroupName($special_group_id)
    {
        $res = '';

        if ($special_group_id != 0) {
            $res = $this->{$this->_module_model_path}->getSpecialGroups($special_group_id);

            $res = (empty($res) && !isset($res[0])) ? '' : $res[0]['name'];
        }

        return $res;
    }

    private function _getCustomerGroupName($customer_group_id)
    {
        $res = '';

        if ($customer_group_id != 0) {
            $res = $this->{$this->_model_path_customer_group}->getCustomerGroup($customer_group_id);
            $res = (empty($res)) ? '' : $res['name'];
        }

        return $res;
    }

    private function _loadLang($data = array())
    {
        $this->load->language('catalog/product');
        $this->load->language($this->_module_name);

        $data['l'] = $this->language;

        return $data;
    }

    private function _checkPermissions($section = "access")
    {
        $errors = array();
        
        if (!$this->user->hasPermission($section, $this->_module_name)) {
            $errors[] = $this->language->get('error_permission');
        }
        return $errors;
    }

    private function _getCustomerGroups()
    {
        return $this->{$this->_model_path_customer_group}->getCustomerGroups();
    }

    private function _price_round($price)
    {
        return number_format($price, 2, '.', '');
    }
}
