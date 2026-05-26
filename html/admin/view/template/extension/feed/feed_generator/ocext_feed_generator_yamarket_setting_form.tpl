    
    <?php $setting = $template_setting['setting']; ?>

    <input type="hidden" name="setting[setting_id]" value="<?php echo $setting_id ?>" />
    <input type="hidden" name="setting[setting_type]" value="<?php echo $setting_type ?>" />
    <input type="hidden" name="setting[setting_product_id]" value="<?php echo $setting_product_id ?>" />
    <?php if($all_template_setting){ ?>  
    <table class="table table-bordered table-hover">
            <tbody>
                
              
                <tr>
                    <td width="40%"><?php echo $text_template_setting_sample_setting; ?></td>
                    <td>
                        
                        <select class="form-control" onchange="getTemplateSettingYamarket(<?php echo $setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_product_id; ?>,this.value);" >
                                <option value="0"><?php echo $text_select; ?></option>
                            <?php foreach($all_template_setting as $all_template_setting_row){ ?>
                                <option value="<?php echo $all_template_setting_row['setting_id']; ?>"><?php echo $all_template_setting_row['setting']['title']; ?></option>
                            <?php } ?>
                            
                        </select>
                        
                    </td>
                </tr>
            </tbody>
    </table>
    <?php } ?> 
    <table class="table table-bordered table-hover">
            <tbody>
                
                
                
            <tr>
                <td width="40%"><?php echo $text_setting_title; ?></td>
                <td>
                    <?php
                    
                        $setting_title = '';
                        if($setting_id){
                            $setting_title = $setting['title'];
                        }
                    
                    ?>
                    <input  type="text" class="form-control" value="<?php echo $setting_title ?>" type="text" onchange="if(this.value==''){ $('#tab-template_setting_nav<?php echo $setting_id ?>').html('<?php echo $tab_template_setting_default; ?>'); }else{ $('#tab-template_setting_nav<?php echo $setting_id ?>').html(this.value); }" name="setting[title]" />
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Основные сведения о товаре в OFFER</td>
            </tr>
            <tr>
                <td><?php echo $text_setting_offer_name; ?></td>
                <td>
                    <div class="setting_offer_name<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'offer_name');
                    });

                    //--></script>
                    
                    
                    <div class="setting_fields<?php echo $setting_id ?>offer_name" id="setting_fields_composite<?php echo $setting_id ?>offer_name" style="<?php if(isset($setting['offer_name']['field']['status']) && $setting['offer_name']['field']['status'] != 'composite' || !isset($setting['offer_name']['field']['status'])){ ?> display: none; <?php } ?>">
                        
                    <div class="help-box"><?php echo $text_setting_name_composite_help; ?></div>

                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <td><?php echo $text_setting_name_composite_element_composite_sort_order ?></td>
                                    <td><?php echo $text_setting_name_composite_element_composite_status ?></td>
                                </tr>
                            </thead>
                            <?php for($i=1;$i<6;$i++){ ?>
                                <tr>
                                    <td>
                                        <?php echo $i ?>
                                    </td>
                                    <td>
                                        <div class="setting_offer_name_composite_<?php echo $i ?><?php echo $setting_id ?>"></div>
                                        <script type="text/javascript"><!--

                                            $(document).ready(function() {
                                                getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'offer_name_composite_<?php echo $i ?>');
                                            });

                                        //--></script>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    
                    </div>
                    
                </td>
            </tr>
            
            
            <?php $setting_field = 'text_capitalize'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            
            
            
            <tr>
                <td><?php echo $text_setting_description; ?></td>
                <td>
                    <select class="form-control" name="setting[offer_description][field]">
                        <?php foreach($offer_description_parts as $offer_description_part){ ?>
                            <option  <?php if(isset($setting['offer_description']['field']) && $setting['offer_description']['field'] == $offer_description_part){ ?> selected="" <?php } ?> value="<?php echo $offer_description_part; ?>"><?php echo ${'text_setting_description_'.$offer_description_part}; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_vendor_model; ?></td>
                <td>
                    <select class="form-control" name="setting[vendor.model]">
                        <?php if(isset($setting['vendor.model']) && $setting['vendor.model'] == 'vendor.model'){ ?>
                            <option selected="" value="vendor.model"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="vendor.model"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_vendor; ?></td>
                <td>
                    <div class="setting_vendor<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'vendor');
                    });

                    //--></script>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_vendorCode; ?></td>
                <td>
                    <div class="setting_vendorCode<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'vendorCode');
                    });

                    //--></script>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_model; ?></td>
                <td>
                    <div class="setting_model<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'model');
                    });

                    //--></script>
                    
                    <div class="setting_fields<?php echo $setting_id ?>model" id="setting_fields_composite<?php echo $setting_id ?>model" style="<?php if(isset($setting['model']['field']['status']) && $setting['model']['field']['status'] != 'composite' || !isset($setting['offer_name']['field']['status'])){ ?> display: none; <?php } ?>">
                        <div class="help-box"><?php echo $text_setting_name_composite_help; ?></div>

                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <td><?php echo $text_setting_name_composite_element_composite_sort_order ?></td>
                                    <td><?php echo $text_setting_name_composite_element_composite_status ?></td>
                                </tr>
                            </thead>
                            <?php for($i=1;$i<6;$i++){ ?>
                                <tr>
                                    <td>
                                        <?php echo $i ?>
                                    </td>
                                    <td>
                                        <div class="setting_model_composite_<?php echo $i ?><?php echo $setting_id ?>"></div>
                                        <script type="text/javascript"><!--

                                            $(document).ready(function() {
                                                getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'model_composite_<?php echo $i ?>');
                                            });

                                        //--></script>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </td>
            </tr>
            
            
            <?php $setting_field = 'pickup'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            <?php $setting_field = 'store'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            <?php $setting_field = 'delivery'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_delivery_options; ?></td>
                <td>
                    <select name="setting[delivery-options][status]" onchange="if(this.value!=0){ $('#template_setting_delivery-options<?php echo $setting_id ?>').show() }else{ $('#template_setting_delivery-options<?php echo $setting_id ?>').hide() }" class="form-control" >
                        <?php $setting_delivery_options_css = "display:none"; ?>
                        <?php if(isset($setting['delivery-options']['status']) && $setting['delivery-options']['status'] == 'delivery-options'){ ?>
                            <?php $setting_delivery_options_css = "display:block"; ?>
                            <option selected=""  value="delivery-options"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="delivery-options"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                    <div id="template_setting_delivery-options<?php echo $setting_id ?>" style="margin-top: 5px; <?php echo $setting_delivery_options_css ?>">
                    <div class="help-box"><?php echo $text_setting_delivery_options_help; ?></div>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <td>cost</td>
                            <td>days</td>
                            <td>order-before</td>
                            <td>Цена товара от (≥)</td>
                            <td>Цена товара до (<)</td>
                            
                            <td>Вес товара от (≥)</td>
                            <td>Вес товара до (<)</td>
                            <td >При статусе заказа</td>
                        </thead>
                        <?php for($i=0;$i<$ocext_feed_generator_yamarket_general_setting['count_delivery_options'];$i++){ ?>
                        <?php
                        $cost = '';
                        $days = '';
                        $order_before = '';
                        $price_from = '';
                        $price_to = '';
                        $weight_from = '';
                        $weight_to = '';
                        if( isset($setting['delivery-options'][$i]['cost']) ){
                            $cost = $setting['delivery-options'][$i]['cost'];
                        }
                        if( isset($setting['delivery-options'][$i]['order-before']) ){
                            $order_before = $setting['delivery-options'][$i]['order-before'];
                        }
                        if( isset($setting['delivery-options'][$i]['days']) ){
                            $days = $setting['delivery-options'][$i]['days'];
                        }
                        if( isset($setting['delivery-options'][$i]['price_from']) ){
                            $price_from = $setting['delivery-options'][$i]['price_from'];
                        }
                        if( isset($setting['delivery-options'][$i]['price_to']) ){
                            $price_to = $setting['delivery-options'][$i]['price_to'];
                        }
                        if( isset($setting['delivery-options'][$i]['weight_from']) ){
                            $weight_from = $setting['delivery-options'][$i]['weight_from'];
                        }
                        if( isset($setting['delivery-options'][$i]['weight_to']) ){
                            $weight_to = $setting['delivery-options'][$i]['weight_to'];
                        }
                        ?>
                        <tr>
                            <td><input style="padding:0px;" type="text" value="<?php echo $cost ?>" name="setting[delivery-options][<?php echo $i ?>][cost]" class="form-control" /></td>
                            <td><input style="padding:0px;" type="text" value="<?php echo $days ?>" name="setting[delivery-options][<?php echo $i ?>][days]" class="form-control" /></td>
                            <td><input style="padding:0px;" type="text" value="<?php echo $order_before ?>" name="setting[delivery-options][<?php echo $i ?>][order-before]" class="form-control" /></td>
                            
                            <td><input style="padding:0px;" type="text" value="<?php echo $price_from ?>" name="setting[delivery-options][<?php echo $i ?>][price_from]" class="form-control" /></td>
                            <td><input style="padding:0px;" type="text" value="<?php echo $price_to ?>" name="setting[delivery-options][<?php echo $i ?>][price_to]" class="form-control" /></td>
                            <td><input style="padding:0px;" type="text" value="<?php echo $weight_from ?>" name="setting[delivery-options][<?php echo $i ?>][weight_from]" class="form-control" /></td>
                            <td><input style="padding:0px;" type="text" value="<?php echo $weight_to ?>" name="setting[delivery-options][<?php echo $i ?>][weight_to]" class="form-control" /></td>
                            
                            <td >
                                
                                
                                <?php if($stock_statuses){ ?>
                                    <div class="scrollbox" style="width:110px; min-height:30px; overflow-y: auto">
                                        <?php foreach($stock_statuses as $stock_status){ ?>
                                            <div>
                                                <?php if(isset($setting['delivery-options'][$i]['stock_status_id'][$stock_status['stock_status_id']])){ ?>
                                                    <input checked="" type="checkbox" name="setting[delivery-options][<?php echo $i ?>][stock_status_id][<?php echo $stock_status['stock_status_id'] ?>]" value="<?php echo $stock_status['stock_status_id'] ?>" />
                                                    <small><?php echo $stock_status['name']; ?></small>
                                                <?php }else{ ?>
                                                    <input type="checkbox" name="setting[delivery-options][<?php echo $i ?>][stock_status_id][<?php echo $stock_status['stock_status_id'] ?>]" value="<?php echo $stock_status['stock_status_id'] ?>" />
                                                    <small><?php echo $stock_status['name']; ?></small>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php }else{ ?>
                                    <div class="alert-info" align="center"><?php echo $text_setting_offer_stock_statuses_empty ?></div>
                                <?php } ?>
                                
                                
                                
                            </td>
                            
                        </tr>
                        <?php } ?> 
                    </table>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Способ информации о наличии</td>
            </tr>
            <tr>
                <td><?php echo $text_setting_offer_available_true; ?></td>
                <td>
                    <?php if($stock_statuses){ ?>
                        <div class="scrollbox" style="height: 70px; overflow-y: auto">
                            <?php foreach($stock_statuses as $stock_status){ ?>
                                <div>
                                    <?php if(isset($setting['available_true']) && $setting['available_true'] == $stock_status['stock_status_id']){ ?>
                                        <input type="radio" checked="" name="setting[available_true]" value="<?php echo $stock_status['stock_status_id'] ?>" />
                                        <?php echo $stock_status['name']; ?>
                                    <?php }else{ ?>
                                        <input type="radio" name="setting[available_true]" value="<?php echo $stock_status['stock_status_id'] ?>" />
                                        <?php echo $stock_status['name']; ?>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php }else{ ?>
                        <div class="alert-info" align="center"><?php echo $text_setting_offer_stock_statuses_empty ?></div>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_offer_available_false; ?></td>
                <td>
                    <?php if($stock_statuses){ ?>
                        <div class="scrollbox" style="height: 70px; overflow-y: auto">
                            <?php foreach($stock_statuses as $stock_status){ ?>
                                <div>
                                    <?php if(isset($setting['available_false'][$stock_status['stock_status_id']])){ ?>
                                        <input checked="" type="checkbox" name="setting[available_false][<?php echo $stock_status['stock_status_id'] ?>]" value="<?php echo $stock_status['stock_status_id'] ?>" />
                                        <?php echo $stock_status['name']; ?>
                                    <?php }else{ ?>
                                        <input type="checkbox" name="setting[available_false][<?php echo $stock_status['stock_status_id'] ?>]" value="<?php echo $stock_status['stock_status_id'] ?>" />
                                        <?php echo $stock_status['name']; ?>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php }else{ ?>
                        <div class="alert-info" align="center"><?php echo $text_setting_offer_stock_statuses_empty ?></div>
                    <?php } ?>
                </td>
            </tr>
            <?php $setting_field = 'available_by_quantity'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field]){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }elseif(isset($setting[$setting_field]) && !$setting[$setting_field]){ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <?php $setting_field = 'dispublic_quantity'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <?php $setting_field = 'replace_av_true'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?>true<?php } ?>" name="setting[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            
            <?php $setting_field = 'replace_av_false'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?>false<?php } ?>" name="setting[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Дополнительные теги и настройки</td>
            </tr>
            <?php $setting_field = 'url_whis_path'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            
            <tr>
                <td><?php echo $text_setting_country_of_origin; ?></td>
                <td>
                    <div class="setting_country_of_origin<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'country_of_origin');
                    });

                    //--></script>
                </td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_custom_offer_id; ?></td>
                <td>
                    <div class="setting_custom_offer_id<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'custom_offer_id');
                    });

                    //--></script>
                </td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_barcode; ?></td>
                <td>
                    <div class="setting_barcode<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'barcode');
                    });

                    //--></script>
                </td>
            </tr>
            
            
            
            <?php $setting_field = 'fee_select'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <div class="setting_<?php echo $setting_field; ?><?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'<?php echo $setting_field; ?>');
                    });

                    //--></script>
                </td>
            </tr>
            <?php $setting_field = 'fee_input'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; } ?>" name="setting[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_expiry; ?></td>
                <td>
                    <div class="setting_expiry<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'expiry');
                    });

                    //--></script>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_weight; ?></td>
                <td>
                    <div class="setting_weight<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'weight');
                    });

                    //--></script>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_dimensions; ?></td>
                <td>
                    <div class="setting_dimensions<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'dimensions');
                    });

                    //--></script>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_typePrefix; ?></td>
                <td>
                    <div class="setting_typePrefix<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'typePrefix');
                    });

                    //--></script>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_age; ?></td>
                <td>
                    <div class="setting_age<?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'age');
                    });

                    //--></script>
                <select class="form-control" name="setting[age][unit]">
                        <?php if(isset($setting['age']['unit']) && $setting['age']['unit'] == 'month'){ ?>
                            <option value="0"><?php echo $text_need_select; ?></option>
                            <option selected="" value="month"><?php echo $text_setting_age_unit_month; ?></option>
                            <option value="year"><?php echo $text_setting_age_unit_year; ?></option>
                        <?php }elseif(isset($setting['age']['unit']) && $setting['age']['unit'] == 'year'){ ?>
                            <option value="0"><?php echo $text_need_select; ?></option>
                            <option value="month"><?php echo $text_setting_age_unit_month; ?></option>
                            <option selected="" value="year"><?php echo $text_setting_age_unit_year; ?></option>
                        <?php }else{ ?>
                            <option value="0"><?php echo $text_need_select; ?></option>
                            <option value="month"><?php echo $text_setting_age_unit_month; ?></option>
                            <option value="year"><?php echo $text_setting_age_unit_year; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            <?php $setting_field = 'cpa'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            
            <?php $setting_field = 'rec'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            
            <?php $setting_field = 'manufacturer_warranty'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            <?php $setting_field = 'adult'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <?php $setting_field = 'attribute_sintaxis'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="attribute_sintaxis"><?php echo $entry_template_setting_attribute_sintaxis_1; ?></option>
                            <option value="0"><?php echo $entry_template_setting_attribute_sintaxis_0; ?></option>
                        <?php }else{ ?>
                            <option value="attribute_sintaxis"><?php echo $entry_template_setting_attribute_sintaxis_1; ?></option>
                            <option selected="" value="0"><?php echo $entry_template_setting_attribute_sintaxis_0; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <?php $setting_field = 'market_category'; ?>
            <tr style="display:none;">
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; } ?>" name="setting[<?php echo $setting_field ?>]" /> 
                </td>
            </tr>
            
            <?php $setting_field = 'all_product_category'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <!--
            <?php $setting_field = 'disable_parent_categories'; ?>
            <tr>
                <td>Исключать родителькие категории</td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            -->
            <?php $setting_field = 'add_ordering_to_category'; ?>
            <tr>
                <td>Выводить в категории порядок сортировки в теге ordering</td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            <?php $setting_field = 'add_url_to_category'; ?>
            <tr>
                <td>Выводить в категории ссылку на категорию в теге url</td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Способ формирования изображений</td>
            </tr>
            <?php $setting_field = 'no_pictures'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            
            
            <tr>
                <td><?php echo $text_setting_pictures_sizes; ?>, px</td>
                <td>
                    <input  type="text" class="form-control" placeholder="px" value="<?php if(isset($setting['pictures_sizes'])){ echo $setting['pictures_sizes']; } ?>" name="setting[pictures_sizes]" />
                </td>
            </tr>
            
            <?php if(isset($rule_pictures)){ ?>
            
            <?php $setting_field = 'rule_picture'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <?php if($rule_pictures){ ?>
                        <select class="form-control" name="setting[<?php echo $setting_field; ?>]">
                            <?php foreach($rule_pictures as $rule_picture_id){ ?>
                                
                                <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $rule_picture_id){ ?>
                                    <option selected="" value="<?php echo $rule_picture_id; ?>"><?php echo ${'text_setting_'.$setting_field.'_'.$rule_picture_id}; ?></option>
                                <?php }else{ ?>
                                    <option value="<?php echo $rule_picture_id; ?>"><?php echo ${'text_setting_'.$setting_field.'_'.$rule_picture_id}; ?></option>
                                <?php } ?>
                            
                            <?php } ?>
                        </select>
                    <?php }else{ ?>
                        <div class="alert-info" align="center"><?php echo $text_setting_currencies_empty; ?></div>
                    <?php } ?>
                </td>
            </tr>
            
            <?php } ?>
            
            <tr>
                <td><?php echo $text_setting_count_pictures; ?></td>
                <td>
                    <select class="form-control"  name="setting[count_pictures]">
                        <?php for($i=1;$i<10;$i++){ ?>
                            <?php if(isset($setting['count_pictures']) && $setting['count_pictures'] == $i){ ?>
                                <option selected="" value="<?php echo $i ?>"><?php echo $i ?></option>
                            <?php }else{ ?>
                                <option value="<?php echo $i ?>"><?php echo $i ?></option>
                            <?php } ?>
                        <?php } ?>
                        <option value="0">0</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Ценообразвание</td>
            </tr>
            <?php $setting_field = 'oldprice'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_ymlprice; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="%" value="<?php if(isset($setting['ymlprice'])){ echo $setting['ymlprice']; } ?>" name="setting[ymlprice]" /> 
                </td>
            </tr>
            
            <?php $setting_field = 'zero_price'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            
            
            <?php if(isset($currencies)){ ?>
            <?php $setting_field = 'price_currencies_from'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <?php if($currencies){ ?>
                        <select class="form-control" name="setting[<?php echo $setting_field; ?>]">
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                            <?php foreach($currencies as $currency_code => $currency_data){ ?>
                                
                                <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $currency_code){ ?>
                                    <option selected="" value="<?php echo $currency_code; ?>"><?php echo $currency_data['title']; ?></option>
                                <?php }else{ ?>
                                    <option value="<?php echo $currency_code; ?>"><?php echo $currency_data['title']; ?></option>
                                <?php } ?>
                            
                            <?php } ?>
                        </select>
                    <?php }else{ ?>
                        <div class="alert-info" align="center"><?php echo $text_setting_currencies_empty; ?></div>
                    <?php } ?>
                </td>
            </tr>
            <?php $setting_field = 'price_currencies_to'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <?php if($currencies){ ?>
                        <select class="form-control" name="setting[<?php echo $setting_field; ?>]">
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                            <?php foreach($currencies as $currency_code => $currency_data){ ?>
                                
                                <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $currency_code){ ?>
                                    <option selected="" value="<?php echo $currency_code; ?>"><?php echo $currency_data['title']; ?></option>
                                <?php }else{ ?>
                                    <option value="<?php echo $currency_code; ?>"><?php echo $currency_data['title']; ?></option>
                                <?php } ?>
                            
                            <?php } ?>
                        </select>
                    <?php }else{ ?>
                        <div class="alert-info" align="center"><?php echo $text_setting_currencies_empty; ?></div>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
            
            
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">У товаров, которым будет присвоен этот шаблон, установить следующие правила попадания в выгрузку</td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_price_from; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting['price_from'])){ echo $setting['price_from']; } ?>" name="setting[price_from]" /> 
                </td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_price_to; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting['price_to'])){ echo $setting['price_to']; } ?>" name="setting[price_to]" /> 
                </td>
            </tr>
            
            
            <tr>
                <td><?php echo $text_setting_product_id_from; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting['product_id_from'])){ echo $setting['product_id_from']; } ?>" name="setting[product_id_from]" /> 
                </td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_product_id_to; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting['product_id_to'])){ echo $setting['product_id_to']; } ?>" name="setting[product_id_to]" /> 
                </td>
            </tr>
            
            
            
            
            <?php $setting_field = 'enb_product_ids'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; } ?>" name="setting[<?php echo $setting_field ?>]" /> 
                </td>
            </tr>
            
            <?php $setting_field = 'dis_product_ids'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; } ?>" name="setting[<?php echo $setting_field ?>]" /> 
                </td>
            </tr>
            
            
            
            
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Способ формирования SALES_NOTE</td>
            </tr>
            
            <?php $setting_field = 'sales_notes_select_on_available_true'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <div class="setting_<?php echo $setting_field; ?><?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'<?php echo $setting_field; ?>');
                    });

                    //--></script>
                </td>
            </tr>
            <?php $setting_field = 'sales_notes_on_available_true'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <input  type="text" class="form-control" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; } ?>" name="setting[<?php echo $setting_field; ?>]" />
                </td>
            </tr>
            
            
            <?php $setting_field = 'sales_notes_select'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <div class="setting_<?php echo $setting_field; ?><?php echo $setting_id ?>"></div>
                    <script type="text/javascript"><!--

                    $(document).ready(function() {
                        getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'<?php echo $setting_field; ?>');
                    });

                    //--></script>
                </td>
            </tr>
            <tr>
                <td><?php echo $text_setting_sales_notes; ?></td>
                <td>
                    <input  type="text" class="form-control" value="<?php if(isset($setting['sales_notes'])){ echo $setting['sales_notes']; } ?>" name="setting[sales_notes]" />
                </td>
            </tr>
            <?php $setting_field = 'sales_notes_on_available_false'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            
            <?php if(isset($sales_note_by_rule)){ ?>
            
                <?php echo $sales_note_by_rule; ?>
            
            <?php } ?>
            
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Создавать товары по опции(ям)</td>
            </tr>
            
            
            
            
            
            
            <?php $setting_field = 'divide_on_option'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select class="form-control" name="setting[<?php echo $setting_field; ?>]" onchange="if(this.value!=0){ $('#setting_fields<?php echo $setting_id ?>divide_on_option_option_id').show() }else{ $('#setting_fields<?php echo $setting_id ?>divide_on_option_option_id').hide() }">
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                    
                    <div id="setting_fields<?php echo $setting_id; ?>divide_on_option_option_id" style="margin-top:5px;<?php if(isset($setting[$setting_field]) && !$setting[$setting_field] || !isset($setting[$setting_field])){ ?> display: none; <?php } ?>">
                        
                        <div class="setting_divide_on_option_option_id<?php echo $setting_id ?>"></div>
                        <script type="text/javascript"><!--

                            $(document).ready(function() {
                                getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'divide_on_option_option_id');
                            });

                    //--></script>
                        
                        
                        
                        
                        <table class="table table-bordered table-hover">
                            <?php $setting_field = 'divide_on_option_prefix'; ?>
                            <tr>
                                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                                <td>
                                    <input  type="text" class="form-control" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?>OPTION<?php } ?>" name="setting[<?php echo $setting_field; ?>]" />
                                </td>
                            </tr>
                            <?php $setting_field = 'divide_on_option_available_by_option_quantity'; ?>
                            <tr>
                                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                                <td>
                                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                                            <option value="0"><?php echo $text_disable; ?></option>
                                        <?php }else{ ?>
                                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <?php $setting_field = 'divide_on_option_add_to_model'; ?>
                            <tr>
                                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                                <td>
                                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                                            <option value="0"><?php echo $text_disable; ?></option>
                                        <?php }else{ ?>
                                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <?php $setting_field = 'divide_on_option_add_to_name'; ?>
                            <tr>
                                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                                <td>
                                    <select  class="form-control" name="setting[<?php echo $setting_field; ?>]">
                                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                                            <option value="0"><?php echo $text_disable; ?></option>
                                        <?php }else{ ?>
                                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            
                            
                        </table>
                        
                        
                        
                        
                        
                    </div>
                    
                    
                    
                </td>
            </tr>
            
            <?php if( isset($divide_by_options) ){ ?>
            
                <?php echo $divide_by_options; ?>
            
            <?php } ?>
            
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Свои теги и значения для них</td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_custom_elements; ?></td>
                <td>
                    
                    
                    
                    <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <td><?php echo $text_setting_custom_elements_name_element ?></td>
                                    <td><?php echo $text_setting_custom_elements_field_element ?></td>
                                </tr>
                            </thead>
                            <?php for($i=0;$i<$ocext_feed_generator_yamarket_general_setting['count_custom_elements'];$i++){ ?>
                                <tr>
                                    <td>
                                        <?php if(isset($setting['custom_elements_name_'.$i]) && $setting['custom_elements_name_'.$i]){ ?>
                                        <<input type="text"  name="setting[custom_elements_name_<?php echo $i ?>]" value="<?php echo $setting['custom_elements_name_'.$i] ?>" />>
                                        <?php }else{ ?>
                                        <<input type="text"  name="setting[custom_elements_name_<?php echo $i ?>]" value="" />>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="setting_custom_elements_field_<?php echo $i ?><?php echo $setting_id ?>"></div>
                                        <script type="text/javascript"><!--

                                            $(document).ready(function() {
                                                getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'custom_elements_field_<?php echo $i ?>');
                                            });

                                        //--></script>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    
                </td>
            </tr>
            
            
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Создание меток в урлах на товары (например, UTM)</td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_param_to_url; ?></td>
                <td>
                    
                    
                    
                    <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <td><?php echo $text_setting_param_to_url_name ?></td>
                                    <td><?php echo $text_setting_param_to_url_value ?></td>
                                </tr>
                            </thead>
                            <?php for($i=0;$i<$ocext_feed_generator_yamarket_general_setting['count_custom_elements'];$i++){ ?>
                                <tr>
                                    <td>
                                        <?php if(isset($setting['param_to_url_name_'.$i]) && $setting['param_to_url_name_'.$i]){ ?>
                                        &<input type="text"  name="setting[param_to_url_name_<?php echo $i ?>]" value="<?php echo $setting['param_to_url_name_'.$i] ?>" />=
                                        <?php }else{ ?>
                                        &<input type="text"  name="setting[param_to_url_name_<?php echo $i ?>]" value="" />=
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="setting_param_to_url_value_<?php echo $i ?><?php echo $setting_id ?>"></div>
                                        <script type="text/javascript"><!--

                                            $(document).ready(function() {
                                                getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'param_to_url_value_<?php echo $i ?>');
                                            });

                                        //--></script>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    
                </td>
            </tr>
            
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">BID и CBID</td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_bid_field; ?></td>
                <td>
                    
                    
                    
                    <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <td>Параметры (может быть задано формулой или из данных о товаре)</td>
                                </tr>
                            </thead>
                            <?php for($i=0;$i<5;$i++){ ?>
                                <tr>
                                    <td>
                                        <div class="setting_bid_field_to_db_<?php echo $i ?><?php echo $setting_id ?>"></div>
                                        <script type="text/javascript"><!--

                                            $(document).ready(function() {
                                                getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'bid_field_to_db_<?php echo $i ?>');
                                            });

                                        //--></script>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="<?php if($i>3){ echo 'display:none;'; } ?>">
                                        
                                        <?php $operators = array('+'=>'+','-'=>'-','/'=>'/','*'=>'*'); ?>
                                        
                                        <select class="form-control" name="setting[bid_field_to_db_oper_<?php echo $i ?>]">
                                            <option selected="" value="0">Оператор</option>
                                            <?php foreach($operators as $operator){ ?>
                                                <?php if(isset($setting['bid_field_to_db_oper_'.$i]) && $setting['bid_field_to_db_oper_'.$i] == $operator){ ?>
                                                    <option selected="" value="<?php echo $operator; ?>"><?php echo $operator; ?></option>
                                                <?php }else{ ?>
                                                    <option value="<?php echo $operator; ?>"><?php echo $operator; ?></option>
                                                <?php } ?>

                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    
                </td>
            </tr>
            
            <tr>
                <td><?php echo $text_setting_cbid_field; ?></td>
                <td>
                    
                    
                    
                    <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <td>Параметры (может быть задано формулой или из данных о товаре)</td>
                                </tr>
                            </thead>
                            <?php for($i=0;$i<5;$i++){ ?>
                                <tr>
                                    <td>
                                        <div class="setting_cbid_field_to_db_<?php echo $i ?><?php echo $setting_id ?>"></div>
                                        <script type="text/javascript"><!--

                                            $(document).ready(function() {
                                                getSettingFieldsYamarket(<?php echo $sample_setting_id; ?>,'<?php echo $setting_type ?>',<?php echo $setting_id ?>,'cbid_field_to_db_<?php echo $i ?>');
                                            });

                                        //--></script>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="<?php if($i>3){ echo 'display:none;'; } ?>">
                                        
                                        <?php $operators = array('+'=>'+','-'=>'-','/'=>'/','*'=>'*'); ?>
                                        
                                        <select class="form-control" name="setting[cbid_field_to_db_oper_<?php echo $i ?>]">
                                            <option selected="" value="0">Оператор</option>
                                            <?php foreach($operators as $operator){ ?>
                                                <?php if(isset($setting['cbid_field_to_db_oper_'.$i]) && $setting['cbid_field_to_db_oper_'.$i] == $operator){ ?>
                                                    <option selected="" value="<?php echo $operator; ?>"><?php echo $operator; ?></option>
                                                <?php }else{ ?>
                                                    <option value="<?php echo $operator; ?>"><?php echo $operator; ?></option>
                                                <?php } ?>

                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    
                </td>
            </tr>
            
            
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Допонительно</td>
            </tr>
            
            <?php $setting_field = 'replace_tags'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="setting[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            
            <?php $setting_field = 'disable_this_product'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <select class="form-control" name="setting[<?php echo $setting_field; ?>]" >
                        <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $setting_field){ ?>
                            <option selected="" value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                        <?php }else{ ?>
                            <option value="<?php echo $setting_field; ?>"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            <?php if(isset($feed_fb)){ ?>
            
            <?php $setting_field = 'fb_condition'; ?>
            <tr>
                <td><?php echo ${'text_setting_'.$setting_field}; ?></td>
                <td>
                    <?php if($fb_conditions){ ?>
                        <select class="form-control" name="setting[<?php echo $setting_field; ?>]">
                            <?php foreach($fb_conditions as $code_data => $title_data){ ?>
                                
                                <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $code_data){ ?>
                                    <option selected="" value="<?php echo $code_data; ?>"><?php echo $title_data; ?></option>
                                <?php }else{ ?>
                                    <option value="<?php echo $code_data; ?>"><?php echo $title_data; ?></option>
                                <?php } ?>
                            
                            <?php } ?>
                        </select>
                    <?php } ?>
                </td>
            </tr>
             <?php } ?>
            
            
            
            <tr>
                <td><?php echo $text_setting_status; ?></td>
                <td>
                    <select class="form-control" name="setting[status]">
                        <?php if(isset($setting['status']) && $setting['status'] == '1'){ ?>
                            <option selected="" value="1"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                            <option value="2"><?php echo $text_delete; ?></option>
                        <?php }else{ ?>
                            <option selected="" value="1"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                            <option value="2"><?php echo $text_delete; ?></option>
                        <?php } ?> ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table> 