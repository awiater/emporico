
<div class="modal" tabindex="-1" role="dialog" id="id_orderlines_payment_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('orders.btn_pyamentinfo') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="id_orderlines_payment_modal_body">
                <?= form_open($url_payment, ['id'=>'id_orderlines_payment_modal_form'], ['invoicenr'=>!empty($record['ord_invoicenr']) ? $record['ord_invoicenr'] : '']) ?>
                <div class="form-group">
                    <label for="id_paidref" class="form-label">
                        <?= lang('orders.ord_paidref') ?>
                    </label>
                    <input type="text" name="paidref" id="id_paidref" maxlength="250" class="form-control">
                </div>
                <div class="form-group">
                    <label for="id_paidvalue" class="form-label">
                        <?= lang('orders.ord_paidvalue') ?>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                        </div>
                        <input type="text" name="paidvalue" class="form-control" id="id_paidvalue" data-mask-reverse="true" data-mask="00000000.00">
                    </div>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger mr-auto" onclick="dismiss_orderlines_payment_modal()">
                    <i class="fas fa-ban mr-1"></i><?=lang('system.buttons.cancel') ?>
                </button>
                <button type="button" class="btn btn-success" onclick="submit_orderlines_payment_modal()">
                    <i class="far fa-save mr-1"></i><?= lang('system.buttons.submit') ?>
                </button>  
            </div>
        </div>
    </div>
</div>
<script>
function dismiss_orderlines_payment_modal(){
    $('#id_orderlines_payment_modal').modal('hide');
    $('[name="paidvalue"],[name="paidref"]').val('');
}

function submit_orderlines_payment_modal(){
    var ref=$('[name="paidref"]').val();
    var val=$('[name="paidvalue"]').val();
    $('[name="paidref"],[name="paidvalue"]').removeClass('border border-danger');
    if (ref.length < 2){
       Dialog('<?= lang('orders.error_payref_error')?>','warning');
       $('[name="paidref"]').addClass('border border-danger');
    }else
    if (($.isNumeric(val) && parseFloat(val) < 0.1) || !$.isNumeric(val)){
       Dialog('<?= lang('orders.error_payval_error')?>','warning');
       $('[name="paidvalue"]').addClass('border border-danger');
    }else{
        addLoader('#id_orderlines_payment_modal_body');
        $('#id_orderlines_payment_modal_form').submit();
    } 
}
</script>