<?php
$_['ecommerce_ga4_name'] = 'Quick Checkout by MarketInSG.com';

$_['ecommerce_ga4_setting_key'] = 'quickcheckout_status';

$_['ecommerce_ga4_event'] = array(
    array('trigger' => 'catalog/view/extension/quickcheckout/checkout/after', 'action' => 'extension/module/ecommerce_ga4/checkout_checkout_after'),
);

$_['ecommerce_ga4_setting'] = array(
    'checkout_shipping' => '#button-payment-method',
    'checkout_payment' => '#button-payment-method',
);