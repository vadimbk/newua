<div class="col-sm-12">
    <p class="text-danger"><?php echo $dae_help_ain_warning;?></p>
    <?php echo $dae_faev_description;?>
</div>
<div class="col-sm-12">
    <span id="generate-status"></span>
</div>

<script>
    $(window).ready(function () {
        $('#dae-run').click(function () {
            layoutAlert.viewByStatus('<?php echo $dae_text_wait;?>',layoutAlert.ALERT_INFO);

            $.ajax({
                url: JS_URL_RUN_ADD_EXISTS_VALUE,
                dataType: 'json',
                success: function (json) {
                    layoutAlert.handlerByResponse(json);
                    if(json.status == 1){
                        var text= '';
                        var length = json.data.length;
                        for (var i = 0; i < length; i++) {
                            text += '<div class="col-md-2">' + json.data[i].name + ': ' + json.data[i].count + '</div>';
                        }
                    /*$.map(json, function(item) {
                     text = item['message'];

                     });*/
                        $('span#generate-status').html(text);
                    }
                }
            });
        });
    });
</script>
