<div class="modal" tabindex="-1" role="dialog" id="id_orderlines_cancel_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('orders.btn_ordercancel') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open($url_ordcanc, ['id'=>'id_orderlines_cancel_modal_form'], ['order'=>!empty($record['ordid']) ? $record['ordid'] : '']) ?>
                <div class="form-group">
                    <label for="id_paidvalue" class="form-label">
                        <?= lang('orders.'.(!empty($record) && intval($record['ord_isquote'])==1 ? 'quote_cancelreason': 'ord_cancelreason')) ?>
                    </label>
                    <textarea name="reason" cols="5" rows="3" class="form-control"></textarea>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger mr-auto" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?=lang('system.buttons.close') ?>
                </button>
                <button type="submit" class="btn btn-success" form="id_orderlines_cancel_modal_form">
                    <i class="far fa-save mr-1"></i><?= lang('system.buttons.submit') ?>
                </button>  
            </div>
        </div>
    </div>
</div>