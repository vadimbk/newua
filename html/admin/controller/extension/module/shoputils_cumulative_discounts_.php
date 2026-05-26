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

class ControllerExtensionModuleShoputilsCumulativeDiscounts extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/shoputils_cumulative_discounts_');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_shoputils_cumulative_discounts_', $this->request->post);
            $this->session->data['success'] = sprintf($this->language->get('text_success'), $this->language->get('heading_title'));
            $this->response->redirect($this->makeUrl('marketplace/extension', 'type=module'));
        }

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle('view/stylesheet/shoputils_cumulative_discounts.css');

        if (version_compare(VERSION, '3.1.0', '>=')) {
            $this->document->addScript('view/javascript/shoputils/cumulative_discounts/extension_compatibility.js');
        }

        $data = $this->_setData(array(
            'action'          => $this->makeUrl('extension/module/shoputils_cumulative_discounts_'),
            'cancel'          => $this->makeUrl('marketplace/extension', 'type=module'),
            'error_warning'   => isset($this->error['warning']) ? $this->error['warning'] : '',
            'text_copyright'  => sprintf($this->language->get('text_copyright'), $this->language->get('heading_title'), date('Y', time())),
            'error_warning'   => isset($this->error['warning']) ? $this->error['warning'] : ''
        ));

        $data['breadcrumbs'][] = array(
            'href'      => $this->makeUrl('common/dashboard'),
            'text'      => $this->language->get('text_home')
        );

        $data['breadcrumbs'][] = array(
            'href'      => $this->makeUrl('marketplace/extension', 'type=module'),
            'text'      => $this->language->get('text_module')
        );

        $data['breadcrumbs'][] = array(
           'href'      => $this->makeUrl('extension/module/shoputils_cumulative_discounts_'),
           'text'      => $this->language->get('heading_title')
        );

        $data = array_merge($data, $this->_updateData(
            array(
                 'module_shoputils_cumulative_discounts__status'
            )
        ));

        $data = array_merge($data, $this->_setData(
            array(
                 'header'       => $this->load->controller('common/header'),
                 'column_left'  => $this->load->controller('common/column_left'),
                 'footer'       => $this->load->controller('common/footer')
            )
        ));
        
        $this->response->setOutput($this->load->view('extension/module/shoputils_cumulative_discounts_', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/shoputils_cumulative_discounts_')) {
            $this->error['warning'] = sprintf($this->language->get('error_permission'), $this->language->get('heading_title'));
        }

        return !$this->error;
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

    protected function makeUrl($route, $url = ''){
        return str_replace('&amp;', '&', $this->url->link($route, $url.'&user_token=' . $this->session->data['user_token'], 'SSL'));
    }
}
?>