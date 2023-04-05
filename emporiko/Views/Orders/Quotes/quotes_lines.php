<div class="col-12" id="print_container">
    <?php if (array_key_exists('ord_done',$record) && intval($record['ord_done'])==0) :?>
        <?php if (array_key_exists('ord_status',$record) && $record['ord_status']=='lost') :?>
            <?= $currentView->getErrorBar('orders.opportunities_msg_cancel_bar','danger',TRUE); ?>
        <?php elseif (array_key_exists('ord_status',$record) && $record['ord_status']=='win') :?>
            <?= $currentView->getErrorBar(lang('orders.opportunities_msg_win_bar',[empty($urlquote) ? '' : $urlquote]),'success',TRUE); ?>
        <?php endif ?>
    <?php endif ?>
    <?= form_open($urlsave,['id'=>''],['ordid'=>$record['ordid']]) ?>
    <div class="card">
        <div class="card-header font-weight-bold"><?= lang('orders.quotes_editrecord') ?></div>
        <div class="card-footer">
            <?php if (!empty($_menubar)) :?>
            <div class="breadcrumb p-1"><?= $_menubar ?></div>
            <?php endif ?>
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <div class="card card-outline card-info">
                        <div class="card-body">
                            <!-- Our Reference -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_ref') ?>
                                </label>
                                <div class="col-sm-8">
                                    <?php if ($editable) :?>
                                    <input type="text" name="ord_ref" value="<?= $record['ord_ref'] ?>" class="form-control" dir="rtl">
                                    <?php else :?>
                                    <div class="form-control text-right">
                                        <?= $record['ord_ref'] ?>
                                    </div>
                                    <?php endif ?>
                                </div>
                            </div>
                            <!-- / Our Reference -->
                            
                            <!-- Customer Reference -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_refcus') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="form-control text-right">
                                        <?= $record['ord_refcus'] ?>
                                    </div>
                                </div>
                            </div>
                            <!-- / Customer Reference -->
                            
                            <!-- Customer Account -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_cusacc') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="border rounded p-1 text-right">
                                        <?= $record['ord_cus_name'] ?>&nbsp;(<?= $record['ord_cusacc'] ?>)
                                    </div>
                                </div>
                            </div>
                            <!-- / Customer Account -->
                            <?php if (strlen($record['ord_cancelref']) > 0) :?>
                            <!-- Cancel Ref -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_cancelref') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="border rounded p-1 text-right">
                                        <?= $record['ord_cancelref'] ?>
                                    </div>
                                </div>
                            </div>
                            <!-- / Cancel Ref -->
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <div class="card card-outline card-info">
                        <div class="card-body">
                            
                            <!-- Add Date -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_addon') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="form-control text-right">
                                        <?= strlen($record['ord_addon']) > 0 ? convertDate($record['ord_addon'], null,'d M Y') : '' ?>
                                    </div>
                                </div>
                            </div>
                            <!-- / Add Date -->
                            <?php if ($edit_acc) :?>
                            <!-- Creator -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_addby') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="form-control text-right">
                                        <?= $record['ord_addby'] ?>
                                    </div>
                                </div>
                            </div>
                            <!-- / Creator -->
                            <?php endif ?>
                            
                            <?php if (intval($record['ord_done'])==1) :?>
                            <!-- Done Date -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_doneon') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="form-control text-right">
                                        <?= strlen($record['ord_doneon']) > 0 ? convertDate($record['ord_doneon'], null,'d M Y') : '' ?>
                                    </div>
                                </div>
                            </div>
                            <!-- / Done Date -->
                            <?php endif ?>
                             <!-- Total Our Value -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.quotes_value') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="form-control d-flex">
                                        <p><?= $record['ord_cus_curr'] ?></p>
                                        <p class="ml-auto">
                                            <?= strlen($record['ord_our_value']) > 0 ? number_format($record['ord_our_value'], 2, '.', '') : 0 ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- / Total Our Value -->
                        </div>
                    </div>
                </div>
            </div>
            <?php if(!empty($record['parts']) && is_array($record['parts']) && array_key_exists('url', $record['parts']) && array_key_exists('type', $record['parts']) && $record['parts']['type']=='pdf') :?>
            <div class="col-xs-12">
                <div class="card card-outline card-info">
                    <div class="card-header"></div>
                    <div class="card-body p-1">
                        <div class="embed-responsive embed-responsive-21by9">
                            <iframe class="embed-responsive-item" src="<?= $record['parts']['url'] ?>"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <?php elseif (!empty($record['parts']) && is_array($record['parts']) && !EMPORIKO\Helpers\Arrays::isAssoc($record['parts'])):?>
            <div class="table-fixed">
            <table class="table table-striped" id="oport_partstable">
                <thead class="card-header">
                    <tr>
                        <th scope="col" style="width:17%;vertical-align: middle!important;"><?= lang('products.prd_brand') ?></th>
                        <th scope="col" style="width:25%;vertical-align: middle!important;"><?= lang('products.prd_apdpartnumber') ?></th>
                        <th scope="col" style="width:30%;vertical-align: middle!important;"><?= lang('products.prd_description') ?></th>
                        <th scope="col" style="width:7%;vertical-align: middle!important;text-align: right;"><?= lang('orders.opportunities_qty') ?></th>
                        <?php if ($record['is_error']) :?>
                        <th scope="col" style="width:7%;vertical-align: middle!important;text-align: center;"><?= lang('orders.ol_cusprice')?></th>
                        <th scope="col" style="width:7%;vertical-align: middle!important;text-align: right;"><?= lang('orders.ol_price')?></th>
                        <?php else :?>
                        <th scope="col" style="width:14%;vertical-align: middle!important;text-align: right;"><?= lang('orders.ol_price')?></th>
                        <?php endif ?>
                        <th scope="col" style="width:7%;vertical-align: middle!important;text-align: right;"><?= lang('orders.total') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($record['parts'] as $key=>$part) :?>
                    <tr class="bg-white border-bottom">
                        <td>
                            <?php if (!empty($part['ol_iserror']) && $part['ol_iserror'] > 0) :?>
                            <i class="fas fa-exclamation-triangle mr-2 text-danger" data-toggle="tooltip" data-placement="right" title="<?= lang('orders.error_quotes_pricingnotmatch')?>"></i>
                            <?php endif ?>
                            <?= $part['ol_partbrand'] ?>
                        </td>
                        <td><?= $part['ol_ourpart'] ?></td>
                        <td><?= $part['ol_partdesc'] ?></td>
                        <td class="text-right">
                            <?php if ($editable) :?>
                            <input type="hidden" name="parts[<?=$key?>][olid]" value="<?= $part['olid'] ?>">
                            <input type="number" name="parts[<?=$key?>][ol_qty]" value="<?= $part['ol_qty'] ?>" class="form-control form-control-sm" dir="rtl" step="1" min="1">
                            <?php else :?>
                            <?= $part['ol_qty'] ?>
                            <?php endif ?>
                        </td>
                        <?php if ($part['ol_iserror'] > 0) :?>
                        <td class="text-right text-danger">
                            <?= $part['ol_cusprice'] ?>
                        </td>
                        <?php endif ?>
                        <td class="text-right">
                            <?php if ($editable) :?>
                            <input type="text" name="parts[<?=$key?>][ol_price]" value="<?= $part['ol_price'] ?>" class="form-control form-control-sm" dir="rtl">
                            <?php else :?>
                            <?= $part['ol_price'] ?>
                            <?php endif ?>
                        </td>
                        <td class="text-right">
                            <?= number_format(intval($part['ol_qty'])*(floatval($part['ol_price'])),2, '.', '') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                </tbody>
            </table>
            </div>
            <?php endif ?>
            <?php if ($editable) :?>           
            <button class="btn btn-success float-right" type="submit" form="orders_details_form">
                <i class="fas fa-save mr-1"></i><?= lang('system.buttons.save') ?>
            </button>
            <?php endif ?>
        </div>
               
    </div>
    </form>
</div>
<?php if (!empty($record['_email']) && is_array($record['_email'])) :?>
<!-- Send to Customer Modal -->
<div class="modal" tabindex="-1" role="dialog" id="quotes_sendemailmodal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header p-1 pr-2">
                <h5 class="modal-title"><?= lang('orders.quotes_sendemail_modal_title')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close')?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open($url_sendemail, ['id'=>'quotes_sendemailmodal_form'], ['record'=>$record['ord_ref']]) ?>
                    <?= $currentView->includeView('Emails/compose',['record'=>$record['_email'],'_external_call'=>TRUE]); ?>
                <?= form_close(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm mr-auto" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel')?>
                </button>
                <button type="submit" class="btn btn-success btn-sm" form="quotes_sendemailmodal_form">
                    <i class="fas fa-save mr-1"></i></i><?= lang('system.buttons.submit')?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- / Send to Customer Modal -->
<?php endif ?>


<?php if (intval($record['ord_done'])==0) :?>
<div class="modal" tabindex="-1" role="dialog" id="oport_cancelmodal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('orders.opportunities_msg_cancel')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close')?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open($urlcancel, ['id'=>'oport_cancelmodal_form'], []) ?>
                    <div class="form-group">
                        <label for="oport_cancelmodal_comment"><?= lang('orders.opportunities_cancelref')?></label>
                        <textarea id="oport_cancelmodal_comment" name="comment" rows="5" cols="10" class="form-control"></textarea>
                    </div>
                <?= form_close(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm mr-auto" id="oport_cancelmodal_btncancel">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel')?>
                </button>
                <button type="button" class="btn btn-success btn-sm" id="oport_cancelmodal_btnsave">
                    <i class="fas fa-save mr-1"></i></i><?= lang('system.buttons.submit')?>
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif ?>
<script>
<?php if (array_key_exists('ord_done',$record) && intval($record['ord_done'])==0) :?>
    $('#oport_cancelmodal_btncancel').on('click',function(){
        $('#oport_cancelmodal').modal('hide');
        $('#oport_cancelmodal_comment').val('');
    });
    
    $('#oport_cancelmodal_btnsave').on('click',function(){
        if ($('#oport_cancelmodal_comment').val().length < 2){
           Dialog('<?= lang('orders.opportunities_error_invalidcomment')?>','warning'); 
        }else{
            $('#oport_cancelmodal_form').submit();
        }
    });
<?php endif ?>    
$('button[data-conf]').on('click',function(){
    var url=$(this).attr('data-conf');
    var mode=$(this).attr('data-mode');
    if (mode=='win'){
        ConfirmDialog('<?= lang('orders.opportunities_msg_convert')?>',function(){
            addLoader();
            window.location=url;
        });
    }else if (mode=='neg'){
        addLoader();
        window.location=url;
    }
});
</script>
<?= dump($record,FALSE);?>