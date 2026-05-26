<form id="dae-form-attribute-group" class="form-horizontal">
    <input type="hidden" name="attribute_group_id" value="<?php echo $attribute_group_id;?>">
    <div class="form-group required">
        <label class="col-sm-4 control-label"><?php echo $dae_fag_attribute_group_name; ?></label>
        <div class="col-sm-8">
            <?php foreach ($languages as $language) { ?>
            <div class="input-group">
                <span class="input-group-addon">
                    <img src="<?php echo $language['path_image']; ?>" title="<?php echo $language['name']; ?>" />
                </span>
                <input type="text" name="attribute_group_description[<?php echo $language['language_id']; ?>][name]" value="<?php echo isset($attribute_group_description[$language['language_id']]) ? $attribute_group_description[$language['language_id']]['name'] : $default_name; ?>"           placeholder="<?= $dae_fag_attribute_group_name; ?>" class="form-control" />
            </div>
            <?php if (isset($error_name[$language['language_id']])) { ?>
            <div class="text-danger"><?php echo $error_name[$language['language_id']]; ?></div>
            <?php } ?>
            <?php } ?>
        </div>

    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label"><?= $dae_fag_sort_order; ?></label>
        <div class="col-sm-8">
            <input type="text" name="sort_order" value="<?php echo isset($attribute_group['sort_order'])?$attribute_group['sort_order']:0; ?>" placeholder="<?php echo $dae_sort_order; ?>" idinput-sort-order" class="form-control" />
        </div>
    </div>
</form>

<script>
    $('#dae-modal-button-save_form_attribute_group').click(function () {
        $.ajax({
            url: JS_URL_SAVE_FORM_ATTRIBUTE_GROUP,
            type: "POST",
            data: $('#dae-form-attribute-group').serialize(),
            dataType: 'json',
            success: function (json) {
                if(json.status == DAE_STATUS_SUCCESS){
                    daeModal.hide();
                    if(json.is_new_attribute_group){
                        daeEvent.dispatch(DAE_EVENT_CREATE_ATTRIBUTE_GROUP, {attribute_group_id: json.attribute_group_id, attribute_group_name: json.attribute_group_name});
                    }else{
                        daeEvent.dispatch(DAE_EVENT_UPDATE_ATTRIBUTE_GROUP, {attribute_group_id: json.attribute_group_id, attribute_group_name: json.attribute_group_name});
                    }
                    layoutAlert.handlerByResponse(json);
                }else{
                    modalAlert.handlerByResponse(json);
                }
            }

        });
    });
</script>