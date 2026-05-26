<?php

require_once ( "Parser.php" );
require_once ( "Filter.php" );
require_once('Zend/Db/Adapter/Abstract.php');
require_once('Zend/Db.php');
require_once('Zend/Db/Table/Abstract.php');
require_once('Zend/Db/Table.php');


abstract class ParserProject extends Parser{
	public $db;
	protected $filter;
	protected $cities;
	protected $parser_prefix;
	
	function __construct() {
		if( !$this->parser_prefix  ){
			$this->parser_prefix = '';
		}
		include('config.php');
		$this->db = $db = Zend_Db::factory('Pdo_Mysql', array(
				'host'             => $dbServer,
				'username'         => $dbUser,
				'password'         => $dbPass,
				'dbname'           => $dbName,
				'charset'  => 'utf8',
		));
		$this->db->query("SET SESSION sql_mode = ''");
		$this->filter = new Filter();
		parent::__construct();
	}
	
	protected function getId($table, $check, $insert = null, $cache = false, $idName = 'id'){
		if( !$insert ){
			$insert = $check;
		}
		if( $cache ){
			$index = md5( print_r($check, 1) );
			if( $this->idCache['name'][$table][$index] ){
				return $this->idCache['name'][$table][$index];
			}
		}
		$select = $this->db->select()->from($table, array($idName));
		foreach( $check as $field => $value ){
			$select->where("$field = ?", array( $value ) );
		}
		$sql = $select->__toString();
		$where_string = preg_match('#where\s+(.*)#usix', $sql, $tmp) ? $tmp[1] : null;
		$rows = $select->query()->fetchAll();
		$id = $rows[0][ $idName ];
		if( !$id ){
			$this->db->insert($table, $insert);
			$id = $this->db->lastInsertId($table);
		} else if( $where_string ) {
			$this->db->update($table, $insert, $where_string);
		}
		if( $cache ) $this->idCache['name'][$table][$index] = $id;
		return $id;
	}
	
	
	protected function getWhiteProxy($name = '', $limit = null){
		if( $limit && ++$this->writeProxyFetched[$name] > $limit){
			return $this->proxy->get();
		}
		if( !$this->writeProxyGetted[$name] ){
			$this->writeProxy[$name] = $this->db->fetchAll('select * from proxy_whitelist where site_id = ?', array( $this->site_id ) );
			$this->writeProxyGetted[$name] = true;
		}
		if( $this->writeProxy[$name] ){
			return array_pop($this->writeProxy[$name]);
		} else {
			return $this->proxy->get();
		}
	}
	
	protected function setWhiteProxy($row){
		if( !$this->db->fetchOne('select id from proxy_whitelist where site_id = ? and ip = ? and port = ?', array( $this->site_id, $row['ip'], $row['port'] ) ) ){
			$this->db->insert( 'proxy_whitelist', array(
					'site_id' => $this->site_id,
					'ip' => $row['ip'],
					'port' => $row['port'],
			) );
		}
	}
	
	protected function removeWhiteProxy($row){
		$this->db->query('delete from proxy_whitelist where site_id = ? and ip = ? and port = ?', array( $this->site_id, $row['ip'], $row['port'] ) );
	
	}
	

	protected function postamble(){
		$result_str = '';
		foreach( $this->counters as $name => $value ){
			$result_str .= "{$name}: {$value}, ";
		}
		$arr = $this->getCpuUsage();
		$this->writeLog($result_str, false, array( 'end' => 1, 'time' => $arr['time'], 'cpu' => $arr['cpu_percentage'], 'needed' => $this->counters['needed'] ));
	}

	
	function writeLog( $str, $error = false, $params = array() ){
		
		$row = array();
		$row['datetime'] = date('Y-m-d H:i:s' );
		$row['error'] = $error ? 1 : 0;
		$row['text'] = $str;
		$row['site_id'] = 1;
		//$row['instance'] = $this->instance;
		$row['key'] = $this->uniqueKey;
		$row['parser_prefix'] = $this->prefix;
		if( $params ){
			foreach( $params  as $idx =>  $param ){
				$row[ $idx ] = $param;
			}
		}
		$this->db->insert('service_log', $row);
		
		echo $str."\n\r";
		
		if( $error ){
			//mail("pavelk@samaritan.ru", "Error occured in avito", $str, "From: admin@pixhost.ru \r\n");
			if( $error ){
				@curl_close( $error );
				throw new Exception( $str );
			}
		}
	}
	
	function preamble() {

	}
	
	function getCpuUsage() {
		$time = (microtime(true) - PHP_TUSAGE) * 1000000;
		if( !$this->windows_server() ){
			$dat = getrusage();
			$dat["ru_utime.tv_usec"] = ($dat["ru_utime.tv_sec"]*1e6 + $dat["ru_utime.tv_usec"]) - PHP_RUSAGE;
		}
		// cpu per request
		if( $time > 0 && !$this->windows_server() ) {
			$cpu = sprintf("%01.2f", ($dat["ru_utime.tv_usec"] / $time) * 100);
		} else {
			$cpu = '0.00';
		}
	
		$time = sprintf("%01.4f", $time / 1000000);
		return array( 'cpu_percentage' => $cpu, 'time' => $time );
	}
	
	function windows_server(){
		return in_array(strtolower(PHP_OS), array("win32", "windows", "winnt"));
	}
}