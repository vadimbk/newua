<?php if(!isset($dae_fsa_category_help)){$dae_fsa_category_help = '';} ?>
<?php if(!isset($selected_categories)){$selected_categories = [];} ?>

<?php if (!empty($_form_select_catagory_single)){ ?>
<div class="row">
    <div class="col-xs-12">
        <label class="control-label">
            <?php if($dae_fsa_category_help){ ?>
            <span data-toggle="tooltip" title="" data-original-title="<?= $dae_fsa_category_help;?>">
            <?php } ?>
            <?= $dae_fsa_category;?>
            <?php if($dae_fsa_category_help){ ?>
            </span>
            <?php } ?>
        </label>
    </div>
    <div class="col-xs-12">
        <input type="text" name="category_name" id="fsc-category-name"  value="" placeholder="" class="form-control" autocomplete="off">
        <input type="hidden" name="category_id" id="fsc-category-id"  value="">
    </div>
</div>
<?php } ?>


<?php if (!empty($_enable_catagory_list)){ ?>
<div class="row" disabled="disabled">
    <div class="col-xs-12">
        <label class="control-label"><span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_fsa_category_list_help;?>"><?php echo $dae_fsa_category_list;?></span></label>
    </div>
    <?php if(empty($is_hide_select_all)){ ?>
    <div class="col-xs-12">
        <!-- <label class="control-label"><span><?php echo $dae_fsa_box_name;?></span></label>-->
        <div class="checkbox">
            <label>
                <input type="checkbox" name="all_categories" id="select-all-category" value="1" /> <?php echo $dae_fsa_all_category;?>
            </label>
        </div>
    </div>
    <?php } ?>
    <div class="col-xs-12">
        <input type="text" name="categories_name"  id="fsc-category-name-by-additionally"  value="" placeholder="" class="form-control" autocomplete="off">
    </div>
    <div class="col-xs-12">
        <div id="fsc-box-by-additionally-categories-id" class="well well-sm" style="height: 337px; overflow: auto;margin-bottom: 0px;">

            <?php foreach($selected_categories as $category) { ?>
                <div id="dae_box_category<?= $category['category_id']?>">
                    <i class="fa fa-minus-circle"></i> <?= $category['name']?>
                    <input type="hidden" name="categories_id[]" value="<?= $category['category_id']?>" />
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php } ?>
<script>
    /**
     * Фукнции для работы формы выбора категории
     * fsc - form select category
     */
    var modelFormSelectCategory = function () {
        var self = this;
        self.ELEMENT_BOX_BY_ADDITIONALLY_CATEGORIES_ID = '#fsc-box-by-additionally-categories-id';
        self.ELEMENT_INPUT_CATEGORY_NAME = '#fsc-category-name';
        self.ELEMENT_INPUT_CATEGORY_ID = '#fsc-category-id';
        self.ELEMENT_INPUT_CATEGORY_NAME_BY_ADDITIONALLY = '#fsc-category-name-by-additionally';
        self.ELEMENT_SELECT_ALL_CATEGORIES = '#select-all-category';

        self.selectCategoriesId = [];

        self.addCategoryInBox = function (category_id, category_name) {
            //$(self.ELEMENT_BOX_BY_ADDITIONALLY_CATEGORIES_ID + item['value']).remove();
            category_id = Number(category_id);
            $(self.ELEMENT_INPUT_CATEGORY_NAME_BY_ADDITIONALLY).val('');
            if ($.inArray(category_id, self.selectCategoriesId) < 0) {
                $(self.ELEMENT_BOX_BY_ADDITIONALLY_CATEGORIES_ID).append(
                        '<div id="dae_box_category' + category_id + '">'
                        + '     <i class="fa fa-minus-circle"></i> ' + category_name
                        + '     <input type="hidden" name="categories_id[]" value="' + category_id + '" />'
                        + '</div>'
                        );

                self.selectCategoriesId[self.selectCategoriesId.length] = category_id;

            }
        };

        self.removeCategoryFromBox = function (category_id) {
            category_id = Number(category_id);
            var index_category_id = $.inArray(category_id, self.selectCategoriesId);
            if (index_category_id >= 0) {
                delete self.selectCategoriesId[index_category_id];
            }
        };

        self.init = function () {
            /*
             Автозавершение и выбор категории
             */
            $(self.ELEMENT_INPUT_CATEGORY_NAME).autocomplete({
                'source': function (request, response) {
                    $.ajax({
                        url: JS_URL_AUTOCOMPLETE_CATEGORY + '&filter_name=' + encodeURIComponent(request),
                        dataType: 'json',
                        success: function (json) {
                            response($.map(json, function (item) {
                                return {
                                    label: item['name'],
                                    value: item['category_id']
                                }
                            }));
                        }
                    });
                },
                'select': function (item) {
                    $(self.ELEMENT_INPUT_CATEGORY_NAME).val(item['label']);
                    $(self.ELEMENT_INPUT_CATEGORY_ID).val(item['value']);

                    daeEvent.dispatch(DAE_EVENT_SELECT_SINGLE_CATEGORY, {category_id: item['value'], category_name: item['label']});
                    //загрузка привязанных атрибутов
                    /*$.ajax({
                     url: JS_URL_GET_FORM_LOAD_ATTRIBUTE_FROM_CATEGORY,
                     data: {category_id:item['value']},
                     dataType: 'json',
                     success: function (json) {
                     $('.alert').addClass('hidden');
                     $('#dae_box_attribute').html('');
                     if (json.type == 'info') {
                     $('.alert-info' + ' span').text(json.message);
                     $('.alert-info').removeClass('hidden');
                     } else {
                     for (var tmp_group_id in json.data) {
                     var attribute_group = json.data[tmp_group_id]['attribute_group'];
                     for (var tmp_attr_index in json.data[tmp_group_id]['attributes']) {
                     var attribute_name = json.data[tmp_group_id]['attributes'][tmp_attr_index]['name'];
                     var attribute_id = json.data[tmp_group_id]['attributes'][tmp_attr_index]['attribute_id'];
                     a_i_c_addAttribute(attribute_id, attribute_name, attribute_group);

                     }
                     }
                     //если выведен список атрибутов группы - обновим у них галочку
                     $('div#box-attributes-for-group input').each(function () {
                     var attribute_id = $(this).val();
                     if ($('div').is('#dae_box_attribute' + attribute_id))
                     $(this).prop('checked', true);
                     else
                     $(this).prop('checked', false);
                     });
                     }
                     }
                     });*/
                }
            });
            /*
             Автозавершение и выбор дополнительных категорий
             */
            $(self.ELEMENT_INPUT_CATEGORY_NAME_BY_ADDITIONALLY).autocomplete({
                'source': function (request, response) {
                    $.ajax({
                        url: JS_URL_AUTOCOMPLETE_CATEGORY + '&filter_name=' + encodeURIComponent(request),
                        dataType: 'json',
                        success: function (json) {
                            response($.map(json, function (item) {
                                return {
                                    label: item['name'],
                                    value: item['category_id']
                                }
                            }));
                        }
                    });
                },
                'select': function (item) {
                    self.addCategoryInBox(item['value'], item['label'])

                }
            });

            //если категории уже были загружены - добавим их в модель
            $(self.ELEMENT_BOX_BY_ADDITIONALLY_CATEGORIES_ID + ' input').each(function(){
                self.selectCategoriesId[self.selectCategoriesId.length] = Number($(this).val());
            });


        }
        self.disableCategoryList = function ($status) {
            $(self.ELEMENT_INPUT_CATEGORY_NAME_BY_ADDITIONALLY).prop('disabled', $status);
            $(self.ELEMENT_BOX_BY_ADDITIONALLY_CATEGORIES_ID + ' input').prop('disabled', $status);
        }
    }
    var formSelectCategory = new modelFormSelectCategory();

    $(document).ready(function () {
        formSelectCategory.init();
        $(formSelectCategory.ELEMENT_BOX_BY_ADDITIONALLY_CATEGORIES_ID).height(150);
        $('body').on('click', formSelectCategory.ELEMENT_SELECT_ALL_CATEGORIES, function () {
            formSelectCategory.disableCategoryList($(this).prop("checked"));
        });
        $('form').on('click', formSelectCategory.ELEMENT_BOX_BY_ADDITIONALLY_CATEGORIES_ID + ' .fa-minus-circle', function () {
            var category_id = $(this).siblings('input').val();
            $(this).parent().remove();
            formSelectCategory.removeCategoryFromBox(category_id);
        });
    });

</script>
