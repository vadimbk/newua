<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title"><?= $modal_title;?></h4>
        </div>

        <div class="modal-body">
            <div class="alert alert-danger hidden" ><i class="fa fa-exclamation-circle"></i> <span></span>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>

            <div class="alert alert-success hidden"><i class="fa fa-check-circle"></i> <span></span>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>

            <div class="alert alert-info hidden"><i class="fa fa-check-circle"></i> <span></span>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>

            <?= $modal_content ?>
        </div>

        <div class="modal-footer">
            <?php if(!empty($view_button_cancel)){ ?>
                <button type="button" id="dae-modal-button-cancel<?= $modal_button_prefix ?>" class="btn btn-default dae-modal-button-cancel" data-dismiss="modal"><?= $button_cancel; ?></button>
            <?php } ?>
            <?php if(!empty($view_button_close)){ ?>
                <button type="button" id="dae-modal-button-close<?= $modal_button_prefix ?>" class="btn btn-default dae-modal-button-close" data-dismiss="modal"><?= $button_close; ?></button>
            <?php } ?>
            <?php if(!empty($view_button_save)){ ?>
                <button type="button" id="dae-modal-button-save<?= $modal_button_prefix ?>" class="btn btn-primary dae-modal-button-save"  ><?= $button_save; ?></button>
            <?php } ?>
            <?php if(!empty($view_button_run)){ ?>
                <button type="button" id="dae-modal-button-run<?= $modal_button_prefix ?>" class="btn btn-primary dae-modal-button-run"  ><?= $button_run; ?></button>
            <?php } ?>
            <?php if(!empty($view_button_add)){ ?>
                <button type="button" id="dae-modal-button-add<?= $modal_button_prefix ?>" class="btn btn-primary dae-modal-button-add"  ><?= $button_add; ?></button>
            <?php } ?>
        </div>
    </div>
</div>
