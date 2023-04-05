<table class="table table-striped" id="id_products_lines_table">
    <thead class="card-header">
        <tr>
            <th><?= lang('orders.ol_ourpart') ?></th>
            <th><?= lang('orders.ol_oepart') ?></th>
            <th><?= lang('orders.ol_qty') ?></th>
            <?php if (intval($record['ord_type'])==1) :?>
            <th><?= lang('orders.ol_cusprice') ?></th>
            <th><?= lang('orders.ol_price_quote') ?></th>
            <?php else :?>
            <th><?= lang('orders.ol_price') ?></th>
            <th><?= lang('orders.ol_status') ?></th>
            <?php endif ?>
            <?php if (!empty($lines_edit) && $lines_edit) :?>
            <th></th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lines as $key=>$line) :?>
        <tr data-lineid="<?= $line['olid'] ?>">
            <td>
                <?= $line['ol_ourpart'] ?>
            </td>
            <td><?= $line['ol_oepart'] ?></td>
            <td>
                <?php if (!empty($lines_edit) && $lines_edit) :?>
                <input type="hidden" value="<?=$line['olid']?>" name="lines[<?=$line['olid']?>][id]">
                <input type="number" min="1" style="max-width:100px" class="bg-transparent border-transparent" name="lines[<?=$line['olid']?>][qty]" value="<?= $line['ol_qty'] ?>" id="id_product_lines_<?=$line['olid']?>_qty" readonly="true"> 
                <?php else :?>
                <?= $line['ol_qty'] ?>
                <?php endif ?>
            </td>
            <?php if (intval($record['ord_type'])==1) :?>
            <td>
                <?= $record['order_curr'] ?>&nbsp;
                <?= $line['ol_cusprice'] ?>
            </td>
            <td>
                <div class="d-flex">
                    <?php if (!empty($curr_list[$record['order_curr']])) :?>
                    <?= $curr_list[$record['order_curr']] ?>&nbsp;
                    <?php endif ?>
                    <p class="ml-auto mr-3">
                        <?php if (!empty($lines_edit) && $lines_edit) :?>
                        <input type="text" dir="rtl" style="max-width:120px" class="bg-transparent border-transparent" name="lines[<?=$line['olid']?>][price]" value="<?= $line['ol_price'] ?>" id="id_product_lines_<?=$line['olid']?>_price" readonly="true" data-mask-reverse="true" data-mask="00000000.00"> 
                        <?php else :?>
                        <?= $line['ol_price'] ?>
                        <?php endif ?>
                    </p>
                </div>
            </td>
            <?php else :?>
            <td>
                <div class="d-flex">
                    <?php if (!empty($curr_list[$record['order_curr']])) :?>
                    <?= $curr_list[$record['order_curr']] ?>&nbsp;
                    <?php endif ?>
                    <p class="ml-auto mr-3">
                        <?php if (!empty($lines_edit) && $lines_edit) :?>
                        <input type="text" dir="rtl" style="max-width:120px" class="bg-transparent border-transparent" name="lines[<?=$line['olid']?>][price]" value="<?= $line['ol_price'] ?>" id="id_product_lines_<?=$line['olid']?>_price" readonly="true" data-mask-reverse="true" data-mask="00000000.00"> 
                        <?php else :?>
                        <?= $line['ol_price'] ?>
                        <?php endif ?>
                    </p>
                </div>
            </td>
            <td>
                <?php if (!empty($lines_edit) && $lines_edit) :?>
                <input type="text" class="bg-transparent border-transparent" name="lines[<?=$line['olid']?>][status]" value="<?= $line['ol_status'] ?>" id="id_product_lines_<?=$line['olid']?>_status" readonly="true"> 
                <?php else :?>
                <?= $line['ol_status'] ?>
                <?php endif ?>
            </td>
            <?php endif ?>
            <?php if (!empty($lines_edit) && $lines_edit) :?>
            <td>
                <ul class="nav">
                    <li class="nav-item d-none" id="id_product_lines_<?=$line['olid']?>_removeitem">
                        <button type="button" class="btn btn-sm btn-danger mr-2" data-removeid="<?=$line['olid']?>">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </li>
                    <li class="nav-item" id="id_product_lines_<?=$line['olid']?>_enableitem">
                        <button type="button" class="btn btn-sm btn-outline-primary btnLineEditable" btn-editableid="<?=$line['olid']?>" onClick="setLineEditable(<?=$line['olid']?>)">
                            <i class="fas fa-lock"></i>
                        </button>
                    </li>
                    <?php if (!empty($line_save) && $line_save) :?>
                    <li class="nav-item d-none" id="id_product_lines_<?=$line['olid']?>_saveitem">
                       <button type="button" class="btn btn-sm btn-success ml-2" data-saveid="<?=$line['olid']?>">
                            <i class="far fa-save"></i>
                        </button>   
                    </li>
                    <?php endif ?> 
                </ul>
            </td>
            <?php endif ?>
        </tr>
         <?php endforeach;?>
    </tbody>
    <?php if (!empty($pagination)) :?>
    <tfoot>
        <tr>
            <td colspan="<?=!empty($lines_edit) && $lines_edit ? 6 : 5?>">
                <div class="float-right">
                    <?= $pagination ?>
                </div>
            </td>
        </tr>
    </tfoot>
    <?php endif ?>
</table>
<?php if (!empty($lines_edit) && $lines_edit) :?>
<datalist id="findPartList"></datalist>
<script>
function setLineEditable(id){
    var cls='fas fa-lock-open';
    var cls_lock='bg-transparent border-transparent';
    var cls_open='form-control form-control-sm';
    if ($('#id_product_lines_'+id+'_price').attr('readonly')==undefined){
            cls='fas fa-lock';
            $('tr[data-lineid="'+id+'"]').find('input').each(function(){
                $(this).removeClass(cls_open).addClass(cls_lock).attr('readonly',true);
                 $(this).unbind();
            });
            $('#id_product_lines_'+id+'_removeitem').addClass('d-none');
            
    }else{
        $('tr[data-lineid="'+id+'"]').find('input').each(function(){
            $(this).removeClass(cls_lock).addClass(cls_open).removeAttr('readonly');
            $(this).bind('change',function(){
                $('#id_product_lines_'+id+'_saveitem').removeClass('d-none');
            });
            $('#id_product_lines_'+id+'_removeitem').removeClass('d-none');
        });
    }
    $('button[btn-editableid="'+id+'"]').find('i').attr('class',cls);
}
$('button[data-removeid]').on('click',function(){
    var id=$(this).attr('data-removeid');
    ConfirmDialog('<?= lang('orders.msg_delete')?>',function(){
        if($('tr[data-lineid="'+id+'"]').find('input[name="lines['+id+'][id]"]').length > 0){
            $('tr[data-lineid="'+id+'"]').html('<input type="text" value="'+id+'" name="lines[delete][]">').addClass('d-none');
            Dialog('<?= lang('orders.msg_save')?>','info');
        }else{
            $('tr[data-lineid="'+id+'"]').remove();
        }
    });
});

function addNewLineToTable(part,qty,price,oepart,origin,commodity){
    var id=parseInt($('#id_products_lines_table').find('tbody').find('tr').last().attr('data-lineid'))+1;
    var html=$('#id_products_lines_table').find('tbody').find('tr:last').clone();
    html.find('button.btnLineEditable').attr('onclick','setLineEditable('+id+')').attr('btn-editableid',id);
    <?php if (!empty($line_save) && $line_save) :?>
    html.find('button[data-saveid]').attr('data-saveid',id).bind('click',$('#id_products_lines_table').find('tbody').find('tr:last').find('button[data-saveid]').click).parent().attr('id','id_product_lines_'+id+'_saveitem').removeClass('d-none');
    <?php endif ?>
    html.find('button[data-removeid]').attr('data-removeid',id).bind('click',$('#id_products_lines_table').find('tbody').find('tr:last').find('button[data-removeid]').click).parent().attr('id','id_product_lines_'+id+'_removeitem');
    html.find('input[id$="_qty"]').attr('id','id_product_lines_'+id+'_qty').attr('name','lines[new]['+id+'][qty]').val(qty);
    html.find('input[id$="_price"]').attr('id','id_product_lines_'+id+'_price').attr('name','lines[new]['+id+'][price]').val(price);
    html.find('input[id$="_status"]').attr('id','id_product_lines_'+id+'_status').attr('name','lines[new]['+id+'][status]').val('');
    html.attr('data-lineid',id);
    html.find("td:eq(1)").text(oepart);
    html.find("td:eq(0)").text(part);
    html.append('<input type="hidden" name="lines[new]['+id+'][origin]" value="'+origin+'">');
    html.append('<input type="hidden" name="lines[new]['+id+'][commodity]" value="'+commodity+'">');
    html.append('<input type="hidden" name="lines[new]['+id+'][ourpart]" value="'+part+'">');
    html.append('<input type="hidden" name="lines[new]['+id+'][oepart]" value="'+oepart+'">');    
    $('#id_products_lines_table').find('tbody').prepend(html);
}
<?php if (!empty($line_save) && $line_save) :?>
$('button[data-saveid]').on('click',function(){
    var id=$(this).attr('data-saveid');
    var input={};
    $('tr[data-lineid="'+id+'"]').find('input').each(function(){
        var name=$(this).attr('name');
        name=name.replace('lines['+id+'][','').replace(']','');
        input[name]=$(this).val();
    });
    
    ajaxCall('<?= url('Api','orders',['updateLine']) ?>'
            ,{
                'ref':'<?= $record['ord_ref'] ?>',
                'data':input
            }
            ,function(data){
                if ('error' in data){
                    Dialog(data['error'],'warning');
                }else{
                    $('#id_product_lines_'+id+'_saveitem').addClass('d-none');
                    if ('msg' in data){
                        Dialog(data['msg'],'info');
                    }  
                }
                    
                console.log(data);
                killLoader();
            }
            ,function(data){
                console.log(data);
                killLoader();
            }
            ,'POST'
    );
});
<?php endif ?>
</script>
<?php endif; ?>

   