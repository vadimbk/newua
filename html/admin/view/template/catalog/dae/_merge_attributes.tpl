<form method="post" enctype="multipart/form-data" id="dae-form" class="form-horizontal">
    <div class="row">
        <div class="col-md-6">
            <h3>Выберите неправильные атрибуты</h3>
            <?= $_form_select_attributes_with_group;?>
            <?= $_form_select_category; ?>
        </div>
        <div class="col-md-6">
            <h3>Параметры объединения</h3>
            <div class="form-group">
                <?= $_form_select_attribute_single;?>
            </div>
            <div class="form-group">
                <div>
                    <label class="radio-inline control-label">
                        <input type="radio" name="action_by_value" value="merge" checked="checked"/>
                        <span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_ma_action_merge_help;?>"><?= $dae_ma_action_merge;?></span>
                    </label>
                </div>
                <div>
                    <label class="radio-inline control-label">
                        <input type="radio" name="action_by_value" value="add" />
                        <span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_ma_action_add_help;?>"><?= $dae_ma_action_add;?></span>
                    </label>
                </div>
                <div>
                    <label class="radio-inline control-label">
                        <input type="radio" name="action_by_value" value="update" />
                        <span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_ma_action_update_help;?>"><?= $dae_ma_action_update; ?></span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label class="col-sm-12">
                        <input type="checkbox"  name="delete_error_attribute" value="1"/> <?= $dae_ma_delete_error_attribute ?>
                    </label>
                </div>
            </div>

        </div>
    </div>
</form>

<script>
    $(document).ready(function () {
        /*
         * Отправка формы
         */
        $('#dae-run').click(function () {
            layoutAlert.viewByStatus('<?php echo $dae_text_wait;?>',layoutAlert.ALERT_INFO);
            var form = $('#dae-form');
            $.ajax({
                url: JS_URL_RUN_MERGE_ATTRIBUTES,
                type: "POST",
                data: form.serialize(),
                dataType: 'json',
                success: function (json) {
                    layoutAlert.handlerByResponse(json);
                }
            });
            return false;
        });
        //по умолчанию отключим выбор категорий
        $(formSelectCategory.ELEMENT_SELECT_ALL_CATEGORIES).click();
        /*formSelectCategory.disableCategoryList(true);
        $('body').on('click', '#activate-category', function () {
            formSelectCategory.disableCategoryList(!$(this).prop("checked"));
        });*/
        //подправим высоту
        $('#fsa-box-select-attributes').height(150);
        $('#fsc-box-by-additionally-categories-id').height(150);
    });
</script>