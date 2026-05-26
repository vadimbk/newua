
<div class="row">
    <div class="col-sm-4">
        <div class="col-sm-12">
            <?= $_form_select_attribute_group; ?>
        </div>
        <div class="col-sm-12" id="_attribute_group_list">
            <?= $_attribute_group_list; ?>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="row" id="panel-actions-default">
            <div  class="col-sm-6">
                <?= $_form_select_attribute_single; ?>
            </div>
            <div  class="col-sm-6 text-right">
                <button type="button" class="btn btn-primary dae-form-attribute new_attribute" data-attribute_id="0" data-attribute_group_id="0"><i class="fa fa-plus"></i> <?= $dae_al_attribute_new;?></button>
                <ul class="nav navbar-nav navbar-right" style="margin-right: 0px;">
                    <li class="dropdown">
                        <button id="drop1" href="#" class="dropdown-toggle btn btn-default" data-toggle="dropdown">
                            <i class="fa fa-cog"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo $url_getFormAddExistsValue;?>"><?= $_form_add_exists_value; ?></a></li>
                            <li><a href="<?php echo $url_getFormAddAttributesInProducts;?>"><?= $_form_attributes_in_products;?></a></li>
                            <li><a href="<?php echo $url_getFormMergeAttributes;?>"><?= $_merge_attributes;?></a></li>
                            <li class="divider"></li>
                            <li><a href="<?php echo $url_getFormRelationCategoryAndAttributes;?>"><?= $_form_relation_category_attributes;?></a></li>
                            <li><a href="<?php echo $url_getFormViewAttributesInCategory;?>"><?= $_form_view_attributes_in_category;?></a></li>
                            <li class="divider"></li>
                            <li><a href="" onclick="return false;" id="activate_form_settings_attribute_view"><?= $dae_form_settings_attribute_view;?></a></li>

                            <li><a href="<?php echo $url_getFormService;?>" ><?= $_form_service;?></a></li>
                            <li class="divider"></li>
                            <li><a href="<?php echo $url_getFormSettings;?>"><i class="fa fa-cog"></i> <?= $_form_settings;?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row hide" id="panel-actions-set-views">
            <div  class="col-sm-8">
            <?php if($page_elements_count){ ?>

                <div class="alert alert-info" role="alert"><i class="fa fa-check-circle"></i>
                    <span><?= $dae_al_info_by_settings_view_attributes;?></span>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>

            <?php } ?>
            </div>
            <div  class="col-sm-4 text-right">
                <button id="panel-actions-set-views-submit" type="button" data-toggle="tooltip" title="<?= $button_save; ?>" class="btn btn-primary" ><i class="fa fa-save"></i></button>
                <span id="panel-actions-set-views-close" data-toggle="tooltip" title="<?= $button_back; ?>" class="btn btn-default"><i class="fa fa-reply"></i></span>

            </div>


        </div>
        <div class="tab-content dae-attribute-list-for-group">
            <form id="list_attributes">
                <?php foreach($attribute_groups as $attribute_group){ ?>
                <div id="tab-dae-attribute<?= $attribute_group['attribute_group_id'];?>" class="tab-pane fade">
                    <p class="hide" style="text-align: center;"><?= $dae_al_wait_loading;?> "<?= $attribute_group['name']; ?>"</p>
                </div>
                <?php } ?>
            </form>
        </div>
    </div>
</div>

<script>
    var MODE_ATTRIBUTE = 0;
    var MODE_ATTRIBUTE_SETTINGS_VIEW = 1;
    var mode = MODE_ATTRIBUTE;

    $(document).ready(function () {
        var dae_version = '<?php echo $dae_version;?>';
        var dae_lang_new_version = '<?php echo $dae_lang_new_version;?>';
        var dae_lang_update_error = '<?php echo $dae_lang_update_error;?>';
        var dae_lang_last_version = '<?php echo $dae_lang_last_version;?>';
        $.ajax({
            url: '<?php echo $dae_url_update;?>',
            method: "POST",
            data: "product=dae&version=" + dae_version,
            dataType: 'json',
            success: function (data) {
                if (Number(data.last_version) > Number(dae_version)) {
                    $('#last-version-text').html(dae_lang_new_version + data.last_version);
                } else {
                    $('#last-version-text').html(dae_lang_last_version);

                }
            },
            error: function (data) {
                if (Number(data.last_version) > Number(dae_version)) {
                    $('#last-version-text').html(dae_lang_update_error);
                }
            }
        });

        $('body').on('click', 'ul#dae-attribute-group li.dae-single-attribute-group a', function () {
            var attribute_group_id = $(this).closest('li').data('attribute_group_id');
            daeGetListAttributesByGroup(attribute_group_id, 1);
            //e.preventDefault();
        });


        $("#dae-attribute-group").sortable({
            axis: 'y',
            update: function (event, ui) {
                var data = $(this).sortable('serialize');
                $.ajax({
                    data: data,
                    type: 'POST',
                    url: JS_URL_SORTED_ATTRIBUTE_GROUP,
                });
            }
        });

        //для автоматической загрузки атрибутов первой(на знаю почему, но надо вызывать два раза)

        $('#dae-attribute-group li.dae-single-attribute-group:first-child a').trigger('click');

        //смена декораций для настройки видимости атрибута
        $('body').on('click', '#activate_form_settings_attribute_view', function () {
            $('#panel-actions-set-views, .attribute-views, .th_attribute-views').removeClass('hide');
            $('#panel-actions-default, .attribute-actions, .th_attribute-actions, .sort_order, .th_sort_order').addClass('hide');
            mode = MODE_ATTRIBUTE_SETTINGS_VIEW;
        });
        //
        $('body').on('click', '.select-all', function () {
            var critery = $(this).data('critery');

            if ($(this).prop('checked')) {
                $(this).closest('table').find('input.' + critery).prop('checked', true);

            } else {
                $(this).closest('table').find('input.' + critery).prop('checked', false);
            }
        });

        $('body').on('click', '#panel-actions-set-views-close', function () {
            $('input.view_catalog, input.view_product, input.view_default').each(function () {
                if ($(this).data('checked')) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });
            $('.select-all').prop('checked', false);
            $('#panel-actions-set-views, .attribute-views, .th_attribute-views').addClass('hide');
            $('#panel-actions-default, .attribute-actions, .th_attribute-actions, .sort_order, .th_sort_order').removeClass('hide');
            mode = MODE_ATTRIBUTE;
        });
        //сохранение настроек видимости
        $('body').on('click', '#panel-actions-set-views-submit', function(){
            layoutAlert.viewInfo('<?php echo $dae_text_wait;?>');
            var url = $('#form-view').attr('action');
            $.ajax({
                    url: JS_URL_SAVE_ATTIBUTES_SETTINGS_VIEW,
                    type: "POST",
                    data: $('#list_attributes').serialize(),
                    dataType: 'json',
                    success: function(json) {
                        //теперь нужно в коде поправить, чтобы кнопка Назад ничего не скидывала
                        $('input.view_catalog, input.view_product, input.view_default').each(function () {
                            if ($(this).prop('checked')) {
                                $(this).data('checked', 1);
                            } else {
                                $(this).data('checked', 0);
                            }
                        });
                        layoutAlert.handlerByResponse(json);
                    }
            });
        });
    });

    $('#dae-attribute-group li.dae-single-attribute-group:first-child a').trigger('click');
    /*$('#dae-attribute-group>li:first-child').addClass('active');
     $('.tab-content>div:first-child').addClass('active in');*/
    function daeReplaseCountForAG(attribute_group_id, value = 1) {
        var elem = $('span#attribute_group_count_value' + attribute_group_id);
        var count = Number($(elem).text());
        $(elem).text(count + value);
    }
    function daeGetListAttributesByGroup(attribute_group_id, page) {
        if(typeof page == 'undefined')
            var page=1;
        //чтобы при открытии формы была выбрана активная группа
        $('.new_attribute').data('attribute_group_id', attribute_group_id);
        if (!$('div').is('#attributes_by_group_' + attribute_group_id + '_page_' + page)) {
            //$('#tab-dae-attribute' + attribute_group_id + ' > p').removeClass('hide');
            $.ajax({
                url: JS_URL_GET_LIST_ATTRIBUTES_BY_GROUP,
                type: "GET",
                data: 'attribute_group_id=' + attribute_group_id + '&page=' + page + '&mode=' + mode,
                dataType: 'json',
                success: function (json) {
                    //каждая страница в отдельном диве, поэтому сначала скроем остальные
                    $('div.attributes_by_group').addClass('hide');
                    //$('#tab-dae-attribute' + attribute_group_id + ' > p').addClass('hide');
                    //$(".container-fluid > .alert").addClass('hidden');
                    if (json.status === DAE_STATUS_SUCCESS) {

                        $('#tab-dae-attribute' + json.attribute_group_id).append(json.html);
                    }
                    layoutAlert.handlerByResponse(json);
                }
            });
        } else {
            $('div.attributes_by_group').addClass('hide');
            $('#attributes_by_group_' + attribute_group_id + '_page_' + page).removeClass('hide');
        }
    }

    //удаление группы атрибутов
    $('body').on('click', '.delete-attribute_group', function (e) {
        if (confirm("<?= $dae_al_delete_attribute_group ?>")) {
            $.ajax({
                url: JS_URL_DELETE_ATTRIBUTE_GROUP,
                type: "POST",
                data: {attribute_group_id: $(this).data('attribute_group_id')},
                dataType: 'json',
                success: function (json) {
                    layoutAlert.handlerByResponse(json);
                    if (json.status === DAE_STATUS_SUCCESS) { //если удаление успешно
                        //удалим его из списка
                        $('li[data-attribute_group_id=' + json.attribute_group_id + ']').remove();
                        $('div#tab-dae-attribute' + json.attribute_group_id).remove();
                    }
                }
            });
        }
        //e.stopPropagation();
    });

    function daeDeleteAttribute(attribute_id) {
    $('tr#dae_attribute_' + attribute_id + ' button.btn-danger').tooltip('destroy');
        if (confirm("Удалить атрибут?")) {

            $.ajax({
                url: JS_URL_DELETE_ATTRIBUTE,
                type: "POST",
                data: 'attribute_id=' + attribute_id,
                dataType: 'json',
                success: function (json) {
                    layoutAlert.handlerByResponse(json);
                    if (json.status == DAE_STATUS_SUCCESS) { //если удаление успешно
                        //удалим его из списка
                        $('tr#dae_attribute_' + attribute_id).remove();
                        //обновим кол-во в группе
                        daeReplaseCountForAG(json.attribute_group_id, -1);
                    }
                }
            });
        }
    }
    /*function daeViewFormValue(attribute_id) {
        $.ajax({
            url: JS_URL_GET_FORM_ATTRIBUTE_VALUE,
            type: 'GET',
            dataType: 'json',
            data: 'place=attribute&attribute_id=' + attribute_id,
            success: function (json) {
                $('#daeModalBox').html(json.body);
                $("#daeModalBox").modal('show');
            }
        });
    }*/
    function daeViewFormCorrection(attribute_id) {
        $.ajax({
            url: JS_URL_GET_FORM_CORRECTION_ATTRIBUTE_VALUE,
            type: 'GET',
            dataType: 'json',
            data: 'attribute_id=' + attribute_id,
            success: function (json) {
                daeModal.init(json.modal);
            }
        });
    }


    document.addEventListener(DAE_EVENT_SELECT_SINGLE_ATTRIBUTE, function(e) {
        //очистим строку поиска для групп атрибутов
        formFormSelectAttributeGroup.resetAttributeGroup();
	$.ajax({
            url: JS_URL_SEARCH_ATTRIBUTE,
            type: "POST",
            data: {attribute_name:e.detail.attribute_name},//, attribute_id:e.detail.attribute_id
            dataType: 'json',
            success: function (json) {
                layoutAlert.handlerByResponse(json);
                if(json.hasOwnProperty('attribute_groups')){
                    $('#dae-attribute-group li.dae-single-attribute-group').addClass('hide');
                    $.map(json.attribute_groups, function (item) {
                        $('#dae_attribute_group_'+item['attribute_group_id']).removeClass('hide');
                        return ;
                    });
                }
                /*if(json.hasOwnProperty('attribute_group_html')){
                    $('#_attribute_group_list').html(json['attribute_group_html']);
                }*/
                if(json.hasOwnProperty('list_attributes_groups')){
                    $('#list_attributes div.tab-pane div').remove();
                    $.map(json.list_attributes_groups, function (item) {
                        $('#tab-dae-attribute'+item['attribute_group_id']).append(item['html_by_list_attributes']);
                        return ;
                    });
                    //скроем все
                    //$('#list_attributes .attributes_by_group').addClass('hide');
                    $('#dae-attribute-group li.dae-single-attribute-group:not(.hide):first a').trigger('click');
                }

            }
        });
    });
    document.addEventListener(DAE_EVENT_SELECT_ATTRIBUTE_GROUP, function(e) {
        //сбросим форму поиска по атрибутам
        formSelectAttributeSingle.resetAttribute();
        $.ajax({
            url: JS_URL_SEARCH_ATTRIBUTE_GROUP,
            type: "POST",
            data: {attribute_group_name:e.detail.attribute_group_name},//, attribute_id:e.detail.attribute_id
            dataType: 'json',
            success: function (json) {
                layoutAlert.handlerByResponse(json);
                if(json.hasOwnProperty('attribute_groups')){
                    $('#dae-attribute-group li.dae-single-attribute-group').addClass('hide');
                    $.map(json.attribute_groups, function (item) {
                        $('#dae_attribute_group_'+item['attribute_group_id']).removeClass('hide');
                        return ;
                    });
                    //тепеьрь нужно активировать первую активную
                    $('#dae-attribute-group li.dae-single-attribute-group:not(.hide):first a').trigger('click');

                }
            }
        });
    });
    document.addEventListener(DAE_EVENT_RESET_ATTRIBUTE_GROUP, function(e) {
	$('#dae-attribute-group li.dae-single-attribute-group').removeClass('hide');
        $('#dae-attribute-group li.dae-single-attribute-group:not(.hide):first a').trigger('click');
    });

    document.addEventListener(DAE_EVENT_CREATE_ATTRIBUTE_GROUP, function(e) {
        html = '<li class="dae-single-attribute-group ui-sortable-handle" data-attribute_group_id="' + e.detail.attribute_group_id + '" id="dae_attribute_group_' + e.detail.attribute_group_id + '">';
        html += '  <a data-toggle="tab" href="#tab-dae-attribute' + e.detail.attribute_group_id + '">';
        html += '    <i class="fa fa-sort" aria-hidden="true" style="cursor: move;"></i> ';
        html += '    <i class="fa fa-pencil dae-form-attribute-group" data-attribute_group_id="' + e.detail.attribute_group_id + '"></i> ';
        html += '    <i class="fa fa-minus-circle delete-attribute_group" data-attribute_group_id="' + e.detail.attribute_group_id + '"></i> ';
        html += '    <span id="attribute_group_name' + e.detail.attribute_group_id + '">' + e.detail.attribute_group_name + '</span> ';
        html += '    (<span id="attribute_group_count_value' + e.detail.attribute_group_id + '">0</span>)';
        html += '  </a>';
        html += '</li>';
        $('ul#dae-attribute-group li.dae-add-attribute-group').before(html);
        $('form#list_attributes').append('<div id="tab-dae-attribute' + e.detail.attribute_group_id + '" class="tab-pane fade"></div>');
        $('#dae_attribute_group_'+e.detail.attribute_group_id+' a').trigger('click');

    });

    document.addEventListener(DAE_EVENT_UPDATE_ATTRIBUTE_GROUP, function(e) {
        $('span#attribute_group_name' + e.detail.attribute_group_id).html(e.detail.attribute_group_name);
    });

    document.addEventListener(DAE_EVENT_RESET_SINGLE_ATTRIBUTE, function(e) {
       //удалим все азгрухенные атрибуты + сбро
       //$('#list_attributes > div').remove();
       $('#list_attributes div.tab-pane div').remove();
       formFormSelectAttributeGroup.resetAttributeGroup();

	//window.location.reload();
    });

    document.addEventListener(DAE_EVENT_CREATE_ATTRIBUTE, function(e) {
        //нужно обновить кол-во в группах
        daeReplaseCountForAG(e.detail.attribute.attribute_group_id,1);

        //удалим все ранее загруженные дивы для данной группы атрибутов
        $('#tab-dae-attribute' + e.detail.attribute.attribute_group_id+' > div').remove();
        //так как изменилось кол-во атрибутов, нужно запросить список заново для группы и страницы
	//daeGetListAttributesByGroup(e.detail.attribute.attribute_group_id, 1);
        $('#dae_attribute_group_'+e.detail.attribute.attribute_group_id+' a').trigger('click');
    });
    document.addEventListener(DAE_EVENT_UPDATE_ATTRIBUTE, function(e) {
        //если группа у атрибута изменилась обновим кол-во
        if(e.detail.old_attribute_group_id && e.detail.attribute.attribute_group_id != e.detail.old_attribute_group_id){
            daeReplaseCountForAG(e.detail.attribute.attribute_group_id, 1);
            daeReplaseCountForAG(e.detail.old_attribute_group_id, -1);
            //и придется перезагрузить обе группы
            $('#tab-dae-attribute' + e.detail.attribute.attribute_group_id+' > div').remove();
            $('#tab-dae-attribute' + e.detail.old_attribute_group_id+' > div').remove();

            $('#dae_attribute_group_'+e.detail.attribute.attribute_group_id+' a').trigger('click');
        }else{

            //обновим название и сортировку
            $('tr#dae_attribute_'+ e.detail.attribute.attribute_id).find('td:eq(1) span.attribute_name').html(e.detail.attribute.name);
            $('tr#dae_attribute_'+ e.detail.attribute.attribute_id).find('td:eq(2)').html(e.detail.attribute.sort_order);
        }

    });
</script>
