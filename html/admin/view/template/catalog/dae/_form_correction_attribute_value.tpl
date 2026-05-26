
<form class="form-horizontal" name="_form_correction_attribute_value" id="_form_correction_attribute_value">

    <div class="form-group">
        <label for="select_value_attr"  class="col-sm-4 control-label" ><?= $dae_fcav_attribute_value;?></label>
        <div class="col-sm-8">
            <select class="form-control" id="search_value" name="search_value">
                <?php foreach ($attribute_distinct_values as $attribute_distinct_value) { ?>
                <?php //if(empty($attribute_distinct_value['text'])) continue; ?>
                <option value="<?php echo $attribute_distinct_value['text']; ?>"><?php echo $attribute_distinct_value['text']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="replace_value_attr"  class="col-sm-4 control-label" ><?= $dae_fcav_replace_value_attribute;?></label>
        <div class="col-sm-8">
            <?= $_form_select_attribute_value ?>

        </div>
    </div>
</form>

    <script type="text/javascript">
    $('.dae-modal-button-run').click(function () {
        $.ajax({
            url: JS_URL_RUN_CORRECTION_ATTRIBUTE_VALUE,
            type: "POST",
            data: $('#_form_correction_attribute_value').serialize(),
            dataType: 'json',
            success: function (json) {
                modalAlert.handlerByResponse(json);
                if (json.status === DAE_STATUS_SUCCESS) {
                    $('#select_value_attr').empty();
                    for (var i in json.list_value) {
                        $("#select_value_attr").append($('<option value="' + json.list_value[i]['text'] + '">' + json.list_value[i]['text'] + '</option>'));
                    }

                }
            }
        });
    });



                                                                            //--></script>
