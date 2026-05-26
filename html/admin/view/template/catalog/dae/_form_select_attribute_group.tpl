<style>
    ul.red_color>li:first-child>a{color: #F08080;}
    ul.green_color>li:first-child>a{color: #3eb489;}
    .dae-attribute-group{font-size: 10px;}
</style>

<div class="row">
    <?php if(!empty($_is_view_label)){ ?>
    <div class="col-xs-12">
        <label class="control-label">
            <span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_fsa_attribute_group_help;?>">
                <?php echo $dae_fsa_attribute_group;?>
            </span>
        </label>
    </div>
    <?php } ?>
    <div class="col-sm-12">
        <div class="input-group">
            <input type="text" name="attribute_group_name" value="" placeholder="<?= $_placeholder_attribute_group_name ?>" class="fsag-attributes-group-name form-control" autocomplete="off">
            <input type="hidden" name="attribute_group_id" value="" class="fsag-attributes-group-id">
            <span class="input-group-btn reset-attribute-group">
                <button type="button" class="btn btn-default"><i class="fa fa-close"></i></button>
            </span>
        </div>
    </div>
</div>

<script>
    /**
     * Фукнции для работы формы выбора группы атрибутов
     * fsag - form select attribute group
     */
    var modelFormSelectAttributeGroup = function () {
        var self = this;
        self.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME = '.fsag-attributes-group-name';
        self.ELEMENT_INPUT_ATTRIBUTES_GROUP_ID = '.fsag-attributes-group-id';
        self.ELEMENT_BUTTON_RESET_ATTRIBUTE = '.reset-attribute-group';
        self.createNewAttributeGroup = true;
        self.resetAttributeGroup = function(){
            daeEvent.dispatch(DAE_EVENT_RESET_ATTRIBUTE_GROUP, {
                attribute_group_id: $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_ID).val(),
                attribute_group_name: $(self.ELEMENT_INPUT_ATTRIBUTE_NAME).val()
            });
            $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_ID).val(0);
            $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME).val('');
        }
        self.init = function(){
            $(self.ELEMENT_BUTTON_RESET_ATTRIBUTE).click(function(){
                self.resetAttributeGroup();
            });
            /*
             Автозавершение и выбор группы атрибутов
            */

            $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME).autocomplete({
                'source': function (request, response) {
                    $.ajax({
                        url: JS_URL_AUTOCOMPLETE_ATTRIBUTE_GROUP + '&filter_name=' + encodeURIComponent(request),
                        dataType: 'json',
                        success: function (json) {
                            var prepareListAttributesGroups = [];

                            if ($.isEmptyObject(json)){
                                if(self.createNewAttributeGroup){
                                    $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME)
                                            .siblings('ul.dropdown-menu')
                                            .addClass('red_color')
                                            .removeClass('green_color');
                                    prepareListAttributesGroups.splice(0, 0,{
                                        category:"",
                                        label: "<i class='fa fa-plus-circle'></i> <?= $dae_fsag_new_attribute_group;?>",
                                        value:"0"
                                    });
                                }
                            }else{
                                prepareListAttributesGroups = $.map(json, function(item) {
                                    return {
                                        label: item['name'],
                                        value: item['attribute_group_id']
                                    }
                                });
                                if(self.createNewAttributeGroup){
                                    $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME)
                                        .siblings('ul.dropdown-menu')
                                        .addClass('green_color')
                                        .removeClass('red_color');
                                    prepareListAttributesGroups.splice(0, 0,{
                                        category:"",
                                        label: "<i class='fa fa-plus-circle'></i> <?= $dae_fsag_new_attribute_group;?>",
                                        value:"-1"
                                    });
                                }
                            }
                            response(prepareListAttributesGroups);
                        }
                    });
                },
                'select': function (item) {
                    if(item['value'] == 0 || item['value'] == -1){
                        //сохраним значение для передачи в форму
                        daeViewFormAttributeGroup(0,$(this).val());
                    }else{
                        daeEvent.dispatch(DAE_EVENT_SELECT_ATTRIBUTE_GROUP, {
                            attribute_group_id: item['value'],
                            attribute_group_name: item['label']
                        });
                        $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_NAME).val(item['label']);
                        $(self.ELEMENT_INPUT_ATTRIBUTES_GROUP_ID).val(item['value']);
                    }
                }
            });
        }
    }

    var formFormSelectAttributeGroup = new modelFormSelectAttributeGroup();
    $(document).ready(function(){
        formFormSelectAttributeGroup.init();
    });
</script>
