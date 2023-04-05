<?php if (!empty($script) && $script) :?>
<script>
    $(function(){
<?php endif ?>
        $('#<?= $args['id'] ?>').on('change',function(){
        var regex = /(?:https?):\/\/(\w+:?\w*)?(\S+)(:\d+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;
        var val=$('#<?= $args['id'] ?>').val();
        if(val.length > 0 && !regex .test(val)) {
            Dialog('<?= $message ?>','warning');
        }
        });
<?php if (!empty($script) && $script) :?>        
    });
</script>
<?php endif ?>