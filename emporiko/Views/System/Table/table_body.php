<?= form_open('',['id'=>$table_view_datatable_id.'_form'],['model'=>empty($_tableview_model) ? '' : $_tableview_model]) ?>

<?php if (empty($_tableview_data) || (!empty($_tableview_data) && is_array($_tableview_data) && count($_tableview_data) < 1)) :?>
<?= $currentView->getErrorBar(!empty($no_data_message) ? $no_data_message : '','info') ?>
<?php else :?>
<table class="<?= $_table_class ?>" id="<?= $table_view_datatable_id?>">
    <thead<?=!empty($table_head_class) ? ' class="'.$table_head_class.'"' : ''?>>
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
        <tr<?= array_key_exists('enabled',$row) &&  intval($row['enabled'])==0 ? ' class="error"' : ''?>>
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
            <td id="<?=$table_view_datatable_id?>_editcolumn" style="width:130px" class="p-0 pl-1 pt-2">
                <ul class="nav">
                    <?php foreach($_edit_column as $button) :?>
                    <li class="nav-item mr-1 mb-1">
                        <?php  $button=str_replace(['-'.$_record_key.'-','-id-'],$row[$_record_key],$button) ?>
                        <?= str_replace(\EMPORIKO\Helpers\Arrays::ParsePatern(array_keys($row), '-value-'),\EMPORIKO\Helpers\Arrays::ParsePatern($row,'value'),$button) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </td>
            <?php endif ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif ?>
</form>
<?php if(!empty($_tableview_pagination)) :?>
<div class="d-flex" id="<?= $table_view_datatable_id?>_pagination">
    <?= str_replace('<nav>', '<nav class="ml-auto">', $_tableview_pagination) ?>
</div>
<?php endif ?>
<?php if(!empty($_uploadform) && is_array($_uploadform) && EMPORIKO\Helpers\Arrays::KeysExists(['button_id','driver'], $_uploadform)) :?>
<?= form_dataupload($_uploadform['driver'], null, $_uploadform) ?>
<?php endif ?>
