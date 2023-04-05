<div class="row col-xs-12 col-md-5 mx-auto">
    <div class="card w-100">
        <div class="card-header"></div>
        <div class="card-body">
            <div class="" id="products_pricefile_download_error">
            <?= !empty($error_msg) ? $error_msg : '' ?>
            </div>    
            <?= form_open_multipart($url,['method'=>'get','id'=>'products_pricefile_download_form'],[]); ?>
                <div class="form-group" id="id_brand_field">
                    <label for="id_brands" class="mr-2">
                        <?= lang('products.prd_brand')?><b class="ml-1 text-danger">*</b>
                    </label>
                    <?php if (!empty($brand) && $brand!=null && is_string($brand) && strlen($brand) > 0) :?>
                    <input type="hidden" value="<?=$brand ?>" name="brand" class="form-control" readonly="true">
                    <?php else :?>
                    <?= form_dropdown('brand', $brands, [], ['class'=>'form-control select2','id'=>'id_brands','required'=>'true']) ?>
                    <?php endif ?>
                    <small id="id_pg_title_tooltip" class="form-text text-muted">
                    </small>
 		</div>
                
                <?php if (!empty($isemail) && is_bool($isemail) && $isemail) :?>
                <div class="form-group" id="id_brand_field">
                    <label for="id_email" class="mr-2">
                        <?= lang('products.pricefile_email')?><b class="ml-1 text-danger">*</b>
                    </label>
                    <input type="email" class="form-control" name="customer" id="id_email" required="true">
                    <small id="id_pg_title_tooltip" class="form-text text-muted">
                    </small>
 		</div>
                <?php else :?>
                <input type="hidden" value="<?=$file ?>" name="customer">
                <?php endif ?>
                <button type="button" form="products_pricefile_download_form" class="btn btn-primary float-right btn-sm">
                    <i class="fas fa-download mr-1"></i>
                    <?= lang('products.btn_download') ?>
                </button>
            <?= form_close() ?>
        </div>
    </div>
</div>
<script>
    <?php if (!empty($isemail) && is_bool($isemail) && $isemail) :?>
        $('#id_email').on('change',function(){
            $(this).removeClass('border-danger');
            if (!isEmail($(this).val())){
                $(this).addClass('border-danger');
                $('#products_pricefile_download_error').html(atob('<?= createErrorMessage('products.error_invalid_email','warning',TRUE) ?>'));
            }
        });
    <?php endif; ?>
    $('button[form="products_pricefile_download_form"]').on('click',function(){
        addLoader();
        var url=$('#products_pricefile_download_form').attr('action');
        var fname=$('#products_tile_pricefile_list').find(':selected');
        url+='?customer='+$('input[name="customer"]').val();
        url+='&brand='+$('[name="brand"]').find(':selected').val();
        
        fname=$('[name="brand"]').find(':selected').text();
        fname=fname+'_<?= formatDate()?>.xlsx';
        var req = new XMLHttpRequest();
        req.open("GET", url, true);
        req.responseType = "blob";
        req.onload = function (event) {
            var blob = req.response;
            var fileName = req.getResponseHeader("fileName") //if you have the fileName header available
            console.log(req);
            if (req.status=='403'){
                $('#products_pricefile_download_error').html(atob('<?= createErrorMessage('products.error_invalid_email_pricing','warning',TRUE) ?>'));
                $('#id_email').addClass('border-danger');
            }else{
                var link=document.createElement('a');
                link.href=window.URL.createObjectURL(blob);
                link.download=fname;
                link.click();
            }
            killLoader();
        };
        req.send();
      });
    
</script>