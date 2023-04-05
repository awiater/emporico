<div id="<?= $args['id']?>" class="input-group">
  <input type="text" class="<?= array_key_exists('editor', $args) && !$args['editor'] ? 'd-none' : 'form-control'?>" value="<?= $value ?>" name="<?= $name ?>"/>
  <span class="input-group-append">
    <span class="input-group-text colorpicker-input-addon" id="<?= $args['id'] ?>_button">
        <i></i>
    </span>
  </span>
</div>

<script>
$(function(){
  $('#<?= $args['id']?>').colorpicker();
  <?php if (array_key_exists('editor', $args) && !$args['editor']) :?>
  $("#<?= $args['id'] ?>_button").parent().css('width','100px');
  $("#<?= $args['id'] ?>_button").css('width','100%').find('i').css('width','100%');
  <?php endif ?>
});
</script>