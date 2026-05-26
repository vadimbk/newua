<style>
    ul.red_color>li:first-child>a{color: #F08080;}
    ul.green_color>li:first-child>a{color: #3eb489;}
</style>
<?php
if(!isset($_placeholder_attribute_value_name)){
    $_placeholder_attribute_value_name = '';
}
if(!isset($_is_view_label)){
    $_is_view_label = true;
}
if(!isset($_unique_attribute_values)){
    $_unique_attribute_values = false;
}
if(!isset($_attributes_id)){
    $_attributes_id = 0;
}
if(!isset($_language_id)){
    $_language_id = 0;
}
?>
<div class="row">
    <?php if($_is_view_label){ ?>
    <div class="col-xs-12">
        <label class="control-label"><?php echo $dae_fsas_attribute;?></label>
    </div>
    <?php } ?>
    <div class="col-xs-12">
        <input type="hidden" name="attribute_id" id="fsav-attribute-id"  value="<?= $_attribute_id ?>">
        <input type="hidden" name="language_id" id="fsav-language-id"  value="<?= $_language_id ?>">
        <input type="hidden" name="attribute_value_id" id="fsav-attribute-value-id"  value="">
        <div class="input-group">
            <input type="text" name="attribute_value_name" id="fsav-attribute-value-name"  value="" placeholder="<?= $_placeholder_attribute_value_name ?>" class="form-control" autocomplete="off">
            <span class="input-group-btn reset-attribute-value">
                <button type="button" class="btn btn-default"><i class="fa fa-close"></i></button>
            </span>
        </div>
    </div>
</div>

<script>
    /**
     * Фукнции для работы формы выбора значения атрибута
     * fsav - form select attribute value
     */
    var modelFormSelectAttributeValue = function () {
        var self = this;

        self.ELEMENT_INPUT_ATTRIBUTE_ID = '#fsav-attribute-id';
        self.ELEMENT_INPUT_LANGUAGE_ID = '#fsav-language-id';
        self.ELEMENT_INPUT_ATTRIBUTE_VALUE_NAME = '#fsav-attribute-value-name';
        self.ELEMENT_INPUT_ATTRIBUTE_VALUE_ID = '#fsav-attribute-value-id';
        self.ELEMENT_BUTTON_RESET_ATTRIBUTE = '.reset-attribute-value';

        self.createNewAttributeValue = <?=(int)(!empty($_create_new_attribute_value)) ?>;

        self.getFormAttributeValue = function(default_name){
            daeViewFormAttributeValue($(self.ELEMENT_INPUT_ATTRIBUTE_ID).val(), 0, $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_NAME).val());
        };

        self.resetAttributeValue = function(){
            if($(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_NAME).val()){
                daeEvent.dispatch(DAE_EVENT_RESET_ATTRIBUTE_VALUE, {
                    attribute_value_id: $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_ID).val(),
                    attribute_name: $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_NAME).val()
                });
                self.clearFields();
             }
        }
        self.clearFields = function(){
            $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_ID).val(0);
            $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_NAME).val('');
        }
        self.init = function(){
            $(self.ELEMENT_BUTTON_RESET_ATTRIBUTE).click(function(){
                self.resetAttributeValue();
            });

            $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_NAME).autocomplete({
                'source': function(request, response) {
                    var attribute_id = $(self.ELEMENT_INPUT_ATTRIBUTE_ID).val();

                    $.ajax({
                        url: JS_URL_AUTOCOMPLETE_ATTRIBUTE_VALUE,
                        dataType: 'json',
                        data:{
                            text:encodeURIComponent(request),
                            attribute_id:attribute_id
                        },
                        success: function(json) {
                            var language_id = $(self.ELEMENT_INPUT_LANGUAGE_ID).val();

                            var listAttributeValues = $.map(json.attribute_values, function(item) {
                                return {
                                  label: item.description[language_id].text,
                                  value: item.attribute_value_id
                                }
                            });

                            if(self.createNewAttributeValue){
                                if((json.status != DAE_STATUS_SUCCESS) || $.isEmptyObject(json.attribute_values)){
                                    $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_NAME).siblings('ul.dropdown-menu').addClass('red_color').removeClass('green_color');
                                    listAttributeValues.unshift({
                                            label: "<i class='fa fa-plus-circle'></i> <?= $dae_fsav_new_attribute_value;?>",
                                            value:"0"
                                        });
                                }else{
                                    $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_NAME).siblings('ul.dropdown-menu').addClass('green_color').removeClass('red_color');
                                    listAttributeValues.unshift({
                                            label: "<i class='fa fa-plus-circle'></i> <?= $dae_fsav_new_attribute_value;?>",
                                            value:"-1"
                                        });
                                }
                            }

                            response(listAttributeValues);

                        }
                    });
                },
                'select': function(item) {
                    if(item['value'] == 0 || item['value'] == -1){
                        //сохраним значение для передачи в форму
                        daeViewFormAttributeValue($(self.ELEMENT_INPUT_ATTRIBUTE_ID).val(), 0, $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_NAME).val());
                    }else{
                        daeEvent.dispatch(DAE_EVENT_SELECT_ATTRIBUTE_VALUE, {
                            attribute_id: $(self.ELEMENT_INPUT_ATTRIBUTE_ID).val(),
                            attribute_value_name: item['label'],
                            attribute_value_id: item['value'],
                            });
                        $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_ID).val(item['value']);
                        $(self.ELEMENT_INPUT_ATTRIBUTE_VALUE_NAME).val(item['label']);
                    }
                }
            });
        }

    }

    var formSelectAttributeValue = new modelFormSelectAttributeValue();
    $(document).ready(function(){

        formSelectAttributeValue.init();
    });
</script>

