
<div class="col-sm-12">
    <div class="form-group">
        <div class="checkbox">
            <label>
                <input type="hidden" name="settings-name[dae_load_sortable]" value="0"/>
                <input type="checkbox"  name="settings-name[dae_load_sortable]" value="1" <?= (isset($settings['dae_load_sortable']) && $settings['dae_load_sortable']==1)?'checked':''; ?>/> <?= $dae_fsal_load_sortable;?>
            </label>
        </div>
    </div>
</div>
            <div class="col-sm-12">
                <div class="form-group">
                    <div class="checkbox">
                        <label class="control-label" >
                        <input type="hidden" name="settings-name[dae_view_val_attr]" value="0"/>
                            <input type="checkbox"  name="settings-name[dae_view_val_attr]" value="1" <?= (isset($settings['dae_view_val_attr']) && $settings['dae_view_val_attr']==1)?'checked':''; ?>/>
                                   <span data-toggle="tooltip" title="" data-original-title="<?= $dae_fsal_view_val_attr_help;?>"><?= $dae_fsal_view_val_attr;?></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <div class="checkbox">
                        <label class="control-label" >
                        <input type="hidden" name="settings-name[dae_view_attr_id]" value="0"/>
                            <input type="checkbox"  name="settings-name[dae_view_attr_id]" value="1" <?= (isset($settings['dae_view_attr_id']) && $settings['dae_view_attr_id']==1)?'checked':''; ?>/>
                                   <span data-toggle="tooltip" title="" data-original-title="<?= $dae_fsal_view_attr_id_help;?>"><?= $dae_fsal_view_attr_id;?></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <div class="checkbox">
                        <label class="control-label" >
                        <input type="hidden" name="settings-name[dae_view_attr_v_id]" value="0"/>
                            <input type="checkbox"  name="settings-name[dae_view_attr_v_id]" value="1" <?= (isset($settings['dae_view_attr_v_id']) && $settings['dae_view_attr_v_id']==1)?'checked':''; ?>/>
                                   <span data-toggle="tooltip" title="" data-original-title="<?= $dae_fsal_view_attr_v_id_help;?>"><?= $dae_fsal_view_attr_v_id;?></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <div class="checkbox">
                        <label class="control-label" >
                        <input type="hidden" name="settings-name[dae_view_count_values]" value="0"/>
                            <input type="checkbox"  name="settings-name[dae_view_count_values]" value="1" <?= (isset($settings['dae_view_count_values']) && $settings['dae_view_count_values']==1)?'checked':''; ?>/>
                                   <span data-toggle="tooltip" title="" data-original-title="<?= $dae_fsal_view_count_values_help;?>"><?= $dae_fsal_view_count_values;?></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="form-group row">
                    <label class="control-label col-sm-3" for="settings-name[dae_count_attributes_in_page]">
                        <span data-toggle="tooltip" title="" data-original-title="<?= $dae_fsal_count_attributes_in_page_help;?>"><?= $dae_fsal_count_attributes_in_page;?></span>
                    </label>
                    <div class="col-sm-1">
                        <input name="settings-name[dae_count_attributes_in_page]" class = "form-control" value="<?= (isset($settings['dae_count_attributes_in_page']))?$settings['dae_count_attributes_in_page']:'20';?>">
                    </div>
                </div>
            </div>