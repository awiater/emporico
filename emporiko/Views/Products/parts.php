<div class="col-12" id="id_parts_container">
    <div id="id_products_view_msgalert">
        <?php if (!$record['enabled']) :?>
        <?= $currentView->getErrorBar('products.msg_obsolete_part','danger',TRUE) ?>
        <?php endif ?>
    </div>
    <div class="breadcrumb p-1">
        <?= !empty($toolbar) ? $toolbar : '' ?>
    </div>
    <?php $card_color='info'; ?>
    <div class="row">
        <div class="col-xs-12 col-md-4">
            <div class="card card-outline card-<?= $card_color?>">
                <div class="card-body">
                    <?php foreach(['prd_brand','prd_productfamily','prd_apdpartnumber','prd_tecdocpart','prd_description'] as $key) :?>
                    <div class="form-group row">
                        <label for="id_products_view_<?= $key?>" class="col-sm-4 col-form-label">
                            <?= lang('products.'.$key) ?>
                        </label>
                        <div class="col-sm-8">
                            <div class="form-control text-right" id="id_products_view_<?= $key?>">
                                <?=$record[$key]?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="form-group row">
                        <label for="id_products_view_enabled" class="col-sm-4 col-form-label">
                            <?= lang('products.prd_enabled') ?>
                        </label>
                        <div class="col-sm-8">
                            <div class="form-control text-right" id="id_products_view_enabled">
                                <?= lang('products.prd_enabled_status_'.$record['enabled']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           
            <!-- Other Details -->
            <div class="card card-outline card-<?= $card_color?>">
                <div class="card-body">
                    <?php foreach(['prd_weight','prd_origin','prd_tecdocid','prd_commodity','prd_unitofissue','prd_boxqty','prd_leadtime'] as $key) :?>
                    <div class="form-group row">
                        <label for="id_products_view_<?= $key?>" class="col-sm-4 col-form-label">
                            <?= lang('products.'.$key) ?>
                        </label>
                        <div class="col-sm-8">
                            <div class="form-control text-right" id="id_products_view_<?= $key?>">
                                <?=$record[$key]?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Other Details -->
        </div>
        <div class="col-xs-12 col-md-4">
             <!-- Pricing -->
            <div class="card card-outline card-<?= $card_color?>">
                <div class="card-body">
                    <?php if (count($record['pricefiles']) < 1) :?>
                    <?= $currentView->getErrorBar('products.error_products_nopricefile','warning',TRUE) ?>
                    <?php endif?>
                    <?php foreach($record['pricefiles']  as $key=>$value) :?>
                    <div class="form-group row">
                        <label for="id_products_view_<?= $key?>" class="col-sm-4 col-form-label">
                            <?= lang($price_label,[$key]) ?>
                        </label>
                        <div class="col-sm-8">
                            <div class="input-group" id="id_products_view_<?= $key?>">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <?php if (array_key_exists($value['ppf_curr'], $curr_icons)):?>
                                            <?php if (substr($curr_icons[$value['ppf_curr']], 0,2)=='fa') :?>
                                            <i class="<?= $curr_icons[$value['ppf_curr']] ?>"></i>
                                            <?php else :?>
                                            <b><?= $value['ppf_curr']?></b>
                                            <?php endif ?>
                                        <?php else :?>
                                        <i class="far fa-money-bill-alt"></i>
                                        <?php endif;?>
                                    </span>
                                </div>
                                <div class="text-right form-control">
                                    <?= $value['prf_price'] ?>
                                </div>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-calendar-check mr-3" data-toggle="tooltip" data-placement="left" title="<?= lang('products.price_updated',[convertDate($value['ppf_updated'], null, 'd M Y')])?>"></i>
                                        <i class="fas fa-link cur-pointer" data-toggle="tooltip" data-placement="right" title="<?= lang('products.msg_pricefile_link')?>" onclick="copyurl('<?= base64url_encode($key)?>')"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- /Pricing -->
        </div>
        <div class="col-xs-12 col-md-4">
            <!-- Order History -->
            <?php if (!empty($record['_orders']) && is_array($record['_orders'])) :?>
            <div class="card">
                <div class="card-header p-2">
                    <h5 class="card-title font-weight-bold"><?= lang('products.head_orders') ?></h5>
                </div>
                <?php if(count($record['_orders']) < 1) :?>
                <div class="card-body">
                    <h6><?= lang('customers.error_noactivity') ?></h6>
                </div>
                <?php else :?>
                <div class="p-1" style="max-height:420px;overflow-y: scroll;">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th style="width:50%"><small><b>Order</b></small></th>
                                <th style="width:30%"><small><b>Customer</b></small></th>
                                <th style="width:10%"><small><b>Qty</b></small></th>
                                <th style="width:20%"><small><b>Price</b></small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($record['_orders'] as $order) :?>
                            <tr<?= !empty($orderurl) ? ' data-url="'. str_replace('-id-', $order['orderid'], $orderurl).'" style="cursor:pointer!important"' :''?>>
                                <td>
                                    <div class="p-0"><?= $order['order'] ?></div>
                                    <small class="text-mutted"><?= $order['status']?></small>
                                </td>
                                <td><?= $order['customer']?></td>
                                <td><?= $order['qty'] ?></td>
                                <td><?= $order['price'] ?></td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <?php endif ?>
            </div>
            <?php endif ?>
            <!-- /Order History -->
            
            <!-- Movements -->
            <?php if (!empty($movements) && is_array($movements)) :?>
            <div class="card">
                <div class="card-header p-2">
                    <h5 class="card-title font-weight-bold"><?= lang('products.mov_head_activity') ?></h5>
                </div>
                <?php if(count($movements) < 1) :?>
                <div class="card-body">
                    <h6><?= lang('customers.error_noactivity') ?></h6>
                </div>
                <?php else :?>
                <ul class="list-group list-group-flush" style="max-height:420px;overflow-y: scroll;">
                    <?php foreach($movements as $movement) :?>
                    <li class="list-group-item p-1 mb-1">
                        <div class="w-100"><?= lang($movements_types[$movement['mhtype']],$movement);?></div>
                        <small class="mr-auto">
                            <?= convertDate($movement['mhdate'],null,'d M Y H:i')?>
                        </small>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </div>
            <?php endif ?>
            <!-- /Movements -->
        </div>
    </div>
</div>

<?php if(!empty($download_form)) :?>
<div class="modal" tabindex="-1" role="dialog" id="id_products_modal_dwonload">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('products.modal_dwonload_title') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?=$download_form?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel') ?>
                </button>
                <button type="button" id="id_products_modal_dwonload_btn" class="btn btn-primary">
                    <i class="fas fa-cloud-download-alt mr-1"></i><?= lang('products.btn_download') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif ?>

<?php if(!empty($upload_form)) :?>
<!-- Modal Upload -->
<div class="modal" tabindex="-1" role="dialog" id="id_products_modal_upload">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('products.modal_upload_title') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open_multipart($url_upload, ['id'=>'id_products_modal_upload_form'], []) ?>
                <p class="font-weight-bold">
                    <?=lang('system.settings.upload_data_label',[url_tag(url('Products','upload',['template']),'<i class="fas fa-file-csv"></i>',['class'=>'p-0'])]) ?>
                </p>
                <?=$upload_form?>
                <?= form_close();?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel') ?>
                </button>
                <button type="button" form="id_products_modal_upload_form" class="btn btn-primary">
                    <i class="fas fa-cloud-upload-alt mr-1"></i><?= lang('system.buttons.upload') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- / Modal Upload -->
<?php endif ?>

<?php if (array_key_exists('_filters', $record) && is_array($record['_filters']) && count($record['_filters']) > 0) :?>
<?= $currentView->includeView('Products/products_multiresults') ?>
<?php endif ?>

<script>
    $(function(){
        $('#menu').addClass('navbar-white bg-white').removeClass('navbar-light bg-light');
        $('.breadcrumb .alert').addClass('m-0 col-12');
        <?php if (array_key_exists('_filters', $record) && is_array($record['_filters']) && count($record['_filters']) > 0) :?>
        $('#id_products_view_filter_listmodal').modal('show');
        <?php endif ?>
    });
    
    <?php if (array_key_exists('_filters', $record) && is_array($record['_filters']) && count($record['_filters']) > 0) :?>
    
    function showFilterResulDetails(part){
            $('input[name="filter_part"]').val(part);
            $('#id_products_view_filter_listmodal').modal('hide');
            addLoader('body');
            $('#id_products_btn_search_form').submit();
    }
    <?php endif ?>
        
    function copyurl(id){
        var url='<?= url('Portal','pricefile.html',[],['file'=> '@-id-','brand'=>$record['prd_brand']])?>';
        url=url.replace('-id-',id);
        copyToClipboard(url);
        Dialog('<?= lang('products.msg_copy_url_ok')?>'.replace('{0}',url),'info');
    }
    
    $('#id_products_btn_download').on('click',function(){
        $('#id_products_modal_dwonload').modal('show');
    });
    $('#id_brand').on('change',function(){
        $(this).attr('value',$(this).find(':selected').val());
    });
    
    $('#id_products_modal_dwonload_btn').on('click',function(){
        var url=$('#id_products_btn_download').attr('data-action');
        <?php if ($record['edit_acc']) :?>
        url=url.replace('-customer-',$('#id_customer').find(':selected').val());
        <?php else :?>
        url=url.replace('-customer-',$('[name="customer"]').val());
        <?php endif ?>
        url=url.replace('-brand-',$('#id_brand').attr('value'));
        $('#id_products_modal_dwonload').modal('hide');
        $('#id_products_view_msgalert').html(atob('<?= base64_encode($currentView->getErrorBar('products.msg_download_wait','success'))?>'));
        window.location=url;
    });
    
    $("[form1]").on('click',function(){
        if ($(this).attr('form')=='id_products_modal_upload_form'){
           $('#id_products_modal_upload').modal('hide');
        }
        addLoader('body');
        $('#'+$(this).attr('form')).submit();
    });
</script>