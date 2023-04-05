<div class="card border child-full" id="<?= $table_view_datatable_id; ?>_container">
    <div class="card-header d-flex">
        <?php if (!empty($_tableview_refurl)) :?>
        <div>
            <a href="<?=$_tableview_refurl?>" class="btn btn-info btn-sm btn-table-back mr-2" data-toggle="tooltip" data-placement="left" title="<?= lang('system.buttons.back') ?>">
                <i class="fas fa-arrow-alt-circle-left"></i>
            </a>
        </div>
        <?php endif ?>
        <?php if (!empty($_tableview_card_title) && strlen($_tableview_card_title) > 0 ) :?>
        <h4><?= $_tableview_card_title ?></h4>
         <?php endif ?>
    </div>
    <div class="card-body overflow-auto p-0">
        <div id="<?= $table_view_datatable_id; ?>_toolbar_section">
            <?= $this->renderSection('table_toolbar') ?> 
        </div>
        <div id="<?= $table_view_datatable_id; ?>_body_section" class="p-2">
            <?= $this->renderSection('table_body') ?>
        </div>       
    </div>
</div>
<?= $this->renderSection('table_script') ?>

