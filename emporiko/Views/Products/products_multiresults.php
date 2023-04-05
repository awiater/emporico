<div class="modal" tabindex="-1" role="dialog" id="id_products_view_filter_listmodal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?=lang('products.msg_filters_many_title')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?=lang('system.buttons.close')?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-1"><?=lang('products.msg_filters_many')?></p>
                <table class="table">
                    <thead>
                        <tr>
                            <th><?=lang('products.prd_brand') ?></th>
                            <th><?=lang('products.prd_apdpartnumber') ?></th>
                            <th><?=lang('products.prd_tecdocpart') ?></th>
                            <th><?=lang('products.prd_description') ?></th>
                            <th style="width:80px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($record['_filters'] as $key=>$part) :?>
                        <tr>
                            <td><?=$part['prd_brand'] ?></td>
                            <td><?=$part['prd_apdpartnumber'] ?></td>
                            <td><?=$part['prd_tecdocpart'] ?></td>
                            <td><?= $part['prd_description'] ?></td>
                            <td>
                                <?php if ($key==0) :?>
                                <button type="button" class="btn btn-sm btn-info" data-dismiss="modal">
                                <?php else :?>
                                <button type="button" class="btn btn-sm btn-info" onclick="showFilterResulDetails('<?=$part['prd_apdpartnumber']?>')">
                                <?php endif ?>    
                                    <i class="fas fa-info"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?=lang('system.buttons.close')?>
                </button>
            </div>
        </div>
    </div>   
</div>