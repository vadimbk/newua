<?php 

class Filter{
	protected $proxyURL;
	private $proxies;
	private $tmp_proxies;
	
	function __construct() {
	}
	public function trim( $text ){
		return trim(preg_replace('/[\x00-\x20\x7F]+/u',' ',str_replace(array( ' ', ' ' ), ' ',html_entity_decode(strip_tags($text),ENT_COMPAT, 'UTF-8'))));
	}
	
	public function price( $text ){
		return preg_replace('|\D+|','', $text);
	}
	
	public function int( $text ){
		return preg_replace('|\D+|','', $text);
	}
	
	public function float( $text, $ignoreComma = false ){
		return preg_replace('|[^\d\.]|','', $ignoreComma ? $text : str_replace( ',', '.', $text ) );
	}
	
	public function ptrim( $preg, $content, $index = 1 ){
		return preg_match($preg, $content, $tmp ) ? $this->trim( $tmp[ $index ] ) : null;
	}
	
	public function phone( $text ){
		$text = $this->int( $text );
		if(strlen($text) > 11){
			if( $text[11] == 8 || $text[11] == 7 ){
				$text = substr( $text, 0, 11 );
			}
		}
		if(strlen($text)==11 and in_array(substr($text,0,1),array(7,8))){
			$text=substr($text,1);
		}
		return $text;
	}
	
	public function toArray( $object_page, $reg1, $reg2, $reverse = false ){
		$params = array();
		foreach( preg_match( $reg1 ,$object_page, $matches) ? ( preg_match_all($reg2, $matches[0],$matches, PREG_SET_ORDER) ? $matches : array()) : array() as $param){
			if( !$reverse ){
				$params[$this->trim($param[1])]=$param[2];
			} else {
				$params[$this->trim($param[2])]=$param[1];
			}
		}
		return $params;
	}
}