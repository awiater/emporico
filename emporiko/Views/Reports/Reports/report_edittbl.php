<?= $currentView->includeView('System/form') ?>
<table class="table" id="id_rcolumns_table">
    <thead class="bg-dark">
        <th scope="col" style="width:50px"><?= lang('reports.repdata_view')?></th>
        <th scope="col"><?= lang('reports.repdata_text')?></th>
        <th scope="col"><?= lang('reports.repdata_field')?></th>
        <th scope="col"><?= lang('reports.repdata_format')?></th>
        <th scope="col" style="width:40px"><?= lang('reports.repdata_isfilter')?></th>
        <th scope="col"><?= lang('reports.repdata_filter_formula')?></th>
        <th scope="col"><?= lang('reports.repdata_filter_text')?></th>
        <th scope="col"><?= lang('reports.repdata_filter_type')?></th>
        <th style="width:50px"><button class="btn btn-primary btn-sm d-none" type="button" id="id_rcolumns_table_addnew"><i class="fas fa-plus"></i></button></th>
    </thead>
    <tbody></tbody>
    <tfoot class="d-none">
    <td>
        <input type="hidden" name="rtables" value="<?= $record['rtables'] ?>">
        <input type="hidden" id="id_rcolumns_source">
        <input type="hidden" name="rcolumns[table]"<?= !empty($record['rcolumns_table']) ? ' value="'.$record['rcolumns_table'].'"' : '' ?>>
        <select class="form-control" id="id_rcolumns_table_fields_source" name="">
            <?php if (array_key_exists('rcolumns_source', $record) && is_array($record['rcolumns_source'])) :?>
            <?php foreach ($record['rcolumns_source'] as $key=>$val) :?>
            <option value="<?= $key ?>"><?= $val?></option>
            <?php endforeach; ?>
            <?php endif ?>
        </select>
    </td>
    </tfoot>
</table>

<script>
$(function(){
    $('#id_rcolumns').html($('#id_rcolumns_table').detach());
    $('#id_formview_submit').attr('onclick','submitEditReportForm()');
    <?php if (is_array($record['rcolumns']) && array_key_exists('fields', $record['rcolumns']) && is_array($record['rcolumns']['fields'])) :?>
    var html='';
    $.each(JSON.parse(atob('<?= base64_encode(json_encode($record['rcolumns']['fields']))?>')),function(key,val){
        html=addNewRow(key,key,val['text'],html,val['visible'],val['format']);
    });
    if (html.length > 0){
        $('#id_rcolumns_table').find('tbody').html(html);
    }
    <?php endif ?>
    <?php if (is_array($record['rfilters']) && count($record['rfilters']) > 0) :?>
        $.each(JSON.parse(atob('<?= base64_encode(json_encode($record['rfilters']))?>')),function(key,val){
            $('input[name="rfilters['+key+'][text]"]').attr('data-filtertext',val['text']);
            $('[name="rfilters['+key+'][field]"]').attr('data-filterfield',val['field']);
            $('input[name="rfilters['+key+'][formula]"]').attr('data-filterform',val['formula']);
            $('input[data-filterenable="'+key+'#'+key+'"').trigger('click');
        });
    <?php endif ?>
         
});

function submitEditReportForm(){
    if ($('#id_rtitle').val().length < 2){
        
       Dialog('<?= lang('reports.error_empty_report_name')?>','warning'); 
    }else if ($('#id_rcolumns_table').find('tbody').find('tr').length > 0){
        submiteditformForm();
    }else{
        Dialog('<?= lang('reports.error_empty_report_data')?>','warning');
    }
}

function addNewRow(id,field,text,html='',visible=1,curr='0'){
    html+='<tr id="id_rcolumns_table_row_'+id+'"><td>';
    html+='<div class="icheck-primary">';
    html+='<input type="checkbox" ';
    if (visible==1){
       html+='checked="true" '; 
    }
    html+='value="1" id="id_rcolumns_table_row_'+id+'_view_'+field+'" name="rcolumns[fields]['+id+'][visible]">';
    html+='<label for="id_rcolumns_table_row_'+id+'_view_'+field+'"></label>';
    html+='</div></td><td>';
    html+='<input type="text" name="rcolumns[fields]['+id+'][text]" value="'+text+'" class="form-control">';
    html+='</td><td>';
    html+='<select name="rcolumns[fields]['+id+'][field]" class="form-control">';
    html+=$('#id_rcolumns_table_fields_source').html().replace('value="'+field+'"','value="'+field+'" selected="true"');
    html+='</select></td><td>';
    html+='<select name="rcolumns[fields]['+id+'][format]" class="form-control">';
    html+='<option value="0"'+(curr=='0' ? ' selected="true"' : '')+'><?=lang('reports.repdata_format_def')?></option>';
    html+='<option value="date"'+(curr=='date' ? ' selected="true"' : '')+'><?=lang('reports.repdata_format_date')?></option>';
    html+='<option value="curr"'+(curr=='curr' ? ' selected="true"' : '')+'><?=lang('reports.repdata_format_curr')?></option>';
    html+='<option value="yesno"'+(curr=='yesno' ? ' selected="true"' : '')+'><?=lang('reports.repdata_format_yesno')?></option>';
    html+='<option value="img"'+(curr=='img' ? ' selected="true"' : '')+'><?=lang('reports.repdata_format_img')?></option>';
    html+='</select></td><td><div class="icheck-primary">';
    html+='<input type="checkbox" data-filterenable="'+id+'#'+field+'" value="1" id="id_rcolumns_table_row_'+id+'_filter_'+field+'">';
    html+='<label for="id_rcolumns_table_row_'+id+'_filter_'+field+'"></label>';
    html+='</div></td><td>';
    html+='<input type="text" name="rfilters['+field+'][formula]" data-filterform="" value="" class="form-control" disabled="true">';
    html+='</td><td>';
    html+='<input type="text" name="rfilters['+field+'][text]" data-filtertext="'+text+'" value="" class="form-control" disabled="true">';
    html+='</td><td>';
    html+='<select name="rfilters['+field+'][field]" class="form-control" disabled="true" data-filterfield="TextField">';
    html+='<option value="0"></option>';
    html+='<option value="TextField"><?=lang('reports.filter_type_text')?></option>';
    html+='<option value="customersList"><?=lang('reports.filter_type_cust')?></option>';
    html+='<option value="brandsList"><?=lang('reports.filter_type_brand')?></option>';
    html+='<option value="DatePicker"><?=lang('reports.filter_type_date')?></option>';
    html+='<option value="usersList"><?=lang('reports.filter_type_user')?></option>';
    html+='<option value="suppsList"><?=lang('reports.filter_type_supp')?></option>';
    html+='</select>';
    html+='</td><td>';
    html+='<button class="btn btn-outline-danger btn-sm" type="button" onclick="$('+"'"+'#id_rcolumns_table_row_'+id+"'"+').remove()">';
    html+='<i class="fas fa-trash"></i>';
    html+='</button>';
    html+='</td></tr>';
    return html;
}

$('#id_rcolumns_table_addnew').on('click',function(){
    $('#id_rcolumns_table').find('tbody').append(addNewRow('','','',''));
});

 $('#id_rcolumns_list').on('select2:select', function (e) {
    var val=e.params.data['id'];
    ajaxCall('<?= url('/api/reports/getfieldsforsource')?>',
            {
                'source':val
            },
            function(data){
                console.log(data);
                if ('error' in data){
                    Dialog(data['error'],'warning');
                }else if('fields' in data){
                    var html='';
                    var id=$('#id_rcolumns_table').find('tbody').html().length;
                    
                    $('#id_rcolumns_source').val(JSON.stringify(data['fields'])).attr('name','rconfig');
                    $('input[name="rcolumns[table]"]').val(val);
                    $.each(data['fields'],function(field,text){
                        html+='<option value="'+field+'">'+text+'</option>';
                    });
                    $('#id_rcolumns_table_fields_source').html(html);
                    $('#id_rcolumns_table_addnew').removeClass('d-none');
                    html='';
                    $.each(data['fields'],function(field,text){
                        html=addNewRow(field,field,text,html);
                    });
                    
                    if (html.length > 0){
                        $('#id_rcolumns_table').find('tbody').html(html);
                    }
                    
                    $.each(data['fields'],function(field,text){
                        $('[name="rcolumns[fields]['+field+'][field]"]').val(field);
                    });
                    
                    if ('table' in data){
                        $('input[name="rtables"]').val(data['table']);
                    }
                }
            },
            function(data){console.log(data);},
            'POST');
 });
 
 $('#id_rcolumns_table tbody').on('click','input[data-filterenable]',function(){
    var id=$(this).attr('data-filterenable');
    id=id.split('#');
    var fil_txt_field=$('input[name="rfilters['+id[1]+'][text]"]');
    var fil_frm_field=$('input[name="rfilters['+id[1]+'][formula]"]');
    var fil_drd_field=$('[name="rfilters['+id[1]+'][field]"]');
    if ($(this).is(':checked')){
        fil_txt_field.removeAttr('disabled').val(fil_txt_field.attr('data-filtertext'));
        fil_frm_field.removeAttr('disabled').val(fil_frm_field.attr('data-filterform'));
        fil_drd_field.removeAttr('disabled').find('option[value="'+fil_drd_field.attr('data-filterfield')+'"]').prop('selected', true);
    }else{
        fil_txt_field.attr('disabled','true').attr('data-filtertext',fil_txt_field.val()).val('');
        fil_frm_field.attr('disabled','true').attr('data-filterform',fil_txt_field.val()).val('');
        fil_drd_field.attr('disabled','true').attr('data-filterfield',fil_drd_field.val()).val('');
    }
 });
 
</script>
