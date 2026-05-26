<style>
  ul.red_color>li:first-child>a{color: #F08080;}
  ul.green_color>li:first-child>a{color: #3eb489;}
  .dae-attribute-group{font-size: 10px;}
</style>
<div id="daeModalBox" class="modal fade"></div>
<div class="dae-alert">
    <div class="alert alert-danger hidden"><i class="fa fa-exclamation-circle"></i>
        <span></span>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>

    <div class="alert alert-success hidden"><i class="fa fa-check-circle"></i>
        <span></span>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>

    <div class="alert alert-info hidden"><i class="fa fa-check-circle"></i>
        <span></span>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
</div>
<div class="row">
  <div  class="col-sm-10" id="dae-box-tab-button">

    <?php $status_button = (empty($settings['dae_product_tab']['view_button_add_attribute_group']))?'hidden':''; ?>
    <button type="button" id="dae_button_add_attribute_group" class="btn btn-primary dae-form-attribute-group <?= $status_button;?>" data-attribute_group_id="0" data-toggle="tooltip" title="" >
        <i class="fa fa-plus-circle"></i> <?= $dae_tap_button_add_attribute_group;?>
    </button>

    <?php $status_button = (empty($settings['dae_product_tab']['view_button_add_attribute']))?'hidden':''; ?>
    <button type="button" id="dae_button_add_attribute" onclick="focus_select_row=-1; daeViewFormAttribute();" data-toggle="tooltip"  title="" class="btn btn-primary <?php echo $status_button;?>" >
        <i class="fa fa-plus-circle"></i> <?= $dae_tap_button_add_attribute;?>
    </button>

    <?php $status_button = (empty($settings['dae_product_tab']['view_button_add_attribute_category']))?'hidden':''; ?>
    <button type="button" id="dae_button_add_attribute_category" onclick="daeViewFormLoadAttributeFromCategory();" data-toggle="tooltip" title="" class="btn btn-primary <?php echo $status_button;?>">
        <i class="fa fa-list-alt"></i> <?= $dae_tap_button_add_attribute_category;?>
    </button>

    <?php $status_button = (empty($settings['dae_product_tab']['view_button_add_attribute_list']))?'hidden':''; ?>
    <button type="button" id="dae_button_add_attribute_list" onclick="daeViewFormLoadAttributeFromList();" data-toggle="tooltip" title="" class="btn btn-primary <?php echo $status_button;?>" >
        <i class="fa fa-list"></i> <?= $dae_tap_button_add_attribute_list;?>
    </button>

    <?php $status_button = (empty($settings['dae_product_tab']['view_button_clear_attribute']))?'hidden':''; ?>
    <button type="button" id="dae_button_clear_attribute" data-toggle="tooltip" title="" class="btn btn-danger <?= $status_button;?>">
        <i class="fa fa-minus-circle"></i> <?= $dae_tap_button_clear_attribute;?>
    </button>


  </div>
  <div class="col-sm-2 text-right">
      <button type="button" class="btn btn-default dae-form-settings-local" data-group="tab_attribute_product" data-toggle="tooltip"><i class="fa fa-cog"></i></button>

  </div>

</div>
<br>
<div class="row">
  <div  class="col-sm-12">

<div class="table-responsive">

  <table id="dae_tab_attribute" class="table table-striped table-bordered table-hover">
    <thead>
      <tr>
        <td class="text-left"><?= $dae_tap_col_attribute; ?></td>
        <td class="text-left"><?= $dae_tap_col_attribute_value ?></td>
        <td></td>
      </tr>
    </thead>
    <tbody>
      <?php $attribute_row = 0; ?>
      <?php $devos_attributes_select = array();?>
      <?php foreach ($devos_attributes as $product_attribute_group) { ?>
        <?php foreach ($product_attribute_group['attributes'] as $product_attribute) { ?>
        <?php $attribute_id = $product_attribute['attribute_id'];?>
        <?php $devos_attributes_select[] = $attribute_id;//уже добавленные атрибуты, нужен для js ?>
      <tr id="attribute-row<?= $attribute_row; ?>" data-row="<?= $attribute_row; ?>">
        <td class="text-left td_attribute_name" style="width: 40%;">
          <span class="dae-attribute-group"><?php echo $product_attribute_group['name'];?></span>
          <input type="text" name="product_attribute[<?php echo $attribute_row; ?>][name]" value="<?php echo $product_attribute['name']; ?>" placeholder="<?php echo $entry_attribute; ?>" class="form-control" />
          <input type="hidden" name="product_attribute[<?php echo $attribute_row; ?>][attribute_id]" value="<?php echo $attribute_id; ?>" />
        </td>
        <td class="text-left td_attribute_value"><?php foreach ($languages as $language) { ?>
          <div class="input-group">
              <span class="input-group-addon">
                  <img src="<?= $language['path_image']; ?>" title="<?= $language['name']; ?>" />
              </span>

        <!-- begin_devos_attribute_ext -->

            <?php

              $type_edit = (isset($devos_attribute_settings[$attribute_id])&&isset($devos_attribute_settings[$attribute_id]['type_edit']))?$devos_attribute_settings[$attribute_id]['type_edit']:'autocomp';
              $style_textarea  = '';
              $rows_textarea = 5;
            ?>
            <?php
              #если установлен флаг мульти значения
              if($type_edit == 'multi'){
                #массив значений одного атрибута
                $dae_attribute_list_for_multiple = array();
                #если есть значение атрибута - превратим его в массив по разделителю
                if(isset($dae_product_attributes_value[$attribute_id][$language['language_id']])){
                  $dae_attribute_list_for_multiple = explode($settings['dae_value_separator'],$dae_product_attributes_value[$attribute_id][$language['language_id']]['text']);
                }
                //прячем текстареа
                $style_textarea  = ' style="display:none;" ';
                ?>

                <input type="text" name="multiple_attribute[<?php echo $attribute_row; ?>][<?php echo $language['language_id']; ?>]" value="" placeholder="" class="form-control multiple_attribute autocomplete" data-language_id ="<?php echo $language['language_id'];?>" data-row_id="<?php echo $attribute_row; ?>" autocomplete="off">
                <div id="dae-product-attribute-multiple-row<?php echo $attribute_row; ?>-<?php echo $language['language_id'];?>" class="well well-sm dae-product-attribute-multiple" style="height: 150px; overflow: auto;margin-bottom: 0px;">

                  <?php foreach($dae_attribute_list_for_multiple as $dae_attribute_for_multiple){ ?>
                  <?php
                    #найдем значение id значения атрибута в DAE
                    #$dae_attribute_for_multiple - массив c текущим значением атрибута, разделенное сепаратором
                    $dae_attribute_id_temp = 0;

                    if(!isset($devos_attributes_values[$attribute_id]['values'][$language['language_id']])){
                      continue;
                    }

                    foreach($devos_attributes_values[$attribute_id]['values'][$language['language_id']] as $devos_attribute_values ){
                      #если значение атрибута совпадает с предопределенным значением, сохраним ид этого значения
                      if(trim($dae_attribute_for_multiple) == trim($devos_attribute_values['text'])){
                        #id значения атрибута в модуле dae
                        $dae_attribute_id_temp = $devos_attribute_values['id'];
                        break;
                      }

                    }
                    #вывод текущих значений атрибута
                  ?>
                  <div id="dae-product-attribute-multiple-value-row<?php echo $attribute_row.'-'.$language['language_id'].'-'.$dae_attribute_id_temp; ?>">
                      <i class="fa fa-minus-circle"></i> <?php echo $dae_attribute_for_multiple; ?>
                    <input type="hidden" name="dae_product_attribute_multiple_value[<?php echo $attribute_id; ?>][<?php echo $language['language_id']; ?>]" value="<?php echo $dae_attribute_for_multiple; ?>" data-attribute_value_id="<?php echo $dae_attribute_id_temp;?>"/>
                  </div>
                  <?php } ?>
                </div>
            <?php } ?>
            <?php if($type_edit == 'autocomp'){ ?>
                <input type="text" name="product_attribute[<?php echo $attribute_row; ?>][product_attribute_description][<?php echo $language['language_id']; ?>][text]" value="<?php echo isset($dae_product_attributes_value[$attribute_id][$language['language_id']]) ? $dae_product_attributes_value[$attribute_id][$language['language_id']]['text'] : ''; ?>" placeholder="<?php echo $entry_text; ?>" class="form-control autocomplete" data-language_id="<?php echo $language['language_id'];?>"/>
            <?php }else if($type_edit == 'multi'){ ?>
                <input type="hidden" name="product_attribute[<?php echo $attribute_row; ?>][product_attribute_description][<?php echo $language['language_id']; ?>][text]" value="<?php echo isset($dae_product_attributes_value[$attribute_id][$language['language_id']]) ? $dae_product_attributes_value[$attribute_id][$language['language_id']]['text'] : ''; ?>" placeholder="<?php echo $entry_text; ?>" class="form-control" data-language_id="<?php echo $language['language_id'];?>"/>
            <?php } else { ?>
              <textarea <?php echo $style_textarea; ?> name="product_attribute[<?php echo $attribute_row; ?>][product_attribute_description][<?php echo $language['language_id']; ?>][text]" rows="<?php echo $rows_textarea; ?>" placeholder="<?php echo $entry_text; ?>" class="form-control"><?php echo isset($dae_product_attributes_value[$attribute_id][$language['language_id']]) ? $dae_product_attributes_value[$attribute_id][$language['language_id']]['text'] : ''; ?></textarea>
            <?php } ?>
          </div>
          <?php } ?></td>
        <td class="text-left"><button type="button" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger remove-attribute-row"><i class="fa fa-minus-circle"></i></button>
        <!-- end_devos_attribute_ext -->
        </td>
      </tr>
      <?php $attribute_row++; ?>
      <?php } //foreach attribute ?>
      <?php } //foreach attribute-group ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2"></td>
        <td class="text-left"><button type="button" onclick="addEmptyAttribute();" data-toggle="tooltip" title="<?php echo $button_attribute_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
      </tr>
    </tfoot>
  </table>
</div>
</div>
</div>
<script type="text/javascript">

    /*<?php foreach($urls_by_js as $js_url_name => $js_url){ ?> */
        var <?= $js_url_name ?> = '<?= $js_url ?>';

    /*<?php } ?>*/
    var attribute_row = '<?= $attribute_row; ?>';
    var attribute_multiple_separator = '<?= $settings["dae_value_separator"]; ?>';
    var dae_lang_text_attr_val = '<?= $dae_tap_attribute_values;?>';
    var dae_auto_load_attr_for_cat = Number('<?= (isset($settings["dae_auto_load_attr_for_cat"])?$settings["dae_auto_load_attr_for_cat"]:0);?>');
    var focus_field_language_id;//язык выбранного поля

</script>
<script type="text/javascript">
    var lastAttributeValues = {};//будет использовать как временное хранилище значений при avtocomplete, чтобы автоматически подставлять значение для другого языка
    var languages = JSON.parse('<?= json_encode($languages)?>');
    var dae_settings = JSON.parse('<?= json_encode($settings)?>');
    var dae_row_number=0;//номер строки, изменяется при клике на некоторые кнопки

    var tmp_not_exists_attribute_value = '';//заполняется значением отсутствующего значения атрибута, для передачи в форму
    var tmp_not_exists_attribute_name = '';//заполняется названием отсутствующего атрибута для дальнейшей передачи в форму
    var tmp_focus_input_attribute_value = '';//
    var focus_select_row = -1;//текущая строка


    function DAE_Attribute(attribute_id, value, settings){
      return {
        attribute_id: attribute_id,
        default_value: value,
        settings: settings
      }
    }

    function DAE(){
        var self = this;
        self.attributes = [];
        self.attributes_select = [];//[<?php echo implode(',',$devos_attributes_select); ?>];
        /*<?php foreach($devos_attributes_select as $value){ ?>*/
            self.attributes_select.push(Number('<?= $value ?>'));
        /*<?php } ?>*/

        self.categories_attributes = [];//<?php echo json_encode($devos_categories_attributes); ?>;
        self.loadAttribute = function(attribute_id, callback){
        if(attribute_id in self.attributes){
          return true;
        }else{
          $.ajax({
            url: JS_URL_GET_FULL_INFO_ATTRIBUTE,
            type: 'GET',
            dataType: 'json',
            data: {attribute_id: attribute_id, default_value: 1},
            success: function(json) {
                if(json.hasOwnProperty('status') && json.status == DAE_STATUS_SUCCESS) {
                    var attribute_id = Number(json.attribute_id);
                    self.setAttribute(attribute_id);
                    self.setAttributeSettings(attribute_id, json.devos_attribute_settings[attribute_id]);
                    self.setAttributeDefaultValue(attribute_id, json.devos_attributes_values[attribute_id]);
                    callback();
                } else {
                    layoutAlert.viewDanger(json.message);
                }
            }
          });
        }
        return false;
      };
      self.emptyAttribute = function(attribute_id){
        attribute_id = Number(attribute_id);
        self.attributes[attribute_id] = {
          attribute_id: attribute_id
        };
        self.setAttributeSettings(attribute_id, []);
        self.setAttributeDefaultValue(attribute_id, []);
      };
      self.getAttribute = function(attribute_id){
        attribute_id = Number(attribute_id);
        if(attribute_id in self.attributes){
          return self.attributes[attribute_id];
        }
      }
      self.setAttribute = function(attribute_id){
        attribute_id = Number(attribute_id);
        self.attributes[attribute_id] = {
          attribute_id: attribute_id
        };
      };
      self.getDefaultSettings = function(){
        return {
            type_edit: "autocomp"
          };
      }
      self.getAttributeSettings = function(attribute_id){

        attribute_id = Number(attribute_id);
        if(attribute_id in self.attributes && !!self.attributes[attribute_id].settings){
          return self.attributes[attribute_id].settings;
        }else{
          return self.getDefaultSettings();
        }
      };
      self.setAttributeSettings = function(attribute_id, settings){

        attribute_id = Number(attribute_id);
        if(!(attribute_id in self.attributes)){
          self.emptyAttribute(attribute_id);
        }
        if(!settings){
          settings = self.getDefaultSettings();
        }
        self.attributes[attribute_id].settings = settings;
      };

      //методы управления значениями
      self.getAttributeValue = function(attribute_id, language_id){
        attribute_id = Number(attribute_id);
        if(attribute_id in self.attributes
          && language_id in self.attributes[attribute_id].default_value){
          return self.attributes[attribute_id].default_value[language_id];
        }else{
          return [];
        }
      };
      self.setAttributeDefaultValue = function(attribute_id, value){
        if(!!!value) value = [];
        if('values' in value)
          value = value['values'];
        self.attributes[attribute_id].default_value = value;
      };
      //методы управления выбранными атрибутами
      self.setSelectAttribute = function (attribute_id){
        attribute_id = Number(attribute_id);
        if($.inArray(attribute_id, self.attributes_select) < 0){
          self.attributes_select[self.attributes_select.length] = attribute_id;
        }
      };
      self.existsSelectAttribute = function (attribute_id){
        attribute_id = Number(attribute_id);
        if($.inArray(attribute_id, self.attributes_select) >= 0){
          return true;
        }else
          return false;
      };
      self.removeSelectAttribute = function (attribute_id){
        attribute_id = Number(attribute_id);
        var index_attribute_id = $.inArray(attribute_id, self.attributes_select);
        if(index_attribute_id >= 0){
          delete self.attributes_select[index_attribute_id];
        }
      };
      self.removeAllSelectAttribute = function (){
        self.attributes_select = [];
      };

    }
    var object_dae = new DAE();


    //получение инпута с именем атрибута
    function getFieldAttributeName(row){
      return $('input[name=\'product_attribute[' + row + '][name]\']');
    }
    //получение инпута с ид атрибута
    function getFieldAttributeId(row){
        return $('input[name=\'product_attribute[' + row + '][attribute_id]\']');
    }
    function getFieldAttributeValue(row, language_id){
        return $('input[name=\'product_attribute['+row+'][product_attribute_description]['+language_id+'][text]\']');
    }
    function setAttributeValueInField(row, language_id, attribute_value){
        //attribute_value - значение атрибута,например {text: '', id:''}

        var field = getFieldAttributeValue(row, language_id);

        if(field.length && attribute_value.hasOwnProperty('text')){
            var value = attribute_value.text;

            /*var value_default_str='';
                                    for(var value_id in values){
                                      if(values[value_id]['default'] == '1'){
                                        value_default.push({"text": values[value_id]['text'], 'id': value_id});
                                        //формирование значения для текстареа
                                        if(value_default_str.length > 0){
                                          value_default_str = value_default_str + attribute_multiple_separator;
                                        }
                                        value_default_str = value_default_str + values[value_id]['text'];
                                        if(type_edit != 'multi')
                                          break;
                                      }
                                    }*/


            //если у инпута нет класса autocomplete, то значит это множественное значение, так как там этот класс у другого инпута

        if(!field.hasClass('autocomplete')){
                $('.multiple_attribute').val('');
                        //if(!$('div').is('#dae-product-attribute-multiple-value-row' + row_id + '-' + item['value'])){

                $('#dae-product-attribute-multiple-value-row' + row + '-' + language_id + '-' + attribute_value.id).remove();
                $('#dae-product-attribute-multiple-row'+row+'-'+language_id).append(
                    '<div id="dae-product-attribute-multiple-value-row' + row + '-' + language_id + '-' + attribute_value.id + '">'
                    +'<i class="fa fa-minus-circle"></i> '
                    + attribute_value.text
                    + '<input type="hidden" name="dae_product_attribute_multiple_value[]" value="" data-attribute_value_id="' + attribute_value.id + '"/>'
                    +'</div>'
                );
                $('#dae-product-attribute-multiple-value-row' + row + '-' + language_id + '-' + attribute_value.id + ' input').val(attribute_value.text);
                var value = '';
                $('#dae-product-attribute-multiple-row'+row+'-'+language_id+' input').each(function(){
                    var this_value = $(this).val();
                    if(value.length > 0){
                      value = value + attribute_multiple_separator;
                    }
                    value = value + this_value;
                });
            }
            field.val(value);
        }
    }

    function setAttributeGroupInForm(row, attribute_group){
      $('#attribute-row'+row+' > td > span.dae-attribute-group').html(attribute_group);
    }


  //добавление атрибутов для категории
  /*function addAttributeForCategory(category_id){
    if(category_id > 0){
      for(var devos_categories_attribute in devos_categories_attributes[category_id]['attributes']){
        attribute_name = devos_categories_attributes[category_id]['attributes'][devos_categories_attribute]['name'];
        attribute_id = devos_categories_attributes[category_id]['attributes'][devos_categories_attribute]['attribute_id'];
        attribute_group = devos_categories_attributes[category_id]['attributes'][devos_categories_attribute]['attribute_group_name'];
        dae_addAttribute(attribute_name,attribute_id,attribute_group);
      }
    }
  }*/

  function daeRemoveAttributeRow(rowIndex){
    var attribute_id = getFieldAttributeId(rowIndex).val();
    object_dae.removeSelectAttribute(attribute_id);
    $('#attribute-row'+rowIndex).remove();
  }

  //добавить атрибуты из категории через окно
  function daeViewFormLoadAttributeFromCategory(){
    $.ajax({
      url: JS_URL_GET_FORM_LOAD_ATTRIBUTE_FROM_CATEGORY,
      type: 'GET',
      dataType: 'json',
        data: $('input[name="product_category[]"]').serialize(),
      success: function(json) {
        layoutAlert.handlerByResponse(json);
        if(json.status == DAE_STATUS_SUCCESS){
            daeModal.init(json.modal);
        }
      }
    });
  }
  //добавить атрибуты из категории автоматически
  document.addEventListener(DAE_EVENT_ADD_CATEGORY_TO_PRODUCT, function(e) {
      console.log(dae_auto_load_attr_for_cat);
    if(dae_auto_load_attr_for_cat){
        daeLoadAttributeFromCategory(e.detail.category_id, false, 1);//атрибуты будут добавлены
    }
  });

  function daeLoadAttributeFromCategory(category_id, replace_attaributes, default_value){

    //if(dae_auto_load_attr_for_cat){
      /* if(category_id in devos_categories_attributes){
        addAttributeForCategory(category_id);
      }else{ */
        $.ajax({
            url: JS_URL_GET_RELATION_ATTRIBUTES_BY_CATEGORY,
            type: 'GET',
            dataType: 'json',
            data: {category_id: category_id, default_value: 1},
            success: function(json) {
                layoutAlert.handlerByResponse(json);
                if(json.status == DAE_STATUS_SUCCESS){
                    for(var category_id in json.dae_categories_attributes){
                      //список ид атрибутов для определенной категории
                      var attributes = json.dae_categories_attributes[category_id].attributes;
                        //если стоит флаг полной замены, то удалим атрибуты, которые не принадлежат категории
                        if(replace_attaributes || default_value){
                            $('#dae_tab_attribute tbody tr').each(function(){
                                var numberRow = $(this).data('row');
                                var attributeIdInRow = getFieldAttributeId(numberRow).val();
                                //принудительная замена атрибутов
                                if(replace_attaributes){
                                    var index = $.inArray(attributeIdInRow, attributes);
                                    if(index == -1){
                                      daeRemoveAttributeRow(numberRow);
                                    }
                                }
                                value_default = {};
                                for(var language_index in languages){
                                    var language = languages[language_index];
                                    var values = object_dae.getAttributeValue(attributeIdInRow, language.language_id);
                                    var value_default_str='';
                                    for(var value_id in values){
                                      if(values[value_id]['default'] == '1'){
                                        value_default = {text: values[value_id]['text'], id: value_id};
                                        break;
                                      }
                                    }
                                    var attributeValue = getFieldAttributeValue(numberRow, language.language_id).val();
                                    //установим значение по умолчанию уже добавленным атрибутам у которых оно пусто
                                    if((default_value == 2 && attributeValue=='') || default_value == 3){
                                        setAttributeValueInField(numberRow, language.language_id, value_default);
                                    }
                                }
                            });
                        }

                      for (var i in attributes) {
                        var attribute_id = Number(attributes[i]['attribute_id']);
                        //добавим в объект DAE
                        object_dae.setAttribute(attribute_id);
                        object_dae.setAttributeSettings(attribute_id, json.dae_attributes_settings[attribute_id]);
                        object_dae.setAttributeDefaultValue(attribute_id, json.dae_attributes_values[attribute_id]);
                        //теперь добавим к товару
                        dae_addAttribute(attributes[i]['name'], attribute_id, attributes[i]['attribute_group_name']);
                      }
                    }
                }
            }
        });
      //}
    //}
  }
  //добавить атрибуты из списка групп
  function daeViewFormLoadAttributeFromList(){
    $.ajax({
      url: JS_URL_GET_FORM_LOAD_ATTRIBUTE_FROM_LIST,
      type: 'GET',
      dataType: 'json',
      success: function(json) {
        layoutAlert.handlerByResponse(json);
        if(json.status == DAE_STATUS_SUCCESS){
            daeModal.init(json.modal);
        }
      }
    });
  }

    //удаление строки атрибута
    $('body').on('click', '.remove-attribute-row', function(){
        $(this).tooltip('destroy');
        var rowIndex = $(this).closest('tr').data('row');
        daeRemoveAttributeRow(rowIndex);
    });
    //удаление всех атрибутов
    $('body').on('click','#dae_button_clear_attribute',function(){
        if (confirm("Очистить список атрибутов?")){
          $('#dae_tab_attribute tbody tr').remove();
          object_dae.removeAllSelectAttribute();
        }
    });
    //добавление по клику атрибута из списка
    $('body').on('click','span.dae_attribute_one',function(){
        var attribute_id = $(this).data('dae-attribute-one');
        var attribute_name = $(this).text();
        var attribute_group = $(this).data('dae-attribute-group');
        dae_addAttribute(attribute_name,attribute_id, attribute_group);
        modalAlert.viewSuccess('<?php echo $success_add_attributes;?>');
    });
    //добавление всех атрибутов группы
    $('body').on('click','span.dae_attribute_group_one',function(){
      $(this).closest('tr').find('td:eq(1) span.dae_attribute_one').each(function(){
        var attribute_id = $(this).data('dae-attribute-one');
        var attribute_name = $(this).text();
        var attribute_group = $(this).data('dae-attribute-group');
        dae_addAttribute(attribute_name,attribute_id, attribute_group);
      });
      modalAlert.viewSuccess('<?php echo $success_add_attributes;?>');
    });

    //локальные настройки
    document.addEventListener(DAE_EVENT_CHANGE_SETTINGS_LOCAL, function(e) {
    
        var change_settings_product_tab = e.detail.change_settings.dae_product_tab;
        
        //на основании формы - покажем или спрячем кнопки
        if(change_settings_product_tab !== undefined) {
          var buttons = ["button_add_attribute_group", "button_add_attribute", "button_add_attribute_category", "button_add_attribute_list", "button_clear_attribute"];
          for (var i=0; i<buttons.length; i++){
            if(change_settings_product_tab.hasOwnProperty('view_'+buttons[i]) && change_settings_product_tab['view_'+buttons[i]])
              $('#dae_'+buttons[i]).removeClass('hidden');
            else
              $('#dae_'+buttons[i]).addClass('hidden');
          }
        }
    });

    //добавление строки для атрибута и заполнение ее данными
    function dae_addAttribute(attribute_name, attribute_id, attribute_group){
      if(object_dae.existsSelectAttribute(attribute_id)){
        return false;
      }
      addEmptyAttribute();
      getFieldAttributeName((attribute_row-1)).val(attribute_name);
      getFieldAttributeId((attribute_row-1)).val(attribute_id);
      setAttributeGroupInForm((attribute_row-1), attribute_group);
      loadAttributeValue(attribute_id, (attribute_row-1));
    }

    //автоподстановка атрибутов
    function dae_attributeAutocomplete(attribute_row) {
      getFieldAttributeName(attribute_row).autocomplete({
        'source': function(request, response) {
          $.ajax({
            url: JS_URL_AUTOCOMPLETE_ATTRIBUTE,
            dataType: 'json',
            data:{filter_name:encodeURIComponent(request)},
            success: function(json) {
              if($.isEmptyObject(json)){
                $(getFieldAttributeName(attribute_row)).siblings('ul.dropdown-menu').addClass('red_color').removeClass('green_color');
                response([{label: "<i class='fa fa-plus-circle'></i> <?php echo $dae_tap_new_attribute;?>",value:"0"}]);
              }else{
                $(getFieldAttributeName(attribute_row)).siblings('ul.dropdown-menu').addClass('green_color').removeClass('red_color');
                json.splice(0, 0,{attribute_group:"",name: "<i class='fa fa-plus-circle'></i> <?php echo $dae_tap_new_attribute;?>",attribute_id:"-1"});
                response($.map(json, function(item) {
                  if(object_dae.existsSelectAttribute(item.attribute_id) === false){
                    return {
                      category: item.attribute_group,
                      label: item.name,
                      value: item.attribute_id
                    }
                  }
                }));
              }
            }
          });
        },
        'select': function(item) {
        //текущая строка
            focus_select_row = attribute_row;
            if(item['value'] == 0){
            //ищем кнопку для запуска значений атрибута
                daeViewFormAttribute(0, 0, $(this).val());
            }else if(item['value'] < 0){
                //открытие формы с пустым названием атрибута
                daeViewFormAttribute(0, 0, '');
            }else{
                //если смена атрибута, удалим предыдущий из выбранных
                var tmp_attribute_id = getFieldAttributeId(attribute_row).val();
                object_dae.removeSelectAttribute(tmp_attribute_id);

                getFieldAttributeName(attribute_row).val(item['label']);
                getFieldAttributeId(attribute_row).val(item['value']);
                setAttributeGroupInForm(attribute_row, item['category']);
                loadAttributeValue(item['value'], attribute_row);
            }
        }
      });
    }
    //сохраним элемент со значением атрибута при получении фокуса - нужно для подсветки
    $('body').on('focus', 'td.td_attribute_value input', function(){
      tmp_focus_input_attribute_value = $(this);
    });

    //автозавершение значений атрибута
    function dae_attributeValueAutocomplete(attribute_row) {
        $('tr#attribute-row'+attribute_row+' td.td_attribute_value input.autocomplete').autocomplete({
            'source': function(request, response) {
                focus_field_language_id = $(this).data("language_id");
                var select_attribute_value_id = [];//чтобы выборка был из невыбранных элементов
                $('#dae-product-attribute-multiple-row'+row_id+'-'+focus_field_language_id+' input').each(function(){
                    select_attribute_value_id.push($(this).data('attribute_value_id'));
                });


                var attribute_id = getFieldAttributeId(attribute_row).val();
                var categories_id = [];

                var row_id = $(this).closest('tr').data('row');

                if(dae_settings.hasOwnProperty('dae_value_category') && dae_settings.dae_value_category){
                    $('input[name="product_category[]"]').each(function(){
                      categories_id.push($(this).val());
                    });
                }
                $.ajax({
                  url: JS_URL_AUTOCOMPLETE_ATTRIBUTE_VALUE,
                  dataType: 'json',
                  data:{
                      text:encodeURIComponent(request),
                      attribute_id:attribute_id,
                      select_attribute_value_id:select_attribute_value_id,
                      categories_id: categories_id
                  },
                  success: function(json) {
                    if((json.status != DAE_STATUS_SUCCESS) || $.isEmptyObject(json.attribute_values)){
                      if(tmp_focus_input_attribute_value)
                        tmp_focus_input_attribute_value.siblings('ul.dropdown-menu').addClass('red_color').removeClass('green_color');
                      response([{label: "<i class='fa fa-plus-circle'></i> <?= $dae_tap_new_value ?>",value:"0"}]);

                    }else{
                        if(tmp_focus_input_attribute_value)
                            tmp_focus_input_attribute_value.siblings('ul.dropdown-menu').addClass('green_color').removeClass('red_color');
                        //сохраним полученные значения
                        lastAttributeValues = json.attribute_values;

                        var respose_attribute_values = $.map(json.attribute_values, function(item) {
                          return {
                            label: item.description[focus_field_language_id].text,
                            value: item.attribute_value_id
                          }
                        });

                        respose_attribute_values.splice(0, 0,{
                            category:"",
                            label: "<i class='fa fa-plus-circle'></i> <?= $dae_tap_new_value ?>",
                            value:"-1"
                        });

                        response(respose_attribute_values);
                    }
                  }
                });
            },
            'select': function(item) {
                //сохраним значение для передачи в форму
                focus_select_row = attribute_row;
                var default_attribute_value = $(this).val();
                var attribute_id = getFieldAttributeId(focus_select_row).val();
                //var language_id = $(this).data("language_id");
                var row_id = $(this).closest('tr').data('row');

                if(item['value'] == 0){
                    //ищем кнопку для запуска значений атрибута
                    daeViewFormAttributeValue(attribute_id, 0, default_attribute_value);
                    return;
                }
                if(item['value'] < 0){
                    //открытие формы с пустым названием атрибута
                    daeViewFormAttributeValue(attribute_id, 0, '');
                    return;
                }
                for(var language_index in languages){
                    var language = languages[language_index];

                    if(lastAttributeValues.hasOwnProperty(item['value'])){
                        var text = lastAttributeValues[item['value']].description[language.language_id].text;
                        setAttributeValueInField(row_id, language.language_id, {text: text, id: item['value']});
                    }

                }
            }
        });
    }
    //обработка события создания нового значения атрибута
    document.addEventListener(DAE_EVENT_CREATE_ATTRIBUTE_VALUE, function(e) {
        var attribute_value = e.detail.attribute_value;
        for (var language_id in attribute_value.description){ //выведем на экран все элементы
            value = attribute_value.description[language_id]['text'];
            setAttributeValueInField(focus_select_row, language_id, {text: attribute_value.description[language_id]['text'], id: attribute_value.id});
        }
    });

  //добавление пустой строки для атрибута
function addEmptyAttribute() {
    html  = '<tr id="attribute-row' + attribute_row + '" data-row="' + attribute_row + '">';
    html += '  <td class="text-left" style="width: 20%;">';
    html += '    <span class="dae-attribute-group"></span>';
    html += '    <input type="text" name="product_attribute[' + attribute_row + '][name]" value="" placeholder="<?= $entry_attribute; ?>" class="form-control" />';
    html += '    <input type="hidden" name="product_attribute[' + attribute_row + '][attribute_id]" value="" />';
    html += '  </td>';
    html += '  <td class="text-left td_attribute_value">';
    for(var language_index in languages){
        var language = languages[language_index];
        html += '<div class="input-group">';
        html += '  <span class="input-group-addon">';
        html += '    <img src="'+language.path_image+'" title="'+language.name+'" />';
        html += '  </span>';
        html += '  <textarea name="product_attribute[' + attribute_row + '][product_attribute_description]['+language.language_id+'][text]" rows="5" placeholder="<?= $entry_text; ?>" class="form-control"></textarea>';
        html += '</div>';
    }
    html += '  </td>';
    html += '  <td class="text-left">';
    html += '    <button type="button" data-toggle="tooltip" title="<?= $button_remove; ?>" class="btn btn-danger remove-attribute-row">';
    html += '      <i class="fa fa-minus-circle"></i>';
    html += '    </button>';
    html += '  </td>';
    html += '</tr>';

    $('#dae_tab_attribute tbody').append(html);
    dae_attributeAutocomplete(attribute_row);
    attribute_row++;
}

    //формирование блока ввода значений
    function loadAttributeValue(dae_attribute_id, dae_attribute_row){
        //если параметры атрибуты еще не загружены - подгрузим их, а потом выполним функцию
        if(!object_dae.loadAttribute(dae_attribute_id, function(){
            loadAttributeValue(dae_attribute_id,dae_attribute_row);
        })){
            return false;
        }
        //пометим атрибут как выбранный
        object_dae.setSelectAttribute(dae_attribute_id);
        var html = '';
  //добавим кнопку изменения списка значений атрибута
  //html = ' <button type="button" class="btn btn-primary dae-button-form-attribute-value" id="dae_button_form_attribute_value'+dae_attribute_row+'" title="" data-toggle="tooltip"  data-original-title="'+dae_lang_text_attr_val+'"><i class="fa fa-list"></i></button>';
  //html += '<input type="hidden" name="dae_row_number[]" value="'+dae_attribute_row+'" id="dae-row-number'+dae_attribute_row+'">';
  //если нет кнопки - добавим кнопку и инпут с номером строки
  /*if(!$('#tab-attribute button').is('#dae_button_form_attribute_value'+dae_attribute_row)){
    $('tr#attribute-row'+dae_attribute_row + ' td:eq(2) > button').after(html);
  }*/
        html = '';
        var attribute_value ='';
        var type_edit = 'autocomp';
        var style_textarea = '';

        //проверка настроек атрибута
        settings = object_dae.getAttributeSettings(dae_attribute_id);
        if('type_edit' in settings){
            type_edit = settings['type_edit'];
        }


  for(var language_index in languages){
        var language = languages[language_index];
    var value_default = [];
    var value_default_str ='';
    //определим значение по умолчанию
    var values = object_dae.getAttributeValue(dae_attribute_id, language.language_id);
    for(var value_id in values){
      if(values[value_id]['default'] == '1'){
        value_default.push({"text": values[value_id]['text'], 'id': value_id});
        //формирование значения для текстареа
        if(value_default_str.length > 0){
          value_default_str = value_default_str + attribute_multiple_separator;
        }
        value_default_str = value_default_str + values[value_id]['text'];
        if(type_edit != 'multi')
          break;
      }
    }

    html += '<div class="input-group">';
    html += '  <span class="input-group-addon">';
    html += '    <img src="'+language.path_image+'" title="'+language.name+'" />';
    html += '  </span>';
    if(type_edit == 'multi'){
        style_textarea = 'style="display: none;"';        
        html +='<input type="text" name="multiple_attribute[' + dae_attribute_row + ']['+language.language_id+']" value="" placeholder=""   class="form-control multiple_attribute autocomplete" data-language_id ="'+language.language_id+'" data-row_id="'+ dae_attribute_row +'"   autocomplete="off">';
        html += '<div id="dae-product-attribute-multiple-row' + dae_attribute_row + '-'+language.language_id+'" class="well well-sm   dae-product-attribute-multiple" style="height: 150px; overflow: auto;margin-bottom: 0px;">';
        for(var value_key in value_default){
          html += '<div id="dae-product-attribute-multiple-value-row' + dae_attribute_row + '-'+language.language_id+'-'+value_default[value_key].id+'">';
          html += ' <i class="fa fa-minus-circle"></i> '+value_default[value_key].text;
          html += ' <input type="hidden" name="dae_product_attribute_multiple_value['+dae_attribute_id+']['+language.language_id+']" value="'+value_default[value_key].text+'" data-attribute_value_id="'+value_default[value_key].id+'"/>';
          html += '</div>';
        }
        html += '</div>';

    }
    if(type_edit == 'autocomp'){
        html +='<input '+style_textarea+' type="text" name="product_attribute[' + dae_attribute_row + '][product_attribute_description]['+language.language_id+'][text]" value="'+value_default_str+'" placeholder="<?= $entry_text; ?>" class="form-control autocomplete" data-language_id="'+language.language_id+'"/>';
    }else if(type_edit == 'multi'){
        html +='<input type="hidden" name="product_attribute[' + dae_attribute_row + '][product_attribute_description]['+language.language_id+'][text]" value="'+value_default_str+'" placeholder="<?= $entry_text; ?>" class="form-control" data-language_id="'+language.language_id+'"/>';
    }else{
        html += '<textarea name="product_attribute[' + dae_attribute_row + '][product_attribute_description]['+language.language_id+'][text]" rows="5" placeholder="<?= $entry_text; ?>" class="form-control">'+value_default_str+'</textarea>';
    }
    html += '</div>';
    }
  $('tr#attribute-row'+dae_attribute_row+' td.td_attribute_value').html(html);
  dae_attributeValueAutocomplete(dae_attribute_row);

}



$('#tab-attribute').delegate('.dae-product-attribute-multiple .fa-minus-circle', 'click', function() {
  var ob = $(this).parent().parent();
  $(this).parent().remove();
  var textarea_text = '';
    $(ob).find('input').each(function(){
      var this_value = $(this).val();
      if(textarea_text.length > 0){
        textarea_text = textarea_text + attribute_multiple_separator;
      }
      textarea_text = textarea_text + this_value;
    });
  $(ob).siblings('textarea').val(textarea_text);

});

    $('#dae_tab_attribute tbody tr').each(function(index, element) {
        dae_attributeAutocomplete(index);
        dae_attributeValueAutocomplete(index);
    });
    //дейсвтия после добавления атрибута
    document.addEventListener(DAE_EVENT_CREATE_ATTRIBUTE, function(e) {
        var attribute = e.detail.attribute;
        if(focus_select_row > -1){
            getFieldAttributeName(focus_select_row).val(attribute.name);
            object_dae.removeSelectAttribute(getFieldAttributeId(focus_select_row).val());
            getFieldAttributeId(focus_select_row).val(attribute.attribute_id);
            object_dae.setSelectAttribute(attribute.attribute_id);
            loadAttributeValue(attribute.attribute_id, focus_select_row);
        }
    });
</script>