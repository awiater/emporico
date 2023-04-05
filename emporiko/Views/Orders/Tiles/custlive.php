<table class="table table-sm table-striped">
    <thead>
        <tr>
            <?php if(intval(loged_user('iscustomer'))==0) :?>
            <th><?= lang('orders.ord_cusacc') ?></th>
            <th><?= lang('orders.ord_ref') ?></th>
            <?php else :?>
            <th><?= lang('orders.ord_refcus') ?></th>
            <th><?= lang('orders.ord_addon') ?></th>
            <?php endif;?>
            
            <th><?= lang('orders.ord_status') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $row) :?>
        <tr>
            <?php if(intval(loged_user('iscustomer'))==0) :?>
            <td>
                <a href="<?= url('Customers','accounts',[$row['ord_cusacc'],'view'],['refurl'=>current_url(FALSE,TRUE)]) ?>" class="btn btn-link p-0 text-dark">
                    <?= $row['ord_cusacc'] ?>
                </a>
            </td>
            <td>
                <a href="<?= url('Orders','customers',[$row['ordid']],['refurl'=>current_url(FALSE,TRUE)]) ?>" class="btn btn-link p-0 text-dark">
                    <?= $row['ord_ref'] ?>
                </a>
            </td>
            <?php else :?>
            <td>
                <a href="<?= url('Orders','customers',[$row['ordid']],['refurl'=>current_url(FALSE,TRUE)]) ?>" class="btn btn-link p-0 text-dark">
                    <?= $row['ord_refcus'] ?>
                </a>
            </td>
            <td><?= convertDate($row['ord_addon'], null, 'd M Y') ?></td>
            <?php endif;?>
            
            <td><?= array_key_exists($row['ord_status'], $status) ? $status[$row['ord_status']] : '' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>