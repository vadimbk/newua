				<form action="<?php echo $action_license; ?>" method="post" enctype="multipart/form-data" id="form-license-update" name="form-license-update" class="form-horizontal">
			<?php if(!empty($license)){ ?>				
					<div class="col-sm-9"><p><strong><?php echo $dae_lang_license_update_help; ?></strong></p></div>					
			<?php } else { ?>				
					<div class="col-sm-9"><p><strong><?php echo $dae_lang_license_help; ?></strong></p></div>					
			<?php } ?>	
					<div class="col-sm-3">				
						<button type="submit" form="form-license" id="buttom-form-license" data-toggle="tooltip" title="<?php echo $dae_license_button_save; ?>" class="btn btn-primary" name="activate"><?php echo $dae_license_button_save; ?></button>
					</div>
				</form>