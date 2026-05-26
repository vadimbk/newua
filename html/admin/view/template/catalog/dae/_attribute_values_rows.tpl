<?php foreach ($attribute_values as $values) { ?>

        <tr id="attribute-value-<?= $values['attribute_value_id']; ?>" data-attribute_id="<?php echo $values['attribute_id']; ?>" data-attribute_value_id="<?= $values['attribute_value_id']; ?>">
            <input type="hidden" name="dae_attribute_value[][attribute_value_id]" value="<?php echo $values['attribute_value_id']; ?>" />
        <td>#<?php echo $values['attribute_value_id'];?></td>
        <td class="text-center dae_a_v_default">
            <?php if (isset($values['default']) && $values['default']==1){ ?>
            <i class="fa fa-check"></i>
            <?php } ?>
        </td>
        <td class="text-center dae_a_v_img" >
            <img src="<?php echo $values['thumb_image']; ?>" alt="" title=""  style="width:30px;" />
        </td>
        <td class="text-left dae_a_v_text">
            <?php foreach ($languages as $language) { ?>
            <span>
                <img src="<?php echo $language['path_image']; ?>" title="<?php echo $language['name']; ?>" />
            </span>
            <span class="dae_a_v_text_language_<?php echo $language['language_id'];?>"><?php echo isset($values['description'][$language['language_id']]['text'])?$values['description'][$language['language_id']]['text'] : ''; ?>
            </span><br>
            <?php } ?>
        </td>
    <?php if(!empty($settings['dae_form_view_url'])){?>
                    <td class="text-left dae_a_v_url">
                      <?php echo isset($values['url']) ? $values['url'] : ''; ?>
                    </td>
                    <?php } ?>
    <?php if(!empty($settings['dae_form_view_description'])){?>
                    <td class="text-left dae_a_v_description">
                      <?php foreach ($languages as $language) { ?>
                      <span>
                        <img src="<?php echo $language['path_image']; ?>" title="<?php echo $language['name']; ?>" />
                      </span>
                      <span class="dae_a_v_description_language_<?php echo $language['language_id'];?>"><?php echo isset($values['description'][$language['language_id']]['description']) ? $values['description'][$language['language_id']]['description'] : ''; ?>
                      </span><br>
                      <?php } ?>
</td>
<?php } ?>
<?php if(!empty($settings['dae_value_category'])){?>
                      <td>

                      <?php
                        if(isset($attribute_values_category[$values['attribute_value_id']])){
                          foreach($attribute_values_category[$values['attribute_value_id']] as $value){
                            echo $value['name'].'<br>';
}
} ?>
</td>
<?php } ?>
<td class="text-right">
    <button type="button" data-toggle="tooltip" title="<?php echo $button_edit;?>" class="btn btn-primary dae-form-attribute-value" data-attribute_value_id="<?= $values['attribute_value_id']; ?>" data-attribute_id="<?= $values['attribute_id']; ?>"><i class="fa fa-pencil"></i></button>
    <button type="button" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger dae-remove-attribute-value"><i class="fa fa-minus-circle"></i></button>
</td>
</tr>
<?php } ?>
