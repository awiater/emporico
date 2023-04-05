<?= $currentView->includeView('System/form') ?>
<div class="form-group" id="id_sendpricefile_others">
    <label data-toggle="collapse" href="#id_sendpricefile_others_collapse" role="button" aria-expanded="false" aria-controls="id_sendpricefile_others_collapse">
        <i class="fas fa-chevron-circle-down mr-1 ml-1"></i><?=lang('products.sendtoacc_othersgroup') ?>
    </label>
    <div class="collapse" id="id_sendpricefile_others_collapse"></div>
</div>
<script>
$(function(){
    $('#id_from_field').before($('#id_sendpricefile_others').detach());
    $('#id_sendpricefile_others_collapse').append($('#id_from_field, #id_emailscc_field, #id_subject_field, #id_msg_field').detach());
    $('#id_formview_submit').removeAttr('onclick').on('click',function(){
       if ($('input[name^="emailsto["]').length < 1){
           Dialog('<?=lang('products.sendtoacc_errornoemails') ?>','warning');
       }else if ($('input[name^="brand["]').length < 1){
           Dialog('<?=lang('products.sendtoacc_errornobrands') ?>','warning');
       }else{
           addLoader();
           $('#<?= $_formview_action_attr['id'] ?>').submit();
       } 
    });
});

function emailsto_listadd(){
    var val=$("#id_emailsto_input").find('option:selected').val();
    val=JSON.parse(atob(val));
    $.each(val,function(i,ival){
        id_emailsto_listadd(ival['ct_email'],ival['ct_name']);
    });
}
//brand[ATE]
</script>    