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

class ControllerExtensionModuleShoputilscumulativediscounts extends Controller {
    public function index() {
        if ($this->customer->isLogged()){
            $this->load->model('extension/total/shoputils_cumulative_discounts');
            $this->load->language('extension/module/shoputils_cumulative_discounts_');

            $data['text_customer'] = $this->customer->getFirstname() . ' ' . $this->customer->getLastname();
            $data['href_discounts'] = $this->url->link('extension/module/shoputils_cumulative_discounts_/discounts', '', 'SSL');

            if ($discount = $this->model_extension_total_shoputils_cumulative_discounts->getLoggedCustomerDiscount()) {
                $data['text_description'] = $discount['description'] ?: $this->language->get('text_description_empty');
                $data['text_href_discounts'] = $this->language->get('text_href_discounts_logged');
                $data['cumulative_summ'] = sprintf($this->language->get('text_cumulative_summ'), $this->currency->format($discount['cumulative_summ'], $this->session->data['currency']));
            } else {
                $data['text_description'] = $this->language->get('text_description_none');
                $data['text_href_discounts'] = $this->language->get('text_href_discounts_not_logged');
            }

            return $this->load->view('extension/module/shoputils_cumulative_discounts_', $data);
        }
    }

    public function discounts() {
        $this->load->model('extension/total/shoputils_cumulative_discounts');
        $this->load->language('extension/module/shoputils_cumulative_discounts_');

        $this->document->setTitle($this->language->get('heading_full_title'));

        $data['breadcrumbs'][] = array(
            'href'  => $this->url->link('common/home', '', 'SSL'),
            'text'  => $this->language->get('text_home')
        );

        $data['breadcrumbs'][] = array(
            'href'  => $this->url->link('extension/module/shoputils_cumulative_discounts_/discounts', '', 'SSL'),
            'text'  => $this->language->get('heading_title')
        );


        $cmsdata = $data['discounts'] = $this->model_extension_total_shoputils_cumulative_discounts->getDiscountsCMSData(
            (int)$this->config->get('config_language_id')
        );

        $data['description_before'] = htmlspecialchars_decode($cmsdata['description_before']);
        $data['description_after'] = htmlspecialchars_decode($cmsdata['description_after']);

        $data['discounts'] =$this->model_extension_total_shoputils_cumulative_discounts->getDiscounts(
            (int)$this->config->get('config_store_id'),
            (int)$this->config->get('config_customer_group_id'),
            (int)$this->config->get('config_language_id')
        );

        $data['column_left']    = $this->load->controller('common/column_left');
        $data['column_right']   = $this->load->controller('common/column_right');
        $data['content_top']    = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/module/shoputils_cumulative_discounts_list', $data));
    }
}
?>