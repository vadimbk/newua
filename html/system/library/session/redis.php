<?php
namespace Session;
final class Redis {
	public $expire = 3600;
	private $redis;

	public function __construct($registry) {
		$this->redis = new \Redis();
		$this->redis->pconnect(CACHE_HOSTNAME, CACHE_PORT);
	}

	public function read($session_id) {
		$raw = $this->redis->get('sess_' . $session_id);
		if ($raw === false) {
			return false;
		}
		return json_decode($raw, true);
	}

	public function write($session_id, $data) {
		if ($session_id) {
			$this->redis->setex('sess_' . $session_id, $this->expire, json_encode($data));
		}
		return true;
	}

	public function destroy($session_id) {
		$this->redis->del('sess_' . $session_id);
		return true;
	}

	public function gc($expire) {
		// Redis TTL expires keys automatically; no explicit GC needed.
		return true;
	}
}
