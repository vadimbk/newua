
<form  method="post" enctype="multipart/form-data" id="dae-form" class="form-horizontal">
    <div class="row">
        <div class="col-md-6">
            <?= $_form_select_category; ?>
        </div>
        <div class="col-md-6">
            <?= $_form_select_attributes_with_group; ?>
        </div>
    </div>
</form>


<script>
    document.addEventListener(DAE_EVENT_SELECT_SINGLE_CATEGORY, function(e) {
	$.ajax({
            url: JS_URL_GET_RELATIONS_ATTRIBUTES_BY_CATEGORY,
            type: "POST",
            data: {category_id:e.detail.category_id},
            dataType: 'json',
            success: function (json) {
                layoutAlert.handlerByResponse(json);
                formSelectAttributeWithGroup.clearAttributeBox();

                if(json.hasOwnProperty('attributes')){
                    for (var index_attribute_group in json.attributes) {
                        var attribute_group = json.attributes[index_attribute_group]['name'];
                        for (var index_attribute in json.attributes[index_attribute_group]['attributes']) {
                            var attribute_name  = json.attributes[index_attribute_group]['attributes'][index_attribute]['name'];
                            var attribute_id    = json.attributes[index_attribute_group]['attributes'][index_attribute]['attribute_id'];
                            formSelectAttributeWithGroup.addAttributeInBox(attribute_id, attribute_name, attribute_group);

                        }
                    }
                }
            }
        });
    });



    /*
     * Отправка формы
     */
    $('#save-form').click(function () {
        layoutAlert.viewByStatus('<?php echo $dae_text_wait;?>',layoutAlert.ALERT_INFO);
        var form = $('#dae-form');
        $.ajax({
            url: JS_URL_SAVE_FORM_RELATION_CATEGORY_AND_ATTRIBUTES,
            type: "POST",
            data: form.serialize(),
            dataType: 'json',
            success: function (json) {
                layoutAlert.handlerByResponse(json);
            }
        });
        return false;
    });

</script>