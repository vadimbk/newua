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
    private $_version = '1.2.1';
    private $_tablename = 'shoputils_cumulative_discounts';
    private $_tablename_cmsdata = 'shoputils_cumulative_discounts_cmsdata';
    private $_tablename_to_store = 'shoputils_cumulative_discounts_to_store';
    private $_tablename_description = 'shoputils_cumulative_discounts_description';
    private $_tablename_to_customer_group = 'shoputils_cumulative_discounts_to_customer_group';

    public function getDiscountsCMSData(){
        if (isset($this->session->data['selected_store_id'])){
            $selected_store_condition = ' WHERE store_id = ' . (int)$this->session->data['selected_store_id'];
        } else {
            $selected_store_condition = '';
        }

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . $this->_tablename_cmsdata . $selected_store_condition);
        $rows = array();
        foreach ($query->rows as $row){
            $rows[$row['language_id']] = $row;
        }
        return $rows;
    }

    public function getAllDiscounts(){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . $this->_tablename ." ORDER BY discount_id");
        $rows = $query->rows;

        foreach ($rows as &$row){
            $query = $this->db->query("SELECT store_id FROM " . DB_PREFIX . $this->_tablename_to_store . " WHERE discount_id = '" . $row['discount_id']."'");
            $row['stores'] = array();
            foreach($query->rows as $item){
                $row['stores'][] = $item['store_id'];
            }

            $query = $this->db->query("SELECT customer_group_id FROM " . DB_PREFIX . $this->_tablename_to_customer_group . " WHERE discount_id = '".$row['discount_id']."'");
            $row['customer_groups'] = array();
            foreach($query->rows as $item){
                $row['customer_groups'][] = $item['customer_group_id'];
            }

            $query = $this->db->query("SELECT language_id, description FROM " . DB_PREFIX . $this->_tablename_description . " WHERE discount_id = '".$row['discount_id']."'");
            $row['descriptions'] = array();
            foreach($query->rows as $item){
                $row['descriptions'][$item['language_id']] = $item['description'];
            }
        }
        return $rows;
    }

    public function triggerDeleteStore($store_id = null){
        $this->db->query('DELETE FROM ' . $this->_tablename_cmsdata . ' WHERE store_id="' . (int)$store_id.'"');
        $this->db->query('DELETE FROM ' . $this->_tablename_to_store . ' WHERE store_id="' . (int)$store_id.'"');
    }

    public function triggerDeleteLanguage($language_id = null){
        $this->db->query('DELETE FROM ' . $this->_tablename_cmsdata . ' WHERE language_id="' . (int)$language_id.'"');
        $this->db->query('DELETE FROM ' . $this->_tablename_description . ' WHERE language_id="' . (int)$language_id.'"');
    }

    public function triggerDeleteCustomerGroup($customer_group_id = null){
        $this->db->query('DELETE FROM ' . $this->_tablename_to_customer_group . ' WHERE customer_group_id="' . (int)$customer_group_id.'"');
    }


    public function editDiscounts($data){
        if (isset($this->session->data['selected_store_id'])) {
            $selected_store_condition = ' WHERE store_id = ' . (int)$this->session->data['selected_store_id'];
            $store_id = $this->session->data['selected_store_id'];
        } else {
            $selected_store_condition = '';
            $store_id = 0;
        }


        $this->db->query("DELETE FROM " . DB_PREFIX . $this->_tablename_cmsdata . $selected_store_condition);
        $this->db->query("DELETE FROM " . DB_PREFIX . $this->_tablename);
        $this->db->query("DELETE FROM " . DB_PREFIX . $this->_tablename_description);
        $this->db->query("DELETE FROM " . DB_PREFIX . $this->_tablename_to_customer_group);
        $this->db->query("DELETE FROM " . DB_PREFIX . $this->_tablename_to_store);

        if (isset($data['cmsdata'])){
            foreach ($data['cmsdata'] as $key => $cmsdata){
                $sql = "INSERT INTO " . DB_PREFIX . $this->_tablename_cmsdata . " SET store_id = '" . (int)$store_id."', language_id = '" . (int)$key . "', description_before = '" . $this->db->escape($cmsdata['description_before']) . "', description_after = '" . $this->db->escape($cmsdata['description_after']) . "'";
                $this->db->query($sql);
            }
        }

        if (isset($data['discounts'])){
            foreach ($data['discounts'] as $discount){
                $this->db->query("INSERT INTO " . DB_PREFIX . $this->_tablename." SET days = '" . (int)$discount['days'] . "', summ = '" . $this->db->escape($discount['summ']) . "', percent = '" . $this->db->escape($discount['percent']) . "', products_special = '" . (isset($discount['products_special']) ? (int)$discount['products_special'] : 0) . "', first_order = '" . (isset($discount['first_order']) ? (int)$discount['first_order'] : 0) . "'");
                $discount_id = $this->db->getLastId();

                if (isset($discount['stores'])){
                    foreach ($discount['stores'] as $store){
                        $store_id = (int)$store;
                        $this->db->query("INSERT INTO " . DB_PREFIX . $this->_tablename_to_store . " SET discount_id = '" . $discount_id . "', store_id = '" . $store_id . "'");
                    }
                }

                if (isset($discount['customer_groups'])){
                    foreach ($discount['customer_groups'] as $customer_group){
                        $customer_group_id = (int)$customer_group;
                        $this->db->query("INSERT INTO " . DB_PREFIX . $this->_tablename_to_customer_group . " SET discount_id = '" . $discount_id . "', customer_group_id = '" . $customer_group_id . "'");
                    }
                }

                if (isset($discount['descriptions'])){
                    foreach ($discount['descriptions'] as $language => $description){
                        $language_id = (int)$language;
                        $this->db->query("INSERT INTO " . DB_PREFIX . $this->_tablename_description . " SET discount_id = '" . $discount_id . "', language_id = '" . $language_id . "', description = '" . $this->db->escape($description) . "'");
                    }
                }
            }
        }
    }
    
    public function install() {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('shoputils_cumulative_discounts');

        if (!array_key_exists('version', $settings)){
            $query = $this->db->query("show tables like '".DB_PREFIX . $this->_tablename."'");
            if (!$query->rows){
                $sql = "CREATE TABLE `".DB_PREFIX . $this->_tablename."` (
                    `discount_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
                    `days` INT NOT NULL DEFAULT '0',
                    `summ` DECIMAL( 11, 2 ) NOT NULL DEFAULT '0',
                    `percent` DECIMAL( 5, 2 ) NOT NULL ,
                    `products_special` TINYINT( 1 ) NOT NULL DEFAULT 0,
                    `first_order` TINYINT( 1 ) NOT NULL DEFAULT 0,
                    PRIMARY KEY ( `discount_id` )
                ) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT = 'Cumulative discounts'";
                $this->db->query($sql);

                $sql = "CREATE TABLE `". DB_PREFIX . $this->_tablename_to_store . "` (
                    `discount_id` INT( 11 ) NOT NULL ,
                    `store_id` INT( 11 ) NOT NULL
                ) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT = 'Cumulative discounts to store'";
                $this->db->query($sql);

                $sql = "ALTER TABLE `".DB_PREFIX . $this->_tablename_to_store."` ADD UNIQUE `IDX_".DB_PREFIX . $this->_tablename_to_store."` ( `discount_id` , `store_id` )";
                $this->db->query($sql);

                $sql = "CREATE TABLE `". DB_PREFIX . $this->_tablename_to_customer_group . "` (
                    `discount_id` INT( 11 ) NOT NULL ,
                    `customer_group_id` INT( 11 ) NOT NULL
                ) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT = 'Cumulative discounts to customer group'";
                $this->db->query($sql);

                $sql = "ALTER TABLE `".DB_PREFIX . $this->_tablename_to_customer_group."` ADD UNIQUE `IDX_".DB_PREFIX . $this->_tablename_to_customer_group."` ( `discount_id` , `customer_group_id` )";
                $this->db->query($sql);

                $sql = "CREATE TABLE `". DB_PREFIX . $this->_tablename_description . "` (
                    `discount_id` INT( 11 ) NOT NULL ,
                    `language_id` INT( 11 ) NOT NULL,
                    `description` text NOT NULL
                ) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT = 'Cumulative discounts descriptions'";
                $this->db->query($sql);

                $sql = "CREATE TABLE `". DB_PREFIX . $this->_tablename_cmsdata . "` (
                    `language_id` INT( 11 ) NOT NULL,
                    `store_id` INT( 11 ) DEFAULT 0,
                    `description_before` text NOT NULL,
                    `description_after` text NOT NULL
                ) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT = 'Cumulative discounts CMS data'";
                $this->db->query($sql);

                $sql = "ALTER TABLE `".DB_PREFIX . $this->_tablename_description."` ADD UNIQUE `IDX_".DB_PREFIX . $this->_tablename_description."` ( `discount_id` , `language_id` )";
                $this->db->query($sql);
            }
            $settings['version'] = $this->_version;
            //$this->model_setting_setting->editSetting('shoputils_cumulative_discounts', $settings);
            $this->model_setting_setting->editSettingValue('shoputils_cumulative_discounts', 'shoputils_cumulative_discounts_version', $this->_version);
        }
    }

    public function uninstall() {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('shoputils_cumulative_discounts');
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . $this->_tablename);
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . $this->_tablename_cmsdata);
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . $this->_tablename_to_store);
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . $this->_tablename_description);
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . $this->_tablename_to_customer_group);
    }
}
?>