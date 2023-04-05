<?php if (empty($noscript_print)) :?>
<script>
<?php endif ?>
$("#<?= $button_id ?>").on('click',function(){
$("#<?= $container_id ?>").printThis({
            debug: false,              
            importCSS: true,             
            importStyle: true,         
            printContainer: true,       
            pageTitle: "<?= $title ?>",             
            removeInline: false,        
            printDelay: 133,            
            header: '<h3><?= $header ?></h3>',             
            formValues: true 
    });
});
<?php if (empty($noscript_print)) :?>
</script>
<?php endif ?>