<?= $currentView->includeView('System/form') ?>
<?php if (array_key_exists('_email', $record) && is_array($record['_email'])) :?>
<div id="form_toolbar">
    <?php if (array_key_exists('_email', $record) && is_array($record['_email'])) :?>
    <button class="btn bg-info" data-toggle="tooltip" data-placement="right" title="<?= lang('orders.opportunities_msg_convertemail_src') ?>" id="form_toolbar_convert" onclick="$('#opport_emailviewer').modal('show');">
        <i class="fas fa-envelope-open-text"></i>
    </button>
    <div class="modal" tabindex="-1" role="dialog" id="opport_emailviewer">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('orders.opportunities_msg_convertemail_src') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= $currentView->includeView('Emails/view',['record'=>$record['_email'],'_external_call'=>TRUE]); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?= lang('system.buttons.close') ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php endif ?>
</div>
<?php endif ?>
<script>
$(function(){
    $('#id_formview_submit').parent().before($('#form_toolbar').detach());
});    
</script>