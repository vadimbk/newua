<?php
$_['ecommerce_ga4_name'] = 'Ajax Quick Checkout 7.x by Dreamvention.ee';

$_['ecommerce_ga4_setting_key'] = 'd_quickcheckout_status';

$_['ecommerce_ga4_table_name'] = 'dqc_setting_data';

$_['ecommerce_ga4_event'] = array(
    array('trigger' => 'catalog/model/extension/d_quickcheckout/order/getOrder/after', 'action' => 'extension/module/ecommerce_ga4/add_order_after')
);

$_['ecommerce_ga4_setting'] = array(
    'checkout_shipping' => '.qc-confirm button',
    'checkout_shipping_ajax' => true,
    'checkout_payment' => '.qc-confirm button',
    'checkout_payment_ajax' => true
);