<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <td><?php echo $dae_fs_product_name;?></td>
            <td><?php echo $dae_fs_attributes_error;?></td>
            <td><?php echo $dae_fs_product_edit;?></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach($products as $product_id => $product){ ?>
        <tr>
            <td><?php echo $product['name'];?></td>
            <td><?php echo implode(', ', $product['attributes']);?></td>
            <td class="text-right"><a href="<?php echo $product['url']; ?>" data-toggle="tooltip" title="<?php echo $dae_fs_product_edit; ?>" class="btn btn-primary" target="_blank"><i class="fa fa-pencil"></i></a></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<div class="row">
    <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
    <div class="col-sm-6 text-right"><?php echo $results; ?></div>
</div>

