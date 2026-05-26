/** OpenCart Ecommerce GA4 v1.0.5 -- https://vanstudio.co.ua -- */

$(document).ready(function() {
    $(document).on('click', '[onclick*=\'cart.add\']', function() {
        var e4_data = $(this).attr('onclick').replace(/'/g, '').replace(/^.*cart\.add\(/, '').replace(');', '').split(',');
        if (e4_data[0]) {
            var e4_params = {
                'event': 'add_to_cart',
                'item_id': e4_data[0],
                'quantity': isNaN(e4_data[1]) ? $(this).parent().parent().find('input[name^=\'quantity\']').val() : e4_data[1],
                'list': $(this).data('e4-list'),
                'list_id': $(this).data('e4-list-id'),
                'index': typeof($(this).data('e4-index')) != 'undefined' ? $(this).data('e4-index') : $('[onclick*=\'cart.add\']').index(this) + 1,
                'url': window.location.href
            };
            if (typeof($(this).data('e4-quantity')) != 'undefined') {
                e4_params['quantity'] = $(this).data('e4-quantity');
            }
            if (typeof($(this).data('e4-option')) != 'undefined') {
                e4_params['option'] = $(this).closest($(this).data('e4-option')).find('[name^=\'option\']').serializeArray();
            }
            sendE4Request(e4_params);
        }
    });

    $(document).on('click', '[onclick*=\'addToCart\']', function() {
        var e4_data = $(this).attr('onclick').replace(/'/g, '').replace(/^.*addToCart\(/, '').replace(');', '').split(',');
        if (e4_data[0]) {
            var e4_params = {
                'event': 'add_to_cart',
                'item_id': e4_data[0],
                'quantity': isNaN(e4_data[1]) ? $(this).parent().parent().find('input[name^=\'quantity\']').val() : e4_data[1],
                'list': $(this).data('e4-list'),
                'list_id': $(this).data('e4-list-id'),
                'index': typeof($(this).data('e4-index')) != 'undefined' ? $(this).data('e4-index') : $('[onclick*=\'cart.add\']').index(this) + 1,
                'url': window.location.href
            };
            if (typeof($(this).data('e4-quantity')) != 'undefined') {
                e4_params['quantity'] = $(this).data('e4-quantity');
            }
            if (typeof($(this).data('e4-option')) != 'undefined') {
                e4_params['option'] = $(this).closest($(this).data('e4-option')).find('[name^=\'option\']').serializeArray();
            }
            sendE4Request(e4_params);
        }
    });

    $(document).on('click', '[onclick*=\'cart.remove\']', function() {
        if (typeof($(this).data('e4-item-id')) != 'undefined') {
            var e4_params = {
                'event': 'remove_from_cart',
                'item_id': $(this).data('e4-item-id'),
                'quantity': $(this).data('e4-quantity'),
                'variant': $(this).data('e4-variant'),
                'list': $(this).data('e4-list'),
                'url': window.location.href
            };
            sendE4Request(e4_params);
        }
    });

    $(document).on('change', 'input[name^=\'quantity\']', function() {
        if (typeof($(this).data('e4-quantity')) != 'undefined') {
            var e4_params = {
                'item_id': $(this).data('e4-item-id'),
                'variant': $(this).data('e4-variant'),
                'list': $(this).data('e4-list'),
                'url': window.location.href,
                'cart_edit': true,
            };
            if ($(this).val() > $(this).data('e4-quantity')) {
                e4_params['event'] = 'add_to_cart';
                e4_params['quantity'] = $(this).val() - $(this).data('e4-quantity');
                sendE4Request(e4_params);
            } else if ($(this).val() < $(this).data('e4-quantity')) {
                e4_params['event'] = 'remove_from_cart';
                e4_params['quantity'] = $(this).data('e4-quantity') - $(this).val();
                sendE4Request(e4_params);
            }
            $(this).data('e4-quantity', $(this).val());
        }
    });

    $('a[data-e4-select]').on('click', function() {
        var e4_params = {
            'event': 'select_item',
            'item_id': $(this).data('e4-select')[0],
            'list': $(this).data('e4-select')[1],
            'index': $(this).data('e4-select')[2],
            'list_id' : $(this).data('e4-select')[3],
            'url': window.location.href
        };
        sendE4Request(e4_params);
    });

    $('[data-e4-cart]').on('click', function() {
        var e4_params = {
            'event': 'add_to_cart',
            'item_id': $(this).data('e4-cart')[0],
            'list': $(this).data('e4-cart')[1],
            'index': $(this).data('e4-cart')[2],
            'list_id' : $(this).data('e4-cart')[3],
            'url': window.location.href
        };
        sendE4Request(e4_params);
    });

    $('#button-cart, [data-e4-p-cart]').on('click', function() {
        var e4_params = {
            'event': 'add_to_cart',
            'item_id': typeof($($(this).data('e4-item-id')).val()) != 'undefined' ? $($(this).data('e4-item-id')).val() : $(this).closest('div').find('input[name=\'product_id\']').val(),
            'quantity': typeof($($(this).data('e4-quantity')).val()) != 'undefined' ? $($(this).data('e4-quantity')).val() : $(this).closest('div').find('input[name=\'quantity\']').val(),
            'option': typeof($($(this).data('e4-option')).val()) != 'undefined' ? $($(this).data('e4-option')).serializeArray() : $('[name^=\'option\']').serializeArray(),
            'url': window.location.href
        };
        sendE4Request(e4_params);
    });

    $(document).on('click', '[onclick*=\'wishlist.add\']', function() {
        var e4_data = $(this).attr('onclick').replace(/'/g, '').replace(/^.*wishlist\.add\(/, '').replace(/\);|\)/g, '').split(',');
        if (e4_data[0]) {
            var e4_params = {
                'event': 'add_to_wishlist',
                'item_id': e4_data[0],
                'list': $(this).data('e4-list'),
                'list_id': $(this).data('e4-list-id'),
                'index': typeof($(this).data('e4-index')) != 'undefined' ? $(this).data('e4-index') : $('[onclick*=\'wishlist.add\']').index(this) + 1,
                'url': window.location.href
            };
            sendE4Request(e4_params);
        }
    });

    $(document).on('click', '[onclick*=\'addToWishList\']', function() {
        var e4_data = $(this).attr('onclick').replace(/'/g, '').replace(/^.*addToWishList\(/, '').replace(/\);|\)/g, '').split(',');
        if (e4_data[0]) {
            var e4_params = {
                'event': 'add_to_wishlist',
                'item_id': e4_data[0],
                'list': $(this).data('e4-list'),
                'list_id': $(this).data('e4-list-id'),
                'index': typeof($(this).data('e4-index')) != 'undefined' ? $(this).data('e4-index') : $('[onclick*=\'wishlist.add\']').index(this) + 1,
                'url': window.location.href
            };
            sendE4Request(e4_params);
        }
    });

    $('[data-e4-wishlist]').on('click', function() {
        var e4_params = {
            'event': 'add_to_wishlist',
            'item_id': $(this).data('e4-wishlist')[0],
            'list': $(this).data('e4-wishlist')[1],
            'index': $(this).data('e4-wishlist')[2],
            'list_id' : $(this).data('e4-wishlist')[3],
            'url': window.location.href
        };
        sendE4Request(e4_params);
    });
});

var e4_start = 0;
function setE4Interval(callback, global) {
    var x = 0;
    var intervalEE = window.setInterval(function () {
        if ((global && ee_start) || (!global && document.cookie.match('(?:^|;)\\s*(_ga=|__utma=)([^;]*)')) || ++x > 5) {
            callback();
            window.clearInterval(intervalEE);
        }
    }, 1000);
}

var e4_position = 0;
var e4_timeout = 600;

function showE4Log(log) {
    log = log.split('\n')[0];
    var seconds = new Date().getTime();
    if ((seconds - e4_position) > 1000) {
        $('body').append('<div id="l-' + seconds + '" style="position: fixed;bottom: 0;z-index: 999999;opacity: .3;display: none;height: 20%"><div class="log" style="background-color: #A8F760;padding: 2px 10px;border-radius: 0 5px 5px 0;font: normal normal 12px sans-serif;">' + log + '</div></div>');
        e4_position = seconds;
        $('#l-' + seconds).animate({
            left: 'toggle',
            height: '20',
            opacity: 0.9
        }, 3000, 'linear', function() {
            $('#l-' + seconds).fadeOut('slow', function(){
                $('#l-' + seconds).remove();
            });
        });
        e4_timeout = 600;
    } else {
        var log_id = 'l-' + seconds + e4_timeout;
        e4_position = seconds;
        setTimeout(function(){
            $('body').append('<div id="' + log_id + '" style="position: fixed;bottom: 0;z-index: 999999;opacity: .3;display: none;height: 20%"><div class="log" style="background-color: #A8F760;padding: 2px 10px;border-radius: 0 5px 5px 0;font: normal normal 12px sans-serif;">' + log + '</div></div>');
            $('#' + log_id).animate({
                left: 'toggle',
                height: '20',
                opacity: 0.9
            }, 3000, 'linear', function() {
                $('#' + log_id).fadeOut('slow', function(){
                    $('#' + log_id).remove();
                });
            });
        }, e4_timeout);
        e4_timeout += 600;
    }
}

function sendE4Request(data) {
    $.ajax({
        url: 'index.php?route=extension/module/ecommerce_ga4',
        type: 'post',
        data: data,
        dataType: 'json',
        success: function(json) {
            if (json['params']) {
                if (json['type'] == 1) {
                    gtag('event', json['event'],  json['params']);
                } else if (json['type'] == 2) {
                    dataLayer.push({ ecommerce: null });
                    dataLayer.push(json['params']);
                }
            }
            if (json['log']) {
                console.log(json['log']['text']);
                if (json['log']['show']) {
                    showE4Log(json['log']['text']);
                }
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.status + ' - ' + xhr.statusText);
        }
    });
}

var e4_promotion = {
    'select': function (item_id, promotion_id, index, image_id) {
        var e4_params = {
            'event': 'select_promotion',
            'item_id': item_id,
            'promotion_id': promotion_id,
            'index': index,
            'image_id' : image_id,
        };
        sendE4Request(e4_params);
    }
}

var e4_item = {
    'select': function (item_id, list, index, list_id) {
        var e4_params = {
            'event': 'select_item',
            'item_id': item_id,
            'list': list,
            'list_id' : list_id,
            'index': index,
            'url': window.location.href
        };
        sendE4Request(e4_params);
    },
    'add_to_cart': function (item_id, quantity, option, list) {
        var e4_params = {
            'event': 'add_to_cart',
            'item_id': typeof(item_id) != 'undefined' ? item_id : $('input[name=\'product_id\']').val(),
            'quantity': typeof(quantity) != 'undefined' ? quantity : $('input[name=\'quantity\']').val(),
            'option': typeof(option) != 'undefined' ? option : $('[name^=\'option\']').serializeArray(),
            'list': list,
            'url': window.location.href
        };
        sendE4Request(e4_params);
    },
}

var e4_checkout = {
    'add_shipping_info': function () {
        var e4_params = {
            'event': 'add_shipping_info',
            'code': $('input[name=\'shipping_method\']').length ? $('input[name=\'shipping_method\']:checked').val() : $('select[name=\'shipping_method\']').val(),
        };
        sendE4Request(e4_params);
    },
    'add_payment_info': function () {
        var e4_params = {
            'event': 'add_payment_info',
            'code': $('input[name=\'payment_method\']').length ? $('input[name=\'payment_method\']:checked').val() : $('select[name=\'payment_method\']').val(),
        };
        sendE4Request(e4_params);
    },
    'add_shipping_info_custom': function (code) {
        var e4_params = {
            'event': 'add_shipping_info',
            'code': code,
        };
        sendE4Request(e4_params);
    },
    'add_payment_info_custom': function (code) {
        var e4_params = {
            'event': 'add_payment_info',
            'code': code,
        };
        sendE4Request(e4_params);
    },
}