<?php
$_['ecommerce_ga4_name'] = 'Best Checkout by Xtensions.in';

$_['ecommerce_ga4_table_name'] = 'xtensions';

$_['ecommerce_ga4_event'] = array(
    array('trigger' => 'catalog/view/extension/module/xtensions/checkout/xheader/after', 'action' => 'extension/module/ecommerce_ga4/common_header_after'),
    array('trigger' => 'catalog/view/extension/module/xtensions/checkout/xheader/before', 'action' => 'extension/module/ecommerce_ga4/common_header_before'),
    array('trigger' => 'catalog/view/extension/module/xtensions/checkout/xfooter/before', 'action' => 'extension/module/ecommerce_ga4/common_footer_before'),
    array('trigger' => 'catalog/view/extension/module/xtensions/checkout/xfooter/after', 'action' => 'extension/module/ecommerce_ga4/common_footer_after'),
    array('trigger' => 'catalog/view/extension/module/xtensions/checkout/xcheckout/after', 'action' => 'extension/module/ecommerce_ga4/checkout_checkout_after'),
    array('trigger' => 'catalog/view/extension/module/xtensions/checkout/xpayment_method/after', 'action' => 'extension/module/ecommerce_ga4/checkout_payment_method_after'),
);

$_['ecommerce_ga4_setting'] = array(
    'checkout_payment_custom' => array(
        'add_value' => '<script type="text/javascript"><!--
  $(document).delegate(\'#button-confirm\', \'click\', function() {
    e4_checkout.add_payment_info_custom($(this).closest(\'.panel\').find(\'[payment_method]\').attr(\'payment_method\'));
  });
--></script>'
    )
);