
    
    <div id="form-ocext_feed_generator_yamarket" class="container-fluid">
        <div align="right">
                <a onclick="setSettingYamarket();" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i>  <?php echo $button_save; ?></a>
        </div>
        <hr>
    <style>
        
        .small_text{
            font-size: 9px;
            color: darkgray;
        }
        .small_text:hover{
            font-size: 9px;
            color: black;
        }
        .scrollbox div:nth-child(2n+1){
           background: lemonchiffon;
        }
        .scrollbox table tbody tr td div:nth-child(2n+1){
           background: none;
        }
        .help-box{ border-left: 2px solid #cccccc; padding-left: 7px; margin-bottom: 10px; background: cornsilk}
        
    </style>

                        <?php $setting_id = 0; ?>
                        
                        <?php $setting = array(); ?>
                        
                        <div id="tab-template_setting_yamarket<?php echo $setting_id ?>" class="tab-pane tab-template_setting-content-yamarket active" >
                            <img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading.gif" />
                        </div>
                        
                        <?php if($template_setting){ ?>
                        
                            <?php foreach($template_setting as $template_setting_row){ ?>
                            
                                <?php $setting_id = $template_setting_row['setting_id']; ?>
                                
                                <div id="tab-template_setting_yamarket<?php echo $setting_id ?>" class="tab-pane tab-template_setting-content-yamarket active" >
                                    <img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading.gif" />
                                </div>
                                
                            <?php } ?>
                            
                    <?php } ?>
                
    
</div>
<script type="text/javascript"><!--

$(document).ready(function() {
    
    getTemplateSettingYamarket(<?php echo $setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_product_id ?>,0);
    
});

function setSettingYamarket(){

    if($("#form-ocext_feed_generator_yamarket input[name=\'setting[title]\']")==''){
        alert('<?php echo $tab_template_setting_title_empty; ?>');
        $("#form-ocext_feed_generator_yamarket input[name=\'setting[title]\']").focus();
        $("#form-ocext_feed_generator_yamarket input[name=\'setting[title]\']").css('border',"1px solid red");
        return;
    }
    
    $.ajax({
            type: 'post',
            url: 'index.php?route=extension/feed/ocext_feed_generator_yamarket/setSettings&&token=<?php echo $token; ?>',
            dataType: 'json',
            data: $('#form-ocext_feed_generator_yamarket select, #form-ocext_feed_generator_yamarket input[type=\'radio\']:checked,  #form-ocext_feed_generator_yamarket input[type=\'checkbox\']:checked, #form-ocext_feed_generator_yamarket input[type=\'hidden\'], #form-ocext_feed_generator_yamarket input[type=\'text\'], #form-ocext_feed_generator_yamarket textarea'),
            success: function(response) {
                if(response['message']=='error'){
                    alert(response['text']);
                }else{
                    window.location.reload();
                }
            },
            <?php if ($debug) { ?>
            error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
            <?php } ?>
    });

}

function  settingFieldsYamarket(value_selected,setting_id,name_field,sample_setting_id,setting_type){
    $('.setting_fields'+setting_id+name_field).hide();
    if(value_selected=='category_id' ||  value_selected=='composite' || value_selected=='text_field'){
        $('#setting_fields_'+value_selected+setting_id+name_field).show();
    }
    if(value_selected=='option_id' || value_selected=='attribute_id'){
        settingFieldsYamarketGetOptOrAtr(value_selected,setting_id,name_field,sample_setting_id,setting_type);
        $('#setting_fields_'+value_selected+setting_id+name_field).show();
    }
}

function  settingFieldsYamarketGetOptOrAtr(value_selected,setting_id,name_field,sample_setting_id,setting_type){
    $('#setting_fields_'+value_selected+setting_id+name_field+" td div.scrollbox").before('<div id="ocext_loading'+value_selected+name_field+setting_id+'"><img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading.gif" /></div>');
    $.ajax({
            type: 'GET',
            url: 'index.php?route=extension/feed/ocext_feed_generator_yamarket/settingFieldsYamarketGetOptOrAtr&sample_setting_id='+sample_setting_id+'&setting_type='+setting_type+'&setting_id='+setting_id+'&name_field='+name_field+'&value_selected='+value_selected+'&token=<?php echo $token; ?>',
            dataType: 'html',
            success: function(response) {
                $('#setting_fields_'+value_selected+setting_id+name_field+" td div.scrollbox").html(response);
                $('#ocext_loading'+value_selected+name_field+setting_id).remove();
            },
            <?php if ($debug) { ?>
            error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
            <?php } ?>
    });
    
}

function getSettingFieldsYamarket(sample_setting_id,setting_type,setting_id,name_field){
    
    $('#form-ocext_feed_generator_yamarket .setting_'+name_field+setting_id).before('<div id="ocext_loading'+name_field+setting_id+'"><img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading.gif" /></div>');
    $.ajax({
            type: 'GET',
            url: 'index.php?route=extension/feed/ocext_feed_generator_yamarket/getSettingFields&sample_setting_id='+sample_setting_id+'&setting_type='+setting_type+'&name_field='+name_field+'&setting_id='+setting_id+'&token=<?php echo $token; ?>',
            dataType: 'html',
            success: function(response) {
                $('#ocext_loading'+name_field+setting_id).remove();
                $('#form-ocext_feed_generator_yamarket .setting_'+name_field+setting_id).before(response);
            },
            <?php if ($debug) { ?>
            error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
            <?php } ?>
    });
    
}

function getTemplateSettingYamarket(setting_id,setting_type,setting_product_id,sample_setting_id){

    $.ajax({
            type: 'GET',
            url: 'index.php?route=extension/feed/ocext_feed_generator_yamarket/getTemplateSetting&sample_setting_id='+sample_setting_id+'&setting_product_id='+setting_product_id+'&setting_type='+setting_type+'&setting_id='+setting_id+'&token=<?php echo $token; ?>',
            dataType: 'html',
            success: function(response) {
                $('.tab-template_setting-content-yamarket').empty();
                $('#tab-template_setting_yamarket'+setting_id).html('<img src="<?php echo HTTP_SERVER; ?>/view/image/ocext/loading.gif" />');
                $('#tab-template_setting_yamarket'+setting_id).html(response);
            },
            <?php if ($debug) { ?>
            error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
            <?php } ?>
    });

}



//--></script>