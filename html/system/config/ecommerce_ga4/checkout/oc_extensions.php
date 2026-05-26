<?php
$_['ecommerce_ga4_name'] = 'Quick Checkout by OC-Extensions.com';

$_['ecommerce_ga4_setting_key'] = 'quick_checkout_status';

$_['ecommerce_ga4_event'] = array(
    array('trigger' => 'catalog/view/quick_checkout/header/after', 'action' => 'extension/module/ecommerce_ga4/common_header_after'),
    array('trigger' => 'catalog/view/quick_checkout/header/before', 'action' => 'extension/module/ecommerce_ga4/common_header_before'),
    array('trigger' => 'catalog/view/quick_checkout/footer/before', 'action' => 'extension/module/ecommerce_ga4/common_footer_before'),
    array('trigger' => 'catalog/view/quick_checkout/footer/after', 'action' => 'extension/module/ecommerce_ga4/common_footer_after'),
    array('trigger' => 'catalog/view/quick_checkout/checkout/after', 'action' => 'extension/module/ecommerce_ga4/checkout_checkout_after'),
);

$_['ecommerce_ga4_setting'] = array(
    'checkout_shipping' => '#btn-confirm-order',
    'checkout_payment' => '#btn-confirm-order',
);