<?= $this->extend('System/form') ?>
<?= $this->section('form_script_afterload') ?>
<?php if (!empty($script)) :?>
<script>
<?php endif ?>
$('#id_filemap_addnew').on('click',function(){
    if ($('tr[id^="id_filemap_tablerow_"]').length >= <?= count($record['columns']) ?>){
       Dialog('<?=lang('system.settings.uploadtpls_cols_addnewerror')?>','warning'); 
    }else{
        $('#id_filemap_modal').modal('show');
    }  
});

$('#id_formview_submit').removeAttr('onclick').on('click',function(){
    editformValidate(function(){
        if($('tr[id^="id_filemap_tablerow_"]').length < 1){
            Dialog('<?=lang('system.settings.uploadtpls_cols_addnewnoitemserror')?>','warning'); 
        }else{
            $('#edit-form').submit(); 
        } 
    });
});

$('#id_filemap_modal_addbtn').on('click',function(){
    if ($('#id_filemap_table').find('input[value="'+$('#id_filemap_modal_modelcol').find('option:selected').val()+'"]').length>0){
        Dialog('<?=lang('system.settings.uploadtpls_cols_addnewexistserror')?>','warning'); 
    }else{
        id_filemap_addnewrow(
        {
            'column':$('#id_filemap_modal_modelcol').find('option:selected').val(),
            'file_column':$('#id_filemap_modal_filecol').val()
        });
        $('#id_filemap_modal_filecol').val(0);
        $('#id_filemap_modal').modal('hide');
    }
});

$('#id_model').trigger('change');
<?php if (!empty($script)) :?>
</script>
<?php endif ?>
<?= $this->endSection() ?>
<?= $this->section('form_html') ?>
<div class="modal" tabindex="-1" role="dialog" id="id_filemap_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('system.settings.uploadtpls_cols_modal_title') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for=""><?= lang('system.settings.uploadtpls_cols_modelcolumn') ?></label>
                    <?= form_dropdown('column', $record['columns'], [], ['id'=>'id_filemap_modal_modelcol','class'=>'form-control form-control-sm']) ?>
                    <?php $uploadtpls_cols_modelcolumn_tooltip=lang('system.settings.uploadtpls_cols_modelcolumn_tooltip'); if ($uploadtpls_cols_modelcolumn_tooltip!='system.settings.uploadtpls_cols_modelcolumn_tooltip') :?>
                    <small id="id_title_tooltip" class="form-text text-muted"><?= $uploadtpls_cols_modelcolumn_tooltip?></small>
                    <?php endif ?>
                </div>
                <div class="form-group">
                    <label for=""><?= lang('system.settings.uploadtpls_cols_filecolumn') ?></label>
                    <input type="number" class="form-control form-control-sm" min="0" max="1000" id="id_filemap_modal_filecol" onkeydown="return false;">
                    <?php $uploadtpls_cols_filecolumn_tooltip=lang('system.settings.uploadtpls_cols_filecolumn_tooltip'); if ($uploadtpls_cols_filecolumn_tooltip!='system.settings.uploadtpls_cols_filecolumn_tooltip') :?>
                    <small id="id_title_tooltip" class="form-text text-muted"><?= $uploadtpls_cols_filecolumn_tooltip?></small>
                    <?php endif ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm mr-auto" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.close') ?>
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="id_filemap_modal_addbtn">
                    <i class="fas fa-plus-circle mr-1"></i><?= lang('system.buttons.add') ?>
                </button>
                
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>