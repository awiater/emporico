<?= $currentView->includeView('System/form') ?>
<div class="modal" tabindex="-1" role="dialog" id="reportsFilterEditor">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('system.reports.rep_filtereditor')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close')?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group" id="reportsFilterEditor_glue_field">
                    <label for="reportsFilterEditor_glue" class="mr-2"><?= lang('system.reports.rep_filterglue')?></label>
                    <?= form_dropdown('', ['AND'=>'AND','OR'=>'OR'],[],['id'=>'reportsFilterEditor_glue','class'=>'form-control']) ?>
                    <small class="form-text text-muted">
                        <?php $lbl=lang('system.reports.rep_filtervalue_tooltip'); echo $lbl=='system.reports.rep_filtervalue_tooltip' ? '' : $lbl?>
                    </small>
 		</div>
                <div class="form-group">
                    <label for="reportsFilterEditor_name" class="mr-2"><?= lang('system.reports.rep_filtername')?></label>
                    <input type="text" id="reportsFilterEditor_name" class="form-control">
                    <small class="form-text text-muted">
                        <?php $lbl=lang('system.reports.rep_filtervalue_tooltip'); echo $lbl=='system.reports.rep_filtervalue_tooltip' ? '' : $lbl?>
                    </small>
 		</div>
                <div class="form-group" id="reportsFilterEditor_comp_field">
                    <label for="reportsFilterEditor_comp" class="mr-2"><?= lang('system.reports.rep_filtercomp')?></label>
                    <?= form_dropdown('', ['='=>'=','=>'=>'=>','<='=>'<=','>'=>'>','<'=>'<','Like'=>'%','<>'=>'<>'],[],['id'=>'reportsFilterEditor_comp','class'=>'form-control']) ?>
                    <small class="form-text text-muted">
                        <?php $lbl=lang('system.reports.reportsFilterEditor_comp_tooltip'); echo $lbl=='system.reports.reportsFilterEditor_comp_tooltip' ? '' : $lbl?>
                    </small>
 		</div>
                <div class="form-group">
                    <label for="reportsFilterEditor_value" class="mr-2"><?= lang('system.reports.rep_filtervalue')?></label>
                    <input type="text" id="reportsFilterEditor_value" class="form-control">
                    <small class="form-text text-muted">
                        <?php $lbl=lang('system.reports.rep_filtervalue_tooltip'); echo $lbl=='system.reports.rep_filtervalue_tooltip' ? '' : $lbl?>
                    </small>
 		</div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="reportsFilterEditor_submit">
                    <i class="far fa-save mr-1"></i><?= lang('system.buttons.add')?>
                </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.close')?>
                </button>
                
            </div>
        </div>
    </div>
</div>
<ul class="list-group sortable" id="id_rcolumns_list" style="overflow-y: scroll;max-height: 250px">
    <?php $ikey=0; ?>
    <?php foreach(is_array($record['rcolumns']) ? $record['rcolumns'] : []  as $key=>$value) :?>
    <li class="list-group-item d-flex" id="id_rcolumns_list_<?= $ikey?>">
        <?= $key ?>
        <input type="hidden" name="rcolumns[<?= $key?>]" value="<?= $value?>">
        <div class="ml-auto">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeReportColumn('<?= $ikey?>')">
                <i class="fa fa-trash "></i>
            </button>   
        </div>
    </li>
    <?php $ikey++; ?>
    <?php endforeach; ?>
</ul>
<ul class="list-group" id="id_rfilters_list" style="overflow-y: scroll;max-height: 250px">
    <?php $ikey=0; ?>
    <?php foreach(is_array($record['rfilters']) ? $record['rfilters'] : []  as $key=>$value) :?>
    <li class="list-group-item d-flex" id="id_rfilters_list_<?= $ikey?>">
        <?= $key?>
        <input type="hidden" name="rfilters[<?= $key?>]" value="<?= $value?>">
        <div class="ml-auto">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeFiltersColumn('<?= $ikey?>')">
                <i class="fa fa-trash "></i>
            </button>   
        </div>
    </li>
    <?php $ikey++; ?>
    <?php endforeach; ?>
</ul>
<script>
    $(function(){
        $("#id_rcolumns_list").detach().appendTo('#id_rcolumns_input_field');
        addRefButton('id_rcolumns_input','addReportColumn()','fas fa-plus','','btn btn-primary');
        $("#bolt_btn_id_rcolumns_input").parent().prepend('<button type="button" class="btn btn-danger btn-sm" onclick="clearList('+"'"+'id_rcolumns_list'+"'"+')"><i class="fas fa-broom"></i></button>');
        
        $("#id_rfilters_list").detach().appendTo('#id_rfilters_input_field');
        addRefButton('id_rfilters_input','addNewFilter()','fas fa-plus','','btn btn-primary');
        $("#bolt_btn_id_rfilters_input").parent().prepend('<button type="button" class="btn btn-danger btn-sm" onclick="clearList('+"'"+'id_rfilters_list'+"'"+')"><i class="fas fa-broom"></i></button>');
        
        applyColumnsSource(true);
        $('[onclick="id_rfilters_listadd()"]').attr('onclick','addNewFilter()');
        $( ".sortable" ).sortable();
    });
    
    $('#id_rtables').on('change',function(){
       applyColumnsSource();
    });
    
    $('#reportsFilterEditor_submit').on('click',function(){
        var val=$('#id_rfilters_input option:selected').val();
        val=JSON.parse(atob(val));
        var suff=$("#reportsFilterEditor_comp option:selected").val();
        suff=suff=='=' ? '' : ' '+suff;
        var pref='';
        if (!$('#reportsFilterEditor_glue_field').hasClass('d-none')){
            pref=$("#reportsFilterEditor_glue option:selected").val()+' ';
        }
        var value={};
        value['txt']=pref+$("#reportsFilterEditor_name").val()+suff;
        val['label']=$("#reportsFilterEditor_name").val();
        val['name']=pref+val['name']+suff;
        if ($("#reportsFilterEditor_value").val().length > 0){
           val['value']=$("#reportsFilterEditor_value").val(); 
           value['txt']=value['txt']+' '+val['value'];
        }
        
        val=btoa(JSON.stringify(val));
        var item=$("#id_rfilters_input option:selected");
        var id=$("#id_rfilters_list").html().length;
        var html=$('<li class="list-group-item d-flex" id="id_rfilters_list'+id+'">');
        html.append(value['txt']);
        html.append('<input type="hidden" name="rfilters['+value['txt']+']" value="'+val+'">');
        html.append('<div class="ml-auto">');
        html.find('.ml-auto').append('<button type="button" class="btn btn-danger btn-sm" onclick="removeFiltersColumn('+id+')">');
        html.find('.ml-auto').find('.btn-danger').append('<i class="fa fa-trash "></i>');
        $('#id_rfilters_list').append(html);
        $("#reportsFilterEditor").modal('hide');
    });
    
    function clearList(id){
        $('#'+id).html('');
    }
    
    function addReportColumn(){
        var item=$("#id_rcolumns_input option:selected");
        var id=$("#id_rcolumns_list").html().length;
        if (item.val()=='[*]'){
            var itema=[];
            $('#id_rcolumns_input option').each(function(){
                 if ($(this).val()!='[*]'){ 
                     itema.push({'val':$(this).val(),'text':$(this).text()}); 
                }
              });
        }else{
            var itema=[{'val':item.val(),'text':item.text()}];
        }
        jQuery.each(itema, function(key,item) {
        var html=$('<li class="list-group-item d-flex" id="id_rcolumns_list_'+id+'">');
            html.append(item.text);
            html.append('<input type="hidden" name="rcolumns['+item.text+']" value="'+item.val+'">');
            html.append('<div class="ml-auto">');
            html.find('.ml-auto').append('<button type="button" class="btn btn-danger btn-sm" onclick="removeReportColumn('+id+')">');
            html.find('.ml-auto').find('.btn-danger').append('<i class="fa fa-trash "></i>');
            $('#id_rcolumns_list').append(html);
        });
    }
    
    function removeReportColumn(id){
        $('#id_rcolumns_list_'+id).remove();
    }
    
    function removeFiltersColumn(id){
        $('#id_rfilters_list_'+id).remove();
    }
    
    function addNewFilter()
    {
        if ($('#id_rfilters_input option:selected').text().length < 1){
            Dialog('aa','warning');
            return;
        }
        $('#reportsFilterEditor_glue_field').removeClass('d-none');
        if($('#id_rfilters_list').find('li').html()==undefined){
          $('#reportsFilterEditor_glue_field').addClass('d-none');  
        }
        $("#reportsFilterEditor_name").val($('#id_rfilters_input option:selected').text());
        $("#reportsFilterEditor").modal('show');
    }
    
    function applyColumnsSource(start=false)
    {
        var val=$('[name="rtables"]').val();
        if (val==undefined){
            val=$('#id_rtables').val()
        }
        if (!start){
             $('#id_rcolumns_list').html('');
             $('#id_rfilters_list').html('');
        }
        var html='';
        val=JSON.parse(atob(val));
        jQuery.each(val['columns'], function(index, item) {
            html+='<option value="'+index+'">'+item+'</option>';
        });
        $('#id_rcolumns_input').html(html);
        html='';
        jQuery.each(val['filters'], function(index, item) {
            html+='<option value="'+index+'">'+item+'</option>';
        });
        $('#id_rfilters_input').html(html);
    }
</script>    