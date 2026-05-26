<table class="table">
    <tbody>
        <tr>
            <td>
                <label class="col-sm-6 control-label"><?php echo $dae_fs_error_module;?><span data-toggle="tooltip" data-original-title="<?php echo $dae_fs_error_module_help;?>"></span></label>
            </td>
            <td id="result_error_module">
                <?php if($service_error_module_result == 0){ ?>
                <span class="label label-success"><?php echo $dae_fs_status_ok;?></span>
                <?php }else{ ?>
                <span class="label label-danger"><?php echo $dae_fs_status_error;?>: <?php echo $service_error_module_result;?></span>
                <?php } ?>
            </td>
            <td>
                <?php if($service_error_module_result > 0){ ?>
                <button type="button" class="btn btn-primary" id="run_error_module"><?php echo $dae_fs_run;?></button>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td>
                <label class="col-sm-6 control-label"><?php echo $dae_fs_remove_attribute;?><span data-toggle="tooltip" data-original-title="<?php echo $dae_fs_remove_attribute_help;?>"></span></label>
            </td>
            <td id="result_remove_attribute">
                <?php if($service_remove_attribute_result == 0){ ?>
                <span class="label label-success"><?php echo $dae_fs_status_ok;?></span>
                <?php }else{ ?>
                <span class="label label-danger"><?php echo $dae_fs_status_error;?>: <?php echo $service_remove_attribute_result;?></span>
                <?php } ?>
            </td>
            <td>
                <?php if($service_remove_attribute_result > 0){ ?>
                <button type="button" class="btn btn-primary" id="run_remove_attribute"><?php echo $dae_fs_run;?></button>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td>
                <label class="col-sm-6 control-label"><?php echo $dae_fs_empty_value;?><span data-toggle="tooltip" data-original-title="<?php echo $dae_fs_empty_value_help;?>"></span></label>
            </td>
            <td id="result_empty_value">
                <?php $flag_error = false; ?>
                <?php foreach($languages as $key => $language){ ?>
                <?php if(!isset($service_empty_value_result[$language['language_id']]) || $service_empty_value_result[$language['language_id']]  == 0){ ?>
                <?php echo $language['name'];?> <span class="label label-success"><?php echo $dae_fs_status_ok;?></span><br>
                <?php }else{ ?>
                <?php $flag_error = true; ?>
                <?php echo $language['name'];?> <span class="label label-danger"><?php echo $dae_fs_status_error;?>: <?php echo $service_empty_value_result[$language['language_id']];?></span><br>
                <?php } ?>
                <?php } ?>
            </td>
            <td>
                <?php if($flag_error){ ?>
                <button type="button" class="btn btn-primary" id="remove_empty_value"><?php echo $dae_fs_remove_empty_value;?></button>
                <a href="<?= $url_getProductEmptyValueAttribute ?>" class="btn btn-primary" ><?php echo $dae_fs_more_empty_value;?></a>
                <?php } ?>
            </td>
        </tr>
    </tbody>
</table>

<script>
    $(window).ready(function(){
    //удаление ошибочных данных в модуле
    $('#run_error_module').click(function(){
        $('#result_error_module').html('<?php echo $dae_text_wait;?>');
        $.ajax({
            url: JS_URL_RUN_SERVICES,
            type: "POST",
            data: {'action':'deleteErrorInModule'},
            dataType: 'json',
            success: function(json) {
              if (json.status === DAE_STATUS_SUCCESS) {
                $('#result_error_module').html('<span class="label label-success"><?php echo $dae_fs_status_ok;?></span>');
              } else {
                $('#result_error_module').html('<span class="label label-danger"><?php echo $dae_fs_status_error;?>: ' + json.message + '</span>');
              }
                    
            }
        });
    });
            //очистка product_attribute от удаленных атрибутов
            $('#run_remove_attribute').click(function(){
    $('#result_remove_attribute').html('<?php echo $dae_text_wait;?>');
            $.ajax({
            url: JS_URL_RUN_SERVICES,
                    type: "POST",
                    data: {'action':'deleteErrorAttribute'},
                    dataType: 'json',
                    success: function(json) {

                      if (json.status === DAE_STATUS_SUCCESS) {
                        $('#result_remove_attribute').html('<span class="label label-success"><?php echo $dae_fs_status_ok;?></span>');
                      } else {
                          $('#result_remove_attribute').html('<span class="label label-danger"><?php echo $dae_fs_status_error;?>: ' + json.message + '</span>');
                      }
                    
                            
                    }
            });
    });
            //очистка product_attribute от атрибутов с пустыми значениями
            $('#remove_empty_value').click(function(){
    if (confirm("<?php echo $dae_fs_confirm_remove_empty_value;?>")){
    $('#result_empty_value').html('<?php echo $dae_text_wait;?>');
            $.ajax({
            url: JS_URL_RUN_SERVICES,
                    type: "POST",
                    data: {'action':'removeEmptyValue'},
                    dataType: 'json',
                    success: function(json) {
                    var html = '';
                <?php foreach($languages as $key => $language){ ?>
                            if (!(<?php echo $language['language_id'];?> in json.result) || json.result[<?php echo $language['language_id'];?>] == 0){
                            //if (json.status === DAE_STATUS_SUCCESS) {
                                    html += '<?php echo $language['name'];?> <span class="label label-success"><?php echo $dae_fs_status_ok;?></span><br>';
                }else{
                                    html += '<?php echo $language['name'];?> <span class="label label-danger"><?php echo $dae_fs_status_error;?>: ' + json.result[<?php echo $language['language_id'];?>]+'</span> < br > ';
                                    //html += '<?php echo $language['name'];?> <span class="label label-danger"><?php echo $dae_fs_status_error;?>: ' + json.message+'</span> < br > ';
                }
                <?php } ?>
                $('#result_empty_value').html(html);
                }
                });
                }
                });
                });
</script>
