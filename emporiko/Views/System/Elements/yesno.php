<?php if (empty($args['wrapped'])) :?>
<br>
<?php endif ?>
<input type="checkbox"<?= $value ? ' checked ' :''?>data-toggle="toggle" id="<?= $args['id']?>_label" data-on="<?= $options[1] ?>" data-off="<?= $options[0] ?>"<?= array_key_exists('readonly', $args) && $args['readonly'] ? ' disabled="disabled"': '' ?>>
<input type="hidden" name="<?= $args['name'] ?>" id="<?=$args['id']?>_value" value="<?= $value ?>">
<script>
    $('#<?= $args['id']?>_label').on('change',function(){
        $('#<?=$args['id']?>_value').val($(this).is(':checked') ? 1 :0);
        
    });

</script>