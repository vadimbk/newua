<?php

$_['ecommerce_ga4_event'] = array(
    array('trigger' => 'catalog/view/journal2/module/side_products/after', 'action' => 'extension/module/ecommerce_ga4/product_list_after'),
    array('trigger' => 'catalog/view/journal2/module/carousel_product/after', 'action' => 'extension/module/ecommerce_ga4/journal2_product_list_after'),
    array('trigger' => 'catalog/view/journal2/module/custom_sections_product/after', 'action' => 'extension/module/ecommerce_ga4/journal2_product_list_after'),
    array('trigger' => 'catalog/view/journal2/module/slider_simple/after', 'action' => 'extension/module/ecommerce_ga4/journal2_promotion_list_after'),
    array('trigger' => 'catalog/view/journal2/module/slider_advanced/after', 'action' => 'extension/module/ecommerce_ga4/journal2_promotion_list_after'),
    array('trigger' => 'catalog/view/journal2/module/static_banners/after', 'action' => 'extension/module/ecommerce_ga4/journal2_promotion_list_after'),
    array('trigger' => 'catalog/view/journal2/checkout/checkout/after', 'action' => 'extension/module/ecommerce_ga4/checkout_checkout_after'),
    array('trigger' => 'catalog/view/journal2/checkout/cart/after', 'action' => 'extension/module/ecommerce_ga4/checkout_cart_after')
);

$_['ecommerce_ga4_setting'] = array(
    'add_to_cart' => array(
        'product/category' => array(
            'search_value' => 'onclick="addToCart(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]"  data-e4-list-id="[list_id]" onclick="addToCart(\'[item_id]\''
        ),
        'product/search' => array(
            'search_value' => 'onclick="addToCart(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToCart(\'[item_id]\''
        ),
        'product/manufacturer_info' => array(
            'search_value' => 'onclick="addToCart(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" data-e4-list-id="[list_id]" onclick="addToCart(\'[item_id]\''
        ),
        'product/special' => array(
            'search_value' => 'onclick="addToCart(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToCart(\'[item_id]\''
        ),
        'extension/module/bestseller' => array(
            'search_value' => 'onclick="addToCart(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToCart(\'[item_id]\''
        ),
        'extension/module/featured' => array(
            'search_value' => 'onclick="addToCart(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToCart(\'[item_id]\''
        ),
        'extension/module/latest' => array(
            'search_value' => 'onclick="addToCart(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToCart(\'[item_id]\''
        ),
        'extension/module/special' => array(
            'search_value' => 'onclick="addToCart(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToCart(\'[item_id]\''
        ),
        'journal2/module/carousel_product' => array(
            'search_value' => 'onclick="addToCart(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" data-e4-list-id="[list_id]" onclick="addToCart(\'[item_id]\''
        ),
        'journal2/module/custom_sections_product' => array(
            'search_value' => 'onclick="addToCart(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" data-e4-list-id="[list_id]" onclick="addToCart(\'[item_id]\''
        ),
    ),
    'add_to_wishlist' => array(
        'product/category' => array(
            'search_value' => 'onclick="addToWishList(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]"  data-e4-list-id="[list_id]" onclick="addToWishList(\'[item_id]\''
        ),
        'product/search' => array(
            'search_value' => 'onclick="addToWishList(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToWishList(\'[item_id]\''
        ),
        'product/manufacturer_info' => array(
            'search_value' => 'onclick="addToWishList(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" data-e4-list-id="[list_id]" onclick="addToWishList(\'[item_id]\''
        ),
        'product/special' => array(
            'search_value' => 'onclick="addToWishList(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToWishList(\'[item_id]\''
        ),
        'extension/module/bestseller' => array(
            'search_value' => 'onclick="addToWishList(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToWishList(\'[item_id]\''
        ),
        'extension/module/featured' => array(
            'search_value' => 'onclick="addToWishList(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToWishList(\'[item_id]\''
        ),
        'extension/module/latest' => array(
            'search_value' => 'onclick="addToWishList(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToWishList(\'[item_id]\''
        ),
        'extension/module/special' => array(
            'search_value' => 'onclick="addToWishList(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" onclick="addToWishList(\'[item_id]\''
        ),
        'journal2/module/carousel_product' => array(
            'search_value' => 'onclick="addToWishList(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" data-e4-list-id="[list_id]" onclick="addToWishList(\'[item_id]\''
        ),
        'journal2/module/custom_sections_product' => array(
            'search_value' => 'onclick="addToWishList(\'[item_id]\'',
            'add_value' => 'data-e4-index="[index]" data-e4-list="[list]" data-e4-list-id="[list_id]" onclick="addToWishList(\'[item_id]\''
        ),
    ),
    'list_var' => array(
        'journal2/module/custom_sections_product' => array(
            'products' => 'items'
        ),
        'journal2/module/carousel_product' => array(
            'products' => 'items'
        )
    ),
    'checkout_shipping' => '#journal-checkout-confirm-button',
    'checkout_payment' => '#journal-checkout-confirm-button'
);