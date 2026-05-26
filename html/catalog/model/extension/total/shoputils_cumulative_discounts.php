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

class ModelExtensionTotalShoputilsCumulativeDiscounts extends Model {
    private $_tablename = 'shoputils_cumulative_discounts';
    private $_tablename_cmsdata = 'shoputils_cumulative_discounts_cmsdata';
    private $_tablename_to_store = 'shoputils_cumulative_discounts_to_store';
    private $_tablename_description = 'shoputils_cumulative_discounts_description';
    private $_tablename_to_customer_group = 'shoputils_cumulative_discounts_to_customer_group';
    private $NOW;

    public function __construct($registry) {
        $this->NOW = date('Y-m-d H:i') . ':00';
        parent::__construct($registry);
    }

    public function getTotal($total) {
        if (!$this->config->get('total_shoputils_cumulative_discounts_status')){
            return;
        }

        if (!$this->customer->isLogged()) {
            return;
        }

        if ($discount = $this->getLoggedCustomerDiscount()) {
            $this->load->language('extension/total/shoputils_cumulative_discounts');

        if ($this->config->get('total_shoputils_cumulative_discounts_disallow_categories') || $discount['products_special']) {
            $this->load->model('catalog/product');
        }

        if ($this->config->get('total_shoputils_cumulative_discounts_disallow_categories')) {
            $disallow_categories = explode(',', $this->config->get('total_shoputils_cumulative_discounts_disallow_categories'));
            $products_total = 0;

            foreach ($this->cart->getProducts() as $product) {
                $categories = array();
                $product_categories = $this->model_catalog_product->getCategories($product['product_id']);
                foreach ($product_categories as $category) {
                    $categories[] = $category['category_id'];
                }

                $product['categories'] = $categories;

                if (!array_intersect($product['categories'], $disallow_categories)){
                    $products_total += $this->tax->calculate($product['total'], $product['tax_class_id'], $this->config->get('config_tax'));
                }
            }
        
        } else {
            $products_total = $this->cart->getTotal();
        }
        
        //Если не надо включать в текущую скидку акционные товары - вычитываем их разницу.
        if ($discount['products_special']) {
            foreach ($this->cart->getProducts() as $product) {
                $product_info = $this->model_catalog_product->getProduct($product['product_id']);
                
                if ($product_info && (float)$product_info['special']) {
                    $products_total -= $this->tax->calculate($product['total'], $product['tax_class_id'], $this->config->get('config_tax'));
                }
            }
        }

            $discount_total = round($products_total * ($discount['percent'] / 100), 2);

            if ($discount_total > 0) {
                $total['totals'][] = array(
                    'code'       => 'shoputils_cumulative_discounts',
                    'title'      => sprintf($this->language->get('text_cumulative_discounts'), $discount['percent']),
                    //'text'       => '-' . $this->currency->format($discount_total, $this->session->data['currency']),
                    'value'      => - $discount_total,
                    'sort_order' => $this->config->get('total_shoputils_cumulative_discounts_sort_order')
                  );
                  $total['total'] -= $discount_total;
            }
        }
    }

    public function getDiscountsCMSData($language_id) {
        $query = $this->db->query("SELECT
            *
        FROM
            " . DB_PREFIX . $this->_tablename_cmsdata . "
        WHERE
            language_id='" . (int)$language_id . "' AND store_id = '" . (int)$this->config->get('config_store_id')."'");
        if (isset($query->rows[0])) {
            $rows = $query->rows[0];
        } else {
            $rows = array();
        }
        return $rows;
    }

    public function getDiscounts($store_id, $customer_group_id, $language_id, $sort_order = 'ASC') {
        $sql = 'SELECT
                    d.discount_id,
                    d.days,
                    d.summ,
                    d.percent,
                    d.products_special,
                    d.first_order,
                    dd.description
                FROM
                    '.DB_PREFIX . $this->_tablename . ' d
                LEFT JOIN
                    '.DB_PREFIX . $this->_tablename_description . ' dd ON (d.discount_id = dd.discount_id)
                LEFT JOIN
                    '.DB_PREFIX . $this->_tablename_to_store . ' d2s ON (d.discount_id = d2s.discount_id)
                LEFT JOIN
                    '.DB_PREFIX . $this->_tablename_to_customer_group . ' d2cg ON (d.discount_id = d2cg.discount_id)
                WHERE
                    d2s.store_id="' . (int)$store_id . '" AND
                    d2cg.customer_group_id="' . (int)$customer_group_id . '" AND
                    dd.language_id="' . (int)$language_id . '" 
                ORDER BY 
                    d.percent ' . $this->db->escape($sort_order);
        
        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCustomerDiscount($store_id, $customer_group_id, $language_id, $customer_id) {
        if (!$this->config->get('total_shoputils_cumulative_discounts_statuses')) {
            return false;
        }
        $discounts = $this->getDiscounts($store_id, $customer_group_id, $language_id, 'DESC');

        foreach ($discounts as $discount){
            //Search order
            $time = time() - $discount['days'] * 24 * 60 * 60;
            if ($time < 0) $time = 0;
            $date = date('Y-m-d H:i:s', $time);

            //if ($discount['products_special']) {
            //    $cumulative_summ = $this->getSumDiscountWithSpecial($customer_group_id, $customer_id, $store_id, $date);
            //} else {
                //$sql = "SELECT SUM(op.total) as summ FROM `" . DB_PREFIX . "order_product` op, `". DB_PREFIX . "order` o WHERE
/*                $sql = "SELECT SUM((op.price + op.tax) * op.quantity) as summ FROM `" . DB_PREFIX . "order_product` op, `". DB_PREFIX . "order` o WHERE
                    o.order_id = op.order_id AND
                    o.customer_id='" . $customer_id . "' AND
                    o.store_id='" . $store_id . "' AND
                    (
                        order_status_id IN (".$this->config->get('total_shoputils_cumulative_discounts_statuses').") AND
                        (date_added >= '" . $date . "' OR date_modified >= '" . $date . "')
                    )

                    GROUP BY o.customer_id";
*/
                $sql = "SELECT SUM(ot.value) as summ FROM `" . DB_PREFIX . "order_total` ot, `". DB_PREFIX . "order` o WHERE
                    o.order_id = ot.order_id AND
                    o.customer_id='" . (int)$customer_id . "' AND
                    o.store_id='" . (int)$store_id . "' AND
                    (
                        order_status_id IN (".$this->db->escape($this->config->get('total_shoputils_cumulative_discounts_statuses')).") AND
                        ot.code IN ('".str_replace(',', "', '", $this->db->escape($this->config->get('total_shoputils_cumulative_discounts_totals')))."') AND
                       (date_added >= '" . $date . "' OR date_modified >= '" . $date . "')
                    )

                    GROUP BY o.customer_id";

                $query = $this->db->query($sql);
                $cumulative_summ = isset($query->rows[0]['summ']) ? $query->rows[0]['summ'] : 0;
            //}

            if ($discount['first_order']) {
                //if ($discount['products_special']) {
                //    foreach ($this->cart->getProducts() as $product) {
                //        $product_id = isset($product['product_id']) ? $product['product_id'] : 0;
                //        $product_info = $this->db->query("SELECT price, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < '" . $this->NOW . "') AND (pd2.date_end = '0000-00-00' OR pd2.date_end > '" . $this->NOW . "')) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < '" . $this->NOW . "') AND (ps.date_end = '0000-00-00' OR ps.date_end > '" . $this->NOW . "')) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= '" . $this->NOW . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
                //        //$product_info = $this->db->query("SELECT price, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity <= '" . $product['quantity'] . "' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < '" . $this->NOW . "') AND (pd2.date_end = '0000-00-00' OR pd2.date_end > '" . $this->NOW . "')) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < '" . $this->NOW . "') AND (ps.date_end = '0000-00-00' OR ps.date_end > '" . $this->NOW . "')) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= '" . $this->NOW . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
                //        if ((!$product_info->num_rows) || (!$product_info->rows[0]['discount'] && !$product_info->rows[0]['special'])) {
                //            $cumulative_summ += $this->tax->calculate($product['total'], $product['tax_class_id'], $this->config->get('config_tax'));
                //        }
                //    }
                //} else {
                    $cumulative_summ += $discount['first_order'] ? $this->cart->getTotal() : 0;
                //}
            }
            
            if ($cumulative_summ >= $discount['summ']) {
                $discount['cumulative_summ'] = $cumulative_summ;
                return $discount;
            }
        }
        return false;
    }

    public function getLoggedCustomerDiscount() {
        return $this->getCustomerDiscount(
            (int)$this->config->get('config_store_id'),
            $this->customer->getGroupId() ? $this->customer->getGroupId() : $this->config->get('config_customer_group_id'),
            (int)$this->config->get('config_language_id'),
            $this->customer->getId()
        );
    }

/*    protected function getSumDiscountWithSpecial($customer_group_id, $customer_id, $store_id, $date) {
        $cumulative_summ = 0;

        $sql = "SELECT
                op.product_id,
                op.quantity,
                op.price,
                op.total,
                op.tax,
                o.date_added
                FROM `" . DB_PREFIX . "order_product` op, `". DB_PREFIX . "order` o WHERE
                o.order_id = op.order_id AND
                o.customer_id='" . $customer_id . "' AND
                o.store_id='" . $store_id . "' AND
                (
                    order_status_id IN (".$this->config->get('total_shoputils_cumulative_discounts_statuses').") AND
                    (date_added >= '" . $date . "' OR date_modified >= '" . $date . "')
                )";
        $orders = $this->db->query($sql);

        if ($orders->num_rows) {
            $debugs = array();
            foreach ($orders->rows as $order) {
                //$product_info = $this->db->query("SELECT price, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < '" . $order['date_added'] . "') AND (pd2.date_end = '0000-00-00' OR pd2.date_end > '" . $order['date_added'] . "')) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < '" . $order['date_added'] . "') AND (ps.date_end = '0000-00-00' OR ps.date_end > '" . $order['date_added'] . "')) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id = '" . (int)$order['product_id'] . "' AND p.date_available <= '" . $order['date_added'] . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
                $product_info = $this->db->query("SELECT price, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity <= '" . $order['quantity'] . "' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < '" . $order['date_added'] . "') AND (pd2.date_end = '0000-00-00' OR pd2.date_end > '" . $order['date_added'] . "')) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < '" . $order['date_added'] . "') AND (ps.date_end = '0000-00-00' OR ps.date_end > '" . $order['date_added'] . "')) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id = '" . (int)$order['product_id'] . "' AND p.date_available <= '" . $order['date_added'] . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
                        $debugs[] = $product_info->rows[0];

                if ((!$product_info->num_rows) || (!$product_info->rows[0]['discount'] && !$product_info->rows[0]['special'])) {
                    //Если товар удален или скидочной или акционной цены нет на момент времени заказа - плюсуем покупателю в накопленную сумму
                    $cumulative_summ += ($order['price'] + $order['tax']) * $order['quantity'];
                    //$cumulative_summ += $order['total'];
                }
            }
        }
        return $cumulative_summ;
    }
*/
}
?>