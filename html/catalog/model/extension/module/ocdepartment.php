<?php
class ModelExtensionModuleOCDepartment extends Model {
  protected function getCategorySQL($product_sql, $parent_id = null) {
    $sql = "SELECT * FROM (SELECT c.parent_id, c.sort_order, cd.name, COUNT(DISTINCT p2c.product_id) AS total, cp.level, cp.path_id AS category_id";

   	$sql .= ", (SELECT MAX(cp2.level) FROM " . DB_PREFIX . "category_path cp2 WHERE cp2.category_id = cp.category_id) AS max_level";

    $sql .= ", (SELECT GROUP_CONCAT(cp3.path_id ORDER BY cp3.level SEPARATOR '_') FROM " . DB_PREFIX . "category_path cp3 WHERE cp3.category_id = cp.category_id AND cp3.level <= cp.level) AS path

    FROM " . DB_PREFIX . "category_path cp

    LEFT JOIN (SELECT p2c.category_id, p2c.product_id FROM " . DB_PREFIX . "product_to_category p2c RIGHT JOIN (" . $product_sql . ") p ON (p2c.product_id = p.product_id)) p2c ON (cp.category_id = p2c.category_id)
    LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p2c.product_id = p2s.product_id)
    LEFT JOIN " . DB_PREFIX . "category c ON (cp.path_id = c.category_id)
    LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
    LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id)

    WHERE c.status = '1' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY cp.path_id) result";

    if (is_null($parent_id)) {
      $sql .= " WHERE IF(max_level > '1', `level` >= (max_level - 1), 1)";
    } else if ($parent_id > 0) {
      $sql .= " WHERE parent_id = '" . (int)$parent_id . "'";
    } else {
      $sql .= " WHERE IF(max_level > '1', `level` >= (max_level - 1), parent_id = '0')";
    }

    $sql .= " ORDER BY LCASE(name), total DESC, sort_order";
	
    return $sql;
  }

  public function getCategories($parent_id = 0) {
    $cache_key = 'category.catalog.rows.' . (int)$parent_id . '.' . (int)$this->config->get('config_language_id');

    $category_data = $this->cache->get($cache_key);

    if (is_array($category_data)) {
      return $category_data;
    }

    $query = $this->db->query("SELECT *, (SELECT COUNT(*) FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p.status = '1' AND p.date_available <= '" . date('Y-m-d') . "' AND p2c.category_id = c.category_id) AS total FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

    $category_data = $query->rows;

    $this->cache->set($cache_key, $category_data);

    return $category_data;
  }
  
	public function getCategoriesNovinki($parent_id = 0) {
    $cache_key = 'category.novinki.rows.' . (int)$parent_id . '.' . (int)$this->config->get('config_language_id');

    $category_data = $this->cache->get($cache_key);

    if (is_array($category_data)) {
      return $category_data;
    }
	
	$product_sql = "SELECT DISTINCT p.product_id FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_to_category as pc ON pc.product_id = p.product_id LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND pc.category_id = '". (int)$parent_id ."'";
	
	
    $query = $this->db->query($this->getCategorySQL($product_sql));
	
    $category_data = $query->rows;

    $this->cache->set($cache_key, $category_data);

    return $category_data;
  }

  public function getManufacturerCategories($manufacturer_id) {
    $cache_key = 'category.manufacturer.rows.' . (int)$manufacturer_id . '.' . (int)$this->config->get('config_language_id');

    $category_data = $this->cache->get($cache_key);

    if (is_array($category_data)) {
      return $category_data;
    }

    $product_sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p WHERE p.status = '1' AND p.date_available <= '" . date('Y-m-d') . "' AND p.manufacturer_id = '" . (int)$manufacturer_id . "'";

    $query = $this->db->query($this->getCategorySQL($product_sql));

    $category_data = $query->rows;

    $this->cache->set($cache_key, $category_data);

    return $category_data;
  }

  public function getSpecialCategories() {
    $cache_key = 'category.special.rows.' . (int)$this->config->get('config_language_id');

    $category_data = $this->cache->get($cache_key);

    if (is_array($category_data)) {
      return $category_data;
    }

	
    $product_sql = "SELECT DISTINCT ps.product_id FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id";
	
	
    $query = $this->db->query($this->getCategorySQL($product_sql));

    $category_data = $query->rows;

    $this->cache->set($cache_key, $category_data);

    return $category_data;
  }

  public function getProductSearchCategories($search, $description = false) {
    $search = trim(preg_replace('/\s+/', ' ', utf8_strtolower(urldecode($search))));

    $cache_key = 'category.product.search.rows.' . md5($search) . '.' . (int)$this->config->get('config_language_id');

    $category_data = $this->cache->get($cache_key);

    if (is_array($category_data)) {
      return $category_data;
    }

    $product_sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.status = '1' AND p.date_available <= '" . date('Y-m-d') . "'";

    $product_sql .= " AND (";

    $product_sql .= "LCASE(pd.name) LIKE '%" . $this->db->escape(str_replace(' ', '%', $search)) . "%'";

    if ($description) {
      $product_sql .= " OR pd.description LIKE '%" . $this->db->escape(str_replace(' ', '%', $search)) . "%'";
    }

    $product_sql .= " OR LCASE(p.model) LIKE '%" . $this->db->escape($search) . "%'";
    $product_sql .= " OR LCASE(p.sku) = '" . $this->db->escape($search) . "'";
    /*
    $product_sql .= " OR LCASE(p.upc) = '" . $this->db->escape($search) . "'";
    $product_sql .= " OR LCASE(p.ean) = '" . $this->db->escape($search) . "'";
    $product_sql .= " OR LCASE(p.jan) = '" . $this->db->escape($search) . "'";
    $product_sql .= " OR LCASE(p.isbn) = '" . $this->db->escape($search) . "'";
    $product_sql .= " OR LCASE(p.mpn) = '" . $this->db->escape($search) . "'";
    */
    $product_sql .= ")";

    $query = $this->db->query($this->getCategorySQL($product_sql));

    $category_data = $query->rows;

    $this->cache->set($cache_key, $category_data);

    return $category_data;
  }

  public function getTotalProductSpecials($data = array()) {
     $sql = "SELECT COUNT(DISTINCT ps.product_id) AS total FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id)";

    if (!empty($data['filter_category_id'])) {
      $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p2c.product_id = p.product_id)";
    }
	
	// OCFilter start
		if (!empty($data['filter_ocfilter'])) {
			$this->load->model('extension/module/ocfilter');

		  $ocfilter_product_sql = $this->model_extension_module_ocfilter->getSearchSQL($data['filter_ocfilter']);
			} else {
		  $ocfilter_product_sql = false;
		}

		if ($ocfilter_product_sql && $ocfilter_product_sql->join) {
			$sql .= $ocfilter_product_sql->join;
		}
		// OCFilter end
	
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))";
	
	  // OCFilter start
			if (!empty($ocfilter_product_sql) && $ocfilter_product_sql->where) {
				$sql .= $ocfilter_product_sql->where;
			}
			// OCFilter end
			
	/* Фильтр по ID производителя */
		if (!empty($data['filter_ocfilter'])) {
			$filter_array = $this->decodeParamsFromString($data['filter_ocfilter']);
			if (!empty($filter_array['m']) && is_array($filter_array['m'])) {
				$manufacturer_ids = array_map('intval', $filter_array['m']);
				$sql .= " AND p.manufacturer_id IN (" . implode(',', $manufacturer_ids) . ")";

			}
		}
		
		/* / Фильтр по ID производителя */
	
    if (!empty($data['filter_category_id'])) {
      $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
    }
	
    $query = $this->db->query($sql);

    if (isset($query->row['total'])) {
      return $query->row['total'];
    } else {
      return 0;
    }
  }
  
  private function decodeParamsFromString($params) {

  	$decode = array();


      foreach (explode(';', $params) as $part) {
        $option = explode(':', $part);

        $values = explode(',', $option[1]);

        sort($values);

        $decode[$option[0]] = $values;
      }
 

    ksort($decode);

    return $decode;
  }
}