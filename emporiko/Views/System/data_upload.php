<?php if ($use_modal) :?>
<div class="modal" tabindex="-1" role="dialog" id="<?= $modal_id?>">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $title==null ? '' : lang($title) ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php endif ?>
                <?= form_open_multipart(!empty($upload_url) ? $upload_url : '', ['id'=>$form_id], ['upload_key'=>$input_name]) ?>
                <label><?= $label?></label>
                <div class="input-group mb-1">
                    <input type="text" class="form-control" value="" id="<?= $input_field_id ?>" onkeydown="return false" style="caret-color: transparent;">
                    <div class="input-group-append">
                        <label class="input-group-text btn bg-primary rounded-right" for="<?= $input_id ?>">
                            <i class="far fa-folder-open"></i>
                        </label>
                        <input type="file" id="<?= $input_id ?>" class="d-none" accept="<?= $input_format ?>" name="<?= $input_name ?>">
                    </div>
                    <script>
                        $('#<?= $input_id ?>').on('change',function(){
                            $("#<?= $input_field_id ?>").val($(this).val());
                        });
                    </script>
                </div>
                <?= form_close() ?>
                <?php if ($use_modal) :?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.close') ?>
                </button>
                <button type="button" data-uploadform="<?= $form_id ?>" class="btn btn-primary">
                    <i class="fas fa-cloud-upload-alt mr-1"></i><?= lang('system.buttons.upload') ?>
                </button>
                <script>
                    $('button[data-uploadform]').on('click',function(){
                        addLoader('#<?= $modal_id?> .modal-body');
                        $("#"+$(this).attr('data-uploadform')).submit();
                    });
                    <?php if (!empty($button_id) && is_string($button_id)) :?>
                    $('#<?=$button_id?>').on('click',function(){
                        $('#<?= $modal_id?>').modal('show');
                    });
                    <?php endif ?>
                </script>
            </div>
        </div>
    </div>
</div>
<?php endif ?>
 
