<style>
    ul.red_color>li:first-child>a{color: #F08080;}
    ul.green_color>li:first-child>a{color: #3eb489;}
    .dae-attribute-group{font-size: 10px;}
</style>
<?php if(!empty($_enable_attributes_group)){ ?>
<div class="row">
    <div class="col-xs-12">
        <label class="control-label"><span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_fsa_attribute_group_help;?>"><?php echo $dae_fsa_attribute_group;?></span></label>
    </div>
    <div class="col-xs-8 col-sm-8">
        <input type="text" name="attribute_group_name" id="fsa-attributes-group-name"  value="" placeholder="" class="form-control" autocomplete="off">
        <input type="hidden" name="attribute_group_id" id="fsa-attributes-group-id"  value="">
    </div>
    <div class="col-xs-4 col-sm-4">
        <button type="button" data-toggle="tooltip" title="<?php echo $dae_fsa_add_attributes_by_group_help; ?>" class="btn btn-primary" id="fsa-add-attributes-for-group"><i class="fa fa-plus"></i></button>
        <button type="button" data-toggle="tooltip" title="<?php echo $dae_fsa_view_attributes_by_group_help; ?>" class="btn btn-primary" id="fsa-view-attributes-for-group"><i class="fa fa-eye"></i></button>
    </div>
</div>
<?php } ?>
<div class="row" id="fsa-box-attributes-by-group">
</div>
<div class="row">
    <div class="col-xs-12">
        <label class="control-label"><span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_fsa_attributes_help;?>"><?php echo $dae_fsa_attributes;?></span></label>
    </div>
    <div class="col-xs-12">
        <input type="text" name="attribute_name" id="fsa-attribute-name"  value="" placeholder="" class="form-control" autocomplete="off">
        <div id="fsa-box-select-attributes" class="well well-sm" style="height: 400px; overflow: auto;margin-bottom: 0px;"></div>
    </div>
</div>
<div id="fsa-daeModalBox" class="modal fade"></div>
<script>
    /**
     * Фукнции для работы формы выбора атрибутов
     * fsa - form select attribute with group
     */
    var modelFormSelectAttributeWithGroup = function () {
        var self = this;
        self.ELEMENT_BOX_SELECT_ATTRIBUTES = '#fsa-box-select-attributes';
        self.ELEMENT_BOX_ATTRIBUTES_BY_GROUP = '#fsa-box-attributes-by-group';
        self.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME = '#fsa-attributes-group-name';
        self.ELEMENT_INPUT_ATTRIBUTES_GROUP_ID = '#fsa-attributes-group-id';
        self.ELEMENT_INPUT_ATTRIBUTE_NAME = '#fsa-attribute-name';

        self.attributesForGroup = []; //список атрибутов, полученный при выборе группы атрибутов
        self.selectAttributes = [];
        self.createNewAttribute = <?=(int)(!empty($_create_new_attribute)) ?>;
        self.createNewAttributeGroup = <?= (int)(!empty($_create_new_attribute_group)) ?>;
        self.addAttributeInBox = function(attribute_id, attribute_name, attribute_group) {
            //$(self.ELEMENT_BOX_SELECT_ATTRIBUTES + attribute_id).remove();
            attribute_id = Number(attribute_id);

            if($.inArray(attribute_id, self.selectAttributes) < 0){
                $(self.ELEMENT_BOX_SELECT_ATTRIBUTES).append(
                    '<div id="fsa-box-select-attributes' + attribute_id + '">'
                    + ' <i class="fa fa-minus-circle"></i> ' + attribute_group + ' > ' + attribute_name
                    + ' <input type="hidden" name="attributes_id[]" value="' + attribute_id + '" />'
                    + '</div>'
                );

                self.selectAttributes[self.selectAttributes.length] = attribute_id;
                //если выведен список атрибутов группы - обновим у них галочку
                $(self.ELEMENT_BOX_ATTRIBUTES_BY_GROUP+' #temp_attribute_id_'+ attribute_id).prop('checked', true);
            }
        };

        self.removeAttributeFromBox = function(attribute_id) {

            attribute_id = Number(attribute_id);
            var index_attribute_id = $.inArray(attribute_id, self.selectAttributes);
            if(index_attribute_id >= 0){
              delete self.selectAttributes[index_attribute_id];
            }
            $(self.ELEMENT_BOX_SELECT_ATTRIBUTES + attribute_id).remove();
            $(self.ELEMENT_BOX_ATTRIBUTES_BY_GROUP+' #temp_attribute_id_'+ attribute_id).prop('checked', false);
        };
        self.clearAttributeBox = function(){
            self.selectAttributes = [];
            $(self.ELEMENT_BOX_SELECT_ATTRIBUTES).html('');
        }
        /*
             Добавить все атрибуты группы
        */
        self.addAttributesByGroupInBox = function () {
            for (var key in self.attributesForGroup) {
                self.addAttributeInBox(self.attributesForGroup[key]['attribute_id'], self.attributesForGroup[key]['name'], self.attributesForGroup[key]['attribute_group']);
            }
            //отметим все чекбоксы, если было их отображение
            $(self.ELEMENT_BOX_ATTRIBUTES_BY_GROUP + ' input').each(function () {
                var attribute_id = $(this).val();
                if ($('div').is(self.ELEMENT_BOX_SELECT_ATTRIBUTES + attribute_id))
                    $(this).prop('checked', true);
                else
                    $(this).prop('checked', false);
            });
        };
        /*
             Отобразить атрибуты группы
        */
        self.viewAttributesByGroup = function () {
            $(self.ELEMENT_BOX_ATTRIBUTES_BY_GROUP).html('');
            var html = '';
            var checked = '';
            for (var key in self.attributesForGroup) {
                if ($('div').is(self.ELEMENT_BOX_SELECT_ATTRIBUTES + self.attributesForGroup[key]['attribute_id']))
                    checked = ' checked = "checked"';
                else
                    checked = '';
                html += ' <div class="col-sm-4">';
                //html += '   <div class="form-group">';
                html += '     <div class="checkbox">';
                html += '       <label>';
                html += '         <input type="checkbox" id="temp_attribute_id_'+self.attributesForGroup[key]['attribute_id']+'" name="temp_attribute_id[]" value="' + self.attributesForGroup[key]['attribute_id'] + '" ' + checked + ' data-attribute-group="' + self.attributesForGroup[key]['attribute_group'] + '" data-attribute-name="' + self.attributesForGroup[key]['name'] + '"/> ' + self.attributesForGroup[key]['name'];
                html += '       <label>';
                html += '     </div>';
                //html += '   </div>';
                html += ' </div>';
            }
            $(self.ELEMENT_BOX_ATTRIBUTES_BY_GROUP).html(html);
        };
        /*
             Изменение поля ввода группы атрибутов
        */
        self.changeInputAttributesGroupName = function() {
            if ($(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME).val() == '') {
                $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_ID).val(0);
                self.attributesForGroup = [];
                //очистка поля с чекбоксами атрибутов
                $(self.ELEMENT_BOX_ATTRIBUTES_BY_GROUP).html('');
            }
        };

        /*
         * Вызов формы для добавления нового атрибута
         */
        self.getFormAttribute = function(default_attribute_name){
            $.ajax({
              url: JS_URL_GET_FORM_ATTRIBUTE,
              type: 'GET',
              dataType: 'json',
              data: {
                  attribute_id:0,
                  default_name:default_attribute_name
              },
              success: function(json) {
                $('#fsa-daeModalBox').html(json.modal);
                $("#fsa-daeModalBox").modal('show');
              }
            });
        };
        self.init = function(){
            /*
             Автозавершение и выбор группы атрибутов
            */

            $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME).autocomplete({
                'source': function (request, response) {
                    $.ajax({
                        url: JS_URL_AUTOCOMPLETE_ATTRIBUTE_GROUP + '&filter_name=' + encodeURIComponent(request),
                        dataType: 'json',
                        success: function (json) {
                            response($.map(json, function (item) {
                                return {
                                    label: item['name'],
                                        value: item['attribute_group_id']
                                }
                            }));
                        }
                    });
                },
                'select': function (item) {
                    $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME).val(item['label']);
                    $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_ID).val(item['value']);
                            //загрузка привязанных атрибутов
                    $.ajax({
                        url: JS_URL_AUTOCOMPLETE_ATTRIBUTE,
                        data: {filter_attribute_group:item['value'],filter_name:''},
                        dataType: 'json',
                        success: function (json) {
                            self.attributesForGroup = json;
                        }
                    });
                }
            });
            /*
                     Автозавершение и выбор атрибутов
            */
            $(self.ELEMENT_INPUT_ATTRIBUTE_NAME).autocomplete({
                'source': function (request, response) {
                    $.ajax({
                        url: JS_URL_AUTOCOMPLETE_ATTRIBUTE,
                        data:{filter_attribute_group:$(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_ID).val(), filter_name:encodeURIComponent(request)},
                        dataType: 'json',
                        success: function (json) {
                            if ($.isEmptyObject(json)){
                                if(self.createNewAttribute){
                                    $(self.ELEMENT_INPUT_ATTRIBUTE_NAME).siblings('ul.dropdown-menu').addClass('red_color').removeClass('green_color');
                                    response([{label: "<i class='fa fa-plus-circle'></i> <?= $dae_fsa_new_attribute ?>",value:"0"}]);
                                }else{
                                response([]);
                                }
                            }else{
                                if(self.createNewAttribute){
                                    $(self.ELEMENT_INPUT_ATTRIBUTE_NAME).siblings('ul.dropdown-menu').addClass('green_color').removeClass('red_color');
                                    json.splice(0, 0,{attribute_group:"",name: "<i class='fa fa-plus-circle'></i> <?= $dae_fsa_new_attribute ?>",attribute_id:"-1"});
                                }
                                response($.map(json, function(item) {
                                    //if(object_dae.existsSelectAttribute(item.attribute_id) === false){
                                    return {
                                        category: item.attribute_group,
                                        label: item.name,
                                        value: item.attribute_id
                                    }
                                    //}
                                }));
                            }
                        }
                    });
                },
                'select': function (item) {
                    if(item['value'] <= 0){
                        //сохраним значение для передачи в форму
                        self.getFormAttribute($(this).val());
                    }else{
                        $(self.ELEMENT_INPUT_ATTRIBUTE_NAME).val('');
                        self.addAttributeInBox(item['value'], item['label'], item['category']);
                    }
                }
            });
        }
    }

    var formSelectAttributeWithGroup = new modelFormSelectAttributeWithGroup();
    $(document).ready(function(){

        formSelectAttributeWithGroup.init();
        /*
         Клик по кнопке "Добавить все атрибуты группы"
         */
        $('body').on('click', '#fsa-add-attributes-for-group', function () {
            formSelectAttributeWithGroup.addAttributesByGroupInBox();
        });

        /*
         Клик по кнопке "Отобразить атрибуты группы"
        */
        $('body').on('click', '#fsa-view-attributes-for-group', function () {
            formSelectAttributeWithGroup.viewAttributesByGroup();
        });

        /*
         Клик по чекбоксу с атрибутом - добавление атрибута в список выбранных
        */
        $('body').on('change', formSelectAttributeWithGroup.ELEMENT_BOX_ATTRIBUTES_BY_GROUP+' input', function () {

            if ($(this).prop("checked")) {
                formSelectAttributeWithGroup.addAttributeInBox($(this).val(),$(this).data('attribute-name'),$(this).data('attribute-group'));
            } else {
                formSelectAttributeWithGroup.removeAttributeFromBox($(this).val());
            }
        });

        /*
         Изменение поля ввода группы атрибутов
        */
        $(formSelectAttributeWithGroup.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME).change(function () {
            formSelectAttributeWithGroup.changeInputAttributesGroupName();
        });

        /*
        * Удаление списков
        */
        $('form').on('click', '.fa-minus-circle', function () {
            var attribute_id = $(this).siblings('input').val();
            //$(this).parent().remove();
            formSelectAttributeWithGroup.removeAttributeFromBox(attribute_id);
        });
    });
</script>
