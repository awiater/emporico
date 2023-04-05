<?php if (empty($args['notable'])||(!empty($args['notable']) && !$args['notable'])) :?>
<div class="border p-1" id="<?=$args['id']?>_container">
<?php else :?>
<div id="<?=$args['id']?>_container">
<?php endif ?>
<?php if (empty($args['readonly']) || (!empty($args['readonly']) && !$args['readonly'])) :?>
<div class="row mb-2">
    <div class="col-3">
        <div class="input-group">
            <select id="<?=$args['id']?>_partnumber" class="form-control form-control-sm" style="width: 350px"></select>
        </div>
    </div>
    <?php if (!empty($args['field_value'])) :?>
    <div class="col-2">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="far fa-money-bill-alt"></i>
                </span>
            </div>
            <input type="text" id="<?=$args['id']?>_value" class="form-control form-control-sm" dir="rtl" data-mask-reverse="true" data-mask="00000000.00" placeholder="<?=lang('products.prd_value') ?>">
        </div>
    </div>
    <?php endif ?>
    <div class="col-2">
        <input type="number" id="<?=$args['id']?>_qty" class="form-control form-control-sm" min="1" placeholder="<?=lang('products.prd_tecdocpart_qty') ?>">
    </div>
    <div class="col-2">
        <button type="button" class="btn btn-primary btn-sm" onClick="<?= empty($args['addfunction']) ? $args['id'].'_listadd()' : $args['addfunction']?>">
            <i class="fas fa-plus"></i>
        </button>
    </div>
</div>    
<?php endif ?>
<?php if (empty($args['notable'])||(!empty($args['notable']) && !$args['notable'])) :?>
<table class="table table-sm table-striped">
    <thead>
        <tr>
            <th><?=lang('products.prd_apdpartnumber') ?></th>
            
            <?php if (!empty($args['field_value'])) :?>
            <th><?=lang('products.prd_value') ?></th>
            <?php endif ?>
            <th><?=lang('products.prd_tecdocpart_qty') ?></th>
            <?php if (!empty($args['totals_values']) && $args['totals_values']) :?>
            <th style="width:150px"><?=lang('products.msg_partpicker_total') ?></th>
            <?php endif ?>
            <?php if ($args['item_delete']) :?>
            <th style="width:80px"></th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody id="<?= $args['id'].'_list' ?>" style="max-height:<?= $args['list_height'] ?>;overflow-y: scroll;">
        <?php foreach(is_array($items) ? $items : [] as $key=>$item) :?>
        
        <tr id="<?= $args['id'].'partlistitem_'.$key ?>">
            <td>
                <div>
                    <?= $item['prd_apdpartnumber'] ?>
                </div>
                <small>
                    <div>
                        <?= $item['prd_description'] ?>
                    </div>
                    <?php foreach($args['list_fields'] as $field_key=>$field) :?>
                        <?php if (array_key_exists($field, $item)) :?>
                            <?php if ($field_key === array_key_last($args['list_fields'])) :?>
                                <?= $item[$field] ?>
                            <?php else :?>
                                <?= $item[$field] ?>&nbsp;|&nbsp;
                            <?php endif ?>
                        <?php endif ?>
                        
                    <?php endforeach; ?>
                </small>
            </td>
            <?php if (!empty($args['field_value'])) :?>
            <td><?= array_key_exists('value', $item) ? '<b class="mr-2">'.$args['def_curr'].'</b>'.$item['value'] : '' ?></td>
            <?php endif ?>
            <td><?= array_key_exists('qty', $item) ? $item['qty'] : '' ?></td>
            <?php if (!empty($args['totals_values']) && $args['totals_values']) :?>
            <td>
                <?php $args['items_totals'][]=(array_key_exists('value', $item) ? $item['value'] : 1)*(array_key_exists('qty', $item) ? intval($item['qty']) : 0); ?>
                <b class="mr-2"><?= $args['def_curr'] ?></b><?=  $args['items_totals'][count($args['items_totals'])-1] ?>
            </td>
            <?php endif ?>
            <?php if ($args['item_delete']) :?>
            <td>
                <button type="button" data-partlistitem="<?= $args['id'].'_list' ?>" class="btn btn-sm btn-danger">
                    <i class="far fa-trash-alt"></i>
                </button>
            </td>
            <?php endif ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <?php if (is_array($items) && count($items) > 0 && !empty($args['totals_values']) && $args['totals_values']) :?>
    <tfoot>
        <tr>
            <td></td>
            <?php if (!empty($args['field_value'])) :?>
            <td></td>
            <?php endif ?>  
            <td><?= array_sum(array_column($items,'qty')); ?></td>
            <td><b class="mr-2"><?= $args['def_curr'] ?></b><?= array_sum($args['items_totals']) ?></td>
        </tr>
    </tfoot>
    <?php endif ?>    
</table>
<?php endif ?>
</div>
<script>
$(function(){
    <?php if (empty($args['readonly']) || (!empty($args['readonly']) && !$args['readonly'])) :?>
    $('#<?=$args['id']?>_partnumber').select2({
        ajax:{
            url: "<?= url('Api','products',['findpartforfield'])?>",
            dataType: 'json',
            delay: 250,
            data:function(params){
                console.log(params);
                return {
                    part: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                var results = [];
                $.each(data, function(k, v) {
                    results.push({
                        id: v.prd_apdpartnumber,
                        text: v.prd_description,
                        description_txt: v.prd_description,
                        raw:v
                    });
                });
                return {
                    results: results,
                    pagination:{
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true        
        },
        placeholder: '<?=lang('products.prd_tecdocpart') ?>',
        minimumInputLength: 3,
        dropdownAutoWidth:true,
        templateResult: function(result){
            if (!result.id){
                return $('<span><?= lang('products.msg_partpicker_search') ?></span>');
            }
            return $('<span>'
                    +'<div>'+result.id+'</div><small>'
                    +'<div>'+result.text+'</div>'
                    <?php foreach($args['list_fields'] as $key=>$field) :?>
                        <?php if ($key === array_key_last($args['list_fields'])) :?>
                            +result.raw.<?= $field ?>
                        <?php else :?>
                            +result.raw.<?= $field ?>+'&nbsp;|&nbsp;'
                        <?php endif ?>
                    <?php endforeach; ?>
                    +'</small></span>');
        },
        templateSelection: function(result){
            $('#<?=$args['id']?>_partnumber').attr('data-value',btoa(JSON.stringify(result.raw)));
            $('#<?=$args['id']?>_partnumber').attr('data-text',btoa(
                '<div>'+result.id+'</div><small>'
                +'<div>'+result.text+'</div>'
                <?php foreach($args['list_fields'] as $key=>$field) :?>
                    +((result!=undefined && 'raw' in result && '<?= $field ?>' in result.raw) ?
                    <?php if ($key === array_key_last($args['list_fields'])) :?>
                        result.raw.<?= $field ?>
                    <?php else :?>
                        result.raw.<?= $field ?>+'&nbsp;|&nbsp;'
                    <?php endif ?>
                    :'')
                <?php endforeach; ?>
                
                +'</small></div>'
            ));
            $('#select2-<?= $args['id'] ?>_partnumber-container').removeClass('text-muted').parent().parent().parent().attr('style','min-width:150px');
            return result.id;
        }
    });
    $('#select2-<?= $args['id'] ?>_partnumber-container').text('<?=lang('products.prd_apdpartnumber') ?>').addClass('text-muted');
    <?php endif ?>
});    

    <?php if (empty($args['addfunction']) && (empty($args['readonly']) || (!empty($args['readonly']) && !$args['readonly']))) :?>
    function <?=$args['id']?>_listadd(){
        var id=$('#<?= $args['id'].'_list' ?>').html().length;
        
        var part=$('#<?=$args['id']?>_partnumber').attr('data-value');
        var html='<tr id="<?=$args['id']?>_listitem_'+id+'">';
        html+='<td>'+atob($('#<?=$args['id']?>_partnumber').attr('data-text'))+'<input type="hidden" name="<?=$args['name']?>['+id+'][data]" value="'+part+'"></td>';      
        <?php if (!empty($args['field_value'])) :?>
        var part_val=$('#<?=$args['id']?>_value').val();
        if (part_val.length==0 || (part_val.length > 0 && parseFloat(part_val) < 0)){
            Dialog('<?= lang('products.error_partpicker_novalue') ?>','warning');
            return false;
        }
        html+='<td><b class="mr-2"><?= $args['def_curr'] ?></b>'+part_val+'<input type="hidden" name="<?=$args['name']?>['+id+'][value]" value="'+part_val+'"></td>';
        <?php endif ?>
        var qty=$('#<?=$args['id']?>_qty').val();
        if (qty.length==0 || (qty.length > 0 && parseInt(qty) < 0)){
            Dialog('<?= lang('products.error_partpicker_noqty') ?>','warning');
            return false;
        }
        html+='<td>'+qty+'<input type="hidden" name="<?=$args['name']?>['+id+'][qty]" value="'+qty+'"></td>';
        html+='<td><button type="button" class="btn btn-sm btn-danger" onClick="<?=$args['id']?>_removeitem('+id+')">';
        html+='<i class="far fa-trash-alt"></i>';
        html+='</button></td>';
        html+='</tr>';
        $('#<?= $args['id'].'_list' ?>').append(html);
        $('#select2-<?= $args['id'] ?>_partnumber-container').text('<?=lang('products.prd_apdpartnumber') ?>').addClass('text-muted');;
        $('#<?=$args['id']?>_qty').val('');
        <?php if (!empty($args['field_value'])) :?>
        $('#<?=$args['id']?>_value').val('');
         <?php endif ?>
        
        
    } 
    <?php endif ?>

    function <?=$args['id']?>_removeitem(id){
        ConfirmDialog('<?= lang('system.general.msg_delete_ques')?>',function(){
            $('#<?=$args['id']?>_listitem_'+id).remove();
        });
    }
    
</script>
