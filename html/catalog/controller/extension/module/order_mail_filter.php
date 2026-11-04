<?php
// Runs instead of mail/order on addOrderHistory (oc_event mail_order_add).
// Simple puts a technical address into orders without an email: there is nobody to write to.
class ControllerExtensionModuleOrderMailFilter extends Controller {
	public function index(&$route, &$args) {
		$order_info = $this->model_checkout_order->getOrder($args[0]);

		if ($order_info && !preg_match('/@localhost\.net$/i', $order_info['email'])) {
			$action = new Action('mail/order');
			$action->execute($this->registry, array(&$route, &$args));
		}
	}
}
