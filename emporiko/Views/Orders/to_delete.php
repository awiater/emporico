<?= $this->extend('System/Table/table_index') ?>

<?= $this->section('table_toolbar') ?>
    <?= $currentView->IncludeView('System/Table/table_toolbar',['_table_filters'=>$currentView->IncludeView('System/Table/table_filters')]); ?>
<?= $this->endSection() ?>

<?= $this->section('table_body') ?>

<?= form_open('',['id'=>$table_view_datatable_id.'_form'],['model'=>empty($_tableview_model) ? '' : $_tableview_model]) ?>
<table class="<?= $_table_class ?>" id="<?= $table_view_datatable_id?>">
    <thead>
        <tr>
        <?php if($_multiedit_column && !$currentView->isMobile()) :?>
            <td style="width:35px;">
                <input type="checkbox" value="" id="<?= $table_view_datatable_id?>_sel_all" onclick="$('input[name*=\'<?= $_record_key?>\']').prop('checked', this.checked);">
            </td>
        <?php endif ?>   
        <?php foreach($_data_cols as $key=>$value) :?>
            <td>
                <?php if (is_array($value) && array_key_exists('label', $value)) :?>
                    <b><?= $value['label'] ?></b>
                    <?php if($_data_sorting) :?>
                    <a href="<?= url($_tableview_filters_url,null,[],['orderby'=>$key]) ?>" class="ml-1">
                        <i class="fas fa-caret-up"></i>
                    </a>
                    <a href="<?= url($_tableview_filters_url,null,[],['orderby'=>$key.' DESC']) ?>" class="ml-1">
                        <i class="fas fa-caret-down"></i>
                    </a>
                    <?php endif?>    
                <?php else :?>
                    <b><?= $value?></b>
                <?php endif ?>
            </td>
        <?php endforeach; ?>
            <?php if (!empty($_edit_column) && is_array($_edit_column) && count($_edit_column)>0) :?>
            <td></td>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($_tableview_data as $row) :?>
        <tr <?= intval($row['enabled'])==0 || strlen($row['ord_cancelref']) > 0? 'class="bg-danger disabled"' : '' ?>>
            <?php if($_multiedit_column && !$currentView->isMobile()) :?>
            <td style="width:35px;">
                <input type="checkbox" name="<?=$_record_key?>[]" value="<?=$row[$_record_key]?>">
            </td>
            <?php endif ?>
            <?php foreach ($_data_cols as $key=>$value) :?>
            <td>
                <?php if (array_key_exists($key, $row)) :?>
                    <?= $currentView->parseValue($row[$key],$value); ?>
                <?php endif ?>
            </td>
            <?php endforeach; ?>
            <?php if (!empty($_edit_column) && is_array($_edit_column) && count($_edit_column)>0) :?>
            <td class="text-right" style="max-width:150px;">
                <?php if ($edit_acc) :?>
                <button type="button" class="mr-1 btn btn-primary edtBtn btn-sm" data-toggle="tooltip" data-placement="left" title="<?= lang('system.buttons.edit_details')?>" data-url="<?= str_replace('-id-',$row['ordid'],$url_edit) ?>">
                    <i class="fa fa-edit"></i>
                </button>
                
                <!-- Order Send in Email Button -->
                    <button type="button" class="mr-1 btn btn-dark btn-sm" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.btn_ordersendinemail')?>" data-sendorder="<?= $row['ordid']?>">
                        <i class="fas fa-envelope-open-text"></i>
                    </button>
                <!-- /Order Send in Email Button -->
                <?php else :?>
                <button type="button" class="mr-1 btn btn-primary edtBtn btn-sm" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.'.(!$isquote ? 'btn_editlines' : 'btn_showquote'))?>" data-url="<?= str_replace('-id-',$row['ordid'],$url_edit) ?>">
                    <i class="fas fa-list"></i>
                </button>
                <?php endif ?>
                
                <?php if (intval($row['enabled'])==1) :?>
                    <?php if ($edit_acc) :?>
                    <button type="button" class="mr-1 btn btn-success edtBtn btn-sm" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.'.(!$isquote ? 'btn_orderdown' : 'btn_quotedownxlsx'))?>" data-url="<?= str_replace('-id-',$row['ordid'],$url_download) ?>" data-noloader="true">
                        <i class="fas fa-file-csv"></i>
                    </button>
                
                    
                    <?php else :?>
                        <?php if(strlen($row['ord_cancelref'])==0 && !$isquote) :?>
                        <button type="button" class="btn btn-sm btn-danger mr-2" data-placement="top" title="<?= lang('orders.btn_ordercancel') ?>" data-toggle="tooltip" data-cancel="<?= $row['ordid'] ?>">
                            <i class="far fa-trash-alt"></i>
                        </button>
                        <?php endif ?>
                    <?php if (!empty($row['ord_invoicenr']) && strlen($row['ord_invoicenr']) > 2  && intval($row['ord_paid'])==0 && strlen($row['ord_cancelref']) < 1):?>
                        <button type="button" class="btn btn-sm btn-warning" data-placement="top" title="<?= lang('orders.btn_invoice_down') ?>" data-toggle="tooltip" data-url="<?= str_replace('-id-',$row['ordid'],$url_downloadinvoice) ?>" data-noloader="true">
                            <i class="<?= $edit_acc ? 'fas fa-file-download':'fas fa-file-invoice-dollar'?>"></i>
                        </button>    
                        <button type="button" class="btn btn-sm btn-primary" data-placement="top" title="<?= lang('orders.btn_pyamentinfo') ?>" data-toggle="tooltip" data-invoice="<?= $row['ord_invoicenr'] ?>">
                            <i class="fas fa-money-check-alt"></i>
                        </button>
                    <?php endif ?>
                    <button type="button" class="mr-1 btn btn-success edtBtn btn-sm" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.'.(!$isquote ? 'btn_orderdown' : 'btn_quotedown').($edit_acc ? '':'xlsx'))?>" data-url="<?= str_replace('-id-',$row['ordid'],$url_downloadxlsx) ?>" data-noloader="true">
                        <i class="fas fa-file-excel"></i>
                    </button>
                    <?php endif ?>
                
                    <?php if (!empty($url_downloadapi) && $edit_acc && intval($row['ord_isquote'])==1) :?>
                        <button type="button" class="mr-1 btn btn-dark edtBtn btn-sm" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.btn_orderdownauto')?>" data-url="<?= str_replace('-id-',$row['ordid'],$url_downloadapi) ?>">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    <?php endif ?>
                <?php elseif ($edit_acc) :?>
                
                    <!-- Order Confirm Button -->
                    <?php if ($row['lines_qty'] > 0) :?>
                    <button type="button" class="mr-1 btn btn-warning edtBtn btn-sm" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.btn_orderconfirm')?>" data-conforder="<?= $row['ordid']?>">
                        <i class="fas fa-share-square"></i>
                    </button>
                    <?php else :?>
                    <button type="button" class="mr-1 btn btn-warning edtBtn btn-sm" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.btn_ordervalidate')?>" data-url="<?= str_replace('-id-',$row['ordid'],$url_check) ?>" data-noloader="true">
                        <i class="fas fa-check-square"></i>
                    </button>
                    <?php endif ?>
                    <!-- /Order Confirm Button -->
                    
                <?php endif ?>
            </td>
            <?php endif ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</form>
<?php if(!empty($_tableview_pagination)) :?>
<div class="d-flex" id="<?= $table_view_datatable_id?>_pagination">
    <?= str_replace('<nav>', '<nav class="ml-auto">', $_tableview_pagination) ?>
</div>
<?php endif ?>
<?php if(!empty($_uploadform) && is_array($_uploadform) && EMPORIKO\Helpers\Arrays::KeysExists(['button_id','driver'], $_uploadform)) :?>
<?= form_dataupload($_uploadform['driver'], null, $_uploadform) ?>
<?php endif ?>

<?php if ($edit_acc) :?>
<!-- Order Invoice Modal -->
<?= $currentView->includeView('Orders/orders_invoice') ?>
<!-- / Order Invoice Modal -->
<?php else :?>  
<!-- Payment Info Modal -->
<?= $currentView->includeView('Orders/payement_ref') ?>
<!-- / Payment Info Modal -->

<!-- Order Cancel Modal -->
<?= $currentView->includeView('Orders/order_cancel') ?>
<!-- / Order Cancel Modal -->

<!-- Order Upload Modal -->
<?= $currentView->includeView('Orders/order_upload') ?>
<!-- / Order Upload Modal -->
<?php endif ?>

<!-- Order Confirm Modal -->
<?= $currentView->includeView('Orders/order_confirm') ?>
<!-- /Order Confirm Modal -->

<?= $this->endSection() ?>

<?= $this->section('table_script') ?>
<script>
    $(function(){
        $.applyDataMask();
    });
   
    $('button[data-invoice]').on('click',function(){
        <?php if ($edit_acc) :?>
         $('input[name="order"]').val($(this).attr('data-invoice'));
         $('#id_invoicenr, #id_invoicevalue').val('');       
         $('#id_orderlines_invoice_modal').modal('show');      
        <?php else :?>        
        $('input[name="invoicenr"]').val($(this).attr('data-invoice'));
        $('#id_paidref, #id_paidvalue').val('');
        $('#id_orderlines_payment_modal').modal('show');
        <?php endif ?>
    });
    
    $('button[data-cancel]').on('click',function(){
        $('input[name="order"]').val($(this).attr('data-cancel'));
        $('textarea[name="reason"]').text('');
        $('#id_orderlines_cancel_modal').modal('show');
    });
    
    $('button[data-new]').on('click',function(){
       $('input[name="ordernr"]').val('<?= $cusordernr?>'); 
        $('#id_orderlines_upload_modal').modal('show');
    });
    
    
    <?= $currentView->IncludeView('System/Table/table_script',['table_script_nodef'=>TRUE,'table_noscript'=>TRUE]); ?>
</script>
<?= $this->endSection() ?>