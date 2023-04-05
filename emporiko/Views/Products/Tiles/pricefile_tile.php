<div class="card card-primary" id="price_file_tile">
    <div class="card-header">
        <h3 class="card-title"><?= lang('products.pricefile_header') ?></h3>
    </div>
    <div class="card-body">
        <div class="form-group col-12">
            <label for="products_tile_pricefile_list">
                    <?= lang('products.pricefile_label'.($iscust ? '' :'_emp')) ?>
                </label>
            <?php if (!$iscust) :?>
            <div class="form-group">
                <?= form_dropdown('acc',$customers,[],['class'=>'form-control select2','id'=>'products_tile_pricefile_customer']) ?>
            </div>
            <?php endif ?>
            <div class="input-group mb-3">
                <?= form_dropdown('brand', $brands, [], ['class'=>'form-control select2','id'=>'products_tile_pricefile_list']) ?>
                <div class="input-group-append">
                    <button type="button" class="btn btn-dark" form="products_tile_pricefile_form" id="products_tile_pricefile_submit">
                        <i class="fas fa-cloud-download-alt"></i>
                    </button>
                </div>
            </div>
           
        </div>
    </div>
</div>
<script>
    $('#products_tile_pricefile_submit').on('click',function(){
        addLoader('#price_file_tile');
        var url='<?= $action?>';
        var fname=$('#products_tile_pricefile_list').find(':selected');
        url=url.replace('-brand-',fname.val());
        <?php if (!$iscust) :?>
        url=url.replace('-acc-',$('#products_tile_pricefile_customer').find(':selected').val());
        <?php endif ?>
        fname=fname.text();
        fname=fname+'_<?= formatDate()?>.xlsx';
        var req = new XMLHttpRequest();
        req.open("GET", url, true);
        req.responseType = "blob";
        req.onload = function (event) {
            var blob = req.response;
            var fileName = req.getResponseHeader("fileName") //if you have the fileName header available
            var link=document.createElement('a');
            link.href=window.URL.createObjectURL(blob);
            link.download=fname;
            link.click();
            killLoader();
        };
        req.send();
      });
</script>
