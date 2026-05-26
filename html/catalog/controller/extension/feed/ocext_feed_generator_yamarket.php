<?php
class ControllerExtensionFeedOcextFeedGeneratorYaMarket extends Controller {
	private $shop = array();
	private $currencies = array();
	private $categories = array();
        private $product_categories = array();
	private $offers = array();
        private $offers_fwrire = array('offers'=>array(),'cache_files'=>array());
        private $prices = array();
        private $parse_yml = array();
        private $delivery_option = array();
        private $general_setting = array();
        private $setting_yml = array();
        private $eol = "\n";
        private $debug = 0;
        private $memory_limit_on_fwrite = 0.7;///0.020625;
        private $l_data = array();
        private $HTTP_SERVER;
        private $count_skips = 0;
        private $count_offers = 0;
        private $total_memory = 0;
        private $log_errors = array();
        private $path_on_model = 'model_extension_feed_ocext_feed_generator_yamarket';
        private $path_oc_version = 'extension/feed';
        private $cfiles = array(
            'offers_cache'=>'multi_yml_offers_',
            'yml_parts_cache'=>'multi_yml_yml_parts_',
            'fb_parts_cache'=>'multi_yml_fb_parts_',
        );




        public function index() {
            
                $error = array();                                                                                                                                                                                                                                                                                                                                                                   eval(base64_decode('JHRoaXMtPmNoZWNrbGljZW5zZSgpOw=='));//OCext.com: do not delete this line | не удаляйте эту строку
            
                if (!$this->config->get('ocext_feed_generator_yamarket_status')) {
                    $error[] = 'Модуль выключен';
                }
                
                $general_setting = $this->config->get('ocext_feed_generator_yamarket_general_setting');
                
                if(!isset($general_setting['user_key']) || !$general_setting['user_key']){
                    $error[] = 'Не указан лицензионный ключ. Отправьте запрос на welcome@ocext.com, в котором укажите сайт, где приобретался модуль, номер заказа и email аккаунта';
                }
                
                if(!isset($general_setting['user_email']) || !$general_setting['user_email']){
                    $error[] = 'Не указан лицензионный email покупателя. В настройках модуля укажите email, который отправлялся при запросе лицензинного ключа';
                }
                
                if(!file_exists($_SERVER["DOCUMENT_ROOT"].'/system/library/vendor/ocext/ocext_feed_generator_yamarket_license.php')){
                    $error[] = 'Неверно лицензионный ключ. Отправьте запрос на welcome@ocext.com, в котором укажите сайт, где приобретался модуль, номер заказа и email аккаунта';
                }
                
                $token_get = '';
                
                $filter_data_group_id = '';
                
                if(isset($this->request->get['token'])){
                    
                    $token_get = $this->request->get['token'];
                    
                    if(isset($general_setting['filter_data'])){
                        
                        foreach ($general_setting['filter_data'] as $filter_data) {
                            
                            if(isset($this->setting_yml['path_token']) && isset($filter_data[$this->setting_yml['path_token']]) && $filter_data[$this->setting_yml['path_token']] ===$token_get){
                                
                                $general_setting['path_token_export'] = $token_get;
                                if(isset($filter_data['fb_filename_export'])){
                                    $general_setting['fb_filename_export'] = $filter_data['fb_filename_export'];
                                }
				
				$general_setting['promos_ids'] = array();
				if(isset($filter_data['promos_ids'])){
                                    $general_setting['promos_ids'] = $filter_data['promos_ids'];
                                }
                                
                                $general_setting['filename_export'] = $filter_data['filename_export'];
                                $general_setting['yml_currencies'] = $filter_data['yml_currencies'];
                                $filter_data_group_id = $filter_data['filter_data_group_id'];
                                
                                if(!isset($filter_data['content_language_id']) || !$filter_data['content_language_id']){
                                    
                                    $content_language_id = $this->config->get('config_language_id');
                                    
                                }else{
                                    
                                    $content_language_id = $filter_data['content_language_id'];
                                    
                                }
                                
                            }
                            
                        }
                        
                    }
                    
                }
                if(!isset($general_setting['path_token_export']) || !$general_setting['path_token_export']){
                    $error[] = 'Не задан защитный параметр ссылки - token';
                }
                
                if(isset($general_setting['path_token_export']) && $token_get!=$general_setting['path_token_export']){
                    $error[] = 'Неверный защитный параметр ссылки - token';
                }
                
                
                if(!isset($general_setting['yml_currencies']) || !$general_setting['yml_currencies']){
                    $error[] = 'Не указана валюта - обязательный параметр';
                }else{
                    $this->setCurrencyAttributes($general_setting['yml_currencies']);
                }
                
                if(!isset($general_setting['name']) || !$general_setting['name']){
                    $error[] = 'Не указано краткое название магазина';
                }else{
                    $this->setShopAttributes('name', $general_setting['name']);
                }
                
                if(!isset($general_setting['company']) || !$general_setting['company']){
                    $error[] = 'Не указано название компании';
                }else{
                    $this->setShopAttributes('company', $general_setting['company']);
                }
                
                if(!$this->config->get('config_secure')){
                    $this->setShopAttributes('url', HTTP_SERVER);
                    $this->HTTP_SERVER = HTTP_SERVER;
                }else{
                    $this->setShopAttributes('url', HTTPS_SERVER);
                    $this->HTTP_SERVER = HTTPS_SERVER;
                }
                
                if(isset($general_setting['platform']) && $general_setting['platform']){
                    $this->setShopAttributes('platform', $this->config->get('ayeogs_platform'));
                }
                
                if(isset($general_setting['version']) && $general_setting['version']){
                    $this->setShopAttributes('version', $general_setting['version']);
                }
                
                $this->general_setting = $general_setting;
		
		$this->load->model($this->path_oc_version.'/ocext_feed_generator_yamarket');
		
		if(isset($general_setting['promos_ids']) && $general_setting['promos_ids']){
		    
		    foreach ($general_setting['promos_ids'] as $key => $promos_id) {
			
			$promos = $this->{$this->path_on_model}->getPromoGift($promos_id);
			
			if($promos){
			    
			    if($promos['promos_type']=='gift-with-purchase'){
				
				foreach ($promos['promo-gifts'] as $promo_gifts_id => $promo_gifts) {
				    
				    if($promo_gifts['name']!=='' && $promo_gifts['picture']!==''){
					
					$this->general_setting['gifts'][ $promos_id.'000'.$promo_gifts_id ] = array(
					    'name' => $promo_gifts['name'],
					    'picture' => $promo_gifts['picture'],
					    'gift_id' => $promos_id.'000'.$promo_gifts_id,
					);
					
					$promos['gift_ids_selected'][$promos_id.'000'.$promo_gifts_id] = $promos_id.'000'.$promo_gifts_id;
					
				    }
				    
				}
				
			    }
			    
			    $this->general_setting['promos'][$promos_id] = $promos;
			    
			}
			
		    }
		    
		}
                
                $this->general_setting['filter_and_raplace_tags'] = $this->{$this->path_on_model}->getFilterData('ocext_feed_generator_yamarket_ym_filter_columns',$filter_data_group_id);
                
                if(!$error){
                    //создаем список категорий
                    $categories_end_offers = $this->getCategoriesAndOffers($filter_data_group_id,$content_language_id,$this->setting_yml);
                    if(!$categories_end_offers){
                        $error[] = 'Не найдены категории и/или товары. Невозможно создать файл без категорий и/или товаров';
                    }
                }
                
                if(isset($categories_end_offers['add_settings'])){
                    
                    $this->general_setting['add_settings'] = $categories_end_offers['add_settings'];
                    
                }
                
                
                if($error){
                    $this->sendErrorXML($error);
                    return;
                }
                
                 $this->load->model($this->path_oc_version.'/ocext_feed_generator_yamarket');
            
                $unlink_all_cache = array($this->cfiles['offers_cache'].$this->request->get['token'].'-');
                
                if(isset($this->request->get['fb'])){
                    
                    $unlink_all_cache[] = $this->cfiles['fb_parts_cache'].$this->request->get['token'].'-';
                    
                }else{
                    
                    $unlink_all_cache[] = $this->cfiles['yml_parts_cache'].$this->request->get['token'].'-';
                    
                }
                
                $this->{$this->path_on_model}->unlinkAllCache($unlink_all_cache);
                
                foreach ($categories_end_offers['categories'] as $category) {
                    $this->setCategoryAttrubite($category['name'], $category['category_id'], $category['parent_id'], $category);
                }
                
                if( isset($this->general_setting['replace_cid_to_product_on_this'])){
                    
                    foreach($this->general_setting['replace_cid_to_product_on_this'] as $replace_cid_to_product_on_this){
                        
                        if(isset($this->categories[$replace_cid_to_product_on_this])){
                            
                            $parentId = $this->categories[$replace_cid_to_product_on_this]['parentId'];
                            
                            unset($this->categories[$replace_cid_to_product_on_this]['parentId']);
                            
                            if(isset($this->categories[$parentId])){
                            
                                $parentId_this = $parentId;
                                
                                if(isset($this->categories[$parentId]['parentId'])){
                                    
                                    $parentId = $this->categories[$parentId]['parentId'];
                                
                                    unset($this->categories[$parentId]);
                                    
                                }
                                
                                unset($this->categories[$parentId_this]);
                                
                            }
                            
                            if(isset($this->categories[$parentId])){
                            
                                $parentId_this = $parentId;
                                
                                if(isset($this->categories[$parentId]['parentId'])){
                                    
                                    $parentId = $this->categories[$parentId]['parentId'];
                                
                                    unset($this->categories[$parentId]);
                                    
                                }
                                
                                unset($this->categories[$parentId_this]);
                                
                            }
                            if(isset($this->categories[$parentId])){
                            
                                $parentId_this = $parentId;
                                
                                if(isset($this->categories[$parentId]['parentId'])){
                                    
                                    $parentId = $this->categories[$parentId]['parentId'];
                                
                                    unset($this->categories[$parentId]);
                                    
                                }
                                
                                unset($this->categories[$parentId_this]);
                                
                            }
                            if(isset($this->categories[$parentId])){
                            
                                $parentId_this = $parentId;
                                
                                if(isset($this->categories[$parentId]['parentId'])){
                                    
                                    $parentId = $this->categories[$parentId]['parentId'];
                                
                                    unset($this->categories[$parentId]);
                                    
                                }
                                
                                unset($this->categories[$parentId_this]);
                                
                            }
                            
                        }
                        
                    }
                    
                    
                    
                    foreach($this->categories as $category_id_on_delete => $category_on_delete){
                        
                        if(isset($category_on_delete['parentId']) && in_array($category_on_delete['parentId'], $this->general_setting['replace_cid_to_product_on_this'])){
                            
                            $category_id_on_delete_this = $category_on_delete['id'];
                            
                            unset($this->categories[$category_id_on_delete]);
                            
                            foreach($this->categories as $category_id_on_delete2 => $category_on_delete2){

                                if(isset($category_on_delete2['parentId']) && $category_on_delete2['parentId']==$category_id_on_delete_this){
                                    
                                    $category_id_on_delete_this3 = $category_on_delete2['id'];
                                    
                                    unset($this->categories[$category_id_on_delete2]);
                                    
                                    foreach($this->categories as $category_id_on_delete3 => $category_on_delete3){
                                        
                                        if(isset($category_on_delete2['parentId']) && $category_on_delete2['parentId']==$category_id_on_delete_this3){
                                            
                                            unset($this->categories[$category_id_on_delete3]);
                                            
                                        }
                                        
                                    }
                                    
                                }

                            }
                            
                        }

                    }

                }
                
                if($this->debug){
                    
                    $this->log_errors[] = 'memory_limit '.ini_get('memory_limit').', max_execution_time '.ini_get('max_execution_time');
                    
                    foreach ($categories_end_offers['log_errors'] as $log_error) {

                        $this->log_errors[] = $log_error;

                    }
                    
                }
                
                $cache_status = 0;
                
                if(isset($this->general_setting['yml_cache_enable']) && $this->general_setting['yml_cache_enable']){
            
                    $cache_status = 1;

                }
                
                if($cache_status && isset($categories_end_offers['cache']) &&  $categories_end_offers['cache']){
                    
                    foreach ($categories_end_offers['cache'] as $cache_file_name) {
                        
                        $cache_offers = $this->getCache($cache_file_name);
                        
                        if($cache_offers){
                            
                            $this->delivery_option += $this->getShopDeliveryOptionAttrubite($cache_offers);
                            
                            foreach ($cache_offers as $i_cache_offer => $product) {
                        
                                $this->getOfferAttrubite($product);

                                unset($cache_offers[$i_cache_offer]);

                            }
                            
                            if($this->debug){
                                
                                $this->total_memory += memory_get_usage()/1024/1024;
                                
                                $this->log_errors[] = 'memory_get_usage for '.$cache_file_name.' '.round((memory_get_usage()/1024/1024),2).' M';
                                
                                $this->offers = array();
                                
                            }
                            
                        }
                        
                        $cache_status++;
                        
                    }
                    
                    if($this->debug){
                        
                        $this->log_errors[] = "count_skips: ".  $this->count_skips.', count_offers: '.$this->count_offers;
                        
                        $this->log_errors[] = "total memory usage: ".  round($this->total_memory,2).' M';
                        
                    }
                    
                }else{
                    
                    $this->delivery_option = $this->getShopDeliveryOptionAttrubite($categories_end_offers['offers']);
                    
                    foreach ($categories_end_offers['offers'] as $i_product => $product) {
                        
                        $this->getOfferAttrubite($product);
                        
                        unset($categories_end_offers['offers'][$i_product]);
                        
                    }
                    
                    $this->total_memory += memory_get_usage()/1024/1024;
                                
                    $this->log_errors[] = "count_skips: ".  $this->count_skips.', count_offers: '.$this->count_offers;
                        
                    $this->log_errors[] = "total memory usage: ". round($this->total_memory,2).' M';
                    
                }
                
                if($this->product_categories){
                    
                    foreach ($this->categories as $category_id => $category) {
                        
                        if(!isset($this->product_categories[$category_id])){
                            
                            unset($this->categories[$category_id]);
                            
                        }
                        
                    }
                    
                }
		
		if(isset($this->general_setting['promos']) && $this->general_setting['promos']){
		    
		    $currency = '';
		    
		    if(isset($this->currencies[0]['id'])){
			
			$currency = $this->currencies[0]['id'];
			
		    }
		    
		    if(isset($this->general_setting['gifts'])){
			
			$gifts_result = '<gifts>'.$this->eol;
			
			foreach ($this->general_setting['gifts'] as $gifts) {
			    
			    $gifts_result .= '<gift id="'.$gifts['gift_id'].'">';

			    $gifts_result .= '<name>'.$gifts['name'].'</name>';
			    
			    $gifts_result .= '<picture>'.$gifts['picture'].'</picture>';
			    
			    $gifts_result .= '</gift>';
			    
			}
			
			$gifts_result .= '</gifts>'.$this->eol;
			
			$this->general_setting['gifts_result'] = $gifts_result;
			
		    }
		    
		    foreach ($this->general_setting['promos'] as $promos_id => $promo) {
			
			$promos_result = '';
			
			if($promo['promos_type']=='promo-code'){
			    
			    $general_values = array(
				'start-date' => $promo['start-date'],
				'end-date' => $promo['end-date'],
				'description' => '<![CDATA['. html_entity_decode($promo['description']).']]>',
				'url' => $this->prepareField($promo['url']),
				'promo-code' => $promo['promo-code']
			    );
			    
			    if($promo['discount_percent']!==''){
				
				$general_values['discount'] = array(
				    'attributes' => array('unit'=>'percent'),
				    'value' => (int)$promo['discount_percent'],
				);
				
			    }elseif($promo['discount_currency']!==''){
				
				$general_values['discount'] = array(
				    'attributes' => array('unit'=>'currency','currency'=>$currency),
				    'value' => (int)$promo['discount_currency'],
				);
				
			    }
			    
			    $purchase = array();
			    
			    if(isset($promo['product_ids_selected'])){
				
				foreach ($promo['product_ids_selected'] as $offer_id) {
				    
				    $purchase[]['product'] = array(
					'attributes' => array('offer-id'=>$offer_id),
					'value' => '',
				    );
				    
				}
				
			    }
			    
			    if(isset($promo['category_ids_selected'])){
				
				foreach ($promo['category_ids_selected'] as $categoryId) {
				    
				    if(isset($this->general_setting['mapping_market_place_categories_replace']) && isset($this->general_setting['mapping_market_place_categories_replace'][$categoryId])){

					$categoryId = $this->general_setting['mapping_market_place_categories_replace'][$categoryId];

				    }
				    
				    $purchase[]['product'] = array(
					'attributes' => array('category-id'=>$categoryId),
					'value' => '',
				    );
				    
				}
				
			    }
			    
			    $general_values['purchase'] = $purchase;
			    
			    $promos_result = '<promo id="'.$promos_id.'" type="promo code">'.$this->eol;
			    
			    foreach ($general_values as $tag_name => $value) {
				
				if($tag_name=='purchase'){
				    
				    $promos_result .= '<purchase>'.$this->eol;
				    
				    foreach ($value as $value2) {
					
					foreach ($value2 as $tag_name2 => $value3) {
					
					    $promos_result .= '<'.$tag_name2.' ';
					
					    foreach ($value3['attributes'] as $attr_name => $attr_value) {
						$promos_result .= $attr_name .'="'.$attr_value.'" ';
					    }

					    $promos_result .= ($value3['value']!=='' ? '>'.$value3['value'].'</'.$tag_name2.'>' : '/>').$this->eol;
					    
					}
					
				    }
				    
				    $promos_result .= '</purchase>'.$this->eol;
				    
				}elseif($tag_name=='discount'){
				    
				    $promos_result .= '<'.$tag_name.' ';
				    
				    foreach ($value['attributes'] as $attr_name => $attr_value) {
					
					$promos_result .= $attr_name .'="'.$attr_value.'" ';
					
				    }
				    
				    $promos_result .= ($value['value']!=='' ? '>'.$value['value'].'</'.$tag_name.'>' : '/>').$this->eol;
				    
				}else{
				    
				    $promos_result .= '<'.$tag_name.'>'. ($value!=='' ? $value.'</'.$tag_name.'>' : '/>').$this->eol;
				    
				}
				
			    }
			    
			    $promos_result .= '</promo>'.$this->eol;
			    
			    $this->general_setting['promos_result'][$promos_id]  =  $promos_result;
			    
			}
			elseif($promo['promos_type']=='flash-discount'){
			    
			    $general_values = array(
				'start-date' => $promo['start-date'],
				'end-date' => $promo['end-date'],
				'description' => '<![CDATA['. html_entity_decode($promo['description']).']]>',
				'url' => $this->prepareField($promo['url'])
			    );
			    
			    $purchase = array();
			    
			    if(isset($promo['product_ids_selected'])){
				
				foreach ($promo['product_ids_selected'] as $offer_id) {
				    
				    $purchase[]['product'] = array(
					'attributes' => array('offer-id'=>$offer_id),
					'value' => '<discount-price currency="'.$currency.'">'.$promo['discount-price'].'</discount-price>',
				    );
				    
				}
				
			    }
			    
			    $general_values['purchase'] = $purchase;
			    
			    $promos_result = '<promo id="'.$promos_id.'" type="flash discount">'.$this->eol;
			    
			    foreach ($general_values as $tag_name => $value) {
				
				if($tag_name=='purchase'){
				    
				    $promos_result .= '<purchase>'.$this->eol;
				    
				    foreach ($value as $value2) {
					
					foreach ($value2 as $tag_name2 => $value3) {
					
					    $promos_result .= '<'.$tag_name2.' ';
					
					    foreach ($value3['attributes'] as $attr_name => $attr_value) {
						$promos_result .= $attr_name .'="'.$attr_value.'" ';
					    }

					    $promos_result .= ($value3['value']!=='' ? '>'.$value3['value'].'</'.$tag_name2.'>' : '/>').$this->eol;
					    
					}
					
				    }
				    
				    $promos_result .= '</purchase>'.$this->eol;
				    
				}elseif($tag_name=='discount'){
				    
				    $promos_result .= '<'.$tag_name.' ';
				    
				    foreach ($value['attributes'] as $attr_name => $attr_value) {
					
					$promos_result .= $attr_name .'="'.$attr_value.'" ';
					
				    }
				    
				    $promos_result .= ($value['value']!=='' ? '>'.$value['value'].'</'.$tag_name.'>' : '/>').$this->eol;
				    
				}else{
				    
				    $promos_result .= '<'.$tag_name.'>'. ($value!=='' ? $value.'</'.$tag_name.'>' : '/>').$this->eol;
				    
				}
				
			    }
			    
			    $promos_result .= '</promo>'.$this->eol;
			    
			    $this->general_setting['promos_result'][$promos_id]  =  $promos_result;
			    
			}
			elseif($promo['promos_type']=='n-plus-m'){
			    
			    $general_values = array(
				'start-date' => $promo['start-date'],
				'end-date' => $promo['end-date'],
				'description' => '<![CDATA['. html_entity_decode($promo['description']).']]>',
				'url' => $this->prepareField($promo['url'])
			    );
			    
			    $purchase = array();
			    
			    $purchase[]['required-quantity'] = array(
				'attributes' => array(),
				'value' => $promo['required-quantity'],
			    );
			    
			    $purchase[]['free-quantity'] = array(
				'attributes' => array(),
				'value' => $promo['free-quantity'],
			    );
			    
			    if(isset($promo['product_ids_selected'])){
				
				foreach ($promo['product_ids_selected'] as $offer_id) {
				    
				    $purchase[]['product'] = array(
					'attributes' => array('offer-id'=>$offer_id),
					'value' => '',
				    );
				    
				}
				
			    }
			    
			    if(isset($promo['category_ids_selected'])){
				
				foreach ($promo['category_ids_selected'] as $categoryId) {
				    
				    if(isset($this->general_setting['mapping_market_place_categories_replace']) && isset($this->general_setting['mapping_market_place_categories_replace'][$categoryId])){

					$categoryId = $this->general_setting['mapping_market_place_categories_replace'][$categoryId];

				    }
				    
				    $purchase[]['product'] = array(
					'attributes' => array('category-id'=>$categoryId),
					'value' => '',
				    );
				    
				}
				
			    }
			    
			    $general_values['purchase'] = $purchase;
			    
			    $promos_result = '<promo id="'.$promos_id.'" type="n plus m">'.$this->eol;
			    
			    foreach ($general_values as $tag_name => $value) {
				
				if($tag_name=='purchase'){
				    
				    $promos_result .= '<purchase>'.$this->eol;
				    
				    foreach ($value as $value2) {
					
					foreach ($value2 as $tag_name2 => $value3) {
					
					    $promos_result .= '<'.$tag_name2.' ';
					
					    foreach ($value3['attributes'] as $attr_name => $attr_value) {
						$promos_result .= $attr_name .'="'.$attr_value.'" ';
					    }

					    $promos_result .= ($value3['value']!=='' ? '>'.$value3['value'].'</'.$tag_name2.'>' : '/>').$this->eol;
					    
					}
					
				    }
				    
				    $promos_result .= '</purchase>'.$this->eol;
				    
				}elseif($tag_name=='discount'){
				    
				    $promos_result .= '<'.$tag_name.' ';
				    
				    foreach ($value['attributes'] as $attr_name => $attr_value) {
					
					$promos_result .= $attr_name .'="'.$attr_value.'" ';
					
				    }
				    
				    $promos_result .= ($value['value']!=='' ? '>'.$value['value'].'</'.$tag_name.'>' : '/>').$this->eol;
				    
				}else{
				    
				    $promos_result .= '<'.$tag_name.'>'. ($value!=='' ? $value.'</'.$tag_name.'>' : '/>').$this->eol;
				    
				}
				
			    }
			    
			    $promos_result .= '</promo>'.$this->eol;
			    
			    $this->general_setting['promos_result'][$promos_id]  =  $promos_result;
			    
			}
			elseif($promo['promos_type']=='gift-with-purchase'){
			    
			    $general_values = array(
				'start-date' => $promo['start-date'],
				'end-date' => $promo['end-date'],
				'description' => '<![CDATA['. html_entity_decode($promo['description']).']]>',
				'url' => $this->prepareField($promo['url'])
			    );
			    //product_as_gift_ids_selected 
			    $purchase = array();
			    
			    $purchase[]['required-quantity'] = array(
				'attributes' => array(),
				'value' => $promo['required-quantity-present'],
			    );
			    
			    if(isset($promo['product_ids_selected'])){
				
				foreach ($promo['product_ids_selected'] as $offer_id) {
				    
				    $purchase[]['product'] = array(
					'attributes' => array('offer-id'=>$offer_id),
				    );
				    
				}
				
			    }
			    
			    $promo_gifts = array();
			    
			    if(isset($promo['gift_ids_selected'])){
				
				foreach ($promo['gift_ids_selected'] as $gift_id) {
				    
				    $promo_gifts[]['promo-gift'] = array(
					'attributes' => array('gift-id'=>$gift_id),
				    );
				    
				}
				
			    }
			    
			    if(isset($promo['product_as_gift_ids_selected'])){
				
				foreach ($promo['product_as_gift_ids_selected'] as $offer_id) {
				    
				    $promo_gifts[]['promo-gift'] = array(
					'attributes' => array('offer-id'=>$offer_id),
				    );
				    
				}
				
			    }
			    
			    $general_values['purchase'] = $purchase;
			    
			    $general_values['promo-gifts'] = $promo_gifts;
			    
			    $promos_result = '<promo id="'.$promos_id.'" type="gift with purchase">'.$this->eol;
			    
			    foreach ($general_values as $tag_name => $value) {
				
				if($tag_name=='purchase' || $tag_name=='promo-gifts'){
				    
				    $promos_result .= '<'.$tag_name.'>'.$this->eol;
				    
				    foreach ($value as $value2) {
					
					foreach ($value2 as $tag_name2 => $value3) {
					
					    $promos_result .= '<'.$tag_name2.' ';
					
					    foreach ($value3['attributes'] as $attr_name => $attr_value) {
						$promos_result .= $attr_name .'="'.$attr_value.'" ';
					    }

					    $promos_result .= ( (isset($value3['value']) && $value3['value']!=='') ? '>'.$value3['value'].'</'.$tag_name2.'>' : '/>').$this->eol;
					    
					}
					
				    }
				    
				    $promos_result .= '</'.$tag_name.'>'.$this->eol;
				    
				}else{
				    
				    $promos_result .= '<'.$tag_name.'>'. ($value!=='' ? $value.'</'.$tag_name.'>' : '/>').$this->eol;
				    
				}
				
			    }
			    
			    $promos_result .= '</promo>'.$this->eol;
			    
			    $this->general_setting['promos_result'][$promos_id]  =  $promos_result;
			    
			}
			
		    }
		    
		}
                
                if(!$this->debug && isset($this->setting_yml['status_validation']) && isset($this->parse_yml[$this->setting_yml['status_validation']]) && $this->parse_yml[$this->setting_yml['status_validation']]){
                    $yml = $this->getYml();
                    if($yml){
                        $this->response->addHeader('Content-Type: application/xml');
                        $this->response->setOutput($yml);
                    }
                    
                    $unlink_all_cache = array($this->cfiles['offers_cache'].$this->request->get['token'].'-');
                
                    if(isset($this->request->get['fb'])){

                        $unlink_all_cache[] = $this->cfiles['fb_parts_cache'].$this->request->get['token'].'-';

                    }else{

                        $unlink_all_cache[] = $this->cfiles['yml_parts_cache'].$this->request->get['token'].'-';

                    }

                    $this->{$this->path_on_model}->unlinkAllCache($unlink_all_cache);
                    
                }else{
                    
                    $this->sendErrorXML($this->log_errors);
		    
                    return;
                    
                }
	}
        
        public function setLogErrors($message) {
            
            if($this->debug){
                
                $this->log_errors[] = $message;
                
            }
            
        }
        
        public function getCache($filename) {
            
            $cache_offers = array();
            
            if(file_exists(DIR_CACHE.$filename)){
                
                $cache_offers = json_decode(file_get_contents(DIR_CACHE.$filename),TRUE);
                
                if( (!$cache_offers or is_null($cache_offers)) && $this->debug ){
                    
                    $this->log_errors[] = "parsing cache error to file ".  DIR_CACHE.$filename;
                    
                }
                
                unlink(DIR_CACHE.$filename);
                
            }else{
                
                if($this->debug){
                        
                    $this->log_errors[] = "no cache file ".  DIR_CACHE.$filename;

                }
                
            }
            
            return $cache_offers;
            
        }
        
        public function getShopDeliveryOptionAttrubite($products) {
            
            $data = array();
            if($products){
                foreach ($products as $product) {
		    
		    $product['setting'] = array();
		    
		    if(isset($product['settings_id']) && isset($this->general_setting['settings']) && isset($this->general_setting['settings'][$product['settings_id']]) ) {
			
			$product['setting'] = $this->general_setting['settings'][$product['settings_id']];
			
		    }
		    
                    if($product['setting'] && $product['setting']['delivery-options']['status']){
                        
                        $weight = '';
                        $stock_status_id = '';
                        $price = '';
                        
                        
                        if(isset($product['weight']) && $product['weight']>0){
                            $weight = (float)$weight;
                        }
                        if(isset($product['weight']) && $product['weight']>0){
                            $stock_status_id = $product['stock_status_id'];
                        }
                        if(isset($product['price'])){
                            $price = $product['price'];
                        }
                        
                        $delivery_options = $this->getDeliveryOption($product['setting'],$price,$weight,$stock_status_id);
                        
                        if($delivery_options){
                            $data['delivery-options'] = $delivery_options;
                        }
                    }
                }
            }
            return $data;
            
        }
        
        public function setProductCategories($category_id) {
            
            if(isset($this->categories[$category_id])){
                $this->product_categories[$category_id] = $category_id;
            }
            
            if(isset($this->categories[$category_id]['parentId'])){
                
                $this->setProductCategories($this->categories[$category_id]['parentId']);
                
            }
            
        }
        
        public function getOfferAttrubite($product) {
            $data = array();
            $template_setting = array();
            $skip_this_offer = FALSE;
	    
	    $product['setting'] = array();
		    
	    if(isset($product['settings_id']) && isset($this->general_setting['settings']) && isset($this->general_setting['settings'][$product['settings_id']]) ) {

		$product['setting'] = $this->general_setting['settings'][$product['settings_id']];

	    }
            
            if($product['setting']){
                $template_setting = $product['setting'];
            }
            
            $data['id'] = $product['product_id'];
            
            if($product['product_id_by_option_id']){
                
                $data['id'] = $product['product_id_by_option_id'];
                
            }
            
            if(isset($template_setting['custom_offer_id']['field']['status']) && $template_setting['custom_offer_id']['field']['status']){
                
                $custom_offer_id = $this->getNameAttributeForType($product,$template_setting,$template_setting['custom_offer_id']['field']['status'],'custom_offer_id');
                
                $data['id'] = str_replace($data['id'], $custom_offer_id, $data['id']);
                
                if($product['product_id_by_option_id'] && $template_setting['custom_offer_id']['field']['status']=='product_id'){

                    $data['id'] = $product['product_id_by_option_id'];

                }
                
            }
            
            if(isset($template_setting['product_id_from']) && $template_setting['product_id_from']!='' && $product['product_id']<$template_setting['product_id_from']){
                $skip_this_offer = TRUE;
            }
            
            if(isset($template_setting['product_id_to']) && $template_setting['product_id_to']!='' &&  $product['product_id']>$template_setting['product_id_to']){
                $skip_this_offer = TRUE;
            }
            
            if(isset($template_setting['disable_this_product']) && $template_setting['disable_this_product']){
                $skip_this_offer = TRUE;
            }
            
            if(isset($template_setting['url_whis_path']) && $template_setting['url_whis_path']){
                $data['url'] = $this->url->link('product/product', 'path='.$this->getPathWhisCategories($product['category_id']).'&product_id='.$product['product_id']);
            }else{
                $data['url'] = $this->url->link('product/product', 'product_id='.$product['product_id']);
            }
            
            $data['url'] .= $product['option_url_param'];
            
            //цены
            
            $product['price'] = $this->getPrice($product['price'],$product,$template_setting);
            
            if($product['special_price']>0){
                $product['special_price'] = $this->getPrice($product['special_price'],$product,$template_setting);
            }
            if($product['discount_special_price']>0){
                $product['discount_special_price'] = $this->getPrice($product['discount_special_price'],$product,$template_setting);
            }
            
            if(isset($template_setting['oldprice']) && $template_setting['oldprice'] && ($product['special_price'] || $product['discount_special_price']) > 0){
                if($product['special_price']>0 && $product['special_price'] < $product['price'] ){
                    
                    $data['oldprice'] = $product['price'];
                    $data['price'] = $product['special_price'];
                    $product['oldprice'] = $product['price'];
                    $product['price'] = $product['special_price'];
                    
                }elseif( $product['discount_special_price']>0 && $product['discount_special_price'] < $product['price']  ){
                    
                    $data['oldprice'] = $product['price'];
                    $data['price'] = $product['discount_special_price'];
                    $product['oldprice'] = $product['price'];
                    $product['price'] = $product['discount_special_price'];
                    
                }else{
                    
                    $data['price'] = $product['price'];
                    
                }
            }
            
            if(isset($template_setting['price_currencies_to'])){
                $decimal_place = (int)$this->currency->getDecimalPlace($template_setting['price_currencies_to']);
            }else{
                $decimal_place = (int)$this->currency->getDecimalPlace(0);
            }
            
            $data['price'] = round($product['price'],$decimal_place);
            
            if(isset($data['oldprice']) && $data['oldprice'] && isset($product['oldprice']) && $product['oldprice']){
                
                $data['oldprice'] = round($product['oldprice'],$decimal_place);
                
            }
                    
            if(isset($this->parse_yml['zero']) && isset($template_setting[$this->parse_yml['zero']]) && !$template_setting[$this->parse_yml['zero']] && $data['price']==0){
                $skip_this_offer = TRUE;
            }
            
            if(isset($template_setting['price_from']) && $template_setting['price_from']!='' && $template_setting['price_from']>$data['price']){
                $skip_this_offer = TRUE;
            }
            
            if(isset($template_setting['price_to']) && $template_setting['price_to']!='' && $data['price'] > $template_setting['price_to']){
                $skip_this_offer = TRUE;
            }
            
            
            if((!$data['price'] || $data['price']==0.0) && (!isset($template_setting['zero_price']) || !$template_setting['zero_price']) ){
                $skip_this_offer = TRUE;
            }else{
                //используется для составных заголовков
                $this->prices[$product['product_id']] = $data['price'];
            }
            //валюта
            $data['currencyId'] = $this->currencies[0]['id'];
            
            
            if(isset($product['market_category']) && $product['market_category']){
                //категория market_category
                $data['market_category'] = $product['market_category'];
            }
            
            //изображения
            $data['picture'] = $this->getPictureAttributes($product,$template_setting);
            
            //если без картинок не выгружать, и картинок нет, то это предложение не делаем
            if(isset($template_setting['no_pictures']) && $template_setting['no_pictures'] && !$data['picture']){
                $skip_this_offer = TRUE;
            }
            
            if(isset($template_setting['store']) && $template_setting['store']){
                $data['store'] = 'true';
            }else{
                $data['store'] = 'false';
            }
            //
            if(isset($template_setting['pickup']) && $template_setting['pickup']){
                $data['pickup'] = 'true';
            }else{
                $data['pickup'] = 'false';
            }
            
            //
            if(isset($template_setting['delivery']) && $template_setting['delivery']){
                $data['delivery'] = 'true';
            }else{
                $data['delivery'] = 'false';
            }
            
            if(!isset($template_setting['delivery'])){
                
                $data['delivery'] = 'true';
                
            }
            
            //delivery-options
            if(isset($template_setting['delivery-options']['status']) && $template_setting['delivery-options']['status']){
                
                $w = '';
                if(isset($product['weight']) && $product['weight']>0){
                    $w = (float)$w;
                }
                $ssi = '';
                if(isset($product['stock_status_id']) && $product['stock_status_id']>0){
                    $ssi = $product['stock_status_id'];
                }
                $p = '';
                if(isset($product['price'])){
                    $p = $product['price'];
                }
                
                $delivery_options = $this->getDeliveryOption($template_setting,$p,$w,$ssi);
                if($delivery_options){
                    $data['delivery-options'] = $delivery_options;
                }
            }
            
            if(isset($product['delivery_option_by_manufacturer']) && $product['delivery_option_by_manufacturer']){
                
                if(isset($data['delivery-options'])){
                    
                    $data['delivery-options'][] = $product['delivery_option_by_manufacturer'];
                    
                }else{
                    
                    $data['delivery-options'] = array($product['delivery_option_by_manufacturer']);
                    
                }
                
            }
            
            if((isset($template_setting['offer_name']['field']['status'])  && $template_setting['offer_name']['field']['status']) ){
                
                //название товара
                $data['name'] = $this->getNameAttribute($product,$template_setting,'offer_name');
                
            }elseif(!isset($template_setting['offer_name']['field']['status'])){
                
                $data['name'] = $this->prepareField($product['name']);
                
            }
                  
            if(isset($template_setting['divide_on_option_add_to_name']) && $template_setting['divide_on_option_add_to_name'] && isset($data['name']) && isset($product['option_add_model_name']) && $product['option_add_model_name']){
                $data['name'] .= ' '.$this->prepareField($product['option_add_model_name']);
            }
            
            if(isset($template_setting['text_capitalize']) && $template_setting['text_capitalize'] && isset($data['name'])){
                $data['name'] = mb_convert_case($data['name'], MB_CASE_TITLE, "UTF-8");
            }
            
            //вендор
            if(isset($template_setting['vendor']['field']['status'])){
                $data['vendor'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['vendor']['field']['status'],'vendor');
            }
            
            if(isset($template_setting['vendorCode']['field']['status'])){
                $data['vendorCode'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['vendorCode']['field']['status'],'vendorCode');
            }
            
            //если вендор модель, выход без вендора
            if(isset($template_setting['vendor.model']) && $template_setting['vendor.model'] && ( !isset($data['vendor']) || !$data['vendor'] )){
                //return;
                $data['type'] = 'vendor.model';
            }elseif(isset ($template_setting['vendor.model']) && $template_setting['vendor.model']){
                $data['type'] = 'vendor.model';
            }
            
            //описание
            if(isset($template_setting['offer_description']['field']) && $template_setting['offer_description']['field']){
                $data['description'] = $this->getDescriptionAttribute($product, $template_setting);
            }
            
            //manufacturer_warranty
            if(isset($template_setting['manufacturer_warranty']) && $template_setting['manufacturer_warranty']){
                $data['manufacturer_warranty'] = 'true';
            }
            
            //country_of_origin
            if(isset($template_setting['country_of_origin'])){
                $data['country_of_origin'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['country_of_origin']['field']['status'],'country_of_origin');
            }
            
            //barcode
            if(isset($template_setting['barcode'])){
                $data['barcode'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['barcode']['field']['status'],'barcode');
            }
            
            //expiry
            if(isset($template_setting['expiry'])){
                $data['expiry'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['expiry']['field']['status'],'expiry');
            }
            
            //weight
            if(isset($template_setting['weight'])){
                $data['weight'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['weight']['field']['status'],'weight');
            }
            
            //weight
            if(isset($template_setting['dimensions'])){
                $data['dimensions'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['dimensions']['field']['status'],'dimensions');
            }
            
            //age
            if(isset($template_setting['age']['field']['status']) && $template_setting['age']['field']['status']){
                if($template_setting['age']['unit']){
                    $data['age']['value'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['age']['field']['status'],'age');
                    $data['age']['unit'] = $template_setting['age']['unit'];
                }
            }
            
            //typePrefix
            if(isset($template_setting['typePrefix']) && $template_setting['typePrefix']){
                $data['typePrefix'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['typePrefix']['field']['status'],'typePrefix');
            }
            
            //cpa
            if(isset($template_setting['cpa']) && $template_setting['cpa']){
                $data['cpa'] = '1';
            }
            
            //rec
            if(isset($template_setting['rec']) && $template_setting['rec'] && $product['rec']){
                $data['rec'] = implode(',', $product['rec']);
            }
            
            //adult
            if(isset($template_setting['adult']) && $template_setting['adult']){
                $data['adult'] = 'true';
            }
            
            //available
            $stock_status_id = $product['stock_status_id'];
            $quantity = (int)$product['quantity'];
            $minimum = (int)$product['minimum'];
            $data['available'] = 'false';
            
            
            if(isset($template_setting['available_by_quantity']) && $template_setting['available_by_quantity']){
                
                if(!$quantity || $quantity<$minimum){
                    $data['available'] = 'false';
                }elseif($quantity || $quantity>=$minimum){
                    $data['available'] = 'true';
                }
                
            }else{
                
                if(isset ($template_setting['available_true']) && $template_setting['available_true']==$stock_status_id){
                    $data['available'] = 'true';
                }elseif(isset ($template_setting['available_false'][$stock_status_id])){
                    $data['available'] = 'false';
                }
                
            }
            
            
            
            
            
            //fee в селекторе
            if(isset($template_setting['fee_select']['field']['status']) && $template_setting['fee_select']['field']['status']){
                $data['fee'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['fee_select']['field']['status'],'fee_select');
            }elseif(isset($template_setting['fee_input']) && $template_setting['fee_input']){
                $data['fee'] = $this->prepareField($template_setting['fee_input']);
            }
            
            if(!isset($template_setting['model']['field']['status']) || (isset($template_setting['model']['field']['status'])  && $template_setting['model']['field']['status']) ){
                
                //модель
                $data['model'] = $this->getNameAttribute($product,$template_setting,'model');
                
            }
            
            if(isset($template_setting['divide_on_option_add_to_model']) && $template_setting['divide_on_option_add_to_model'] && isset($data['model']) && isset($product['option_add_model_name']) && $product['option_add_model_name']){
                $data['model'] .= ' '.$this->prepareField($product['option_add_model_name']);
            }
            
            if(isset($template_setting['text_capitalize']) && $template_setting['text_capitalize'] && isset($data['model'])){
                $data['model'] = mb_convert_case($data['model'], MB_CASE_TITLE, "UTF-8");
            }
            
            if(isset($template_setting['dispublic_quantity']) && !$template_setting['dispublic_quantity'] && $data['available']=='false' ){
                $skip_this_offer = TRUE;
            }
            
            //sales_notes в селекторе
            if(isset($template_setting['sales_notes_select']['field']['status']) && $template_setting['sales_notes_select']['field']['status']){
                $data['sales_notes'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['sales_notes_select']['field']['status'],'sales_notes_select');
            }elseif(isset($template_setting['sales_notes']) && $template_setting['sales_notes']){
                $data['sales_notes'] = $this->prepareField($template_setting['sales_notes']);
            }
            
            if(isset($template_setting['sales_notes_select_on_available_true']['field']['status']) && $template_setting['sales_notes_select_on_available_true']['field']['status'] && $data['available'] == 'true'){
                $data['sales_notes'] = $this->getNameAttributeForType($product,$template_setting,$template_setting['sales_notes_select_on_available_true']['field']['status'],'sales_notes_select_on_available_true');
            }elseif(isset($template_setting['sales_notes_on_available_true']) && $template_setting['sales_notes_on_available_true'] && $data['available'] == 'true'){
                $data['sales_notes'] = $this->prepareField($template_setting['sales_notes_on_available_true']);
            }
            
            if(isset($template_setting['sales_notes_on_available_false']) && $template_setting['sales_notes_on_available_false'] && $data['available'] == 'false'){
                $data['sales_notes'] = $this->prepareField($template_setting['sales_notes']);
            }elseif(isset($template_setting['sales_notes_on_available_false']) && $template_setting['sales_notes_on_available_false'] && $data['available'] == 'true'){
                $data['sales_notes'] = '';
            }
            
            if(isset($template_setting['sales_note_by_rule'])){
                
                for($sni=0;$sni<count($template_setting['sales_note_by_rule']);$sni++){
                    
                    $sales_note_by_rule_status = $this->{$this->path_on_model}->getStatusByWhere($template_setting['sales_note_by_rule'][$sni],$product);
                    if($sales_note_by_rule_status){
                        $data['sales_notes'] = $this->prepareField($template_setting['sales_note_by_rule'][$sni]['sales_note']);
                    }
                    
                }
                
            }
            //param
            $param_atributes = array();
            if($product['ym_attributes']){
                $param_atributes = $this->getParamAttribute($product['ym_attributes'],$template_setting);
            }
            
            $param_options = array();
            if($product['ym_options']){
                $param_options = $this->getParamOption($product['ym_options']);
                
                if($param_options){
                    
                    $param_options_temp = $param_options;
                    
                    $param_options = array();
                    
                    foreach ($param_options_temp as $param_option_value) {
                        $param_options[$param_option_value['name'].'_'.$param_option_value['value']] = $param_option_value;
                    }
                    
                }
            }
            
            if($param_options || $param_atributes){
                $data['param'] = array_merge($param_options,$param_atributes);
            }
            
            if(isset($template_setting['text_capitalize']) && $template_setting['text_capitalize'] && isset($data['param'])){
                
                $param_name = $data['param'];
                
                $data['param'] = array();
                
                foreach ($param_name as $key_param => $value_param) {
                    $data['param'][ $key_param ]['name'] = mb_convert_case($value_param['name'], MB_CASE_TITLE, "UTF-8");
                    $data['param'][ $key_param ]['value'] = mb_convert_case($value_param['value'], MB_CASE_TITLE, "UTF-8");
                    if(isset($value_param['unit'])){
                        
                        $data['param'][ $key_param ]['unit'] = $value_param['unit'];
                        
                    }
                }
                
            }
            
            $param_to_url = array();
            
            //дополнительные элементы
            if(isset($this->general_setting['count_custom_elements'])){
                
                $count_custom_elements = (int)$this->general_setting['count_custom_elements'];
                
                if($count_custom_elements){
                    
                    for($i=0;$i<$count_custom_elements;$i++){
                        
                        $name_element_key = 'custom_elements_name_'.$i;
                        
                        $field_element_key = 'custom_elements_field_'.$i;
                        
                        if(isset($template_setting[$name_element_key]) && $template_setting[$name_element_key]!=='' && isset($template_setting[$field_element_key]) && $template_setting[$field_element_key]['field']['status']!==''){
                            
                            $data['custom_elements'][$template_setting[$name_element_key]] = $this->getNameAttributeForType($product,$template_setting,$template_setting[$field_element_key]['field']['status'],$field_element_key);
                            
                        }
                        
                    }
                    
                    for($i=0;$i<$count_custom_elements;$i++){
                        
                        $name_element_key = 'param_to_url_name_'.$i;
                        
                        $field_element_key = 'param_to_url_value_'.$i;
                        
                        if(isset($template_setting[$name_element_key]) && $template_setting[$name_element_key]!=='' && isset($template_setting[$field_element_key]) && $template_setting[$field_element_key]['field']['status']!==''){
                            
                            $param_to_url[] = $template_setting[$name_element_key].'='.$this->getNameAttributeForType($product,$template_setting,$template_setting[$field_element_key]['field']['status'],$field_element_key);
                            
                        }
                        
                    }
                    
                }
                
            }
            
            if($param_to_url){
                
                $data['url'] .= '&'.implode('&', $param_to_url);
                
            }
            
            $data['bid'] = '';
            
            for($i=0;$i<5;$i++){
                        
                $name_element_key = 'bid_field_to_db_'.$i;

                $field_element_key = 'bid_field_to_db_oper_'.($i-1);

                if($i==0 && isset($template_setting[$name_element_key]['field']['status']) && $template_setting[$name_element_key]['field']['status']){

                    $data['bid'] = $this->getNameAttributeForType($product,$template_setting,$template_setting[$name_element_key]['field']['status'],$name_element_key);;

                }
                
                if(isset($template_setting[$field_element_key]) && isset($template_setting[$name_element_key]['field']['status']) && $template_setting[$name_element_key]['field']['status']){
                    
                    if($template_setting[$field_element_key]=='+'){
                        
                        $data['bid'] = $data['bid'] + $this->getNameAttributeForType($product,$template_setting,$template_setting[$name_element_key]['field']['status'],$name_element_key);;
                        
                    }elseif($template_setting[$field_element_key]=='-'){
                        
                        $data['bid'] = $data['bid'] - $this->getNameAttributeForType($product,$template_setting,$template_setting[$name_element_key]['field']['status'],$name_element_key);;
                        
                    }elseif($template_setting[$field_element_key]=='*'){
                        
                        $data['bid'] = $data['bid'] * $this->getNameAttributeForType($product,$template_setting,$template_setting[$name_element_key]['field']['status'],$name_element_key);;
                        
                    }elseif($template_setting[$field_element_key]=='/'){
                        
                        $data['bid'] = $data['bid'] / $this->getNameAttributeForType($product,$template_setting,$template_setting[$name_element_key]['field']['status'],$name_element_key);;
                        
                    }
                    
                }
                
            }
            
            if($data['bid']>0){
                
                $data['bid'] = round($data['bid']*100);
                
            }
            
            if(isset($product['group_id'])){
                
                $data['group_id'] = $product['group_id'];
                
            }
            
            $data['cbid'] = '';
            
            for($i=0;$i<5;$i++){
                        
                $name_element_key = 'cbid_field_to_db_'.$i;

                $field_element_key = 'cbid_field_to_db_oper_'.($i-1);

                if($i==0 && isset($template_setting[$name_element_key]['field']['status']) && $template_setting[$name_element_key]['field']['status']){

                    $data['cbid'] = $this->getNameAttributeForType($product,$template_setting,$template_setting[$name_element_key]['field']['status'],$name_element_key);;

                }
                
                if(isset($template_setting[$field_element_key]) && isset($template_setting[$name_element_key]['field']['status']) && $template_setting[$name_element_key]['field']['status']){
                    
                    if($template_setting[$field_element_key]=='+'){
                        
                        $data['cbid'] = $data['cbid'] + $this->getNameAttributeForType($product,$template_setting,$template_setting[$name_element_key]['field']['status'],$name_element_key);;
                        
                    }elseif($template_setting[$field_element_key]=='-'){
                        
                        $data['cbid'] = $data['cbid'] - $this->getNameAttributeForType($product,$template_setting,$template_setting[$name_element_key]['field']['status'],$name_element_key);;
                        
                    }elseif($template_setting[$field_element_key]=='*'){
                        
                        $data['cbid'] = $data['cbid'] * $this->getNameAttributeForType($product,$template_setting,$template_setting[$name_element_key]['field']['status'],$name_element_key);;
                        
                    }elseif($template_setting[$field_element_key]=='/'){
                        
                        $data['cbid'] = $data['cbid'] / $this->getNameAttributeForType($product,$template_setting,$template_setting[$name_element_key]['field']['status'],$name_element_key);;
                        
                    }
                    
                }
                
            }
            
            if($data['cbid']>0){
                
                $data['cbid'] = round($data['cbid']*100);
                
            }
            
            if(isset($this->request->get['fb'])){
                
                $data['condition'] = 'new';
                
                if(isset($template_setting['fb_condition'])){
                    
                    $data['condition'] = $template_setting['fb_condition'];
                    
                }
                
            }
            
            if(isset($product['product_to_category']['main_category']) && $product['product_to_category']['main_category']){
                
                $data['categoryId'] = $product['product_to_category']['main_category'];
                
            }else{
                
                $data['categoryId'] = $product['category_id'];
                
            }
            
            //категория
            
            if(!$skip_this_offer && isset($this->general_setting['dall_categories_to_yml']) && $this->general_setting['dall_categories_to_yml']){
                
                
                $this->setProductCategories($data['categoryId']);
                
                
            }
            
            if(isset($this->general_setting['replace_cid_to_product_on_this']) && isset($product['product_to_category']['category_ids'])){
                
                $p_category_ids = $product['product_to_category']['category_ids'];
                
                $replace_cid = FALSE;
                
                foreach ($p_category_ids as $p_category_id) {
                    
                    if(in_array($p_category_id, $this->general_setting['replace_cid_to_product_on_this'])){
                        
                        $replace_cid = TRUE;
                        
                    }
                    
                }
                
                if($replace_cid){
                    
                    
                    foreach ($p_category_ids as $p_category_id_key => $p_category_id) {
                    
                        if(!in_array($p_category_id, $this->general_setting['replace_cid_to_product_on_this'])){

                            unset($p_category_ids[$p_category_id_key]);

                        }

                    }
                    
                    $product['product_to_category']['category_ids'] = $p_category_ids;
                    
                    $data['categoryId'] = current($p_category_ids);
                    
                }
                
            }
            
            if(isset($template_setting['all_product_category']) && $template_setting['all_product_category'] && isset($product['product_to_category']['category_ids'])){
                
                $main_category = $data['categoryId'];
				
                $data['categoryId'] = $product['product_to_category']['category_ids'];
				
                $data['categoryId'][$main_category] = $main_category;
                
            }
	    
	    if(isset($this->general_setting['promos'])){
		
		$this->checkPromosByOfferData($data);
		
	    }
	    
	    if(isset($data['categoryId']) && isset($this->general_setting['mapping_market_place_categories_replace'])){
		
		if(is_array($data['categoryId'])){
		    
		    foreach ($data['categoryId'] as $categoryId => $tmp) {

			if(isset($this->general_setting['mapping_market_place_categories_replace']) && isset($this->general_setting['mapping_market_place_categories_replace'][$categoryId])){

			    $data['categoryId'][$this->general_setting['mapping_market_place_categories_replace'][$categoryId]] = $this->general_setting['mapping_market_place_categories_replace'][$categoryId];

			}

		    }
		    
		}else{
		    
		    if(isset($this->general_setting['mapping_market_place_categories_replace']) && isset($this->general_setting['mapping_market_place_categories_replace'][$data['categoryId']])){

			$data['categoryId'] = $this->general_setting['mapping_market_place_categories_replace'][$data['categoryId']];

		    }
		    
		}
		
	    }
            
            $data['url'] = str_replace(' ', '%20', $data['url']); 
            
            if(isset($template_setting['replace_av_true']) && $template_setting['replace_av_true']!='true' && $data['available']=='true'){
                
                $data['available'] = $template_setting['replace_av_true'];
                
            }
            
            if(isset($template_setting['replace_av_false']) && $template_setting['replace_av_false']!='false' && $data['available']=='false'){
                
                $data['available'] = $template_setting['replace_av_false'];
                
            }
            
            if(!$skip_this_offer){
                
                $this->setOffer($data,$template_setting);
                
                $this->count_offers++;
                
            }else{
                
                $this->count_skips++;
            }
            
            return;
        }
	
	public function checkPromosByOfferData($offer){
	    
	    $offer_id = $offer['id'];
	    
	    $category_ids = array();
	    
	    if(isset($offer['categoryId']) && is_array($offer['categoryId'])){
		
		$category_ids = $offer['categoryId'];
		
	    }elseif(isset($offer['categoryId'])){
		
		$category_ids = array($offer['categoryId']=>$offer['categoryId']);
		
	    }
	    
	    foreach ($this->general_setting['promos'] as $promos_id => $promos) {
		
		if(!isset($promos['product_ids_checked'])){
		    
		    $product_ids_checked = $this->{$this->path_on_model}->cleanExplode(',',$promos['product_ids']);
		    
		    $promos['product_ids_checked'] = $product_ids_checked;
		    
		    $promos['product_ids_selected'] = array();
		    
		}
		
		if(!isset($promos['product_as_gift_ids_checked'])){
		    
		    $product_as_gift_ids_checked = $this->{$this->path_on_model}->cleanExplode(',',$promos['product_as_gift_ids']);
		    
		    $promos['product_as_gift_ids_checked'] = $product_as_gift_ids_checked;
		    
		    $promos['product_as_gift_ids_selected'] = array();
		    
		}
		
		if(in_array($offer_id,$promos['product_ids_checked'])){
		    
		    $promos['product_ids_selected'][$offer_id] = $offer_id;
		    
		}
		
		if(in_array($offer_id,$promos['product_as_gift_ids_checked'])){
		    
		    $promos['product_as_gift_ids_selected'][$offer_id] = $offer_id;
		    
		}
		
		if(!isset($promos['category_ids_checked'])){
		    
		    $promos['category_ids_checked'] = array();
		    
		    $promos['category_ids_selected'] = array();
		    
		}
		
		if(isset($promos['category_ids']) && $promos['category_ids']){
		    
		    $promos['category_ids_checked'] = $promos['category_ids'];
		    
		}
		
		foreach ($promos['category_ids_checked'] as $key => $category_id_checked) {
		    
		    if(isset($category_ids[$category_id_checked])){
			
			$promos['category_ids_selected'][$category_id_checked] = $category_id_checked;
			
			$promos['product_ids_selected'][$offer_id] = $offer_id;
			
			unset($promos['category_ids_checked'][$key]);
			
		    }
		    
		}
		
		$this->general_setting['promos'][$promos_id] = $promos;
		
	    }
	    
	}
        
        public function getParamOption($ym_options) {
            $result = array();
            foreach ($ym_options as $key => $value) {
                $name = $this->prepareField($value['name']);
                $value['unit'] = $this->prepareField($value['unit']);
                $unit = '';
                if($value['unit']){
                    $unit = $value['unit'];
                }
                if(isset($value['product_option_value']) && $value['product_option_value']){
                    foreach ($value['product_option_value'] as $key => $product_option_value) {
                        if(!$unit){
                            $result[] = array('name'=>  $name,'value'=>  $this->prepareField($product_option_value['name']));
                        }else{
                            $result[] = array('name'=>  $name,'value'=>  $this->prepareField($product_option_value['name']),'unit'=>$unit);
                        }
                    }
                }
            }
            return $result;
        }
        
        public function getParamAttribute($ym_attributes,$template_setting=array()) {
            $result = array();
            foreach ($ym_attributes as $key => $value) {
                $value['unit'] = $this->prepareField($value['unit']);
                $unit = '';
                if($value['unit']){
                    $unit = $value['unit'];
                }
                if(isset($value['attribute']) && $value['attribute']){
                    foreach ($value['attribute'] as $key => $attribute) {
                        
                        $unit_result = $unit;
                        
                        if(isset($attribute['unit']) && $attribute['unit']!==''){
                            
                            $unit_result = $attribute['unit'];
                            
                        }
                        
                        if(isset($template_setting['attribute_sintaxis']) && $template_setting['attribute_sintaxis']){
                    
                            $name_attribute = $this->prepareField($attribute['name']);
                            $value_attribute = $this->prepareField($attribute['text']);

                        }else{

                            $name_attribute = $this->prepareField($value['name']);
                            $value_attribute = $this->prepareField($attribute['name'].' '.$attribute['text']);

                        }
                        
                        
                        if(!$unit_result){
                            $result[] = array('name'=>  $name_attribute,'value'=>  $value_attribute);
                        }else{
                            $result[] = array('name'=>  $name_attribute,'value'=>  $value_attribute,'unit'=>$unit_result);
                        }
                    }
                }
            }
            return $result;
        }
        
        public function getNameAttributeForType($product,$template_setting,$composite_types,$field_name) {
            
            $result = '';
            switch ($composite_types){
                case 'attribute_id':
                    if(isset($template_setting[$field_name]['field'][$composite_types])){
                        $attributes_parts = explode('___', $template_setting[$field_name]['field'][$composite_types]);
                        $attribute_group_id = $attributes_parts[0];
                        $attribute_id = $attributes_parts[1];
                        if($product['all_attributes']){
                            foreach ($product['all_attributes'] as $group_attributes) {
                                if($group_attributes['attribute_group_id'] == $attribute_group_id && $group_attributes['attribute']){
                                    foreach ($group_attributes['attribute'] as $attribute_group_value) {
                                        if($attribute_group_value['attribute_id']==$attribute_id){
                                            $result = trim($this->prepareField($attribute_group_value['text']));
                                        }
                                    }

                                }
                            }
                        }
                    }
                    break;
                case 'option_id':
                    if(isset($template_setting[$field_name]['field'][$composite_types])){
                        $option_id = $template_setting[$field_name]['field'][$composite_types];
                        if($product['all_options']){
                            foreach ($product['all_options'] as $option) {
                                if($option['option_id'] == $option_id){

                                    $name_option = trim($this->prepareField($option['name']));
                                    $name_option_vields = array();
                                    foreach ($option['product_option_value'] as $product_option_value) {
                                        $name_option_vields[] = trim($this->prepareField($product_option_value['name']));
                                    }
                                    $result = implode(' ', $name_option_vields);
                                }
                            }
                        }
                    }
                    break;
                case 'manufacturer_id':
                    $product['manufacturer'] = $this->prepareField($product['manufacturer']);
                    if($product['manufacturer']){
                        $result = $product['manufacturer'];
                    }
                    break;

                case 'price':
                    if(isset($this->prices[$product['product_id']])){
                        $result = $this->prices[$product['product_id']];
                    }
                    break;

                case 'weight':
                    if((float)$product['weight']>0){
                        $result = (float)$this->weight->format($product['weight'],$product['weight_class_id']);
                    }
                    break;

                case 'length_width_height':
                    $length_width_height = array();
                    if((float)$product['length']>0){
                        $length_width_height[] = (float)$this->length->format($product['length'],$product['length_class_id']);
                    }
                    if((float)$product['width']>0){
                        $length_width_height[] = (float)$this->length->format($product['width'],$product['length_class_id']);
                    }
                    if((float)$product['height']>0){
                        $length_width_height[] = (float)$this->length->format($product['height'],$product['length_class_id']);
                    }
                    if($length_width_height){
                        $result = implode('/', $length_width_height);
                    }
                    break;
                case 'category_id':
                    if(isset($this->categories[$product['category_id']]['name'])){
                        $result = $this->categories[$product['category_id']]['name'];
                    }
                    break;
                case 'text_field':
                    if(isset($template_setting[$field_name]['field']['text_field'])){
                        $result = $this->prepareField($template_setting[$field_name]['field']['text_field']);
                    }
                    break;
                case 'keywords':
                    if(isset($product['tag'])){
                        $result = str_replace(', ',',', $this->prepareField($product['tag'])) ;
                    }
                    break;
                case 'description_whis_html':
                    if($product['description']){
                        $result = '<![CDATA['. html_entity_decode($product['description']).']]>';
                    }
                    break;
                case 'date_add':
                    if($product['date_added']){
                        $result = $product['date_added'];
                    }
                    break;
                case 'date_mod':
                    if($product['date_modified']){
                        $result = $product['date_modified'];
                    }
                    break;
                    
                    
                case 'product_meta_description':
                        if(isset($product['meta_description'])){
                            $result = $product['meta_description'];
                        }
                    break;
                case 'product_meta_title':
                        if(isset($product['meta_title'])){
                            $result = $product['meta_title'];
                        }
                    break;
                case 'product_meta_keyword':
                        if(isset($product['meta_keyword'])){
                            $result = $product['meta_keyword'];
                        }
                    break; 
                    
                default:
                if(isset($product[$composite_types])){
                    $product[$composite_types] = $this->prepareField($product[$composite_types]);
                    if($product[$composite_types]!==''){
                        $result = $product[$composite_types];
                    }
                }
                break;
            }
            return $result;
        }

        public function getDescriptionAttribute($product,$template_setting){
            
            if(!isset($template_setting['offer_description']['field']) || !$template_setting['offer_description']['field']){
                $description = $this->prepareField($product['description'],TRUE);
                return $description;
            }
            else{
                
                $key = $template_setting['offer_description']['field'];
                
                switch ($key){
                    case 'option_id':
                    $description = '';
                    $option = $this->getParamOption($product['ym_options']);
                    if($option){
                        foreach ($option as $value) {
                            $description .= ' '.implode(' ', $value);
                        }
                    }
                    break;

                    case 'attribute_id':
                    $description = '';
                    $attributes = $this->getParamAttribute($product['ym_attributes']);
                    if($attributes){
                        foreach ($attributes as $value) {
                            $description .= ' '.implode(' ', $value);
                        }
                    }
                    break;

                    case 'meta_title':
                        if(isset($product['meta_title']))
                        $description = $this->prepareField($product['meta_title'],TRUE);
                    break;
                
                    case 'meta_keyword':
                        if(isset($product['meta_keyword']))
                        $description = $this->prepareField($product['meta_keyword'],TRUE);
                    break;

                    case 'meta_description':
                        if(isset($product['meta_description']))
                        $description = $this->prepareField($product['meta_description'],TRUE);
                    break;
                    
                    case 'description':
                        if(isset($product['description']))
                        $description = $this->prepareField($product['description'],TRUE);
                    break;
                    
                    default:
                        $description = '';
                    break;
                }
                return $description;
            }
            
            $result = $this->prepareField($product['name']);
            if(isset($template_setting['offer_name']['field']) && $template_setting['offer_name']['field']=='name'){
                return $result;
            }
            if(isset($template_setting['offer_name']['field']) && $template_setting['offer_name']['field']=='meta_title' && isset($product['meta_title'])){
                $result = $this->prepareField($product['meta_title']);
                return $result;
            }
            if(isset($template_setting['offer_name']['field']) && $template_setting['offer_name']['field']=='composite' && $template_setting['offer_name']['composite']){
                ksort($template_setting['offer_name']['composite']);
                $name = array();
                foreach ($template_setting['offer_name']['composite'] as $composite) {
                    //атрибут
                    if($composite['status']=='attribute_id'){
                        $attributes_parts = explode('___', $composite['attribute_id']);
                        $attribute_group_id = $attributes_parts[0];
                        $attribute_id = $attributes_parts[1];
                        if($product['all_attributes']){
                            foreach ($product['all_attributes'] as $group_attributes) {
                                if($group_attributes['attribute_group_id'] == $attribute_group_id && $group_attributes['attribute']){
                                    foreach ($group_attributes['attribute'] as $attribute_group_value) {
                                        if($attribute_group_value['attribute_id']==$attribute_id){
                                            $name[] = trim($this->prepareField($attribute_group_value['name'])).': '.trim($this->prepareField($attribute_group_value['text']));
                                        }
                                    }
                                    
                                }
                            }
                        }
                    }
                    //опция
                    elseif($composite['status']=='option_id'){
                        $option_id = $composite['option_id'];
                        if($product['all_options']){
                            foreach ($product['all_options'] as $option) {
                                if($option['option_id'] == $option_id){
                                    
                                    $name_option = trim($this->prepareField($option['name']));
                                    $name_option_vields = array();
                                    foreach ($option['product_option_value'] as $product_option_value) {
                                        $name_option_vields[] = trim($this->prepareField($product_option_value['name']));
                                    }
                                    if($name_option_vields){
                                        $name[] = $name_option.': '.  implode(', ', $name_option_vields);
                                    }else{
                                        $name[] = $name_option;
                                    }
                                }
                            }
                        }
                    }
                    //цена
                    elseif($composite['status']=='price'){
                        $name[] = $this->prices[$product['product_id']];
                    }
                    //вес
                    elseif($composite['status']=='weight'){
                        if((float)$product['weight']>0){
                            $name[] = $this->weight->format($product['weight'],$product['weight_class_id']);
                        }
                    }
                    //производитель
                    elseif($composite['status']=='manufacturer_id'){
                        $product['manufacturer'] = $this->prepareField($product['manufacturer']);
                        if($product['manufacturer']){
                            $name[] = $product['manufacturer'];
                        }
                    }
                    //категория
                    elseif($composite['status']=='category_id'){
                        $name[] = $this->categories[$product['category_id']]['name'];
                    }
                    //габариты
                    elseif($composite['status']=='length_width_height'){
                        $length_width_height = array();
                        if((float)$product['length']>0){
                            $length_width_height[] = $this->length->format($product['length'],$product['length_class_id']);
                        }
                        if((float)$product['width']>0){
                            $length_width_height[] = $this->length->format($product['width'],$product['length_class_id']);
                        }
                        if((float)$product['height']>0){
                            $length_width_height[] = $this->length->format($product['height'],$product['length_class_id']);
                        }
                        if($length_width_height){
                            $name[] = implode('/', $length_width_height);
                        }
                    }
                    //остальные
                    elseif (isset ($product[$composite['status']])) {
                        $product[$composite['status']] = $this->prepareField($product[$composite['status']]);
                        if($product[$composite['status']]){
                            $name[] = $product[$composite['status']];
                        }
                    }
                }
                $result = trim($this->prepareField(implode(' ', $name)));
                return $result;
            }
        }
        
        public function getNameAttribute($product,$template_setting,$field_name){
            
            $result = '';
            
            if(isset($template_setting[$field_name]['field']['status']) && $template_setting[$field_name]['field']['status']!='composite'){
                
                $result = $this->getNameAttributeForType($product,$template_setting,$template_setting[$field_name]['field']['status'],$field_name);
                
            }elseif(isset($template_setting[$field_name]['field']['status']) && $template_setting[$field_name]['field']['status']=='composite'){
                
                $field_type = 'composite';
                
                $count_composite_elements = 10;
                
                $result_parts = array();
                
                for($i=1;$i<$count_composite_elements;$i++){
                    
                    if(isset($template_setting[ $field_name.'_'.$field_type.'_'.$i ]) && $template_setting[ $field_name.'_'.$field_type.'_'.$i ]){
                        
                        $result_parts[] = $this->getNameAttributeForType($product,$template_setting,$template_setting[ $field_name.'_'.$field_type.'_'.$i ]['field']['status'],$field_name.'_'.$field_type.'_'.$i);
                        
                    }
                    
                }
                
                if($result_parts){
                    
                    $result = implode(' ', $result_parts);
                    
                }
                
                
            }
            return $result;
        }
        
        public function getTemplateSettingNameComposite() {
            $columns_product_description = $this->db->query('SHOW COLUMNS FROM '.DB_PREFIX.'product_description');
            $columns_product = $this->db->query('SHOW COLUMNS FROM '.DB_PREFIX.'product');

            $template_setting_name_composite['name'] = 'name';
            if($columns_product_description->rows){
                foreach($columns_product_description->rows as $column){
                    if($column['Field']=='meta_title'){
                        $template_setting_name_composite['meta_title'] = 'meta_title';
                    }
                }
            }
            $unset_product_fileds = array_flip(array('quantity','stock_status_id','image','shipping','points','tax_class_id','date_available','weight_class_id','length_class_id','subtract','minimum','sort_order','status','viewed','date_added','date_modified'));
            $product_fileds = array();
            if($columns_product->rows){
                foreach($columns_product->rows as $key=>$column){
                    if(!isset($unset_product_fileds[$column['Field']])){
                        $product_fileds[$column['Field']] = $column['Field'];
                    }
                }
            }
            if(isset($product_fileds['length']) && isset($product_fileds['width']) && isset($product_fileds['height'])){
                unset($product_fileds['length']);
                unset($product_fileds['width']);
                unset($product_fileds['height']);
                $product_fileds['length_width_height'] = 'length_width_height';
            }
            $template_setting_name_composite += $product_fileds;
            $template_setting_name_composite['category_id'] = 'category_id';
            $template_setting_name_composite['option_id'] = 'option_id';
            $template_setting_name_composite['attribute_id'] = 'attribute_id';
            return $template_setting_name_composite;
        }

        public function getDeliveryOption($tamplate_setting,$price='',$weight='',$stock_status_id=''){
            $result = array();
            if(isset($tamplate_setting['delivery-options']['status'])){
                unset($tamplate_setting['delivery-options']['status']);
            }
            foreach ($tamplate_setting['delivery-options'] as $delivery_options) {
                $delivery_options['days'] = trim($delivery_options['days']);
                $delivery_options['order-before'] = trim($delivery_options['order-before']);
                $delivery_options['cost'] = trim($delivery_options['cost']);
                
                $skip = FALSE;
                //var_dump($delivery_options['price_from']);exit();
                if(isset($delivery_options['price_to']) && $delivery_options['price_to']!='' && isset($delivery_options['price_from']) && $delivery_options['price_from']!='' && $price!=''){
                    
                    
                    if($price>=$delivery_options['price_to'] || $price<$delivery_options['price_from']){
                        
                        $skip = TRUE;
                        
                    }
                    
                }elseif(isset($delivery_options['weight_from']) && $delivery_options['weight_from']!='' && isset($delivery_options['weight_to']) && $delivery_options['weight_to']!='' && $weight!=''){
                    
                    if($weight>=$delivery_options['weight_to'] || $weight<$delivery_options['weight_from']){
                        
                        $skip = TRUE;
                        
                    }
                    
                }
                
                if(isset($delivery_options['stock_status_id']) && $stock_status_id!='' && !isset($delivery_options['stock_status_id'][$stock_status_id])){
                    
                    $skip = TRUE;
                    
                }
                if(!$skip && ($delivery_options['cost']!='' || $delivery_options['days']!='' || $delivery_options['order-before']!='')){
                    if($delivery_options['cost']!=''){
                        $option['cost'] = (int)$delivery_options['cost'];
                    }
                    if($delivery_options['days']!=''){
                        $option['days'] = $delivery_options['days'];
                    }
                    if($delivery_options['order-before']!=''){
                        $option['order-before'] = $delivery_options['order-before'];
                    }
                    $result[] = $option;
                    unset($option);
                }
            }
            return $result;
        }
        
        public function getPrice($price,$product,$tamplate_setting){
            
            $result_price = 0.0;
            $price = $this->tax->calculate($price, $product['tax_class_id']);
            $new_price = (float)$price;
            if(isset($tamplate_setting['ymlprice']) && $tamplate_setting['ymlprice']){
                $tamplate_setting['ymlprice'] = (float)$tamplate_setting['ymlprice'];
                $new_price = $new_price + $tamplate_setting['ymlprice']/100*$price;
            }
            
            if($new_price==0.0){
                $result_price =  $price;
            }else{
                $result_price = $new_price;
            }
            
            if(isset($tamplate_setting['price_currencies_from']) && $tamplate_setting['price_currencies_from'] && isset($tamplate_setting['price_currencies_to']) && $tamplate_setting['price_currencies_to']){
                
                $result_price = $this->currency->convert($result_price,$tamplate_setting['price_currencies_from'],$tamplate_setting['price_currencies_to']);
                
            }
            
            return $result_price;
        }
        
        public function getPictureAttributes($product,$tamplate_setting) {
            if(isset($tamplate_setting['count_pictures']) && !$tamplate_setting['count_pictures']){
                return array();
            }elseif(isset($tamplate_setting['count_pictures'])){
                $count_pictures = (int)$tamplate_setting['count_pictures'];
            }else{
                $count_pictures = 1;
            }
            
            $pictures_sizes = 500;
            $default_scale = '';
            $no_cache = FALSE;
            
            if(isset($tamplate_setting['pictures_sizes'])){
                $tamplate_setting['pictures_sizes'] = (int)$tamplate_setting['pictures_sizes'];
                if($tamplate_setting['pictures_sizes']>0){
                    $pictures_sizes = $tamplate_setting['pictures_sizes'];
                }
            }
            if(isset($tamplate_setting['rule_picture'])){
                if($tamplate_setting['rule_picture']=='by_w_side'){
                    $default_scale = 'w';
                }
                elseif($tamplate_setting['rule_picture']=='by_h_side'){
                    $default_scale = 'h';
                }
                elseif($tamplate_setting['rule_picture']=='no_cache'){
                    $no_cache = TRUE;
                }
            }
            $result = array();
            if ($product['image'] && $product['image']!='no_image.jpg' && $product['image']!='no_image.png' && $product['image']!='no-image.jpg' && $product['image']!='no-image.png' && file_exists(DIR_IMAGE.$product['image'])) {
                
                $this->load->model('tool/image');
                if($no_cache){
                    $result[] = $this->HTTP_SERVER.'image/'.$product['image'];
                }else{
                    $result[] = $this->resizeImage($product['image'],$pictures_sizes,$pictures_sizes,$default_scale,  $this->HTTP_SERVER);
                }
                
                if($product['images']){
                    for($i=0;($i<($count_pictures-1) && isset($product['images'][$i]));$i++){
                        if($no_cache && file_exists(DIR_IMAGE.$product['images'][$i]['image'])){
                            $result[] = $this->HTTP_SERVER.'image/'.$product['images'][$i]['image'];
                        }elseif($product['images'][$i]['image']!=$product['image'] && $product['images'][$i]['image'] && file_exists(DIR_IMAGE.$product['images'][$i]['image'])){
                            //$result[] = $this->model_tool_image->resize($product['images'][$i]['image'], $pictures_sizes, $pictures_sizes);
                            $result[] = $this->resizeImage($product['images'][$i]['image'],$pictures_sizes,$pictures_sizes,$default_scale,  $this->HTTP_SERVER);
                        }
                    }
                }
            }
            return $result;
        }
        
        public function resizeImage($file,$w,$h,$d,$HTTP_SERVER) {
            
            $image = $this->{$this->path_on_model}->resizeImage($file,$w,$h,$d,  $HTTP_SERVER);
            
            return $image;
        }

        public function getCategoriesAndOffers($filter_data_group_id,$content_language_id,$setting_yml=array()) {
            
            $ym_categories = array();
            $this->load->model($this->path_oc_version.'/ocext_feed_generator_yamarket');
            
            $all_yml_export_ocext_ym_filter_data_categories = $this->{$this->path_on_model}->getFilterData('ocext_feed_generator_yamarket_ym_filter_category',$filter_data_group_id);
            
	    /*
	    
            foreach ($all_yml_export_ocext_ym_filter_data_categories as $c_id => $crow) {
                
                if(FALSE && isset($crow['disable_parent_child_categories']) && $crow['disable_parent_child_categories']){
                    
                    $this->general_setting['replace_cid_to_product_on_this'][$c_id] = $c_id;
                    
                }
                
            }
	    
	    */
            
            if($all_yml_export_ocext_ym_filter_data_categories){
                $ym_categories = $all_yml_export_ocext_ym_filter_data_categories;
                if($ym_categories){
                    foreach ($ym_categories as $category_id=>$ym_category){
                        if(!isset($ym_category['category_id'])){
                            unset($ym_categories[$category_id]);
                        }
                    }
                    
                    if(!$ym_categories){
                        $ym_categories = $all_yml_export_ocext_ym_filter_data_categories;
                    }
                }
            }
            $ym_manufacturers = array();
            $all_yml_export_ocext_ym_filter_data_manufacturers = $this->{$this->path_on_model}->getFilterData('ocext_feed_generator_yamarket_ym_filter_manufacturers',$filter_data_group_id);
            if($all_yml_export_ocext_ym_filter_data_manufacturers){
                $ym_manufacturers = $all_yml_export_ocext_ym_filter_data_manufacturers;
                if($ym_manufacturers){
                    foreach ($ym_manufacturers as $manufacturer_id=>$ym_manufacturer){
                        if(!isset($ym_manufacturer['manufacturer_id'])){
                            unset($ym_manufacturers[$manufacturer_id]);
                        }
                    }
                    
                    if(!$ym_manufacturers){
                        $ym_manufacturers = $all_yml_export_ocext_ym_filter_data_manufacturers;
                        $ym_manufacturers[''] = array("setting_id"=>"0");
                        $ym_manufacturers[0] = array("setting_id"=>"0");
                    }
                }
            }
            
            
            $this->load->model($this->path_oc_version.'/ocext_feed_generator_yamarket');
            $categories_and_products = $this->{$this->path_on_model}->getCategoriesAndProducts($ym_categories,$ym_manufacturers,$filter_data_group_id,$content_language_id,  $this->general_setting);
            
            if(isset($this->general_setting['replace_cid_to_product_on_this']) && isset($categories_and_products['categories']) && $categories_and_products['categories']){
                
                foreach ($categories_and_products['categories'] as $key => $category_info) {
                    
                    if(in_array($category_info['category_id'], $this->general_setting['replace_cid_to_product_on_this'])){
                        
                        $category_info['replace_cid_to_product_on_this'] = TRUE;
                        
                        $categories_and_products['categories'][$key] = $category_info;
                        
                    }
                    
                }
                
            }
	    
	    $this->general_setting['mapping_market_place_categories_replace'] = array();
	    
	    if(isset($categories_and_products['mapping_market_place_categories'])){
		
		foreach ($categories_and_products['mapping_market_place_categories'] as $mapping_market_place_category) {
		    
		    $categories_and_products['categories'][] = $mapping_market_place_category;
		    
		    $this->general_setting['mapping_market_place_categories_replace'][$mapping_market_place_category['site_category_id']] = $mapping_market_place_category['category_id'];
		    
		}
		
	    }
	    
	    $this->general_setting['settings'] = array();
	    
	    if(isset($categories_and_products['settings'])){
		
		$this->general_setting['settings'] = $categories_and_products['settings'];
		
	    }
            
            if(!isset($setting_yml['products']) || !isset($setting_yml['catalog']) || ( !$categories_and_products[$setting_yml['products']] && !$categories_and_products['cache'] ) || !$categories_and_products[$setting_yml['catalog']]){
                return FALSE;
            }else{
                return $categories_and_products;
            }
        }
        
        private function prepareField($field,$description=FALSE) {
            if(is_string($field) && !$description){
                $field = strip_tags(htmlspecialchars_decode($field));
                $from = array('"', '&', '>', '<', '\'','`','&acute;','™','©');
                $to = array('&quot;', '&amp;', '&gt;', '&lt;', '&apos;','','','','');
                $field = str_replace($from, $to, $field);
                $field = trim($field);
            }elseif(is_string($field) && $description){
                $field = htmlspecialchars_decode($field);
                $from = array('<br>', '</br>','<br />', '<hr>', '\n', '\r','&nbsp;');
                $to = array($this->eol.' ');
                $field = str_replace($from, $to, $field);
                $field = strip_tags($field);
                $from = array('"', '&', '>', '<', '\'','`','&acute;','™','©');
                $to = array('&quot;', '&amp;', '&gt;', '&lt;', '&apos;','','','','');
                $field = str_replace($from, $to, $field);
                $field = trim($field);
            }
            return $field;
	}
        
        private function setShopAttributes($name, $value) {
            $attributes = array('name', 'company', 'url', 'platform', 'version');
            if (in_array($name, $attributes)) {
                    $this->shop[$name] = $this->prepareField($value);
            }
	}
        
        private function setCurrencyAttributes($currency, $rate = 'CBRF') {
            if($currency){
                if(!isset($this->currencies[0])){
                    $this->currencies[] = array(
                        'id'=>$currency,
                        'rate'=>1
                    );
                }else{
                    $this->currencies[] = array(
                        'id'=>$currency,
                        'rate'=>$rate
                    );
                }
                return TRUE;
            }else{
                return FALSE;
            }
	}
        
        private function setCategoryAttrubite($name, $category_id, $parent_id = 0, $category=array()) {
            $name = $this->prepareField($name);
            if(!$category_id || !$name) {
                return;
            }
            
            if($parent_id) {
                $this->categories[$category_id] = array(
                        'id'=>$category_id,
                        'parentId'=>$parent_id,
                        'name'=>$this->prepareField($name)
                );
            }else{
                $this->categories[$category_id] = array(
                    'id'=>$category_id,
                    'name'=>$this->prepareField($name)
                );
            }
            
            if(isset($this->general_setting['add_settings']) && isset($this->general_setting['add_settings']['add_ordering_to_category']) && $this->general_setting['add_settings']['add_ordering_to_category']){
                
                $this->categories[$category_id]['attributes']['ordering'] = (int)$category['sort_order'];
                
            }
            
            if(isset($this->general_setting['add_settings']) && isset($this->general_setting['add_settings']['add_url_to_category']) && $this->general_setting['add_settings']['add_url_to_category']){
                
                $this->categories[$category_id]['attributes']['url'] = $this->prepareField($this->url->link('product/category', 'category_id='.$category_id));
                
            }
            
	}
        
        protected function getPathWhisCategories($category_id,$old_path = '') {
            if (isset($this->categories[$category_id])) {
                if (!$old_path) {
                    $new_path = $this->categories[$category_id]['id'];
                } else {
                    $new_path = $this->categories[$category_id]['id'].'_' .$old_path;
                }	
                if (isset($this->categories[$category_id]['parentId'])) {
                    return $this->getPathWhisCategories($this->categories[$category_id]['parentId'], $new_path);
                } else {
                    return $new_path;
                }
            }
	}
        
        
        private function setOffer($data,$template_setting=array()) {
            
            $optimization_file_feed_write = 0;
            
            $memory_limit = 0;
            
            if(isset($this->general_setting['optimization_file_feed_write']) && $this->general_setting['optimization_file_feed_write']){
                
                $optimization_file_feed_write = $this->general_setting['optimization_file_feed_write'];
                
                $this->load->model($this->path_oc_version.'/ocext_feed_generator_yamarket');
            
                $memory_limit = $this->{$this->path_on_model}->getMemoryLimit($this->memory_limit_on_fwrite);
                
            }
            
            $custom_elements = array();
            
            if(isset($data['custom_elements'])){
                
                $custom_elements = $data['custom_elements'];
                
            }
            
            $offer = array();
            $attributes = array('id', 'type', 'available', 'bid', 'cbid', 'param', 'fee', 'group_id');
            $attributes = array_intersect_key($data, array_flip($attributes));
            
            foreach ($attributes as $key => $value) {
                switch ($key){
                    case 'id':
                    if ($value > 0) {
                            $offer[$key] = $value;
                    }
                    case 'cbid':
                    if ($value!='') {
                            $offer[$key] = $value;
                    }
                    case 'bid':
                    if ($value!='') {
                            $offer[$key] = $value;
                    }
                    break;
                    
                    case 'fee':
                    $value = (int)$value;
                    if ($value > 0) {
                            $offer[$key] = $value;
                    }
                    break;

                    case 'type':
                    if (in_array($value, array('vendor.model'))) {
                            $offer['type'] = $value;
                    }
                    break;

                    case 'available':
                    //$offer['available'] = (($value=='true') ? 'true' : 'false');
                    $offer['available'] = $value;
                    break;

                    case 'param':
                    if (is_array($value)) {
                        $offer['param'] = $value;
                    }
                    break;
                    
                    case 'group_id':
                    $offer['group_id'] = (int)$value;
                    break;
                
                    default:
                    break;
                }
            }
            $type = isset($offer['type']) ? $offer['type'] : '';
            $finded_tags = array('url'=>0, 'price'=>0);
            
            if(isset($data['oldprice']) && $data['oldprice']>0){
                $finded_tags['oldprice'] = 1;
            }
            
            $finded_tags = array_merge($finded_tags,array('currencyId'=>1, 'categoryId'=>1,'market_category'=>0, 'picture'=>0, 'store'=>0, 'pickup'=>0, 'delivery'=>0,'delivery-options'=>0));
            
            switch ($type) {
                case 'vendor.model':
                    $finded_tags = array_merge($finded_tags, array('name'=>0,'vendor'=>0, 'vendorCode'=>0, 'model'=>0));
                    break;
                default:
                    $finded_tags = array_merge($finded_tags, array('name'=>0, 'vendor'=>0, 'vendorCode'=>0, 'model'=>0));
                    break;
            }
            $finded_tags = array_merge($finded_tags, array('description'=>0, 'condition'=>0 , 'typePrefix'=>0, 'sales_notes'=>0, 'manufacturer_warranty'=>0, 'country_of_origin'=>0, 'adult'=>0, 'barcode'=>0, 'weight'=>0,'description'=>0, 'dimensions'=>0, 'age'=>0, 'cpa'=>0, 'rec'=>0));
            $requiredes = array_filter($finded_tags);
            if (sizeof(array_intersect_key($data, $requiredes)) != sizeof($requiredes)) {
                    return;
            }
            $data = array_intersect_key($data, $finded_tags);
            $finded_tags = array_intersect_key($finded_tags, $data);
            $offer['data'] = array();
            foreach ($finded_tags as $key => $value) {
                    $offer['data'][$key] = $this->prepareField($data[$key]);
            }
            
            if($custom_elements){
                
                foreach ($custom_elements as $key => $value) {
                    $offer['data'][$key] = $value;
                }
            }
            
            if(isset($template_setting['replace_tags']) && $template_setting['replace_tags']!==''){
                
                $replace_tags_parts = explode('|', $template_setting['replace_tags']);
                
                $replace_tags = array();
                
                foreach ($replace_tags_parts as $replace_tags_part) {
                    
                    $replace_tags_this = explode('---', $replace_tags_part);
                    
                    if(isset($replace_tags_this[1]) && $replace_tags_this[1] && $replace_tags_this[0]){
                        
                        $replace_tags[$replace_tags_this[0]] = $replace_tags_this[1];
                        
                    }
                    
                }
                
                if($replace_tags){
                    
                    foreach ($offer['data'] as $tag_name => $tmp) {
                        
                        if(isset($replace_tags[$tag_name])){
                            
                            $offer['data'][$replace_tags[$tag_name]] = $tmp;
                            unset($offer['data'][$tag_name]);
                            
                        }
                        
                    }
                    
                }
                
            }
            
            if($optimization_file_feed_write){
             
                $memory_get_usage = memory_get_usage();
                
                $this->offers_fwrire['offers'][] = $offer;
                
                if($memory_get_usage >= $memory_limit){

                    $fname = $this->cfiles['offers_cache'].$this->request->get['token'].'-'.  count($this->offers_fwrire['cache_files']).'.txt';
                    
                    $this->{$this->path_on_model}->writeCache($fname, $this->offers_fwrire['offers']);
                    
                    $this->offers_fwrire['offers'] = array();

                    $this->offers_fwrire['cache_files'][$fname] = $fname;

                }
                
            }else{
                
                $this->offers[] = $offer;
                
            }
	}
        
        
        private function getYml() {
            
            if($this->offers_fwrire['offers'] || $this->offers_fwrire['cache_files']){
                
                $xml_parts = array();
                
                $result = '';
                
                if($this->offers_fwrire['offers']){
                    
                    $fname = $this->cfiles['offers_cache'].$this->request->get['token'].'-'.  count($this->offers_fwrire['cache_files']).'.txt';
                    
                    $this->{$this->path_on_model}->writeCache($fname, $this->offers_fwrire['offers']);

                    $this->offers_fwrire['offers'] = array();

                    $this->offers_fwrire['cache_files'][$fname] = $fname;
                    
                }
                
                //$this->offers_fwrire['offers']
                
                if(isset($this->request->get['fb'])){
                    
                    $fb  = '<?xml version="1.0"?>' . $this->eol;
                    $fb .= '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">' . $this->eol;
                    $fb .= '<title>'.$this->shop['name'].'</title>'. $this->eol;
                    $fb .= $this->createFBTag(array('link'=>''),array('link'=>array('rel'=>'self','href'=>$this->shop['url'])));
                    
                    $fb_parts_cache = $this->cfiles['fb_parts_cache'].$this->request->get['token'].'-'.  count($xml_parts);
                    
                    $fb = $this->replaceTags($fb);
                    
                    $this->{$this->path_on_model}->writeCache($fb_parts_cache,array(), $fb);
                    
                    $xml_parts[$fb_parts_cache] = $fb_parts_cache;
                    
                    $fb = '';
                    
                    if($this->offers_fwrire['cache_files']){
                        
                        foreach ($this->offers_fwrire['cache_files'] as $cache_file) {
                            
                            $offers = $this->getCache($cache_file);
                            
                            foreach ($offers as $num_offer=>$offer) {
                                
                                if(isset($offer['id']) && $offer['id'] && isset($offer['available']) && $offer['available'] && isset($offer['data']['description']) && $offer['data']['description'] && isset($offer['data']['vendor']) && $offer['data']['vendor'] && isset($offer['data']['picture']) && $offer['data']['picture'] && isset($offer['data']['price']) && $offer['data']['price'] && isset($offer['data']['name']) && $offer['data']['name']){

                                    $fb_data = array();

                                    $fb_data['id'] = $offer['id'];

                                    $fb_data['availability'] = 'out of stock';

                                    if($offer['available']=='true'){

                                        $fb_data['availability'] = 'in stock';

                                    }

                                    $fb_data['condition'] = $offer['data']['condition'];

                                    $fb_data['description'] = $offer['data']['description'];

                                    foreach ($offer['data']['picture'] as $picture) {

                                        if(!isset($fb_data['image_link'])){

                                            $fb_data['image_link'] = $picture;

                                        }else{

                                            $fb_data['additional_image_link'] = $picture;

                                        }

                                    }
                                    
                                    if(isset($offer['data']['google_product_category'])){

                                        $fb_data['google_product_category'] = $offer['data']['google_product_category'];

                                    }else{
                                        
                                        unset($fb_data['google_product_category']);
                                        
                                    }

                                    $fb_data['link'] = $offer['data']['url'];

                                    $fb_data['title'] = $offer['data']['name'];

                                    $fb_data['price'] = number_format(round($offer['data']['price'],2),2, '.', '').' '.$offer['data']['currencyId'];

                                    $fb_data['brand'] = $offer['data']['vendor'];

                                    for($i=0;$i<5;$i++){

                                        if(isset($offer['data']['custom_label_'.$i])){

                                            $fb_data['custom_label_'.$i] = $offer['data']['custom_label_'.$i];

                                        }

                                    }

                                    $rows = $this->createFBTag($fb_data,array(),'g:');

                                    $fb .= $this->getFBElement(array(), 'entry', $rows);

                                }
                            }
                            
                            if($fb){
                                
                                $fb_parts_cache = $this->cfiles['fb_parts_cache'].$this->request->get['token'].'-'.  count($xml_parts);
                            
                                $fb = $this->replaceTags($fb);
                                
                                $this->{$this->path_on_model}->writeCache($fb_parts_cache,array(), $fb);

                                $xml_parts[$fb_parts_cache] = $fb_parts_cache;

                                $fb = '';
                                
                            }
                            
                        }
                        
                    }
                    
                    $fb .= '</feed>';
                
                    $fb = $this->replaceTags($fb);
                    
                    $fb_parts_cache = $this->cfiles['fb_parts_cache'].$this->request->get['token'].'-'.  count($xml_parts);

                    $this->{$this->path_on_model}->writeCache($fb_parts_cache,array(), $fb);

                    $xml_parts[$fb_parts_cache] = $fb_parts_cache;

                    $rootPath = realpath(DIR_APPLICATION . '..'); 

                    if($xml_parts && isset($this->general_setting['fb_filename_export']) && $this->general_setting['fb_filename_export']){

                        $file_name_and_path = $rootPath.'/'.$this->general_setting['fb_filename_export'].'.xml';

                        if(file_exists($file_name_and_path)){
                            
                            $handle = fopen($file_name_and_path, 'w+');

                            fclose($handle);
                            
                        }

                        foreach ($xml_parts as $xml_part) {

                            if(file_exists(DIR_CACHE.$xml_part) && is_writable($file_name_and_path)){

                                $cache_offers = file_get_contents(DIR_CACHE.$xml_part);

                                $handle = fopen($file_name_and_path, 'a');

                                fwrite($handle, $cache_offers . $this->eol);

                                fclose($handle);

                            }else{

                                $this->sendErrorXML(array('Файл не может быть записан. Выключите "Оптимизацию при формировании итогового YML (запись файла частями). Если is_writable = 0 (см. далее), то установите права на запись 755 для папки: '.$this->eol. dirname($file_name_and_path).$this->eol.' is_writable: '.is_writable($file_name_and_path).', cache file_exists: '.file_exists(DIR_CACHE.$xml_part)));

                            }

                        }

                    }
                    
                }else{
                    
                    $yml  = '<?xml version="1.0" encoding="UTF-8"?>' . $this->eol;
                    $yml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . $this->eol;
                    $yml .= '<yml_catalog date="' . date('Y-m-d H:i') . '">' . $this->eol;
                    $yml .= '<shop>' . $this->eol;
                    $yml .= $this->createTag($this->shop);
                    $yml .= '<currencies>' . $this->eol;
                    foreach ($this->currencies as $currency) {
                            $yml .= $this->getElement($currency, 'currency');
                    }
                    $yml .= '</currencies>' . $this->eol;
                    $yml .= '<categories>' . $this->eol;
                    foreach ($this->categories as $category) {
                            $category_name = $category['name'];
                            
                            if(isset($category['attributes'])){
                                
                                $category = array_merge($category,$category['attributes']);
                                unset($category['attributes']);
                                
                            }
                            
                            unset($category['name'], $category['export']);
                            $yml .= $this->getElement($category, 'category', $category_name);
                    }
                    $yml .= '</categories>' . $this->eol;
                    if($this->delivery_option){
                        $yml .= $this->createTag($this->delivery_option);
                    }
                    $yml .= '<offers>' . $this->eol;
                    
                    $yml_parts_cache = $this->cfiles['yml_parts_cache'].$this->request->get['token'].'-'.  count($xml_parts);
                    
                    $yml = $this->replaceTags($yml);
                    
                    $this->{$this->path_on_model}->writeCache($yml_parts_cache,array(), $yml);
                    
                    $xml_parts[$yml_parts_cache] = $yml_parts_cache;
                    
                    $yml = '';
                    
                    if($this->offers_fwrire['cache_files']){
                        
                        foreach ($this->offers_fwrire['cache_files'] as $cache_file) {
                            
                            $offers = $this->getCache($cache_file);
                            
                            foreach ($offers as $num_offer=>$offer) {
                                
                                $rows = $this->createTag($offer['data']);
                                unset($offer['data']);
                                if (isset($offer['param'])) {
                                    $rows .= $this->createParam($offer['param']);
                                    unset($offer['param']);
                                }
                                $yml .= $this->getElement($offer, 'offer', $rows);
                                unset($this->offers[$num_offer]);
                                
                            }
                            
                            if($yml){
                                
                                $yml = $this->replaceTags($yml);
                                
                                $yml_parts_cache = $this->cfiles['yml_parts_cache'].$this->request->get['token'].'-'.  count($xml_parts);
                            
                                $this->{$this->path_on_model}->writeCache($yml_parts_cache,array(), $yml);

                                $xml_parts[$yml_parts_cache] = $yml_parts_cache;

                                $yml = '';
                                
                            }
                            
                        }
                        
                    }
                    
                    $yml .= '</offers>' . $this->eol;
			    
		    if(isset($this->general_setting['gifts_result'])){
			
			$yml .= $this->general_setting['gifts_result'];
			
		    }
		    
		    if(isset($this->general_setting['promos_result'])){
			
			$yml .= '<promos>' . $this->eol;
			
			foreach ($this->general_setting['promos_result'] as $promos_result) {
			    
			    $yml .= $promos_result;
			    
			}
			
			$yml .= '</promos>' . $this->eol;
			
		    }
                    
                    $yml .= '</shop>';
                    
                    $yml .= '</yml_catalog>';
                    
                    $yml = $this->replaceTags($yml);
                    
                    $yml_parts_cache = $this->cfiles['yml_parts_cache'].$this->request->get['token'].'-'.  count($xml_parts);
                    
                    $this->{$this->path_on_model}->writeCache($yml_parts_cache,array(), $yml);
                    
                    $xml_parts[$yml_parts_cache] = $yml_parts_cache;
                    
                    $rootPath = realpath(DIR_APPLICATION . '..'); 

                    if($xml_parts && isset($this->general_setting[$this->parse_yml['export_filename']]) && $this->general_setting[$this->parse_yml['export_filename']]){

                        $file_name_and_path = $rootPath.'/'.$this->general_setting['filename_export'].'.xml';
                        
                        if(file_exists($file_name_and_path)){
                            
                            $handle = fopen($file_name_and_path, 'w+');

                            fclose($handle);
                            
                        }

                        foreach ($xml_parts as $xml_part) {

                            if(file_exists(DIR_CACHE.$xml_part) && is_writable($file_name_and_path)){

                                $cache_offers = file_get_contents(DIR_CACHE.$xml_part);

                                $handle = fopen($file_name_and_path, 'a');

                                fwrite($handle, $cache_offers . $this->eol);

                                fclose($handle);

                            }else{

                                $this->sendErrorXML(array('Файл не может быть записан. Выключите "Оптимизацию при формировании итогового YML (запись файла частями). Если is_writable = 0 (см. далее), то установите права на запись 755 для папки: '.$this->eol. dirname($file_name_and_path).$this->eol.' is_writable: '.is_writable($file_name_and_path).', cache file_exists: '.file_exists(DIR_CACHE.$xml_part)));

                            }

                        }

                    }
                    
                }
                
                if(file_exists($file_name_and_path) && is_writable($file_name_and_path)){
                        
                    $result = file_get_contents($file_name_and_path);

                }else{
                    
                    $this->sendErrorXML(array('Файл не записан, is_writable: '.is_writable($file_name_and_path).', cache file_exists: '.file_exists(DIR_CACHE.$xml_part)));
                    
                }
                
                return $result;
                
            }elseif(isset($this->request->get['fb'])){
                
                $fb  = '<?xml version="1.0"?>' . $this->eol;
		$fb .= '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">' . $this->eol;
                $fb .= '<title>'.$this->shop['name'].'</title>'. $this->eol;
		$fb .= $this->createFBTag(array('link'=>''),array('link'=>array('rel'=>'self','href'=>$this->shop['url'])));
                
		foreach ($this->offers as $num_offer=>$offer) {
                    
                        if(isset($offer['id']) && $offer['id'] && isset($offer['available']) && $offer['available'] && isset($offer['data']['description']) && $offer['data']['description'] && isset($offer['data']['vendor']) && $offer['data']['vendor'] && isset($offer['data']['picture']) && $offer['data']['picture'] && isset($offer['data']['price']) && $offer['data']['price'] && isset($offer['data']['name']) && $offer['data']['name']){
                            
                            $fb_data = array();
                            
                            $fb_data['id'] = $offer['id'];
                            
                            $fb_data['availability'] = 'out of stock';
                            
                            if($offer['available']=='true'){
                                
                                $fb_data['availability'] = 'in stock';
                                
                            }
                            
                            $fb_data['condition'] = $offer['data']['condition'];
                            
                            $fb_data['description'] = $offer['data']['description'];
                            
                            foreach ($offer['data']['picture'] as $picture) {
                                
                                if(!isset($fb_data['image_link'])){
                                    
                                    $fb_data['image_link'] = $picture;
                                    
                                }else{
                                    
                                    $fb_data['additional_image_link'] = $picture;
                                    
                                }
                                
                            }
                            
                            if(isset($offer['data']['google_product_category'])){

                                $fb_data['google_product_category'] = $offer['data']['google_product_category'];

                            }else{

                                unset($fb_data['google_product_category']);

                            }
                            
                            $fb_data['link'] = $offer['data']['url'];
                            
                            $fb_data['title'] = $offer['data']['name'];
                            
                            $fb_data['price'] = number_format(round($offer['data']['price'],2),2, '.', '').' '.$offer['data']['currencyId'];
                            
                            $fb_data['brand'] = $offer['data']['vendor'];
                            
                            for($i=0;$i<5;$i++){
                                
                                if(isset($offer['data']['custom_label_'.$i])){
                                    
                                    $fb_data['custom_label_'.$i] = $offer['data']['custom_label_'.$i];
                                    
                                }
                                
                            }
                            
                            $rows = $this->createFBTag($fb_data,array(),'g:');
                            
                            $fb .= $this->getFBElement(array(), 'entry', $rows);
                            
                        }
                        
                        unset($this->offers[$num_offer]);
                        
		}
		$fb .= '</feed>';
                
                $fb = $this->replaceTags($fb);
                
                $rootPath = realpath(DIR_APPLICATION . '..'); 
                
                if(isset($this->general_setting['fb_filename_export']) && $this->general_setting['fb_filename_export']){
                    
                    $file_name_and_path = $rootPath.'/'.$this->general_setting['fb_filename_export'].'.xml';
                    
                    $dirname = dirname($file_name_and_path);

                    $handle = FALSE;

                    if($file_name_and_path && !file_exists($file_name_and_path)){

                        if(!is_dir($dirname)){

                            mkdir($dirname,0777,TRUE);

                        }

                        $handle = fopen($file_name_and_path, "w");

                    }elseif(file_exists($file_name_and_path)){

                        $handle = fopen($file_name_and_path, "w");

                    }

                    if($handle){
                        fwrite($handle, $fb);
                        fclose($handle);
                    }
                    
                }
		return $fb;
                
            }else{
                
                $yml  = '<?xml version="1.0" encoding="UTF-8"?>' . $this->eol;
		$yml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . $this->eol;
		$yml .= '<yml_catalog date="' . date('Y-m-d H:i') . '">' . $this->eol;
		$yml .= '<shop>' . $this->eol;
		$yml .= $this->createTag($this->shop);
		$yml .= '<currencies>' . $this->eol;
		foreach ($this->currencies as $currency) {
			$yml .= $this->getElement($currency, 'currency');
		}
		$yml .= '</currencies>' . $this->eol;
		$yml .= '<categories>' . $this->eol;
		foreach ($this->categories as $category) {
			$category_name = $category['name'];
			
			if(isset($category['attributes'])){

			    $category = array_merge($category,$category['attributes']);
			    unset($category['attributes']);

			}
			
			unset($category['name'], $category['export']);
			$yml .= $this->getElement($category, 'category', $category_name);
		}
		$yml .= '</categories>' . $this->eol;
                if($this->delivery_option){
                    $yml .= $this->createTag($this->delivery_option);
                }
		$yml .= '<offers>' . $this->eol;
		foreach ($this->offers as $num_offer=>$offer) {
			$rows = $this->createTag($offer['data']);
			unset($offer['data']);
			if (isset($offer['param'])) {
                            $rows .= $this->createParam($offer['param']);
                            unset($offer['param']);
			}
			$yml .= $this->getElement($offer, 'offer', $rows);
                        unset($this->offers[$num_offer]);
		}
		$yml .= '</offers>' . $this->eol;
		$yml .= '</shop>';
		$yml .= '</yml_catalog>';
                
                $yml = $this->replaceTags($yml);
                
                //пишем файл
                $rootPath = realpath(DIR_APPLICATION . '..'); 
                
                if(isset($this->general_setting[$this->parse_yml['export_filename']]) && $this->general_setting[$this->parse_yml['export_filename']]){
                    
                    
                    $file_name_and_path = $rootPath.'/'.$this->general_setting['filename_export'].'.xml';
                
                    $dirname = dirname($file_name_and_path);

                    $handle = FALSE;

                    if($file_name_and_path && !file_exists($file_name_and_path)){

                        if(!is_dir($dirname)){

                            mkdir($dirname,0777,TRUE);

                        }

                        $handle = fopen($file_name_and_path, "w");

                    }elseif(file_exists($file_name_and_path)){

                        $handle = fopen($file_name_and_path, "w");

                    }

                    if($handle){
                        fwrite($handle, $yml);
                        fclose($handle);
                    }
                    
                }
		return $yml;
                
            }
	}
        
        
        public function replaceTags($string){
            
            if(isset($this->general_setting['filter_and_raplace_tags']['replace_tags']) && $this->general_setting['filter_and_raplace_tags']['replace_tags']!==''){
                
                $replace_tags_parts = explode('|', $this->general_setting['filter_and_raplace_tags']['replace_tags']);
                
                $find_tags = array();
                
                $replace_tags = array();
                
                $num_replace = 0;
                
                foreach ($replace_tags_parts as $replace_tags_part) {
                    
                    $replace_tags_this = explode('---', $replace_tags_part);
                    
                    if(isset($replace_tags_this[1]) && $replace_tags_this[1] && $replace_tags_this[0]){
                        
                        $find_tags[] = '<'.$replace_tags_this[0].' ';
                        
                        $replace_tags[] = '<'.$replace_tags_this[1].' ';
                        
                        $find_tags[] = '</'.$replace_tags_this[0].'>';
                        
                        $replace_tags[] = '</'.$replace_tags_this[1].'>';
                        
                        $find_tags[] = '<'.$replace_tags_this[0].'>';
                        
                        $replace_tags[] = '<'.$replace_tags_this[1].'>';
                        
                    }
                    
                }
                
                if($replace_tags){
                    
                    $string = str_replace($find_tags, $replace_tags, $string);
                    
                }
                
            }
            
            return $string;
            
        }
        
        private function getFBElement($attributes, $element_name, $element_value = '') {
            $retval = '<'.$element_name.' ';
            foreach ($attributes as $key => $value) {
                $retval .= $key .'="'.$value.'" ';
            }
            $retval .= $element_value ? '>' .$this->eol. $element_value.'</'.$element_name.'>' : '/>';
            $retval .= $this->eol;
            return $retval;
	}
        
	private function createFBTag($tags,$attributes=array(),$tag_add='') {
            
            $retval = '';
            foreach ($tags as $key => $value) {
                
                $attribute = '';
                
                if(isset($attributes[$key])){
                    
                    foreach ($attributes[$key] as $attribute_name => $attribute_value) {
                        
                        $attribute .= ' '.  $attribute_name.'="'.  $attribute_value.'"';
                        
                    }
                    
                }
                
                if($tag_add){
                    $key = $tag_add.$key;
                }
                
                
                if(!is_array($value) && $value){
                    $retval .= '<'.$key.$attribute.'>'.$value.'</'.$key .'>'.$this->eol;
                }elseif (is_array($value) && $key == $tag_add.'shipping') {
                    $retval .= $this->createDeliveryOptions($value); 
                }elseif (is_array($value)) {
                    foreach ($value as $key_two=>$value_two) {
                        $retval .= '<'.$key.$attribute.'>'.$value_two.'</'.$key.'>' . $this->eol;
                    }
                }elseif(!is_array($value) && !$value){
                    $retval .= '<'.$key.$attribute.'/>'. $this->eol;
                }
            }
            return $retval;
	}
        
        public function checklicense() {
            //asju
            eval(base64_decode('JGdlbmVyYWxfc2V0dGluZyA9ICR0aGlzLT5jb25maWctPmdldCgnb2NleHRfZmVlZF9nZW5lcmF0b3JfeWFtYXJrZXRfZ2VuZXJhbF9zZXR0aW5nJyk7DQogICAgICAgICAgICBpZihpc3NldCgkZ2VuZXJhbF9zZXR0aW5nWyd1c2VyX2tleSddKSAmJiBpc3NldCgkZ2VuZXJhbF9zZXR0aW5nWyd1c2VyX2VtYWlsJ10pKXsNCiAgICAgICAgICAgICAgICAkdGhpcy0+bF9kYXRhWyd1J10gPSAkZ2VuZXJhbF9zZXR0aW5nWyd1c2VyX2tleSddOw0KICAgICAgICAgICAgICAgICR0aGlzLT5sX2RhdGFbJ2UnXSA9ICRnZW5lcmFsX3NldHRpbmdbJ3VzZXJfZW1haWwnXTsNCiAgICAgICAgICAgIH1lbHNlew0KICAgICAgICAgICAgICAgICR0aGlzLT5sX2RhdGFbJ3UnXSA9IG1kNSh0aW1lKCkpOw0KICAgICAgICAgICAgICAgICR0aGlzLT5sX2RhdGFbJ2UnXSA9IHRpbWUoKTsNCiAgICAgICAgICAgIH0='));//OCext.com: do not delete this line | не удаляйте эту строку
            //indjds
            eval(base64_decode('aWYoZmlsZV9leGlzdHMoJF9TRVJWRVJbJ0RPQ1VNRU5UX1JPT1QnXS4nL3N5c3RlbS9saWJyYXJ5L3ZlbmRvci9vY2V4dC9vY2V4dF9mZWVkX2dlbmVyYXRvcl95YW1hcmtldF9saWNlbnNlLnBocCcpKXsNCiAgICAgICAgICAgICAgICBpbmNsdWRlX29uY2UgJF9TRVJWRVJbJ0RPQ1VNRU5UX1JPT1QnXS4nL3N5c3RlbS9saWJyYXJ5L3ZlbmRvci9vY2V4dC9vY2V4dF9mZWVkX2dlbmVyYXRvcl95YW1hcmtldF9saWNlbnNlLnBocCc7DQogICAgICAgICAgICB9'));//OCext.com: do not delete this line | не удаляйте эту строку
        }

	private function getElement($attributes, $element_name, $element_value = '') {
            $retval = '<'.$element_name.' ';
            foreach ($attributes as $key => $value) {
                $retval .= $key .'="'.$value.'" ';
            }
            $retval .= $element_value ? '>' .$element_value.'</'.$element_name.'>' : '/>'.$this->eol;
            return $retval;
	}
	
	private function getXMLFromArray($value,$result) {
	    
	    $result = '';
	    
	    if(is_array($value)){
		
		foreach ($value as $tag_name => $value2) {
		    
		    foreach ($attributes as $key => $value) {
			
			$result .= $key .'="'.$value.'" ';
			
		    }
		    
		}
		
	    }
	    
	    
	    
            $retval = '<'.$element_name.' ';
            foreach ($attributes as $key => $value) {
                $retval .= $key .'="'.$value.'" ';
            }
            $retval .= $element_value ? '>' .$element_value.'</'.$element_name.'>' : '/>'.$this->eol;
            return $retval;
	}
        
	private function createTag($tags) {
            $retval = '';
            foreach ($tags as $key => $value) {
                
                if(!is_array($value) && $value!==''){
                    
                    $key_parts = explode(' ', $key);
                    
                    $key_attr = '';
                    
                    if(isset($key_parts[1])){
                        
                        $key = trim($key_parts[0]);
                        
                        for($kp=1;$kp<=count($key_parts);$kp++){
                            
                            if(isset($key_parts[$kp])){
                                
                                $key_parts[$kp] = trim($key_parts[$kp]);
                        
                                if($key_parts[$kp]){
                                    $key_attr .= ' '.  str_replace("&quot;", '"', $key_parts[$kp]);
                                }
                                
                            }
                            
                        }
                        
                    }
                    
                    $retval .= '<'.$key.$key_attr.'>'.$value.'</'.$key .'>'.$this->eol;
                    
                }elseif (is_array($value) && $key == 'delivery-options') {
                    $retval .= $this->createDeliveryOptions($value); 
                }elseif (is_array($value) && $key == 'age') {
                    $retval .= '<'.$key.' unit="'.$value['unit'].'">'.$value['value'].'</'.$key.'>' . $this->eol;
                }elseif (is_array($value)) {
                    foreach ($value as $key_two=>$value_two) {
                        $retval .= '<'.$key.'>'.$value_two.'</'.$key.'>' . $this->eol;
                    }
                }elseif (!is_array($value) && $value==='') {
                    
                    $key_parts = explode(' ', $key);
                    
                    $key_attr = '';
                    
                    if(isset($key_parts[1])){
                        
                        $key = trim($key_parts[0]);
                        
                        for($kp=1;$kp<=count($key_parts);$kp++){
                            
                            if(isset($key_parts[$kp])){
                                
                                $key_parts[$kp] = trim($key_parts[$kp]);
                        
                                if($key_parts[$kp]){
                                    $key_attr = ' '.  str_replace("&quot;", '"', $key_parts[$kp]);
                                }
                                
                            }
                            
                        }
                        
                    }
                    
                    if($key_attr){
                        $retval .= '<'.$key.$key_attr.'></'.$key.'>'.$this->eol;
                    }
                    
                }
            }
            return $retval;
	}
        
	private function createParam($params) {
            $retval = '';
            foreach ($params as $param) {
                $retval .= '<param name="'.$this->prepareField($param['name']);
                if (isset($param['unit'])) {
                        $retval .= '" unit="'.$this->prepareField($param['unit']);
                }
                $retval .= '">'.$this->prepareField($param['value']) . '</param>'.$this->eol;
            }
            return $retval;
	}
        
        private function createDeliveryOptions($delivery_options) {
            $retval = '';
            if($delivery_options){
                foreach ($delivery_options as $option) {
                    if(isset($option['cost']) && isset($option['days'])){
                        $retval .= '<option cost="' . trim($option['cost']).'" days="'.trim($option['days']).'';
                        if (isset($option['order-before']) && $option['order-before']) {
                                $retval .= '" order-before="' . trim($option['order-before']);
                                unset($option['order-before']);
                        }
                        $retval .= '"/>'.$this->eol;
                    }
                }
            }
            if($retval){
                $retval = '<delivery-options>'.$this->eol.$retval.'</delivery-options>'.$this->eol;
            }
            
            return $retval;
	}
        
        public function sendErrorXML($errors) {
            $yml  = '<?xml version="1.0" encoding="UTF-8"?>' . $this->eol;
            $yml .= '<msgs date="' . date('Y-m-d H:i') . '">' . $this->eol;
            foreach ($errors as $error) {
                $yml .= $this->createTag(array('msg'=>$error)) . $this->eol;
            }
            $yml .= '</msgs>';
            $this->response->addHeader('Content-Type: application/xml');
            $this->response->setOutput($yml);
        }
}
?>