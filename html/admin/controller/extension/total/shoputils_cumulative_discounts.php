<?php
/*
 * Shoputils
 *
 * ПРИМЕЧАНИЕ К ЛИЦЕНЗИОННОМУ СОГЛАШЕНИЮ
 *
 * Этот файл связан лицензионным соглашением, которое можно найти в архиве,
 * вместе с этим файлом. Файл лицензии называется: LICENSE.3.0.x-3.1.x.RUS.TXT
 * Так же лицензионное соглашение можно найти по адресу:
 * https://opencart.market/LICENSE.3.0.x-3.1.x.RUS.TXT
 * 
 * =================================================================
 * OPENCART/ocStore 3.0.x-3.1.x ПРИМЕЧАНИЕ ПО ИСПОЛЬЗОВАНИЮ
 * =================================================================
 *  Этот файл предназначен для Opencart/ocStore 3.0.x-3.1.x. Shoputils не
 *  гарантирует правильную работу этого расширения на любой другой 
 *  версии Opencart/ocStore, кроме Opencart/ocStore 3.0.x-3.1.x. 
 *  Shoputils не поддерживает программное обеспечение для других 
 *  версий Opencart/ocStore.
 * =================================================================
*/

class ControllerExtensionTotalShoputilsCumulativeDiscounts extends Controller {
    private $error = array();
    private $version = '1.3';
    const FILE_NAME_LIC = 'shoputils_cumulativediscounts3.lic';

    public function __construct($registry){
        parent::__construct($registry);
        $this->load->language('extension/total/shoputils_cumulative_discounts');
        $this->document->setTitle($this->language->get('heading_title'));
    }

    public function index() {
        if (!is_file(DIR_APPLICATION . self::FILE_NAME_LIC)) {
            $this->response->redirect($this->url->link('extension/total/shoputils_cumulative_discounts/lic', '&user_token=' . $this->session->data['user_token'], 'SSL'));
        }
        register_shutdown_function(array($this, 'licShutdownHandler'));
        $this->load->model('extension/module/shoputils_cumulative_discounts');
        $this->load->model('extension/total/shoputils_cumulative_discounts');

        $this->model_extension_total_shoputils_cumulative_discounts->install();

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->request->post['total_shoputils_cumulative_discounts_statuses'] = isset($this->request->post['total_shoputils_cumulative_discounts_statuses']) ?
                                                                              implode(',', $this->request->post['total_shoputils_cumulative_discounts_statuses']) : '';

            $this->request->post['total_shoputils_cumulative_discounts_totals'] = isset($this->request->post['total_shoputils_cumulative_discounts_totals']) ?
                                                                              implode(',', $this->request->post['total_shoputils_cumulative_discounts_totals']) : '';

            $this->request->post['total_shoputils_cumulative_discounts_disallow_categories'] = isset($this->request->post['total_shoputils_cumulative_discounts_disallow_categories']) ?
                                                                              implode(',', $this->request->post['total_shoputils_cumulative_discounts_disallow_categories']) : '';


            $this->model_extension_module_shoputils_cumulative_discounts->setSetting();
            $this->response->redirect($this->model_extension_module_shoputils_cumulative_discounts->makeUrl('marketplace/extension', 'type=total'));
        }

        $this->load->model('setting/store');
        $this->load->model('localisation/language');
        $this->load->model('localisation/order_status');
        $this->load->model('catalog/category');

        $scripts = array(
            'view/javascript/codemirror/lib/codemirror.js',
            'view/javascript/codemirror/lib/xml.js',
            'view/javascript/codemirror/lib/formatting.js',
            'view/javascript/summernote/summernote.js',
            'view/javascript/summernote/summernote-image-attributes.js',
            'view/javascript/summernote/opencart.js'
        );
        
        if (version_compare(VERSION, '3.1.0', '>=')) {
            $scripts[] = 'view/javascript/shoputils/cumulative_discounts/extension_compatibility.js';
        }
        
        $this->_addScript($scripts);

        $this->_addStyle(array(
            'view/javascript/codemirror/lib/codemirror.css',
            'view/javascript/summernote/summernote.css',
            'view/stylesheet/shoputils_cumulative_discounts.css'
        ));

        $data = $this->_setData(array(
            'entry_discount_summ' => sprintf($this->language->get('entry_discount_summ'), $this->config->get('config_currency')),
            'text_copyright'      => sprintf($this->language->get('text_copyright'), $this->language->get('heading_title'), date('Y', time())),
            'error_warning'       => isset($this->error['warning']) ? $this->error['warning'] : '',
            'action'              => $this->model_extension_module_shoputils_cumulative_discounts->makeUrl('extension/total/shoputils_cumulative_discounts'),
            'cancel'              => $this->model_extension_module_shoputils_cumulative_discounts->makeUrl('marketplace/extension', 'type=total'),
            //'ckeditor'            => $this->config->get('config_editor_default'),
            'discounts'           => $this->model_extension_total_shoputils_cumulative_discounts->getAllDiscounts(),
            'cmsdata'             => $this->model_extension_total_shoputils_cumulative_discounts->getDiscountsCMSData(),
            'stores'              => $this->model_setting_store->getStores(),
            'categories'          => $this->model_catalog_category->getCategories(0),
            'order_statuses'      => $this->model_localisation_order_status->getOrderStatuses(),
            'languages'           => $this->model_localisation_language->getLanguages()
        ));

        $this->load->model('customer/customer_group');
        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        if ($this->config->get('default_config_name')){
            $data['text_default'] = $this->config->get('default_config_name');
        }

        $data['breadcrumbs'][] = array(
            'href' => $this->model_extension_module_shoputils_cumulative_discounts->makeUrl('common/dashboard'),
            'text' => $this->language->get('text_home')
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->model_extension_module_shoputils_cumulative_discounts->makeUrl('marketplace/extension', 'type=total'),
            'text' => $this->language->get('text_total')
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->model_extension_module_shoputils_cumulative_discounts->makeUrl('extension/total/shoputils_cumulative_discounts'),
            'text' => $this->language->get('heading_title')
        );

        $data = array_merge($data, $this->_updateData(
            array(
                 'total_shoputils_cumulative_discounts_status',
                 'total_shoputils_cumulative_discounts_sort_order',
                 'total_shoputils_cumulative_discounts_statuses',
                 'total_shoputils_cumulative_discounts_totals',
                 'total_shoputils_cumulative_discounts_disallow_categories'
            )
        ));

        if (!is_array($data['total_shoputils_cumulative_discounts_statuses'])) {
            $data['total_shoputils_cumulative_discounts_statuses'] = explode(',', $data['total_shoputils_cumulative_discounts_statuses']);
        }

        if (!is_array($data['total_shoputils_cumulative_discounts_totals'])) {
            $data['total_shoputils_cumulative_discounts_totals'] = explode(',', $data['total_shoputils_cumulative_discounts_totals']);
        }

        if (!is_array($data['total_shoputils_cumulative_discounts_disallow_categories'])) {
            $data['total_shoputils_cumulative_discounts_disallow_categories'] = explode(',', $data['total_shoputils_cumulative_discounts_disallow_categories']);
        }

        $data['totals'] = $this->model_extension_module_shoputils_cumulative_discounts->getExtensions('total');

        $data = array_merge($data, $this->_setData(
            array(
                 'header'       => $this->load->controller('common/header'),
                 'column_left'  => $this->load->controller('common/column_left'),
                 'footer'       => $this->load->controller('common/footer')
            )
        ));
        
        $this->response->setOutput($this->load->view('extension/total/shoputils_cumulative_discounts', $data));
    }

    public function lic() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->user->hasPermission('modify', 'extension/total/shoputils_cumulative_discounts')) {
                $this->session->data['warning'] = sprintf($this->language->get('error_permission'), $this->language->get('heading_title'));
            } elseif (!empty($this->request->post['lic_data'])) {
                if (!is_writable(DIR_APPLICATION)) {
                    $perms = fileperms(DIR_APPLICATION);
                    chmod(DIR_APPLICATION, 0755);
                }
                
                $lic = '------ LICENSE FILE DATA -------' . "\n";
                $lic .= trim($this->request->post['lic_data']) . "\n";
                $lic .= '--------------------------------' . "\n";
                $file = DIR_APPLICATION . self::FILE_NAME_LIC;
                $handle = @fopen($file, 'w'); 
                fwrite($handle, $lic);
                fclose($handle); 
                if (isset($perms)) {
                    chmod(DIR_APPLICATION, $perms);
                }

                if (!is_file($file)) {
                    $this->session->data['warning'] = sprintf($this->language->get('error_dir_perm'), DIR_APPLICATION);
                    $this->response->redirect($this->url->link('extension/total/shoputils_cumulative_discounts/lic', '&user_token=' . $this->session->data['user_token'], 'SSL'));
                }

                register_shutdown_function(array($this, 'licShutdownHandler'));
                $this->load->model('extension/module/shoputils_cumulative_discounts');

                $this->response->redirect($this->url->link('extension/total/shoputils_cumulative_discounts', '&user_token=' . $this->session->data['user_token'], 'SSL'));
            }
        }

        $domain = str_replace('http://', '', HTTP_SERVER);
        $domain = explode('/', str_replace('https://', '', $domain));
        
        $loader = extension_loaded('ionCube Loader');

        $loader_min_version = '5.0';
        $loader_version = function_exists('ioncube_loader_version') ? ioncube_loader_version() : $loader_min_version;
        $loader_compare = version_compare($loader_version, $loader_min_version, '>=');

        $php_min_version = '5.4';
        $php_version = phpversion();
        $php_compare = version_compare($php_version, $php_min_version, '>=');

        if (version_compare(VERSION, '3.1.0', '>=')) {
            $this->_addScript(array('view/javascript/shoputils/cumulative_discounts/extension_compatibility.js'));
        }
        
        $data = $this->_setData(array(
            'error_loader'          => sprintf($this->language->get('error_loader'), $loader_min_version),
            'error_loader_version'  => sprintf($this->language->get('error_loader_version'), $loader_min_version),
            'error'                 => !($loader && $loader_compare && $php_compare),
            'text_domain'           => sprintf($this->language->get('text_domain'), $domain[0]),
            'text_loader'           => sprintf($this->language->get('text_loader'), $loader_version, $loader_min_version),
            'text_php'              => sprintf($this->language->get('text_php'), $php_version, $php_min_version),
            'action'                => $this->url->link('extension/total/shoputils_cumulative_discounts/lic', '&user_token=' . $this->session->data['user_token'], 'SSL'),
            'cancel'                => $this->url->link('marketplace/extension', 'type=total&user_token=' . $this->session->data['user_token'], 'SSL'),
            'loader'                => $loader,
            'icon'                  => 'view/image/shoputils_discounts.png',
            'loader_compare'        => $loader_compare,
            'php_compare'           => $php_compare
        ));
        
        if (isset($this->session->data['warning'])) {
          $data['error_warning'] = $this->session->data['warning'];
          unset($this->session->data['warning']);
          if (is_file(DIR_APPLICATION . self::FILE_NAME_LIC)) {
              @unlink(DIR_APPLICATION . self::FILE_NAME_LIC);
          }
        } else {
          $data['error_warning'] = '';
        }

        $data = array_merge($data, $this->_setData(
            array(
                 'header'       => $this->load->controller('common/header'),
                 'column_left'  => $this->load->controller('common/column_left'),
                 'footer'       => $this->load->controller('common/footer')
            )
        ));
        
        $this->response->setOutput($this->load->view('extension/total/shoputils_cumulative_discounts_lic', $data));
    }

    protected function validate() {
        if (!$this->model_extension_module_shoputils_cumulative_discounts->validatePermission()) {
            $this->error['warning'] = sprintf($this->language->get('error_permission'), $this->language->get('heading_title'));
        } else if (!isset($this->request->post['total_shoputils_cumulative_discounts_statuses']) || !$this->request->post['total_shoputils_cumulative_discounts_statuses']) {
            $this->error['warning'] = $this->language->get('error_need_select_order_status');
        } else if (!isset($this->request->post['total_shoputils_cumulative_discounts_totals']) || !$this->request->post['total_shoputils_cumulative_discounts_totals']) {
            $this->error['warning'] = $this->language->get('error_need_select_order_total');
        }

        return !$this->error;
    }

    function licShutdownHandler() {
        if (@is_array($e = @error_get_last())) {
            $code = isset($e['type']) ? $e['type'] : 0;
            $msg = isset($e['message']) ? $e['message'] : '';
            if(($code > 0) && (strpos($msg, 'requires a license file') || strpos($msg, 'is not valid for this server'))) {
                $this->session->data['warning'] = $this->language->get('error_key');
                $this->response->redirect($this->url->link('extension/total/shoputils_cumulative_discounts/lic', '&user_token=' . $this->session->data['user_token'], 'SSL'));
            }
        }
    }

    protected function _addScript($files) {
        foreach ($files as $file) {
            if (is_file(DIR_APPLICATION . $file)) {
                $this->document->addScript($file);
            }
        }
    }
    protected function _addStyle($files) {
        foreach ($files as $file) {
            if (is_file(DIR_APPLICATION . $file)) {
                $this->document->addStyle($file);
            }
        }
    }

    protected function _setData($values) {
        $data = array();
        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $data[$value] = $this->language->get($value);
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    protected function _updateData($keys, $info = array()) {
        $data = array();
        foreach ($keys as $key) {
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } elseif (isset($info[$key])) {
                $data[$key] = $info[$key];
            } else {
                $data[$key] = $this->config->get($key);
            }
        }
        return $data;
    }
}
?>