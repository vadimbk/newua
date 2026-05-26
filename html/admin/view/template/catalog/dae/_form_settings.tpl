
<form method="post" enctype="multipart/form-data" id="dae-form" name="form" class="form-horizontal">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-settings-1" data-toggle="tab"><?php echo $dae_tab_settings_1; ?></a></li>
        <li><a href="#tab-settings-2" data-toggle="tab"><?php echo $dae_tab_settings_2; ?></a></li>
        <li><a href="#tab-settings-3" data-toggle="tab"><?php echo $dae_tab_settings_3; ?></a></li>
        <li><a href="#tab-settings-4" data-toggle="tab"><?php echo $dae_tab_settings_4; ?></a></li>
    </ul>


    <div class="tab-content">
        <div class="tab-pane active" id="tab-settings-1">
            <h4><?php echo $dae_settings_view_in_product_tab_header; ?></h4>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_view_product_tab]"><?php echo $dae_settings_view;?></label>
                <div class="col-sm-5">
                    <label class="radio-inline">
                        <input type="radio"  name="settings-name[dae_view_product_tab]" value="1" <?php echo (isset($settings['dae_view_product_tab']) && $settings['dae_view_product_tab']==1)?'checked':''; ?>/><?php echo $text_yes;?>
                    </label>
                    <label class="radio-inline">
                        <input type="radio"  name="settings-name[dae_view_product_tab]" value="0" <?php echo (empty($settings['dae_view_product_tab']))?'checked':''; ?>/><?php echo $text_no;?>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_separator_view_product_tab]"><?php echo $separator_name;?></label>
                <div class="col-sm-5">
                    <input type="text" name="settings-name[dae_separator_view_product_tab]" class = "form-control" value="<?php echo (isset($settings['dae_separator_view_product_tab']))?$settings['dae_separator_view_product_tab']:'; ';?>">
                </div>
            </div>

            <input type="hidden" name="settings-name[dae_count_view_product_tab]" value="<?php echo (isset($settings['dae_count_view_product_tab']))?$settings['dae_count_view_product_tab']:'0';?>">

            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_template_product_tab]"><?php echo $skin_attr_view_pr_name;?></label>
                <div class="col-sm-5">
                    <textarea name="settings-name[dae_template_product_tab]" class = "form-control" rows="5"><?php echo (isset($settings['dae_template_product_tab']))?$settings['dae_template_product_tab']:'';?></textarea>
                </div>
            </div>
            <h4><?php echo $dae_settings_view_in_product_header; ?></h4>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_view_product]"><?php echo $dae_settings_view;?></label>
                <div class="col-sm-5">
                    <label class="radio-inline">
                        <input type="radio"  name="settings-name[dae_view_product]" value="1" <?php echo (isset($settings['dae_view_product']) && $settings['dae_view_product']==1)?'checked':''; ?>/><?php echo $text_yes;?>
                    </label>
                    <label class="radio-inline">
                        <input type="radio"  name="settings-name[dae_view_product]" value="0" <?php echo (empty($settings['dae_view_product']))?'checked':''; ?>/><?php echo $text_no;?>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_separator_view_product]"><?php echo $separator_name;?></label>
                <div class="col-sm-5">
                    <input type="text" name="settings-name[dae_separator_view_product]" class = "form-control" value="<?php echo (isset($settings['dae_separator_view_product']))?$settings['dae_separator_view_product']:'; ';?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_count_view_product]"><?php echo $count_view_attr_pr_name;?></label>
                <div class="col-sm-5">
                    <input type="text" name="settings-name[dae_count_view_product]" class = "form-control" value="<?php echo (isset($settings['dae_count_view_product']))?$settings['dae_count_view_product']:'0';?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_template_product]"><?php echo $skin_attr_view_pr_name;?></label>
                <div class="col-sm-5">
                    <textarea name="settings-name[dae_template_product]" class = "form-control" rows="5"><?php echo (isset($settings['dae_template_product']))?$settings['dae_template_product']:'';?></textarea>
                </div>
            </div>
        </div>
        <div class="tab-pane"  id="tab-settings-2">
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_view_catalog]"><?php echo $view_catalog_name;?></label>
                <div class="col-sm-5">
                    <label class="radio-inline">
                        <input type="radio"  name="settings-name[dae_view_catalog]" value="1" <?php echo (isset($settings['dae_view_catalog']) && $settings['dae_view_catalog']==1)?'checked':''; ?>/><?php echo $text_yes;?>
                    </label>
                    <label class="radio-inline">
                        <input type="radio"  name="settings-name[dae_view_catalog]" value="0" <?php echo (empty($settings['dae_view_catalog']))?'checked':''; ?>/><?php echo $text_no;?>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_separator_view_catalog]"><?php echo $separator_name;?></label>
                <div class="col-sm-5">
                    <input type="text" name="settings-name[dae_separator_view_catalog]" class = "form-control" value="<?php echo (isset($settings['dae_separator_view_catalog']))?$settings['dae_separator_view_catalog']:'; ';?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_count_view_catalog]"><?php echo $count_view_attr_name;?></label>
                <div class="col-sm-5">
                    <input type="text" name="settings-name[dae_count_view_catalog]" class = "form-control" value="<?php echo (isset($settings['dae_count_view_catalog']))?$settings['dae_count_view_catalog']:'0';?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_template_catalog]"><?php echo $skin_attr_view_name;?></label>
                <div class="col-sm-5">
                    <textarea name="settings-name[dae_template_catalog]" class = "form-control" rows="5"><?php echo (isset($settings['dae_template_catalog']))?$settings['dae_template_catalog']:'{a_name}: {a_v}';?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_full_template_catalog]"><?php echo $skin_a_d_view_name;?></label>
                <div class="col-sm-5">
                    <textarea name="settings-name[dae_full_template_catalog]" class = "form-control" rows="5"><?php echo (isset($settings['dae_full_template_catalog']))?$settings['dae_full_template_catalog']:'{attributes}';?></textarea>
                </div>
            </div>
        </div>
        <div class="tab-pane"  id="tab-settings-3">
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_value_separator]"><?php echo $value_separator_name;?></label>
                <div class="col-sm-5">
                    <input type="text" name="settings-name[dae_value_separator]" class = "form-control" value="<?php echo (isset($settings['dae_value_separator']))?$settings['dae_value_separator']:', ';?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_replace_val_att]"><?php echo $replace_val_att_name;?></label>
                <div class="col-sm-5">
                    <textarea name="settings-name[dae_replace_val_att]" class = "form-control" rows="5"><?php echo (isset($settings['dae_replace_val_att']))?$settings['dae_replace_val_att']:'';?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_skip_replace_val_att]"><?php echo $skip_replace_val_att_name;?></label>
                <div class="col-sm-5">
                    <textarea name="settings-name[dae_skip_replace_val_att]" class = "form-control" rows="5"><?php echo (isset($settings['dae_skip_replace_val_att']))?$settings['dae_skip_replace_val_att']:'';?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_att_image_h]"><?php echo $dae_att_image_name;?></label>
                <div class="col-sm-2">
                    <input name="settings-name[dae_att_image_h_c]" class = "form-control" value="<?php echo (!empty($settings['dae_att_image_h_c']))?$settings['dae_att_image_h_c']:'20';?>" />
                </div>
                <div class="col-sm-2">
                    <input name="settings-name[dae_att_image_w_c]" class = "form-control" value="<?php echo (!empty($settings['dae_att_image_w_c']))?$settings['dae_att_image_w_c']:'20';?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="settings-name[dae_val_image_h_c]"><?php echo $dae_val_image_name;?></label>
                <div class="col-sm-2">
                    <input name="settings-name[dae_val_image_h_c]" class = "form-control" value="<?php echo (!empty($settings['dae_val_image_h_c']))?$settings['dae_val_image_h_c']:'20';?>" />
                </div>
                <div class="col-sm-2">
                    <input name="settings-name[dae_val_image_w_c]" class = "form-control" value="<?php echo (!empty($settings['dae_val_image_w_c']))?$settings['dae_val_image_w_c']:'20';?>" />
                </div>
            </div>
        </div>
        <div class="tab-pane"  id="tab-settings-4">
         <div class="row"><!-- костыль для правильно отображения формы в модульном и полныом окнах -->
           <div class="col-sm-6">
            <h3><?= $_form_settings_attribute_values_list ?></h3>
            <?= $_fields_settings_attribute_values_list ?>
           </div>
         </div>         
            <h3><?= $_form_settings_tab_attribute_product ?></h3>
            <?= $_fields_settings_tab_attribute_product ?>
            
            <h3><?= $_form_settings_attributes_list ?></h3>
            <?= $_fields_settings_attributes_list ?>

            <div class="col-sm-12">
                <div class="form-group row">
                    <label class="control-label col-sm-3" for="settings-name[dae_count_autocomplete_attribute]"><?php echo $dae_settings_count_attribute;?></label>
                    <div class="col-sm-1">
                        <input name="settings-name[dae_count_autocomplete_attribute]" class = "form-control" value="<?php echo (isset($settings['dae_count_autocomplete_attribute']))?$settings['dae_count_autocomplete_attribute']:'10';?>">
                    </div>

                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group row">
                    <label class="control-label col-sm-3" for="settings-name[dae_count_autocomplete_attribute_value]"><?php echo $dae_settings_count_attribute_value;?></label>
                    <div class="col-sm-1">
                        <input name="settings-name[dae_count_autocomplete_attribute_value]" class = "form-control" value="<?php echo (isset($settings['dae_count_autocomplete_attribute_value']))?$settings['dae_count_autocomplete_attribute_value']:'10';?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $(window).ready(function () {
        $('#save-form').click(function () {
            layoutAlert.viewByStatus('<?php echo $dae_text_wait;?>',layoutAlert.ALERT_INFO);
            $.ajax({
                url: JS_URL_SAVE_FROM_SETTINGS,
                type: "POST",
                data: $('#dae-form').serialize(),
                dataType: 'json',
                success: function (json) {
                    layoutAlert.handlerByResponse(json);
                }
            });
        });
    });
</script>
