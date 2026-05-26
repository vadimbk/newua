<?php echo $header; ?><?php echo $column_left; ?>
<!--<style>
    .fixed-button{
        position: fixed;
        right: 50px;
        bottom: 50px;
        border-radius: 50%;
        padding: 20px;
        font-size: 29px;
        height: 70px;
        width: 70px;
    }
</style>-->
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <h1><?php echo $dae_lang_heading_title; ?></h1>
            <div class="pull-right">
                <?php if($is_home){ ?>
                <span><?php echo $dae_lang_this_version.$dae_version;?></span>
                <span style="font-weight:bold;" id="last-version-text"></span>
                <a class="btn btn-default box-button" href="<?php echo $dae_url_documentation;?>" target="_blank"><i class="fa fa-file"></i> <?php echo $dae_lang_url_documentation;?></a>
                <?php }else{ ?>

                    <?php if(!empty($url_help)){?>
                        <a href="<?= $url_help ?>" target="_blank" data-toggle="tooltip" title="<?php echo $button_help; ?>" class="btn btn-default"><i class="fa fa-info-circle"></i></a>
                    <?php } ?>
                    <?php if(!empty($view_button_run)){?>
                        <button type="button" data-toggle="tooltip" title="<?php echo $button_run; ?>" class="btn btn-success" id="dae-run"><i class="fa fa-play"></i></button>
                    <?php } ?>
                    <?php if(!empty($view_button_save)){?>
                        <button type="button" form="dae-form" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary" id="save-form"><i class="fa fa-save"></i></button>
                    <?php } ?>
                    <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
                <?php } ?>
            </div>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <div class="dae-alert">
            <div class="alert alert-danger <?= (empty($error_warning)?'hidden':'') ?>"><i class="fa fa-exclamation-circle"></i>
                <span><?= (isset($error_warning)?$error_warning:'') ?></span>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>

            <div class="alert alert-success hidden"><i class="fa fa-check-circle"></i>
                <span></span>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>

            <div class="alert alert-info hidden"><i class="fa fa-check-circle"></i>
                <span></span>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $dae_title_panel; ?></h3>
            </div>
            <div class="panel-body">
                <script>
                    <?php foreach($urls_by_js as $js_url_name => $js_url){ ?>
                            var <?= $js_url_name ?> = '<?= $js_url ?>';
                    <?php } ?>
                    var dae_settings = JSON.parse('<?= json_encode($settings)?>');
                </script>
                <?= $body ?>
            </div>
        </div>
    </div>
</div>
<div id="daeModalBox" class="modal fade"></div>

<?php echo $footer; ?>