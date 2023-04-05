<div class="input-group">
    <?= $field ?>
    <div class="input-group-append">
        <?= form_button($button_args); ?>
    </div>
</div>
<script>
    function <?= $args['id'] ?>_check(){
        var elem=$('#<?= $args['id'] ?>');
        var cls='fas fa-lock-open';
        if (elem.is('select') && elem.attr('disabled')==undefined){
            cls='fas fa-lock';
            elem.attr('disabled',true);
        }else    
        if (elem.attr('readonly')==undefined && !elem.is('select')){
            cls='fas fa-lock';
            elem.attr('readonly',true);
        }else{
            elem.removeAttr('readonly');
            elem.removeAttr('disabled');
        }
        $('#<?= $button_args['id'] ?>').find('i').attr('class',cls);
    }
</script>