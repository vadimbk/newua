<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-salesdrive" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1>Интеграция с SalesDrive</h1>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <?php if ($success) { ?>
        <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-salesdrive" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-name">Ваш домен SalesDrive</label>
                        <div class="col-sm-10">
                            <input type="text" name="module_salesdrive_domain" value="<?php echo $module_salesdrive_domain; ?>" placeholder="Ваш домен SalesDrive" id="input-domain" class="form-control" />
                            <?php if ($error_domain) { ?>
                            <div class="text-danger"><?php echo $error_domain; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-limit">Ключ формы</label>
                        <div class="col-sm-10">
                            <input type="text" name="module_salesdrive_key" value="<?php echo $module_salesdrive_key; ?>" placeholder="Ключ формы" id="input-key" class="form-control" />
                            <?php if ($error_key) { ?>
                            <div class="text-danger"><?php echo $error_key; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status">Статус</label>
                        <div class="col-sm-10">
                            <select name="module_salesdrive_status" id="input-status" class="form-control">
                                <option value="1" <?php if($module_salesdrive_status){ echo 'selected="selected"'; } ?>>Включен</option>
                                <option value="0" <?php if(!$module_salesdrive_status){ echo 'selected="selected"'; } ?>>Отключен</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status">Привязка товара</label>
                        <div class="col-sm-10">
							<input type="radio" id="module_salesdrive_product_bind_id" name="module_salesdrive_product_bind" value="id" <?php if($module_salesdrive_product_bind == 'id'){ echo 'checked'; }?>> <label style="font-weight: normal; vertical-align: middle;" for="module_salesdrive_product_bind_id">ID (рекомендуется)</label><br>
							<input type="radio" id="module_salesdrive_product_bind_model" name="module_salesdrive_product_bind" value="model"  <?php if($module_salesdrive_product_bind == 'model'){ echo 'checked'; }?>> <label style="font-weight: normal; vertical-align: middle;" for="module_salesdrive_product_bind_model">Модель</label><br>
							<input type="radio" id="module_salesdrive_product_bind_sku" name="module_salesdrive_product_bind" value="sku"  <?php if($module_salesdrive_product_bind == 'sku'){ echo 'checked'; }?>> <label style="font-weight: normal; vertical-align: middle;" for="module_salesdrive_product_bind_sku">SKU</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-status">Язык товаров</label>
                        <div class="col-sm-10">
                            <select name="module_salesdrive_product_language" id="input-status" class="form-control">
							<?php foreach($languages as $language){ ?>
                                <option value="<?php echo $language['language_id']; ?>" <?php if($language['language_id']==$module_salesdrive_product_language){ echo 'selected="selected"'; } ?>><?php echo $language['name']; ?></option>
							<?php } ?>
                            </select>
						</div>
                    </div>
                    
                    <?php if (!empty($module_salesdrive_domain) && !empty($module_salesdrive_key)) { ?>
						<div class="form-group">
							<label class="col-sm-2 control-label" for="input-status">Сопоставление способов оплаты</label>
							<div class="col-sm-10">
								<?php  if (!empty($salesdrive_payment_methods_error)) { ?>
								<div class="alert alert-danger"><?php echo $salesdrive_payment_methods_error; ?></div>
								<?php } ?>
								<table class="table" style="width: auto; margin-bottom: 0;">
								<thead>
									<tr>
										<th width="250">В OpenCart</th>
										<th width="250">В SalesDrive</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($payment_methods as $payment_method){ ?>
									<tr>
										<td><?php echo $payment_method['name']; ?></td>
										<td><select name="module_salesdrive_match_payment_methods[<?php echo $payment_method['code']; ?>]" style="width:100%">
											<option>---</option>
											<?php foreach($salesdrive_payment_methods as $salesdrive_payment_method){ ?>
											<option value="<?php echo htmlspecialchars($salesdrive_payment_method['parameter']); ?>"
											<?php
										  		if(isset($match_payment_methods[$payment_method['code']]) && ($match_payment_methods[$payment_method['code']] == $salesdrive_payment_method['parameter'])){ echo 'selected'; } 
										  	?>
											><?php echo $salesdrive_payment_method['name']; ?></option>
											<?php } ?>
										</select></td>
									</tr>
									<?php } ?>
								<tbody>
								</table>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label" for="input-status">Сопоставление способов доставки</label>
							<div class="col-sm-10">
								<?php  if (!empty($salesdrive_shipping_methods_error)) { ?>
								<div class="alert alert-danger"><?php echo $salesdrive_shipping_methods_error; ?></div>
								<?php } ?>
								<table class="table" style="width: auto; margin-bottom: 0;">
								<thead>
									<tr>
										<th width="250">В OpenCart</th>
										<th width="250">В SalesDrive</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($shipping_methods as $shipping_method){ ?>
									<tr>
										<td><?php echo $shipping_method['name']; ?></td>
										<td><select name="module_salesdrive_match_shipping_methods[<?php echo $shipping_method['code']; ?>]" style="width:100%">
											<option>---</option>
											<?php foreach($salesdrive_shipping_methods as $salesdrive_shipping_method){ ?>
											<option value="<?php echo htmlspecialchars($salesdrive_shipping_method['parameter']); ?>"
												<?php 
												if(isset($match_shipping_methods[$shipping_method['code']]) && ($match_shipping_methods[$shipping_method['code']] == $salesdrive_shipping_method['parameter']))
												{ echo 'selected'; } 
												?>
											><?php echo $salesdrive_shipping_method['name']; ?></option>
											<?php } ?>
										</select></td>
									</tr>
									<?php } ?>
								<tbody>
								</table>
							</div>
						</div>
                  	<?php } ?>
                   
                    <div class="form-group" style="padding-left: 15px;">
                   		<button type="submit" form="form-salesdrive" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp; Сохранить</button>
                    </div>
                    
                    <?php if (!empty($module_salesdrive_domain) && !empty($module_salesdrive_key)) { ?>
                    <div class="form-group" style="padding-left: 15px; border-bottom: 1px solid #ededed;">
                   		<div class="btn btn-warning" id="fca-import-order"><i class="fa fa-refresh"></i>&nbsp; Синхронизировать все товары</div>
						<script src="view/javascript/salesdrive/sync.js"></script>
						<link type="text/css" href="view/javascript/salesdrive/sync.css" rel="stylesheet" media="screen" />
						<div id="importProductsUrl" style="display: none"><?php echo $synchronize; ?></div>
						<div class="fca_ajax_result" style="display: none">Экспортировано товаров: <span id="currentOffset">0</span>. Создано товаров с учетом вариаций: <span id="variationCount">0</span>. Время выполнения: <span id="timeElapsed">0</span>. <span id="sd-finish" style="display: none">ЗАВЕРШЕНО!</span></div>
						<div id="fc_api_project_box">
							<div class="fca_preloader">
								<div>Товары и категории экспортируются. Не закрывайте браузер до завершения экспорта.</div>
								<div class="lds-default">
									<div></div>
									<div></div>
									<div></div>
									<div></div>
									<div></div>
									<div></div>
									<div></div>
									<div></div>
									<div></div>
									<div></div>
									<div></div>
									<div></div>
								</div>
							</div>
						</div>
                    </div>
                    <?php } ?>
                    
                    <h3 style="margin-top: 33px;">Импорт остатков на складе с SalesDrive</h3>
                    <div class="form-group">
						<label class="col-sm-2 control-label" for="input-feed">Ссылка на YML-экспорт</label>
						<div class="col-sm-10">
							<input type="text" name="module_salesdrive_feed" value="<?php echo $module_salesdrive_feed; ?>" placeholder="Ссылка на YML-экспорт" id="input-feed" class="form-control" />
						</div>
                    </div>
                    <div class="form-group">
						<label class="col-sm-2 control-label" for="input_gen">Скрипт импорта остатков</label>
						<div class="col-sm-10">
							<input type="text" name="module_salesdrive_gen" value="<?php echo $module_salesdrive_import_stock_script; ?>" id="input_gen" class="form-control" disabled />
						</div>
                    </div>
                    <div class="form-group">
						<label class="col-sm-2 control-label" for="input_gen">Команда Cron</label>
						<div class="col-sm-10">
							<input type="text" name="module_salesdrive_gen" value="curl <?php echo $module_salesdrive_import_stock_script; ?>" id="input_gen" class="form-control" disabled />
						</div>
                    </div>
					<div class="form-group" style="padding-left: 15px;">
						<button type="submit" form="form-salesdrive" data-toggle="tooltip" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp; Сохранить</button>
					</div>
               		
               		<?php if (!empty($module_salesdrive_domain) && !empty($module_salesdrive_key)) { ?>
               		<h3 style="margin-top: 33px;">Передача статусов с SalesDrive на сайт</h3>
               		<div class="alert alert-info" style="margin-bottom: 0;">В SalesDrive установите веб-хук:
						<ul style="margin-bottom: 5px;">
               			<li>Настройки → Общие настройки и интеграции → Другие сервисы → webhook → Добавить</li>
               			</ul>
               			<strong>Данные веб-хука:</strong>
						<ul>
               			<li>Событие = Изменение статуса заявки</li>
               			<li>Добавьте условия:
               				<ul>
               					<li>Тип = Заявка онлайн</li>
               					<li>Сайт = Текущий сайт (требуется, если у вас несколько сайтов или маркетплейсов)</li>
               				</ul>
						</li>
               			<li>URL для передачи webhook:<br>
               			<input type="text" style="width: 100%" disabled value="<?php echo $module_salesdrive_set_order_status; ?>">
               			</li>
               			<li>Информация о заявке = Только статусы</li>
               			</ul>
               		</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-status">Сопоставление статусов</label>
						<div class="col-sm-10">
							<?php  if (!empty($salesdrive_statuses_error)) { ?>
							<div class="alert alert-danger"><?php echo $salesdrive_statuses_error; ?></div>
							<?php } ?>
							<table class="table" style="width: auto; margin-bottom: 0;">
							<thead>
								<tr>
									<th width="250">Статус SalesDrive</th>
									<th width="250">Статус на сайте</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($salesdrive_statuses as $salesdrive_status){ ?>
								<tr>
									<td><?php echo $salesdrive_status['name']; ?></td>
									<td><select name="module_salesdrive_match_order_statuses[<?php echo $salesdrive_status['id']; ?>]" style="width:100%">
										<option>---</option>
										<?php foreach($order_statuses as $order_status){ ?>
										<option value="<?php echo $order_status['order_status_id']; ?>"
										<?php if(isset($match_order_statuses[$salesdrive_status['id']]) && ($match_order_statuses[$salesdrive_status['id']] == $order_status['order_status_id'])){ echo 'selected'; } ?>
										  ><?php echo $order_status['name']; ?></option>
										<?php } ?>
									</select></td>
								</tr>
								<?php } ?>
							<tbody>
							</table>
						</div>
					</div>
					<div class="form-group" style="padding-left: 15px;">
						<button type="submit" form="form-salesdrive" data-toggle="tooltip" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp; Сохранить</button>
					</div>
               		<?php } ?>
                </form>
            </div>            
        </div>
    </div>
</div>
<?php echo $footer; ?>
