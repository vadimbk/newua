<?php
class ModelExtensionEcommerceEcommerceGa4 extends Model {

    public function getOrders($data = array()) {
        $sql = "SELECT eo.*, o.order_id, o.store_name, CONCAT(o.firstname, ' ', o.lastname) AS customer, 
        (SELECT SUM(op2.quantity) FROM " . DB_PREFIX . "order_product op2 WHERE op2.order_id = o.order_id) AS product_quantity,
        (SELECT SUM(erp.quantity) FROM " . DB_PREFIX . "ga4_ecommerce_refund_product erp WHERE erp.order_id = o.order_id) AS refund_product_quantity,
        (SELECT MAX(erp2.date_refund) FROM " . DB_PREFIX . "ga4_ecommerce_refund_product erp2 WHERE erp2.order_id = o.order_id) AS date_refund, 
        (SELECT COUNT(*) FROM " . DB_PREFIX . "order_product op WHERE op.order_id = o.order_id) AS product_count, 
        (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status, 
        o.shipping_code, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified FROM `" . DB_PREFIX . "order` o";

        $sql .= " LEFT JOIN " . DB_PREFIX . "ga4_ecommerce_order eo ON o.order_id = eo.order_id";

        if (!empty($data['filter_order_status'])) {
            $implode = array();

            $order_statuses = explode(',', $data['filter_order_status']);

            foreach ($order_statuses as $order_status_id) {
                $implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
            }

            if ($implode) {
                $sql .= " WHERE (" . implode(" OR ", $implode) . ")";
            }
        } elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
            $sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        } else {
            $sql .= " WHERE o.order_status_id > '0'";
        }

        if (!empty($data['filter_order_id'])) {
            $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_date_added'])) {
            $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if (!empty($data['filter_date_modified'])) {
            $sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
        }

        if (!empty($data['filter_total'])) {
            $sql .= " AND o.total = '" . (float)$data['filter_total'] . "'";
        }

        if (isset($data['store_id'])) {
            $sql .= " AND o.store_id = '" . (int)$data['store_id'] . "'";
        }

        $sort_data = array(
            'o.order_id',
            'order_status',
            'o.date_added',
            'o.date_modified',
            'o.total',
            'eo.purchase_status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY o.order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getOrderProducts($order_id) {
        $sql = "SELECT op.*, o.currency_code, o.currency_value, erp.quantity AS refund FROM `" . DB_PREFIX . "order_product` op";
        $sql .= " LEFT JOIN " . DB_PREFIX . "order o ON o.order_id = op.order_id";
        $sql .= " LEFT JOIN " . DB_PREFIX . "ga4_ecommerce_refund_product erp ON op.order_product_id = erp.order_product_id";
        $sql .= " WHERE op.order_id = '" . (int)$order_id . "'";

        $query = $this->db->query($sql);

        foreach ($query->rows as $key => $item) {
            $query_option = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_option` WHERE order_product_id = '" . (int)$item['order_product_id'] . "'");

            $query->rows[$key]['options'] = $query_option->rows;

            if (!$query->rows[$key]['refund']) {
                $query->rows[$key]['refund'] = 0;
            }

            $query->rows[$key]['price'] = $this->currency->format($item['price'] + ($this->config->get('config_tax') ? $item['tax'] : 0), $item['currency_code'], $item['currency_value']);
            $query->rows[$key]['total'] = $this->currency->format($item['total'] + ($this->config->get('config_tax') ? ($item['tax'] * $item['quantity']) : 0), $item['currency_code'], $item['currency_value']);
        }

        return $query->rows;
    }

    public function addEOrder($order_id, $data = array()) {
        $sql = "INSERT IGNORE INTO `" . DB_PREFIX . "ga4_ecommerce_order` SET order_id = '" . (int)$order_id . "', ";
        $sql .= "`client_id` = '" . $this->db->escape($data['client_id']) . "', ";
        $sql .= "`tracking_type` = '" . (int)$data['tracking_type'] . "', ";
        $sql .= "`date_registration` = NOW()";

        $this->db->query($sql);
    }

    public function editEOrder($order_id, $data = array()) {
        $sql = "UPDATE `" . DB_PREFIX . "ga4_ecommerce_order` SET date_registration = NOW() ";

        if (isset($data['client_id'])) {
            $sql .= ", `client_id` = '" . $this->db->escape($data['client_id']) . "' ";
        }

        if (isset($data['tracking_type'])) {
            $sql .= ", tracking_type = '" . (int)$data['tracking_type'] . "'";
        }

        $sql .= " WHERE order_id = '" . (int)$order_id . "'";

        $this->db->query($sql);
    }

    public function getTotalOrders($data = array()) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`";

        if (!empty($data['filter_order_status'])) {
            $implode = array();

            $order_statuses = explode(',', $data['filter_order_status']);

            foreach ($order_statuses as $order_status_id) {
                $implode[] = "order_status_id = '" . (int)$order_status_id . "'";
            }

            if ($implode) {
                $sql .= " WHERE (" . implode(" OR ", $implode) . ")";
            }
        } elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
            $sql .= " WHERE order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        } else {
            $sql .= " WHERE order_status_id > '0'";
        }

        if (!empty($data['filter_order_id'])) {
            $sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_date_added'])) {
            $sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if (!empty($data['filter_date_modified'])) {
            $sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
        }

        if (!empty($data['filter_total'])) {
            $sql .= " AND total = '" . (float)$data['filter_total'] . "'";
        }

        if (isset($data['store_id'])) {
            $sql .= " AND store_id = '" . (int)$data['store_id'] . "'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function install($code, $path) {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ga4_ecommerce_order`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ga4_ecommerce_order_session`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ga4_ecommerce_refund_product`");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ga4_ecommerce_order` (
          `order_id` int(11) NOT NULL,
          `client_id` varchar(64) NOT NULL,
          `tracking_type` tinyint(1) NOT NULL DEFAULT '0',
          `purchase_status` tinyint(1) NOT NULL DEFAULT '0',
          `date_registration` datetime NOT NULL,
          `date_tracking` datetime NOT NULL,
          PRIMARY KEY (`order_id`, `client_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ga4_ecommerce_order_session` (
          `order_id` int(11) NOT NULL,
          `measurement_id` varchar(32) NOT NULL,
          `session_id` int(11) NOT NULL,
          `session_number` int(11) NOT NULL,
          PRIMARY KEY (`order_id`, `measurement_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ga4_ecommerce_refund_product` (
          `order_product_id` int(11) NOT NULL,
          `order_id` int(11) NOT NULL,
          `product_id` int(11) NOT NULL,
          `quantity` int(4) NOT NULL,
          `tracking_type` tinyint(1) NOT NULL DEFAULT '0',
          `date_refund` datetime NOT NULL,
          PRIMARY KEY (`order_product_id`, `order_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");


        if (substr(VERSION, 0, 7) >= '3.0.0.0') {
            $events = array(
                array('trigger' => 'catalog/view/common/header/after', 'action' => $path . '/common_header_after'),
                array('trigger' => 'catalog/view/common/header/before', 'action' => $path . '/common_header_before'),
                array('trigger' => 'catalog/view/common/footer/before', 'action' => $path . '/common_footer_before'),
                array('trigger' => 'catalog/view/common/footer/after', 'action' => $path . '/common_footer_after'),
                array('trigger' => 'catalog/view/product/category/after', 'action' => $path . '/product_list_after'),
                array('trigger' => 'catalog/view/product/search/after', 'action' => $path . '/product_list_after'),
                array('trigger' => 'catalog/view/product/manufacturer_info/after', 'action' => $path . '/product_list_after'),
                array('trigger' => 'catalog/view/product/compare/after', 'action' => $path . '/product_list_after'),
                array('trigger' => 'catalog/view/product/special/after', 'action' => $path . '/product_list_after'),
                array('trigger' => 'catalog/view/product/product/after', 'action' => $path . '/product_product_after'),
                array('trigger' => 'catalog/view/extension/module/bestseller/after', 'action' => $path . '/product_list_after'),
                array('trigger' => 'catalog/view/extension/module/featured/after', 'action' => $path . '/product_list_after'),
                array('trigger' => 'catalog/view/extension/module/latest/after', 'action' => $path . '/product_list_after'),
                array('trigger' => 'catalog/view/extension/module/special/after', 'action' => $path . '/product_list_after'),
                array('trigger' => 'catalog/controller/account/wishlist/add/before', 'action' => $path . '/account_wishlist_add_before'),
                array('trigger' => 'catalog/view/checkout/cart/after', 'action' => $path . '/checkout_cart_after'),
                array('trigger' => 'catalog/view/common/cart/after', 'action' => $path . '/common_cart_after'),
                array('trigger' => 'catalog/controller/checkout/cart/add/before', 'action' => $path . '/checkout_cart_add_before'),
                array('trigger' => 'catalog/controller/checkout/cart/edit/before', 'action' => $path . '/checkout_cart_edit_before'),
                array('trigger' => 'catalog/controller/checkout/cart/remove/before', 'action' => $path . '/checkout_cart_remove_before'),
                array('trigger' => 'catalog/view/checkout/checkout/after', 'action' => $path . '/checkout_checkout_after'),
                array('trigger' => 'catalog/view/checkout/payment_method/after', 'action' => $path . '/checkout_payment_method_after'),
                array('trigger' => 'catalog/view/checkout/confirm/after', 'action' => $path . '/checkout_confirm_after'),
                array('trigger' => 'catalog/model/checkout/order/addOrder/after', 'action' => $path . '/add_order_after'),
                array('trigger' => 'catalog/model/checkout/order/addOrderHistory/after', 'action' => $path . '/add_order_history_after'),
                array('trigger' => 'catalog/view/extension/module/banner/after', 'action' => $path . '/promotion_list_after'),
                array('trigger' => 'catalog/view/extension/module/carousel/after', 'action' => $path . '/promotion_list_after'),
                array('trigger' => 'catalog/view/extension/module/slideshow/after', 'action' => $path . '/promotion_list_after'),
                array('trigger' => 'catalog/view/common/success/after', 'action' => $path . '/common_success_after')
            );

            $this->installEvents($code, $events);
        }

        $this->db->query("UPDATE `" . DB_PREFIX . "modification` SET status=1 WHERE `code` LIKE '" . $code . "%'");

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification` WHERE `code` LIKE '" . $code . "%'");

        return $query->num_rows;
    }

    public function installEvents($code, $events) {
        $this->load->model('setting/event');

        $this->uninstallEvents($code);

        $query = $this->db->query("SELECT MAX(sort_order) as sort_order FROM `" . DB_PREFIX . "event`");

        if ($query->num_rows) {
            $sort_order = $query->row['sort_order'];
        } else {
            $sort_order = 0;
        }

        foreach ($events as $key => $event) {
            $this->model_setting_event->addEvent($code, $event['trigger'], $event['action'], isset($event['status']) ? $event['status'] : 1, ++$sort_order);
        }
    }

    public function uninstall($code) {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ga4_ecommerce_order`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ga4_ecommerce_order_session`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ga4_ecommerce_refund_product`");

        if ($this->getTableExist('ga4_ecommerce_order')) {
            $this->db->query("TRUNCATE `" . DB_PREFIX . "ga4_ecommerce_order`");
        }
        if ($this->getTableExist('ga4_ecommerce_order_session')) {
            $this->db->query("TRUNCATE `" . DB_PREFIX . "ga4_ecommerce_order_session`");
        }
        if ($this->getTableExist('ga4_ecommerce_refund_product')) {
            $this->db->query("TRUNCATE `" . DB_PREFIX . "ga4_ecommerce_refund_product`");
        }

        if (substr(VERSION, 0, 7) >= '3.0.0.0') {
            $this->uninstallEvents($code);
        }

        $this->deleteModifications($code . '_theme_');
        $this->deleteModifications($code . '_checkout_');

        $this->db->query("UPDATE `" . DB_PREFIX . "modification` SET status=0 WHERE `code` LIKE '" . $code . "%'");

        $modifications = $this->getModifications($code);

        return count($modifications);
    }

    public function uninstallEvents($code) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` LIKE '" . $this->db->escape($code) . "%'");
    }

    public function checkSettingValueExist($key, $value) {
        $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE `key` = '" . $this->db->escape($key) . "' AND `value` = '" . $this->db->escape($value) . "'");

        return $query->num_rows ? true : false;
    }

    public function getModifications($code) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification` WHERE `code` LIKE '" . $this->db->escape($code) . "%'");

        return $query->rows;
    }

    private function deleteModifications($code) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE code LIKE'" . $this->db->escape($code) . "%'");
    }

    public function getTableExist($table_name) {
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table_name . "'");

        return $query->num_rows;
    }

    public function getTableColumns($table_name, $exclusion = array()) {
        $query = $this->db->query("SELECT `COLUMN_NAME` as name FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_NAME`='" . DB_PREFIX . $table_name . "' AND `TABLE_SCHEMA` = '" . DB_DATABASE . "'");

        $columns = array();

        if ($query->num_rows) {
            foreach ($query->rows as $column) {
                if (!in_array($column['name'], $exclusion))
                    $columns[] = $column['name'];
            }
        }

        return $columns;
    }

    public function getSettingValue($key, $store_id = 0) {
        $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

        if ($query->num_rows) {
            return $query->row['value'];
        } else {
            return null;
        }
    }
}