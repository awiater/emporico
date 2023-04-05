<div class="col-12" id="id_parts_container">
    <?php $card_color='info'; ?>
    <?php if (!empty($msg_alert)) :?>
    <?= $msg_alert?>
    <?php $card_color='danger'; ?>
    <?php endif?>
    <div class="row">
        <div class="col-xs-12 col-md-4">
            <!-- Filters -->
            <div class="card card-outline card-<?= $card_color?>">
                <div class="card-body">
                    <div class="form-group row">
                        <label for="id_products_view_filter" class="col-sm-4 col-form-label">
                            <?= lang('system.buttons.filter') ?>
                        </label>
                        <div class="col-sm-8">
                            <?=form_open($url_filter,['id'=>'id_products_view_filter_form']) ?>
                            <div class="input-group">
                                <input type="text" class="form-control" name="filter_part" value="<?= !empty($_filter_value) ? $_filter_value : '' ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-secondary" form="id_products_view_filter_form">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                                <?php if (array_key_exists('_filters', $record) && is_array($record['_filters']) && count($record['_filters']) > 0) :?>
                                <button type="button" class="btn btn-primary ml-2" onclick="$('#id_products_view_filter_listmodal').modal('show')" data-toggle="tooltip" data-placement="left" title="<?=lang('products.msg_filters_many_title')?>">
                                    <i class="fas fa-asterisk"></i>    
                                </button>
                                <?php endif ?>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Filters -->
            
            <!-- Toolbar -->
            <div class="card card-outline card-<?= $card_color?>">
                <div class="card-body">
                    <?php if (!empty($download_url)) :?> 
                    <div class="form-group row">
                        <label for="id_down_brand" class="col-sm-4 col-form-label">
                            <?= lang('products.prd_brand') ?>
                        </label>
                        <div class="col-sm-8">
                            <?php if (is_array($download_url)) :?>                            
                            <?= form_dropdown('brand', $download_url['brands'], [$record['prd_brand']], ['class'=>'form-control select2','id'=>'id_down_brand']); ?>                            
                            <?php else :?>
                            <div class="form-control">
                                <?= $record['mode']['brand'] ?>
                            </div>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="form-group row p-0">
                        <div class="col-12 p-0">
                            <button type="button" class="btn btn-success mr-2 float-right" <?= is_string($download_url) ? 'data-url="'.$download_url.'" data-noloader="true"':'data-download="'.$download_url['url'].'"' ?>>
                                <i class="fa fa-fas fa-download mr-1"></i><?=lang('products.download_pricefile')?>
                            </button>
                        </div>
                    </div>
                    <?php endif ?>
                </div>
            </div>
            <!-- / Toolbar -->
            
        </div>
        <div class="col-xs-12 col-md-4">
            <!-- Main Details --> 
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
            <!-- / Main Details -->
           
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
            
            <?php if(is_array($record['mode']) && array_key_exists('showcost', $record['mode']) && intval($record['mode']['showcost']) > 0) :?> 
            <!-- Pricing -->
            <div class="card card-outline card-<?= $card_color?>">
                <div class="card-body">
                    <div class="form-group row">
                        <label for="id_products_view_price_updated" class="col-sm-4 col-form-label">
                            <?= lang('products.price_updated',['']) ?>
                        </label>
                        <div class="col-sm-8">
                            <div class="form-control text-right" id="id_products_view_price_updated">
                                <?= array_key_exists($record['customer'],$record['pricefiles']) ? convertDate($record['pricefiles'][$record['customer']]['ppf_updated'], null, 'd M Y') : '' ?>
                            </div>
                        </div>
                    </div>
                    <?php foreach($record['pricefiles'] as $key=>$value) :?>
                    <div class="form-group row">
                        <label for="id_products_view_<?= $key?>" class="col-sm-4 col-form-label">
                            <?= lang($price_label,[$key]) ?>
                        </label>
                        <div class="col-sm-8">
                            <div class="input-group">
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
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- /Pricing -->
            <?php endif ?>
            
            <?php if (!empty($_quotebasket)) :?>
            <!-- Quote Bakset -->
            <div class="card card-outline card-<?= $card_color?>">
                <div class="card-header p-1">
                    <h6><?= lang('products.msg_quote_basket_title') ?></h6>
                </div>
                <div class="card-body">
                    <?= form_open(current_url(),['id'=>'id_products_view_basket_form'],['basket_part'=>$record['prd_apdpartnumber']]) ?>
                    <div class="form-group row">
                        <div class="input-group">
                            <input type="number" name="basket_qty" class="form-control form-control-sm mr-1" step="1" min="1" placeholder="<?= lang('products.prd_qty') ?>">
                            <?php if (!empty($record['customer_curr_icon']) && strlen($record['customer_curr_icon']) > 0) :?>
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="<?= $record['customer_curr_icon']?>"></i>
                                </span>
                            </div>
                            <?php endif ?>
                            <input type="text" name="basket_price" class="form-control form-control-sm"  dir="rtl" placeholder="<?= lang('products.prd_price',['']) ?>">
                            <div class="input-group-append">
                                <button type="submit" form="id_products_view_basket_form" class="btn btn-sm btn-dark" data-toggle="tooltip" data-placement="left" title="<?= lang('products.prd_qty_tooltip')?>">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?= form_close() ?>
                    <?php if(is_string($_quotebasket)) :?>
                    <div class="alert alert-warning">
                        <?= lang($_quotebasket) ?>
                    </div>
                    <?php elseif (is_array($_quotebasket)) :?>
                    <table class="table">
                        <tbody>
                        <?php foreach($_quotebasket as $part=>$value) :?>
                            <tr>
                                <td><?= $part?></td>
                                <td><?= $value['qty']?></td>
                                <td><?= $value['price']?></td>
                                <td>
                                    <?=form_open(current_url(),['id'=>'id_products_view_basket_remove_form_'. base64url_encode($part)],['basket_part_del'=>$part]) ?>
                                    <button class="btn btn-xs btn-danger" type="submit" form="id_products_view_basket_remove_form_<?= base64url_encode($part)?>">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                    <?= form_close() ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tbody>
                    </table>
                    <hr>
                    <?= form_open(current_url(),['id'=>'id_products_view_basket_form_confirm'],['basket_line'=> base64_encode(json_encode($_quotebasket))]) ?>
                    <div class="form-group row pl-2 pr-2">
                        <div class="input-group" id="basket_ref_field">
                            <input type="text" name="basket_ref" class="form-control form-control-sm mr-1" placeholder="<?= lang('products.quote_ref') ?>">
                            
                            <div class="input-group-append">
                                <button type="submit" form="id_products_view_basket_form_confirm" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="left" title="<?= lang('products.prd_qty_tooltip')?>">
                                    <i class="fas fa-mail-bulk mr-1"></i><?= lang('products.btn_quote_ref_sub') ?>
                                </button>
                            </div>
                        </div>
                        <small class="text-danger" id="basket_ref_error"></small>
                    </div>
                    <?= form_close() ?>
                    <?php endif ?>
                    
                </div>
            </div>
            <!-- / Quote Bakset -->
            <?php endif ?>
    </div>
</div>
<?php if (array_key_exists('_filters', $record) && is_array($record['_filters']) && count($record['_filters']) > 0) :?>
<?= $currentView->includeView('Products/products_multiresults') ?>
<?php endif ?>
<script>
    $(function(){
        <?php if (array_key_exists('_filters', $record) && is_array($record['_filters']) && count($record['_filters']) > 0) :?>
        $('#id_products_view_filter_listmodal').modal('show');
        <?php endif ?>
        $('input[name="basket_price"]').mask('00000000.00', {reverse: true});
        
        $('input[name="basket_ref"]').on('change',function(){
            var val=$(this).val();
            addLoader('#basket_ref_field');
            ajaxCall('<?= api_url('sales','checkref') ?>',{'ref':val},
            function(data){
                if ('error' in data){
                    $('#basket_ref_error').text(data['error']);
                    $('input[name="basket_ref"]').addClass('border-danger');
                    killLoader();
                }else
                {
                    $('#basket_ref_error').text('');
                    $('input[name="basket_ref"]').removeClass('border-danger');
                    killLoader();
                }
            },
            function(data){
                console.log(data);
            });
        });
    });
    function showFilterResulDetails(part){
            $('input[name="filter_part"]').val(part);
            $('#id_products_view_filter_form').submit();
    }
    $('button[data-download]').on('click',function(){
        var url=$(this).attr('data-download');
        url=url.replace('-brand-',$('#id_down_brand').find('option:selected').val());
        window.location=url;
    }); 
</script>

