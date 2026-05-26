<?php
require_once ( "ParserProject.php" );
//recommended 20 min
set_time_limit( 120 );
class ParserCatalog extends ParserProject{
	
	protected $city_name;
	protected $city_id;
	function __construct(){
		//$this->site_id = 101;
		parent::__construct();
		//$this->pages_count = 1;
		//$this->domen = '';
		//$this->useCookie = true;
	}
	
	
	protected function getMainUrls(){
		$this->writeLog('start');
		if( !$this->db->fetchOne('select id from category where parsed = ?', array( date('Y-m-d') ) ) ){

			$this->db->query('truncate table category');
			$this->db->query('truncate table product');
			$this->db->query('truncate table product_image');
			$this->db->query('truncate table product_param');
			
			$url = 'http://my-tools.com.ua/';

			$proxy = $this->proxy->get();
			$urls []= array( 
					'url' => $url,
					'referer' => $url,
					'proxy' => $proxy,
					'cookie_keep_alive' => true,
					'data' => array( 'type' => 'main', 'proxy' => $proxy ),
			);
			return $urls;
		}
		$categories = $this->db->fetchAll("select * from category where done = 0 limit 1");
		foreach( $categories as $category ){
			$proxy = $this->proxy->get();
			$url = $category['url'].'?page='.$category['page'];
			$urls []= array( 
					'url' => $url,
					'referer' => $url,
					'proxy' => $proxy,
					'cookie_keep_alive' => true,
					'data' => array( 'category' => $category, 'proxy' => $proxy ),
			);
		}
		
		$products = $this->db->fetchAll("select product.* from product where product.done = 0 limit 10");
		foreach( $products as $product ){
			$proxy = $this->proxy->get();
			$url = $product['url'];
			$urls []= array(
					'url' => $url,
					'referer' => $url,
					'proxy' => $proxy,
					'data' => array( 'product' => $product, 'proxy' => $proxy, 'type' => 'main_page' ),
			);
		}
		
		if( !$categories && !$products ){
			$this->writeLog('done!');die;
		}
		return $urls;
	}
	function captchaResponse( $content, $url, $ch, $data ){
		$this->writeLog($content);
	}
	function processMainPage( $content, $url, $ch, $data ){
		$content = iconv("cp1251","UTF-8//IGNORE",$content);
		$content = str_replace('&nbsp;', ' ', $content);
		$info = curl_getinfo($ch);
		if( !$content || !strpos($content, 'Любые вопросы можно уточнить по телефону') ){
			$this->writeLog("Invalid content $url");
		}
		if( $data['type'] == 'main' ){
			$main_categories = array(
				array( 'name' => 'Инструменты', 'url' => 'http://my-tools.com.ua/instrumenty/c-224.html', 'parsed' => date('Y-m-d'), 'done' => 1 ),
				array( 'name' => 'Принадлежности', 'url' => 'http://my-tools.com.ua/prinadlezhnosti/c-326.html', 'parsed' => date('Y-m-d'), 'done' => 1 ),
			);
			foreach( $main_categories as $category ){
				$this->db->insert('category', $category );
				$parent_id = $this->db->lastInsertId('category');
				preg_match('%<span>\s*'.$category['name'].'\s*<.*?<div[^<>]*ul_path2[^<>]*>.*?</div>\s*</div>\s*</div>\s*</div>%usix', $content, $tmp);
				preg_match_all('%<a[^<>]*href="(?<url>[^"]*)">(?<name>.*?)</a>%usix', $tmp[0], $rows, PREG_SET_ORDER );
				foreach($rows as $row){
					$row['url'] = $this->filter->trim( $row['url'] );
					if( strpos( $row['url'], '/' ) === 0 ){
						$row['url'] == 'http://my-tools.com.ua'.$row['url'];
					}
					if( !$this->db->fetchOne('select id from category where url = ?', array( $row['url'] ) ) ){
						$this->db->insert('category', array( 'name' => $this->filter->trim( $row['name'] ), 'url' => $row['url'], 'parsed' => date('Y-m-d'), 'parent_id' => $parent_id ) );
					}
				}
			}
		} else if( $data['category'] ){
			$category = $data['category'];
			preg_match_all( '%<a[^<>]*(?:product_name|product_image)[^<>]*href="(?<url>[^"]*)"%usix', $content, $rows, PREG_SET_ORDER );
			if( !$rows ){
				$this->writeLog("Can't find rows $url $content");
				$this->db->update('category', array( 'done' => 1 ), "id = {$data['category']['id']}");
			}
			foreach( $rows as $row ){
				$product = array();
				$product['category_id'] = $data['category']['id'];
				$product['url'] = $row[1];
				if( strpos( $product['url'], '/' ) === 0 ){
					$product['url'] = 'http://my-tools.com.ua'.$product['url'];
				}
				if( !$this->db->fetchOne( 'select * from product where url = ? ', array( $product['url'] ) ) ){
					$this->db->insert('product', $product);
				}
			}
			$this->counters['parsed']++;
			if( strpos($content, 'page='.( $data['category']['page']+1) ) ){
				$this->db->update( 'category', array( 'page' => $category['page']+1 ), 'id = '.$category['id'] );
			} else {
				$this->db->update('category', array( 'done' => 1 ), "id = {$data['category']['id']}");
			}
		} else if( $data['product'] ){
			$product = array();
			$props = $this->filter->toArray($content, '%<h2>\s*Технические\s*характеристики[^<>]*</h2>\s*<table>.*?</table>%usix', '%<tr>\s*<td>(.*?)</td>\s*<td>(.*?)</td>\s*</tr>%usix');
			if( !$props ){
				$props = $this->filter->toArray($content, '%<strong>\s*Технические\s*характеристики:\s*</strong>\s*</p>\s*<p>.*?</p>%usix', '/([^<>]*):([^<>]*)<br/usix');
			}
			if( !$props ){
				$props = $this->filter->toArray($content, '%products_description.*?<table[^>]*>.*?</table>%usix', '%<tr>\s*<td>(.*?)</td>\s*<td>(.*?)</td>\s*</tr>%usix');
			}
			foreach($props as $name => $value){
				$this->addParamValue($data['product']['id'], $this->filter->trim( $name ), $this->filter->trim( $value ) ) ;
			}
			$product['name'] = preg_match('%<p[^<>]*product_name[^<>]*>(.*?)</p>%usix', $content, $tmp) ? $this->filter->trim( $tmp[1] ) : null;
			$product['description'] = preg_match('%<h2>\s*Параметры\s*</h2>\s*<ul>.*?</ul>%usix', $content, $tmp) ? $tmp[0] : null;
			if( !$product['description'] ){
				$product['description'] = preg_match('%<strong>\s*Особенности:\s*</strong>\s*</p>\s*<p>(.*?)</p>%usix', $content, $tmp) ? $tmp[1] : null;
			}
			if( !$product['description'] ){
				$product['description'] = preg_match('%<div[^>]*products_description[^>]*>\s*<h1>.*?</h1>\s*(<ul>.*?</ul>)%usix', $content, $tmp) ? $tmp[1] : null;
			}
			$product['price'] = preg_match('/name="prod_price"\s*value="(.*?)"/usix', $content, $tmp) ? $tmp[1] : null;
			$product['image'] = preg_match('/javascript:zoomProduct.*?data-image="(.*?)"/usix', $content, $tmp) ? "http://my-tools.com.ua/r_imgs.php?thumb={$tmp[1]}&w=500&h=500" : null;
			$product['subname'] = preg_match('%<div[^<>]*product_short_name[^<>]*>(.*?)</div>%six', $content, $tmp) ? $this->filter->trim( $tmp[1] ) : null;
			if( !$product['subname'] ){
				$product['subname'] = preg_match('%<div[^>]*products_description[^>]*>\s*<h1>(.*?)</h1>%usix', $content, $tmp) ? $this->filter->trim( $tmp[1] ) : null;
			}
			$product['done'] = 1;
			$this->counters['parsed']++;
			$this->db->update('product', $product, 'id='.$data['product']['id']);
		}
	
	}
	
	function addParamValue( $product_id, $name, $value ){
		$value = $this->filter->trim( strip_tags( $value ) );
		$param_id = $this->db->fetchOne('select id from param where name = ?', array( $name ) );
		if( !$param_id ){
			$this->db->insert('param', array( 'name' => $name ));
			$param_id = $this->db->lastInsertId('param');
		}
		if( !$this->db->fetchOne('select id from product_param where product_id = ? and param_id = ?', array( $product_id, $param_id ) ) ){
			$this->db->insert('product_param', array( 'product_id' => $product_id, 'param_id' => $param_id, 'value' => $value ) );
		}
	}
	
	function processAdvertImage( $content, $url, $ch, $data ){
	}
	
	
	protected function advertPageCheck( $content, $ch ){
		return true;
	}
}