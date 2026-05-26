<?php $tmp_id=time(); ?>
<form id="dae-form-attribute-value" class="form-horizontal">
    <input type="hidden" name="attribute_id" value="<?= $attribute_id;?>">
    <input type="hidden" name="dae_attribute_value[attribute_value_id]" value="<?= $attribute_value['attribute_value_id']; ?>" />
    <input type="hidden" name="dae_attribute_value[attribute_id]" value="<?= $attribute_value['attribute_id']; ?>" />
    <input type="hidden" name="update_value_in_product" value="0" id="update_value_in_product">
 <div class="form-group">
        <div class="col-sm-3">

            <a href="" id="thumb-image<?= $tmp_id; ?>" data-toggle="image" class="img-thumbnail">
                <img src="<?= $attribute_value['thumb_image']; ?>" alt="" title="" data-placeholder="<?= $dae_fav_text_value; ?>"  style="width:30px;" />
            </a>
            <input type="hidden" name="dae_attribute_value[image]" value="<?= $attribute_value['image']; ?>" id="input-image<?= $tmp_id; ?>" />

        </div>
        <div class="col-sm-9">
            <input type="text" name="dae_attribute_value[url]" value="<?= (isset($attribute_value['url']) ? $attribute_value['url'] : ''); ?>" placeholder="<?= $dae_fav_value_url; ?>" class="form-control" data-original-title="<?= $dae_fav_value_url; ?>" data-toggle="tooltip"/>
        </div>
    </div>
            <?php if(count($languages)>1){ ?>
            <ul class="nav nav-tabs" id="language">
                <?php $i=0;?>
                <?php foreach ($languages as $language) { ?>
                <li class="<?php echo ($i==0)?'active':''?>"><a href="#language_<?= $language['language_id']; ?>_<?= $tmp_id; ?>" data-toggle="tab"><img src="<?= $language['path_image']; ?>" title="<?= $language['name']; ?>"  /> <?= $language['name']; ?></a></li>
                <?php $i++;?>
                <?php } ?>
            </ul>
            <?php } ?>
            <div class="tab-content" style="background:#fff;">
                <?php $i=0;?>
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane <?= (($i == 0)?'active':'') ?>" id="language_<?= $language['language_id']; ?>_<?= $tmp_id; ?>">
                    <div class="form-group">
                        <div class="col-sm-12 input_attribute_value" id="by_language_<?= $language['language_id']; ?>">
                            <input class="form-control text-value new_value _by_language_<?= $language['language_id']; ?>" type="text" name="dae_attribute_value[description][<?= $language['language_id']; ?>][text]" value="<?= (isset($attribute_value['description'][$language['language_id']]['text']) ? $attribute_value['description'][$language['language_id']]['text'] : ((!empty($default_value))?$default_value:'')); ?>" placeholder="<?= $dae_fav_text_value ?>"  data-original-title="<?= $dae_fav_value_description; ?>" data-toggle="tooltip"/>
                            <input class="old_value _by_language_<?= $language['language_id']; ?>" type="hidden" name="dae_attribute_value[description][<?= $language['language_id']; ?>][old_text]" value="<?= (isset($attribute_value['description'][$language['language_id']]['text']) ? $attribute_value['description'][$language['language_id']]['text'] : ''); ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-12">
                            <textarea name="dae_attribute_value[description][<?= $language['language_id']; ?>][description]" placeholder="<?= $dae_fav_value_description; ?>" class="form-control" data-original-title="<?= $dae_fav_value_description; ?>" data-toggle="tooltip"><?= (isset($attribute_value['description'][$language['language_id']]['description']) ? $attribute_value['description'][$language['language_id']]['description'] : ''); ?></textarea>
                        </div>
                    </div>

                </div>
                <?php $i++;?>
                <?php } ?>
            </div>



    <div class="col-sm-12">
        <div class="form-group">
            <div class="checkbox" >
                <label class="col-sm-12">
                    <input type="checkbox" class="check_default"  name="dae_attribute_value[default]" value="1" <?= (isset($attribute_value['default']) && $attribute_value['default']==1)?'checked':''; ?>/> <?= $dae_lang_default;?>
                </label>
            </div>
        </div>
    </div>
    <?php if(!empty($settings['dae_value_category'])){ ?>
    <div class="col-sm-12">
        <?= $_form_select_category ;?>
    </div>

    <?php } ?>

</form>

<script>

    var attribute_id = <?= $attribute_id; ?>;
    var thumb_image_no_image = '<?= $thumb_image_no_image; ?>';
    var view_message_confirm_update_value_in_product = false;//типа флага, чтобы сообщение показывалось один раз для нескольких языков
    $('#dae-modal-button-save_form_attribute_value').click(function () {
    //проверка на непустое значение
    if ($(".text-value").is('empty')) {
            modalAlert.viewDanger("<?= $dae_fav_empty_value;?>");
            return false;
    }
    //проверка на измененное значение
    $('.input_attribute_value').each(function(){
            if (view_message_confirm_update_value_in_product === false && $(this).find('.old_value').val() != '' && $(this).find('.new_value').val() != $(this).find('.old_value').val()) {
                view_message_confirm_update_value_in_product = true;
                if (confirm("Значение атрибута изменено. Заменить в товарах?")) {
                        $('#update_value_in_product').val(1);
    }
    }
    });

        $.ajax({
                        url: JS_URL_SAVE_FORM_ATTRIBUTE_VALUE,
                        type: "POST",
                        data: $('#dae-form-attribute-value').serialize(),
                        dataType: 'json',
                        success: function (json) {
                            if (json.status === DAE_STATUS_SUCCESS) {
                                daeModal.hide();
                                if (json.is_new_attribute_value) {
                                            daeEvent.dispatch(DAE_EVENT_CREATE_ATTRIBUTE_VALUE, {attribute_value: json.attribute_value});
                    } else {
                                                    daeEvent.dispatch(DAE_EVENT_UPDATE_ATTRIBUTE_VALUE, {attribute_value: json.attribute_value});
                    }
                    layoutAlert.handlerByResponse(json);
                    } else {
                                            modalAlert.handlerByResponse(json);
                    }
                    }
                    });
                    });




                    /*
                    $.ajax({
                                            url: $('#dae-form-attribute-value').attr('action'),
                                                    type: "POST",
                                                    data: $('#dae-form-attribute-value').serialize(),
                                                    dataType: 'json',
                                                    success: function(json) {
                                                    viewMessage('.dae_form_attribute_value', 'success', "<?php echo $dae_text_success;?>");
                                                            //поведение после сохранения на странице Атрибутов
                                                            if (json.place == 'attribute'){
                                                    addRowAttributeValue(work_row, json.dae_attribute_value);
                }
                //поведение после сохранения на странице Товара

                    if(json.place == 'product'){

                                                            //for (var attribute_value_id in json.dae_attribute_value){
                                                            for (var language_id in json.dae_attribute_value['description']){ //выведем на экран все элементы
                                                    //for (var attribute_value_id in json.devos_attributes_values[attribute_value_id]['description'][language_id]){ //выведем на экран все элементы
                                                    attribute_value = json.dae_attribute_value['description'][language_id]['text'];
                                                            //console.log(attribute_value + ' '+focus_select_row);
                                                            $('input[name=\'product_attribute[' + focus_select_row + '][product_attribute_description][' + language_id + '][text]\']').val(attribute_value);
                                                            //}
        }
    //}
        }
        $('#dae-form-attribute-value input,#dae-form-attribute-value button').tooltip('destroy');
        $("#daeModalBox").modal('hide');
    }
    });
    }*/

</script>