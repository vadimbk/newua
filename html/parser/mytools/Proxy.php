<?php 
class Proxy{
	protected $proxyURL;
	private $proxies;
	private $tmp_proxies;
	private $parserObj;
	
	function __construct( $url, &$parserObj ) {
		include( 'config.php' );
		$this->proxyURL = $proxyURL;
		$this->parserObj = &$parserObj;
		$this->initializeProxies();
	}
	protected function initializeProxies(){
		include( 'config.php' );
		if( !$this->proxyURL ){
			return;
		}
		$this->proxies = array();
		$content = file_get_contents( $this->proxyURL );
		$this->proxies = json_decode( $content );
		if( !$this->proxies ){
			$this->proxies = array();
			preg_match_all('/([\d\.]+):(\d+)/six', $content, $tmp, PREG_SET_ORDER);
			foreach( $tmp as $row ){
				$this->proxies[] = array( 'ip' => $row[1], 'port' => $row[2] );
			}
		}
		shuffle( $this->proxies );
		if( count( $this->proxies ) < 5 ){
			$this->parserObj->writeLog('We need at least 5 proxies to take data', true);
		}
	}
	
	public function get(){
		if( !$this->proxyURL ){
			return;
		}
		if( !$this->tmp_proxies ){
			$this->tmp_proxies = $this->proxies;
		}
		if( !$this->tmp_proxies ){
			$this->initializeProxies();
			return $this->proxy->get();
		}
		return (array)array_pop($this->tmp_proxies);
	}
	
	public function getProxyCount(){
		return count( $this->proxies );
	}
	
	protected function removeProxy( $ip ){
		foreach( $this->proxies as $i => $proxy ){
			if( $proxy['ip'] == $ip ){
				unset( $this->proxies[$i] );
				break;
			}
		}
	
		if( $this->tmp_proxies ){
			foreach( $this->tmp_proxies as $i => $proxy ){
				if( $proxy['ip'] == $ip ){
					unset( $this->tmp_proxies[$i] );
					break;
				}
			}
		}
	}
}