<div class="card" id="tile_<?= $name ?>" style="max-height:500px;">
    <div class="card-header d-flex"<?= !empty($header_style) ? ' style="'.$header_style.'"' : '' ?>>
        <h3 class="card-title font-weight-bold">
            <?= lang($header) ?>
        </h3>
        <span class="ml-auto">
            <?php if (!empty($tilePrintButton) && $tilePrintButton!=FALSE) :?>
            <button type="button" class="btn btn-sm btn-secondary float-right" onclick="printTile('<?= is_bool($tilePrintButton) ? '#tile_'.$name.' .tab-content': $tilePrintButton ?>','<?= lang($header) ?>')" data-toggle="tooltip" data-placement="top" title="<?= lang('system.buttons.print') ?>">
                <i class="fa fa-fas fa-print"></i>
            </button>
            <?php endif ?>
        </span>
    </div>
    <div class="card-body">
        <div class="tab-content p-0"<?= !empty($ScrollBody) ? ' style="max-height:420px;overflow-y: scroll;"' : ''?>>
            <?= !empty($data) && is_string($data) ? $data: ''?>
        </div>
    </div>
</div>
