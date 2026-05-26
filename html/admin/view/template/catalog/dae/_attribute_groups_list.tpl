<ul id="dae-attribute-group" class="nav nav-pills nav-stacked">
    <?php foreach($attribute_groups as $attribute_group){?>
<li class="dae-single-attribute-group" data-attribute_group_id="<?php echo $attribute_group['attribute_group_id'];?>" id="dae_attribute_group_<?php echo $attribute_group['attribute_group_id'];?>">

    <a data-toggle="tab" href="#tab-dae-attribute<?php echo $attribute_group['attribute_group_id'];?>">
        <?php if(isset($settings['dae_load_sortable']) && $settings['dae_load_sortable']){?>
            <i class="fa fa-sort" aria-hidden="true" style="cursor: move;"></i>&nbsp;
        <?php }?>
        <i class="fa fa-pencil dae-form-attribute-group" data-attribute_group_id="<?= $attribute_group['attribute_group_id'];?>"></i>&nbsp;
        <i class="fa fa-minus-circle delete-attribute_group" data-attribute_group_id="<?= $attribute_group['attribute_group_id'];?>"></i>

        <span id="attribute_group_name<?php echo $attribute_group['attribute_group_id'];?>">
            <?=  $attribute_group['name'];?>
        </span>
        (<span id="attribute_group_count_value<?= $attribute_group['attribute_group_id'];?>">
            <?= (isset($attributes_count_in_groups[$attribute_group['attribute_group_id']])?$attributes_count_in_groups[$attribute_group['attribute_group_id']]:((isset($attribute_group['attributes']))?count($attribute_group['attributes']):0));?>
        </span>)
    </a>
</li>
<?php }?>
    <li class="dae-add-attribute-group">
        <button class="btn btn-primary dae-form-attribute-group" title="" data-toggle="tooltip" type="button" data-attribute_group_id="0">
            <i class="fa fa-plus-circle"></i> <?= $dae_al_attribute_group_new;?>
        </button>
    </li>
</ul>