<div class="card border" id="id_newcase_templates_container">
    <div class="card-header d-flex p-1">
        <h4><?= lang('tickets.tck_newmodal_title') ?></h4>
        <button type="button" class="btn btn-danger btn-sm ml-auto" data-url="<?= $refurl ?>">
            <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel') ?>
        </button>
    </div>
    <div class="card-body overflow-auto p-0">
        <div id="id_newcase_templates_container_body" class="p-2">
            <div class="row">
                <?php $key=0;foreach($templates as $template) :?>
                <div class="col-xs-12 col-md-3">
                    <div class="small-box bg-<?= $template['type'] ?>">
                        <div class="inner">
                            <strong style="color:<?= $template['text_color'] ?>"><?= $template['title'] ?></strong>
                            <p><small style="color:<?= $template['text_color'] ?>"><?= $template['desc'] ?></small></p>
                        </div>
                        <div class="icon" style="color: rgba(0,0,0,.05)!important;">
                            <i class="<?= $template['icon'] ?>"></i>
                        </div>
                        <button type="button" class="btn btn-link p-0 w-100 small-box-footer" data-url="<?= $template['url'] ?>" style="color:<?= $template['text_color'] ?>">
                            <?= lang('tickets.tck_newmodal_btnopen') ?><i class="fas fa-arrow-circle-right ml-1"></i>
                        </button>
                    </div>
                </div>
                <?php $key++;if ($key==4) : ?>
                </div><div class="row">
                <?php $key=0; ?>
                <?php endif; ?>    
                <?php endforeach; ?>
            </div>
        </div>       
    </div>
</div>