<form id="dae-form-attribute" class="form-horizontal">
    <input type="hidden" name="attribute_id" value="<?= $attribute_id;?>">
    <input type="hidden" name="old_attribute_group_id" value="<?= isset($attribute['attribute_group_id'])?$attribute['attribute_group_id']:0;?>">

    <div class="form-group required">
        <label class="col-sm-4 control-label"><?= $dae_fa_name_attribute; ?></label>
        <div class="col-sm-8">
            <?php foreach ($languages as $language) { ?>
            <div class="input-group">
                <span class="input-group-addon">
                    <img src="<?= $language['path_image']; ?>" title="<?= $language['name']; ?>" />
                </span>
                <input type="text" name="attribute_description[<?= $language['language_id']; ?>][name]" value="<?=   isset($attribute_description[$language['language_id']]) ? $attribute_description[$language['language_id']]['name'] : $default_attribute_name; ?>" placeholder="<?= $dae_fa_name_attribute_placeholder; ?>" class="form-control" />
            </div>
            <?php if (isset($error_name[$language['language_id']])) { ?>
            <div class="text-danger"><?= $error_name[$language['language_id']]; ?></div>
            <?php } ?>
            <?php } ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 control-label" for="input-attribute-group"><?= $dae_fa_attibute_group; ?></label>
        <div class="col-sm-8">
            <select name="attribute_group_id" id="input-attribute-group" class="form-control">
                <?php foreach ($attribute_groups as $attribute_group) { ?>
                <?php if (isset($attribute['attribute_group_id']) && $attribute_group['attribute_group_id'] == $attribute['attribute_group_id']) { ?>
                <option value="<?= $attribute_group['attribute_group_id']; ?>" selected="selected"><?= $attribute_group['name']; ?></option>
                <?php } else { ?>
                <option value="<?= $attribute_group['attribute_group_id']; ?>"><?= $attribute_group['name']; ?></option>
                <?php } ?>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label" for="input-attribute-group"><?= $dae_fa_sort_order; ?></label>
        <div class="col-sm-8">
            <input type="text" name="sort_order" value="<?= isset($attribute['sort_order'])?$attribute['sort_order']:0; ?>" placeholder="<?= $dae_fa_sort_order; ?>" id="  input-sort-order" class="form-control" />
        </div>
    </div>
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-view" data-toggle="tab"><?= $dae_fa_type_view; ?></a></li>
        <li><a href="#tab-enter" data-toggle="tab"><?= $dae_fa_type_enter; ?></a></li>
        <li><a href="#tab-additionally" data-toggle="tab"><?= $dae_fa_additionally; ?></a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab-view">
            <div class="form-group">
                <div class="checkbox col-sm-4">
                    <label class="">
                        <input type="checkbox"  name="setting_attribute[view_catalog]" value="1" <?= (isset($attribute_settings['view_catalog']) && $attribute_settings['view_catalog']==1)?'checked':''; ?>/> <?= $dae_fa_view_catalog;?>
                    </label>
                </div>
                <div class="checkbox col-sm-4">
                    <label class="">
                        <input type="checkbox"  name="setting_attribute[view_product]" value="1" <?= (isset($attribute_settings['view_product']) && $attribute_settings['view_product']==1)?'checked':''; ?>/> <?= $dae_fa_view_product;?>
                    </label>
                </div>
                <div class="checkbox col-sm-4">
                    <label class="">
                        <input type="checkbox"  name="setting_attribute[view_product_tab]" value="1" <?= ((isset($attribute_settings['view_product_tab']) && $attribute_settings['view_product_tab']==1)||empty($attribute_id))?'checked':''; ?>/> <?= $dae_fa_view_product_tab;?>
                    </label>
                </div>
            </div>
            <!-- 
            <h4><?= $dae_fa_individual_templates;?></h4>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="setting_attribute[template_catalog]"><?= $dae_fa_template_catalog;?></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="setting_attribute[template_catalog]" value="<?= (!empty($attribute_settings['template_catalog']))?$attribute_settings['template_catalog']:'';?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="setting_attribute[template_product]"><?= $dae_fa_template_product;?></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="setting_attribute[template_product]" value="<?= (!empty($attribute_settings['template_product']))?$attribute_settings['template_product']:'';?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="setting_attribute[template_product_tab]"><?= $dae_fa_template_product_tab;?></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="setting_attribute[template_product_tab]" value="<?= (!empty($attribute_settings['template_product_tab']))?$attribute_settings['template_product_tab']:'';?>"/>
                </div>
            </div>
            -->
        </div>
        <div class="tab-pane" id="tab-enter">
            <div class="form-group">
                <label class="col-sm-4 control-label" for="setting_attribute[type_edit]"><?= $dae_fa_type;?></label>
                <div class="col-sm-8">
                    <select name="setting_attribute[type_edit]" class="form-control">
                        <option value="autocomp" <?php if (empty($attribute_settings['type_edit']) || $attribute_settings['type_edit'] == 'autocomp') { ?> selected="selected" <?php } ?> ><?= $dae_fa_type_autocomp;?></option>
                        <option value="default" <?php if (isset($attribute_settings['type_edit']) && $attribute_settings['type_edit'] == 'default') { ?> selected="selected" <?php } ?> ><?= $dae_fa_type_default;?></option>
                        <option value="multi" <?php if (isset($attribute_settings['type_edit']) && $attribute_settings['type_edit'] == 'multi') { ?> selected="selected" <?php } ?> ><?= $dae_fa_type_multi;?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="tab-additionally">
            <div class="form-group">
                <label class="col-sm-4 control-label" ><?= $dae_fa_short_name; ?></label>
                <div class="col-sm-8">
                    <?php foreach ($languages as $language) { ?>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <img src="<?= $language['path_image']; ?>" title="<?= $language['name']; ?>" />
                        </span>
                        <input type="text" class="form-control" name="setting_attribute[description][<?= $language['language_id']; ?>][short_name]"
                               value="<?= (isset($attribute_settings['description']) && isset($attribute_settings['description'][$language['language_id']])
                               )?$attribute_settings['description'][$language['language_id']]['short_name']:''; ?>"/>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label"><?= $dae_fa_attribute_tooltip;?></label>
                <div class="col-sm-8">
                    <?php foreach ($languages as $language) { ?>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <img src="<?= $language['path_image']; ?>" title="<?= $language['name']; ?>" />
                        </span>
                        <input type="text" class="form-control" name="setting_attribute[description][<?= $language['language_id']; ?>][tooltip]" value="<?= (isset($attribute_settings['description']) && isset($attribute_settings['description'][$language['language_id']]))?$attribute_settings['description'][$language['language_id']]['tooltip']:'';?>"/>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="setting_attribute[attribute_html]"><?= $dae_fa_attribute_html;?></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="setting_attribute[attribute_html]" value="<?= (!empty($attribute_settings['attribute_html']))?$attribute_settings['attribute_html']:'';?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="input-image"><?= $dae_fa_attribute_image; ?></label>
                <div class="col-sm-8">
                    <a href="" id="thumb-image" data-toggle="image" class="img-thumbnail"><img src="<?= $attribute_settings['thumb_attribute_image']; ?>" alt="" title="" data-placeholder="<?= $dae_fa_attribute_image; ?>" style="height:30px;"/></a>
                    <input type="hidden" name="setting_attribute[attribute_image]" value="<?= (!empty($attribute_settings['attribute_image']))?$attribute_settings['attribute_image']:''; ?>" id="input-image" />
                </div>
            </div>

        </div>
    </div>



</form>

<script>
    $('#option a:first').tab('show');
    $('#dae-modal-button-save_form_attribute').click(function () {
        $.ajax({
            url: JS_URL_SAVE_FORM_ATTRIBUTE,
            type: "POST",
            data: $('#dae-form-attribute').serialize(),
            dataType: 'json',
            success: function (json) {
                if (json.status === DAE_STATUS_SUCCESS) {
                    daeModal.hide();
                    if (json.is_new_attribute) {
                        daeEvent.dispatch(DAE_EVENT_CREATE_ATTRIBUTE, {attribute: json.attribute});
                    } else {
                        daeEvent.dispatch(DAE_EVENT_UPDATE_ATTRIBUTE, {
                            attribute: json.attribute,
                            old_attribute_group_id: json.old_attribute_group_id
                        });
                    }
                    layoutAlert.handlerByResponse(json);
                }else{
                    modalAlert.handlerByResponse(json);
                }
            }
        });
    });
</script>