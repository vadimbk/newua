<style>
    ul.red_color>li:first-child>a{color: #F08080;}
    ul.green_color>li:first-child>a{color: #3eb489;}
    .dae-attribute-group{font-size: 10px;}
</style>
<?php
if(!isset($_placeholder_attribute_name)){
    $_placeholder_attribute_name = '';
}
if(!isset($_is_view_label)){
    $_is_view_label = true;
}
if(!isset($_unique_attributes)){
    $_unique_attributes = false;
}

?>
<div class="row">
    <?php if($_is_view_label){ ?>
    <div class="col-xs-12">
        <label class="control-label"><?php echo $dae_fsas_attribute;?></label>
    </div>
    <?php } ?>
    <div class="col-xs-12">
        <div class="input-group">
            <input type="text" name="attribute_name" id="fsas-attribute-name"  value="" placeholder="<?= $_placeholder_attribute_name ?>" class="form-control" autocomplete="off">
            <input type="hidden" name="attribute_id" id="fsas-attribute-id"  value="" placeholder="" class="form-control" autocomplete="off">
            <span class="input-group-btn reset-attribute">
                <button type="button" class="btn btn-default"><i class="fa fa-close"></i></button>
            </span>
        </div>
    </div>
</div>

<script>
    /**
     * Фукнции для работы формы выбора атрибута
     * fsas - form select attribute single
     */
    var modelFormSelectAttributeSingle = function () {
        var self = this;

        self.ELEMENT_INPUT_ATTRIBUTE_ID = '#fsas-attribute-id';
        self.ELEMENT_INPUT_ATTRIBUTE_NAME = '#fsas-attribute-name';
        self.ELEMENT_BUTTON_RESET_ATTRIBUTE = '.reset-attribute';

        self.createNewAttribute = <?=(int)(!empty($_create_new_attribute)) ?>;

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
                $('#daeModalBox').html(json.modal);
                $("#daeModalBox").modal('show');
              }
            });
        };
        self.resetAttribute = function(){
            daeEvent.dispatch(DAE_EVENT_RESET_SINGLE_ATTRIBUTE, {
                attribute_id: $(self.ELEMENT_INPUT_ATTRIBUTE_ID).val(),
                attribute_name: $(self.ELEMENT_INPUT_ATTRIBUTE_NAME).val()
                });
            $(self.ELEMENT_INPUT_ATTRIBUTE_ID).val(0);
            $(self.ELEMENT_INPUT_ATTRIBUTE_NAME).val('');
        }
        self.init = function(){
            $(self.ELEMENT_BUTTON_RESET_ATTRIBUTE).click(function(){
                self.resetAttribute();
            });
            /*
                     Автозавершение и выбор атрибутов
            */
            $(self.ELEMENT_INPUT_ATTRIBUTE_NAME).autocomplete({
                'source': function (request, response) {
                    $.ajax({
                        url: JS_URL_AUTOCOMPLETE_ATTRIBUTE,
                        data:{filter_attribute_group:$(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_ID).val(), filter_name:encodeURIComponent(request), unique_attributes:<?= (int)$_unique_attributes ?>},
                        dataType: 'json',
                        success: function (json) {
                            if ($.isEmptyObject(json)){
                                if(self.createNewAttribute){
                                    $(self.ELEMENT_INPUT_ATTRIBUTE_NAME).siblings('ul.dropdown-menu').addClass('red_color').removeClass('green_color');
                                    response([{label: "<i class='fa fa-plus-circle'></i> <?= $dae_fsas_new_attribute;?>",value:"0"}]);
                                }else{
                                    response([]);
                                }
                            }else{

                                var prepareListAttributes = $.map(json, function(item) {
                                    return {
                                        <?php if(!$_unique_attributes){ ?> category: item.attribute_group,<?php } ?>
                                        label: item.name,
                                        value: <?=((!$_unique_attributes)?'item.attribute_id':'item.name')?>
                                    }
                                });
                                if(self.createNewAttribute){
                                    $(self.ELEMENT_INPUT_ATTRIBUTE_NAME)
                                        .siblings('ul.dropdown-menu')
                                        .addClass('green_color')
                                        .removeClass('red_color');
                                    prepareListAttributes.splice(0, 0,{category:"",label: "<i class='fa fa-plus-circle'></i> <?= $dae_fsas_new_attribute;?>",value:"-1"});
                                }
                                response(prepareListAttributes);
                            }
                        }
                    });
                },
                'select': function (item) {
                    if(item['value'] == 0 || item['value'] == -1){
                        //сохраним значение для передачи в форму
                        self.getFormAttribute($(this).val());
                    }else{
                        daeEvent.dispatch(DAE_EVENT_SELECT_SINGLE_ATTRIBUTE, {attribute_id: item['value'], attribute_name: item['label']});
                        $(self.ELEMENT_INPUT_ATTRIBUTE_ID).val(item['value']);
                        $(self.ELEMENT_INPUT_ATTRIBUTE_NAME).val(item['label']);
                    }
                }
            });
        }

    }
    //************************
    var formSelectAttributeSingle = new modelFormSelectAttributeSingle();
    $(document).ready(function(){
        formSelectAttributeSingle.init();
    });
</script>
