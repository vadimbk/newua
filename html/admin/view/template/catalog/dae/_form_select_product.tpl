<div class="row">
    <?php if (!empty($_enable_products)){ ?>
    <!-- Добавляемые товары -->
    <div class="col-md-12">
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label class="control-label"><span data-toggle="tooltip" title="" data-original-title="<?php echo $dae_fsp_product_help;?>"><?= $dae_fsp_product ?></span></label>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="all_products" id="fsp-select-all-products" value="1"/> <?= $dae_fsp_all_product ?>
                        </label>
                    </div>
                </div>
                <div class="col-md-12" id = "product_">
                    <input type="text" name="product_name" id="fsp-product-name" value="" placeholder="" class="form-control" autocomplete="off">
                    <div id="fsp-box-product" class="well well-sm" style="height: 337px; overflow: auto;margin-bottom: 0px;"></div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    <?php if (!empty($_enable_ignore_products)){ ?>
    <!-- Игнорируемые товары -->
    <div class="col-md-12">
        <div class="form-group">
            <div class="row">
                <label class="col-md-12 control-label"><span data-toggle="tooltip" title="" data-original-title="<?= $dae_fsp_product_ignore_help ?>"><?= $dae_fsp_product_ignore ?></span></label>
                <div class="col-md-12" id="product_ignore_">
                    <input type="text" name="ignore_product_name" id="fsp-ignore-product-name" value="" placeholder="" class="form-control" autocomplete="off">
                    <div id="fsp-box-product-ignore" class="well well-sm" style="height: 337px; overflow: auto;margin-bottom: 0px;"></div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
<script>
    /**
     * Фукнции для работы формы выбора товаров
     * fsp - form select category
     */
    var modelFormSelectProduct = function () {
        var self = this;
        self.ELEMENT_BOX_PRODUCTS_ID = '#fsp-box-product';
        self.ELEMENT_INPUT_PRODUCT_NAME = '#fsp-product-name';
        self.ELEMENT_BOX_IGNORE_PRODUCTS_ID = '#fsp-box-product-ignore';
        self.ELEMENT_INPUT_IGNORE_PRODUCT_NAME = '#fsp-ignore-product-name';
        self.ELEMENT_SELECT_ALL_PRODUCTS = '#fsp-select-all-products';

        self.selectProductsId = [];
        self.selectIgnoreProductsId = [];

        self.addProductInBox = function (product_id, product_name) {
            //$(self.ELEMENT_BOX_BY_ADDITIONALLY_CATEGORIES_ID + item['value']).remove();
            product_id = Number(product_id);
            $(self.ELEMENT_INPUT_PRODUCT_NAME).val('');
            if ($.inArray(product_id, self.selectProductsId) < 0) {
                $(self.ELEMENT_BOX_PRODUCTS_ID).append(
                        '<div id="dae_box_product' + product_id + '">'
                        + '     <i class="fa fa-minus-circle"></i> ' + product_name
                        + '     <input type="hidden" name="products_id[]" value="' + product_id + '" />'
                        + '</div>'
                        );

                self.selectProductsId[self.selectProductsId.length] = product_id;

            }
        };

        self.removeProductFromBox = function (product_id) {
            product_id = Number(product_id);
            var index_product_id = $.inArray(product_id, self.selectProductsId);
            if (index_product_id >= 0) {
                delete self.selectProductsId[index_product_id];
            }
        };
        self.addIgnoreProductInBox = function (product_id, product_name) {
            //$(self.ELEMENT_BOX_BY_ADDITIONALLY_CATEGORIES_ID + item['value']).remove();
            product_id = Number(product_id);
            $(self.ELEMENT_INPUT_IGNORE_PRODUCT_NAME).val('');
            if ($.inArray(product_id, self.selectIgnoreProductsId) < 0) {
                $(self.ELEMENT_BOX_IGNORE_PRODUCTS_ID).append(
                        '<div id="dae_box_ignore_product' + product_id + '">'
                        + '     <i class="fa fa-minus-circle"></i> ' + product_name
                        + '     <input type="hidden" name="ignore_products_id[]" value="' + product_id + '" />'
                        + '</div>'
                        );

                self.selectIgnoreProductsId[self.selectIgnoreProductsId.length] = product_id;

            }
        };

        self.removeIgnoreProductFromBox = function (product_id) {
            product_id = Number(product_id);
            var index_product_id = $.inArray(product_id, self.selectIgnoreProductsId);
            if (index_product_id >= 0) {
                delete self.selectIgnoreProductsId[index_product_id];
            }
        };

        self.init = function () {
            /*
            * Автозавершение и выбор товаров
            */
           $(self.ELEMENT_INPUT_PRODUCT_NAME).autocomplete({
               'source': function (request, response) {
                   $.ajax({
                       url: JS_URL_AUTOCOMPLETE_PRODUCT+'&filter_name=' + encodeURIComponent(request),
                       dataType: 'json',
                       success: function (json) {
                           response($.map(json, function (item) {
                               return {
                                   label: item['name'],
                                   value: item['product_id']
                               }
                           }));
                       }
                   });
               },
               'select': function (item) {
                   self.addProductInBox(item['value'], item['label']);
               }
           });
            /*
            * Автозавершение и выбор игнорируемых товаров
            */
           $(self.ELEMENT_INPUT_IGNORE_PRODUCT_NAME).autocomplete({
               'source': function (request, response) {
                   $.ajax({
                       url: JS_URL_AUTOCOMPLETE_PRODUCT+'&filter_name=' + encodeURIComponent(request),
                       dataType: 'json',
                       success: function (json) {
                           response($.map(json, function (item) {
                               return {
                                   label: item['name'],
                                   value: item['product_id']
                               }
                           }));
                       }
                   });
               },
               'select': function (item) {
                   self.addIgnoreProductInBox(item['value'], item['label']);
               }
           });





        }
        self.disableProductList = function ($status) {
            $(self.ELEMENT_INPUT_PRODUCT_NAME).prop('disabled', $status);
            $(self.ELEMENT_BOX_PRODUCTS_ID + ' input').prop('disabled', $status);
        }
    }
    var formSelectProduct = new modelFormSelectProduct();

    $(document).ready(function () {
        formSelectProduct.init();
        $('body').on('click', formSelectProduct.ELEMENT_SELECT_ALL_PRODUCTS, function () {
            formSelectProduct.disableProductList($(this).prop("checked"));
        });
        //удаление обычных товаров
        $('form').on('click', formSelectProduct.ELEMENT_BOX_PRODUCTS_ID + ' .fa-minus-circle', function () {
            var product_id = $(this).siblings('input').val();
            $(this).parent().remove();
            formSelectProduct.removeProductFromBox(product_id);
        });
        //удаление игнорируемых товаров
        $('form').on('click', formSelectProduct.ELEMENT_BOX_IGNORE_PRODUCTS_ID + ' .fa-minus-circle', function () {
            var product_id = $(this).siblings('input').val();
            $(this).parent().remove();
            formSelectProduct.removeIgnoreProductFromBox(product_id);
        });
    });

</script>