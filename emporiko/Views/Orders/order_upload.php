<?= form_open_multipart($url_ordupl, ['id'=>'id_ordertile_uplform'], []) ?>
    <?php if (!empty($customers)) :?>
    <div class="form-group">
        <label for="id_ordertile_uplcustomer" class="form-label">
            <?= lang('orders.ol_cusacc') ?>
        </label>
        <?= $customers->render() ?>
    </div>
    <?php endif?>
    <div class="form-group">
        <label for="id_ordertile_uplref" class="form-label">
            <?= lang('orders.ord_ref_tile') ?>
        </label>
        <input type="text" name="reference" class="form-control" id="id_ordertile_uplref"<?=!empty($ordernr) ? ' value="'.$ordernr.'"' : ''?>>
    </div>
    <div class="form-group">
        <label for="id_orderfile" class="form-label">
            <?= lang('orders.ord_orderfile') ?>
        </label>
        <?= \EMPORIKO\Controllers\Pages\HtmlItems\UploadField::create()->setArgs(['name'=>'parts_file','id'=>'id_ordertile_uplfile','accept'=>'.xlsx'])->render() ?>
        <small><?php $lng=lang('orders.ord_orderfile_tooltip'); echo $lng!='orders.ord_orderfile_tooltip' ? $lng : '' ?></small>
    </div>
    <button type="button" class="btn btn-success float-right" form="id_ordertile_uplform">
        <i class="far fa-save mr-1"></i><?= lang('system.buttons.submit') ?>
    </button>
</form>
<script>
$('[form="id_ordertile_uplform"]').on('click',function(){
    $('#id_ordertile_uplfile_input,#id_ordertile_uplref').removeClass('border-danger');
    
    if ($('#id_ordertile_uplref').val().length < 3){
        Dialog('<?= lang('orders.error_orderupload_refempty') ?>','warning');
        $('#id_ordertile_uplref').addClass('border-danger');
    }else
    if ($('#id_ordertile_uplfile_input').val().length < 3){
        Dialog('<?= lang('orders.error_order_uploadorder_file') ?>','warning');
        $('#id_ordertile_uplfile_input').addClass('border-danger');
    }else{
        $('#id_ordertile_uplform').submit();
    }
});    
</script>    