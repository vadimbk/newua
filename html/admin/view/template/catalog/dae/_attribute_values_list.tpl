<div id="attribute_values_<?= $attribute_id; ?>_page_<?= $page;?>" class="box-attribute-values">
<table class="table table-hover">
    <thead>
        <tr>
            <td style="width:10px;">#</td>
            <td style="width:20px;"><?php echo $dae_lang_default;?></td>
            <td class="text-left" style="width:40px;"><?= $dae_avl_image_value ?></td>
            <td class="text-left required"><?= $dae_avl_value ?></td>
            <?php if(!empty($settings['dae_form_view_url'])){?>
                        <td><?= $dae_avl_attribute_value_url ?></td>
                      <?php } ?>
            <?php if(!empty($settings['dae_form_view_description'])){?>
                        <td><?= $dae_avl_attribute_value_description ?></td>
                      <?php } ?>
            <?php if(!empty($settings['dae_value_category'])){?>
                        <td><?= $dae_avl_attribute_value_category ?></td>
                      <?php } ?>
            <td style="width: 130px;">&nbsp;</td>
        </tr>
    </thead>
    <tbody>

        <?php foreach ($attribute_values as $attribute_value) { ?>

        <tr id="attribute-value-<?= $attribute_value['attribute_value_id']; ?>" data-attribute_id="<?php echo $attribute_value['attribute_id']; ?>" data-attribute_value_id="<?= $attribute_value['attribute_value_id']; ?>">
            <input type="hidden" name="dae_attribute_value[][attribute_value_id]" value="<?php echo $attribute_value['attribute_value_id']; ?>" />
        <td>#<?php echo $attribute_value['attribute_value_id'];?></td>
        <td class="text-center dae_a_v_default">
            <?php if (isset($attribute_value['default']) && $attribute_value['default']==1){ ?>
            <i class="fa fa-check"></i>
            <?php } ?>
        </td>
        <td class="text-center dae_a_v_img" >
            <img src="<?php echo $attribute_value['thumb_image']; ?>" alt="" title="" data-placeholder="<?= $dae_avl_image_value ?>"  style="width:30px;" />
        </td>
        <td class="text-left dae_a_v_text">
            <?php foreach ($languages as $language) { ?>
            <span>
                <img src="<?php echo $language['path_image']; ?>" title="<?php echo $language['name']; ?>" />
            </span>
            <span class="dae_a_v_text_language_<?php echo $language['language_id'];?>"><?php echo isset($attribute_value['description'][$language['language_id']]['text'])?$attribute_value['description'][$language['language_id']]['text'] : ''; ?>
            </span><br>
            <?php } ?>
        </td>
        <?php if(!empty($settings['dae_form_view_url'])){?>
            <td class="text-left dae_a_v_url">
              <?php echo isset($attribute_value['url']) ? $attribute_value['url'] : ''; ?>
            </td>
        <?php } ?>
        <?php if(!empty($settings['dae_form_view_description'])){?>
                        <td class="text-left dae_a_v_description">
                          <?php foreach ($languages as $language) { ?>
                          <span>
                            <img src="<?php echo $language['path_image']; ?>" title="<?php echo $language['name']; ?>" />
                          </span>
                          <span class="dae_a_v_description_language_<?php echo $language['language_id'];?>"><?php echo isset($attribute_value['description'][$language['language_id']]['description']) ? $attribute_value['description'][$language['language_id']]['description'] : ''; ?>
                          </span><br>
                          <?php } ?>
    </td>
    <?php } ?>
    <?php if(!empty($settings['dae_value_category'])){?>
                          <td>

                          <?php
                            if(isset($attribute_values_category[$attribute_value['attribute_value_id']])){
                              foreach($attribute_values_category[$attribute_value['attribute_value_id']] as $value){
                                echo $value['name'].'<br>';
    }
    } ?>
    </td>
    <?php } ?>
    <td class="text-right">
        <button type="button" data-toggle="tooltip" title="<?php echo $button_edit;?>" class="btn btn-primary dae-form-attribute-value" data-attribute_value_id="<?= $attribute_value['attribute_value_id']; ?>" data-attribute_id="<?= $attribute_value['attribute_id']; ?>"><i class="fa fa-pencil"></i></button>
        <button type="button" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger dae-remove-attribute-value" data-attribute_value_id="<?= $attribute_value['attribute_value_id']; ?>" ><i class="fa fa-trash-o"></i></button>
    </td>
    </tr>
    <?php } ?>

</tbody>
</table>
    <div class="text-right">
        <button type="button" data-toggle="tooltip" title="<?php echo $button_option_value_add; ?>" class="btn btn-primary dae-add-attribute-value fixed-button dae-form-attribute-value" data-attribute_value_id="0" data-attribute_id="<?= $attribute_id; ?>"><i class="fa fa-plus"></i></button>
    </div>

<?php if(isset($pagination)){ ?>
    <div class="row">
        <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
        <div class="col-sm-6 text-right"><?php echo $results; ?></div>
    </div>
    <?php } ?>
</div>