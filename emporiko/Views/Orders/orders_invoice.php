<div class="modal" tabindex="-1" role="dialog" id="id_orderlines_invoice_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('orders.btn_invoice') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open_multipart($url_invoiceupl, ['id'=>'id_orderlines_invoice_modal_form'], ['order'=>!empty($record['ordid']) ? $record['ordid'] : '']) ?>
                <div class="form-group">
                    <label for="id_invoicenr" class="form-label">
                        <?= lang('orders.ord_invoicenr') ?>
                    </label>
                    <input type="text" name="invoicenr" class="form-control" id="id_invoicenr">
                </div> 
                <div class="form-group">
                    <label for="id_invoicevalue" class="form-label">
                        <?= lang('orders.ord_invoicevalue') ?>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                        </div>
                        <input type="text" name="invoicevalue" class="form-control" id="id_invoicevalue" data-mask-reverse="true" data-mask="00000000.00">
                    </div>
                </div>
                <div class="form-group">
                    <label for="id_invoicefile" class="form-label">
                        <?= lang('orders.ord_invoicefile') ?>
                    </label>
                    <?= \EMPORIKO\Controllers\Pages\HtmlItems\UploadField::create()->setArgs(['name'=>'invoicefile','id'=>'id_invoicefile','accept'=>'application/pdf'])->render() ?>
                    
                </div> 
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger mr-auto" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?=lang('system.buttons.close') ?>
                </button>
                <button type="submit" class="btn btn-success" form="id_orderlines_invoice_modal_form">
                    <i class="far fa-save mr-1"></i><?= lang('system.buttons.submit') ?>
                </button>  
            </div>
        </div>
    </div>
</div>