<p class="text-danger"><?php echo $dae_help_ain_warning;?></p>
<form method="POST" id="dae-form">

    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <!-- выбор категорий -->
                <div class="col-md-12">
                    <?= $_form_select_category ?>
                </div>
                <div class="col-md-12">
                    <?= $_form_select_product ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <?= $_form_select_attributes_with_group ?>
            <div class="row">
                <div class="col-md-12">
                    <label><?php echo $dae_text_ain_params_add;?></label>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="radio">
                                <label class="radio-inline control-label">
                                    <input type="radio" name="params" value="1" checked="checked" /> <span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_help_ain_clear;?>"><?php echo $dae_text_ain_clear;?></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="radio">
                                <label class="radio-inline control-label">
                                    <input type="radio" name="params" value="2"/> <span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_help_ain_add_new;?>"><?php echo $dae_text_ain_add_new;?></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="radio">
                                <label class="radio-inline control-label">
                                    <input type="radio" name="params" value="3"/> <span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_help_ain_only_clear;?>"><?php echo $dae_text_ain_only_clear;?></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="checkbox">
                                <label class="control-label">
                                    <input type="checkbox" name="set_default" value="1"/> <span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_help_ain_set_default;?>"><?php echo $dae_text_ain_set_default;?></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="checkbox "  >
                                <label class="control-label" >
                                    <input type="checkbox" name="all_attributes" value="1"  data-critery="attribute" class="select-group"/> <span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_help_ain_all_attribute;?>"><?php echo $dae_text_ain_all_attribute;?></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


<script>


    $(document).ready(function () {
        $(formSelectProduct.ELEMENT_BOX_PRODUCTS_ID).height(150);
        $(formSelectProduct.ELEMENT_BOX_IGNORE_PRODUCTS_ID).height(150);
        $(formSelectCategory.ELEMENT_BOX_BY_ADDITIONALLY_CATEGORIES_ID).height(150);
        $(formSelectAttributeWithGroup.ELEMENT_BOX_SELECT_ATTRIBUTES).height(150);

        /*
         * Отправка формы
         */
        $('#dae-run').click(function () {
            layoutAlert.viewByStatus('<?php echo $dae_text_wait;?>',layoutAlert.ALERT_INFO);
            var form = $('#dae-form');
            $.ajax({
                url: JS_URL_RUN_ADD_ATTRIBUTES_IN_PRODUCTS,
                type: "POST",
                data: form.serialize(),
                dataType: 'json',
                success: function (json) {
                    layoutAlert.handlerByResponse(json);
                }
            });
        });
    });
</script>