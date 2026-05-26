
<div class="form-group">
    <div class="checkbox col-sm-12">
        <input type="hidden"  name="settings-name[dae_product_tab][view_button_add_attribute_group]" value="0">
        <label class="">
            <input type="checkbox"
                   name="settings-name[dae_product_tab][view_button_add_attribute_group]"
                   value="1"
                   <?= ($settings['dae_product_tab']['view_button_add_attribute_group'])?'checked':''; ?>
                   />
                   <?= $dae_fs_product_tab_view_button_add_attribute_group ?>
        </label>
    </div>
</div>
<div class="form-group">
    <div class="checkbox col-sm-12">
        <input type="hidden"  name="settings-name[dae_product_tab][view_button_add_attribute]" value="0">
        <label class="">
            <input type="checkbox"
                   name="settings-name[dae_product_tab][view_button_add_attribute]"
                   value="1"
                   <?= ($settings['dae_product_tab']['view_button_add_attribute'])?'checked':''; ?>
                   />
                   <?= $dae_fs_product_tab_view_button_add_attribute ?>
        </label>
    </div>
</div>

<div class="form-group">
    <div class="checkbox col-sm-12">
        <input type="hidden"  name="settings-name[dae_product_tab][view_button_add_attribute_category]" value="0">
        <label class="">
            <input type="checkbox"
                   name="settings-name[dae_product_tab][view_button_add_attribute_category]"
                   value="1"
                   <?= ($settings['dae_product_tab']['view_button_add_attribute_category'])?'checked':''; ?>
                   />
                   <?= $dae_fs_product_tab_view_button_add_attribute_category ?>
        </label>
    </div>
</div>
<div class="form-group">
    <div class="checkbox col-sm-12">
        <input type="hidden"  name="settings-name[dae_product_tab][view_button_add_attribute_list]" value="0">
        <label class="">
            <input type="checkbox"
                   name="settings-name[dae_product_tab][view_button_add_attribute_list]"
                   value="1"
                   <?= ($settings['dae_product_tab']['view_button_add_attribute_list'])?'checked':''; ?>
                   />
                   <?= $dae_fs_product_tab_view_button_add_attribute_list ?>
        </label>
    </div>
</div>
<div class="form-group">
    <div class="checkbox col-sm-12">
        <input type="hidden"  name="settings-name[dae_product_tab][view_button_clear_attribute]" value="0">
        <label class="">
            <input type="checkbox"
                   name="settings-name[dae_product_tab][view_button_clear_attribute]"
                   value="1"
                   <?= ($settings['dae_product_tab']['view_button_clear_attribute'])?'checked':''; ?>
                   />
                   <?= $dae_fs_product_tab_view_button_clear_attribute ?>
        </label>
    </div>
</div>
<div class="form-group">
    <div class="checkbox col-sm-12">
        <input type="hidden"  name="settings-name[dae_auto_load_attr_for_cat]" value="0">
        <label class="">
            <input type="checkbox"
                   name="settings-name[dae_auto_load_attr_for_cat]"
                   value="1"
                   <?= ($settings['dae_auto_load_attr_for_cat'])?'checked':''; ?>
                   />
                   <?= $dae_fs_product_tab_auto_load_attr_for_cat ?>
        </label>
    </div>
</div>


<!--
<div class="form-group hidden">
    <div class="col-sm-12">
        <label>echo $dae_pt_settings_value_source;</label>
    </div>
    <div class="col-sm-4">
        <div class="radio">
            <label><input type="radio" name="settings-name[dae_product_tab][source_value]" value="module" <?php echo (isset($settings_product_tab['source_value']) && $settings_product_tab['source_value']== 'module')?'checked':''; ?>>  echo $dae_pt_settings_value_module</label>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="radio">
            <label><input type="radio" name="settings-name[dae_product_tab][source_value]" value="product" <?php echo (isset($settings_product_tab['source_value']) && $settings_product_tab['source_value']== 'product')?'checked':''; ?>">  echo $dae_pt_settings_value_product; </label>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="radio">
            <label><input type="radio" name="settings-name[product_tab][source_value]" value="product_category" <?php echo (isset($settings_product_tab['source_value']) && $settings_product_tab['source_value']== 'product_category')?'checked':''; ?>"> echo $dae_pt_settings_value_product_category;</label>
        </div>
    </div>
</div>
-->