/** OpenCart Ecommerce GA4 v1.0.5 -- https://vanstudio.co.ua -- */

$(document).ready(function() {
    $('select[name=\'store_id\']').change(function() {
        if (confirm($(this).data('confirm'))) {
            if (window.location.search.indexOf('store_id=') > -1) {
                window.location.href = window.location.href.replace('store_id=' + $(this).data('store_id'), 'store_id='+ $(this).val());
            } else {
                window.location.href = window.location.href + '&store_id='+ $(this).val();
            }
        } else {
            $(this).val($(this).data('store_id'));
        }
    });

    $('input').on('click', function() {
        if ($(this).closest('.form-group').hasClass('has-error') || $(this).closest('.form-group').hasClass('has-warning')) {
            $(this).closest('.form-group').removeClass('has-error has-warning');

            var help_block = $(this).closest('.form-group').find('.help-block[class*=\'error-\']');

            if (help_block.data('text')) {
                help_block.text(help_block.data('text'));
            } else {
                help_block.text('');
            }

            $('a[href=\'#' + $(this).closest('.tab-left').attr('id') + '\']').removeClass('bg-danger bg-warning');
        }
    });

    $('input[name^=\'purchase_tracking_status\']').change(function() {
        if ($(this).is(':checked')) {
            $('input[name^=\'refund_tracking_status\'][value=\'' + $(this).val() + '\']').attr('disabled', true).closest('.checkbox').hide();
        } else {
            $('input[name^=\'refund_tracking_status\'][value=\'' + $(this).val() + '\']').attr('disabled', false).closest('.checkbox').show();
        }


    });
    $('input[name^=\'refund_tracking_status\']').change(function() {
        if ($(this).is(':checked')) {
            $('input[name^=\'purchase_tracking_status\'][value=\'' + $(this).val() + '\']').attr('disabled', true).closest('.checkbox').hide();
        } else {
            $('input[name^=\'purchase_tracking_status\'][value=\'' + $(this).val() + '\']').attr('disabled', false).closest('.checkbox').show();
        }
    });

    $('#orders').delegate('.pagination a', 'click', function(e) {
        e.preventDefault();
        reloadOrderList(this.href);
    });

    $('#orders').delegate('.sort-col a', 'click', function(e) {
        e.preventDefault();
        reloadOrderList(this.href);
    });

    $('button.btn').on('click', function() {
        $(this).tooltip('hide');
    });
});

function changeTranslator(text_confirm) {
    if (confirm(text_confirm)) {
        if (window.location.search.indexOf('translator=') > -1) {
            window.location.href = window.location.href.replace('&translator=1', '');
        } else {
            window.location.href = window.location.href + '&translator=1';
        }
    }
}

function changeToggleSwitch(element, enabled, disabled) {
    if (parseInt(element.parent().find('input').val())) {
        element.parent().find('input').val(0);
        element.removeClass('fa-toggle-on fa-toggle-off').addClass('fa-toggle-off').attr('data-original-title', typeof disabled !== 'undefined' ? disabled : 'Disabled');

        if (element.data('fade')) {
            $(element.data('fade')).fadeOut();
        }
        if (element.data('fadein')) {
            $(element.data('fadein')).fadeIn();
        }
    } else {
        element.parent().find('input').val(1);
        element.removeClass('fa-toggle-on fa-toggle-off').addClass('fa-toggle-on').attr('data-original-title', typeof enabled !== 'undefined' ? enabled : 'Enabled');
        if (element.data('fade')) {
            $(element.data('fade')).fadeIn();
        }
        if (element.data('fadein')) {
            $(element.data('fadein')).fadeOut();
        }
    }
    if (element.data('opacity')) {
        if ($(element.data('opacity')).hasClass('opacity-on')) {
            $(element.data('opacity')).removeClass('opacity-on');
        } else {
            $(element.data('opacity')).addClass('opacity-on');
        }
    }
}

function bulkStatusChange(btn) {
    $('.btn-status').each(function() {
        if ($(this).parent().find('input').val() != btn.parent().find('input').val()) {
            $(this).trigger('click');
        }
    });
}

function changeTrackingType(btn, type) {
    btn.closest('.form-group').find('button.active').removeClass('active');
    $('input[name=\'type\']').val(type);
    btn.addClass('active').blur();
    btn.closest('.form-group').find('.text-info').html(btn.data('note'));
    switch (type) {
        case 0:
            $('.gmp-group').fadeIn();
            $('.not-gmp-group').fadeOut();
            $('.gtm-group').fadeOut();
            $('.not-gtm-group').fadeIn();
            if ($('input[name=\'validation_mode\']').val() == 1) {
                $('.debug-view-mode').animate({opacity: 0.4}, 500);
                $('.alert-validation-mode').fadeIn();
            } else {
                $('.debug-view-mode').animate({opacity: 1}, 500);
                $('.alert-validation-mode').fadeOut();
            }
            break;
        case 1:
            $('.gmp-group').fadeOut();
            $('.not-gmp-group').fadeIn();
            $('.gtm-group').fadeOut();
            $('.not-gtm-group').fadeIn();
            $('.debug-view-mode').animate({opacity: 1}, 500);
            $('.alert-validation-mode').fadeOut();
            break;
        case 2:
            $('.gtm-group').fadeIn();
            $('.not-gtm-group').fadeOut();
            $('.gmp-group').fadeOut();
            $('.not-gmp-group').fadeIn();
            $('.debug-view-mode').animate({opacity: 1}, 500);
            $('.alert-validation-mode').fadeOut();
            break;
    }
}

function changeValidationMode(element) {
    if (element.parent().find('input[name=\'validation_mode\']').val() == 1) {
        $('.alert-validation-mode').fadeIn();
        $('.debug-view-mode').animate({opacity: 0.4}, 500);
        if ($('input[name=\'log\']').val() == 0) {
            $('input[name=\'log\']').next('i').click();
        }
    } else {
        $('.debug-view-mode').animate({opacity: 1}, 500);
        $('.alert-validation-mode').fadeOut();
    }
}

function changeOpacity(select, element) {
    if (select.val() == '0') {
        element.animate({opacity: 1}, 500);
    } else {
        element.animate({opacity: 0.4}, 500);
    }
}

function addFilterValue(name, value, separator) {
    if ($.trim($('textarea[name=\'' + name + '\']').val()) == '') {
        $('textarea[name=\'' + name + '\']').val(value);
    } else {
        $('textarea[name=\'' + name + '\']').val($('textarea[name=\'' + name + '\']').val() + separator + value);
    }
}

function submitForm(btn, form_id) {
    $('.form-group').removeClass('has-error has-warning');
    $('a.bg-danger').removeClass('bg-danger bg-warning');
    $('[class*=\'error-\']').empty();

    if (typeof form_id == 'undefined') {
        form_id = 'form-general';
    }

    $.ajax({
        type: 'post',
        url: $('#' + form_id).attr('action'),
        data: $('#' + form_id).serialize(),
        dataType: 'json',
        beforeSend: function() {
            btn.button('loading');
            $('.loading-wrapper').fadeIn();
        },
        complete: function() {
            btn.button('reset');
            $('.loading-wrapper').fadeOut();
        },
        success: function(json) {
            $('.loading-wrapper').fadeOut();

            if (json.message) {
                alertEMessage(json.message);
            }

            if (json.warning) {
                alertEError(json.warning);
            }

            if (json.error) {
                alertEError(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            if (xhr.responseText.toString().indexOf('index.php?route=common/forgotten') !== -1) {
                alertEMessage(btn.data('session'));
            }
            console.log(xhr.statusText + "\r\n" + xhr.responseText);
            $('.loading-wrapper').fadeOut();
        }
    });
}

function alertEWarning(error) {
    $.each(error, function(key, value) {
        if (key == 'message') {
            alertEMessage(value);
        } else {
            if (typeof value === 'object') {
                $.each(value, function(key2, value2) {
                    $('.error-' + key + '-' + key2).html(value2).closest('.form-group').addClass('has-warning');
                    $('a[href=\'#' + $('.error-' + key + '-' + key2).closest('.tab-left').attr('id') + '\']').addClass('bg-warning');
                });
            } else {
                $('.error-' + key).html(value).closest('.form-group').addClass('has-warning');
                $('a[href=\'#' + $('.error-' + key).closest('.tab-left').attr('id') + '\']').addClass('bg-warning');
            }
        }
    });
}

var progress = false;

function alertEError(error) {
    $.each(error, function(key, value) {
        if (key == 'message') {
            alertEMessage(value);
        } else {
            if (typeof value === 'object') {
                $.each(value, function(key2, value2) {
                    $('.error-' + key + '-' + key2).html(value2).closest('.form-group').addClass('has-error');
                    $('a[href=\'#' + $('.error-' + key + '-' + key2).closest('.tab-left').attr('id') + '\']').addClass('bg-danger');
                });
            } else {
                $('.error-' + key).html(value).closest('.form-group').addClass('has-error');
                $('a[href=\'#' + $('.error-' + key).closest('.tab-left').attr('id') + '\']').addClass('bg-danger');
            }
        }
    });
}

function alertEMessage(message) {
    $('.message-wrapper').html('').fadeIn();

    if (typeof message === 'object') {
        var msg =  message;
    } else {
        var msg =  { text: message };
    }

    if (progress) {
        clearInterval(progress);
        progress = false;
    }

    if (!msg.hasOwnProperty('type')) {
        msg.type = 'danger';
    }
    if (!msg.hasOwnProperty('icon')) {
        msg.icon = 'fa-info-circle';
    }

    if (!msg.hasOwnProperty('delay')) {
        msg.delay = 5500;
    }

    var html = '<div class="progress">';
    html += '<div class="progress-bar progress-bar-' + msg.type + '" role="progressbar" style="width: 0px" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>';
    html += '</div>';
    html += '<div class="alert alert-' + msg.type + ' alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button><i class="fa ' + msg.icon + '"></i> ' + msg.text + '</div>';
    $('.message-wrapper').html(html);

    var percent = 0;
    progress = setInterval(function () {
        $('.message-wrapper .progress-bar').attr('style', 'width:' + percent + '%').attr('aria-valuenow', percent);
        if (percent > 100) {
            clearInterval(progress);
            progress = false;
        }
        percent += 1;
    }, 50);

    if (msg.delay) {
        $('.message-wrapper').delay(msg.delay).fadeOut();
    }


    $('button.close').on('click', function () {
        $(this).parent().prev('.progress').hide();
    });
}

function showModalImg(src) {
    $('#modal-general').find('img').attr('src', src);
    $('#modal-general').modal('show');
}

function clearELog(btn, url) {
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        beforeSend: function() {
            btn.button('loading');
            $('.loading-wrapper').fadeIn();
        },
        complete: function() {
            btn.button('reset');
            $('.loading-wrapper').fadeOut();
        },
        success: function (json) {
            if (json.success) {
                $('textarea[id=\'logs\']').val('');
                alertEMessage(json.success);
            } else if (json.error) {
                alertEError(json.error);
            }
            btn.button('reset');
            $('.loading-wrapper').fadeOut();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            if (xhr.responseText.toString().indexOf('index.php?route=common/forgotten') !== -1) {
                alertEMessage($('button[onclick^=\'submitForm(\']').data('session'));
            }
            btn.button('reset');
            $('.loading-wrapper').fadeOut();
        }
    });
}

function refreshELog(btn, url) {
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        beforeSend: function() {
            btn.button('loading');
            $('.loading-wrapper').fadeIn();
            $('textarea[id=\'logs\']').val('');
        },
        complete: function() {
            btn.button('reset');
            $('.loading-wrapper').fadeOut();
        },
        success: function (json) {
            $('textarea[id=\'logs\']').val(json.logs);
            btn.button('reset');
            $('.loading-wrapper').fadeOut();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            if (xhr.responseText.toString().indexOf('index.php?route=common/forgotten') !== -1) {
                alertEMessage($('button[onclick^=\'submitForm(\']').data('session'));
            }
            btn.button('reset');
            $('.loading-wrapper').fadeOut();
        }
    });
}

$(document).on('change', 'input#support-data', function () {
    if ($(this).prop('checked') == true) {
        $('#btn-support').attr('href', $('#btn-support').attr('href') + $(this).val());
    } else {
        $('#btn-support').attr('href', $('#btn-support').attr('href').replace($(this).val(), ''));
    }
});