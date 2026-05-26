<form id="dae-form-settings-local" class="form-horizontal">
    <?= $form_settings_local ?>
</form>

<script>
    $('#dae-modal-button-save_form_settings_local').click(function () {
        $.ajax({
            url: JS_URL_SAVE_FORM_SETTINGS_LOCAL,
            type: "POST",
            data: $('#dae-form-settings-local').serialize(),
            dataType: 'json',
            success: function (json) {

                if (json.status === DAE_STATUS_SUCCESS) {
                    daeModal.hide();
                    
                    if (json.change_settings !== false) {
                         for (var setting_name in json.change_settings) {
                             if(dae_settings.hasOwnProperty(setting_name)) {
                                 dae_settings[setting_name] = json.change_settings[setting_name];
                             }
                         }
          
                        daeEvent.dispatch(DAE_EVENT_CHANGE_SETTINGS_LOCAL, {change_settings: json.change_settings});
                    }
                    layoutAlert.handlerByResponse(json);
                } else {
                    modalAlert.handlerByResponse(json);
                }
            }
        });
    });
</script>