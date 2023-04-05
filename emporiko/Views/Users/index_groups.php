<?= $currentView->includeView('System/table') ?>

<script>
    $(function(){
        $("input[value='<?=$currentView->getViewData('superadmin')['ugid']?>']").addClass('d-none');
    });
</script>   