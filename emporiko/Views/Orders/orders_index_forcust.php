<div class="card">
    <div class="card-header p-2">
        <h5 class="card-title font-weight-bold"><?= lang('orders.customer_view') ?></h5>
        <div class="float-right">
            <button type="button" class="btn btn-default btn-xs mr-2" data-card-widget="collapse" data-toggle="tooltip" data-placement="top" title="<?= lang('system.buttons.collapse_tooltip') ?>">
                <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-xs btn-default m-0" data-toggle="tooltip" data-placement="top" title="<?= lang('orders.customer_view_openbtn') ?>" data-url="<?=$url_show_orders?>">
                <i class="fas fa-external-link-alt"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-2">
        <?php if (count($records) < 1) :?>
        <h5 class="text-center w-100">
            <?= lang('orders.customer_view_noorders') ?>
        </h5>
        <?php else :?>
        <table class="table table-sm table-hover table-striped" id="id_recent_orders_form">
            <thead class="bg-dark">
                <tr><!-- 'ord_ref','ord_refcus','ord_addon' -->
                    <th><?= lang('orders.ord_ref')?></th>
                    <th><?= lang('orders.ord_addon')?></th>
                    <th><?= lang('orders.ord_status')?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($records as $record) :?>
                <tr>
                    <td style="width:40%"><?= $record['ord_ref'] ?></td>
                    <td style="width:18%"><?= strlen($record['ord_addon']) ? convertDate($record['ord_addon'], null, 'd M Y') : '' ?></td>
                    <td style="width:30%"><?= array_key_exists($record['ord_status'], $statuses) ? $statuses[$record['ord_status']] : $record['ord_status']?></td>
                    <td style="max-width:12%">
                        <ul class="nav">
                        <?php if ($edit_acc) :?>
                        <li class="nav-item">
                            <button type="button" class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="left" title="<?= lang('system.buttons.edit_details')?>" data-url="<?= str_replace('-id-',$record['ordid'],$url_edit) ?>">
                                <i class="fa fa-edit"></i>
                            </button>
                        </li>
                        
                        <!-- Order Send in Email Button -->
                        <li class="nav-item">
                            <button type="button" class="btn btn-dark btn-xs ml-1" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.btn_ordersendinemail')?>" data-sendorder="<?= $record['ordid']?>">
                                <i class="fas fa-envelope-open-text"></i>
                            </button>
                        <!-- /Order Send in Email Button -->
                        </li>
                            <?php if ($record['ord_status']=='placed') :?>
                            <!-- Order Confirm Button -->
                            <li class="nav-item">
                                <?php if ($record['lines_qty'] > 0) :?>
                                <button type="button" class="ml-1 btn btn-warning btn-xs" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.btn_orderconfirm')?>" data-conforder="<?= $record['ordid']?>">
                                    <i class="fas fa-share-square"></i>
                                </button>
                                <?php else :?>
                                <button type="button" class="ml-1 btn btn-warning btn-xs" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.btn_ordervalidate')?>" data-url="<?= str_replace('-id-',$record['ordid'],$url_check) ?>" data-noloader="true">
                                    <i class="fas fa-check-square"></i>
                                </button>
                                <?php endif ?>
                            <!-- /Order Confirm Button -->
                            </li>
                            <?php endif ?>
                        <?php else :?>
                        <li class="nav-item">    
                            <button type="button" class="btn btn-xs btn-primary" data-toggle="tooltip" data-placement="left" title="<?= lang('system.buttons.edit_details')?>" data-url="<?= str_replace('-id-',$record['ordid'],$url_edit) ?>">
                                <i class="fa fa-edit"></i>
                            </button>
                        </li>
                        <?php endif ?>
                        <li class="nav-item">
                            <button type="button" class="mr-1 btn btn-success btn-xs ml-1" data-toggle="tooltip" data-placement="left" title="<?= lang('orders.'.(intval($record['ord_isquote']) ==0 ? 'btn_orderdownxlsx' : 'btn_quotedownxlsx'))?>" data-url="<?= str_replace('-id-',$record['ordid'],$url_downloadxlsx) ?>" data-noloader="true">
                                <i class="fas fa-file-excel"></i>
                            </button>
                        </li>  
                        </ul>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif ?>
    </div>
</div>
<?php if ($edit_acc) :?>
<!-- Order Confirm Modal -->
<?= $currentView->includeView('Orders/order_confirm') ?>
<!-- /Order Confirm Modal -->
<?php endif; ?>
