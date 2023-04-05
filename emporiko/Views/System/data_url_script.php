<?php if(empty($_just_script_content)) :?>
<script>
<?php endif ?>
$('[data-url], a[class="nav-link"], [data-loader]').on('click',function(){
    var loader=false;
    if ($(this).attr('data-noloader')==undefined && $(this).attr('data-newtab')==undefined){
        loader=true;
    }
    var url=null;
    if ($(this).attr('href')!=undefined){
        url=$(this).attr('href');
    }
    if ($(this).attr('data-url')!=undefined){
        url=$(this).attr('data-url');
    }
    
    if ($(this).attr('data-delete')!=undefined){
        var dlg_loader=loader;
        var dlg_url=url;
        ConfirmDialog('<?= lang('system.general.msg_delete_ques')?>',function(){
            if (dlg_loader){
                addLoader('<?= $container?>'); 
            }
            window.location=dlg_url; 
        });
        loader=false;
        url=null;
    }
    if (loader){
       addLoader('<?= $container?>'); 
    }
    
    if (url!=null){
        if ($(this).attr('data-newtab')==undefined){
            window.location=url;
        }else{
            window.open(url);
        }
    }
});
<?php if(empty($_just_script_content)) :?>
</script>
<?php endif ?>
        