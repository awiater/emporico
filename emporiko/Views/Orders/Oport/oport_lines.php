<div class="col-12" id="print_container">
    <?php if (array_key_exists('ord_done',$record) && intval($record['ord_done'])==0) :?>
        <?php if (array_key_exists('ord_status',$record) && $record['ord_status']=='lost') :?>
            <?= $currentView->getErrorBar('orders.opportunities_msg_cancel_bar','danger',TRUE); ?>
        <?php elseif (array_key_exists('ord_status',$record) && $record['ord_status']=='win') :?>
            <?= $currentView->getErrorBar(lang('orders.opportunities_msg_win_bar',[empty($urlquote) ? '' : $urlquote]),'success',TRUE); ?>
        <?php endif ?>
    <?php endif ?>
    <div class="card">
        <div class="card-header font-weight-bold"><?= lang('orders.opportunities_editrecord') ?></div>
        <div class="card-footer">
            <?php if (!empty($_menubar)) :?>
            <div class="breadcrumb p-1"><?= $_menubar ?></div>
            <?php endif ?>
            <div class="row">
                <div class="col-xs-12 col-md-4">
                    <div class="card card-outline card-info">
                        <div class="card-body">
                            <!-- Our Reference -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_ref') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="form-control text-right">
                                        <?= $record['ord_ref'] ?>
                                    </div>
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
                <div class="col-xs-12 col-md-4">
                    <div class="card card-outline card-info">
                        <div class="card-body">
                            <!-- Source -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_source') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="form-control text-right">
                                        <?= array_key_exists($record['ord_source'], $sources) ? $sources[$record['ord_source']] : lang('orders.opportunities_source_cust') ?>
                                    </div>
                                </div>
                            </div>
                            <!-- / Source -->
                            <!-- Status -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_stage') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="form-control text-right">
                                        <?= array_key_exists($record['ord_status'], $statuses) ? $statuses[$record['ord_status']] : lang('orders.opportunities_stage_prop') ?>
                                    </div>
                                </div>
                            </div>
                            <!-- / Status -->
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
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="card card-outline card-info">
                        <div class="card-body">
                            <!-- Total Customer Value -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_cus_value') ?>
                                </label>
                                <div class="col-sm-8">
                                    <div class="form-control d-flex">
                                        <p><?= $record['ord_cus_curr'] ?></p>
                                        <p class="ml-auto">
                                            <?= strlen($record['ord_cus_value']) > 0 ? number_format($record['ord_cus_value'], 2, '.', '') : 0 ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- / Total Customer Value -->
                            <!-- Total Our Value -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">
                                    <?= lang('orders.opportunities_our_value') ?>
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
            <div class="card card-outline">
                <div class="card-body p-1">
                    <?= $fields['tab_parts']['parts']['value'] ?>
                </div>
            </div>
            
            <div class="table-fixed d-none">   
            <table class="table table-striped" id="oport_partstable">
                <thead class="card-header">
                    <tr>
                        <th scope="col" style="width:17%;vertical-align: middle!important;"><?= lang('products.prd_brand') ?></th>
                        <th scope="col" style="width:25%;vertical-align: middle!important;"><?= lang('products.prd_apdpartnumber') ?></th>
                        <th scope="col" style="width:34%;vertical-align: middle!important;"><?= lang('products.prd_description') ?></th>
                        <th scope="col" style="width:7%;vertical-align: middle!important;text-align: right;"><?= lang('orders.opportunities_qty') ?></th>
                        <th scope="col" style="width:10%;vertical-align: middle!important;text-align: right;"><?= lang('orders.opportunities_value').($showourcost ? '<br>('.lang('orders.opportunities_rvalue').')' : '') ?></th>
                        <th scope="col" style="width:7%;vertical-align: middle!important;text-align: right;"><?= lang('orders.total') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($record['parts'] as $part) :?>
                    <tr class="bg-white border-bottom">
                        <td><?= $part['prd_brand']?></td>
                        <td><?= $part['prd_apdpartnumber']?></td>
                        <td><?= $part['prd_description']?></td>
                        <td class="text-right"><?= $part['qty']?></td>
                        <td class="text-right"><?= $part['value'].($showourcost ? '<br>('.$part['rvalue'].')' : '')?></td>
                        <td class="text-right">
                            <?= number_format(intval($part['qty']) * floatval($part['value']), 2, '.', '') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>    
        </div>
               
    </div>
</div>
<?php if (array_key_exists('ord_done',$record) && intval($record['ord_done'])==0) :?>
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