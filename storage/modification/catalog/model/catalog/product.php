<?php
class ModelCatalogProduct extends Model {
	public function updateViewed($product_id) {

			if ($product_id) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `code` LIKE 'oct_product_views'");
			
				if ($query->num_rows) {
					$product_ids = [];
					
					if (isset($this->request->cookie['oct_product_views'])) {
			            $product_ids = explode(',', $this->request->cookie['oct_product_views']);
			        } elseif (isset($this->session->data['oct_product_views'])) {
			            $product_ids = $this->session->data['oct_product_views'];
			        }
			        
			        if (isset($this->request->cookie['viewed'])) {
			            $product_ids = array_merge($product_ids, explode(',', $this->request->cookie['viewed']));
			        } elseif (isset($this->session->data['viewed'])) {
			            $product_ids = array_merge($product_ids, $this->session->data['viewed']);
			        }
					
					$product_ids = array_diff($product_ids, [(int)$product_id]);
		            
		            array_unshift($product_ids, (int)$product_id);
		            
		            setcookie('oct_product_views', implode(',',$product_ids), time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
				}
			}
			
		$this->db->query("UPDATE " . DB_PREFIX . "product SET viewed = (viewed + 1) WHERE product_id = '" . (int)$product_id . "'");
	}


	public function getProductUkrcredits($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_ukrcredits WHERE product_id = '" . (int)$product_id . "'");

		return $query->row;
	}
			
	public function getProduct($product_id) {

$this->load->model('extension/module/product_status');
      

    /* Bulk Specials Editor */
    $this->load->model('extension/module/timer');
    $timer_exist = $this->model_extension_module_timer->checkExistenceExtension('module', 'timer');
    
    $timer_query = '';

    if($timer_exist){
      $timer_query .= "
        (SELECT date_start FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND (ps.date_start = '0000-00-00' OR ps.date_start < NOW()) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS date_start, 
        (SELECT date_end FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS date_end, 
        (SELECT timer FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS timer, ";
    }
    
    $hours_days = ($hours_days = $this->config->get('hours_and_days_settings')) ? $hours_days['module_status'] : false;
    
    if($hours_days){
        $timer_query .= "
            (SELECT weekdays FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' AND (ps.date_start = '0000-00-00' OR ps.date_start < NOW()) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS weekdays,
            (SELECT hours FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' AND (ps.date_start = '0000-00-00' OR ps.date_start < NOW()) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS hours, ";
    }
    /* Bulk Specials Editor */
    
		$query = $this->db->query("SELECT DISTINCT *,  pd.name AS name, p.image, $timer_query  m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return array(

'statuses' => $this->model_extension_module_product_status->getHTMLProductStatuses($query->row),
      
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_title'       => $query->row['meta_title'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'warranty'         => $query->row['warranty'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],

            'stock_status_id'     => $query->row['stock_status_id'],
        
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],

			'oct_stickers'		=> isset($query->row['oct_stickers']) ? unserialize($query->row['oct_stickers']) : false,
			'you_save'          => $query->row['special'] ? '-' . ($query->row['discount'] ? number_format(((float)$query->row['discount'] - (float)$query->row['special']) / (float)$query->row['discount'] * 100, 0) : number_format(((float)$query->row['price'] - (float)$query->row['special']) / (float)$query->row['price'] * 100, 0)) . '%' : false,
			
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'weight'           => $query->row['weight'],
				'weight_class_id'  => $query->row['weight_class_id'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'length_class_id'  => $query->row['length_class_id'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round($query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],

    /* Bulk Specials Editor */
    'timer'          => ($timer_exist) ? $query->row['timer'] : '',
    'date_start'     => ($timer_exist) ? $query->row['date_start'] : '',
    'date_end'       => ($timer_exist) ? $query->row['date_end'] : '',
    'datetime_end'   => ($hours_days && !is_null($query->row['date_end'])) ? $this->model_extension_module_timer->getFullDateTime($query->row['date_end'], $query->row['weekdays'], $query->row['hours']) : '',
    /* Bulk Specials Editor */
    
				'viewed'           => $query->row['viewed']
			);
		} else {
			return false;
		}
	}


			public function getOCTProductPrice($product_id, $quantity) {
				$query = $this->db->query("
					SELECT
						p.price,
						p.tax_class_id,
						(
							SELECT
								price
							FROM
								" . DB_PREFIX . "product_discount pd2
							WHERE
								pd2.product_id = p.product_id
								AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
								AND pd2.quantity <= '" . (int)$quantity . "'
								AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW())
								AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW()))
							ORDER BY
								pd2.quantity DESC,
								pd2.priority ASC,
								pd2.price ASC
							LIMIT 1
						) AS discount,
						(
							SELECT
								price
							FROM
								" . DB_PREFIX . "product_special ps
							WHERE
								ps.product_id = p.product_id
								AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
								AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW())
								AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))
							ORDER BY
								ps.priority ASC,
								ps.price ASC
							LIMIT 1
						) AS special
					FROM
						" . DB_PREFIX . "product p
					LEFT JOIN
						" . DB_PREFIX . "product_to_store p2s
					ON
						(p.product_id = p2s.product_id)
					WHERE
						p.product_id = '" . (int)$product_id . "'
						AND p.status = '1'
						AND p.date_available <= NOW()
						AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
					LIMIT 1
				");

				return $query->row;
			}
			
	public function getProducts($data = array()) {
		$sql = "SELECT p.product_id, 
				(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, 
				(SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, 
				(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special,
				(SELECT  
SUM(op.quantity) AS total FROM oc_order_product op 
LEFT JOIN `oc_order` o ON (op.order_id = o.order_id) 
LEFT JOIN oc_product_to_store p2s ON (p.product_id = p2s.product_id) 
WHERE op.product_id = p.product_id AND o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW()
GROUP BY op.product_id ORDER BY total DESC) as bestseller";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
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
      
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if ( !empty( $data['filter_category_id'] ) ) {
			if ( !empty( $data['filter_sub_category'] ) ) {
				if ( isset( $data['filter_category_id2'] ) )
					$sql .= " AND cp.path_id IN ('" . (int) $data['filter_category_id'] . "', '".(int)$data['filter_category_id2']."')";
				else
					$sql .= " AND cp.path_id = '" . (int) $data['filter_category_id'] . "'";
			} else {
				if (isset($data['filter_category_id2']))
					$sql .= " AND p2c.category_id IN ('" . (int) $data['filter_category_id'] . "', '" . (int) $data['filter_category_id2'] . "')";
				else
					$sql .= " AND p2c.category_id = '" . (int) $data['filter_category_id'] . "'";
			}

			if ( !empty( $data['filter_filter'] ) ) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int) $filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.warranty) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}


		if (!empty($data['only_stock'])) {
			$sql .= " AND p.quantity > 0 ";
		}
		if (!empty($data['only_special'])) {
			$sql .= " AND EXISTS (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) ";
		}
		

    // OCFilter start
    if (!empty($ocfilter_product_sql) && $ocfilter_product_sql->where) {
    	$sql .= $ocfilter_product_sql->where;
    }
    // OCFilter end
      
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		$sql .= " GROUP BY p.product_id";
		/* fixed darkedhart */
		if (isset($data['filter_category_id2'])){
			if (!empty($data['filter_sub_category']))
				$sql .= " HAVING COUNT(DISTINCT cp.path_id) = 2";
			else
				$sql .= " HAVING COUNT(DISTINCT p2c.category_id) = 2";
		}
		/* / fixed darkedhart */
		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
 
				'p.viewed',
			
			'p.price',
			'rating',
			'special',
			'bestseller',
			'p.sort_order',
			'p.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				
			$sql .= " ORDER BY ";

			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_no_quantity_last')) {
				$sql .= "p.quantity > 0 DESC, ";
			}

			$sql .= "LCASE(" . $data['sort'] . ")";
			
			} elseif ($data['sort'] == 'p.price') {
				
			$sql .= " ORDER BY ";

			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_no_quantity_last')) {
				$sql .= "p.quantity > 0 DESC,";
			}

			$sql .= " (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			
			} elseif ($data['sort'] == 'special') {
				$sql .= " ORDER BY special";
			} elseif ($data['sort'] == 'bestseller') {
				$sql .= " ORDER BY bestseller";		
			} else {
				
			$sql .= " ORDER BY ";

			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_no_quantity_last')) {
				$sql .= "p.quantity > 0 DESC, ";
			}

			$sql .= $data['sort'];
			
			}
		} else {
			
			$sql .= " ORDER BY ";

			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_no_quantity_last')) {
				$sql .= "p.quantity > 0 DESC, ";
			}

			$sql .= "p.sort_order";
			
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
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

		$product_data = array();
		
		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}

		return $product_data;
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
	
	public function getProductSpecials($data = array()) {
		$sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps";
		
		// Department start
			if ( !empty( $data['filter_category_id'] ) ) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p2c.product_id = ps.product_id)";
			}
		// Department end
		
		/* Фильтр по ID производителя */
		
		$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN "  .  DB_PREFIX .  "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN "  .  DB_PREFIX  .  "product_description_seo  pds  ON (p.product_id  =  pds.product_id AND pds.language_id  =  '"  .  (int)$this->config->get('config_language_id')  .  "')  LEFT  JOIN  " . DB_PREFIX  .  "product_to_store p2s  ON  (p.product_id = p2s.product_id) WHERE  p.status  =  '1'  AND  p.date_available <= NOW() AND  p2s.store_id =  '" .  (int)$this->config->get('config_store_id')  .  "'  AND  ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))";
		// OCFilter start
		if ( !empty( $data['filter_ocfilter'] ) ) {
			$this->load->model('extension/module/ocfilter');
			$ocfilter_product_sql = $this->model_extension_module_ocfilter->getSearchSQL($data['filter_ocfilter']);
		
		} else {
			$ocfilter_product_sql = false;
		}
		
		if ( $ocfilter_product_sql && $ocfilter_product_sql->join ) {
			$sql .= $ocfilter_product_sql->join;
		}
		// OCFilter end
		
		if ( !empty( $data['filter_ocfilter'] ) ) {
			$filter_array = $this->decodeParamsFromString( $data['filter_ocfilter'] );
			if (!empty($filter_array['m']) && is_array($filter_array['m'])) {
				$manufacturer_ids = array_map('intval', $filter_array['m']);
				$sql .= " AND p.manufacturer_id IN (" . implode(',', $manufacturer_ids) . ")";

			}
		}
		/* / Фильтр по ID производителя */
		// OCFilter start
		if (!empty( $ocfilter_product_sql ) && $ocfilter_product_sql->where) {
			$sql .= $ocfilter_product_sql->where;
		}
		// OCFilter end
		// Department start
			if (!empty( $data['filter_category_id'] )) {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}
		// Department end
		
		$sort_data = array(
			'pd.name',
			'p.model',
			'ps.price',
			'rating',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				
			$sql .= " ORDER BY ";

			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_no_quantity_last')) {
				$sql .= "p.quantity > 0 DESC, ";
			}

			$sql .= "LCASE(" . $data['sort'] . ")";
			
			} else {
				
			$sql .= " ORDER BY ";

			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_no_quantity_last')) {
				$sql .= "p.quantity > 0 DESC, ";
			}

			$sql .= $data['sort'];
			
			}
		} else {
			
			$sql .= " ORDER BY ";

			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_no_quantity_last')) {
				$sql .= "p.quantity > 0 DESC, ";
			}

			$sql .= "p.sort_order";
			
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
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

		$product_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}

		return $product_data;
	}

	public function getLatestProducts($limit) {
		$product_data = $this->cache->get('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

		if (!$product_data) {
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.date_added DESC LIMIT " . (int)$limit);

			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
		}

		return $product_data;
	}

	public function getPopularProducts($limit) {
		$product_data = $this->cache->get('product.popular.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);
	
		if (!$product_data) {
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.viewed DESC, p.date_added DESC LIMIT " . (int)$limit);
	
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}
			
			$this->cache->set('product.popular.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
		}
		
		return $product_data;
	}


			public function getOCTBestSellerProducts($product_id) {
				$query = $this->db->query("SELECT SUM(op.quantity) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.product_id = '". (int)$product_id ."' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id')."'");
		
				return $query->row['total'];
			}
			
	public function getBestSellerProducts($limit) {
		$product_data = $this->cache->get('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

		if (!$product_data) {
			$product_data = array();

			$query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);

			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
		}

		return $product_data;
	}


	        public function getOctProductAttributes($product_id, $limit = 5) {
		        $product_attribute_data = [];
		        
				$product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name LIMIT " . (int)$limit);
		
				foreach ($product_attribute_query->rows as $product_attribute) {
					$product_attribute_data[] = [
						'attribute_id' => $product_attribute['attribute_id'],
						'name'         => $product_attribute['name'],
						'text'         => $product_attribute['text']
					];
				}
		
				return $product_attribute_data;
			}
			
	public function getProductAttributes($product_id) {
		$product_attribute_group_data = array();

		$product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

		foreach ($product_attribute_group_query->rows as $product_attribute_group) {
			$product_attribute_data = array();

			$product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

			foreach ($product_attribute_query->rows as $product_attribute) {
				$product_attribute_data[] = array(
					'attribute_id' => $product_attribute['attribute_id'],
					'name'         => $product_attribute['name'],
					'text'         => $product_attribute['text']
				);
			}

			$product_attribute_group_data[] = array(
				'attribute_group_id' => $product_attribute_group['attribute_group_id'],
				'name'               => $product_attribute_group['name'],
				'attribute'          => $product_attribute_data
			);
		}

		return $product_attribute_group_data;
	}

	public function getProductOptions($product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'name'                    => $product_option_value['name'],
					'image'                   => $product_option_value['image'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix']
				);
			}

			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			);
		}

		return $product_option_data;
	}

	public function getProductDiscounts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC");

		return $query->rows;
	}

	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getProductRelated($product_id) {
		$product_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$product_data[$result['related_id']] = $this->getProduct($result['related_id']);
		}

		return $product_data;
	}

	public function getProductLayoutId($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getCategories($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	public function getTotalProducts($data = array()) {
		if (isset( $data['filter_category_id2']) )
			$sql  =  "SELECT COUNT(*) AS total FROM (SELECT p2c.product_id ";
		else
			$sql  =  "SELECT COUNT(DISTINCT p.product_id) AS total";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
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
      
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if ( !empty( $data['filter_category_id']) ) {
			if ( !empty( $data['filter_sub_category'] ) ) {
				if ( isset( $data['filter_category_id2'] ) )
					$sql .= " AND cp.path_id IN ('" . (int)$data['filter_category_id'] . "', '".(int)$data['filter_category_id2']."')";
				else
					$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				if ( isset( $data['filter_category_id2'] ) )
					$sql .= " AND cp.path_id IN ('" . (int)$data['filter_category_id'] . "', '".(int)$data['filter_category_id2']."')";
				else
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty( $data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}


		if (!empty($data['only_stock'])) {
			$sql .= " AND p.quantity > 0 ";
		}
		if (!empty($data['only_special'])) {
			$sql .= " AND EXISTS (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) ";
		}
		

    // OCFilter start
    if (!empty($ocfilter_product_sql) && $ocfilter_product_sql->where) {
    	$sql .= $ocfilter_product_sql->where;
    }
    // OCFilter end
      
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}
		/* fixed darkedhart */
		if ( isset( $data['filter_category_id2'] ) ){
			if ( !empty( $data['filter_sub_category'] ) ) 
				$sql  .=  " GROUP BY p.product_id HAVING COUNT(DISTINCT cp.path_id) = 2";
			else
				$sql  .=  " GROUP BY p.product_id HAVING COUNT(DISTINCT p2c.category_id) = 2";
			$sql .= ") AS subquery";
		}
		/* / fixed darkedhart */
		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getProfile($product_id, $recurring_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recurring r JOIN " . DB_PREFIX . "product_recurring pr ON (pr.recurring_id = r.recurring_id AND pr.product_id = '" . (int)$product_id . "') WHERE pr.recurring_id = '" . (int)$recurring_id . "' AND status = '1' AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

		return $query->row;
	}

	public function getProfiles($product_id) {
		$query = $this->db->query("SELECT rd.* FROM " . DB_PREFIX . "product_recurring pr JOIN " . DB_PREFIX . "recurring_description rd ON (rd.language_id = " . (int)$this->config->get('config_language_id') . " AND rd.recurring_id = pr.recurring_id) JOIN " . DB_PREFIX . "recurring r ON r.recurring_id = rd.recurring_id WHERE pr.product_id = " . (int)$product_id . " AND status = '1' AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getTotalProductSpecials() {
		$sql = "SELECT COUNT(DISTINCT ps.product_id) AS total FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))";
		
		// OCFilter start
		if ( !empty( $data['filter_ocfilter']) ) {
			$this->load->model('extension/module/ocfilter');

		  $ocfilter_product_sql = $this->model_extension_module_ocfilter->getSearchSQL($data['filter_ocfilter']);
			} else {
		  $ocfilter_product_sql = false;
		}

		if ( $ocfilter_product_sql && $ocfilter_product_sql->join ) {
			$sql .= $ocfilter_product_sql->join;
		}
		// OCFilter end
	
	
		/* Фильтр по ID производителя */
		$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_description_seo pds ON (p.product_id = pds.product_id AND pds.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))";
		
		  // OCFilter start
			if (!empty($ocfilter_product_sql) && $ocfilter_product_sql->where) {
				$sql .= $ocfilter_product_sql->where;
			}
			// OCFilter end
		if (!empty($data['filter_ocfilter'])) {
			$filter_array = $this->decodeParamsFromString($data['filter_ocfilter']);
			if (!empty($filter_array['m']) && is_array($filter_array['m'])) {
				$manufacturer_ids = array_map('intval', $filter_array['m']);
				$sql .= " AND p.manufacturer_id IN (" . implode(',', $manufacturer_ids) . ")";

			}
		}
		
		/* / Фильтр по ID производителя */
		$query  =  $this->db->query($sql);
		
		if ( isset( $query->row['total'] ) ) {
			return  $query->row['total'];
		} else {
			return  0;
		}
	}
}
