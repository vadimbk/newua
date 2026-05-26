<?php
class ModelCatalogDevosAttributeExt extends Model {
	const TEMPLATE_CATALOG = 'catalog';
	const TEMPLATE_PRODUCT = 'product';
	const TEMPLATE_PRODUCT_TAB = 'product_tab';
	var $settings = array();	
	var $settings_view = array();
	var $template='';
	var $template_full = '{attributes}';
	var $template_type='';
	var $template_vars = array();
	var $language_id = 0;
	var $attributes_id = array();
	var $attributes_settings = array();
	var $attributes_values = array();
	var $products_attributes = array();
	private function ClearData(){
		$this->settings = array();	
		$this->settings_view = array();
		$this->template='';
		$this->template_full = '{attributes}';
		$this->template_type='';
		$this->template_vars = array();
		$this->language_id = 0;
		$this->attributes_id = array();
		$this->attributes_settings = array();
		$this->attributes_values = array();
		$this->products_attributes = array();
	}
	//получение настроек модуля
	public function getSettings(){
		if(empty($this->settings)){
			$this->load->model('setting/setting');
			$this->settings = $this->model_setting_setting->getSetting('dae');
		}
	}
	private function getLanguageID(){
		return (int)$this->config->get('config_language_id');//текущий язык
	}
	//формирование хеша для значения
	private function getHash($value){
		return md5($value.$this->getLanguageID());
	}
	//получение атрибутов товара
	private function getProductsAttributes($products_id = array()){
		$result = array();
		$products_attributes = $this->db->query("
			SELECT pa.product_id, a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa 
			LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) 
			LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id)
			LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) AND ad.language_id = '" . $this->getLanguageID() . "'
			WHERE pa.product_id in (" . implode(',',$products_id) . ")  AND pa.language_id = '" . $this->getLanguageID() . "' AND pa.text != ''
			ORDER BY ag.sort_order, a.sort_order");

		$this->products_attributes = array();
		foreach ($products_attributes->rows as $product_attributes) {
			if(!isset($this->products_attributes[$product_attributes['product_id']]))
				$this->products_attributes[$product_attributes['product_id']] = array();

			$this->attributes_id[$product_attributes['attribute_id']]=1;

			$this->products_attributes[$product_attributes['product_id']][$product_attributes['attribute_id']] = array(
					'attribute_id' => $product_attributes['attribute_id'],
					'name'         => $product_attributes['name'],
					'text'         => $product_attributes['text']
				);			
		}
		//конвертирование массива с ид атрибутов		
		$this->attributes_id = array_keys($this->attributes_id);
	}
	//получение атрибутов, которые нужно выводить в указанной категории
	private function getAttributesViewInCategory($category_id){
		$attributes_id = array();
		if((int)$category_id > 0){	
			$category_attributes = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "dae_view_attribute_in_category WHERE category_id=".(int)$category_id);

			$attributes_id = array();
			foreach ($category_attributes->rows as $row) {
				$attributes_id[] = (int)$row['attribute_id'];
			}	
		}
		return $attributes_id;
	}
	//список атрибутов, которые нужно выводить 
	private function getAttributeForView($place='catalog',$attributes_id=array()){
		$where = array();
		$where[] = "view_".$place." = 1"; //

		if($attributes_id)//чтобы лишнего не тянуть
			$where[] = "attribute_id in (" . implode(',',$attributes_id) . ")";

		$attributes = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "dae_attribute WHERE ".implode(' AND ', $where));	
		$attributes_id = array();
		foreach ($attributes->rows as $row) {
			$attributes_id[] = (int)$row['attribute_id'];
		}	
		return $attributes_id;
	}
	//метод оставит только те атрибуты, которые должны будут выведены
	private function filterAttributes($place='catalog', $category_id = 0){
		//проверим список выводимых атрибутов у категории
		$attributes_id_tmp = array();
		if($category_id)
			$attributes_id_tmp = $this->getAttributesViewInCategory($category_id);

		//если у категории нет атрибутов - смотрим в общей таблице
		if(empty($attributes_id_tmp))
			$attributes_id_tmp = $this->getAttributeForView($place, $this->attributes_id);

		$this->attributes_id = $attributes_id_tmp;
	}



	//получение настроек атрибутов
	private function getSettingsAttributes(){
		$this->attributes_settings = array();
		if($this->attributes_id){
			$sql = "
				SELECT da.*, dad.tooltip as tooltip, dad.short_name as short_name FROM " . DB_PREFIX . "dae_attribute da 
				LEFT JOIN " . DB_PREFIX . "dae_attribute_description dad ON dad.attribute_id = da.attribute_id AND dad.language_id = ".$this->getLanguageID()."
				WHERE da.attribute_id in (".implode(',', $this->attributes_id).")";
			$query = $this->db->query($sql);
			foreach($query->rows as $row){				
				$this->attributes_settings[$row['attribute_id']] = $row;
			}		
		}
		
	}

	//получение предопределенных значений атрибута
	private function getValuesAttributes(){
		$sql = "
			SELECT 
				davd.text as 'text', 
				davd.attribute_id as attribute_id, 
				davd.language_id as language_id, 
				davd.attribute_value_id as attribute_value_id, 
				davd.description as description,
				dav.url as url,
				dav.image as image
				FROM " . DB_PREFIX . "dae_attribute_value_description davd 
				LEFT JOIN " . DB_PREFIX . "dae_attribute_value dav ON dav.attribute_value_id = davd.attribute_value_id 
				WHERE davd.attribute_id in (" . implode(',', $this->attributes_id) . ") AND davd.language_id=".$this->getLanguageID();
		$query = $this->db->query($sql);
		$this->attributes_values = array();
		foreach($query->rows as $row){
			if(!isset($this->attributes_values[$row['attribute_id']]))
				$this->attributes_values[$row['attribute_id']] = array();
			$this->attributes_values[$row['attribute_id']][$this->getHash($row['text'])] = array(
				'id' => $row['attribute_value_id'],					
				'attribute_id' => $row['attribute_id'],
				'image' => $row['image'],
				'url' => $row['url'],
				'text' => $row['text'],
				'description' => $row['description']
			);	
		}
	}
	
	//получение шаблона для категории
	protected function getTemplateForCategory($category_id){
		$sql = "SELECT template FROM " . DB_PREFIX . "dae_category_settings WHERE category_id=".(int)$category_id;
		$query = $this->db->query($sql);
		if(isset($query->rows[0]) && !empty($query->rows[0]['template']))
			return $query->rows[0]['template'];
		else
			return '';
	}
	//выявление переменных замены в шаблоне
	protected function getVarsTemplate(){
		$template_vars = array();

		if(!empty($this->template)){
		
			$pattern = "|{(.+?)}|im";
			//распарсим шаблон на заменяемые элементы		
			$out = array();		
			preg_match_all($pattern, $this->template, $out); 
			if(isset($out[1]) && is_array($out[1])){
				foreach ($out[1] as $value) {
					$template_vars[] = $value;
				}
			}
		}
		$this->template_vars = $template_vars;
	}

	//формирование описания + атрибуты по шаблону
	public function buildTemplate($product_id, $description){
		$build_description = '';
		if(isset($this->products_attributes[$product_id])){
			//список атрибутов со значениями товара
			$product_attributes = $this->products_attributes[$product_id];

			//если задано кол-во выводимых атрибутов - обрежем массив
            if((int)$this->settings['dae_count_view_'.$this->template_type]){
				$product_attributes = array_intersect_key($product_attributes, array_flip($this->attributes_id));
                $product_attributes = array_slice($product_attributes, 0, (int)$this->settings['dae_count_view_'.$this->template_type]);
			}

			//подставим атрибуты в шаблон
			$build_template_attribute = $this->buildTemplateAttributes($product_attributes);
			
			//объединим полученные результаты
			$build_description = html_entity_decode(implode($this->settings['dae_separator_view_'.$this->template_type], $build_template_attribute), ENT_QUOTES, 'UTF-8');			
		}

		//для каталога при необходимости объединим с описанием
			if($this->template_type == 'catalog'){
				$build_description = html_entity_decode(str_replace(array('{attributes}','{description}'),array($build_description, $description), $this->template_full));
			}
		return $build_description;
	}
	//проверит  - множественный атрибут или нет
	private function checkMulti($attribute_id){
		return (isset($this->attributes_settings[$attribute_id]) && $this->attributes_settings[$attribute_id]['type_edit'] == 'multi')?true:false;
	}
	//разбивает множественное значение
	private function explodeMultiValue($text){
		return explode($this->settings['dae_value_separator'], $text);
	}
	private function implodeMultiValue($text){
		return implode($this->settings['dae_value_separator'], $text);
	}
	public function varToTemplate($var){
		return '{'.$var.'}';
	}
	//соберет вывод для всех атрибутов
	public function buildTemplateAttributes($attributes){				
		$result = array();
		
		if($attributes){
			$dae_product_attributes_replace = explode('#',$this->settings['dae_replace_val_att']);//получим массив из списка значений для замены
			$dae_product_attributes_skip_replace = explode('#',$this->settings['dae_skip_replace_val_att']);//получим массив из списка значений для замены(чтобы пропустить атрибут)

			$flag_replace_value_attr = false;
			$flag_skip_replace_value_attr = false;
			//если в шаблоне нет вывода названия атрибута, то возможно будет замена значения на название
			if(!(in_array('a_name',$this->template_vars) || in_array('a_name_tt',$this->template_vars))){
				if(!empty($dae_product_attributes_replace))
					$flag_replace_value_attr = true;

				if(!empty($dae_product_attributes_skip_replace))
					$flag_skip_replace_value_attr = true;
			}

			if(!isset($this->model_tool_image))
				$this->load->model('tool/image');
			
			foreach ($attributes as $attribute) {
				$attribute_id = $attribute['attribute_id'];
				if(!in_array($attribute_id, $this->attributes_id))
					continue;
				//если значение пусто - идем к след атрибуту
				if(empty($attribute['text']))
					continue;
				
				//если множественное значение - разделим на значения
				/*if(!empty($this->attributes_settings[$attribute_id]['type_edit'] == 'multi'))
					$value = explode($this->settings['dae_value_separator'], $attribute['text']);
				else
					$value = $attribute['text'];*/
				$value_replace = '';
				//не для множественного значения
				if(!$this->checkMulti($attribute_id)){
					//пропуск при замене значений на названия
					if(($flag_skip_replace_value_attr)&&(in_array($attribute['text'], $dae_product_attributes_skip_replace))){
						continue;
					}

					//замена значения атрибута на название
					$value_replace = '';
					if(($flag_replace_value_attr)&&(in_array($attribute['text'], $dae_product_attributes_replace))){
						$value_replace = $attribute['name'];
					}
				}

				$tmp_val = array();

				//цикл по параметрам шаблона
				$continue_ = false;
				foreach ($this->template_vars as $template_var) {							
					$tmp_val[$template_var] = '';
					//сформируем название метода для подстановки по конкретной переменной
					$method_template = 'template_' . $template_var;
					if(method_exists($this, $method_template)){								
						$tmp_val[$template_var] = $this->$method_template(array(
							'attribute_id' => $attribute_id,
							'name' => $attribute['name'],
							'text' => $attribute['text'],
							'text_replace' => $value_replace
							), 
							(isset($this->attributes_settings[$attribute_id]))?$this->attributes_settings[$attribute_id]:array(),
							(isset($this->attributes_values[$attribute_id]))?$this->attributes_values[$attribute_id]:array()
						);
						if($tmp_val[$template_var] === false){
							$continue_ = true;
							break;
						}
					}	
				}
				//если замена не получилась, то не будем записывать полностью атрибут
				if($continue_)
					continue;
				
				//формирование строки по шаблону
				if(count($tmp_val)){
					$t = trim(str_replace(array_map(array($this,"varToTemplate"), $this->template_vars), $tmp_val, $this->template));
					if(!empty($t))
						$result[$attribute_id] = $t;
				}	
			}
		}
		return $result;
	}	
	
	//преобразует описание товаров
	private function init($products_id, $params = array()){
		$this->ClearData();
		if(empty($products_id))
			return $products_id;

		//будем использовать настройки вывода в категории
		$this->template_type = (isset($params['template_type']))?$params['template_type']:self::TEMPLATE_CATALOG;

		//получим настройки модуля
		$this->getSettings();

		//проверим, нужно ли выводить в указанном месте
		if(!empty($this->settings['dae_view_'.$this->template_type])){

			//проверим переданный шаблон
			if(!empty($params['template_custom'])){
				$this->template = $params['template_custom'];			
			}
			$category_id = (!empty($params['category_id']))?$params['category_id']:0;
			//проверим наличие индивидуального шаблона для категории
			if(($this->template_type == self::TEMPLATE_CATALOG) && empty($this->template)){
				
				if($category_id){
					$this->template = $this->getTemplateForCategory($category_id);
				}
			}

			//если индивидуального не нашлось - берем общий для каталога
			if(empty($this->template)){
 				$this->template = $this->settings['dae_template_'.$this->template_type];
			}

			//если шаблон задан - идем дальше
			if(!empty($this->template)){
				//подготовим общий шаблон
				if(!empty($this->settings['dae_full_template_'.$this->template_type]))
					$this->template_full = $this->settings['dae_full_template_'.$this->template_type];

				//для полученных ид товаров достанем атрибуты + заполнение модели атрибутами
				$this->getProductsAttributes($products_id);				
				//оставим только те атрибуты, которые должны выводится
				$this->filterAttributes($this->template_type, $category_id);

				if(empty($this->attributes_id))
					return true;

				//загрузим настройки для участвующих атрибутов
				$this->getSettingsAttributes();

				//загрузим значения атрибутов
				$this->getValuesAttributes();

				//подготовим переменные замены для шаблона
				$this->getVarsTemplate();

				return true;				
			}
		}
		//если дошли сюда, то выводить не требуется
		return false;
	}

	//для всех
	public function daeCatalog($products, $params = array()){
		if(empty($products))
			return $products;
		$params['template_type'] = 'catalog';
		//соберем id переданных товаров
		$products_id = array();
		foreach ($products as &$product) {
			if(isset($product['description'])){						
				$products_id[] = $product['product_id'];						
			}
		}
		//инициализируем модель
		if($products_id && $this->init($products_id, $params)){
			//сформируем описание с атрибутами

			foreach ($products as &$product) {
				if(isset($product['description'])){
					$product['description'] = $this->buildTemplate($product['product_id'], $product['description']);
				}
			}
		}
		return $products;
	}
	
	//для одного
	public function daeProduct($product_id, $params = array()){
		//соберем id переданных товаров
		$products_id = array($product_id);					
		$params['template_type'] = 'product';
		//инициализируем модель
		if($products_id && $this->init($products_id, $params)){			
			return $this->buildTemplate($product_id, '');				
		}
		return '';
	}
	//для одного в табе
	public function daeProductTab($product_id, $params = array()){

		//соберем id переданных товаров
		$products_id = array($product_id);
		$product_attributes = (isset($params['attributes']))?$params['attributes']:array();

		//сохраним текущие атрибуты товара 
		$dae_product_attributes = $product_attributes;
		$params['template_type'] = 'product_tab';
		//инициализируем модель
		
		if($product_attributes && $products_id && $this->init($products_id, $params) ){	
			if(!empty($this->products_attributes[$product_id])){
				//получим вывод атрибутов по шаблону		
				$dae_attributes = $this->buildTemplateAttributes($this->products_attributes[$product_id]);				

				foreach($product_attributes as $dae_group_key => $dae_group_value){
					foreach($dae_group_value['attribute'] as $dae_attribue_key => $dae_attribute_value){

						//если для атрибута нет вывода, то удалим его из списка атрибутов, иначе добавим сформированный вывод
						if(!isset($dae_attributes[$dae_attribute_value['attribute_id']]))
							unset($dae_product_attributes[$dae_group_key]['attribute'][$dae_attribue_key]);
						else
							$dae_product_attributes[$dae_group_key]['attribute'][$dae_attribue_key]['dae_view'] = html_entity_decode($dae_attributes[$dae_attribute_value['attribute_id']], ENT_QUOTES, 'UTF-8');			  	
					}
					//если у грппы нет атрибутов для вывода - удалим группу
					if(empty($dae_product_attributes[$dae_group_key]['attribute'])){
						unset($dae_product_attributes[$dae_group_key]);
					}            
				}
			}else{
				$dae_product_attributes = [];
			}
		}

		return $dae_product_attributes;
	}

	//для сравнения - удаление атрибута из показа
	public function daeCompare($product_id, $params = array()){

		//соберем id переданных товаров
		$products_id = array($product_id);
		$attribute_groups = (isset($params['attributes']))?$params['attributes']:array();
    //получим настройки модуля
		$this->getSettings();
    if(empty($this->settings['dae_view_product_tab'])){
      return $attribute_groups;
    }
    
		if(empty($attribute_groups))
			return array();
		$attribute_groups_tmp = $attribute_groups;
		foreach ($attribute_groups as $attribute_group) {
			foreach ($attribute_group['attribute'] as $attribute) {
				//чтобы не было повторных значений, на всякий случай
				$this->attributes_id[$attribute['attribute_id']] = 1;
			}
		}
		//из ключей составим массив и добавим их в модель
		$this->attributes_id = array_keys($this->attributes_id);

		//определим какие атрибуты нужны
		$this->filterAttributes('product_tab', 0);

		if(empty($this->attributes_id))
			return array();

		$attribute_groups_tmp = $attribute_groups;
		foreach ($attribute_groups_tmp as $key_group => $attribute_group) {
			foreach ($attribute_group['attribute'] as $key_attribute => $attribute) {
				if(!in_array($attribute['attribute_id'], $this->attributes_id))
					unset($attribute_groups[$key_group]['attribute'][$key_attribute]);
				//если группа полностью пуста, удалим ее
				if(empty($attribute_groups[$key_group]['attribute']))
					unset($attribute_groups[$key_group]);
			}
		}
		
		return $attribute_groups;
	}

	/*
	* Методы подстановки по шаблонам
	*/
	//attribute_name
	protected function template_a_name($attribute, $attribute_settings, $attribute_values){

		return $attribute['name'];
	}

	//attribute_short_name
	protected function template_a_name_s($attribute, $attribute_settings, $attribute_values){
		return (!empty($attribute_settings['short_name']))?$attribute_settings['short_name']: $attribute['name'];
	}

	//attribute_id
	protected function template_a_id($attribute, $attribute_settings, $attribute_values){
		return $attribute['attribute_id'];
	}

	//attribute_image
	protected function template_a_img($attribute, $attribute_settings, $attribute_values){
		$result = '';
		//если у атрибута указана картинка
		if (isset($attribute_settings['attribute_image']) && is_file(DIR_IMAGE . $attribute_settings['attribute_image'])) {
			$img_file = $this->model_tool_image->resize($attribute_settings['attribute_image'], $this->settings['dae_att_image_w_c'], $this->settings['dae_att_image_h_c']);
			$result = '<img src="' .$img_file . '" alt="' .  $attribute['name'] . '">';
		}
		return $result;
	}

	//attribute_html
	protected function template_a_html($attribute, $attribute_settings, $attribute_values){
		return (!empty($attribute_settings['attribute_html']))?$attribute_settings['attribute_html']:'';									
	}

	//attribute_tooltip
	protected function template_a_tt($attribute, $attribute_settings, $attribute_values){
		return (!empty($attribute_settings['tooltip']))?'<span class="dae-tooltip" data-original-title="' . $attribute_settings['tooltip'] . '" data-toggle="tooltip" title=""></span>':'';
	}

	//attribute_name_tooltip
	protected function template_a_name_tt($attribute, $attribute_settings, $attribute_values){
		return (!empty($attribute_settings['tooltip']))?'<span data-original-title="' . $attribute_settings['tooltip'] . '" data-toggle="tooltip" title="">'.$attribute['name'].'</span>':$attribute['name'];
	}

	//attribute_image_tooltip
	protected function template_a_img_tt($attribute, $attribute_settings, $attribute_values){
		$result = '';
		$attribute_image_tooltip = '';
		if(!empty($attribute_settings['tooltip'])){
			$attribute_image_tooltip =' data-original-title="' . $attribute_settings['tooltip'] . '" data-toggle="tooltip"';
		}
		if (isset($attribute_settings['attribute_image']) && is_file(DIR_IMAGE . $attribute_settings['attribute_image'])) {
			$img_file = $this->model_tool_image->resize($attribute_settings['attribute_image'], $this->settings['dae_att_image_w_c'], $this->settings['dae_att_image_h_c']);
			$result = '<img src="' .$img_file . '" alt="' .  $attribute['name'] . '" '.$attribute_image_tooltip.'>';
		}
		return $result;
	}

	//attribute_value_id - не применимо к множественному значению
	protected function template_a_v_id($attribute, $attribute_settings, $attribute_values){
		if($this->checkMulti($attribute['attribute_id']))
			return 0;
		$md5 = $this->getHash($attribute['text']);
		return (isset($attribute_values[$md5]))?$attribute_values[$md5]['id']:0;
	}

	//attribute_value
	protected function template_a_v($attribute, $attribute_settings, $attribute_values){
		if(empty($attribute['text']))
			return false;
		return (!empty($attribute['value_replace'] ))?$attribute['value_replace']:$attribute['text'];//если была замена значения на название...
	}

	//attribute_value_image
	protected function template_a_v_img($attribute, $attribute_settings, $attribute_values){
		$result = array();
		//если атрибут имеет множественное значение, то достанем картинку для каждого значения
		$values = $this->explodeMultiValue($attribute['text']);
		
		foreach ($values as $value) {
			$attribute_value_image = (isset($attribute_values[$this->getHash($value)]))?$attribute_values[$this->getHash($value)]['image']:null;
								
			if (!empty($attribute_value_image) && is_file(DIR_IMAGE . $attribute_value_image)) {
				$img_file = $this->model_tool_image->resize($attribute_value_image, $this->settings['dae_val_image_w_c'], $this->settings['dae_val_image_h_c']);
				$result[] = '<img src="' .$img_file . '" alt="' . $attribute['name'].': '. $value . '"> ';
			}
		}
		return implode('',$result);//$this->implodeMultiValue($result);
	}
	//attribute_value_image+text
	protected function template_a_v_img_text($attribute, $attribute_settings, $attribute_values){
		$result = array();
		//если атрибут имеет множественное значение, то достанем картинку для каждого значения
		$values = $this->explodeMultiValue($attribute['text']);
		
		foreach ($values as $value) {

			$attribute_value_image = (isset($attribute_values[$this->getHash($value)]))?$attribute_values[$this->getHash($value)]['image']:null;
								
			if (!empty($attribute_value_image) && is_file(DIR_IMAGE . $attribute_value_image)) {
				$img_file = $this->model_tool_image->resize($attribute_value_image, $this->settings['dae_val_image_w_c'], $this->settings['dae_val_image_h_c']);
				$result[] = '<img src="' .$img_file . '" alt="' . $attribute['name'].': '. $value . '"> '.$value;
			}else{//если нет картинки, выведем просто значение
				$result[]=$value;
			}
		}
		return $this->implodeMultiValue($result);
	}
	//attribute_value_tooltip - один тултип даже для множественного значения						
	protected function template_a_v_tt($attribute, $attribute_settings, $attribute_values){

		$text = (!empty($attribute['value_replace']))?$attribute['value_replace']:$attribute['text'];
		if(!empty($attribute_settings['tooltip']))
			return '<span data-original-title="' . $attribute_settings['tooltip'] . '" data-toggle="tooltip" title="">'.$text.'</span>';
		else 
			return $text;
	}

	//attribute_value_description_tooltip
	protected function template_a_v_desc_tt($attribute, $attribute_settings, $attribute_values){
		$result = array();
		$values = $this->explodeMultiValue($attribute['text']);
		foreach ($values as $value) {
			if(!empty($attribute_values[$this->getHash($value)]['description']))
				$result[] = '<span data-original-title="' . htmlentities($attribute_values[$this->getHash($value)]['description']) . '" data-toggle="tooltip" title="">'.$value.'</span>';
			else
				$result[] = $value;
		}

		return implode($this->settings['dae_value_separator'], $result);
	}

	//attribute_value_description - только для одиночных значений
	protected function template_a_v_desc($attribute, $attribute_settings, $attribute_values){
		$md5 = $this->getHash($attribute['text']);
		return (isset($attribute_values[$md5]) && !empty($attribute_values[$md5]['description']))?$attribute_values[$md5]['description']:'';
	}

	//attribute_value_url
	protected function template_a_v_url($attribute, $attribute_settings, $attribute_values){
		$result = array();
		$values = $this->explodeMultiValue($attribute['text']);
		foreach ($values as $value) {
			if(isset($attribute_values[$this->getHash($value)]) && !empty($attribute_values[$this->getHash($value)]['url']))
				$result[] = '<a href ="' . htmlentities($attribute_values[$this->getHash($value)]['url']) . '" target="_blank">'.$value.'</a>';
			else
				$result[] = $value;
		}
		return implode($this->settings['dae_value_separator'], $result);
	}
	//attribute_value_image_tooltip
	protected function template_a_v_img_tt($attribute, $attribute_settings, $attribute_values){
		$attribute_value_image_tooltip = '';
		if(!empty($attribute_settings['tooltip']))
			$attribute_value_image_tooltip =' data-original-title="' . $attribute_settings['tooltip'] . '" data-toggle="tooltip"';
		$values = $this->explodeMultiValue($attribute['text']);							
		$result = array();
		foreach ($values as $value) {
			$attribute_value_image = (isset($attribute_values[$this->getHash($value)]))?$attribute_values[$this->getHash($value)]['image']:'';
			if (!empty($attribute_value_image) && is_file(DIR_IMAGE . $attribute_value_image)) {
				$img_file = $this->model_tool_image->resize($attribute_value_image, $this->settings['dae_val_image_w_c'], $this->settings['dae_val_image_h_c']);
				$result[] = '<img src="' .$img_file . '" alt="' . $attribute['name'].': '. $attribute['text'] . '" '.$attribute_value_image_tooltip.'>';
			}
		}
		return implode($this->settings['dae_value_separator'], $result);
	}
	
	#custom methods		
	
}