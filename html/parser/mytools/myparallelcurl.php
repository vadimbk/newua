<?php
require_once('parallelcurl.php');
class MyParallelCurl extends ParallelCurl {
	private $callback;
	function __construct( $callback, $params ){
		$this->callback = $callback;
		
		$this->useragents = array(
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116"
		);
		$curl_options = array(
				CURLOPT_TIMEOUT => @$params['timeout']? @$params['timeout'] : 120,
				CURLOPT_FOLLOWLOCATION => 1,
		
		);
		$this->params = $params;
		parent::__construct( @$params['threads'] ? $params['threads'] : 50, $curl_options );
	}
	
	public function setCallback( $callback ){
		$this->callback = $callback;
	}
	
	function TakeData( $urls ) {
		
		foreach( $urls as $index => $row ){
			$this->go($row);
		}
	}
	
	public function go( $row ){
		$url = $row['url'];
		$referer = $row['referer'];
		$proxy = @$row['proxy'];
		$post = @$row['post'];
		$data = @$row['data'];
		$headers = array(
				'User-Agent: '.$this->useragents[ rand(0, count($this->useragents)-1) ],
				'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
				'Referer: '.$referer,
		);
		if( $row['headers'] ){
			foreach( $row['headers'] as $value ){
				$headers[] = $value;
			}
		}
		$params = array(
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_ENCODING => '',
		);
		if( $this->params['nobody'] ){
			$params += array(
				CURLOPT_NOBODY => true,
			);
		}
		if( stristr( $url, 'https' ) !== false ){
			$params += array(
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_SSLVERSION => 1,
			);
		}
		if( $this->params['include_headers'] ){
			$params += array(
				CURLOPT_HEADER => true,
			);
		}
		if( $post ){
			$params += array(
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => $post
			);
		} else if( isset( $params['post'] ) && $params['post'] ){
			$params += array(
					CURLOPT_POST => 1,
					CURLOPT_POSTFIELDS => $params['post']
			);
		}
		if( $proxy ){
			$params += array(
					CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
					CURLOPT_PROXY => $proxy['ip'],
					CURLOPT_PROXYPORT => $proxy['port']
			);
			if( $proxy['username'] ){
				$params += array(
					CURLOPT_PROXYUSERPWD =>$proxy['username'].':'.$proxy['password']
				);
			}
		}
		if( $row['cookie_txt'] ){
			$params += array(
					CURLOPT_COOKIE => $row['cookie_txt'],
			);
		} else if( $this->params['cookie'] || $row['cookie_keep_alive'] ){
			@$cookie_file = dirname(__FILE__)."/cookie/{$row['cookie_prefix']}cookie{$proxy['ip']}_{$proxy['port']}.txt";
			$params += array(
					CURLOPT_COOKIESESSION => $row['cookie_keep_alive'] ? false : true,
					CURLOPT_COOKIEFILE => $cookie_file,
					CURLOPT_COOKIEJAR => $cookie_file,
					//CURLOPT_VERBOSE => true,
			);
		}
		$this->startRequest($url, array( &$this, 'common_callback' ), $data, $params);
	}
	
	public function common_callback($content, $url, $ch, $data){
		/*if( stristr( $content, 'windows-1251' ) ){
			$content = iconv("cp1251","UTF-8",$content);
		}*/
		call_user_func($this->callback, $content, $url, $ch, $data);
	}
	
	public function wait(){
		$this->finishAllRequests();
	}
	
	public function bye(){
		parent::__destruct();
		unset( $this );
	}
}
