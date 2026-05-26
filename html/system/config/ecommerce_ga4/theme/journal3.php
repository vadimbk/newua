<?php

$_['ecommerce_ga4_event'] = array(
    array('trigger' => 'catalog/view/journal3/module/products/after', 'action' => 'extension/module/ecommerce_ga4/journal3_product_list_after'),
    array('trigger' => 'catalog/view/journal3/module/side_products/after', 'action' => 'extension/module/ecommerce_ga4/journal3_product_list_after'),
    array('trigger' => 'catalog/view/journal3/module/banners/after', 'action' => 'extension/module/ecommerce_ga4/journal3_promotion_list_after'),
    array('trigger' => 'catalog/view/journal3/module/master_slider/after', 'action' => 'extension/module/ecommerce_ga4/journal3_promotion_list_after'),
    array('trigger' => 'catalog/view/journal3/checkout/checkout/after', 'action' => 'extension/module/ecommerce_ga4/checkout_checkout_after'),
    array('trigger' => 'catalog/model/journal3/order/save/after', 'action' => 'extension/module/ecommerce_ga4/add_order_after')
);

$_['ecommerce_ga4_setting'] = array(
    'checkout_shipping' => '#quick-checkout-button-confirm',
    'checkout_payment' => '#quick-checkout-button-confirm'
);