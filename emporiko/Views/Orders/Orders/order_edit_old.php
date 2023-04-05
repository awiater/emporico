<?= $currentView->includeView('System/form') ?>
<table class="table" id="id_ord_parts_list_table" style="background-color: #fafbfc!important;">
    <thead class="card-header">
        <tr>
            <th><?= lang('orders.ol_oepart') ?></th>
            <th><?= lang('orders.ol_ourpart') ?></th>
            <th><?= lang('orders.ol_qty') ?></th>
            <th><?= lang('orders.ol_price') ?></th>
            <?php if(!$record['_readonly']) :?>
            <th><?= lang('orders.ol_status') ?></th>
            <?php endif ?>
            <?php if(!$record['_readonly']) :?>
            <th></th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($record['parts'] as $key=>$line) :?>
        <tr <?=$line['ol_price']==0 ? 'class="bg-danger disabled"' : ''?> id="id_orderline_<?= $key ?>">
            <td>
                <input type="hidden" name="parts[<?= $key ?>][olid]" value="<?= $line['olid'] ?>" disabled="true">
                <input type="text" name="parts[<?= $key ?>][ol_oepart]" data-oldvalue="<?= $line['ol_oepart'] ?>" value="<?= $line['ol_oepart'] ?>" class="border-0 form-control form-control-sm" readonly="TRUE" <?=$line['ol_price']==0 ? 'style="color:#fff!important;background-color:#dc3545!important;opacity:.65;"' : ''?>>
            </td>
            <td>
                <input type="text" name="parts[<?= $key ?>][ol_ourpart]" data-oldvalue="<?= $line['ol_ourpart'] ?>" value="<?= $line['ol_ourpart'] ?>" class="border-0 form-control form-control-sm" readonly="TRUE" <?=$line['ol_price']==0 ? 'style="color:#fff!important;background-color:#dc3545!important;opacity:.65;"' : ''?>>
            </td>
            <td>
                <input type="number" name="parts[<?= $key ?>][ol_qty]" data-oldvalue="<?= $line['ol_qty'] ?>" value="<?= $line['ol_qty'] ?>" class="form-control border-0 form-control-sm" readonly="TRUE" <?=$line['ol_price']==0 ? 'style="color:#fff!important;background-color:#dc3545!important;opacity:.65;"' : 'style="width:80px"'?>>
            </td>
            <td id="parts[<?= $key ?>][ol_price]"><?= $line['ol_price'] ?></td>
            <?php if(!$record['_readonly']) :?>
            <td><?= $line['ol_status'] ?></td>
            <?php endif ?>
            <?php if(!$record['_readonly'] && $record['enabled']!=0 && strlen($record['ord_invoicenr']) < 1) :?>
            <td>
                <button type="button" class="btn btn-sm btn-primary" data-placement="top" data-toggle="tooltip" title="<?= lang('orders.btn_editline')?>a" data-editid="<?= $key ?>" data-edit="true">
                    <i class="fa fa-fa fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" data-placement="top" onclick="removeLineFromList('<?=$key?>',false)" data-toggle="tooltip" title="<?= lang('orders.btn_editline_delete')?>">
                    <i class="far fa-trash-alt"></i>
                </button>
                <div id="id_orderline_<?= $key ?>_toolbox" class="d-none bg-light">
                    <button type="button" class="btn btn-sm btn-danger" data-placement="top" data-toggle="tooltip" title="<?= lang('system.buttons.cancel')?>" data-editid="<?= $key ?>" data-edit="remove">
                        <i class="fas fa-ban"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-success" data-placement="top" data-toggle="tooltip" title="<?= lang('system.buttons.save')?>" data-editid="<?= $key ?>" data-edit="save">
                        <i class="far fa-save"></i>
                    </button>
                </div>
            </td>
            <?php endif ?>
        </tr>
        <?php endforeach ?>
        <?php if (!empty($record['parts_pagination'])) :?>
        <tr>
            <td colspan="100%">
                <div class="float-right">
                    <?= $record['parts_pagination'] ?>
                </div>
            </td>
        </tr>
        <?php endif ?>
    </tbody>
</table>

<?php if(is_array($record['payments']) && count($record['payments']) > 0) :?>
<div class="form-group">
<table class="table table-striped table-sm border" id="id_ord_payments_table">
    <thead>
        <tr>
            <th><?= lang('orders.ord_paiddate') ?></th>
            <th><?= lang('orders.ord_paidref') ?></th>
            <th><?= lang('orders.ord_paidvalue') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($record['payments'] as $payement) :?>
        <tr>
            <td><?= convertDate($payement['date'],null, 'd M Y H:i') ?></td>
            <td><?= $payement['paidref'] ?></td>
            <td><?= $payement['paidvalue'] ?></td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
</div>

<?php endif ?>

<?php if (strlen($record['ord_invoicenr']) > 0) :?>
<!-- Payment Info Modal -->
<?= $currentView->includeView('Orders/payement_ref') ?>
<!-- / Payment Info Modal -->
<?php endif ?>

<!-- Order Invoice Modal -->
<?= $currentView->includeView('Orders/orders_invoice') ?>
<!-- / Order Invoice Modal -->

<script>
$(function(){
    $('#id_ord_parts_list_table').detach().appendTo($('#id_ord_parts_list_field'));
    $('#id_ord_parts_list').remove();
    $('#id_ord_invoicenr_field').before($('#id_payments_toolbar_field').detach());
    $('.page-link').each(function(){
        $(this).attr('href',$(this).attr('href')+'&tab=tab_parts');
    });
    <?php if($record['_readonly'] || $record['enabled']==0) :?>
        <?php if($record['enabled']==0) :?>
            $('#id_formview_submit').parent().before('<button type="button" class="btn btn-danger mr-auto" data-url="<?=$record['_deleteurl']?>"><i class="far fa-trash-alt mr-2"></i><?= lang('system.buttons.remove') ?></button>');
        <?php endif ?>
        $('#id_formview_submit').remove();
    <?php endif ?>
    <?php if(is_array($record['payments']) && count($record['payments']) > 0) :?>
    $('#id_ord_paidvalue_field').after($('#id_ord_payments_table').detach());
    <?php endif ?>
    <?php if(array_key_exists('_notify', $record) && is_string($record['_notify']) && strlen($record['_notify']) > 0) :?>
        $('#form_container').before('<div class="col-12"><div class="alert alert-warning" role="alert"><?=$record['_notify']?></div></div>');
    <?php endif ?>
    $('button[data-url]').on('click',function(){
        addLoader('#form_container');
        window.location=$(this).attr('data-url');
    });    
});

$('input[name="add_parts_nr"]').on('change',function(){
    
   
});
function removeLineFromList(id,remove=true){
    if ($('#id_ord_parts_list_table').find('tbody').find('tr:not("[class]")').length<2){
        ConfirmDialog('<?= lang('orders.msg_void_order') ?>',function(){
            addLoader('#add_parts_nr_container');
            window.location='<?= $urldelete ?>';
        },function(){
           
        });
    }else{
        if (remove){
            $('#id_orderline_'+id).remove();
        }else{
            $('[name="parts['+id+'][olid]"]').removeAttr('disabled');
            $('#id_orderline_'+id).addClass('d-none').append('<input type="text" name="parts['+id+'][delete]" value="1">');
        }
    }
        
}

function addPartToList(){
    var url='<?= $validateparturl ?>';
    var part=$('#add_parts_nr_partnumber').val();
    var qty=$('#add_parts_nr_qty').val();
    var id=$('#id_ord_parts_list_table').find('tbody').html().length;
    url=url.replace('-id-',btoa(part));
    
    $('#add_parts_nr_partnumber,#add_parts_nr_qty').removeClass('border border-danger');
    if (qty<1){
        $('#add_parts_nr_qty').addClass('border border-danger');
        Dialog('<?=lang('products.error_invalid_partqty')?>','warning');
    }else
    {
        addLoader('#add_parts_nr_container');
        ajaxCall(
            url,
            [],
            function(data){
                if ('error' in data){
                    $('#add_parts_nr_partnumber').addClass('border border-danger');
                    Dialog(data['error'],'warning');
                }else{
                    data=data['parts'][0];
                    if($('input[value="'+data['prd_apdpartnumber']+'"]').length > 0){//02.6290-0197.2 03.0101-3501.2
                        var name=$('input[value="'+data['prd_apdpartnumber']+'"]').attr('name');
                        if ($('input[name="'+name+'"]').parent().parent().find('td:eq(3)').text()==data['prd_price']){
                            name=name.replace('ol_ourpart','ol_qty');
                            qty=parseInt($('input[name="'+name+'"]').val())+parseInt(qty);
                            $('input[name="'+name+'"]').val(qty);
                            name=name.replace('ol_qty','olid');
                            $('input[name="'+name+'"]').removeAttr('disabled');
                            killLoader();
                            return 0;
                        }   
                    }
                    var html='<tr id="id_orderline_'+id+'">';
                    html+='<td>'+data['prd_tecdocpart']+'</td>';
                    html+='<input type="hidden" name="parts['+id+'][ol_oepart]" value="'+data['prd_tecdocpart']+'">';
                    html+='<td>'+data['prd_apdpartnumber']+'</td>';
                    html+='<input type="hidden" name="parts['+id+'][ol_ourpart]" value="'+data['prd_apdpartnumber']+'">';
                    html+='<td>'+qty+'</td>';
                    html+='<input type="hidden" name="parts['+id+'][ol_qty]" value="'+qty+'">';
                    html+='<td>'+data['prd_price']+'</td>';
                    html+='<input type="hidden" name="parts['+id+'][ol_price]" value="'+data['prd_price']+'">';
                    html+='<input type="hidden" name="parts['+id+'][ol_commodity]" value="'+data['prd_commodity']+'">';
                    html+='<input type="hidden" name="parts['+id+'][ol_origin]" value="'+data['prd_origin']+'">';
                    html+='<input type="hidden" name="parts['+id+'][ol_ref]" value="<?= $record['ord_ref']?>">';
                    html+='<td><button type="button" class="btn btn-danger btn-sm" onclick="removeLineFromList('+id+')">';
                    html+='<i class="far fa-trash-alt"></i></button></td>';
                    html+='</tr>';
                    $('#id_ord_parts_list_table').find('tbody').append(html);
                    
                }
                $('#add_parts_nr_partnumber,#add_parts_nr_qty').val(' ');
                killLoader();
            },
            function(data){console.log(data);}
        );
    }
}

$('button[data-cancel]').on('click',function(){
    var url=$(this).attr('data-cancel');
    ConfirmDialog('<?= lang('orders.msg_cancel_order_conf')?>',function(){
        addLoader('#form_container');
        window.location=url;
    });
});

$('button[data-editid]').on('click',function(){
    var id=$(this).attr('data-editid');
    
    if ($(this).attr('data-edit')=='save'){
        var callData={};
        $('#id_orderline_'+id).find('input').each(function(){
            callData[$(this).attr('name')]=$(this).val();
        });
        $('[name="parts['+id+'][olid]"]').removeAttr('disabled');
        disableEdit(id,false);
    }else
    if ($(this).attr('data-edit')=='cancel'){
        disableEdit(id);
    }else{
        $('#id_orderline_'+id).find('input').removeAttr('readonly').removeAttr('style').removeClass('border-0').addClass('text-dark');
        $('#id_orderline_'+id).find('input[type="number"]').attr('style','width:80px');
        if ($('#id_orderline_'+id).hasClass('disabled')){
            $('#id_orderline_'+id).removeClass('disabled').removeClass('bg-danger');
            $('#id_orderline_'+id+'_toolbox').attr('data-disabled',1);
        }
        $(this).addClass('d-none');
        $('#id_orderline_'+id+'_toolbox').removeClass('d-none');
    }
});

    $('button[data-invoice]').on('click',function(){
         $('input[name="order"]').val($(this).attr('data-invoice'));
         $('#id_invoicenr, #id_invoicevalue').val('');       
         $('#id_orderlines_invoice_modal').modal('show'); 
    });

function disableEdit(id,revert=true){
    $('#id_orderline_'+id+'_toolbox').addClass('d-none');
    $('#id_orderline_'+id).find('input').each(function(){
        if ($(this).attr('type')!='hidden'){
            $(this).attr('readonly','TRUE').addClass('border-0');
            if (revert){
               $(this).val($(this).attr('data-oldvalue')); 
            }
        }
    });
    if ($('#id_orderline_'+id+'_toolbox').attr('data-disabled')!=undefined && revert){
        $('#id_orderline_'+id).find('input').attr('style','color:#fff!important;background-color:#dc3545!important;opacity:.65;');
        $('#id_orderline_'+id).addClass('bg-danger disabled');
    }
    $('button[data-editid="'+id+'"].btn-primary').removeClass('d-none');
}
</script>