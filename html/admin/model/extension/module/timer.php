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

class ModelExtensionModuleTimer extends Model
{
    public static $hours_days_status = false;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('setting/setting');

        // Check the status of module "Days and hours in specials"
        // Get settings of the module "Days and hours in specials"
        $settings = $this->config->get('hours_and_days_settings');

        if (isset($settings) && $settings['module_status'] != false) {
            $this->hours_days_status = true;
        }
    }

    public function getHoursDaysStatus()
    {
        return $this->hours_days_status;
    }

    public function getTotalProductsWithoutSpecials()
    {
        $sql = "select count(p.product_id) AS max_products from " . DB_PREFIX . "product p ";
        $sql .= "where p.product_id not in (select product_id from " . DB_PREFIX . "product_special) ";
        $sql .= "AND p.status = 1 AND p.quantity > 0";

        $query = $this->db->query($sql);

        return $query->rows[0]['max_products'];
    }

    public function getProductsSpecials($data = array())
    {
        $sql = "SELECT DISTINCT(ps.product_special_id), p.product_id, p.manufacturer_id, p.image, pd.name, p.status, p.quantity, p.price as 'old_price', ";
        $sql .= " ps.customer_group_id, ps.priority, ps.price AS 'special_price', ps.date_start AS 'special_date_start', ps.date_end AS 'special_date_end', ps.timer AS 'timer_status', ps.product_special_group_id AS 'special_group_id'";

        if ($this->hours_days_status) {
            $sql .= ", ps.weekdays AS 'special_weekdays', ps.hours AS 'special_hours'";
        }

        $sql .= " FROM " . DB_PREFIX . "product_special ps";

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = ps.product_id)";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = ps.product_id) ";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (ps.product_id = p2c.product_id)";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_special_group psg ON (ps.product_special_group_id = psg.product_special_group_id)";
        $sql .= " WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "'";

        // Filter start
        $sql .= $this->getAdditionalSQLByFilters($data);

        # Start to SORT
        $sort_data = array(
            'pd.name',
            'p.price',
            'ps.price',
            'p.quantity',
            'p.status',
            // 'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY pd.name";
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

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalProductsSpecials($data = array())
    {
        /* For single search among all specials */

        $sql = "SELECT COUNT(DISTINCT(ps.product_special_id)) AS total FROM " . DB_PREFIX . "product_special ps ";

        $sql .= "LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = ps.product_id) ";
        $sql .= "LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = ps.product_id) ";
        $sql .= "LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (ps.product_id = p2c.product_id) ";
        $sql .= "LEFT JOIN " . DB_PREFIX . "product_special_group psg ON (ps.product_special_group_id = psg.product_special_group_id) ";
        $sql .= "WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "' ";

        $sql .= $this->getAdditionalSQLByFilters($data);

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    # Additional part of SQL query to filter Products Specials
    private function getAdditionalSQLByFilters($data = array())
    {
        $sql = "";

        // Security
        foreach ($data as $key => $val) {
            if (is_null($val)) {
                $data[$key] = null;
            } else {
                $data[$key] = $this->db->escape($val);
            }
        }

        # Filter by Product Name
        if (!is_null($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '%" . $data['filter_name'] . "%'";
        }

        # Filter by Model
        if (!is_null($data['filter_model'])) {
            $sql .= " AND p.model LIKE '%" . $data['filter_model'] . "%'";
        }

        # Filter by Product Special Weekdays and Hours if the module is enabled
        # Function Find_in_set() can search only for one string in a set of strings.
        if ($this->hours_days_status) {
            if (!is_null($data['filter_weekdays'])) {
                $weekdays = explode('_', $data['filter_weekdays']);

                foreach ($weekdays as $weekday_id) {
                    $sql .= " AND FIND_IN_SET('" . $weekday_id . "', ps.weekdays)";
                }
            }

            if (!is_null($data['filter_hours'])) {
                $hours = explode('_', $data['filter_hours']);

                foreach ($hours as $hour_id) {
                    $sql .= " AND FIND_IN_SET('" . $hour_id . "', ps.hours)";
                }
            }
        }

        # Filter by Product Special Dates
        if (!is_null($data['filter_special_date_from']) || !is_null($data['filter_special_date_to'])) {
            if (!is_null($data['filter_special_date_from']) and is_null($data['filter_special_date_to'])) {
                $sql .= " AND ps.date_start >= '" . $data['filter_special_date_from'] . "'";
            }

            if (is_null($data['filter_special_date_from']) and !is_null($data['filter_special_date_to'])) {
                $sql .= " AND ps.date_end <= '" . $data['filter_special_date_to'] . "'";
            }

            if (!is_null($data['filter_special_date_from']) and !is_null($data['filter_special_date_to'])) {
                $sql .= " AND (ps.date_start >= '" . $data['filter_special_date_from'] . "' AND ps.date_end <= '" . $data['filter_special_date_to'] . "')";
            }
        }

        # Filter by Ordinary Price
        if (!is_null($data['filter_price_from']) || !is_null($data['filter_price_to'])) {
            if (!is_null($data['filter_price_from']) and is_null($data['filter_price_to'])) {
                $sql .= " AND p.price >= '" . $data['filter_price_from'] . "'";
            }

            if (is_null($data['filter_price_from']) and !is_null($data['filter_price_to'])) {
                $sql .= " AND p.price <= '" . $data['filter_price_to'] . "'";
            }

            if (!is_null($data['filter_price_from']) and !is_null($data['filter_price_to'])) {
                $sql .= " AND (p.price >= '" . $data['filter_price_from'] . "' AND p.price <= '" . $data['filter_price_to'] . "')";
            }
        }

        # Filter by Category
        if (!is_null($data['filter_special_price_from']) || !is_null($data['filter_special_price_to'])) {
            if (!is_null($data['filter_special_price_from']) and is_null($data['filter_special_price_to'])) {
                $sql .= " AND ps.price >= '" . $data['filter_special_price_from'] . "'";
            }

            if (is_null($data['filter_special_price_from']) and !is_null($data['filter_special_price_to'])) {
                $sql .= " AND ps.price <= '" . $data['filter_special_price_to'] . "'";
            }

            if (!is_null($data['filter_special_price_from']) and !is_null($data['filter_special_price_to'])) {
                $sql .= " AND (ps.price >= '" . $data['filter_special_price_from'] . "' AND ps.price <= '" . $data['filter_special_price_to'] . "')";
            }
        }

        # Filter by Quantity
        if (!is_null($data['filter_quantity_from']) || !is_null($data['filter_quantity_to'])) {
            if (!is_null($data['filter_quantity_from']) and is_null($data['filter_quantity_to'])) {
                $sql .= " AND p.quantity >= '" . $data['filter_quantity_from'] . "'";
            }

            if (is_null($data['filter_quantity_from']) and !is_null($data['filter_quantity_to'])) {
                $sql .= " AND p.quantity <= '" . $data['filter_quantity_to'] . "'";
            }

            if (!is_null($data['filter_quantity_from']) and !is_null($data['filter_quantity_to'])) {
                $sql .= " AND (p.quantity >= '" . $data['filter_quantity_from'] . "' AND p.quantity <= '" . $data['filter_quantity_to'] . "')";
            }
        }

        # Filter by Category
        if (!empty($data['filter_category'])) {
            if (!empty($data['filter_sub_category'])) {
                $implode_data = array();

                $implode_data[] = "category_id = '" . (int) $data['filter_category'] . "'";

                $this->load->model('catalog/category');

                $categories = $this->model_catalog_category->getCategories($data['filter_category']);

                foreach ($categories as $category) {
                    $implode_data[] = "p2c.category_id = '" . (int) $category['category_id'] . "'";
                }

                $sql .= " AND (" . implode(' OR ', $implode_data) . ")";
            } else {
                $sql .= " AND p2c.category_id = '" . (int) $data['filter_category'] . "'";
            }
        }

        # Filter by Manufacturer
        if (isset($data['filter_manufacturer']) && $data['filter_manufacturer'] !== null) {
            $sql .= " AND p.manufacturer_id = '" . (int) $data['filter_manufacturer'] . "'";
        }

        # Filter by Customer groups
        if (!is_null($data['filter_customer_groups'])) {
            $customer_groups = array_map('intval', explode('_', $data['filter_customer_groups']));

            $sql .= " AND ps.customer_group_id IN (" . implode(',', $customer_groups) . ")";
        }

        # Filter by Special Group
        if (isset($data['filter_special_group']) && $data['filter_special_group'] !== null) {
            $data['filter_special_group'] = (int) $data['filter_special_group'];

            // Search for specials with not specified special group
            if ($data['filter_special_group'] == 0) {
                $sql .= " AND (ps.product_special_group_id = 0 OR psg.product_special_group_id IS NULL)";
            } else {
                $sql .= " AND ps.product_special_group_id = '" . (int) $data['filter_special_group'] . "'";
            }
        }

        # Filter by Product Status
        if (isset($data['filter_status']) && $data['filter_status'] !== null) {
            $sql .= " AND p.status = '" . (int) $data['filter_status'] . "'";
        }

        return $sql;
    }

    public function setNewProductSpecial($product_id, $data = 0)
    {
        $ignore_creation = 0;

        $sql = "INSERT INTO " . DB_PREFIX . "product_special";
        $sql .= " (product_id, customer_group_id, product_special_group_id, priority, price, date_start, date_end, timer";

        if ($this->hours_days_status) {
            $sql .= ", weekdays, hours";
        }

        $sql .= ") ";

        $params = array(
            (int) $product_id,
            $data['customer_group_id'],
            $this->db->escape($data['special_group_id']),
            (int) $data['priority'],
            (float) $this->db->escape($data['price']),
            $this->db->escape($data['date_start']),
            $this->db->escape($data['date_end']),
            $this->db->escape($data['timer']),
        );

        if ($this->hours_days_status) {
            array_push($params, $this->db->escape($data['weekdays']), $this->db->escape($data['hours']));
        }

        // Wrap all elements in single quotes
        $sql .= " SELECT '" . implode("', '", $params) . "' ";

        if ($data['ignore_creation_if_special_exists']) {
            // We should make a query from any table that has at least 1 record (that's why we choose information_schema)
            $sql .= "FROM information_schema.columns";
            $subquery = "SELECT 1 FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int) $product_id . "' AND customer_group_id = '" . $data['customer_group_id'] . "'";

            $sql .= " WHERE NOT EXISTS (" . $subquery . ") LIMIT 1";

            $ignore_creation = $this->db->query($subquery)->num_rows;
        }

        $query = $this->db->query($sql);

        if ($query && $ignore_creation == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getSpecialInfo($product_special_id)
    {
        $query = $this->db->query("SELECT *, product_special_group_id AS special_group_id FROM " . DB_PREFIX . "product_special WHERE product_special_id = " . $this->db->escape($product_special_id));
        return $query->rows;
    }

    // Update action
    public function updateProductSpecialBySpecialId($data)
    {
        $query     = "UPDATE " . DB_PREFIX . "product_special SET ";
        $query_arr = array();

        if (isset($data['overwrite'])) {
            if (isset($data['overwrite']['price'])) {
                $query_arr[] = "price = '" . $this->db->escape($data["price"]) . "'";
            }

            if (isset($data['overwrite']['customer_group_id'])) {
                $query_arr[] = "customer_group_id = '" . $this->db->escape($data["customer_group_id"]) . "'";
            }

            if (isset($data['overwrite']['special_group_id'])) {
                $query_arr[] = "product_special_group_id = '" . $this->db->escape($data["special_group_id"]) . "'";
            }

            if (isset($data['overwrite']['priority'])) {
                $query_arr[] = "priority = '" . $this->db->escape($data["priority"]) . "'";
            }

            if (isset($data['overwrite']['date_start'])) {
                $query_arr[] = "date_start = '" . $this->db->escape($data["date_start"]) . "'";
            }

            if (isset($data['overwrite']['date_end'])) {
                $query_arr[] = "date_end = '" . $this->db->escape($data["date_end"]) . "'";
            }

            if (isset($data['overwrite']['timer'])) {
                $query_arr[] = "timer = '" . $this->db->escape($data["timer"]) . "'";
            }

            if ($this->hours_days_status && isset($data['overwrite']['weekdays'])) {
                $query_arr[] = "weekdays = '" . $this->db->escape($data['weekdays']) . "'";
            }

            if ($this->hours_days_status && isset($data['overwrite']['hours'])) {
                $query_arr[] = "hours = '" . $this->db->escape($data['hours']) . "'";
            }

        } else {
            $query_arr[] = "price = '" . $this->db->escape($data["price"]) . "'";
            $query_arr[] = "customer_group_id = '" . $this->db->escape($data["customer_group_id"]) . "'";
            $query_arr[] = "product_special_group_id = '" . $this->db->escape($data["special_group_id"]) . "'";
            $query_arr[] = "priority = '" . $this->db->escape($data["priority"]) . "'";
            $query_arr[] = "date_start = '" . $this->db->escape($data["date_start"]) . "'";
            $query_arr[] = "date_end = '" . $this->db->escape($data["date_end"]) . "'";
            $query_arr[] = "timer = '" . $this->db->escape($data["timer"]) . "'";

            if ($this->hours_days_status) {
                $query_arr[] = "weekdays = '" . $this->db->escape($data['weekdays']) . "'";
                $query_arr[] = "hours = '" . $this->db->escape($data['hours']) . "'";
            }
        }

        if (count($query_arr) > 0) {
            $query .= implode(', ', $query_arr);
            $query .= " WHERE product_special_id = '" . $this->db->escape($data["product_special_id"]) . "'";

            $query = $this->db->query($query);
        } else {
            $query = false;
        }

        if ($query) {
            return $this->getSpecialInfo($this->db->escape($data['product_special_id']));
        } else {
            return false;
        }
    }

    # Delete all Procuts Specials
    public function deleteAllProductsSpecials()
    {
        $query = $this->db->query("DELETE FROM " . DB_PREFIX . "product_special");

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteSpecialById($product_special_id)
    {
        $query = $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_special_id = " . $product_special_id);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteProductSpecialByProductId($product_id)
    {
        $query = $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = " . (int) $product_id);
        return true;
    }

    //  Category tree
    public function getCategories($parent_id, $level = -1)
    {
        $level++;

        $results = $this->getCategoriesByParentId($parent_id);

        $categories_data = array();

        foreach ($results as $result) {
            $categories_data[] = array(
                'category_id' => $result['category_id'],
                'name'        => $result['name'],
                'level'       => $level,
            );

            $categories_data = array_merge($categories_data, $this->getCategories($result['category_id'], $level));
        }

        return $categories_data;
    }

    private function getCategoriesByParentId($parent_id = 0)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.parent_id = '" . (int) $parent_id . "' AND cd.language_id = '" . (int) $this->config->get('config_language_id') . "' ORDER BY c.sort_order, cd.name ASC");
        return $query->rows;
    }

    public function getProductPriceByProductSpecialId($product_special_id)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "product_special ps ";
        $sql .= "INNER JOIN " . DB_PREFIX . "product p";
        $sql .= "  ON ps.product_id = p.product_id ";
        $sql .= "INNER JOIN " . DB_PREFIX . "product_description pd";
        $sql .= "  ON ps.product_id = pd.product_id ";
        $sql .= "WHERE ps.product_special_id = '" . $this->db->escape($product_special_id) . "' ";
        $sql .= " AND pd.language_id = '" . (int) $this->config->get('config_language_id') . "' ";

        $query = $this->db->query($sql);
        return $query->rows[0];
    }

    # Special Groups
    public function getSpecialGroups($special_group_id = 0)
    {
        if ($special_group_id > 0) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special_group WHERE product_special_group_id = '" . (int) $special_group_id . "'");
        } else {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special_group ORDER by name ASC");
        }

        return $query->rows;
    }

    public function addNewSpecialGroup($special_group_name)
    {
        $query = $this->db->query("INSERT INTO " . DB_PREFIX . "product_special_group (name) VALUES ('" . $this->db->escape($special_group_name) . "')");
        return $this->db->getLastId();
    }

    public function renameSpecialGroup($special_group_id, $special_group_name)
    {
        $sql = "UPDATE " . DB_PREFIX . "product_special_group ";
        $sql .= "SET name = '" . $special_group_name . "' ";
        $sql .= "WHERE product_special_group_id = '" . $special_group_id . "'";

        $this->db->query($sql);
    }

    public function deleteSpecialGroupById($special_group_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special_group WHERE product_special_group_id = " . (int) $special_group_id);
        return true;
    }

    # Part for attributes
    # Limit 100 attributes
    public function getAttributes($args)
    {
        $sql = "
            SELECT
                agd.name as attribute_group_name,
                ad.name as attribute_name,
                pa.text AS attribute_value,
                pa.attribute_id

            FROM " . DB_PREFIX . "product_attribute AS pa
            LEFT JOIN " . DB_PREFIX . "attribute AS a ON pa.attribute_id = a.attribute_id
            LEFT JOIN " . DB_PREFIX . "attribute_description AS ad ON pa.attribute_id = ad.attribute_id
            LEFT JOIN " . DB_PREFIX . "attribute_group_description AS agd
                ON a.attribute_group_id = agd.attribute_group_id AND pa.language_id = agd.language_id

            WHERE ad.name LIKE '%" . $args['attribute_name'] . "%'
                AND pa.language_id = '" . (int) $this->config->get('config_language_id') . "'
                AND pa.text LIKE '%" . $args['attribute_value'] . "%' " . $args['extra_conditons'] . "

            GROUP BY pa.text
            ORDER BY ad.name, attribute_value ASC
            LIMIT 100";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getOptions($args)
    {
        $sql = "
            SELECT
                ovd.option_id,
                od.name as option_name,
                ovd.option_value_id,
                ovd.name as option_value_name

            FROM " . DB_PREFIX . "option_value_description AS ovd
            LEFT JOIN " . DB_PREFIX . "option_description AS od ON ovd.option_id = od.option_id AND ovd.language_id = od.language_id
            WHERE od.name LIKE '%" . $args['option_name'] . "%'
                AND ovd.language_id = '" . (int) $this->config->get('config_language_id') . "'
                AND ovd.name LIKE '%" . $args['option_value'] . "%'
                " . $args['extra_conditons'] . "
            ORDER BY option_name ASC
            LIMIT 100
        ";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getPossibleProducts($post)
    {
        $header = " SELECT p.product_id, pd.name, p.price, p.image, p.status, p.quantity ";
        $header .= " FROM " . DB_PREFIX . "product as p ";
        $header .= " LEFT JOIN " . DB_PREFIX . "product_description AS pd ON p.product_id = pd.product_id ";

        $sql = $header;

        // Append additional tables to check the conditions
        if (isset($post['categories'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category as p2c ON p.product_id = p2c.product_id ";
        }

        if (isset($post['attributes'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_attribute as pa ON p.product_id = pa.product_id AND pd.language_id = pa.language_id";
        }

        if (isset($post['options'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_option_value AS pov ON p.product_id = pov.product_id ";
        }

        // Conditions
        $sql .= " WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "' ";

        // Filter by categories
        if (isset($post['categories'])) {
            $category_ids = array_map('intval', $post['categories']);
            $category_ids = implode(",", $category_ids);

            $sql .= " AND p2c.category_id IN (" . $category_ids . ") ";
        }

        // Filter by manufacturers
        if (isset($post['manufacturers'])) {
            $manufacturer_ids = array_map('intval', $post['manufacturers']);
            $manufacturer_ids = implode(",", $manufacturer_ids);

            $sql .= " AND p.manufacturer_id IN (" . $manufacturer_ids . ") ";
        }

        // Filter by attributes
        if (isset($post['attributes'])) {
            $attribute_ids          = array_map('intval', $post['attributes']['id']);
            $attribute_values       = $post['attributes']['value'];
            $attribute_restrictions = array();

            // nested conditions for attributes
            foreach ($attribute_ids as $key => $attribute_id) {
                $attribute_restrictions[] = " (pa.attribute_id = '" . $attribute_id . "' AND pa.text = '" . $this->db->escape($attribute_values[$key]) . "') ";
            }

            $sql .= " AND (" . implode(" OR ", $attribute_restrictions) . ") ";
        }

        // Filter by options
        if (isset($post['options'])) {
            $option_ids          = array_map('intval', $post['options']['id']);
            $option_values       = array_map('intval', $post['options']['value']);
            $option_restrictions = array();

            // nested conditions for options
            foreach ($option_ids as $key => $option_id) {
                $option_restrictions[] = " (pov.option_id = '" . $option_id . "' AND pov.option_value_id = '" . $option_values[$key] . "') ";
            }

            $sql .= " AND (" . implode(" OR ", $option_restrictions) . ") ";
        }

        if (isset($post['ignore_creation_if_special_exists'])) {
            $sql .= " AND p.product_id NOT IN (SELECT product_id FROM " . DB_PREFIX . "product_special) ";
        }

        // Attach additional chosen products
        if (isset($post['products'])) {
            $product_ids = array_map('intval', $post['products']['id']);

            $sql .= " UNION " . $header;

            // If nothing is chosen, then just show all
            if (!isset($post['categories']) && !isset($post['manufacturers']) && !isset($post['attributes']) && !isset($post['options'])) {
                $sql = $header;
            }

            $sql .= " WHERE p.product_id IN (" . implode(",", $product_ids) . ") AND pd.language_id = '" . (int) $this->config->get('config_language_id') . "' ";

            if (isset($post['ignore_creation_if_special_exists'])) {
                $sql .= " AND p.product_id NOT IN (SELECT product_id from " . DB_PREFIX . "product_special) ";
            }
        }

        $sql .= " GROUP BY product_id ";
        $sql .= " ORDER BY name ";
        // $sql .= " ORDER BY product_name ";

        $query = $this->db->query($sql);

        // return total # of possible products, and their ids and names
        return array(
            'total'    => $query->num_rows,
            'products' => $query->rows,
        );
    }

    public function getPossibleSpecials($post, $start_limit, $load_all = false)
    {
        // Conditions for SQL query
        function getSQLWhereCondition($query_header, $post, $config_language_id)
        {
            $sql = " WHERE pd.language_id = '" . (int) $config_language_id . "' ";

            // Filter by categories
            if (isset($post['categories'])) {
                $category_ids = array_map('intval', $post['categories']);
                $category_ids = implode(",", $category_ids);

                $sql .= " AND p2c.category_id IN (" . $category_ids . ") ";
            }

            // Filter by manufacturers
            if (isset($post['manufacturers'])) {
                $manufacturer_ids = array_map('intval', $post['manufacturers']);
                $manufacturer_ids = implode(",", $manufacturer_ids);

                $sql .= " AND p.manufacturer_id IN (" . $manufacturer_ids . ") ";
            }

            // Filter by special_groups
            if (isset($post['special_groups'])) {
                $special_groups_ids = array_map('intval', $post['special_groups']);
                $special_groups_ids = implode(",", $special_groups_ids);

                $sql .= " AND ps.product_special_group_id IN (" . $special_groups_ids . ") ";
            }

            // Attach additional chosen products
            if (isset($post['products'])) {
                $product_ids = array_map('intval', $post['products']['id']);

                $sql .= " UNION " . $query_header;
                $sql .= " WHERE ps.product_id IN (" . implode(",", $product_ids) . ") AND pd.language_id = '" . (int) $config_language_id . "' ";
            }

            return $sql;
        }

        $sql_join_conditions = " FROM `" . DB_PREFIX . "product_special` as ps ";
        $sql_join_conditions .= " LEFT JOIN `" . DB_PREFIX . "product` as p ON (ps.product_id = p.product_id) ";
        $sql_join_conditions .= " LEFT JOIN `" . DB_PREFIX . "product_description` as pd ON (ps.product_id = pd.product_id) ";
        $sql_join_conditions .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` as p2c ON (ps.product_id = p2c.product_id) ";
        $sql_join_conditions .= " LEFT JOIN `" . DB_PREFIX . "product_special_group` as psg ON (ps.product_special_group_id = psg.product_special_group_id) ";

        $query_header = " SELECT
                            DISTINCT(ps.product_special_id),
                            ps.product_id,
                            ps.priority,
                            ps.customer_group_id,
                            ps.date_start,
                            ps.date_end,
                            ps.price as `special_price`,
                            p.price as `old_price`,
                            IFNULL(psg.name, '-') AS 'special_group_name',
                            ps.timer AS 'timer_status',
                            p.image,
                            p.quantity,
                            p.status,
                            pd.name ";

        if ($this->hours_days_status) {
            $query_header .= ", ps.weekdays, ps.hours ";
        }

        $query_header .= $sql_join_conditions;

        // As we use
        $sql = "SELECT * FROM (" . $query_header . getSQLWhereCondition($query_header, $post, $this->config->get('config_language_id')) . ") as temp_table ";
        $sql .= " ORDER BY name, product_id ";

        // Show only 100 products each time
        if (!$load_all) {
            $limit = 100;
            $sql .= " LIMIT " . $start_limit . ", " . $limit;
        }

        $query    = $this->db->query($sql);
        $specials = $query->rows;

        /**
         *
         * Count total num-r of possible products for given filters
         *
         */
        $query_header = " SELECT DISTINCT(ps.product_special_id) ";
        $query_header .= $sql_join_conditions;

        $sql = "SELECT COUNT(*) as total FROM (" . $query_header . getSQLWhereCondition($query_header, $post, $this->config->get('config_language_id')) . ") as temp_table";

        $query = $this->db->query($sql);
        $total = $query->row['total'];

        // return total # of possible products, and their info
        return array(
            'total'    => $total,
            'specials' => $specials,
        );
    }

    # =============================
    # For admin panel
    public function checkExistenceExtension($type, $extension_name)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($extension_name) . "' ");

        return $query->rows;
    }

}
