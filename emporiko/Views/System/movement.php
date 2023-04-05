<?php if (!empty($viewmode) && $viewmode=='table') :?>
<table class="table">
    <thead class="bg-secondary">
        <tr>
            <?php foreach ($_table_columns as $key=>$val) :?>
            <th><?= $val ?></th>
            <?php endforeach;?>
            <?php if (!empty($cfg_acc)) :?>
            <th style="width:50px;"><?php if (!empty($addlog) && array_key_exists('button', is_array($addlog) ? $addlog : [])) :?><?=$addlog['button']?><?php endif ?></th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($movements as $movement) :?>
        <tr>
            <?php foreach ($_table_columns as $key=>$val) :?>
            <?php if ($key=='mhdate') :?>
            <td><?= $movement[$key]!=null ? convertDate($movement[$key], null, !empty($date_format) ? $date_format :'d M Y H:i') : '' ?></td>
            <?php else :?>
            <td><?= lang($movement[$key]) ?></td>
            <?php endif ?>
            <?php endforeach;?>
            <?php if (!empty($cfg_acc)) :?>
            <td>
                <button type="button" class="btn btn-sm btn-danger" data-url="<?= str_replace('-id-', $movement['mhid'], $movements_del_url)?>">
                    <i class="far fa-trash-alt"></i>
                </button>
            </td>
            <?php endif ?>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
<?php else :?>
<div class="card">
    <div class="card-header p-2 d-flex">
        <h5 class="card-title font-weight-bold"><?= lang($title) ?></h5>
        <?php if (!empty($addlog) && is_array($addlog) && array_key_exists('button', $addlog)) : ?>
        <div class="ml-auto">
            <?= $addlog['button'] ?>
        </div>
        <?php endif; ?>
    </div>
    <?php if(count($movements) < 1) :?>
    <div class="card-body">
        <h6><?= lang($no_data_msg) ?></h6>
    </div>
    <?php else :?>
    <ul class="list-group list-group-flush" style="max-height:450px;overflow-y: scroll">
        <?php foreach($movements as $movement) :?>
        <li class="list-group-item p-1 mb-1">
            <div class="d-flex">
            <div<?= !$cfg_acc ? ' class="w-100"':''?>>
                <div class="w-100"><?=str_replace('-curl-',current_url(FALSE,TRUE),lang($movement['mhtype_name'].'_view',$movement));?></div>
                <small class="mr-auto">
                    <?= convertDate($movement['mhdate'],null,!empty($date_format) ? $date_format :'d M Y H:i')?>,&nbsp;
                    <?= $movement['mhuser']?>
                </small>
            </div>
            <?php if ($cfg_acc) :?>
            <div class="ml-auto mr-1">
                <button class="btn btn-xs ml-auto btn-danger" type="button" data-url="<?= str_replace('-id-', $movement['mhid'], $movements_del_url) ?>">
                    <i class="far fa-trash-alt"></i>
                </button>
            </div>
            <?php endif ?>
            </div>    
        </li>
        <?php endforeach ?>
    </ul>
    <?php endif ?>
</div>
<?php endif ?>
<?php if (!empty($addlog) && is_array($addlog) && array_key_exists('ref', $addlog)) : ?>
<div class="modal" tabindex="-1" role="dialog" id="movements_addlog_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang($addlog['title']) ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open($addlog['action'], ['id'=>'movements_addlog_modal_form'],['ref'=>$addlog['ref'],'msgs'=>$addlog['msgs']]) ?>
                <div class="form-group">
                    <label for="field_info" class="mr-2">
                        <?= lang($addlog['field_info']) ?>
                    </label>
                    <?= \EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor::create()->setArgs(['id'=>'field_info','name'=>'info','value'=>''])->render()?>
                </div>
                <?= form_close() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm mr-auto" onclick="$('#movements_addlog_modal').modal('hide');">
                    <i class="fas fa-ban mr-1"></i><?=lang('system.buttons.cancel') ?>
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="addLoader('#movements_addlog_modal');$('#movements_addlog_modal_form').submit();">
                    <i class="far fa-save mr-1"></i><?=lang('system.buttons.submit') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
