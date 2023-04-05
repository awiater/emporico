<?php if ((!empty($_tableview_btns) && is_array($_tableview_btns) && count($_tableview_btns)>0) || !empty($_tableview_filters)) : ?>
<nav class="navbar navbar-expand-lg navbar-light bg-white" id="<?= $table_view_datatable_id; ?>_toolbar_nav">
    <?php if ($currentView->isMobile()) :?>
    <button class="btn btn-secondary btn-mobiletoolbartoogler" type="button" data-toggle="collapse" data-target="#<?= $table_view_datatable_id; ?>_toolbar_items" aria-controls="<?= $table_view_datatable_id; ?>_toolbar_items" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fas fa-grip-horizontal fa-lg"></i>
    </button>
    <?php endif ?>
    <?= !empty($_table_filters) ? $_table_filters : '' ?>
    <div class="collapse navbar-collapse" id="<?= $table_view_datatable_id; ?>_toolbar_items">
        <ul class="navbar-nav ml-auto">
        <?php foreach (!empty($_tableview_btns) ? $_tableview_btns : [] as $button) : ?>
            <li class="nav-item <?= $currentView->isMobile() ? 'mt-1 w-100' : 'ml-1' ?>">
                <?= $button; ?>
            </li>
        <?php endforeach;?>     
        </ul>
    </div>
</nav>
<?php endif ?>
<?php if (!empty($_columns_to_edit) && !empty($_columns_to_edit_url)) :?>
<div class="modal" tabindex="-1" role="dialog" id="id_tableview_coledit_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('system.buttons.columns') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open_multipart($_columns_to_edit_url,['id'=>'id_tableview_coledit_modal_form'],[]) ?>
                <?= $_columns_to_edit ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="id_tableview_coledit_modal_form" class="btn btn-success">
                    <i class="far fa-save mr-1"></i>
                    <?= lang('system.buttons.save') ?>
                </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i>
                    <?= lang('system.buttons.close') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    $("#id_tableview_btn_coledit").on('click',function(){
        $('#id_tableview_coledit_modal').modal('show');
    });
</script>
<?php endif; ?>
