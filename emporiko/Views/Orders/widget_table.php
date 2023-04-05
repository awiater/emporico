<?php if (empty($records) || (!empty($records) && (!is_array($records) || (is_array($records) && count($records) < 1)))) :?>
<h6 class="text-center"><?=!empty($error_norecords) ? lang($error_norecords) : '' ?></h6>
<?php else :?>
<div class="table-fixed">
<table class="table table-hover table-striped" id="accounts_emails_container">
    <tbody>
        <?php foreach($records as $record) :?>
        <tr style="cursor:pointer" data-url="<?= str_replace(['-id-','-mode-'], [$record['ordid'], str_replace([0,1,2], ['opportunities','quotes','orders'], $record['ord_type'])], $url_view) ?>">
            <td style="width:20px">
                <?php if(intval($record['ord_type'])==0) :?>
                <i class="fas fa-hand-holding-usd fa-2x"></i>
                <?php elseif(intval($record['ord_type'])==1) :?>
                <i class="fas fa-file-invoice-dollar fa-2x"></i>
                <?php elseif(intval($record['ord_type'])==2) :?>
                <i class="fas fa-file-invoice fa-2x"></i>
                <?php else :?>
                <i class="fas fa-money-check-alt fa-2x"></i>
                <?php endif ?>
            </td>
            <td>
                <div>
                    <?= $record['ord_ref'] ?>
                </div>
                <small>
                    <?php if(strlen($record['ord_desc']) > 0) :?>
                    <?= $record['ord_desc'] ?>&nbsp;|&nbsp;
                    <?php endif ?>
                    <?= $record['ord_refcus'] ?>
                </small>
            </td>
            <td style="width:120px;text-align: right">
                <?= $record[$field_value] ?>
            </td>
            <td style="width:120px">
                <?= convertDate($record['ord_addon'],'DB','d M Y') ?>
            </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
</div>
<?php endif; ?>