<div class="card card-widget widget-user-2" style="height:500px;">
    <div class="widget-user-header bg-warning">
        <h3 class="card-title font-weight-bold"><?= lang('products.brand_list_tile_title') ?></h3>
        <span class="float-right mr-1">
            <?php if (!empty($edit_acc) && $edit_acc) :?>
            <button type="button" class="btn btn-xs btn-danger float-right ml-1" onclick="brand_update_modal_show(1)" data-toggle="tooltip" data-placement="top" title="<?= lang('products.brand_list_tile_upd') ?>">
                <i class="far fa-edit"></i>
            </button>
            <a class="btn btn-xs btn-dark float-right ml-1" href="<?=url('Products','brands',[],['refurl'=>current_url(FALSE,TRUE)])?>" data-toggle="tooltip" data-placement="top" title="<?= lang('products.brands_list') ?>">
                <i class="fas fa-tasks"></i>
            </a>
            <?php endif ?>
            <button type="button" class="btn btn-xs btn-primary float-right ml-1" data-toggle="tooltip" data-placement="top" title="<?= lang('products.brand_list_tile_title') ?>" onclick="$('#brand_update_fullmodal').modal('show');">
                <i class="fas fa-calendar-week"></i>
            </button>
            <button type="button" class="btn btn-xs btn-secondary float-right" onclick="printTile('#brands_updates_tile_print','<?= lang('products.brand_list_tile_title') ?>')" data-toggle="tooltip" data-placement="top" title="<?= lang('system.buttons.print') ?>">
                <i class="fa fa-fas fa-print"></i>
            </button>
            
        </span>       
    </div>
    <div class="card-footer bg-white border-top p-0">
        <div class="container" style="max-height:420px;overflow-y: scroll;">
            <ul class="products-list product-list-in-card pl-2 pr-2">
                <?php foreach($brands as $brand) :?>
                <li class="item">
                    <div class="product-img">
                        <img src="<?=parsePath($brand['prb_logo'])?>" class="img-size-50" alt="<?= $brand['prb_name'] ?>"> 
                    </div>
                    <div class="product-info">
                        <span class="product-title">
                            <?= $brand['prb_name'] ?>
                            <?php if (strlen($brand['lastupdt']) > 0 || strlen($brand['nextupdt']) > 0) :?>
                            <span class="float-right">
                                <?php if (strlen($brand['lastupdt']) > 0) :?>
                                <small class="row">
                                <?= lang('products.brand_list_tile_last') ?>:&nbsp;&nbsp;
                                <p class="rounded p-0 pl-1 pr-1 font-size-sm bg-<?= $brand['lastupdt']== formatDate('now',TRUE,'Ymd0000') ? 'cyan' : 'green' ?>">
                                    <?=  strtoupper(convertDate($brand['lastupdt'], null, 'd M Y')) ?>
                                </p>
                                </small>
                                <?php endif ?>
                                <?php if (strlen($brand['nextupdt']) > 0) :?>
                                <small class="row">
                                    <?= lang('products.brand_list_tile_next') ?>:&nbsp;&nbsp;
                                    <p class="rounded p-0 pl-1 pr-1 font-size-sm bg-indigo">
                                        <?=  strtoupper(convertDate($brand['nextupdt'], null, 'd M Y')) ?>
                                    </p>
                                </small>
                                <?php endif ?>
                            </span>
                            <?php endif ?>
                        </span>
                        
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="brand_update_fullmodal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?= lang('products.brand_list_tile_title') ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
           <?= $currentView->includeView('Products/Tiles/brand_updates_calendar') ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">
                <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.close') ?>
            </button>
        </div>
        </div>
    </div>
</div>
<?php if (!empty($edit_acc) && $edit_acc) :?>
<div class="modal" tabindex="-1" role="dialog" id="brand_update_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('products.brand_update_widget_title') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= $currentView->includeView('Products/Tiles/brand_update_tile',) ?>
            </div>
            <div class="modal-footer">
                <button type="button" id="brand_update_modal_save" class="btn btn-sm btn-success">
                    <i class="far fa-save mr-1"></i><?= lang('system.buttons.save') ?>
                </button>
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.close') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function brand_update_modal_show(brand){
        $("#brand_update_widget_brands_list").val(brand).trigger('change');
        $("#brand_update_modal").modal('show');  
    }
    
    $("#brand_update_modal_save").on('click',function(){
        var url='<?= $url ?>';
        if ($("#brand_update_widget_brands_list").find(':selected').val()==undefined){
            Dialog('<?= lang('products.error_brandtile_nobrand') ?>','warning');
        }else{
            url=url.replace('-date-',$("#brand_update_widget_date_value").val());
            url=url.replace('-id-',$("#brand_update_widget_brands_list").find(':selected').val());
        }
        window.location=url;
    });
    
</script>

<?php endif ?>
