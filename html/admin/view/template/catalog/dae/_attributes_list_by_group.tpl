<div id="attributes_by_group_<?= $attribute_group['attribute_group_id'].'_page_'.$page ;?>" class="attributes_by_group">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th class="text-left">&nbsp;</th>
          <th class="text-left"><?= $dae_alg_column_attribute_name;?></th>
          <th class="text-right th_sort_order <?= ($mode != 0)?'hide':''?>"><?= $dae_alg_column_sort_order;?></th>
          <th class="text-center th_attribute-views <?= ($mode == 0)?'hide':''?>">
                <label class="control-label">
                  <span title="" data-toggle="tooltip" data-original-title="<?= $dae_alg_help_attr_view_category;?>">
                    <?= $dae_alg_column_attr_view_category;?>
                  </span>
                </label>
              <br><input type="checkbox" class="select-all" data-critery="view_catalog" value="" />

          </th>
          <th class="text-center th_attribute-views <?= ($mode == 0)?'hide':''?>">
            <label class="control-label">
              <span title="" data-toggle="tooltip" data-original-title="<?= $dae_alg_help_attr_view_product;?>">
                <?= $dae_alg_column_attr_view_product;?>
              </span>
            </label>
              <br><input type="checkbox" class="select-all" data-critery="view_product" value="" />
          </th>
          <th class="text-center th_attribute-views <?= ($mode == 0)?'hide':''?>">
                <label class="control-label">
                        <span title="" data-toggle="tooltip" data-original-title="<?= $dae_alg_help_attr_view_def_product;?>">
                            <?= $dae_alg_column_attr_view_def_product;?>
                        </span>
                </label>
              <br><input type="checkbox" class="select-all" data-critery="view_default" value="" />
          </th>
          <th class="text-right th_attribute-actions <?= ($mode != 0)?'hide':''?>" style="width:230px;"><?= $dae_alg_column_action; ?></th>
        </tr>
      </thead>
      <tbody class="dae_attribute_sorted">

        <?php if (isset($attribute_group['attributes'])) { ?>
        <?php foreach ($attribute_group['attributes'] as $attribute) { ?>
        <tr id="dae_attribute_<?php echo $attribute['attribute_id'];?>" >
          <td class="text-center" style="cursor: move;">
            <i class="fa fa-sort" aria-hidden="true" style="cursor: move;"></i>
          </td>
          <td class="text-left">
              <input type="hidden" name="setting_attribute[attributes][]" value="<?= $attribute['attribute_id'] ?>" />
            <span class="attribute_name"><?= $attribute['name']; ?></span>
            <!-- если в настройках есть вывод ид атрибутов -->
            <?php if(!empty($settings['dae_view_attr_id'])){ ?>
              #<?= $attribute['attribute_id']; ?>
            <?php } ?>
            <!-- если в настройках есть вывод кол-ва значений -->
            <?php if(!empty($settings['dae_view_count_values'])){ ?>
              (<?= (isset($count_values_in_attributes[$attribute['attribute_id']]))?$count_values_in_attributes[$attribute['attribute_id']]:0 ?>)
            <?php } ?>
            <br>
            <!-- если в настройках есть вывод значений атрибутов -->
            <?php if(!empty($settings['dae_view_val_attr'])){ ?>
              <span style="color:#aaa;" id="dae_attribute_values_<?php echo $attribute['attribute_id'];?>">
              <?php
                $list_values_tmp=array();
                if(isset($list_attribute_values[$attribute['attribute_id']])){
                  foreach($list_attribute_values[$attribute['attribute_id']]['values'] as $attribute_value) {

                    $attribute_value_description = current($attribute_value['description']);
                    //если в настройках есть вывод ид значений
                    $current_attibute_value_text = '';
                    if(!empty($settings['dae_view_attr_v_id'])){
                      $current_attibute_value_text .= ' #' . $attribute_value['attribute_value_id'];
                    }
                    $current_attibute_value_text .= $attribute_value_description['text'];
                    if($attribute_value['default'] == 1)
                      $list_values_tmp[] = '<u>'.$current_attibute_value_text.'</u>';
                    else
                      $list_values_tmp[] = $current_attibute_value_text;
                  }
                }
              ?>
              <?php echo implode(' • ',$list_values_tmp);?>
              </span>
            <?php } ?>
          </td>
          <td class="text-right sort_order <?= ($mode != 0)?'hide':''?>"><?php echo $attribute['sort_order']; ?></td>

          <td class="text-center attribute-views attribute-view-in-catalog <?= ($mode == 0)?'hide':''?>">
              <input type="checkbox" name="setting_attribute[view_catalog][]"
                     value="<?php echo $attribute['attribute_id'];?>"
                     class="view_catalog"
                     data-checked="<?= (!empty($settings_attributes[$attribute['attribute_id']]['view_catalog']))?'checked':''; ?>"
                     <?= (!empty($settings_attributes[$attribute['attribute_id']]['view_catalog']))?'checked':''; ?>
                     />
          </td>
          <td class="text-center attribute-views attribute-view-in-product <?= ($mode == 0)?'hide':''?>">
              <input type="checkbox" name="setting_attribute[view_product][]"
                     value="<?php echo $attribute['attribute_id'];?>"
                     class="view_product"
                     data-checked="<?= (!empty($settings_attributes[$attribute['attribute_id']]['view_product']))?'checked':''; ?>"
                     <?= (!empty($settings_attributes[$attribute['attribute_id']]['view_product']))?'checked':''; ?>
                     />
          </td>
          <td class="text-center attribute-views attribute-view-in-default <?= ($mode == 0)?'hide':''?>">
              <input type="checkbox" name="setting_attribute[view_product_tab][]"
                     value="<?php echo $attribute['attribute_id'];?>"
                     class="view_default"
                     data-checked="<?= (isset($settings_attributes[$attribute['attribute_id']]['view_product_tab']) && $settings_attributes[$attribute['attribute_id']]['view_product_tab'] == 1)?'checked':''; ?>"
                     <?= (isset($settings_attributes[$attribute['attribute_id']]['view_product_tab']) && $settings_attributes[$attribute['attribute_id']]['view_product_tab'] == 1)?'checked':''; ?>
                     />
          </td>
          <td class="text-right attribute-actions <?= ($mode != 0)?'hide':''?>">
            <a href="<?= $url_getAttributeValuesList ?>&attribute_id=<?= $attribute['attribute_id'] ?>" type="button" data-toggle="tooltip" title="<?php echo $dae_lang_button_list_attr; ?>" class="btn btn-primary"><i class="fa fa-list"></i></a>
            <button type="button" data-toggle="tooltip" title="<?php echo $button_edit; ?>" class="btn btn-primary dae-form-attribute" data-attribute_id="<?= $attribute['attribute_id'] ;?>" data-attribute_group_id="0"><i class="fa fa-pencil"></i></button>
            <button type="button" data-toggle="tooltip" title="<?php echo $button_replase; ?>" class="btn btn-primary" onclick="daeViewFormCorrection(<?php echo $attribute['attribute_id'];?>);"><i class="fa fa-retweet"></i></button>
            <button type="button" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger" onclick="daeDeleteAttribute(<?php echo $attribute['attribute_id'];?>);" data-attribute_group_id="<?php echo $attribute_group['attribute_group_id'];?>"><i class="fa fa-trash-o"></i></button>
          </td>
        </tr>
          <?php } ?>
        <?php } else { ?>
        <tr class="empty_attributes">
          <td class="text-center" colspan="3"><?php echo $text_no_results; ?></td>
        </tr>
        <?php } ?>

      </tbody>
    </table>
  </div>
    <?php if(isset($pagination)){ ?>
    <div class="row">
        <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
        <div class="col-sm-6 text-right"><?php echo $results; ?></div>
    </div>
    <?php } ?>
</div>
<script>
    $(document).ready(function(){
        $( "#tab-dae-attribute<?= $attribute_group['attribute_group_id']; ?> .dae_attribute_sorted" ).sortable({
          axis: 'y',
          update: function (event, ui) {
            var data = $(this).sortable('serialize');
            $.ajax({
              data: data,
              type: 'POST',
              url: JS_URL_SORTED_ATTRIBUTE,
            });
            var sort_order_i=0;
            $(this).find('tr td.sort_order').each(function(){
              $(this).html(sort_order_i);
              sort_order_i = sort_order_i+1;
            });
          }
        });

    });
    $(".pagination a").click(function(event) {
        event.preventDefault();
        var href = $(this).attr('href');
        var url = getUrlParams(href);
        daeGetListAttributesByGroup(url.attribute_group_id, (url.hasOwnProperty('page'))?url.page:1);
    });
</script>