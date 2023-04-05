<?= $currentView->includeView('System/form') ?>
<?= $currentView->includeView('Orders/products_lines',['lines'=>$record['parts'],'lines_edit'=>TRUE,'pagination'=>$record['parts_pagination'],'line_save'=>TRUE]) ?>
<script>
$(function(){
    $('#id_products_lines_table').detach().appendTo($('#id_ord_parts_list_field'));//
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
   
    <?php if(array_key_exists('_notify', $record) && is_string($record['_notify']) && strlen($record['_notify']) > 0) :?>
        $('#form_container').before('<div class="col-12"><div class="alert alert-warning" role="alert"><?=$record['_notify']?></div></div>');
    <?php endif ?>
     $('#add_parts_nr_partnumber').attr('list','findPartList');
     $('input[list="findPartList"]').on('change',function(){
        var val=$(this).val();
        if (val.length > 0){
            addLoader();
            findPart(val);
        }
     });
});
<?php if (!empty($validateparturl)) :?>
    
function addPartToList(){
    var result=[];
    part=$('#add_parts_nr_partnumber').val();
    $("#findPartList").find("option").each(function() {
        if ($(this).val()==part){
            result=$(this).attr('data-val');
            result=atob(result);
            result=JSON.parse(result);
        }
    });
   if ('price' in result){
       addNewLineToTable(part,$('#add_parts_nr_qty').val(),result['price'],result['tecdocpart'],result['origin'],result['commodity']);
       $('#add_parts_nr_qty').val('');
       $('#add_parts_nr_partnumber').val('');
   }
}

function findPart(part){
    ajaxCall('<?= url('Api','products',['findPart']) ?>'
        ,{
            'part':part,
            'customer':'<?= $record['ord_cusacc']?>'
        }
        ,function(data){
            killLoader();
           console.log(data);
            if ('parts' in data){
                
                var html='';
                $.each(data['parts'],function(key,val){
                    html+='<option value="'+val['apdpartnumber']+'" data-val="'+btoa(JSON.stringify(val))+'">';
                });
                $('#findPartList').html(html);
            }
        }
        ,function(data){
            console.log(data);
            killLoader();
        }
        ,'POST'
    );
}
<?php endif ?>
</script>
