<?= $this->extend('System/Table/table_index') ?>

<?= $this->section('table_toolbar') ?>
    <?= $currentView->IncludeView('System/Table/table_toolbar',['_table_filters'=>$currentView->IncludeView('System/Table/table_filters')]); ?>
<?= $this->endSection() ?>

<?= $this->section('table_body') ?>
<?= form_open('',['id'=>$table_view_datatable_id.'_form'],['model'=>empty($_tableview_model) ? '' : $_tableview_model]) ?>
<table class="<?= $_table_class ?>" id="<?= $table_view_datatable_id?>">
    <thead>
        <tr>
        <?php if($_multiedit_column && !$currentView->isMobile()) :?>
            <td style="width:35px;">
                <input type="checkbox" value="" id="<?= $table_view_datatable_id?>_sel_all" onclick="$('input[name*=\'<?= $_record_key?>\']').prop('checked', this.checked);">
            </td>
        <?php endif ?>   
        <?php foreach($_data_cols as $key=>$value) :?>
            <td>
                <?php if (is_array($value) && array_key_exists('label', $value)) :?>
                    <b><?= $value['label'] ?></b>
                    <?php if($_data_sorting) :?>
                    <a href="<?= url($_tableview_filters_url,null,[],['orderby'=>$key]) ?>" class="ml-1">
                        <i class="fas fa-caret-up"></i>
                    </a>
                    <a href="<?= url($_tableview_filters_url,null,[],['orderby'=>$key.' DESC']) ?>" class="ml-1">
                        <i class="fas fa-caret-down"></i>
                    </a>
                    <?php endif?>    
                <?php else :?>
                    <b><?= $value?></b>
                <?php endif ?>
            </td>
        <?php endforeach; ?>
            <?php if (!empty($_edit_column) && is_array($_edit_column) && count($_edit_column)>0) :?>
            <td></td>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($_tableview_data as $row) :?>
        <tr>
            <?php if($_multiedit_column && !$currentView->isMobile()) :?>
            <td style="width:35px;">
                <input type="checkbox" name="<?=$_record_key?>[]" value="<?=$row[$_record_key]?>">
            </td>
            <?php endif ?>
            <?php foreach ($_data_cols as $key=>$value) :?>
            <td>
                <?php if (array_key_exists($key, $row)) :?>
                    <?= $currentView->parseValue($row[$key],$value,$row); ?>
                <?php endif ?>
            </td>
            <?php endforeach; ?>
            <?php if (!empty($_edit_column) && is_array($_edit_column) && count($_edit_column)>0) :?>
            <td>
                <button type="button" class="mr-1 btn btn-primary btn-sm" data-toggle="tooltip" data-placement="left" title="<?= lang('system.buttons.edit_details') ?>" data-url="<?=!empty($edit_url) ? str_replace('-id-',$row['ecid'],$edit_url) : ''?>">
                    <i class="fa fa-edit"></i>
                </button>
                <?php if ($row['ec_status']=='plan') :?>
                <button type="button" class="mr-1 btn btn-success btn-sm" data-toggle="tooltip" data-placement="left" title="<?= lang('emails.btn_campaignstart') ?>" data-url="<?=!empty($start_url) ? str_replace('-id-',$row['ecid'],$start_url) : ''?>">
                    <i class="far fa-play-circle"></i>
                </button>
                <?php endif ?>
                <?php if ($row['ec_status']=='live') :?>
                <button type="button" name="edit_1" class="mr-1 btn btn-danger btn-sm"  data-toggle="tooltip" data-placement="left" title="<?= lang('emails.btn_campaignstop') ?>" data-url="<?=!empty($stop_url) ? str_replace('-id-',$row['ecid'],$stop_url) : ''?>">
                    <i class="fas fa-stop-circle"></i>
                </button>
                <?php endif ?>
            </td>
            <?php endif ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</form>
<?php if(!empty($_tableview_pagination)) :?>
<div class="d-flex" id="<?= $table_view_datatable_id?>_pagination">
    <?= str_replace('<nav>', '<nav class="ml-auto">', $_tableview_pagination) ?>
</div>
<?php endif ?>
<?php if(!empty($_uploadform) && is_array($_uploadform) && EMPORIKO\Helpers\Arrays::KeysExists(['button_id','driver'], $_uploadform)) :?>
<?= form_dataupload($_uploadform['driver'], null, $_uploadform) ?>
<?php endif ?>

<?= $this->endSection() ?>

<?= $this->section('table_script') ?>
    <?= $currentView->IncludeView('System/Table/table_script'); ?>
<?= $this->endSection() ?>