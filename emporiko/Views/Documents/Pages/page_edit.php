<?= $currentView->includeView('System/form')?>
<script>
$(function(){
    <?php if (intval($record['pg_restricted'])==0) :?>
    $('#id_pg_order_field').addClass('d-none');
    <?php endif ?>
});
$('#access').on('change',function(){
    var val=$(this).find('option:selected').val();
    if (val==0 || val=='0'){
        $('#id_pg_order_field').addClass('d-none');
        $('input[name="pg_restricted"]').val(0);
    }else{
       $('#id_pg_order_field').removeClass('d-none');
       $('input[name="pg_restricted"]').val(1);
    }
});   
</script>