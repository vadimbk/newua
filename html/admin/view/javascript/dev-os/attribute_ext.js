    var DAE_EVENT_SELECT_SINGLE_CATEGORY = 'SELECT_SINGLE_CATEGORY';
    var DAE_EVENT_SELECT_SINGLE_ATTRIBUTE = 'SELECT_SINGLE_ATTRIBUTE';
    var DAE_EVENT_RESET_SINGLE_ATTRIBUTE = 'RESET_SINGLE_ATTRIBUTE';

    var DAE_EVENT_SELECT_ATTRIBUTE_GROUP = 'SELECT_ATTRIBUTE_GROUP';
    var DAE_EVENT_RESET_ATTRIBUTE_GROUP = 'RESET_ATTRIBUTE_GROUP';

    var DAE_EVENT_CREATE_ATTRIBUTE_GROUP = 'CREATE_ATTRIBUTE_GROUP';
    var DAE_EVENT_UPDATE_ATTRIBUTE_GROUP = 'UPDATE_ATTRIBUTE_GROUP';
    //var DAE_SAVE_ATTRIBUTE_GROUP = 'SAVE_ATTRIBUTE_GROUP';

    var DAE_EVENT_CREATE_ATTRIBUTE = 'CREATE_ATTRIBUTE';
    var DAE_EVENT_UPDATE_ATTRIBUTE = 'UPDATE_ATTRIBUTE';

    var DAE_EVENT_SELECT_ATTRIBUTE_VALUE = 'SELECT_ATTRIBUTE_VALUE';
    var DAE_EVENT_RESET_ATTRIBUTE_VALUE = 'RESET_ATTRIBUTE_VALUE';
    var DAE_EVENT_CREATE_ATTRIBUTE_VALUE = 'CREATE_ATTRIBUTE_VALUE';
    var DAE_EVENT_UPDATE_ATTRIBUTE_VALUE = 'UPDATE_ATTRIBUTE_VALUE';
    var DAE_EVENT_DELETE_ATTRIBUTE_VALUE = 'DELETE_ATTRIBUTE_VALUE';

    var DAE_EVENT_GENERATE_ATTRIBUTE_VALUES = 'GENERATE_ATTRIBUTE_VALUES';
    var DAE_EVENT_ADD_CATEGORY_TO_PRODUCT = 'ADD_CATEGORY_TO_PRODUCT';

    var DAE_EVENT_CHANGE_SETTINGS_LOCAL = 'CHANGE_SETTINGS_LOCAL';

    var DAE_STATUS_SUCCESS = 1;
    var DAE_STATUS_ERROR = 0;
    var DAE_STATUS_INFO = 2;

      /**
       * Работа с alert
       */
    var daeServiceAlert = function(context)
    {
        var self = this;
        self.context = context;
        self.ALERT_SUCCESS = 'success';
        self.ALERT_DANGER = 'danger';
        self.ALERT_INFO = 'info';
        self.view = function(text, type)
        {
            $(self.context + ' > .alert').addClass('hidden')
            $(self.context + ' > .alert-'+type+' span').text(text);
            $(self.context + ' > .alert-'+type).removeClass('hidden');
        };
        self.viewSuccess = function(text)
        {
            self.view(text, self.ALERT_SUCCESS);
        };
        self.viewDanger = function(text)
        {
            self.view(text, self.ALERT_DANGER);
        };
        self.viewInfo = function(text)
        {
            self.view(text, self.ALERT_INFO);
        };

        self.viewByStatus = function(text, status){
            self.view(text, self.getTypeAlertByStatus(status));
        }
        self.getTypeAlertByStatus = function(status){
            var result = self.ALERT_INFO;
            switch (status) {
                case DAE_STATUS_SUCCESS:
                    result = self.ALERT_SUCCESS;
                    break;
                case DAE_STATUS_ERROR:
                    result = self.ALERT_DANGER;
                    break;
            }
            return result;
        }
        self.handlerByResponse = function(response){
            if(response.hasOwnProperty('status') && response.hasOwnProperty('message')){
                self.viewByStatus(response.message, response.status);
            }
        }

    }
    var layoutAlert = new daeServiceAlert('.dae-alert');
    var modalAlert = new daeServiceAlert('#daeModalBox .modal-body');


function getUrlParams(url) {
        var queryString = url ? url.split('?')[1] : '';
        var obj = {};
        if (queryString) {
            queryString = queryString.split('#')[0];
            var arr = queryString.split('&');
            for (var i = 0; i < arr.length; i++) {
                var a = arr[i].split('=');
                var paramNum = undefined;
                var paramName = a[0].replace(/\[\d*\]/, function (v) {
                    paramNum = v.slice(1, -1);
                    return '';
                });

                var paramValue = typeof (a[1]) === 'undefined' ? true : a[1];
                paramName = paramName.toLowerCase();
                paramValue = paramValue.toLowerCase();

                if (obj[paramName]) {
                    if (typeof obj[paramName] === 'string') {
                        obj[paramName] = [obj[paramName]];
                    }

                    if (typeof paramNum === 'undefined') {
                        obj[paramName].push(paramValue);
                    } else {
                        obj[paramName][paramNum] = paramValue;
                    }
                } else {
                    obj[paramName] = paramValue;
                }
            }
        }
        return obj;
    }
/*
 * dae_dispatchEvent(DAE_EVENT_SELECT_SINGLE_CATEGORY, {category_id: 1});
 */
    var daeServiceEvent = function(){
        var self = this;
        self.dispatch = function(eventName, detail){
            if(typeof detail == 'undefined')
                detail = {};
            /*var daeEvent = new CustomEvent(eventName, {
                detail: detail
            });*/
            document.dispatchEvent(new CustomEvent(eventName, {
                detail: detail
            }));
         }
    }

    var daeEvent = new daeServiceEvent();

    var daeServiceModal = function(context){
        var self = this;
        self.context = context;
        self.init = function(html){
            $(self.context).html(html);
            self.show();
        }
        self.show = function(){
            $(self.context).modal('show');
        }
        self.hide = function(){
            $(self.context).modal('hide');
        }
    }
    var daeModal = new daeServiceModal('#daeModalBox');

    function daeViewFormAttributeGroup(attribute_group_id, default_attribute_group_name){
        if(typeof attribute_group_id === 'undefined'){
            attribute_group_id = 0;
        }

        if(typeof default_attribute_group_name === 'undefined'){
            default_attribute_group_name = '';
        }
        //открытие формы группы атрибутов
        $.ajax({
            url: JS_URL_GET_FORM_ATTRIBUTE_GROUP,
            dataType: 'json',
            data: {
                attribute_group_id: attribute_group_id,
                default_name:default_attribute_group_name
            },
            success: function (json) {
                layoutAlert.handlerByResponse(json);
                if(json.status == DAE_STATUS_SUCCESS){
                    daeModal.init(json.modal);
                }
            }
        });
    }

    function daeViewFormAttribute(attribute_id, attribute_group_id, default_attribute_name){
        if(typeof attribute_id === 'undefined'){
            attribute_id = 0;
        }
        if(typeof attribute_group_id === 'undefined'){
            attribute_group_id = 0;
        }

        if(typeof default_attribute_name === 'undefined'){
            default_attribute_name = '';
        }
        $.ajax({
            url: JS_URL_GET_FORM_ATTRIBUTE,
            type: 'GET',
            dataType: 'json',
            data: {
                attribute_id: attribute_id,
                attribute_group_id: attribute_group_id,
                default_attribute_name:default_attribute_name
            },
            success: function (json) {
                layoutAlert.handlerByResponse(json);
                if(json.status == DAE_STATUS_SUCCESS){
                    daeModal.init(json.modal);
                }
            }
        });
    }

    function daeViewFormAttributeValue(attribute_id, attribute_value_id, default_attribute_value){
        if(typeof attribute_id === 'undefined'){
            attribute_id = 0;
        }
        if(typeof attribute_value_id === 'undefined'){
            attribute_value_id = 0;
        }

        if(typeof default_attribute_value === 'undefined'){
            default_attribute_value = '';
        }
        $.ajax({
                url: JS_URL_GET_FORM_ATTRIBUTE_VALUE,
                type: 'GET',
                dataType: 'json',
                data: {attribute_id: attribute_id, attribute_value_id: attribute_value_id, default_attribute_value: default_attribute_value},
                success: function (json) {
                    layoutAlert.handlerByResponse(json);
                    if(json.status == DAE_STATUS_SUCCESS){
                        daeModal.init(json.modal);
                    }
                }
            });
    }

    $(document).ready(function(){
        //вызов формы для группы атрибутов
        $('body').on('click', '.dae-form-attribute-group', function (e) {
            daeViewFormAttributeGroup($(this).data('attribute_group_id'));
        });
        //вызов формы атрибута
        $('body').on('click', '.dae-form-attribute', function (e) {
            daeViewFormAttribute($(this).data('attribute_id'), $(this).data('attribute_group_id') , '');
        });


        //вызов формы для значения атрибута
        $('body').on('click', '.dae-form-attribute-value', function (e) {
            daeViewFormAttributeValue($(this).data('attribute_id'), $(this).data('attribute_value_id'));
        });
        //вызов форму локальных настроек
        $('body').on('click', '.dae-form-settings-local', function (e) {
            $.ajax({
                url: JS_URL_GET_FORM_SETTINGS_LOCAL,
                type: 'GET',
                dataType: 'json',
                data: {group: $(this).data('group')},
                success: function (json) {
                    layoutAlert.handlerByResponse(json);
                    if(json.status == DAE_STATUS_SUCCESS){
                        daeModal.init(json.modal);
                    }
                }
            });
        });

    });


