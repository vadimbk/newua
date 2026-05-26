<?php
namespace Cache;
class Redis {
	private $expire;
	private $cache;

	public function __construct($expire) {
		$this->expire = $expire;

		$this->cache = new \Redis();
		$this->cache->pconnect(CACHE_HOSTNAME, CACHE_PORT);
	}

	public function get($key) {
		// Preserve the File adaptor's miss sentinel: missing keys must return false,
		// not null. Several storefront controllers (e.g. octemplates/oct_megamenu)
		// gate regeneration with `if (isset($x) && empty($x))`, which only fires on
		// false; with bare json_decode(false, true) we get null and skip regen.
		$raw = $this->cache->get(CACHE_PREFIX . $key);
		if ($raw === false) {
			return false;
		}
		return json_decode($raw, true);
	}

	public function set($key, $value) {
		$status = $this->cache->set(CACHE_PREFIX . $key, json_encode($value));

		$this->cache->expire(CACHE_PREFIX . $key, $this->expire);

		return $status;
	}

	public function delete($key) {
		// Mirror File adaptor wildcard behaviour: delete all keys whose name starts
		// with the given prefix (e.g. 'ocfilter.price' removes all ocfilter.price.*
		// variants). Plain del() only hits an exact key and silently misses the rest.
		$iterator = null;
		$pattern  = CACHE_PREFIX . $key . '*';
		do {
			$keys = $this->cache->scan($iterator, $pattern, 100);
			if ($keys) {
				$this->cache->del($keys);
			}
		} while ($iterator !== 0);
	}
}
