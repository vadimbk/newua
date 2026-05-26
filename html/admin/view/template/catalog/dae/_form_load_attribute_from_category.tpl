<form id="dae_form_load_attribute_from_category" class="form-horizontal">
    <div class="row">
        <div class="col-xs-12">
            <select class="form-control" id="flafc_category">
                <option value="0">-</option>
                <?php foreach ($devos_categories_attributes as $devos_category_attributes) { ?>
                  <option value="<?= $devos_category_attributes['category_id'];?>" ><?= $devos_category_attributes['category_name'];?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-xs-12">
            <br>
            <p><?= $dae_flafc_description ?></p>
        </div>
        <div class="col-xs-12">
            <div class="form-group">
                <div class="checkbox">
                    <label class="control-label">
                        <input type="checkbox"  name="flafc_replace_attributes" id='flafc_replace_attributes' value="1"/> <?= $dae_flafc_replaceAttributes; ?>
                        <span data-toggle="tooltip" title="" data-original-title="<?= $dae_flafc_replaceAttributesHelp;?>"></span>
                    </label>
                </div>
            <select class="form-control" id="flafc_default_value">
                <option value="1" selected="selected"><?= $dae_flafc_default_value_only_new_empty ?></option>
                <option value="2"><?= $dae_flafc_default_value_only_empty ?></option>
                <option value="3"><?= $dae_flafc_default_value_full_replace ?></option>
            </select>
            </div>
        </div>
    </div>
</form>
<script>
    $('#dae-modal-button-add_form_load_attribute_from_category').click(function () {
        var category_id = $('select#flafc_category').val();
        var replace_attaributes = $('#flafc_replace_attributes').is(':checked');
        var default_value = $('select#flafc_default_value').val();

        if(category_id > 0){
          /*if(category_id in devos_categories_attributes)
            addAttributeForCategory(category_id);
          else*/
            daeLoadAttributeFromCategory(category_id, replace_attaributes, default_value);
            modalAlert.viewSuccess('<?= $success_add_attributes;?>');
        }else{
            modalAlert.viewDanger('<?= $success_no_add_attributes;?>');
        }
    });
</script>