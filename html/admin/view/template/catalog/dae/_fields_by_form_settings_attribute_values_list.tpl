
<div class="row">
    <div class="col-sm-12">
        <input type="hidden"  name="settings-name[dae_form_view_url]" value="0">
        <div class="checkbox">
            <label class="col-sm-12">
                <input type="checkbox"  name="settings-name[dae_form_view_url]" value="1" <?= ($settings['dae_form_view_url']==1)?'checked':''; ?>/>
                       <?= $dae_fs_form_view_url; ?>
            </label>
        </div>
    </div>
    <div class="col-sm-12">
        <input type="hidden"  name="settings-name[dae_form_view_description]" value="0">
        <div class="checkbox">
            <label>
                <input type="checkbox"  name="settings-name[dae_form_view_description]" value="1" <?= ($settings['dae_form_view_description']==1)?'checked':''; ?>/>
                       <?= $dae_fs_form_view_description;?>
            </label>
        </div>
    </div>
    <div class="col-sm-12">
        <input type="hidden"  name="settings-name[dae_value_category]" value="0">
        <div class="checkbox">
            <label class="control-label" >
                <input type="checkbox"  name="settings-name[dae_value_category]" value="1" <?= ($settings['dae_value_category']==1)?'checked':''; ?>/>
                    <span data-toggle="tooltip" title="" data-original-title="<?= $dae_fs_value_category_help;?>"><?= $dae_fs_value_category;?></span>
            </label>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="row form-group">
            <label class="control-label col-sm-6" for="settings-name[dae_count_attribute_values_in_page]">
                <span data-toggle="tooltip" title="" data-original-title="<?= $dae_fs_count_attribute_values_in_page_help;?>"><?= $dae_fs_count_attribute_values_in_page;?></span>
            </label>
            <div class="col-sm-6">
                <input name="settings-name[dae_count_attribute_values_in_page]" class = "form-control" value="<?= $settings['dae_count_attribute_values_in_page'] ?>">
            </div>
        </div>
    </div>
</div>
