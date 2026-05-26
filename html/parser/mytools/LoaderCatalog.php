<?php
require_once ( "ParserProject.php" );

//recommended 20 min
set_time_limit( 120 );
class LoaderCatalog extends ParserProject{
	
	protected $city_name;
	protected $city_id;
	function __construct(){
		//$this->site_id = 101;
		parent::__construct();
		$xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><yml_catalog date=\"".date('Y-m-d H:i')."\"/>");
		$xml->shop->name = 'My Tools';
		$xml->shop->url = 'http://my-tools.com';
		$xml->shop->version = 1;
		$categories = $this->db->fetchAll('select * from category');
		$xml->shop->currencies->currency = '';
		$xml->shop->currencies->currency->addAttribute('id', 'UAH');
		$xml->shop->currencies->currency->addAttribute('rate', '1');
		
		foreach( $categories as $index => $category ){
			$xml->shop->categories->category[ $index ] = $category['name'];
			$xml->shop->categories->category[ $index ]->addAttribute('id', $category['id']);
			if( $category['parent_id'] ){
				$xml->shop->categories->category[ $index ]->addAttribute('parentId', $category['parent_id']);
			}
		}
		$rows = $this->db->fetchAll('select product_id, product_param.value, param.name from product_param join param on param.id = param_id' );
		foreach($rows as $row){
			$params[ $row['product_id'] ][] = $row;
		}
		$products = $this->db->fetchAll('select * from product', array( $category['id'] ) );
		foreach($products as $i => $product){
			$fields = array( 'price', 'url' );
			foreach( $fields as $field ){
				$xml->shop->offers->offer[$i]->{$field} = (string)$product[ $field ];
			}
			$xml->shop->offers->offer[$i]->addAttribute('id', $product['id']);
			$xml->shop->offers->offer[$i]->addAttribute('available', 'true');
			$xml->shop->offers->offer[$i]->description = (string)preg_replace( '#>\s*#usix', '>', $product['description'] );
			$xml->shop->offers->offer[$i]->currencyId = (string)'UAH';
			$xml->shop->offers->offer[$i]->my_vendor = 'Makita';
			$xml->shop->offers->offer[$i]->garanty = '36';
			$xml->shop->offers->offer[$i]->vendorCode = (string)$product[ 'name' ];
			$xml->shop->offers->offer[$i]->name = (string)$product[ 'subname' ];
			$xml->shop->offers->offer[$i]->picture = (string)$product[ 'image' ];
			$xml->shop->offers->offer[$i]->categoryId = (string)$product[ 'category_id' ];
			if( $params[ $product['id'] ] ) foreach( $params[ $product['id'] ] as $j => $param ){
				$xml->shop->offers->offer[$i]->param[$j] = (string)$param['value'];
				$xml->shop->offers->offer[$i]->param[$j]->addAttribute('name', $param['name']);
			}
			$i++;
		}
		$dom = dom_import_simplexml($xml)->ownerDocument;
		$dom->formatOutput = true;
		header('Content-type: text/xml');
		header('Content-Disposition: attachment; filename="export-'.date('Y-m-d').'.xml"');
		echo $dom->saveXML();
		die;
	}
}