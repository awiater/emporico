<div class="col-12" id="print_container">
    <?php if(intval($record['ord_done'])==1) :?>
    <div class="alert alert-danger" role="alert"><?=lang('orders.msg_order_disabled')?></div>
    <?php endif ?>
    <div class="card" id="id_orders_line_container">
        <div class="card-header"></div>
        <div class="card-body">
            <div class="breadcrumb p-1"><?= $_menubar ?></div>
            <div id="id_quote_info">
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <!-- Reference -->
                        <div class="card card-outline card-info">
                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">
                                        <?= lang('orders.ord_ref') ?>
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control text-right">
                                            <?= $record['ord_ref'] ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">
                                        <?= lang('orders.ord_refcus') ?>
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control text-right">
                                            <?= $record['ord_refcus'] ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">
                                        <?= lang('orders.ord_status') ?>
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control text-right">
                                            <?= is_array($order_status) && array_key_exists($record['ord_status'], $order_status) ? $order_status[$record['ord_status']] : ''?>
                                        </div>
                                    </div>
                                </div>
                                <?php if(intval($record['ord_done'])==1) :?>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">
                                        <?= lang('orders.ord_cancelref_quote_note') ?>
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="border rounded p-1"<?=strlen($record['ord_cancelref']['note']) > 150 ? ' style="max-height:200px;overflow-y: scroll;"' : ''?>>
                                            <?= $record['ord_cancelref']['note'] ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif ?>
                            </div>
                        </div>
                        <!-- / Reference -->
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <!-- Dates -->
                        <div class="card card-outline card-info">
                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">
                                        <?= lang('orders.ord_addon') ?>
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control d-flex">
                                            <i class="far fa-calendar-alt mt-1"></i>
                                            <p class="ml-auto">
                                                <?= strlen($record['ord_addon']) > 0 ? convertDate($record['ord_addon'] ,null, 'date') : ''?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">
                                        <?= lang('orders.order_value') ?>
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control d-flex">
                                            <?= $record['order_curr'] ?>
                                            <p class="ml-auto">
                                                <?= $record['order_value']?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if(intval($record['ord_done'])==1) :?>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">
                                        <?= lang('orders.ord_doneon_quote') ?>
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control d-flex">
                                            <i class="far fa-calendar-alt mt-1"></i>
                                            <p class="ml-auto">
                                                <?= strlen($record['ord_doneon']) > 0 ? convertDate($record['ord_doneon'] ,null, 'date') : ''?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label">
                                        <?= lang('orders.ord_cancelref_quote') ?>
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control d-flex">
                                            <i class="fas fa-user-tie mt-1"></i>
                                            <p class="ml-auto">
                                                <?= $record['ord_cancelref']['user']?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif ?>
                            </div>
                        </div>
                        <!-- / Dates -->
                    </div>
                </div>
            </div>
            <div id="id_quote_lines">
                <?= $currentView->includeView('Orders/products_lines') ?>
            </div>
        </div>
    </div>
</div>
<?php if (!empty($modal_cncurl)) :?>
<!-- Order Cancel Modal -->
<?= $currentView->includeView('Orders/cancel_modal',['modal_cnctitle'=>lang('orders.btn_ordercancel'),'modal_cncreaserror'=>lang('orders.error_quote_cancel_reason')]) ?>
<!-- / Order Cancel Modal -->
<?php endif ?>
<script>
    $('button[data-url]').on('click',function(){
        if($(this).attr('data-noloader')==undefined){
            addLoader();
        }
        var url=$(this).attr('data-url');
        if($(this).attr('data-msg')!=undefined){
             ConfirmDialog($(this).attr('data-msg'),function(){
                window.location=url 
             },function(){killLoader();});
        }else{
           window.location=url; 
        }
        //
    });
</script> 