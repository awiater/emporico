<?= $currentView->includeView('System/form') ?>
<script>
$(function(){
    $('#id_formview_submit')
            .removeClass('btn-success')
            .addClass('btn-danger')
            .html('<i class="far fa-play-circle mr-2"></i><?= lang('system.settings.query_run')?>');
});
</script>