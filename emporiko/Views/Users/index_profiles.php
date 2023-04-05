<?= $currentView->includeView('System/table') ?>

<script>
    
    $("input[name='userid[]'").each(function(){
       var admins=JSON.parse(atob('<?= base64_encode(json_encode($admins))?>'));
        if (jQuery.inArray($(this).val(),admins)>-1){
            $(this).remove();
        }    
    });
</script> 

