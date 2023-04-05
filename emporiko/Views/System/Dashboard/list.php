<div class="card" id="chart<?= $name ?>Tile" style="max-height:500px;">
    <div class="card-header d-flex"<?= !empty($header_style) ? ' style="'.$header_style.'"' : '' ?>>
        <h3 class="card-title">
            <i class="fas fa-chart-pie mr-1"></i>
            <?= lang($header) ?>
        </h3>
        <span class="ml-auto">
            <?php if (!empty($tilePrintButton) && $tilePrintButton) :?>
            <button type="button" class="btn btn-sm btn-secondary float-right" onclick="printTile('#chart<?= $name ?>','<?= lang($header_chart) ?>')" data-toggle="tooltip" data-placement="top" title="<?= lang('system.buttons.print') ?>">
                <i class="fa fa-fas fa-print"></i>
            </button>
            <?php endif ?>
        </span>
    </div>
    <div class="card-body">
        <div class="tab-content p-0" style="max-height:420px;overflow-y: scroll;">
            <table class="table table-striped table-sm">
                <tbody>
                    <?php foreach(!empty($data) && is_array($data) ? $data : [] as $key=>$value) :?>
                    <tr>
                        <td>
                            <?= lang($key) ?>
                        </td>
                        <td style="text-align: right;padding-right: 5px">
                            <?php if (!empty($badge)) :?>
                            <span class="badge bg-<?=$badge?> mt-1">
                            <?php endif ?>
                            <?= lang($value) ?>
                            <?php if (!empty($badge)) :?>
                            </span>
                            <?php endif ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
