<?php if(!empty($record['_msg'])) :?>
<?= $currentView->includeView('errors/html/exception',['msg'=>$record['_msg'],'type'=>'warning']); ?>
<?php endif ?>
<?= $currentView->IncludeView('System/form') ?>
<?php if(is_numeric($record['cjid'])) :?>
<button type="button"  class="btn btn-danger mr-auto" id="id_crontab_deletebtn" data-url="<?=$url_delete?>" data-delete="true">
    <i class="far fa-save mr-1"></i><?= lang('system.buttons.remove') ?>              	            
</button>
<?php endif ?>
<script>
$(function(){
    <?php if(is_numeric($record['cjid'])) :?>
    $('#id_formview_submit').parent().before($('#id_crontab_deletebtn').detach());
    <?php endif ?>
    $('#id_command').on('change',function(){
        $('#id_name').val($(this).find('option:selected').text());
    });
});
</script>