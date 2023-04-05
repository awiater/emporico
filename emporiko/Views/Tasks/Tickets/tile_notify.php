<table class="table table-sm table-striped" id="<?= $name ?>">
    <tbody>
        <?php foreach($data as $row) :?>
            <tr>
                <td>
                    <p>
                        <i class="far fa-clock mr-1"></i>
                        <?= $row['mhdate']!=null ? convertDate($row['mhdate'], null, 'd M Y') : '' ?>
                    </p>
                    <button class="btn btn-link p-0 text-dark" onclick="addLoader('#tile_<?= $name ?>');window.location='<?=url('Settings','notification',[$row['mhid']],['refurl'=>current_url(FALSE,TRUE)])?>'">
                     <?= lang($row['mhinfo']) ?>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table> 