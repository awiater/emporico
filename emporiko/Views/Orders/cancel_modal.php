<div class="modal" tabindex="-1" role="dialog" id="id_orders_cancel_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= !empty($modal_cnctitle) ? $modal_cnctitle : lang('orders.btn_ordercancel') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open($modal_cncurl, ['id'=>'id_orders_cancel_modal_form']) ?>
                <?php if (!empty($modal_cncorder)) :?>
                <input type="hidden" name="order" value="<?= $order_nr?>">
                <?php endif ?>
                <div class="form-group">
                    <label for="id_paidvalue" class="form-label">
                        <?= lang('orders.ord_cancelreason') ?>
                    </label>
                    <textarea name="reason" cols="5" rows="3" class="form-control"></textarea>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger mr-auto" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?=lang('system.buttons.close') ?>
                </button>
                <button type="button" class="btn btn-success" form="id_orders_cancel_modal_form">
                    <i class="far fa-save mr-1"></i><?= lang('system.buttons.submit') ?>
                </button>  
            </div>
        </div>
    </div>
</div>
<script>
    $('#id_orders_cancel_modal').on('hidden.bs.modal', function () {
        $('[name="reason"]').removeClass('border-danger').val('');
    });
    
    $('[form="id_orders_cancel_modal_form"]').on('click',function(){
        $('[name="reason"]').removeClass('border-danger');
        if ($('[name="reason"]').val().length < 10){
            Dialog('<?= !empty($modal_cncreaserror) ? $modal_cncreaserror : lang('orders.error_order_cancel_reason') ?>','warning');
            $('[name="reason"]').addClass('border-danger');
        }else{
            $('#id_orders_cancel_modal_form').submit();
        }
    });    
</script>

