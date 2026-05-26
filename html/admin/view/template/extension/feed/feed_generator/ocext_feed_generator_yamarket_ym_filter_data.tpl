


<?php if(isset($categories) && $categories){ ?>
<div class="scrollbox" style="max-height: 350px; overflow-y: auto; margin-top: 7px;">
    
    <?php if($templates_setting){ ?>
    <select class="form-control" onchange="if(this.value!=''){ $('.all_yml_export_ocext_ym_filter_data_categories_class').val(this.value); }" name="[setting_id]" >
            <option value=""><?php echo $text_ym_filter_data_all_data ?></option>
            <option value="0"><?php echo $text_ym_filter_data_templates_setting_0; ?></option>
            <?php
            
                foreach($templates_setting as $template_setting){
                
                    $setting_id = $template_setting['setting_id'];
                    $setting_tilte = $template_setting['setting']['title'];
                
                    ?>
                    <option  value="<?php echo $setting_id ?>"><?php echo $setting_tilte; ?></option>
                    
            <?php } ?>
    </select>
    <?php } ?>
    <hr>
    <table class="table table-bordered table-hover">
        <thead>
            <td><?php echo $text_ym_filter_data_categories_name; ?> <a onclick="if($('.categories_filtred').prop('checked')){ $('.categories_filtred').prop('checked',false); }else{ $('.categories_filtred').prop('checked',true); }">(выделить все)</a></td>
            <td><?php echo $tab_template_setting; ?></td>
            <?php if(isset($mapping_market_place_categories)){ ?>
            <td>Заменить названия категорий на указанные (если нужно указать ветку, то используйте разделитель: <b>></b><br>Например: <span style="color:red">Каталог > Одежда > Сорочки</span></td>
            <?php } ?>
            
            
        </thead>
        <tbody>
            <?php foreach($categories as $category){ ?>
            <tr>
                
                    <td>
                    <?php if($ym_categories && isset($ym_categories[ $category['category_id'] ]['category_id']) && $ym_categories[ $category['category_id'] ]['category_id']){ ?>
                        <div style="min-height: 25px;">
                            <input class="categories_filtred" checked="" type="checkbox"  name="ocext_feed_generator_yamarket_ym_filter_category[<?php echo $category['category_id'] ?>][category_id]" value="<?php echo $category['category_id'] ?>" />
                        <?php echo $category['name']; ?>
                        </div>
                    <?php }else{ ?>
                        <div style="min-height: 25px;">
                            <input  class="categories_filtred" type="checkbox"  name="ocext_feed_generator_yamarket_ym_filter_category[<?php echo $category['category_id'] ?>][category_id]" value="<?php echo $category['category_id'] ?>" />
                        <?php echo $category['name']; ?>
                        </div>
                    <?php } ?>
                    </td>
                    <td>
                    <?php if($templates_setting){ ?>
                        <select name="ocext_feed_generator_yamarket_ym_filter_category[<?php echo $category['category_id'] ?>][setting_id]" class="all_yml_export_ocext_ym_filter_data_categories_class form-control">
                            <option value="0"><?php echo $text_ym_filter_data_templates_setting_0; ?></option>
                        <?php
            
                            foreach($templates_setting as $template_setting){

                                $setting_id = $template_setting['setting_id'];
                                $setting_tilte = $template_setting['setting']['title'];

                                ?>
                                <?php if($ym_categories && isset($ym_categories[ $category['category_id'] ]['setting_id']) && $ym_categories[ $category['category_id'] ]['setting_id']==$setting_id){ ?>
                                    <option selected=""  value="<?php echo $setting_id ?>"><?php echo $setting_tilte; ?></option>
                                <?php }else{ ?>
                                <option  value="<?php echo $setting_id ?>"><?php echo $setting_tilte; ?></option>
                                <?php } ?>
                        <?php } ?>
                        </select>
                    
                    
                        <?php if(FALSE){ ?>
                    
                    
                        <b>Главная категория (дочерние и родительские будут отброшены у товаров, связанных с это категорией)</b>
                        <select  class="form-control" name="ocext_feed_generator_yamarket_ym_filter_category[<?php echo $category['category_id'] ?>][disable_parent_child_categories]" class="all_yml_export_ocext_ym_filter_data_categories_class form-control">
                            <?php if($ym_categories && isset($ym_categories[ $category['category_id'] ]['disable_parent_child_categories']) && $ym_categories[ $category['category_id'] ]['disable_parent_child_categories']){ ?>
                                <option selected="" value="1">Включено</option>
                                <option value="0">Выключено</option>
                            <?php }else{ ?>
                                <option value="1">Включено</option>
                                <option selected="" value="0">Выключено</option>
                            <?php } ?>
                        </select>
                        
                        
                        <?php } ?>
                    
                    <?php }else{ ?>
                        <div class="alert-info" style="margin-top: 7px;" align="center"><?php echo $text_ym_filter_data_templates_setting_empty ?></div>
                    <?php } ?>
                    </td>
                    
                    
                    
                    
                    <?php if(isset($mapping_market_place_categories)){ ?>

                        <td>

                                <?php

                                    $mapping_market_place_categories_this = '';
                                
                                    if(isset($mapping_market_place_categories[$category['category_id']])){

                                        $mapping_market_place_categories_this = $mapping_market_place_categories[$category['category_id']];

                                    }

                                ?>
                                
                                <input class="form-control"  type="text" name="ocext_feed_generator_yamarket_ym_filter_mapping_market_place_categories[<?php echo $category['category_id']; ?>]"  value="<?php echo $mapping_market_place_categories_this ?>" >
                                

                        </td>

                    <?php } ?>
                    
                    
                    
                    
                    
                
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php }elseif(isset($categories) && !$categories){ ?>
    <div class="alert-info" style="margin-top: 7px;" align="center"><?php echo $text_categories_empty ?></div>
<?php }elseif(isset($manufacturers) && $manufacturers){ ?>
<div class="scrollbox" style="max-height: 350px; overflow-y: auto; margin-top: 7px;">
    
    <?php if($templates_setting){ ?>
    <select  class="form-control"  onchange="if(this.value!=''){ $('.all_yml_export_ocext_ym_filter_data_manufacturers_class').val(this.value); }" name="[setting_id]" >
            <option value=""><?php echo $text_ym_filter_data_all_data ?></option>
            <option value="0"><?php echo $text_ym_filter_data_templates_setting_0; ?></option>
            <?php
                        foreach($templates_setting as $template_setting){

                            $setting_id = $template_setting['setting_id'];
                            $setting_tilte = $template_setting['setting']['title'];

                            ?>
                    <option  value="<?php echo $setting_id ?>"><?php echo $setting_tilte; ?></option>
            <?php } ?>
        </select>
    <?php } ?>
    <hr>
    
    <table class="table table-bordered table-hover">
        <thead>
            <td><?php echo $text_ym_filter_data_manufacturers_name; ?> <a onclick="if($('.manufacturers_filtred').prop('checked')){ $('.manufacturers_filtred').prop('checked',false); }else{ $('.manufacturers_filtred').prop('checked',true); }">(выделить все)</a></td>
            <td><?php echo $tab_template_setting; ?></td>
        </thead>
        <tbody>
            <?php foreach($manufacturers as $manufacturer){ ?>
                <tr>

                        <td>
                        <?php if($ym_manufacturers && isset($ym_manufacturers[ $manufacturer['manufacturer_id'] ]['manufacturer_id']) && $ym_manufacturers[ $manufacturer['manufacturer_id'] ]['manufacturer_id']){ ?>
                            <div style="min-height: 25px;">
                            <input  class="manufacturers_filtred" checked="" type="checkbox"  name="ocext_feed_generator_yamarket_ym_filter_manufacturers[<?php echo $manufacturer['manufacturer_id'] ?>][manufacturer_id]" value="<?php echo $manufacturer['manufacturer_id'] ?>" />
                            <?php echo $manufacturer['name']; ?>
                            </div>
                        <?php }else{ ?>
                            <div style="min-height: 25px;">
                            <input  class="manufacturers_filtred" type="checkbox"  name="ocext_feed_generator_yamarket_ym_filter_manufacturers[<?php echo $manufacturer['manufacturer_id'] ?>][manufacturer_id]" value="<?php echo $manufacturer['manufacturer_id'] ?>" />
                            <?php echo $manufacturer['name']; ?>
                            </div>
                        <?php } ?>
                        </td>
                        <td>
                        <?php if($templates_setting){ ?>
                            <select class="all_yml_export_ocext_ym_filter_data_manufacturers_class form-control" name="ocext_feed_generator_yamarket_ym_filter_manufacturers[<?php echo $manufacturer['manufacturer_id'] ?>][setting_id]" >
                                <option value="0"><?php echo $text_ym_filter_data_templates_setting_0; ?></option>
                                    <?php foreach($templates_setting as $template_setting){

                                        $setting_id = $template_setting['setting_id'];
                                        $setting_tilte = $template_setting['setting']['title'];

                                        ?>
                                    <?php if($ym_manufacturers && isset($ym_manufacturers[ $manufacturer['manufacturer_id'] ]['setting_id']) && $ym_manufacturers[ $manufacturer['manufacturer_id'] ]['setting_id']==$setting_id){ ?>
                                        <option selected=""  value="<?php echo $setting_id ?>"><?php echo $setting_tilte; ?></option>
                                    <?php }else{ ?>
                                        <option  value="<?php echo $setting_id ?>"><?php echo $setting_tilte; ?></option>
                                    <?php } ?>
                            <?php } ?>
                            </select>
                        <?php }else{ ?>
                            <div class="alert-info" style="margin-top: 7px;" align="center"><?php echo $text_ym_filter_data_templates_setting_empty ?></div>
                        <?php } ?>
                        </td>

                </tr>
            
                <?php if(isset($delivery_option_by_manufacturer)){ ?>

                    <tr>
                        <td colspan="2">
                            
                            <table class="table">
                                
                                <thead>
                                    <tr>
                                        <td>Вес от (>)</td>
                                        <td>Вес до (<=)</td>
                                        <td>COST</td>
                                        <td>DAYS</td>
                                    </tr>
                                </thead>
                                
                                <?php for($r=0;$r<3;$r++){ ?>

                                <?php
                                
                                $delivery_option_by_manufacturer_this = array(
                                
                                    'w_from'=>'',
                                    'w_to'=>'',
                                    'cost'=>'',
                                    'days'=>'',
                                
                                );
                                
                                if(isset($delivery_option_by_manufacturer[$manufacturer['manufacturer_id']][$r])){
                                
                                    $delivery_option_by_manufacturer_this = $delivery_option_by_manufacturer[$manufacturer['manufacturer_id']][$r];
                                
                                }
                                
                                ?>
                                
                                <tr>
                                    <td>
                                        <input type="text" name="ocext_feed_generator_yamarket_ym_filter_delivery_option_by_manufacturer[<?php echo $manufacturer['manufacturer_id']; ?>][<?php echo $r; ?>][w_from]"  value="<?php echo $delivery_option_by_manufacturer_this['w_from'] ?>" >
                                    </td>
                                    <td>
                                        <input type="text" name="ocext_feed_generator_yamarket_ym_filter_delivery_option_by_manufacturer[<?php echo $manufacturer['manufacturer_id']; ?>][<?php echo $r; ?>][w_to]"  value="<?php echo $delivery_option_by_manufacturer_this['w_to'] ?>" >
                                    </td>
                                    <td>
                                        <input type="text" name="ocext_feed_generator_yamarket_ym_filter_delivery_option_by_manufacturer[<?php echo $manufacturer['manufacturer_id']; ?>][<?php echo $r; ?>][cost]"  value="<?php echo $delivery_option_by_manufacturer_this['cost'] ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="ocext_feed_generator_yamarket_ym_filter_delivery_option_by_manufacturer[<?php echo $manufacturer['manufacturer_id']; ?>][<?php echo $r; ?>][days]"  value="<?php echo $delivery_option_by_manufacturer_this['days'] ?>" >
                                    </td>
                                </tr>

                                <?php } ?>
                            
                            </table>
                        </td>
                    </tr>

                <?php } ?>
            
            <?php } ?>
        </tbody>
    </table>
</div>
<?php }elseif(isset($manufacturers) && !$manufacturers){ ?>
    <div class="alert-info" style="margin-top: 7px;" align="center"><?php echo $text_manufacturers_empty ?></div>
<?php }elseif(isset($attributes) && $attributes){ ?>
<div class="scrollbox" style="max-height: 350px; overflow-y: auto; width: 100%">
    <?php foreach($attributes as $attribute_group_id=>$attribute_group){ ?>

        <?php if(isset($attribute_group_name)){ ?>
            <?php unset($attribute_group_name); ?>
        <?php } ?>

        <?php foreach($attribute_group as $attribute_id=>$attribute){ ?>
            <?php if(!isset($attribute_group_name)){ ?>
                <?php $attribute_group_name = $attribute['attribute_group']; ?>
                <h4 style="margin-top: 15px; margin-bottom: 10px;"><?php echo $attribute_group_name ?></h4>
            <?php } ?>

            <div>

                <?php if(isset($ym_attributes[$attribute_group_id.'___'.$attribute_id]) && $ym_attributes[$attribute_group_id.'___'.$attribute_id]==$attribute_group_id.'___'.$attribute_id){ ?>
                <input checked="" type="checkbox" name="ocext_feed_generator_yamarket_ym_filter_attributes[<?php echo $attribute_group_id.'___'.$attribute_id ?>]" value="<?php echo $attribute_group_id.'___'.$attribute_id ?>" />
                    <?php echo $attribute['name']; ?>
                <?php }else{ ?>
                    <input type="checkbox" name="ocext_feed_generator_yamarket_ym_filter_attributes[<?php echo $attribute_group_id.'___'.$attribute_id ?>]" value="<?php echo $attribute_group_id.'___'.$attribute_id ?>" />
                    <?php echo $attribute['name']; ?>
                <?php } ?>
            </div>

        <?php } ?>

    <?php } ?>
</div>
<?php }elseif(isset($attributes) && !$attributes){ ?>
    <div class="alert-info" style="margin-top: 7px;" align="center"><?php echo $text_attributes_empty ?></div>
<?php }elseif(isset($options) && $options){ ?>
<div class="scrollbox" style="max-height: 350px; overflow-y: auto; width: 100%">
    <?php foreach($options as $option_id=>$option){ ?>

        <div>


            <?php if(isset($ym_options[$option_id]) && $ym_options[$option_id]==$option_id){ ?>
                <input type="checkbox" checked="" name="ocext_feed_generator_yamarket_ym_filter_options[<?php echo $option_id ?>]" value="<?php echo $option_id ?>" />
                <?php echo $option['name']; ?>
            <?php }else{ ?>
                <input type="checkbox" name="ocext_feed_generator_yamarket_ym_filter_options[<?php echo $option_id ?>]" value="<?php echo $option_id ?>" />
                <?php echo $option['name']; ?>
            <?php } ?>

        </div>

    <?php } ?>
</div>
<?php }elseif(isset($options) && !$options){ ?>
    <div class="alert-info" style="margin-top: 7px;" align="center"><?php echo $text_options_empty ?></div>
<?php } ?>

<?php if(isset($columns) && $columns && isset($operators) && $operators){ ?>

    <div class="scrollbox" style="max-height: 350px; overflow-y: auto; width: 100%">
    
    <table class="table table-bordered table-hover">
        <thead>

            <tr>

                <td>Поле в таблице товара</td>
                <td>Оператор</td>
                <td>Значение</td>
                <td>Логика</td>

            </tr>

        </thead>
        <?php for($i=0;$i<5;$i++){ ?>


                    <tr>

                        <td>

                            <div class="input-group" >
                                <select name="ocext_feed_generator_yamarket_ym_filter_columns[product][<?php echo $i ?>][product_field]"  class="form-control select-type-data">
                                    <option value="0" >Не указано</option>
                                        <?php foreach($columns as $product_field => $product_value){ ?>
                                            <?php if(isset($ym_columns['product'][$i]['product_field']) && $ym_columns['product'][$i]['product_field']==$product_field ){ ?>
                                    <option value="<?php echo $product_field ?>" selected="" ><?php echo $product_value; ?></option>
                                            <?php }else{ ?>
                                    <option value="<?php echo $product_field ?>" ><?php echo $product_value; ?></option> 
                                            <?php } ?>
                                        <?php } ?>
                                </select>
                            </div>

                        </td>

                        <td>

                            <div class="input-group" >
                                <select name="ocext_feed_generator_yamarket_ym_filter_columns[product][<?php echo $i ?>][operator]"  class="form-control select-type-data">
                                    <option value="0" >Не указывать</option>
                                        <?php foreach($operators as $product_field => $product_value){ ?>
                                            <?php if(isset($ym_columns['product'][$i]['operator']) && $ym_columns['product'][$i]['operator']==$product_field ){ ?>
                                    <option value="<?php echo $product_field ?>" selected="" ><?php echo $product_value; ?></option>
                                            <?php }else{ ?>
                                    <option value="<?php echo $product_field ?>" ><?php echo $product_value; ?></option> 
                                            <?php } ?>
                                        <?php } ?>
                                </select>
                            </div>

                        </td>

                        <td>

                            <div class="input-group" >

                                <?php if(isset($ym_columns['product'][$i]['value']) ){ ?>
                                    <input name="ocext_feed_generator_yamarket_ym_filter_columns[product][<?php echo $i ?>][value]"  value="<?php echo $ym_columns['product'][$i]['value'] ?>" class="form-control select-type-data" type="text" />
                                <?php }else{ ?>
                                    <input name="ocext_feed_generator_yamarket_ym_filter_columns[product][<?php echo $i ?>][value]" value=""  class="form-control select-type-data" type="text" />
                                <?php } ?>

                            </div>

                        </td>
                        
                        <td>

                            <div class="input-group" >
                                <select name="ocext_feed_generator_yamarket_ym_filter_columns[product][<?php echo $i ?>][logic]"  class="form-control select-type-data">
                                        <?php foreach($logics as $product_field => $product_value){ ?>
                                            <?php if(isset($ym_columns['product'][$i]['logic']) && $ym_columns['product'][$i]['logic']==$product_field ){ ?>
                                    <option value="<?php echo $product_field ?>" selected="" ><?php echo $product_value; ?></option>
                                            <?php }else{ ?>
                                    <option value="<?php echo $product_field ?>" ><?php echo $product_value; ?></option> 
                                            <?php } ?>
                                        <?php } ?>
                                </select>
                            </div>

                        </td>

                    </tr>


        <?php } ?>
    </table>
    
</div>

<div class="scrollbox" style="max-height: 350px; overflow-y: auto; width: 100%">
    
    <table class="table table-bordered table-hover">
        <thead>

            <tr>

                <td>Если необходимо переименовать какие-либо теги, перечислите их здесь через следующий разделитель:<br>oldprice---price_old|offer---product<br>В итоговом фиде вместе тега oldprice будет указан тег price_old, а вместо тега offer будет указан тег product</td>
            </tr>
        </thead>
        

    <tr>
        
        <td>
            <input  type="text" class="form-control" placeholder="" value="<?php if(isset($ym_columns['replace_tags'])){ echo $ym_columns['replace_tags']; }else{ ?><?php } ?>" name="ocext_feed_generator_yamarket_ym_filter_columns[replace_tags]" /> 
        </td>
    </tr>
    </table>

</div>

<?php } ?>