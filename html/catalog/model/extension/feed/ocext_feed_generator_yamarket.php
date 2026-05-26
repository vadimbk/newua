<?php
class ModelExtensionFeedOcextFeedGeneratorYaMarket extends Model {
    
    public function __construct($registry) {
        
        $this->registry = $registry;
        $this->setSettingVersion();
        
    }
    
    public function setSettingVersion(){
        
        require_once(DIR_SYSTEM . 'library/vendor/ocext/multiyml_setting_version.php');
        $this->registry->set('setting_version',new multiYMLSettingVersion($this->registry,  $this->path_oc_version, $this->language,$this->load,$this->db));
        
    }

    public function getFilterData($key,$filter_data_group_id) {
        
        $sql = "SELECT * FROM `" . DB_PREFIX . "ocext_feed_generator_yamarket_filter_data` WHERE `key` = '".$key."' AND filter_data_group_id = '".$filter_data_group_id."' ";
        $query = $this->db->query($sql);
        $result = array();
        if($query->row){
            $result = json_decode($query->row['filter_data'], true);
        }
        
        return $result;
    }
    
    private function replaceOperator($operator){

        $find = array('&lt;','≤','=','≥','&gt;','≠');

        $replace = array('<','<=','=','>=','>','!=');

        $operator = str_replace($find, $replace, $operator);

        return $operator;

    }
    
    public function getStatusByWhere($product_data_where,$product) {

        $result = FALSE;
            
        if($product_data_where['product_field'] && $product_data_where['operator']){

            $operator = $this->replaceOperator($product_data_where['operator']);

            $product_field = $product_data_where['product_field'];

            $value = trim($product_data_where['value']);

            if($operator && $product_field){

                if($operator=='like' && isset($product[$product_field]) && strstr($product[$product_field], $value)){

                    $result = TRUE;

                }elseif($operator=='not_like' && isset($product[$product_field]) && !strstr($product[$product_field], $value)){

                    $result = TRUE;

                }else{

                    if($operator=='<' && isset($product[$product_field]) && $product[$product_field] < $value){

                        $result = TRUE;

                    }elseif($operator=='<=' && isset($product[$product_field]) && $product[$product_field] <= $value){

                        $result = TRUE;

                    }elseif($operator=='=' && isset($product[$product_field]) && $product[$product_field] == $value){

                        $result = TRUE;

                    }elseif($operator=='>=' && isset($product[$product_field]) && $product[$product_field] >= $value){

                        $result = TRUE;

                    }elseif($operator=='>' && isset($product[$product_field]) && $product[$product_field] > $value){

                        $result = TRUE;

                    }elseif($operator=='!=' && isset($product[$product_field]) && $product[$product_field] != $value){

                        $result = TRUE;

                    }

                }

            }

        }
        
        return $result;

    }
    
    public function resizeImage($file,$w,$h,$d,$HTTP_SERVER) {
            
        $image = $this->setting_version->resizeImage($file,$w,$h,$d,  $HTTP_SERVER);

        return $image;
    }
    
    public function getWhere($product_data) {

        $where = array();
        
        foreach ($product_data as $product_data_where){

            if($product_data_where['product_field'] && $product_data_where['operator']){

                $operator = $this->replaceOperator($product_data_where['operator']);

                $product_field = $product_data_where['product_field'];

                $value = trim($product_data_where['value']);
                
                $sql_operator = $product_data_where['logic'];

                if($operator && $product_field){
                    
                    $sql = '';

                    if($operator=='like_right'){

                        $sql = ' p.'.$product_field.' LIKE  "%'.$this->db->escape($value).'" ';

                    }elseif($operator=='like_left'){

                        $sql = ' p.'.$product_field.' LIKE  "'.$this->db->escape($value).'%" ';

                    }elseif($operator=='like'){

                        $sql = ' p.'.$product_field.' LIKE  "%'.$this->db->escape($value).'%" ';

                    }elseif($operator=='not_like_right'){

                        $sql = ' p.'.$product_field.' NOT LIKE  "%'.$this->db->escape($value).'" ';

                    }elseif($operator=='not_like_left'){

                        $sql = ' p.'.$product_field.' NOT LIKE  "'.$this->db->escape($value).'%" ';

                    }elseif($operator=='not_like'){

                        $sql = ' p.'.$product_field.' NOT LIKE  "%'.$this->db->escape($value).'%" ';

                    }else{

                        $sql = ' p.'.$product_field.' '.$operator.' "'.$this->db->escape($value).'" ';

                    }

                    $where[][$sql_operator] = $sql;
                    
                }
                
            }

        }
        
        $where_result = '';
        
        if($where){
            
            $count = 1;
            
            foreach ($where as $sql_where) {
                
                $sql_operator = key($sql_where);
                
                $sql_where = current($sql_where);
                
                if($count<count($where)){
                    
                    $where_result .= $sql_where.$sql_operator;
                    
                }else{
                    
                    $where_result .= $sql_where;
                    
                }
                
                $count++;
                
            }
            
        }
        
        return $where_result;

    }
    
    
    
    
    
    public function getSettings($setting_type=0,$setting_id=FALSE,$setting_product_id=0) {
        
        $sql = "SELECT * FROM `" .   DB_PREFIX   . 'ocext_feed_generator_yamarket_setting` ';
        
        $where = array();
        
        if($setting_type){
            $where[] = " setting_type = '".$setting_type."' ";
        }
        //$setting_id===0 - иначе придут все настройки, когда вызван новый фид, т.к. на $where[] = " setting_type = '".$setting_type."' "; много результатов
        if($setting_id || $setting_id===0){
            $where[] = " setting_id = ".$setting_id." ";
        }
        
        if($setting_product_id){
            
            $where[] = " setting LIKE '%\"setting_product_id\":\"".$setting_product_id."\"%' "; //"setting_product_id":"0"
            
        }
        
        if($where){
            $sql .= 'WHERE '.implode(' AND ', $where);
        }
        
        $result = $this->db->query($sql);
        
        return $result->rows;
        
    }
    
    public function getMemoryLimit($level=0.3){
        
        $memory_limit = ini_get('memory_limit');
        
        if(strstr($memory_limit, 'M')){
			
			$memory_limit = (int)str_replace('M','',$memory_limit);
            
            $memory_limit *= (1024*1024*$level); 
            
        }elseif(strstr($memory_limit, 'G')){
			
			$memory_limit = (int)str_replace('G','',$memory_limit);
            
            $memory_limit *= (1024*1024*1024*$level); 
            
        }else{
			
            $memory_limit = (int)$memory_limit;
            
            $memory_limit *= $level;
            
        }
        
        return $memory_limit;
        
    }
    
    public function getProductCategories($product_id) {
        
        $mcat = '';
        
        if($this->checkColumnTable('product_to_category', 'main_category')){
            
            $mcat .= " p2c.main_category, ";
            
        }
        
        $result = array('main_category'=>0,'category_ids'=>array());
        
        $categories = $this->db->query("SELECT ".$mcat." p2c.category_id FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "category ctg ON (p2c.category_id = ctg.category_id)  WHERE p2c.product_id = '" . (int)$product_id."' AND ctg.status = '1' ");
        
        if($categories->rows){
            
            foreach ($categories->rows as $category) {
                
                if(isset($category['main_category']) && $category['main_category']){
                    
                    $result['main_category'] = $category['category_id'];
                    
                }else{
                    
                    $result['category_ids'][$category['category_id']] = $category['category_id'];
                    
                }
                
            }
            
        }
        
        return $result;
        
    }
    
    private function checkColumnTable($table,$column) {

        if($this->showTable($table, DB_PREFIX)){
            
            $check = $this->db->query(" SHOW columns FROM `".DB_PREFIX.$table."` WHERE `Field` = '".$column."'  ");
            
            if(!$check->num_rows){
                return FALSE;
            }else{
                return TRUE;
            }
            
        }else{
            
            return FALSE;
            
        }

    }

    
    public function getCategoriesAndProducts($ym_categories,$ym_manufacturers,$filter_data_group_id,$content_language_id,$general_setting) {
        
            $cache_status = 0;
            
            $memory_limit = $this->getMemoryLimit();
            
        if(isset($general_setting['yml_cache_enable']) && $general_setting['yml_cache_enable']){
            
            $cache_status = 1;
            
            $memory_limit = $this->getMemoryLimit((float)$general_setting['yml_cache_level']);
            
        }
        
        $cache_file_name = 'yml_cache_'.$this->request->get['token'];
        
        $add_settings = array();
        
        $p2c = '';
        $ym_attributes = array();
        $all_yml_export_ocext_ym_filter_data_attributes = $this->getFilterData('ocext_feed_generator_yamarket_ym_filter_attributes',$filter_data_group_id,$content_language_id);
        if($all_yml_export_ocext_ym_filter_data_attributes){
            $ym_attributes = $all_yml_export_ocext_ym_filter_data_attributes;
        }
        $ym_options = array();
        $all_yml_export_ocext_ym_filter_data_options = $this->getFilterData('ocext_feed_generator_yamarket_ym_filter_options',$filter_data_group_id,$content_language_id);
        if($all_yml_export_ocext_ym_filter_data_options){
            $ym_options = $all_yml_export_ocext_ym_filter_data_options;
        }
        
        $delivery_option_by_manufacturer = $this->getFilterData('ocext_feed_generator_yamarket_ym_filter_delivery_option_by_manufacturer',$filter_data_group_id,$content_language_id);
	
	$mapping_market_place_categories = $this->getFilterData('ocext_feed_generator_yamarket_ym_filter_mapping_market_place_categories',$filter_data_group_id,$content_language_id);
        
	if($ym_categories){
            $sql_cats = array();
            foreach ($ym_categories as $category_id => $ym_category) {
                $sql_cats[] = " p2c.category_id = '".(int)$category_id."' ";
            }
            if($sql_cats){
                $p2c = ' AND ( '.implode(' OR ', $sql_cats).' ) ';
            }
        }
        /*$mcat = '';
        
        if($this->checkColumnTable('product_to_category', 'main_category')){
            
            $mcat .= " AND p2c.main_category = '1' ";
            
        }
        
        $sql = "SELECT p.*, pd.name, pd.*, m.name AS manufacturer, p2c.category_id, ps.price AS special_price, pds.price AS discount_special_price FROM " . DB_PREFIX . "product p JOIN " . DB_PREFIX . "product_to_category AS p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "category ctg ON (p2c.category_id = ctg.category_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id) AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ps.date_start < NOW() AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) LEFT JOIN " . DB_PREFIX . "product_discount pds ON (p.product_id = pds.product_id)  AND pds.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pds.date_start < NOW() AND (pds.date_end = '0000-00-00' OR pds.date_end > NOW())   WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND pd.language_id = '" . (int)$content_language_id . "' AND p.date_available <= NOW() AND ctg.status = '1' AND p.status = '1' ".$mcat.$p2c." GROUP BY p.product_id";*/
        
        $sql_columns = '';
        
        $columns_where = $this->getFilterData('ocext_feed_generator_yamarket_ym_filter_columns',$filter_data_group_id,$content_language_id);
        
        if(isset($columns_where['product'])){
            
            $sql_columns = $this->getWhere($columns_where['product']);
            
            if($sql_columns){
                
                $sql_columns = " AND ( ".$sql_columns." ) ";
                
            }
            
        }
        
        $sql = "SELECT p.*, pd.name, pd.*, m.name AS manufacturer, p2c.category_id, ps.price AS special_price, pds.price AS discount_special_price FROM " . DB_PREFIX . "product p JOIN " . DB_PREFIX . "product_to_category AS p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id) AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ps.date_start <= NOW() AND ( ps.date_end = '0000-00-00' OR ps.date_end >= NOW() ) AND ps.priority in ( SELECT min(ps.priority) ) LEFT JOIN " . DB_PREFIX . "product_discount pds ON (p.product_id = pds.product_id)  AND pds.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pds.date_start < NOW() AND (pds.date_end = '0000-00-00' OR pds.date_end > NOW())   WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND pd.language_id = '" . (int)$content_language_id . "' AND p.date_available <= NOW() AND p.status = '1' ".$sql_columns.$p2c." GROUP BY p.product_id";
        $count_sql = "SELECT p.status FROM " . DB_PREFIX . "product p JOIN " . DB_PREFIX . "product_to_category AS p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id) AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ps.date_start <= NOW() AND ( ps.date_end = '0000-00-00' OR ps.date_end >= NOW() ) AND ps.priority in ( SELECT min(ps.priority) ) LEFT JOIN " . DB_PREFIX . "product_discount pds ON (p.product_id = pds.product_id)  AND pds.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pds.date_start < NOW() AND (pds.date_end = '0000-00-00' OR pds.date_end > NOW())   WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND pd.language_id = '" . (int)$content_language_id . "' AND p.date_available <= NOW() AND p.status = '1' ".$sql_columns.$p2c." GROUP BY p.product_id";
        if($this->config->get('ocext_feed_generator_yamarket_ym_filter_prioritet')){
            $prioritet = $this->config->get('ocext_feed_generator_yamarket_ym_filter_prioritet');
        }else{
            $prioritet['categories'] = 1;
            $prioritet['manufacturers'] = 2;
        }
        
        $parts_select = array($sql);
        
        if(isset($general_setting['optimization_feed_limit_products'])){
            
            $parts_select = $this->setting_version->getPartsSelect($sql,$count_sql,$general_setting['optimization_feed_limit_products'],$parts_select);
            
        }
        
        $ym_categories_whis_categories = $this->getYmCategoriesFromDb();
        $result['categories'] = array();
        $result['cache'] = array();
        $result['offers'] = array();
	$result['settings'] = array();
	$result['mapping_market_place_categories'] = $this->getMappingMarketPlaceCategories($mapping_market_place_categories);
	
        $products_filtered = array();
        
        foreach ($parts_select as $sql) {
            
            $query = $this->db->query($sql);
            
            $result['log_errors'][] =  'total offers selected to db: '.$query->num_rows;

            if($query->rows){

                foreach ($query->rows as $i_product => $product) {

                    if($ym_categories){
                        foreach ($ym_categories as $category_id => $ym_category) {
                            if($category_id==$product['category_id'] && (!$ym_manufacturers || ($ym_manufacturers && isset($ym_manufacturers[$product['manufacturer_id']])))){
                                $products_filtered[$product['product_id']] = $product;
                                if(isset($ym_manufacturers[$product['manufacturer_id']]['setting_id']) && $ym_manufacturers[$product['manufacturer_id']]['setting_id']){
                                    $products_filtered[$product['product_id']]['manufacturer_setting_id'] = $ym_manufacturers[$product['manufacturer_id']]['setting_id'];
                                }else{
                                    $products_filtered[$product['product_id']]['manufacturer_setting_id'] = 0;
                                }
                                if(isset($ym_categories[$product['category_id']]['setting_id']) && $ym_categories[$product['category_id']]['setting_id']){
                                    $products_filtered[$product['product_id']]['category_setting_id'] = $ym_categories[$product['category_id']]['setting_id'];
                                }else{
                                    $products_filtered[$product['product_id']]['category_setting_id'] = 0;
                                }
                            }
                        }
                    }elseif ($ym_manufacturers) {
                        foreach ($ym_manufacturers as $manufacturer_id => $ym_manufacturer) {
                            if($manufacturer_id==$product['manufacturer_id']  && (!$ym_categories || ($ym_categories && isset($ym_categories[$product['category_id']]))) ){

                                $products_filtered[$product['product_id']] = $product;
                                if(isset($ym_manufacturers[$product['manufacturer_id']]['setting_id']) && $ym_manufacturers[$product['manufacturer_id']]['setting_id']){
                                    $products_filtered[$product['product_id']]['manufacturer_setting_id'] = $ym_manufacturers[$product['manufacturer_id']]['setting_id'];
                                }else{
                                    $products_filtered[$product['product_id']]['manufacturer_setting_id'] = 0;
                                }
                                if(isset($ym_categories[$product['category_id']]['setting_id']) && $ym_categories[$product['category_id']]['setting_id']){
                                    $products_filtered[$product['product_id']]['category_setting_id'] = $ym_categories[$product['category_id']]['setting_id'];
                                }else{
                                    $products_filtered[$product['product_id']]['category_setting_id'] = 0;
                                }

                            }
                        }
                    }

                    unset($query->rows[$i_product]);

                }

                $result['log_errors'][] =  'total offers after cat/manuf filter: '.count($products_filtered);

                if($products_filtered){

                    foreach ($products_filtered as $i_product => $product) {

                        $memory_get_usage = memory_get_usage();

                        $set = array();
                        $setting = array();
                        if($prioritet['manufacturers']<$prioritet['categories']){

                            $set[$product['manufacturer_setting_id']] = $product['manufacturer_setting_id'];

                        }else{

                            $set[$product['category_setting_id']] = $product['category_setting_id'];

                        }

                        if($set){
                            $setting_row = $this->getSettings(0, end($set));
                            if($setting_row){
                                $setting = json_decode($setting_row[0]['setting'],TRUE);
				$setting['setting_id'] = $setting_row[0]['setting_id'];
                            }
                            if(!isset($setting['status']) || !$setting['status']){
                                $setting = array();
                            }
                        }

                        //получаем настройку продукта, если есть и меняем на неё шаблон продукта
                        $product_setting = $this->getSettings('product_setting', FALSE, $product['product_id']);
                        if($product_setting){
                            $set = array();
                            $set[$product_setting[0]['setting_id']] = $product_setting[0]['setting_id'];
                            $setting_update = json_decode($product_setting[0]['setting'],TRUE);

                            //если выключен и нет более верхнего шаблона, то обнуляем шаблон, иначе делаем более верхний
                            if(!$setting_update['status'] && !$setting){
                                $setting = array();
                            }elseif($setting_update['status']){
                                $setting = $setting_update;
                            }
                        }

                        $dis_product_ids = array();

                        if(isset($setting['dis_product_ids']) && $setting['dis_product_ids']){

                            $dis_product_ids_ids = explode(',', $setting['dis_product_ids']);

                            foreach ($dis_product_ids_ids as $dis_product_ids_id) {

                                $dis_product_ids_id = trim((int)$dis_product_ids_id);

                                $dis_product_ids[$dis_product_ids_id] = $dis_product_ids_id;

                            }

                        }

                        $enb_product_ids = array();

                        if(isset($setting['enb_product_ids']) && $setting['enb_product_ids']){

                            $enb_product_ids_ids = explode(',', $setting['enb_product_ids']);

                            foreach ($enb_product_ids_ids as $enb_product_ids_id) {

                                $enb_product_ids_id = trim((int)$enb_product_ids_id);

                                $enb_product_ids[$enb_product_ids_id] = $enb_product_ids_id;

                            }

                        }

                        if(isset($dis_product_ids[$product['product_id']])){

                            $set = array();

                        }

                        if($enb_product_ids && !isset($enb_product_ids[$product['product_id']])){

                            $set = array();

                        }

                        if($set){
                            
                            if(isset($setting['add_ordering_to_category']) && $setting['add_ordering_to_category']){
                                
                                $add_settings['add_ordering_to_category'] = $setting['add_ordering_to_category'];
                                
                            }
                            
                            if(isset($setting['add_url_to_category']) && $setting['add_url_to_category']){
                                
                                $add_settings['add_url_to_category'] = $setting['add_url_to_category'];
                                
                            }
                            
                            $result['offers'][$product['product_id']] = $product;
                            //добавлемя набор параметров по категории, если нет - нулевой шаблон
                            $result['offers'][$product['product_id']]['setting_id'] = $set;
                            $result['offers'][$product['product_id']]['ym_attributes'] = $this->getProductAttributes($product['product_id'], $ym_attributes,$content_language_id);
                            $result['offers'][$product['product_id']]['all_attributes'] = $this->getProductAttributes($product['product_id'],array(),$content_language_id);
                            $result['offers'][$product['product_id']]['ym_options'] = $this->getProductOptions($product['product_id'], $ym_options,$content_language_id);
                            $result['offers'][$product['product_id']]['all_options'] = $this->getProductOptions($product['product_id'],array(),$content_language_id);
                            $result['offers'][$product['product_id']]['images'] = $this->getProductImages($product['product_id']);
                            $result['offers'][$product['product_id']]['rec'] = $this->getProductRelated($product['product_id']);
                            $result['offers'][$product['product_id']]['product_to_category'] = $this->getProductCategories($product['product_id']);
                            $result['offers'][$product['product_id']]['option_url_param'] = '';
                            $result['offers'][$product['product_id']]['product_id_by_option_id'] = '';
                            $product_spec_or_disc_prices = $this->getSpecialAndDiscontPrices($product['product_id']);
                            $result['offers'][$product['product_id']]['discount_special_price'] = $product_spec_or_disc_prices['discount_special_price'];
                            $result['offers'][$product['product_id']]['special_price'] = $product_spec_or_disc_prices['special_price'];

                            if($delivery_option_by_manufacturer && isset( $delivery_option_by_manufacturer[$product['manufacturer_id']] )){
                                
                                $result['offers'][$product['product_id']]['delivery_option_by_manufacturer'] = $this->getDeliveryOptionByManufacturer($delivery_option_by_manufacturer[$product['manufacturer_id']],$product);
                                
                            }
			    
			    $set_setting_id = 0;
			    
			    if(isset($setting['setting_id'])){
				
				$set_setting_id = $setting['setting_id'];
				
			    }

                            $result['offers'][$product['product_id']]['settings_id'] = $set_setting_id;

			    $result['settings'][$set_setting_id] = $setting;

                            $market_category = '';

                            if(isset($setting['market_category'])){

                                $market_category = trim($setting['market_category']);

                            }

                            if(!$market_category && isset($ym_categories_whis_categories[$product['category_id']])){
                                $result['offers'][$product['product_id']]['market_category'] = $ym_categories_whis_categories[$product['category_id']];
                            }else{
                                $result['offers'][$product['product_id']]['market_category'] = $market_category;
                            }
                            
                            if(isset($setting['divide_by_options']) && $setting['divide_by_options']['option_value_sku']!=='' && $result['offers'][$product['product_id']]['all_options']){
                                
                                $divide_on_options_selected = $setting['divide_on_options_selected'];
                                
                                $all_option = $result['offers'][$product['product_id']]['all_options'];
                                
                                $option_value_sku = $setting['divide_by_options']['option_value_sku'];
                                
                                $sub_offers = array();
                                
                                foreach ($divide_on_options_selected as $num_sel_option => $option_selected) {
                                    
                                    if($option_selected){
                                        
                                        $option_id_this = 0;
                                            
                                        if(isset($setting['divide_on_options_option_id'.$num_sel_option]['field']['option_id']) && $setting['divide_on_options_option_id'.$num_sel_option]['field']['option_id']){

                                            $option_id_this = $setting['divide_on_options_option_id'.$num_sel_option]['field']['option_id'];

                                        }
                                        
                                        foreach ($all_option as $all_option_row) {
                                            
                                            foreach ($all_option_row['product_option_value'] as $product_option_value) {
                                                
                                                if(isset($product_option_value[$option_value_sku]) && $option_id_this==$all_option_row['option_id']){
                                                    
                                                    $on_option_prefix = 'OPTION'.$num_sel_option;
                                                    
                                                    $option_value_id_this = $product_option_value['option_value_id'];

                                                    if(isset($setting['divide_on_options_prefix'][$num_sel_option]) && $setting['divide_on_options_prefix'][$num_sel_option]!==''){

                                                        $on_option_prefix = $setting['divide_on_options_prefix'][$num_sel_option];

                                                    }
                                                    
                                                    $new_offer_key = $product['product_id'].'__'.$product_option_value[$option_value_sku];
                                                    
                                                    if(!isset($sub_offers[$new_offer_key])){
                                                        
                                                        $sub_offers[$new_offer_key]['product'] = $result['offers'][$product['product_id']];
                                                        
                                                    }
                                                    
                                                    if(!isset($sub_offers[$new_offer_key]['options'][$option_value_id_this])){
                                                        
                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['price'] = '';
                                                        
                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['quantity'] = '';
                                                        
                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['option_url_param'] = '';
                                                        
                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['option_add_model_name'] = '';
                                                        
                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['divide_on_option_add_to_name'] = '';
                                                        
                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['product_id_by_option_id'] = '';
                                                        
                                                    }
                                                    
                                                    
                                                    if(isset($product_option_value['price']) && isset($product_option_value['price_prefix']) && $product_option_value['price_prefix']=='+'){

                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['price'] = $product_option_value['price'];

                                                    }elseif (isset($product_option_value['price']) && isset($product_option_value['price_prefix']) && $product_option_value['price_prefix']=='-') {

                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['price'] = (0-$product_option_value['price']);

                                                        if($sub_offers[$new_offer_key]['options'][$option_value_id_this]['price']<0){

                                                            $sub_offers[$new_offer_key]['options'][$option_value_id_this]['price'] = 0.0;

                                                        }

                                                    }elseif (isset($product_option_value['price']) && isset($product_option_value['price_prefix']) && $product_option_value['price_prefix']=='=') {

                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['price'] = $product_option_value['price'];

                                                    }
                                                    
                                                    $sub_offers[$new_offer_key]['options'][$option_value_id_this]['option_url_param'] = '&option_id[]='.$product_option_value['product_option_value_id'];

                                                    if(isset($setting['divide_on_options_add_to_model'][$num_sel_option]) && $setting['divide_on_options_add_to_model'][$num_sel_option]){

                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['option_add_model_name'] = $product_option_value['name'];

                                                    }
                                                    
                                                    if(isset($setting['divide_on_options_add_to_name'][$num_sel_option]) && $setting['divide_on_options_add_to_name'][$num_sel_option]){

                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['divide_on_option_add_to_name'] = $product_option_value['name'];
                                                        
                                                    }

                                                    $sub_offers[$new_offer_key]['options'][$option_value_id_this]['product_id_by_option_id'] = $on_option_prefix.$product_option_value['product_option_value_id'];

                                                    $sub_offers[$new_offer_key]['product']['group_id'] = $product['product_id'].'-'.$product_option_value[$option_value_sku];

                                                    if(isset($setting['divide_on_options_available_by_option_quantity'][$num_sel_option]) && $setting['divide_on_options_available_by_option_quantity'][$num_sel_option]){

                                                        $sub_offers[$new_offer_key]['options'][$option_value_id_this]['quantity'] = $product_option_value['quantity'];

                                                    }

                                                    foreach ($sub_offers[$new_offer_key]['product']['all_options'] as $key => $all_option_row3) {

                                                        if($all_option_row3['option_id']==$option_id_this && isset($sub_offers[$new_offer_key]['product']['all_options']) && isset($sub_offers[$new_offer_key]['product']['all_options'][$key])){

                                                            unset($sub_offers[$new_offer_key]['product']['all_options'][$key]);

                                                        }
                                                        
                                                        if($all_option_row3['option_id']==$option_id_this && isset($sub_offers[$new_offer_key]['product']['ym_options']) && isset($sub_offers[$new_offer_key]['product']['ym_options'][$key])){

                                                            unset($sub_offers[$new_offer_key]['product']['ym_options'][$key]);

                                                        }

                                                    }
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                }
                                
                                $sub_offers_result = array();
                                
                                foreach ($sub_offers as $new_offer_key => $sub_offer) {
                                    
                                    $sub_offer_result_product = $sub_offer['product'];
                                    
                                    $sub_offers_result[$new_offer_key] = $sub_offer_result_product;
                                    
                                    foreach ($sub_offer['options'] as $sub_offer_option) {
                                        
                                        if(!isset($sub_offers_result[$new_offer_key]['option_add_model_name'])){
                                            
                                            $sub_offers_result[$new_offer_key]['option_url_param'] = '';
                                            
                                            $sub_offers_result[$new_offer_key]['option_add_model_name'] = '';
                                            
                                            $sub_offers_result[$new_offer_key]['divide_on_option_add_to_name'] = '';
                                            
                                            $sub_offers_result[$new_offer_key]['product_id_by_option_id'] = $sub_offer_result_product['product_id'];
                                            
                                        }
                                        
                                        $sub_offers_result[$new_offer_key]['price'] += $sub_offer_option['price'];
                                        
                                        $sub_offers_result[$new_offer_key]['quantity'] += $sub_offer_option['quantity'];
                                        
                                        $sub_offers_result[$new_offer_key]['option_url_param'] .= $sub_offer_option['option_url_param'];
                                        
                                        if($sub_offer_option['option_add_model_name']){
                                            
                                            $sub_offers_result[$new_offer_key]['option_add_model_name'] .= ' '.$sub_offer_option['option_add_model_name'];
                                            
                                        }
                                        
                                        if($sub_offer_option['divide_on_option_add_to_name']){
                                            
                                            $sub_offers_result[$new_offer_key]['divide_on_option_add_to_name'] .= ' '.$sub_offer_option['divide_on_option_add_to_name'];
                                            
                                        }
                                        
                                        $sub_offers_result[$new_offer_key]['product_id_by_option_id'] .= '_'.$sub_offer_option['product_id_by_option_id'];
                                        
                                    }
                                    
                                }
                                
                                if($sub_offers_result){
                                    
                                    unset($result['offers'][$product['product_id']]);
                                    
                                    $result['offers'] = array_merge($result['offers'],$sub_offers_result);
                                    
                                }
                                
                            }
                            

                            if(isset($setting['divide_on_option']) && $setting['divide_on_option'] && isset($result['offers'][$product['product_id']]['all_options']) && $result['offers'][$product['product_id']]['all_options']){

                                $option_id = 0;
                                if(isset($setting['divide_on_option_option_id']['field']['status']) && $setting['divide_on_option_option_id']['field']['status']){
                                    $option_id = $setting['divide_on_option_option_id']['field']['option_id'];
                                }

                                $all_option = $result['offers'][$product['product_id']]['all_options'];

                                if($option_id){
                                    foreach ($all_option as $key => $option) {

                                        if($option['option_id']!=$option_id){

                                            unset($all_option[$key]);

                                        }

                                    }
                                }
                                if($all_option){

                                    $on_option_prefix = 'OPTION';

                                    if(isset($setting['divide_on_option_prefix'])){

                                        $setting['divide_on_option_prefix'] = trim($setting['divide_on_option_prefix']);

                                        if($setting['divide_on_option_prefix']){

                                            $on_option_prefix = $setting['divide_on_option_prefix'];

                                        }

                                    }

                                    foreach ($all_option as $key => $option) {

                                        foreach ($option['product_option_value'] as $product_option_value) {

                                            $option_value_id_this = $product_option_value['option_value_id'];

                                            $result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']] = $result['offers'][$product['product_id']];

                                            if(isset($product_option_value['price']) && isset($product_option_value['price_prefix']) && $product_option_value['price_prefix']=='+'){

                                                $result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['price'] += $product_option_value['price'];

                                            }elseif (isset($product_option_value['price']) && isset($product_option_value['price_prefix']) && $product_option_value['price_prefix']=='-') {

                                                $result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['price'] -= $product_option_value['price'];

                                                if($result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['price']<0){

                                                    $result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['price'] = 0.0;

                                                }

                                            }elseif (isset($product_option_value['price']) && isset($product_option_value['price_prefix']) && $product_option_value['price_prefix']=='=') {

                                                $result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['price'] = $product_option_value['price'];

                                            }

                                            $result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['option_url_param'] = '&option_id='.$product_option_value['product_option_value_id'];

                                            $result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['option_add_model_name'] = $product_option_value['name'];

                                            $result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['product_id_by_option_id'] = $product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id'];

                                            $result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['group_id'] = $product['product_id'];

                                            if(isset($setting['divide_on_option_available_by_option_quantity']) && $setting['divide_on_option_available_by_option_quantity']){

                                                $result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['quantity'] = $product_option_value['quantity'];

                                            }

                                            foreach ($result['offers'][$product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id']]['all_options'] as $key => $all_option_row) {

                                                if($all_option_row['option_id']==$option_id){
                                                    
                                                    if(isset($result['offers'][ $product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id'] ]['all_options'][$key])){
                                                        
                                                        foreach ($result['offers'][ $product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id'] ]['all_options'][$key]['product_option_value'] as $key2 => $all_option_row2) {

                                                            if($all_option_row2['option_value_id']!=$option_value_id_this){

                                                                unset($result['offers'][ $product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id'] ]['all_options'][$key]['product_option_value'][$key2]);

                                                            }
                                                        }
                                                        
                                                    }
                                                    
                                                    if(isset($result['offers'][ $product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id'] ]['ym_options'][$key])){
                                                        
                                                        foreach ($result['offers'][ $product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id'] ]['ym_options'][$key]['product_option_value'] as $key2 => $all_option_row2) {
                                                            
                                                            if($all_option_row2['option_value_id']!=$option_value_id_this){

                                                                unset($result['offers'][ $product['product_id'].$on_option_prefix.$product_option_value['product_option_value_id'] ]['ym_options'][$key]['product_option_value'][$key2]);

                                                            }
                                                        }
                                                        
                                                    }

                                                }

                                            }

                                        }

                                    }

                                    unset($result['offers'][$product['product_id']]);

                                }

                            }

                        }

                        unset($products_filtered[$i_product]);

                        if($memory_get_usage >= $memory_limit && $cache_status){

                            $this->writeCache($cache_file_name.'_'.count($result['cache']).'.txt', $result['offers']);

                            $result['cache'][$cache_file_name.'_'.count($result['cache']).'.txt'] = $cache_file_name.'_'.count($result['cache']).'.txt';

                            $result['offers'] = array();

                        }

                    }

                }

            }
        
            if($cache_status){

                $this->writeCache($cache_file_name.'_'.count($result['cache']).'.txt', $result['offers']);

                $result['cache'][$cache_file_name.'_'.count($result['cache']).'.txt'] = $cache_file_name.'_'.count($result['cache']).'.txt';

                $result['offers'] = array();

            }
            
        }
        
        //Если товары есть, соберем данные про категории
        if($result['offers'] || $result['cache']){
            $sql = "SELECT cd.name, c.sort_order, c.category_id, c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$content_language_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' AND c.sort_order <> '-1'";
            $query = $this->db->query($sql);
            $result['categories'] = $query->rows;
        }
        
        $result['add_settings'] = $add_settings;
        
        return $result;
    }
    
    public function getMappingMarketPlaceCategories($mapping_market_place_categories) {
	
	$result = array();
	
	$category = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category WHERE category_id = ( SELECT max(category_id) FROM " . DB_PREFIX . "category ) ");
	
	$category_id_start = 0;
	
	if($category->row){
	    
	    $category_id_start = $category->row['category_id']+1;
	    
	}
	
	$new_category_ids = array();
	
	if($mapping_market_place_categories){
	    
	    foreach ($mapping_market_place_categories as $site_category_id => $mapping_market_place_category) {
		
		$mapping_market_place_category = html_entity_decode($mapping_market_place_category);
		
		$mapping_market_place_category = $this->cleanExplode('|', $mapping_market_place_category);
		
		foreach ($mapping_market_place_category as $mapping_market_place_category_path) {
		    
		    $mapping_market_place_category_path = $this->cleanExplode('>',$mapping_market_place_category_path);
		    
		    $parent_id = 0;
		    
		    foreach ($mapping_market_place_category_path as $level => $category_name) {
			
			$category_md5 = md5($parent_id.'-'.$this->transliterate(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',  mb_strtoupper($category_name) ))); 
			
			if(!isset($new_category_ids[$category_md5])){
			    
			    $category_id = $category_id_start;
			    
			    $new_category_ids[$category_md5] = $category_id;
			    
			    $category_id_start++;
			    
			}else{
			    
			    $category_id = $new_category_ids[$category_md5];
			    
			}
			
			if($category_id && $category_name!==''){
			    
			    $result[$category_id] = array(
				'category_id'=>$category_id,
				'parent_id'=>$parent_id,
				'name'=>$category_name,
				'sort_order'=>0,
				'site_category_id'=>$site_category_id
			    );
			    
			    $parent_id = $category_id;
			    
			}
			
		    }
		    
		}
		
	    }
	    
	}
	
	return $result;
	
    }
    
    public function transliterate($textcyr) {
	$cyr = array(
	'ж',  'ч',  'щ',   'ш',  'ю',  'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я',
	'Ж',  'Ч',  'Щ',   'Ш',  'Ю',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я');
	$lat = array(
	'zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q',
	'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q');
	return mb_strtoupper(str_replace($cyr, $lat, $textcyr));
    }
    
    public function cleanExplode($delimiter,$string) {
	
	$explode = explode($delimiter, $string);
	
	foreach ($explode as $key => $value) {
	    
	    $value = trim($value);
	    
	    if($value===''){
		
		unset($explode[$key]);
		
	    }else{
		
		$explode[$key] = $value;
		
	    }
	    
	}
	
	if(!$explode){
	    
	    $explode = array();
	    
	}
	
	return $explode;
	
    }
    
    public function getPromoGift($promos_id=NULL) {
        
        $sql = "SELECT * FROM `" .   DB_PREFIX   . 'ocext_feed_generator_yamarket_promo_gift` ';
        
        $where = array();
	
	$where[] = " status = 1 ";
	
	if(!is_null($promos_id)){
	    
	    $where[] = " promos_id = ".$promos_id." ";
	    
	}
        
        if($where){
            $sql .= 'WHERE '.implode(' AND ', $where);
        }
        
        $result = $this->db->query($sql);
	
	if($result->row){
	    
	    $result = json_decode($result->row['promos'],TRUE);
	    
	}else{
	    
	    $result = array();
	    
	}
        
        return $result;
        
    }
    
    public function getDeliveryOptionByManufacturer($delivery_option_by_manufacturer,$product) {
        
        $weight = (float)$product['weight'];
        
        $result = array();
        
        foreach ($delivery_option_by_manufacturer as $rule) {
            
            if($rule['w_from'] !== '' && $weight > $rule['w_from'] && ($rule['w_to']==='' ||  $weight <=  $rule['w_to']) ){
                
                $result = array(
                    'cost' => $rule['cost'],
                    'days' => $rule['days'],
                );
                
            }
            
        }
        
        return $result;
        
    }
    
    public function writeCache($filename,$array,$string='') {
        
        $handle = fopen(DIR_CACHE. $filename, 'w+');
        
        if(!$handle){
            
            $log_errors = new ControllerFeedOcextFeedGeneratorYaMarket($this->registry);
            
            $log_errors->setLogErrors('cache write error to file: '.DIR_CACHE. $filename);
            
        }
        
        if($string){
            fwrite($handle, $string);
        }else{
            fwrite($handle, json_encode($array));
        }
        
        fclose($handle);
        
    }
    
    public function unlinkAllCache($cache_files) {

        $files = scandir(DIR_CACHE);

        foreach($files as $file_name){

            foreach($cache_files as $cache_file){

                if(strstr($file_name, $cache_file)){

                    unlink(DIR_CACHE.$file_name);

                }

            }

        }

    }
    
    
    public function getYmCategoriesFromDb($data=array('category_id'=>1,'status'=>0)) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "ocext_feed_generator_yamarket_ym_categories` ";
        $where = array();
        if ($data['category_id']==1) {
            $where[] = " category_id != '0' ";
        }
        
        if (!empty($data['ym_category_last_child'])) {
            $where[] = " ym_category_last_child LIKE '" . $this->db->escape($data['ym_category_last_child']) . "%' ";
        }
        
        if ($data['status']!='') {
            $where[] = " status = '" . (int)$data['status'] . "' ";
        }
        
        if($where){
            $where = ' WHERE '.implode(' AND ', $where);
        }else{
            $where = '';
        }
        
        $sql .= $where;
        
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
        
        $result = array();
        if($query->rows){
            foreach ($query->rows as $ym_category) {
                $categories = json_decode($ym_category['category_id'],TRUE);
                if($categories){
                    foreach ($categories as $category_id) {
                        $result[$category_id] = $ym_category['ym_category_path'];
                    }
                }
            }
        }
        
        return $result;
    }
    
    public function getProductAttributes($product_id,$ym_attributes=array(),$content_language_id) {
        
            $attributes_sql = array();
            //Вычеркнуть атрибуты
            if($ym_attributes){
                foreach ($ym_attributes as $attribute) {
                    $attributes_parts = explode('___', $attribute);
                    //0 - группа, 1 - id атрибута
                    $attributes_sql[$attributes_parts[0]][$attributes_parts[1]] = $attributes_parts[1];
                }
                
            }
            $product_attribute_group_data = array();

            $product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$content_language_id . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

            
            foreach ($product_attribute_group_query->rows as $product_attribute_group) {
                    $product_attribute_data = array();

                    $product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$content_language_id . "' AND pa.language_id = '" . (int)$content_language_id . "' ORDER BY a.sort_order, ad.name");

                    foreach ($product_attribute_query->rows as $product_attribute) {
                        if(!isset($attributes_sql[$product_attribute_group['attribute_group_id']][$product_attribute['attribute_id']])){
                            
                            /*
                             * 
                            $product_attribute_data[] = array(
                                'attribute_id' => $product_attribute['attribute_id'],
                                'name'         => $product_attribute['name'],
                                'text'         => $product_attribute['text']
                            );
                             * 
                             */
                            
                            $unit = $this->getUnit($product_attribute['name']);
						
                            if($unit!==''){
                                    $product_attribute['name'] = trim(str_replace(array(', ('.$unit.')',',('.$unit.')','('.$unit.')',), '', $product_attribute['name']));
                                    $product_attribute_data[] = array(
                                            'attribute_id' => $product_attribute['attribute_id'],
                                            'name'         => $product_attribute['name'],
                                            'text'         => $product_attribute['text'],
                                            'unit'	=> $unit
                                    );

                            }else{

                                    $product_attribute_data[] = array(
                                        'attribute_id' => $product_attribute['attribute_id'],
                                        'name'         => $product_attribute['name'],
                                        'text'         => $product_attribute['text']
                                    );

                            }
                            
                        }
                    }
                    
                    $unit = $this->getUnit($product_attribute_group['name']);
                    
                    if($unit){
                        $product_attribute_group['name'] = trim(str_replace(array(', ('.$unit.')',',('.$unit.')','('.$unit.')',), '', $product_attribute_group['name']));
                    }

                    $product_attribute_group_data[] = array(
                            'attribute_group_id' => $product_attribute_group['attribute_group_id'],
                            'name'               => $product_attribute_group['name'],
                            'attribute'          => $product_attribute_data,
                            'unit'              => $unit
                    );
            }

            return $product_attribute_group_data;
    }
    
    public function getProductOptions($product_id,$ym_options=array(),$content_language_id) {
        
        
        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$content_language_id . "' ORDER BY o.sort_order");

        foreach ($product_option_query->rows as $product_option) {
                $product_option_value_data = array();

                $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$content_language_id . "' ORDER BY ov.sort_order");

                foreach ($product_option_value_query->rows as $product_option_value) {
                    
                    $product_option_value_data_this = array();
                    
                    foreach ($product_option_value as $product_option_value_name => $product_option_value_value) {
                        $product_option_value_data_this[$product_option_value_name] = $product_option_value_value;
                    }
                    
                    $product_option_value_data[] = $product_option_value_data_this;
                    
                    /*
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
                        */
                }
                
                $unit = $this->getUnit($product_option['name']); 
                    
                if($unit){
                    $product_option['name'] = trim(str_replace(array(', ('.$unit.')',',('.$unit.')','('.$unit.')',), '', $product_option['name']));
                }
                
                if(!isset($ym_options[$product_option['option_id']])){
                    $product_option_data[] = array(
                        'product_option_id'    => $product_option['product_option_id'],
                        'product_option_value' => $product_option_value_data,
                        'option_id'            => $product_option['option_id'],
                        'name'                 => $product_option['name'],
                        'type'                 => $product_option['type'],
                        'value'                => $product_option['value'],
                        'required'             => $product_option['required'],
                        'unit' => $unit
                    );
                }
        }
        return $product_option_data;
}
    
public function getProductImages($product_id) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "product_image` WHERE product_id = '".$product_id."' AND image != 'no-image.jpg' AND image != 'no-image.png' AND image != 'no_image.png' AND image != 'no_image.jpg' ORDER BY sort_order ASC ";
        $query = $this->db->query($sql);
        return $query->rows;
    }    
    
public function getProductRelated($product_id) {
            $product_related_data = array();

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");

            foreach ($query->rows as $result) {
                    $product_related_data[] = $result['related_id'];
            }

            return $product_related_data;
    }

private function getUnit($string){
        $units_parts = explode(' (', $string);
        $unit = '';
        if($units_parts && is_array($units_parts)){
            foreach ($units_parts as $units_part) {
                $parts = explode(')', $units_part);
                if($parts && count($parts)>1){
                    $unit = $parts[0];
                }
            }
        }
        return $unit;
    }

    public function getCategories() {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c  LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE  c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' AND c.sort_order <> '-1'");
            $categories = array();
            if($query->rows){
                foreach ($query->rows as $category){
                    $categories[ $category['cvategory_id'] ] = $category['cvategory_id'];
                }
            }
            return $categories;
    }
    
    public function getManufacturers() {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer m  LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE  m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND m.status = '1' AND m.sort_order <> '-1'");
            $manufacturers = array();
            if($query->rows){
                foreach ($query->rows as $manufacturer){
                    $manufacturers[ $manufacturer['manufacturer_id'] ] = $manufacturer['manufacturer_id'];
                }
            }
            return $manufacturers;
    }
    
    
    
    
    
    
    
    public function showTable($table='ocext_all_yml_export_filter_data') {
        
        $check = $query = $this->db->query('SHOW TABLES from `'.DB_DATABASE.'` like "'.DB_PREFIX.$table.'" ');
        if(!$check->num_rows){
            return FALSE;
        }else{
            return TRUE;
        }
        
    }
    
    
    
    
    
    public function getSpecialAndDiscontPrices($product_id) {
        
        $result = array('special_price'=>0,'discount_special_price'=>0);
        
        $sql = "SELECT * FROM `".DB_PREFIX."product_special` WHERE customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND date_start <= NOW() AND ( date_end = '0000-00-00' OR date_end >= NOW() ) AND product_id=".$product_id;
        
        $special_prices = $this->db->query($sql." ORDER BY priority ASC ");
        
        if($special_prices->row){
            $result['special_price'] = $special_prices->row['price'];
        }
        
        $sql = "SELECT * FROM `".DB_PREFIX."product_discount` WHERE customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND date_start <= NOW() AND ( date_end = '0000-00-00' OR date_end >= NOW() ) AND product_id=".$product_id;
        
        $discount_prices = $this->db->query($sql." AND quantity=1 ORDER BY priority ASC ");
        
        if($discount_prices->row){
            $result['discount_special_price'] = $discount_prices->row['price'];
        }
        
        return $result;
        
    }
    
    
	
}
?>
