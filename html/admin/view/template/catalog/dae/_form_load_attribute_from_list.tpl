<table class="table">
  <?php foreach ($devos_attributes as $attribute_group) { ?>
    <tr>
      <td>
          <span
              style="cursor:pointer;"
              class = "dae_attribute_group_one"
              data-dae-attribute-group-id-one="<?= $attribute_group['attribute_group_id']; ?>">
            <strong><?= $attribute_group['name']; ?></strong>
          </span>
      </td>
      <td>
      <?php foreach ($attribute_group['attributes'] as $attribute) { ?>
        <span style="padding: 0px 3px;cursor:pointer;"
              class = "dae_attribute_one"
              data-dae-attribute-one="<?= $attribute['attribute_id']; ?>"
              data-dae-attribute-group="<?= $attribute_group['name']; ?>"><?= $attribute['name']; ?></span>
        <span>&bull;</span>
      <?php } ?>
      </td>
    </tr>
  <?php } ?>
  </table>
