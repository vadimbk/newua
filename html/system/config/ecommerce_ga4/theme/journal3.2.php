<?php

$_['ecommerce_ga4_event'] = array(
    array('trigger' => 'catalog/view/journal3/module/banners/after', 'action' => 'extension/module/ecommerce_ga4/journal3_promotion_list_after'),
    array('trigger' => 'catalog/view/journal3/module/master_slider/after', 'action' => 'extension/module/ecommerce_ga4/journal3_promotion_list_after'),
    array('trigger' => 'catalog/view/journal3/products/after', 'action' => 'extension/module/ecommerce_ga4/journal32_product_list_after'),
    array('trigger' => 'catalog/view/journal3/side_products/after', 'action' => 'extension/module/ecommerce_ga4/journal32_product_list_after'),
    array('trigger' => 'catalog/view/journal3/checkout/checkout/after', 'action' => 'extension/module/ecommerce_ga4/checkout_checkout_after'),
    array('trigger' => 'catalog/model/journal3/order/save/after', 'action' => 'extension/module/ecommerce_ga4/add_order_after')
);

$_['ecommerce_ga4_xml'] = '<?xml version="1.0" encoding="utf-8"?>
<modification>
    <name>Ecommerce GA4 (Add-on for Journal theme 3.2.0-rc)</name>
    <file path="catalog/controller/journal3/event/products.php">
        <operation error="skip">
            <search><![CDATA[private function product($product, $product_info) {]]></search>
            <add position="after"><![CDATA[        /** Ecommerce GA4 Extension */
        $data[\'products\'] = $product_info;
        /** Ecommerce GA4 Extension */]]></add>
        </operation>
    </file>
</modification>';


$_['ecommerce_ga4_setting'] = array(
    'list' => array(
        'product/search' => array(
            'search_value' => '',
            'add_position' => '',
        ),
        'product/category' => array(
            'search_value' => '',
            'add_position' => '',
        ),
        'product/manufacturer/info' => array(
            'search_value' => '',
            'add_position' => '',
        ),
        'product/special' => array(
            'search_value' => '',
            'add_position' => '',
        ),
    ),
    'checkout_shipping' => '#quick-checkout-button-confirm',
    'checkout_payment' => '#quick-checkout-button-confirm'
);