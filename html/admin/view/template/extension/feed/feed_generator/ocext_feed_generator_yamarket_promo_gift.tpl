    
    <?php
    
    $setting = $promoss['promos'];
    
    $setting_type = 0;
    
    ?>

    <input type="hidden" name="promos[promos_id]" value="<?php echo $promos_id ?>" />
    <?php if($all_promoss){ ?>  
    <table class="table table-bordered table-hover">
            <tbody>
                
              
                <tr>
                    <td width="40%"><?php echo $text_template_setting_sample_setting; ?></td>
                    <td>
                        
                        <select class="form-control" onchange="getPromoGift(<?php echo $promos_id; ?>,this.value);" >
                                <option value="0"><?php echo $text_select; ?></option>
                            <?php foreach($all_promoss as $all_template_setting_row){ ?>
                                <option value="<?php echo $all_template_setting_row['promos_id']; ?>"><?php echo $all_template_setting_row['promos']['title']; ?></option>
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
                        if($promos_id){
                            $setting_title = $setting['title'];
                        }
                    
                    ?>
                    <input  type="text" class="form-control" value="<?php echo $setting_title ?>" type="text" onchange="if(this.value==''){ $('#tab-promo-gift_nav<?php echo $promos_id ?>').html('Новая промоакция'); }else{ $('#tab-promo-gift_nav<?php echo $promos_id ?>').html(this.value); }" name="promos[title]" />
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-center" style="background:red; color:white">Основные сведения о промоакции</td>
            </tr>
            
            <?php $setting_field = 'start-date'; ?>
            <tr>
                <td>Начало акции (например: <b>2019-10-16 01:00:01+0500</b>)</td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            
            <?php $setting_field = 'end-date'; ?>
            <tr>
                <td>Завершение акции (например: <b>2019-10-16 01:00:01+0500</b>)</td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            
            <?php $setting_field = 'url'; ?>
            <tr>
                <td>Ссылка на описание акции на сайте магазина</td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            
            <?php $setting_field = 'description'; ?>
            <tr>
                <td>Краткое описание акции. Максимум 500 символов. Можно использовать xhtml-разметку, но только в виде блока символьных данных CDATA</td>
                <td>
                    <textarea class="form-control" placeholder="" name="promos[<?php echo $setting_field; ?>]" ><?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?></textarea> 
                </td>
            </tr>
            
            <?php $setting_field = 'product_ids'; ?>
            <tr>
                <td>offer-id, как он будет установлен в товаре в настройках Шаблона описаний товара (product_id, или модель и т.п.) товаров, которые участвуют в акции, через запятую</td>
                <td>
                    <textarea class="form-control" placeholder="" name="promos[<?php echo $setting_field; ?>]" ><?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?></textarea> 
                </td>
            </tr>
            
            
            
            <?php $setting_field = 'category_ids'; ?>
            <tr>
                <td>Категории, товаров которых участвуют в акции</td>
                <td>
                    <select  style="max-height: 300px;" class="form-control" multiple tabindex="10" name="promos[<?php echo $setting_field; ?>][]">
                        
                        <?php foreach($categories as $category){ ?>
                        
                            <option value="<?php echo $category['category_id']; ?>"
                                <?php if(isset($setting[$setting_field]) && in_array($category['category_id'],$setting[$setting_field]) ){ ?>
                                    selected=""
                                <?php } ?>
                                ><?php echo $category['name']; ?>
                            </option>
                        
                        <?php } ?>
                        
                    </select>
                </td>
            </tr>
            
            <?php $setting_field = 'promos_type'; ?>
            <tr>
                <td>Выбрать тип акции</td>
                <td>
                    <select onchange="$('.promos-type').hide(); $('.'+this.value).show(); " class="form-control" name="promos[<?php echo $setting_field; ?>]">
                        
                            <option value="0">Выбрать</option>
                        
                        <?php foreach($promos_types as $promos_type => $promos_type_title){ ?>
                        
                            <?php if(isset($setting[$setting_field]) && $setting[$setting_field] == $promos_type){ ?>
                                <option selected="" value="<?php echo $promos_type; ?>"><?php echo $promos_type_title; ?></option>
                            <?php }else{ ?>
                                <option value="<?php echo $promos_type; ?>"><?php echo $promos_type_title; ?></option>
                            <?php } ?>
                        
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td>Акция включена</td>
                <td>
                    <select class="form-control" name="promos[status]">
                        <?php if(isset($setting['status']) && $setting['status'] == '1'){ ?>
                            <option selected="" value="1"><?php echo $text_enable; ?></option>
                            <option value="0"><?php echo $text_disable; ?></option>
                            <option value="2"><?php echo $text_delete; ?></option>
                        <?php }else{ ?>
                            <option value="1"><?php echo $text_enable; ?></option>
                            <option selected="" value="0"><?php echo $text_disable; ?></option>
                            <option value="2"><?php echo $text_delete; ?></option>
                        <?php } ?> ?>
                    </select>
                </td>
            </tr>
            
            <tr class="promos-type promo-code">
                <td colspan="2" class="text-center" style="background:red; color:white">Настройка акции "Промокод"</td>
            </tr>
            <?php $setting_field = 'promo-code'; ?>
            <tr class="promos-type promo-code">
                <td>Текст промокода. Максимальная длина — 20 символов</td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            <?php $setting_field = 'discount_percent'; ?>
            <tr class="promos-type promo-code">
                <td>Размер скидки в процентах от стоимости, числом (в процентах, доступные значения: от 5 до 95 процентов)</td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            
            <?php $setting_field = 'discount_currency'; ?>
            <tr class="promos-type promo-code">
                <td>Размер скидки в деньгах от стоимости, числом (в влюте прайса, доступные значения кратные 50)</td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            
            <tr class="promos-type flash-discount">
                <td colspan="2" class="text-center" style="background:red; color:white">Настройка акции "Специальная цена"</td>
            </tr>
            <?php $setting_field = 'discount-price'; ?>
            <tr class="promos-type flash-discount">
                <td>Цена со скидкой на время акции</td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            
            
            <tr class="promos-type n-plus-m">
                <td colspan="2" class="text-center" style="background:red; color:white">Настройка акции "N+M"</td>
            </tr>
            <?php $setting_field = 'required-quantity'; ?>
            <tr class="promos-type n-plus-m">
                <td>Количество товаров (в штуках), которое нужно приобрести, чтобы получить подарок. Можно указывать только числовые значения. Максимально допустимое значение — 24. Значение по умолчанию — 1 (один товар)</td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            <?php $setting_field = 'free-quantity'; ?>
            <tr class="promos-type n-plus-m">
                <td>Количество товаров, которые покупатель получит в подарок. Максимально допустимое значение — 24</td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            
            
            <tr class="promos-type gift-with-purchase">
                <td colspan="2" class="text-center" style="background:red; color:white">Настройка акции "Подарок"</td>
            </tr>
            <?php $setting_field = 'required-quantity-present'; ?>
            <tr class="promos-type gift-with-purchase">
                <td>Количество товаров (в штуках), которое нужно приобрести, чтобы получить подарок. Можно указывать только числовые значения. Максимально допустимое значение — 24. Значение по умолчанию — 1 (один товар)</td>
                <td>
                    <input  type="text" class="form-control" placeholder="" value="<?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>]" /> 
                </td>
            </tr>
            <?php $setting_field = 'product_as_gift_ids'; ?>
            <tr  class="promos-type gift-with-purchase">
                <td>offer-id, как он будет установлен в товаре в настройках Шаблона описаний товара (product_id, или модель и т.п.) товаров, которые идут подарок, через запятую</td>
                <td>
                    <textarea class="form-control" placeholder="" name="promos[<?php echo $setting_field; ?>]" ><?php if(isset($setting[$setting_field])){ echo $setting[$setting_field]; }else{ ?><?php } ?></textarea> 
                </td>
            </tr>
            
            <?php $setting_field = 'promo-gifts'; ?>
            
            <?php for($p=0;$p<12;$p++){ ?>
            
                <tr class="promos-type gift-with-purchase">
                    <td>Подарок <?php echo ($p+1); ?></td>
                    <td>
                        <input  type="text" class="form-control" placeholder="Название товара подарка" value="<?php if(isset($setting[$setting_field]) && isset($setting[$setting_field][$p]) ){ echo $setting[$setting_field][$p]['name']; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>][<?php echo $p; ?>][name]" /> 
                        <input  type="text" class="form-control" placeholder="Ссылка на изображение товара подарка" value="<?php if(isset($setting[$setting_field]) && isset($setting[$setting_field][$p]) ){ echo $setting[$setting_field][$p]['picture']; }else{ ?><?php } ?>" name="promos[<?php echo $setting_field; ?>][<?php echo $p; ?>][picture]" /> 
                    </td>
                </tr>
            
            <?php } ?>
            
        </tbody>
    </table> 
    
    <script type="text/javascript"><!--

$(document).ready(function() {
    
    $("select[name='promos[promos_type]']").change();
    
});
//--></script> 