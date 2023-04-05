<div class="input-group">
    <input type="text" class="form-control" value="<?= $args['value'] ?>" id="<?= $args['id'].'_input' ?>" onkeydown="return false" style="caret-color: transparent;"<?= array_key_exists('required', $args) ? ' required="true"':''?>>
    <div class="input-group-append">
        <?php if(!empty($args['_uploadurl'])) :?>
        <button type="button" class="input-group-text btn bg-dark" data-url="<?= $args['_uploadurl'] ?>" data-noloader="true">
            <i class="fas fa-file-download"></i>
        </button>
        <?php endif ?>
        <label class="input-group-text btn bg-primary rounded-right" for="<?= $args['id'].'_file' ?>">
            <i class="far fa-folder-open"></i>
        </label >
        
        <?php if (array_key_exists('readonly', $args) && $args['readonly']) :?><?php else :?>
        <input type="file" id="<?= $args['id'].'_file' ?>" class="d-none"<?= array_key_exists('accept', $args) ? ' accept="'.$args['accept'].'"' : ''?> name="<?= $name ?>">
        <?php endif ?>
        <?php if(array_key_exists('_uploads_dir', $args)) :?>
         <input type="hidden" name="_uploads_dir" value="<?= $args['_uploads_dir'] ?>">
        <?php endif ?>
         <?php if(array_key_exists('_upload_filename', $args)) :?>
         <input type="hidden" name="_upload_filename" value="<?= $args['_upload_filename'] ?>">
        <?php endif ?>
        <?php if(array_key_exists('_export_justname', $args)) :?>
         <input type="hidden" name="_export_justname" value="<?= $args['_export_justname'] ?>">
        <?php endif ?>
         <?php if(array_key_exists('_storage_engine', $args)) :?>
         <input type="hidden" name="_storage_engine" value="<?= $args['_storage_engine'] ?>">
        <?php endif ?>
    </div>
</div>

<script>
$('#<?= $args['id'].'_file' ?>').on('change',function(){
    $("#<?= $args['id'].'_input' ?>").val($(this).val());
});
</script>