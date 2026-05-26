<?php
/**************************************************************/
/*	@copyright	OCTemplates 2015-2019						  */
/*	@support	https://octemplates.net/					  */
/*	@license	LICENSE.txt									  */
/**************************************************************/

class ModelOCTemplatesModuleOctLiveSearch extends Model {
	public function doSearch($key) {
		$sql = "
			SELECT 
				p.product_id, 
				(
					SELECT 
						price 
					FROM " . DB_PREFIX . "product_discount pd2 
					WHERE 
						pd2.product_id = p.product_id 
						AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' 
						AND pd2.quantity = '1' 
						AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) 
						AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) 
						ORDER BY 
							pd2.priority ASC, pd2.price ASC 
						LIMIT 1
				) AS discount, 
				(
					SELECT 
						price 
					FROM " . DB_PREFIX . "product_special ps 
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
		";
						
		$sql .= " FROM " . DB_PREFIX . "product p";
		
		$sql .= " 
			LEFT JOIN " . DB_PREFIX . "product_description pd 
				ON (p.product_id = pd.product_id) 
			LEFT JOIN " . DB_PREFIX . "product_to_store p2s 
				ON (p.product_id = p2s.product_id) 
			WHERE 
				pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
				AND p.status = '1' 
				AND p.date_available <= NOW() 
				AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
		";
		
		if (isset($key) && !empty($key)) {
			$sql .= " AND (";
			
			$implode = [];
			
			$words = explode(' ', trim(preg_replace('/\s+/', ' ', $key)));
			
			foreach ($words as $word) {
				$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
			}
			
			if ($implode) {
				$sql .= " " . implode(" AND ", $implode) . "";
			}
			
			$sql .= " OR LCASE(p.sku) LIKE '%" . $this->db->escape(utf8_strtolower($key)) . "%'";
			$sql .= " OR LCASE(p.model) LIKE '%" . $this->db->escape(utf8_strtolower($key)) . "%'";
			$sql .= ")";
		}
		
		$sql .= " 
			GROUP BY 
				p.product_id 
			ORDER BY 
				p.sort_order ASC, 
				LOWER(pd.name) ASC, 
				LOWER(p.model) ASC 
			LIMIT 15
		";
		
		$query = $this->db->query($sql);
		
		return $query->rows;
	}
}