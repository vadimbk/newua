<div class ="row">
    <div class="col-xs-12 col-sm-6">
        <?= $_form_select_attribute_value ?>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="text-right">
            <button type="button" class="btn btn-success dae-form-generic-attribute-value" data-toggle="tooltip" title="<?= $_form_generic_attribute_values;?>" data-attribute_id="<?= $attribute_id;?>"><i class="fa fa-plus-circle"></i></button>
            <button type="button" class="btn btn-primary dae-form-attribute-value" data-toggle="tooltip" title="<?php echo $button_option_value_add; ?>" data-attribute_value_id="0" data-attribute_id="<?= $attribute_id; ?>"><i class="fa fa-plus"></i></button>
            <button type="button" class="btn btn-danger dae-remove-attribute-values" data-toggle="tooltip" title="<?php echo $dae_form_a_v_remove_all;?>"><i class="fa fa-trash-o"></i></button>
            <button type="button" class="btn btn-default dae-form-settings-local" data-group="attribute_values_list" data-toggle="tooltip" title="<?php echo $dae_form_a_v_generic;?>"><i class="fa fa-cog"></i></button>
        </div>
    </div>
</div>
<div id="box-attribute-values">
    <?= $content_attribute_values; ?>
</div>


<script>

    var attribute_id = '<?php echo $attribute_id; ?>';
    var thumb_image_no_image = '<?php echo $thumb_image_no_image; ?>';


    /*
     * Удаление всех значений
     */
    $('body').on('click', '.dae-remove-attribute-values', function () {
        if (confirm("<?= $dae_avl_confirm_remove_val_all;?>")) {
            $.ajax({
                url: JS_URL_DELETE_ATTRIBUTE_VALUES,
                type: 'POST',
                dataType: 'json',
                data: {attribute_id: attribute_id},
                success: function (json) {
                    layoutAlert.handlerByResponse(json);
                    if (json.status == DAE_STATUS_SUCCESS) {
                        $('div.box-attribute-values').remove();
                        daeGetListValuesByAttribute(attribute_id, 1);
                    }
                }
            });

        }

    });
    /*
     * Удаление строки со значением
     */
    $('body').on('click', '.dae-remove-attribute-value', function () {
        $(this).tooltip('destroy');
        if (confirm("<?= $dae_avl_confirm_remove_val;?>")) {
            var attribute_value_id = $(this).closest('tr').data('attribute_value_id');
            $.ajax({
                url: JS_URL_DELETE_ATTRIBUTE_VALUE,
                type: 'POST',
                dataType: 'json',
                data: {attribute_value_id:attribute_value_id},
                success: function (json) {
                    layoutAlert.handlerByResponse(json);
                    if (json.status == DAE_STATUS_SUCCESS) {
                        daeEvent.dispatch(DAE_EVENT_DELETE_ATTRIBUTE_VALUE, {attribute_value_id: json.attribute_value_id});
                    }
                }
            });
        }
    });
    $('body').on('click', '.dae-form-generic-attribute-value', function () {
        $.ajax({
            url: JS_URL_GET_FORM_GENERIC_ATTRIBUTE_VALUES,
            type: 'GET',
            dataType: 'json',
            data: {attribute_id: $(this).data('attribute_id')},
            success: function (json) {
                layoutAlert.handlerByResponse(json);
                if(json.status == DAE_STATUS_SUCCESS){
                    daeModal.init(json.modal);
                }
            }
        });
    });

    $('body').on('click', '.pagination a', function (event) {
        event.preventDefault();
        var href = $(this).attr('href');
        var url = getUrlParams(href);
        daeGetListValuesByAttribute(url.attribute_id, (url.hasOwnProperty('page')) ? url.page : 1);
    });
    /*$('body').on('click', '.dae-form-settings-attribute-value', function(){
    $.ajax({
            url: JS_URL_GET_FORM_SETTINGS_ATTRIBUTE_VALUE,
            type: 'GET',
            dataType: 'json',
            data: {},
            success: function (json) {
                layoutAlert.handlerByResponse(json);
                if(json.status == DAE_STATUS_SUCCESS){
                    daeModal.init(json.modal);
                }
            }
        });
    });*/
    function daeGetListValuesByAttribute(attribute_id, page, reload) {
        formSelectAttributeValue.clearFields();
        page = page || 1;
        reload = reload || false;
        if(reload){
            $('div#attribute_values_' + attribute_id + '_page_' + page).remove();
        }
        if (!$('div').is('#attribute_values_' + attribute_id + '_page_' + page)) {
            //$('#tab-dae-attribute' + attribute_group_id + ' > p').removeClass('hide');
            $.ajax({
                url: JS_URL_GET_LIST_VALUES_BY_ATTRIBUTE,
                type: "GET",
                data: {attribute_id: attribute_id, page: page, is_ajax: true},
                dataType: 'json',
                success: function (json) {
                    //каждая страница в отдельном диве, поэтому сначала скроем остальные
                    $('div.box-attribute-values').addClass('hide');
                    if (json.status == DAE_STATUS_SUCCESS) {
                        $('#box-attribute-values').append(json.html);
                    }
                    layoutAlert.handlerByResponse(json);
                }
            });
        } else {
            $('div.box-attribute-values').addClass('hide');
            $('#attribute_values_' + attribute_id + '_page_' + page).removeClass('hide');
        }
    }
    function loadTemplateAttributeValue(data) {
        html  = ' <tr id="attribute-value-'+data['attribute_value_id']+'" data-attribute_id="'+data['attribute_id']+'" data-attribute_value_id="'+data['attribute_value_id']+'">';
        html += '   <input type="hidden" name="dae_attribute_value[][attribute_value_id]" value="'+data['attribute_value_id']+'" />';
        html += '   <td>#' + data['attribute_value_id'] + '</td>';
        html += '   <td class="text-center dae_a_v_default">';
        if (data['default'] > 0 ){
          <?php if(!isset($setting_attribute['type_edit']) || $setting_attribute['type_edit'] != 'multi'){?>
            //уберем отметку у остальных
            $('td.dae_a_v_default').html('');
          <?php } ?>
          html += '   <i class="fa fa-check"></i>';
        }

        html += '   </td>';

        html += '   <td class="text-center dae_a_v_img">';
        html += '         <img src="' + data['thumb_image'] + '" alt="" title="" style="width:30px;" />';
        html += '   </td>';
        html += '   <td class="text-left dae_a_v_text">';
        <?php foreach ($languages as $language) { ?>
        html += '     <span>';
        html += '       <img src="<?php echo $language['path_image']; ?>" title="<?php echo $language['name']; ?>" />';
        html += '     </span>';
        html += '     <span class="dae_a_v_text_language_<?php echo $language['language_id'];?>">';
        html += data['description'][<?php echo $language['language_id']?>]['text'];
        html += '     </span><br>';
        <?php } ?>
        html += '   </td>';

        if(Number.parseInt(dae_settings.dae_form_view_url)){
          html += '   <td class="text-left dae_a_v_url">';
          html += data['url'];
          html += '   </td>';
        }

        if(Number.parseInt(dae_settings.dae_form_view_description)){
          html += '   <td class="text-left dae_a_v_description">';
          <?php foreach ($languages as $language) { ?>
            html += '   <span>';
            html += '     <img src="<?php echo $language['path_image']; ?>" title="<?php echo $language['name']; ?>" />';
            html += '   </span>';
            html += '   <span class="dae_a_v_description_language_<?php echo $language['language_id'];?>">';
            html += data['description'][<?php echo $language['language_id'];?>]['description'];
            html += '   </span><br>';
          <?php } ?>
          html += '  </td>';
        }

        if(Number.parseInt(dae_settings.dae_value_category)){
          html += '<td>';
          for(var i in data['attribute_values_category']){
            html += data['attribute_values_category'][i]['name']+'<br>';
          }
          html += '</td>';
        }

        html += '  <td class="text-right">';
        html += '    <button type="button" data-toggle="tooltip" title="<?php echo $button_edit;?>" class="btn btn-primary dae-form-attribute-value" data-attribute_value_id="'+data['attribute_value_id']+'" data-attribute_id="'+data['attribute_id']+'"><i class="fa fa-pencil"></i></button>';
        html += '    <button type="button" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger dae-remove-attribute-value"><i class="fa fa-minus-circle"></i></button>';

        html += '  </td>';
        html += '</tr>';
        return html;
        //$('#attribute-value tbody').append(html);

        //$('#attribute-value tbody tr#attribute-value-row'+row).html(html);

    }
    document.addEventListener(DAE_EVENT_RESET_ATTRIBUTE_VALUE, function(e) {
        $('#box-attribute-values div').remove();
        daeGetListValuesByAttribute(attribute_id, 1);
    });

    document.addEventListener(DAE_EVENT_DELETE_ATTRIBUTE_VALUE, function(e) {
        var activePageBox = $('.box-attribute-values:not(.hide)');
         //удаление строки во вьюхе
        activePageBox.find('tr#attribute-value-' + e.detail.attribute_value_id).remove();

        var activePagination = activePageBox.find('ul.pagination li.active span');
        //страницу будем обновлять только если выведено несколько страниц, иначе смысла нет
        if(activePagination.length > 0){

            var page = (activePagination.length)?activePagination.text():1;
            //если это была единственная строка, то уменьшим страницы на 1 и перезагрузим ее
            if(activePageBox.find('table tbody tr').length == 0 && page > 1) {
                page = page-1;
            }
            $('#box-attribute-values div').remove();
            daeGetListValuesByAttribute(attribute_id, page);
        }
    });
    //при обновлении атрибут будет находится на этой же странице поуэтому просто ее перезагрузим
    document.addEventListener(DAE_EVENT_UPDATE_ATTRIBUTE_VALUE, function(e) {
    loadTemplateAttributeValue(e.detail.attribute_value)
        $('#attribute-value-'+e.detail.attribute_value.attribute_value_id).replaceWith(loadTemplateAttributeValue(e.detail.attribute_value));
        //var page = ($('ul.pagination li.active span').length)?$('ul.pagination li.active span').text():1;
        //daeGetListValuesByAttribute(attribute_id, page, true);
    });
    document.addEventListener(DAE_EVENT_CREATE_ATTRIBUTE_VALUE, function(e) {
        //$('#attribute-values tbody').append(loadTemplateAttributeValue(e.detail.attribute_value));
        //var attribute_id = e.detail.attribute_value.attribute_id;
        var activePagination = $('.box-attribute-values:not(.hide) ul.pagination li.active span');
        var page = (activePagination.length)?activePagination.text():1;
        $('#box-attribute-values div').remove();
        daeGetListValuesByAttribute(attribute_id, page);
    });
    document.addEventListener(DAE_EVENT_GENERATE_ATTRIBUTE_VALUES, function(e) {
        $('#box-attribute-values div').remove();
        daeGetListValuesByAttribute(e.detail.attribute_id, 1);
    });
    document.addEventListener(DAE_EVENT_CHANGE_SETTINGS_LOCAL, function(e) {
        //var change_settings = e.detail.change_settings;
        $('div.box-attribute-values').remove();
        daeGetListValuesByAttribute(attribute_id, 1);
    });


    document.addEventListener(DAE_EVENT_SELECT_ATTRIBUTE_VALUE, function(e) {
        //var change_settings = e.detail.change_settings;
        $('#box-attribute-values div').remove();


        //$('#tab-dae-attribute' + attribute_group_id + ' > p').removeClass('hide');
        $.ajax({
            url: JS_URL_SEARCH_ATTRIBUTE_VALUE,
            type: "GET",
            data: {
                attribute_id: e.detail.attribute_id,
                attribute_value_name: e.detail.attribute_value_name
                },
            dataType: 'json',
            success: function (json) {
                if (json.status == DAE_STATUS_SUCCESS) {
                    $('#box-attribute-values').append(json.html);
                }
                layoutAlert.handlerByResponse(json);
            }
        });

    });

</script>
