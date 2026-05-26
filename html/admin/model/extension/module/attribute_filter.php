<?php
class ModelExtensionModuleAttributeFilter extends Model {
	
	public function changeAttributeGroup($data) {
		$sql_attribute_id = '';
		if ($data['selected']) {
			foreach ($data['selected'] as $key=>$selected) {
				$data['selected'][$key] = (int)$selected;
			}
			$sql_attribute_id = implode(',',$data['selected']);
		}
		if ((int)$data['attribute_group_id'] && $sql_attribute_id) {
			$sql = "UPDATE " . DB_PREFIX . "attribute SET 
				attribute_group_id = " . (int)$data['attribute_group_id'] . "
				WHERE attribute_id IN (" . $sql_attribute_id . ")";
			$this->db->query($sql);	
		}
	}
	public function getNames($attribute_id) {
		$query = $this->db->query("SELECT name, language_id FROM " . DB_PREFIX . "attribute_description 
			WHERE attribute_id = '" . (int)$attribute_id . "'");
		$results = [];
		if ($query->num_rows) {
			foreach ($query->rows as $row) {
				$results[$row['language_id']] = $row['name'];
			}
		}
		return $results;
			
		return $results->rows;
	}

	public function getTotalValues($attribute_id) {
		$sql = "SELECT language_id, COUNT(pa.text) as total FROM `" . DB_PREFIX . "product_attribute` pa
		WHERE 1 AND attribute_id = " . (int)$attribute_id . "
		GROUP BY language_id";

		$result = $this->db->query($sql);
		$results = [];
		if ($result->num_rows) {
			foreach ($result->rows as $row) {
				$results[$row['language_id']] = $row['total'];
			}
		}
		return $results;
	}

}