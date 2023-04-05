<?= $currentView->includeView('System/table') ?>
<div class="modal" tabindex="-1" role="dialog" id="id_tickets_newmodal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('tickets.tck_newmodal_title')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close')?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="overflow-y: scroll;height:450px;">
                <?php foreach(!empty($templates) && is_array($templates) ? $templates : [] as $template) :?>
                <div class="row border">
                    <div class="col-12">
                        <div class="small-box bg-<?= $template['type'] ?>">
                            <div class="inner">
                                <strong style="color:<?= $template['text_color'] ?>"><?= $template['title'] ?></strong>
                                <p><small style="color:<?= $template['text_color'] ?>"><?= $template['desc'] ?></small></p>
                            </div>
                            <div class="icon" style="color: rgba(0,0,0,.05)!important;">
                                <i class="<?= $template['icon'] ?>"></i>
                            </div>
                            <button type="button" class="btn btn-link p-0 w-100 small-box-footer" data-tile="<?= $template['url'] ?>" style="color:<?= $template['text_color'] ?>">
                                <?= lang('tickets.tck_newmodal_btnopen') ?><i class="fas fa-arrow-circle-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
    });
    
    $('button[data-tile]').on('click',function(){
        $('#id_tickets_newmodal').modal('hide');
        addLoader('.table');
        window.location=$(this).attr('data-tile');
    });
</script>