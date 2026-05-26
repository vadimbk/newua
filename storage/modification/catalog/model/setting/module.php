<?php
class ModelSettingModule extends Model {
	public function getModule($module_id) {

		// Code
		if (isset($module_id) && strpos($module_id, 'seolang|') !== false) {
       		if ($this->registry->get('seolanglib')) {
       			$query = new stdClass();
       			$query->row = $this->seolanglib->getModule(str_replace('seolang|', '', $module_id));
       		}
		} else {
		// End of code

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE module_id = '" . (int)$module_id . "'");
		

		// Code
		}
		// End of code

		if ($query->row) {
			return json_decode($query->row['setting'], true);
		} else {
			return array();	
		}
	}		
}