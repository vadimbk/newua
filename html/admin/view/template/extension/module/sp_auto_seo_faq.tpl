<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
<?php if (empty($message)) { ?>
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-sp_auto_seo_faq" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-sp_auto_seo_faq" class="form-horizontal">
		  <ul class="nav nav-tabs">
			<li class="active"><a href="#tab-settings" data-toggle="tab"><i class="fa fa-cog"></i> <?php echo $text_settings; ?></a></li>
			<li><a href="#tab-auto-home" data-toggle="tab"><i class="fa fa-rocket"></i> <?php echo $text_auto_home; ?></a></li>
            <li><a href="#tab-auto-category" data-toggle="tab"><i class="fa fa-rocket"></i> <?php echo $text_auto_category; ?></a></li>
            <li><a href="#tab-auto-manufacturer" data-toggle="tab"><i class="fa fa-rocket"></i> <?php echo $text_auto_manufacturer; ?></a></li>
            <li><a href="#tab-auto-product" data-toggle="tab"><i class="fa fa-rocket"></i> <?php echo $text_auto_product; ?></a></li>
            <li><a href="#tab-auto-information" data-toggle="tab"><i class="fa fa-rocket"></i> <?php echo $text_auto_information; ?></a></li>
            <li><a href="#tab-help" data-toggle="tab"><i class="fa fa-life-ring"></i> <?php echo $text_help; ?></a></li>
          </ul>
		  <div class="tab-content">
		  <div class="tab-pane active" id="tab-settings">
		  <div class="form-group"><label class="col-sm-2 control-label"></label><div class="col-sm-10"><?php echo $text_documentation; ?></div></div> 
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_status" class="form-control">
                <?php if ($sp_auto_seo_faq_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
		  <label class="col-sm-2 control-label"><?php echo $entry_type; ?></label>
		  <div class="col-sm-10">
			<select name="sp_auto_seo_faq_type" class="form-control">
				<?php foreach ($sp_auto_seo_faq_types as $type) { ?>
				<?php if ($type['id'] == $sp_auto_seo_faq_type) { ?>
				<option value="<?php echo $type['id']; ?>" selected="selected"><?php echo $type['desc']; ?></option>
				<?php } else { ?>
				<option value="<?php echo $type['id']; ?>"><?php echo $type['desc']; ?></option>
				<?php } ?>
				<?php } ?>
			</select>
		 </div>
		 </div>
		 <div class="form-group">
         <label class="col-sm-2 control-label"><?php echo $text_cache_enable; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_cache_status" class="form-control">
                <?php if ($sp_auto_seo_faq_cache_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>  
		 <div class="form-group">
         <label class="col-sm-2 control-label"><?php echo $text_expand; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_expand" class="form-control">
                <?php if ($sp_auto_seo_faq_expand) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>  
		 <div class="form-group">
         <label class="col-sm-2 control-label"><?php echo $text_not_first_page; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_not_first_page" class="form-control">
                <?php if ($sp_auto_seo_faq_not_first_page) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>  
		 <div class="form-group">
         <label class="col-sm-2 control-label"><?php echo $text_only_stock; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_only_stock" class="form-control">
                <?php if ($sp_auto_seo_faq_only_stock) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>  
	  </div>
	  <div class="tab-pane" id="tab-auto-product">
	  <legend><?php echo $text_auto_product_help; ?></legend>
	  <?php foreach ($languages as $language) { ?>
         <div class="form-group">
			<label class="col-sm-2 control-label"><?php echo $entry_product_title; ?> (<?php echo $language['name']; ?>)</label>
			<div class="col-sm-10">
				<input type="text" name="sp_auto_seo_faq_product_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_product_title; ?>" value="<?php echo isset($sp_auto_seo_faq_product_title[$language['language_id']]) ? $sp_auto_seo_faq_product_title[$language['language_id']] : ''; ?>" class="form-control" />
			</div>
         </div>
		 <?php } ?>
		 <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_show_in_product; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_show_in_product" class="form-control">
                <?php if ($sp_auto_seo_faq_show_in_product) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
		  <legend><?php echo $text_auto_product_all_faq; ?></legend>
		  <div class="table-responsive">
                <table id="product-faq" class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <td class="text-center"><?php echo $column_question; ?></td>
                      <td class="text-center"><?php echo $column_faq; ?></td>
                      <td class="text-center" style="width:10%"><?php echo $column_sort_order; ?></td>
                      <td class="text-center" style="width:10%"></td>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $p_faq_row = 0; ?>
                    <?php foreach ($product_faq as $faq) { ?>
                    <tr id="faq-product-row<?php echo $p_faq_row; ?>">
                      <td class="text-center">
					  <?php foreach($languages as $language) { ?>
					  <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <input type="text" name="sp_auto_seo_faq_product_faq[<?php echo $p_faq_row; ?>][faq_data][<?php echo $language['language_id']; ?>][question]" value="<?php if (!empty($faq['faq_data'][$language['language_id']]['question'])) echo $faq['faq_data'][$language['language_id']]['question']; ?>" class="form-control" style="display:inline-block;width:80%;" /><br>
					  <?php } ?>
					  </td>
                      <td class="text-center">
					  <?php foreach($languages as $language) { ?>
					  <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <textarea rows="3" name="sp_auto_seo_faq_product_faq[<?php echo $p_faq_row; ?>][faq_data][<?php echo $language['language_id']; ?>][answer]" class="form-control" style="display:inline-block;width:80%;"><?php if (!empty($faq['faq_data'][$language['language_id']]['answer'])) echo $faq['faq_data'][$language['language_id']]['answer']; ?></textarea><br>
					  <?php } ?> 
					  </td>
                      <td class="text-center"><input type="text" name="sp_auto_seo_faq_product_faq[<?php echo $p_faq_row; ?>][sort_order]" value="<?php echo $faq['sort_order']; ?>" class="form-control" /></td>
                      <td class="text-center"><button type="button" onclick="$('#faq-product-row<?php echo $p_faq_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                    </tr> 
                    <?php $p_faq_row++; ?>
                    <?php } ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3"></td>
                      <td class="text-center"><button type="button" onclick="addProductFaq();" data-toggle="tooltip" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
<script type="text/javascript"><!--
var p_faq_row = <?php echo $p_faq_row; ?>;
function addProductFaq() {
html  = '<tr id="faq-product-row' + p_faq_row + '">';
html += '  <td class="text-center">';
<?php foreach($languages as $language) { ?>
html += ' <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <input type="text" name="sp_auto_seo_faq_product_faq[' + p_faq_row + '][faq_data][<?php echo $language['language_id']; ?>][question]" value="" class="form-control" style="display:inline-block;width:80%;" /><br>';
<?php } ?>
html += '</td>'; 
html += '  <td class="text-center">';
<?php foreach($languages as $language) { ?>
html += ' <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <textarea rows="3" name="sp_auto_seo_faq_product_faq[' + p_faq_row + '][faq_data][<?php echo $language['language_id']; ?>][answer]" value="" class="form-control" style="display:inline-block;width:80%;"></textarea><br>';
<?php } ?>
html += '</td>';
html += '  <td class="text-center" style="width:10%"><input type="text" name="sp_auto_seo_faq_product_faq[' + p_faq_row + '][sort_order]" value=""  class="form-control" /></td>';
html += '  <td class="text-center"><button type="button" onclick="$(\'#faq-product-row' + p_faq_row + '\').remove();" data-toggle="tooltip" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
html += '</tr>';

$('#product-faq tbody').append(html);

p_faq_row++;
}
//--></script>
	  </div>
	  <div class="tab-pane" id="tab-auto-home">
	  <legend><?php echo $text_auto_home_help; ?></legend>
	   <?php foreach ($languages as $language) { ?>
         <div class="form-group">
			<label class="col-sm-2 control-label"><?php echo $entry_home_title; ?> (<?php echo $language['name']; ?>)</label>
			<div class="col-sm-10">
				<input type="text" name="sp_auto_seo_faq_home_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_home_title; ?>" value="<?php echo isset($sp_auto_seo_faq_home_title[$language['language_id']]) ? $sp_auto_seo_faq_home_title[$language['language_id']] : ''; ?>" class="form-control" />
			</div>
         </div>
		 <?php } ?> 
	   <div class="table-responsive">
                <table id="home-faq" class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <td class="text-center"><?php echo $column_question; ?></td>
                      <td class="text-center"><?php echo $column_faq; ?></td>
                      <td class="text-center" style="width:10%"><?php echo $column_sort_order; ?></td>
                      <td class="text-center" style="width:10%"></td>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $h_faq_row = 0; ?>
                    <?php foreach ($home_faq as $faq) { ?>
                    <tr id="faq-home-row<?php echo $h_faq_row; ?>">
                      <td class="text-center">
					  <?php foreach($languages as $language) { ?>
					  <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <input type="text" name="sp_auto_seo_faq_home_faq[<?php echo $h_faq_row; ?>][faq_data][<?php echo $language['language_id']; ?>][question]" value="<?php if (!empty($faq['faq_data'][$language['language_id']]['question'])) echo $faq['faq_data'][$language['language_id']]['question']; ?>" class="form-control" style="display:inline-block;width:80%;" /><br>
					  <?php } ?>
					  </td>
                      <td class="text-center">
					  <?php foreach($languages as $language) { ?>
					  <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <textarea rows="3" name="sp_auto_seo_faq_home_faq[<?php echo $h_faq_row; ?>][faq_data][<?php echo $language['language_id']; ?>][answer]" class="form-control" style="display:inline-block;width:80%;"><?php if (!empty($faq['faq_data'][$language['language_id']]['answer'])) echo $faq['faq_data'][$language['language_id']]['answer']; ?></textarea><br>
					  <?php } ?> 
					  </td>
                      <td class="text-center"><input type="text" name="sp_auto_seo_faq_home_faq[<?php echo $h_faq_row; ?>][sort_order]" value="<?php echo $faq['sort_order']; ?>" class="form-control" /></td>
                      <td class="text-center"><button type="button" onclick="$('#faq-home-row<?php echo $h_faq_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                    </tr> 
                    <?php $h_faq_row++; ?>
                    <?php } ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3"></td>
                      <td class="text-center"><button type="button" onclick="addHomeFaq();" data-toggle="tooltip" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
<script type="text/javascript"><!--
var h_faq_row = <?php echo $h_faq_row; ?>;
function addHomeFaq() {
html  = '<tr id="faq-home-row' + h_faq_row + '">';
html += '  <td class="text-center">';
<?php foreach($languages as $language) { ?>
html += ' <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <input type="text" name="sp_auto_seo_faq_home_faq[' + h_faq_row + '][faq_data][<?php echo $language['language_id']; ?>][question]" value="" class="form-control" style="display:inline-block;width:80%;" /><br>';
<?php } ?>
html += '</td>'; 
html += '  <td class="text-center">';
<?php foreach($languages as $language) { ?>
html += ' <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <textarea rows="3" name="sp_auto_seo_faq_home_faq[' + h_faq_row + '][faq_data][<?php echo $language['language_id']; ?>][answer]" value="" class="form-control" style="display:inline-block;width:80%;"></textarea><br>';
<?php } ?>
html += '</td>';
html += '  <td class="text-center" style="width:10%"><input type="text" name="sp_auto_seo_faq_home_faq[' + h_faq_row + '][sort_order]" value=""  class="form-control" /></td>';
html += '  <td class="text-center"><button type="button" onclick="$(\'#faq-home-row' + h_faq_row + '\').remove();" data-toggle="tooltip" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
html += '</tr>';

$('#home-faq tbody').append(html);

h_faq_row++;
}
//--></script>
	  </div>
	  <div class="tab-pane" id="tab-auto-information">
	  <legend><?php echo $text_auto_information_help; ?></legend>
	  <?php foreach ($languages as $language) { ?>
         <div class="form-group">
			<label class="col-sm-2 control-label"><?php echo $entry_information_title; ?> (<?php echo $language['name']; ?>)</label>
			<div class="col-sm-10">
				<input type="text" name="sp_auto_seo_faq_information_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_information_title; ?>" value="<?php echo isset($sp_auto_seo_faq_information_title[$language['language_id']]) ? $sp_auto_seo_faq_information_title[$language['language_id']] : ''; ?>" class="form-control" />
			</div>
         </div>
      <?php } ?>
	  <legend><?php echo $text_auto_information_all_faq; ?></legend>
		  <div class="table-responsive">
                <table id="information-faq" class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <td class="text-center"><?php echo $column_question; ?></td>
                      <td class="text-center"><?php echo $column_faq; ?></td>
                      <td class="text-center" style="width:10%"><?php echo $column_sort_order; ?></td>
                      <td class="text-center" style="width:10%"></td>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $i_faq_row = 0; ?>
                    <?php foreach ($information_faq as $faq) { ?>
                    <tr id="faq-information-row<?php echo $i_faq_row; ?>">
                      <td class="text-center">
					  <?php foreach($languages as $language) { ?>
					  <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <input type="text" name="sp_auto_seo_faq_information_faq[<?php echo $i_faq_row; ?>][faq_data][<?php echo $language['language_id']; ?>][question]" value="<?php if (!empty($faq['faq_data'][$language['language_id']]['question'])) echo $faq['faq_data'][$language['language_id']]['question']; ?>" class="form-control" style="display:inline-block;width:80%;" /><br>
					  <?php } ?>
					  </td>
                      <td class="text-center">
					  <?php foreach($languages as $language) { ?>
					  <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <textarea rows="3" name="sp_auto_seo_faq_information_faq[<?php echo $i_faq_row; ?>][faq_data][<?php echo $language['language_id']; ?>][answer]" class="form-control" style="display:inline-block;width:80%;"><?php if (!empty($faq['faq_data'][$language['language_id']]['answer'])) echo $faq['faq_data'][$language['language_id']]['answer']; ?></textarea><br>
					  <?php } ?> 
					  </td>
                      <td class="text-center"><input type="text" name="sp_auto_seo_faq_information_faq[<?php echo $i_faq_row; ?>][sort_order]" value="<?php echo $faq['sort_order']; ?>" class="form-control" /></td>
                      <td class="text-center"><button type="button" onclick="$('#faq-information-row<?php echo $i_faq_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                    </tr> 
                    <?php $i_faq_row++; ?>
                    <?php } ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3"></td>
                      <td class="text-center"><button type="button" onclick="addInformationFaq();" data-toggle="tooltip" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
<script type="text/javascript"><!--
var i_faq_row = <?php echo $i_faq_row; ?>;
function addInformationFaq() {
html  = '<tr id="faq-information-row' + i_faq_row + '">';
html += '  <td class="text-center">';
<?php foreach($languages as $language) { ?>
html += ' <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <input type="text" name="sp_auto_seo_faq_information_faq[' + i_faq_row + '][faq_data][<?php echo $language['language_id']; ?>][question]" value="" class="form-control" style="display:inline-block;width:80%;" /><br>';
<?php } ?>
html += '</td>'; 
html += '  <td class="text-center">';
<?php foreach($languages as $language) { ?>
html += ' <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <textarea rows="3" name="sp_auto_seo_faq_information_faq[' + i_faq_row + '][faq_data][<?php echo $language['language_id']; ?>][answer]" value="" class="form-control" style="display:inline-block;width:80%;"></textarea><br>';
<?php } ?>
html += '</td>';
html += '  <td class="text-center" style="width:10%"><input type="text" name="sp_auto_seo_faq_information_faq[' + i_faq_row + '][sort_order]" value=""  class="form-control" /></td>';
html += '  <td class="text-center"><button type="button" onclick="$(\'#faq-information-row' + i_faq_row + '\').remove();" data-toggle="tooltip" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
html += '</tr>';

$('#information-faq tbody').append(html);

i_faq_row++;
} 
//--></script>
	  </div>
	  <div class="tab-pane" id="tab-auto-category">
	  <legend><?php echo $text_auto_category_help; ?></legend>
	  	 <?php foreach ($languages as $language) { ?>
         <div class="form-group">
			<label class="col-sm-2 control-label"><?php echo $entry_category_title; ?> (<?php echo $language['name']; ?>)</label>
			<div class="col-sm-10">
				<input type="text" name="sp_auto_seo_faq_category_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_category_title; ?>" value="<?php echo isset($sp_auto_seo_faq_category_title[$language['language_id']]) ? $sp_auto_seo_faq_category_title[$language['language_id']] : ''; ?>" class="form-control" />
			</div>
         </div>
		 <?php } ?>
	  <legend><?php echo $text_latest; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_latest_status" class="form-control">
                <?php if ($sp_auto_seo_faq_latest_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_latest_limit" value="<?php echo $sp_auto_seo_faq_latest_limit; ?>" class="form-control" />
              </div>
		  </div>
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_latest_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_latest_title[$language['language_id']]) ? $sp_auto_seo_faq_latest_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_special; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_special_status" class="form-control">
                <?php if ($sp_auto_seo_faq_special_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_special_limit" value="<?php echo $sp_auto_seo_faq_special_limit; ?>" class="form-control" />
              </div>
		  </div>
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_special_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_special_title[$language['language_id']]) ? $sp_auto_seo_faq_special_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_bestseller; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_bestseller_status" class="form-control">
                <?php if ($sp_auto_seo_faq_bestseller_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_bestseller_limit" value="<?php echo $sp_auto_seo_faq_bestseller_limit; ?>" class="form-control" />
              </div>
		  </div>
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_bestseller_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_bestseller_title[$language['language_id']]) ? $sp_auto_seo_faq_bestseller_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_min_price; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_min_price_status" class="form-control">
                <?php if ($sp_auto_seo_faq_min_price_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_min_price_limit" value="<?php echo $sp_auto_seo_faq_min_price_limit; ?>" class="form-control" />
              </div>
		  </div>
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_min_price_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_min_price_title[$language['language_id']]) ? $sp_auto_seo_faq_min_price_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_max_price; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_max_price_status" class="form-control">
                <?php if ($sp_auto_seo_faq_max_price_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_max_price_limit" value="<?php echo $sp_auto_seo_faq_max_price_limit; ?>" class="form-control" />
              </div>
		  </div>
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_max_price_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_max_price_title[$language['language_id']]) ? $sp_auto_seo_faq_max_price_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_viewed; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_viewed_status" class="form-control">
                <?php if ($sp_auto_seo_faq_viewed_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_viewed_limit" value="<?php echo $sp_auto_seo_faq_viewed_limit; ?>" class="form-control" />
              </div>
		  </div>  
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_viewed_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_viewed_title[$language['language_id']]) ? $sp_auto_seo_faq_viewed_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_price_from_to; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_price_from_to_status" class="form-control">
                <?php if ($sp_auto_seo_faq_price_from_to_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_price_from_to_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_price_from_to_title[$language['language_id']]) ? $sp_auto_seo_faq_price_from_to_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
		<legend><?php echo $text_auto_category_all_faq; ?></legend>
		  <div class="table-responsive">
                <table id="category-faq" class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <td class="text-center"><?php echo $column_question; ?></td>
                      <td class="text-center"><?php echo $column_faq; ?></td>
                      <td class="text-center" style="width:10%"><?php echo $column_sort_order; ?></td>
                      <td class="text-center" style="width:10%"></td>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $c_faq_row = 0; ?>
                    <?php foreach ($category_faq as $faq) { ?>
                    <tr id="faq-category-row<?php echo $c_faq_row; ?>">
                      <td class="text-center">
					  <?php foreach($languages as $language) { ?>
					  <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <input type="text" name="sp_auto_seo_faq_category_faq[<?php echo $c_faq_row; ?>][faq_data][<?php echo $language['language_id']; ?>][question]" value="<?php if (!empty($faq['faq_data'][$language['language_id']]['question'])) echo $faq['faq_data'][$language['language_id']]['question']; ?>" class="form-control" style="display:inline-block;width:80%;" /><br>
					  <?php } ?>
					  </td>
                      <td class="text-center">
					  <?php foreach($languages as $language) { ?>
					  <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <textarea rows="3" name="sp_auto_seo_faq_category_faq[<?php echo $c_faq_row; ?>][faq_data][<?php echo $language['language_id']; ?>][answer]" class="form-control" style="display:inline-block;width:80%;"><?php if (!empty($faq['faq_data'][$language['language_id']]['answer'])) echo $faq['faq_data'][$language['language_id']]['answer']; ?></textarea><br>
					  <?php } ?> 
					  </td>
                      <td class="text-center"><input type="text" name="sp_auto_seo_faq_category_faq[<?php echo $c_faq_row; ?>][sort_order]" value="<?php echo $faq['sort_order']; ?>" class="form-control" /></td>
                      <td class="text-center"><button type="button" onclick="$('#faq-category-row<?php echo $c_faq_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                    </tr> 
                    <?php $c_faq_row++; ?>
                    <?php } ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3"></td>
                      <td class="text-center"><button type="button" onclick="addCategoryFaq();" data-toggle="tooltip" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
<script type="text/javascript"><!--
var c_faq_row = <?php echo $c_faq_row; ?>;
function addCategoryFaq() {
html  = '<tr id="faq-category-row' + c_faq_row + '">';
html += '  <td class="text-center">';
<?php foreach($languages as $language) { ?>
html += ' <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <input type="text" name="sp_auto_seo_faq_category_faq[' + c_faq_row + '][faq_data][<?php echo $language['language_id']; ?>][question]" value="" class="form-control" style="display:inline-block;width:80%;" /><br>';
<?php } ?>
html += '</td>'; 
html += '  <td class="text-center">';
<?php foreach($languages as $language) { ?>
html += ' <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <textarea rows="3" name="sp_auto_seo_faq_category_faq[' + c_faq_row + '][faq_data][<?php echo $language['language_id']; ?>][answer]" value="" class="form-control" style="display:inline-block;width:80%;"></textarea><br>';
<?php } ?>
html += '</td>';
html += '  <td class="text-center" style="width:10%"><input type="text" name="sp_auto_seo_faq_category_faq[' + c_faq_row + '][sort_order]" value=""  class="form-control" /></td>';
html += '  <td class="text-center"><button type="button" onclick="$(\'#faq-category-row' + c_faq_row + '\').remove();" data-toggle="tooltip" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
html += '</tr>';

$('#category-faq tbody').append(html);

c_faq_row++;
}
//--></script>
</div>
	  <div class="tab-pane" id="tab-auto-manufacturer">
	  <legend><?php echo $text_auto_manufacturer_help; ?></legend>
	   <?php foreach ($languages as $language) { ?>
         <div class="form-group">
			<label class="col-sm-2 control-label"><?php echo $entry_manufacturer_title; ?> (<?php echo $language['name']; ?>)</label>
			<div class="col-sm-10">
				<input type="text" name="sp_auto_seo_faq_manufacturer_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_manufacturer_title; ?>" value="<?php echo isset($sp_auto_seo_faq_manufacturer_title[$language['language_id']]) ? $sp_auto_seo_faq_manufacturer_title[$language['language_id']] : ''; ?>" class="form-control" />
			</div>
         </div>
		 <?php } ?>
	  <legend><?php echo $text_latest; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_m_latest_status" class="form-control">
                <?php if ($sp_auto_seo_faq_m_latest_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_m_latest_limit" value="<?php echo $sp_auto_seo_faq_m_latest_limit; ?>" class="form-control" />
              </div>
		  </div>
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_m_latest_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_m_latest_title[$language['language_id']]) ? $sp_auto_seo_faq_m_latest_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_special; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_m_special_status" class="form-control">
                <?php if ($sp_auto_seo_faq_m_special_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_m_special_limit" value="<?php echo $sp_auto_seo_faq_m_special_limit; ?>" class="form-control" />
              </div>
		  </div>
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_m_special_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_m_special_title[$language['language_id']]) ? $sp_auto_seo_faq_m_special_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_bestseller; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_m_bestseller_status" class="form-control">
                <?php if ($sp_auto_seo_faq_m_bestseller_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_m_bestseller_limit" value="<?php echo $sp_auto_seo_faq_m_bestseller_limit; ?>" class="form-control" />
              </div>
		  </div>
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_m_bestseller_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_m_bestseller_title[$language['language_id']]) ? $sp_auto_seo_faq_m_bestseller_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_min_price; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_m_min_price_status" class="form-control">
                <?php if ($sp_auto_seo_faq_m_min_price_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_m_min_price_limit" value="<?php echo $sp_auto_seo_faq_m_min_price_limit; ?>" class="form-control" />
              </div>
		  </div>
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_m_min_price_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_m_min_price_title[$language['language_id']]) ? $sp_auto_seo_faq_m_min_price_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_max_price; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_m_max_price_status" class="form-control">
                <?php if ($sp_auto_seo_faq_m_max_price_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_m_max_price_limit" value="<?php echo $sp_auto_seo_faq_m_max_price_limit; ?>" class="form-control" />
              </div>
		  </div>
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_m_max_price_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_m_max_price_title[$language['language_id']]) ? $sp_auto_seo_faq_m_max_price_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_viewed; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_m_viewed_status" class="form-control">
                <?php if ($sp_auto_seo_faq_m_viewed_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_limit; ?></label>
              <div class="col-sm-10">
                 <input type="text" name="sp_auto_seo_faq_m_viewed_limit" value="<?php echo $sp_auto_seo_faq_m_viewed_limit; ?>" class="form-control" />
              </div>
		  </div>  
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_m_viewed_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_m_viewed_title[$language['language_id']]) ? $sp_auto_seo_faq_m_viewed_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
	  <fieldset>
	  <legend><?php echo $text_price_from_to; ?></legend>
		  <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="sp_auto_seo_faq_m_price_from_to_status" class="form-control">
                <?php if ($sp_auto_seo_faq_m_price_from_to_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div> 
		  <?php foreach ($languages as $language) { ?>
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_title; ?> (<?php echo $language['name']; ?>)</label>
                  <div class="col-sm-10">
                    <input type="text" name="sp_auto_seo_faq_m_price_from_to_title[<?php echo $language['language_id']; ?>]" placeholder="<?php echo $entry_title; ?>" value="<?php echo isset($sp_auto_seo_faq_m_price_from_to_title[$language['language_id']]) ? $sp_auto_seo_faq_m_price_from_to_title[$language['language_id']] : ''; ?>" class="form-control" />
                  </div>
                </div>
              <?php } ?>
		</fieldset>
		<legend><?php echo $text_auto_manufacturer_all_faq; ?></legend>
		  <div class="table-responsive">
                <table id="manufacturer-faq" class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <td class="text-center"><?php echo $column_question; ?></td>
                      <td class="text-center"><?php echo $column_faq; ?></td>
                      <td class="text-center" style="width:10%"><?php echo $column_sort_order; ?></td>
                      <td class="text-center" style="width:10%"></td>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $m_faq_row = 0; ?>
                    <?php foreach ($manufacturer_faq as $faq) { ?>
                    <tr id="faq-manufacturer-row<?php echo $m_faq_row; ?>">
                      <td class="text-center">
					  <?php foreach($languages as $language) { ?>
					  <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <input type="text" name="sp_auto_seo_faq_manufacturer_faq[<?php echo $m_faq_row; ?>][faq_data][<?php echo $language['language_id']; ?>][question]" value="<?php if (!empty($faq['faq_data'][$language['language_id']]['question'])) echo $faq['faq_data'][$language['language_id']]['question']; ?>" class="form-control" style="display:inline-block;width:80%;" /><br>
					  <?php } ?>
					  </td>
                      <td class="text-center">
					  <?php foreach($languages as $language) { ?>
					  <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <textarea rows="3" name="sp_auto_seo_faq_manufacturer_faq[<?php echo $m_faq_row; ?>][faq_data][<?php echo $language['language_id']; ?>][answer]" class="form-control" style="display:inline-block;width:80%;"><?php if (!empty($faq['faq_data'][$language['language_id']]['answer'])) echo $faq['faq_data'][$language['language_id']]['answer']; ?></textarea><br>
					  <?php } ?> 
					  </td>
                      <td class="text-center"><input type="text" name="sp_auto_seo_faq_manufacturer_faq[<?php echo $m_faq_row; ?>][sort_order]" value="<?php echo $faq['sort_order']; ?>" class="form-control" /></td>
                      <td class="text-center"><button type="button" onclick="$('#faq-manufacturer-row<?php echo $m_faq_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                    </tr> 
                    <?php $m_faq_row++; ?>
                    <?php } ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3"></td>
                      <td class="text-center"><button type="button" onclick="addManufacturerFaq();" data-toggle="tooltip" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
<script type="text/javascript"><!--
var m_faq_row = <?php echo $m_faq_row; ?>;
function addManufacturerFaq() {
html  = '<tr id="faq-manufacturer-row' + m_faq_row + '">';
html += '  <td class="text-center">';
<?php foreach($languages as $language) { ?>
html += ' <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <input type="text" name="sp_auto_seo_faq_manufacturer_faq[' + m_faq_row + '][faq_data][<?php echo $language['language_id']; ?>][question]" value="" class="form-control" style="display:inline-block;width:80%;" /><br>';
<?php } ?>
html += '</td>'; 
html += '  <td class="text-center">';
<?php foreach($languages as $language) { ?>
html += ' <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" style="display:inline-block;"/> <textarea rows="3" name="sp_auto_seo_faq_manufacturer_faq[' + m_faq_row + '][faq_data][<?php echo $language['language_id']; ?>][answer]" value="" class="form-control" style="display:inline-block;width:80%;"></textarea><br>';
<?php } ?>
html += '</td>';
html += '  <td class="text-center" style="width:10%"><input type="text" name="sp_auto_seo_faq_manufacturer_faq[' + m_faq_row + '][sort_order]" value=""  class="form-control" /></td>';
html += '  <td class="text-center"><button type="button" onclick="$(\'#faq-manufacturer-row' + m_faq_row + '\').remove();" data-toggle="tooltip" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
html += '</tr>';

$('#manufacturer-faq tbody').append(html);

m_faq_row++;
}
//--></script>
		</div>
		<div class="tab-pane" id="tab-help">
			<legend><?php echo $text_help; ?></legend>
			<?php echo $text_credits; ?>
		</div>
		</div>
		</form>
    </div>
  </div>
</div>
<style>
.switch {
	position: relative;
	display: inline-block;
	width: 60px;
	height: 34px;
}

.switch input {
	display:none;
}

.slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	-webkit-transition: .4s;
	transition: .4s;
}

.slider:before {
	position: absolute;
	content: "";
	height: 26px;
	width: 26px;
	left: 4px;
	bottom: 4px;
	background-color: white;
	-webkit-transition: .4s;
	transition: .4s;
}

input:checked + .slider {
	background-color: #167d4b;
}

input:focus + .slider {
	box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
	-webkit-transform: translateX(26px);
	-ms-transform: translateX(26px);
	transform: translateX(26px);
}

.slider.round {
	border-radius: 34px;
}

.slider.round:before {
	border-radius: 50%;
}

.form-group select.form-control {
	text-align: left;
	max-width: 350px;
}

.form-group input[type="text"] {
	text-align: left;
	max-width: 350px;
}
</style>
<?php } else { echo $message; } ?>  
</div>
<?php echo $footer; ?> 