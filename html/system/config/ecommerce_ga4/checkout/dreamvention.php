<?php
$_['ecommerce_ga4_name'] = 'Ajax Quick Checkout 6.x by Dreamvention.ee';

$_['ecommerce_ga4_setting_key'] = 'd_quickcheckout_status';

$_['ecommerce_ga4_event'] = array(
    array('trigger' => 'catalog/view/checkout/d_quickcheckout/after', 'action' => 'extension/module/ecommerce_ga4/checkout_checkout_after'),
    array('trigger' => 'catalog/model/extension/d_quickcheckout/order/addOrder/after', 'action' => 'extension/module/ecommerce_ga4/add_order_after')
);

$_['ecommerce_ga4_setting'] = array(
    'checkout_shipping' => '#qc_confirm_order',
    'checkout_shipping_ajax' => true,
    'checkout_payment' => '#qc_confirm_order',
    'checkout_payment_ajax' => true
);