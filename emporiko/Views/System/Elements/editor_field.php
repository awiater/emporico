<?php if (!empty($placeholders) && is_array($placeholders) && count($placeholders) > 0) :?>
<div class="row">
    <div class="col-xs-12 col-md-10">
<?php endif; ?>
<textarea name="<?= $args['name'] ?>" cols="40" rows="10" class="form-control" id="<?= $args['id'] ?>">
    <?= !empty($args['value']) ? $args['value'] : ''?>
</textarea>
<?php if (!empty($placeholders) && is_array($placeholders) && count($placeholders) > 0) :?>        
    </div>
    <div class="col-xs-12 col-md-2">
        <ul class="list-group" style="min-height:250px;max-height:420px;overflow-y: scroll;">
        <?php foreach($placeholders as $key=>$value) :?>
            <li class="list-group-item">
                <div>{<?= $key ?>}</div>
                <small class="text-muted">
                    <?= $value ?>
                </small>
                <div class="float-right">
                    <button type="button" class="btn btn-xs btn-dark" onclick="copyToClipboard('{<?=$key?>}');Dialog('<?= lang('emails.msg_tag_copytoclip') ?>','info');">
                        <i class="far fa-clipboard"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-primary" onclick="tinymce.activeEditor.execCommand('mceInsertContent', false, '{<?=$key?>}');">
                        <i class="fas fa-plus-square"></i>
                    </button>
                </div>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($scripts)):?>
<script>
    <?=$scripts?>
</script>
<?php endif; ?>


