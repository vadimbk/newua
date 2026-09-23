<?php
function token($length = 32) {
	// Create random token
	$string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	
	$max = strlen($string) - 1;
	
	$token = '';
	
	for ($i = 0; $i < $length; $i++) {
		$token .= $string[mt_rand(0, $max)];
	}	
	
	return $token;
}

/**
 * Backwards support for timing safe hash string comparisons
 * 
 * http://php.net/manual/en/function.hash-equals.php
 */

if(!function_exists('hash_equals')) {
	function hash_equals($known_string, $user_string) {
		$known_string = (string)$known_string;
		$user_string = (string)$user_string;

		if(strlen($known_string) != strlen($user_string)) {
			return false;
		} else {
			$res = $known_string ^ $user_string;
			$ret = 0;

			for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);

			return !$ret;
		}
	}
}
/**
 * Preorder rule.
 *
 * quantity is set by the supplier feed import and means "the supplier has it now",
 * not a shelf count. stock_status_id is chosen by a manager and means "what to do
 * once the supplier has none". A product is a preorder only when both apply:
 * the supplier is out AND the manager marked it as still sourceable.
 *
 * oc_stock_status: 6 = expected in 2-3 days, 8 = on order, 11 = on order 7-14 days.
 * Keep this list in sync with the in-stock-alert module button replacement list.
 */
function preorder_stock_status_ids() {
	return array(6, 8, 11);
}

function is_preorder_product($quantity, $stock_status_id) {
	if ($quantity > 0) {
		return false;
	}

	return in_array((int)$stock_status_id, preorder_stock_status_ids(), true);
}

// oc_stock_status 5 = out of stock. Default availability for a new product, so a
// product never reaches the storefront with an empty stock status.
function default_stock_status_id() {
	return 5;
}
