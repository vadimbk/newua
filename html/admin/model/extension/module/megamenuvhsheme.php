<?php
class ModelExtensionModuleMegamenuvhsheme extends Model {
	public function createsItem($mm_sheme_id, $data = array()) {
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "megamenuvh_sheme WHERE menu_type = 'category' AND mm_sheme_id='". (int)$mm_sheme_id ."'");
		foreach($data as $result){
			$category_setting = '';
			if($result['menu_type'] == 'category'){
				$category_setting = json_encode($result['category_setting'], true);
			}
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "megamenuvh_sheme SET 
			mm_sheme_id = '" . (int)$mm_sheme_id . "',
			namemenu = '" . $this->db->escape(json_encode($result['namemenu'], true)) . "',
			link = '" . $this->db->escape(json_encode($result['link'], true)) . "',
			menu_type = '" . $this->db->escape($result['menu_type']) . "',
			status = '" . (int)$result['status'] . "',
			sticker_parent = '" . $this->db->escape(json_encode($result['sticker_parent'], true)) . "',
			sticker_parent_bg = '" . $this->db->escape($result['sticker_parent_bg']) . "',
			spctext = '" . $this->db->escape($result['spctext']) . "',
			sort_menu = '" . (int)$result['sort_menu'] . "',
			image = '" .  $this->db->escape($result['image']) . "',
			image_hover = '" .  $this->db->escape($result['image_hover']) . "',
			informations_list = '" .  $this->db->escape($result['informations_list']) . "',
			manufacturers_setting = '" .  $this->db->escape($result['manufacturers_setting']) . "',
			products_setting = '" .  $this->db->escape($result['products_setting']) . "',
			link_setting = '" .  (int)$result['link_setting'] . "',
			category_setting = '" .  $this->db->escape($category_setting) . "',
			html_setting = '" .  $this->db->escape($result['html_setting']) . "',
			freelinks_setting = '" .  $this->db->escape($result['freelinks_setting']) . "',
			use_add_html = '" .  (int)$result['use_add_html'] . "',
			add_html = '" .  $this->db->escape($result['add_html']) . "'
			");
		}
		$this->cache->delete('mmsheme');
		
	}
	public function addItem($mm_sheme_id,$data = array()) {
		
		$informations_list = '';
		if($data['menu_type'] == 'information'){
			$informations_list = json_encode($data['informations_list'], true);
		}
		$manufacturers_setting = '';
		if($data['menu_type'] == 'manufacturer'){
			$manufacturers_setting = json_encode($data['manufacturers_setting'], true);
		}
		$products_setting = '';
		if($data['menu_type'] == 'product'){
			$products_setting = json_encode($data['product'], true);
		}
		
		$category_setting = '';
		if($data['menu_type'] == 'category'){
			$category_setting = json_encode($data['category_setting'], true);
		}
		$html_block = '';
		if($data['menu_type'] == 'html'){
			$html_block = json_encode($data['html_block'], true);
		}
		$freelinks_setting = '';
		if($data['menu_type'] == 'freelink'){
			$freelinks_setting = json_encode($data['sfl'], true);
		}
		$add_html = '';
		$add_html = json_encode($data['add_html'], true);
		$this->db->query("INSERT INTO " . DB_PREFIX . "megamenuvh_sheme SET 
			mm_sheme_id = '" . (int)$mm_sheme_id . "',
			namemenu = '" . $this->db->escape(json_encode($data['namemenu'], true)) . "',
			link = '" . $this->db->escape(json_encode($data['link'], true)) . "',
			menu_type = '" . $this->db->escape($data['menu_type']) . "',
			status = '" . (int)$data['status'] . "',
			sticker_parent = '" . $this->db->escape(json_encode($data['sticker_parent'], true)) . "',
			sticker_parent_bg = '" . $this->db->escape($data['sticker_parent_bg']) . "',
			spctext = '" . $this->db->escape($data['spctext']) . "',
			sort_menu = '" . (int)$data['sort_menu'] . "',
			image = '" .  $this->db->escape($data['image']) . "',
			image_hover = '" .  $this->db->escape($data['image_hover']) . "',
			informations_list = '" .  $this->db->escape($informations_list) . "',
			manufacturers_setting = '" .  $this->db->escape($manufacturers_setting) . "',
			products_setting = '" .  $this->db->escape($products_setting) . "',
			link_setting = '" .  (int)$data['use_target_blank'] . "',
			category_setting = '" .  $this->db->escape($category_setting) . "',
			html_setting = '" .  $this->db->escape($html_block) . "',
			freelinks_setting = '" .  $this->db->escape($freelinks_setting) . "',
			use_add_html = '" .  (int)$data['use_add_html'] . "',
			add_html = '" .  $this->db->escape($add_html) . "'
		");
		$this->cache->delete('mmsheme');
		$megamenu_id = $this->db->getLastId();

		return $megamenu_id;
	}
	
	public function editItem($megamenu_id, $data = array()) {
		$informations_list = '';
		if($data['menu_type'] == 'information'){
			$informations_list = json_encode($data['informations_list'], true);
		}
		$manufacturers_setting = '';
		if($data['menu_type'] == 'manufacturer'){
			$manufacturers_setting = json_encode($data['manufacturers_setting'], true);
		}
		$products_setting = '';
		if($data['menu_type'] == 'product'){
			$products_setting = json_encode($data['product'], true);
		}
		$category_setting = '';
		if($data['menu_type'] == 'category'){
			$category_setting = json_encode($data['category_setting'], true);
		}
		$html_block = '';
		if($data['menu_type'] == 'html'){
			$html_block = json_encode($data['html_block'], true);
		}
		$freelinks_setting = '';
		if($data['menu_type'] == 'freelink'){
			$freelinks_setting = json_encode($data['sfl'], true);
		}
		$add_html = '';
		$add_html = json_encode($data['add_html'], true);
		
		$this->db->query("UPDATE " . DB_PREFIX . "megamenuvh_sheme SET 
			namemenu = '" . $this->db->escape(json_encode($data['namemenu'], true)) . "',
			link = '" . $this->db->escape(json_encode($data['link'], true)) . "',
			menu_type = '" . $this->db->escape($data['menu_type']) . "',
			status = '" . (int)$data['status'] . "',
			sticker_parent = '" . $this->db->escape(json_encode($data['sticker_parent'], true)) . "',
			sticker_parent_bg = '" . $this->db->escape($data['sticker_parent_bg']) . "',
			spctext = '" . $this->db->escape($data['spctext']) . "',
			sort_menu = '" . (int)$data['sort_menu'] . "',
			image = '" .  $this->db->escape($data['image']) . "',
			image_hover = '" .  $this->db->escape($data['image_hover']) . "',
			informations_list = '" .  $this->db->escape($informations_list) . "',
			manufacturers_setting = '" .  $this->db->escape($manufacturers_setting) . "',
			products_setting = '" .  $this->db->escape($products_setting) . "',
			link_setting = '" .  (int)$data['use_target_blank'] . "',
			category_setting = '" .  $this->db->escape($category_setting) . "',
			html_setting = '" .  $this->db->escape($html_block) . "',
			freelinks_setting = '" .  $this->db->escape($freelinks_setting) . "',
			use_add_html = '" .  (int)$data['use_add_html'] . "',
			add_html = '" .  $this->db->escape($add_html) . "'
			WHERE megamenu_id = '" . (int)$megamenu_id . "'	
		");
		$this->cache->delete('mmsheme');
	}
	
	
	public function saveTypeMenu($megamenu_id, $data = array()) {
		
		$informations_list = '';
		if($data['menu_type'] == 'information'){
			$informations_list = json_encode($data['informations_list'], true);
		}
		$manufacturers_setting = '';
		if($data['menu_type'] == 'manufacturer'){
			$manufacturers_setting = json_encode($data['manufacturers_setting'], true);
		}
		$products_setting = '';
		if($data['menu_type'] == 'product'){
			$products_setting = json_encode($data['product'], true);
		}
		$category_setting = '';
		if($data['menu_type'] == 'category'){
			$category_setting = json_encode($data['category_setting'], true);
		}
		$html_block = '';
		if($data['menu_type'] == 'html'){
			$html_block = json_encode($data['html_block'], true);
		}
		$freelinks_setting = '';
		if($data['menu_type'] == 'freelink'){
			$freelinks_setting = json_encode($data['sfl'], true);
		}
		$add_html = '';
		$add_html = json_encode($data['add_html'], true);
		
		$this->db->query("UPDATE " . DB_PREFIX . "megamenuvh_sheme SET 
			menu_type = '" . $this->db->escape($data['menu_type']) . "',
			informations_list = '" .  $this->db->escape($informations_list) . "',
			manufacturers_setting = '" .  $this->db->escape($manufacturers_setting) . "',
			products_setting = '" .  $this->db->escape($products_setting) . "',
			link_setting = '" .  (int)$data['use_target_blank'] . "',
			category_setting = '" .  $this->db->escape($category_setting) . "',
			html_setting = '" .  $this->db->escape($html_block) . "',
			freelinks_setting = '" .  $this->db->escape($freelinks_setting) . "',
			use_add_html = '" .  (int)$data['use_add_html'] . "',
			add_html = '" .  $this->db->escape($add_html) . "'
			WHERE megamenu_id = '" . (int)$megamenu_id . "'	
		");
		$this->cache->delete('mmsheme');

	}
	public function saveLinkMenu($megamenu_id, $data = array()) {
		$this->db->query("UPDATE " . DB_PREFIX . "megamenuvh_sheme SET link = '" . $this->db->escape(json_encode($data['link'], true)) . "' WHERE megamenu_id = '" . (int)$megamenu_id . "'");
		$this->cache->delete('mmsheme');
	}
	public function saveNameItem($megamenu_id, $data = array()) {
		$this->db->query("UPDATE " . DB_PREFIX . "megamenuvh_sheme SET namemenu = '" . $this->db->escape(json_encode($data['namemenu'], true)) . "' WHERE megamenu_id = '" . (int)$megamenu_id . "'");
		$this->cache->delete('mmsheme');
	}
	public function saveStickerMenu($megamenu_id, $data = array()) {
		$this->db->query("UPDATE " . DB_PREFIX . "megamenuvh_sheme SET sticker_parent = '" . $this->db->escape(json_encode($data['sticker_parent'], true)) . "', sticker_parent_bg = '" . $this->db->escape($data['sticker_parent_bg']) . "', spctext = '" . $this->db->escape($data['spctext']) . "' WHERE megamenu_id = '" . (int)$megamenu_id . "'");
		$this->cache->delete('mmsheme');
	}
	
	public function deleteItem($megamenu_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "megamenuvh_sheme WHERE megamenu_id = '" . (int)$megamenu_id . "'");
	}
	public function changeStatus($megamenu_id, $value){
		$this->db->query("UPDATE " . DB_PREFIX . "megamenuvh_sheme SET status = '" . (int)$value . "' WHERE megamenu_id = '" . (int)$megamenu_id . "'");
		$this->cache->delete('mmsheme');
	}
	public function addListMenuName($data){
		$this->db->query("INSERT INTO " . DB_PREFIX . "megamenuvh_sheme_list SET name = '" . $this->db->escape($data['name']) . "', menu_sheme_type = '" . (int)$data['menu_sheme_type'] . "'");
	}
	public function editListMenuName($data){
			$this->db->query("UPDATE " . DB_PREFIX . "megamenuvh_sheme_list SET name = '" . $this->db->escape($data['name']) . "', menu_sheme_type = '" . (int)$data['menu_sheme_type'] . "' WHERE mm_sheme_id = '" . (int)$data['mm_sheme_id'] . "'");
	}
	public function deleteMenu($mm_sheme_id){
		$this->db->query("DELETE FROM " . DB_PREFIX . "megamenuvh_sheme WHERE mm_sheme_id='". (int)$mm_sheme_id ."'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "megamenuvh_sheme_list WHERE mm_sheme_id = '" . (int)$mm_sheme_id . "'");
	}
	public function getListMenuName(){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "megamenuvh_sheme_list");
		return $query->rows;
	}
	public function getMenuNameType($mm_sheme_id){
		$query = $this->db->query("SELECT name,menu_sheme_type FROM " . DB_PREFIX . "megamenuvh_sheme_list WHERE mm_sheme_id = '" . (int)$mm_sheme_id . "'");
		
		return $query->row;
	}
	
	public function getListMenu($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "megamenuvh_sheme";
		$sql .= " WHERE mm_sheme_id = '" . (int)$data['mm_sheme_id'] . "'";
		$sql .= " GROUP BY megamenu_id";
		$sort_data = array(
			'menu_type',
			'status',
			'sort_menu',
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_menu";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		$query = $this->db->query($sql);
			
		return $query->rows;
	}
	public function getItem($megamenu_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "megamenuvh_sheme WHERE megamenu_id = '" . (int)$megamenu_id . "'");
		
		return $query->row;
	}
	public function installDB() {
		$query = $this->db->query("CREATE TABLE IF NOT EXISTS  ". DB_PREFIX ."megamenu_key (`key` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, license_key text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL)");
		$license_key = $this->db->query("SELECT `key` FROM ". DB_PREFIX ."megamenu_key WHERE `key`='local_key' LIMIT 1");
		if ($license_key->num_rows <= 0) { $this->db->query("INSERT INTO ". DB_PREFIX ."megamenu_key (`key`, `license_key`) VALUES('local_key', '');"); }		
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "megamenuvh_sheme` (
			`megamenu_id` int(11) NOT NULL AUTO_INCREMENT,
			`mm_sheme_id` int(11) NOT NULL,
			`namemenu` varchar(255) NOT NULL,
			`link` text NOT NULL,
			`menu_type` varchar(45) NOT NULL,
			`status` tinyint(1) NOT NULL DEFAULT '1',
			`sticker_parent` varchar(255) NOT NULL,
			`sticker_parent_bg` varchar(255) NOT NULL,
			`spctext` varchar(255) NOT NULL,
			`sort_menu` int(3) NOT NULL DEFAULT '0',
			`image` varchar(255) NOT NULL,
			`image_hover` varchar(255) NOT NULL,
			`informations_list` longtext NOT NULL,
			`manufacturers_setting` longtext NOT NULL,
			`products_setting` longtext NOT NULL,
			`link_setting` tinyint(1) NOT NULL,
			`category_setting` longtext NOT NULL,
			`html_setting` longtext NOT NULL,
			`freelinks_setting` longtext NOT NULL,
			`use_add_html` tinyint(1) NOT NULL,
			`add_html` longtext NOT NULL,
			PRIMARY KEY (`megamenu_id`,`mm_sheme_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "megamenuvh_sheme_list` (
			`mm_sheme_id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL,
			`menu_sheme_type` int(1) NOT NULL,
			PRIMARY KEY (`mm_sheme_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");	
	}
	

}