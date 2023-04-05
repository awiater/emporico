<?= $currentView->includeView('System/table') ?>
<script>
$(function(){
    $('button[data-convert="1"]').remove();
    $('button[data-convert="0"]').each(function(){
        $(this).attr('data-convert',$(this).attr('data-url')).removeAttr('data-url').unbind('click');
        $(this).on('click',function(){
            var url=$(this).attr('data-convert');
            ConfirmDialog('<?= lang('orders.opportunities_msg_convert')?>',function(){
               addLoader();
               window.location=url;
            });
        });
    });
   
});    
</script>