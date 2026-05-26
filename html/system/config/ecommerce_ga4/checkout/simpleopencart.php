<?php
$_['ecommerce_ga4_name'] = 'Simple Checkout by SimpleOpencart.com';

$_['ecommerce_ga4_setting_key'] = 'simple_replace_checkout';

$_['ecommerce_ga4_event'] = array(
    array('trigger' => 'catalog/view/checkout/simplecheckout/after', 'action' => 'extension/module/ecommerce_ga4/checkout_checkout_after'),
);

$_['ecommerce_ga4_setting'] = array(
    'checkout_shipping' => '#simplecheckout_button_confirm',
    'checkout_shipping_ajax' => true,
    'checkout_payment' => '#simplecheckout_button_confirm',
    'checkout_payment_ajax' => true
);