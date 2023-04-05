<table style="display: table;width:250px;">
    <thead>
        <tr>
        <th style="text-align: left!important;"><?= lang('products.prd_tecdocpart')?></th>
        <th style="text-align: left!important;"><?= lang('products.prd_tecdocpart_qty') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach(!empty($parts) && is_array($parts) ? $parts : [] as $part) :?>
        <tr>
            <td style="text-align: left!important;"><?= array_key_exists('part',$part) ? $part['part'] : '' ?></td>
            <td style="text-align: left!important;"><?= array_key_exists('qty',$part) ? $part['qty'] : '' ?></td>
        </tr>
        <?php endforeach; ?>        
    </tbody>
</table>