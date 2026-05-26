<form class="form-horizontal" id="form-generic-attribute-value">
    <input type="hidden" name="attribute_id" value="<?= $attribute_id;?>">
    <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo $dae_fgav_field_list;?></label>
        <div class="col-sm-10">
            <textarea class="form-control" name="list_attribute_values" placeholder="<?php echo $dae_fgav_field_list_help;?>"></textarea>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo $dae_fgav_field_split;?></label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="split" value="<?= $dae_value_separator; ?>">
        </div>
    </div>
    <p style="text-align:center;"><b><?= $dae_fgav_text_or ?></b></p>
    <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo $dae_fgav_field_mask;?></label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="mask" value="">
        </div>
    </div>
    <?= $_form_select_category ?>
</form>

<script>
    /*
     * Генерация значений из строки
     */
    $('#dae-modal-button-run_form_generic_attribute_values').click(function () {

        /*if (!$('textarea[name="list_attribute_values"]').val()) {
            dae_showAlertDangerByModal("<?= $dae_fgav_empty_list_value; ?>");
            return false;
        }

        if (!$('input[name="split"]').val()) {
            dae_showAlertDangerByModal("<?= $dae_fgav_empty_split;?>");
            return false;
        }*/
        $.ajax({
            url: JS_URL_RUN_GENERIC_ATTRIBUTE_VALUES,
            type: 'POST',
            dataType: 'json',
            data: $('#form-generic-attribute-value').serialize(),
            success: function (json) {
                modalAlert.handlerByResponse(json);
                if (json.status == DAE_STATUS_SUCCESS) {
                    daeModal.hide();
                    daeEvent.dispatch(DAE_EVENT_GENERATE_ATTRIBUTE_VALUES, {attribute_id: json.attribute_id});
                }
            }
        });
    });
</script>