<?php
ini_set('display_errors', 1);
ini_set('memory_limit', '300M');
//ini_set('mbstring.internal_encoding','UTF-8');
ini_set( 'default_charset', 'UTF-8' );

//error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL & ~E_NOTICE );
set_time_limit(60);
date_default_timezone_set('Europe/Moscow');

$path = dirname(__FILE__);
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

header("Content-Type: text/html; charset=UTF-8");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
require_once('myparallelcurl.php');
require_once('Proxy.php');
require_once 'Zend/Loader/StandardAutoloader.php';
include_once ( "simple_html_dom.php" );
setlocale(LC_ALL, 'ru_RU.UTF-8');
ini_set('pcre.backtrack_limit', '5000000');

function e(){
	throw new Exception();
}

abstract class Parser{
	protected $counters;
	protected $parserURL;
	protected $pages_count;
	protected $main_pages_threads;
	protected $main_pages_time_seconds;
	protected $main_pages_max_iterations;
	
	protected $main_pages;
	protected $curl_pages;
	protected $curl_images;
	protected $curl_main_pages;
	protected $pages_threads;
	protected $images_threads;
	
	protected $try_counters;
	protected $needed_urls;
	protected $isProxyCheck;
	protected $proxy;
	
	protected $curl_main_pages_params;
	
	function __construct() {
		//proc_nice(25);
		$this->uniqueKey = md5(microtime());
		$this->proxy = new Proxy( $this->proxyURL, $this );
		$this->counters = array( 'analized' => 0, 'needed' => 0, 'loaded' => 0, 'parsed' => 0, 'images_loaded' => 0, 'phones_parsed' => 0);
		$this->parserURL = '';
		$this->pages_count = 0;
		$this->needed_adverts = array();
		$this->useCookie = false;
		$this->isProxyCheck = false;
		
		$this->main_pages_threads = 500;
		$this->main_pages_time_seconds = 50;
		$this->main_pages_max_iterations = 12;
		
		$this->pages_threads = 10;
		$this->images_threads = 10;
		
		$autoloader = new Zend\Loader\StandardAutoloader(array(
				'fallback_autoloader' => true,
		));
		$autoloader->register();
		
	}
	
	function callback_getMainPages($content, $url, $ch, $data) {
		$this->processMainPage( $content, $url, $ch, $data );
	}
	
	function callback_getAdPage($content, $url, $ch, $data){
		$this->processAdvertPage( $content, $url, $ch, $data );
	}
	
	function callback_getAdImage($content, $url, $ch, $data){
		$this->processAdvertImage( $content, $url, $ch, $data );
	}
	
	protected function curlInit( $callback, $params = array() ){
		if( $this->useCookie ){
			$params['cookie'] = true;
		}
		return new MyParallelCurl( array( &$this, $callback ), $params );
	}
	
	protected function getMainUrls(){
		$pages_count = $this->pages_count;
		$urls = array();
		for( $i = 1; $i < $pages_count + 1; $i++ ){
			$urls[]= array( 'url' => $this->parserURL.$i,
					'referer' => 'http://yandex.ru',
					'proxy' => $this->proxy->get(),
					'data' => array( 'page' => $i ),
			);
		}
		return $urls;
	}
	
	function getHTML( $row ){
		if( !$row ){
			return null;
		}
		return simplexml_import_dom($row)->asXML();
	}
	
	function getInnerHTML($row)
	{
		return preg_match('#^\s*<[^>]*>(.*)</[^>]*>\s*$#usix', $this->getHTML($row), $tmp) ? $tmp[1] : null;
	}
	
	function parseHTML( $html ){
		return new Zend\Dom\Query('<meta http-equiv="Content-Type" content="charset=utf-8" />'.$html);
	}
	
	function mainParse(){
		$this->preamble();
		
		$urls = $this->getMainUrls();
		$iterations = 0;
		$start_time = microtime(true);
		if( $urls ){
			$urls = array_values( $urls );
			echo ' '.count( $urls ).' # ';
			$this->curl_main_pages = $this->curlInit( 'callback_getMainPages', array( 'threads' => 4, 'timeout' => 15 ) );
			$chs = array();
			for( $i = 0; $i <= count( $urls ); $i += $this->main_pages_threads ){
				$iterations ++;
				@$this->counters['main_pages_iterations']++;
				$prepared_urls = array_slice( $urls, $i, $this->main_pages_threads );
				$this->writeLog( 'Main pages iteration, count: '.count( $prepared_urls ) );
				/*if( $this->site_id == 2 ){
					foreach( $prepared_urls as &$url ){
						$index = $url['proxy']['ip'].$url['proxy']['port'];
						if($url['proxy']) {
							if( !$chs[ $index ]){
								$chs[ $index ] = curl_init();
							}
							$url['data']['ch'] = $chs[ $index ];
						}
					}
				}*/
				
				$this->curl_main_pages->TakeData( $prepared_urls );
				$this->curl_main_pages->wait();
				
			}
			$this->curl_main_pages->bye();
		}
		/*
		$this->curl_pages = $this->curlInit( 'callback_getAdPage', array( 'threads' => 1, 'timeout' => 10 ) );
		$this->curl_images = $this->curlInit( 'callback_getAdImage', array( 'threads' => 1, 'timeout' => 10 ) );
		
		$this->getNeededUrls();
		if( $this->needed_urls ){
			foreach( $this->needed_urls as $url ){
				$this->curl_pages->go($url);
			}
		}
		$this->curl_pages->wait();
		$this->curl_images->wait();
		$this->curl_pages->bye();
		$this->curl_images->bye();
		*/
		$this->postamble();
	}
	
	protected function getNeededUrls(){
		
	}
	
	protected function getTryCounter( $type ){
		if( ++$this->try_counters[$type] > 100){
			$this->writeLog( 'Loop in '.$type.' counter '.print_r( $this->reasons, 1 ), true );
		}
		return true;
	}
	
	protected function get_headers_from_curl_response($headerContent)
	{
	
		$headers = array();
	
		// Split the string on every "double" new line.
		$arrRequests = explode("\r\n\r\n", $headerContent);
	
		// Loop of response headers. The "count() -1" is to
		//avoid an empty row for the extra line break before the body of the response.
		for ($index = 0; $index < count($arrRequests) -1; $index++) {
	
			foreach (explode("\r\n", $arrRequests[$index]) as $i => $line)
			{
				if ($i === 0)
					$headers[$index]['http_code'] = $line;
				else
				{
					list ($key, $value) = explode(': ', $line);
					$headers[$index][$key] = $value;
				}
			}
		}
	
		return $headers;
	}
	
	
	protected function processAdvertImage( $content, $url, $ch, $data ){}
	
	
	protected function mainPageCheck( $content, $ch ){
		return stristr($content, "</body>");
	}
	protected function advertPageCheck( $content, $ch ){
		return stristr($content, "</body>");
	}
	protected function imagePageCheck( $content, $ch ){
		return true;
	}
	
	function processMainPage( $content, $url, $ch, $data ){}
	function processAdvertPage( $content, $url, $ch, $data ){}
	function getAdvertUrls(){}
	
	
	abstract function writeLog( $str, $error, $params );
	
	abstract protected function preamble();
	abstract protected function postamble();
	
}