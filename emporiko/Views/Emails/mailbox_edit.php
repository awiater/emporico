<?= $currentView->includeView('System/form') ?>
<div class="mr-auto" id="id_mailboxedit_actionbtns">
    <button class="btn btn-dark" id="id_mailboxedit_btntest" data-toggle="tooltip" data-placement="top" title="<?= lang('emails.emm_btntest_tooltip') ?>">
        <i class="far fa-check-circle mr-1"></i><?= lang('emails.emm_btntest') ?>
    </button>
    <?php if (!empty($url_sync)) :?>
    <button class="btn btn-danger ml-1" id="id_mailboxedit_btnsync" data-toggle="tooltip" data-placement="top" title="<?= lang('emails.emm_btnsync_tooltip') ?>">
        <i class="fas fa-sync mr-1"></i><?= lang('emails.emm_btnsync') ?>
    </button>
    <?php endif ?>
</div>
<script>
    $(function(){
        $('#id_formview_submit').parent().before($('#id_mailboxedit_actionbtns').detach());
    });
    
    $('#id_mailboxedit_btntest').on('click',function(){
       var data=new FormData(document.querySelector('form'));
       var form_data={};
       for(var record of data.entries())
       {
           form_data[record[0]]=record[1];
       }
       addLoader('.card');
       ajaxCall('<?= $url_test ?>',form_data,
       function(data){
           killLoader();
           Dialog(data['msg'],data['error']==1 ? 'danger' : 'success');
       },
        function(data){console.log(data);killLoader();}
       );/**/
       
    });
    <?php if (!empty($url_sync)) :?>
    $('#id_mailboxedit_btnsync').on('click',function(){
        var url='<?= $url_sync?>';
        addLoader('.card');
        ConfirmDialog('<?= lang('emails.msg_sync_fresh')?>',function(){
            url=url.replace('sync','sync_fresh');
            window.location=url;
        },function(){
            window.location=url;
        });
        
    });
    <?php endif ?>
</script>