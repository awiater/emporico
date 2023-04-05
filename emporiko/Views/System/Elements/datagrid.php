<table class="table table-striped" id="<?= $id?>_table">
    <thead<?=!empty($_header) && strlen($_header) > 0 ? ' class="'.$_header.'"' : ''?>>
        <tr>
            <?php foreach($_columns as $_column) :?>
            <th<?= is_array($_column['args']) && array_key_exists('style', $_column['args'])? ' style="'.$_column['args']['style'].'"' : '' ?><?=$_column['name']=='_action' ? ' data-action="true"' : ''?>><?=lang($_column['title']) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach(!empty($value) && is_array($value) ? $value : [] as $key=>$row) :?>
        <tr id="<?= $id ?>_tablerow_<?= $key ?>">
            <?php $row=is_array($row) ? $row : [];foreach($_columns as $_column) :?>        
            <td<?= is_array($_column['args']) && array_key_exists('style', $_column['args'])? ' style="'.$_column['args']['style'].'"' : '' ?>>
                <?php if(array_key_exists($_column['name'], $row)) :?>
                    <?php if($_column['editable']) :?>
                        <?php if (array_key_exists('field', $_column['args'])) :?>
                            <?= $_column['args']['field']->setName($name.'['.$key.']['.$_column['name'].']')->setValue($row[$_column['name']])->render() ?>
                        <?php else :?>
                            <?= $row[$_column['name']] ?>
                        <?php endif ?>
                    <?php else :?>
                        <?php if (array_key_exists('money', $_column['args'])) :?>
                            <div class="d-flex">
                                <?= $_column['args']['money'].$row[$_column['name']] ?>
                            </div>
                        <?php elseif (array_key_exists('list', $_column['args']) && array_key_exists($row[$_column['name']], $_column['args']['list'])) :?>
                            <?= $_column['args']['list'][$row[$_column['name']]]?>
                        <?php else :?>
                            <?= $row[$_column['name']] ?>
                        <?php endif; ?>
                        <?php if (array_key_exists('_action', $_columns)) :?>
                        <input type="hidden" name="<?= $name ?>[<?=$key?>][<?=$_column['name']?>]" value="<?=$row[$_column['name']]?>">
                        <?php endif; ?>
                    <?php endif; ?>
                <?php elseif ($_column['name']=='_action') :?>
                <button type="button" class="btn btn-danger btn-sm" onclick="$('#<?= $id ?>_tablerow_<?= $key ?>').remove()">
                    <i class="far fa-trash-alt"></i>
                </button>
                <?php elseif (array_key_exists('total', $_column['args']) && is_array($_column['args']['total'])) :?>
                      <?php if ($_column['args']['total']['method']=='sum') :?>
                        <?php  $_totals[$key]=\EMPORIKO\Helpers\Arrays::SumValues(\EMPORIKO\Helpers\Arrays::getValuesOfKeys($row, $_column['args']['total']['columns']));echo $_totals[$key]; ?>
                      <?php elseif ($_column['args']['total']['method']=='x') :?>
                        <?php $_totals[$key]=\EMPORIKO\Helpers\Arrays::TimesValues($row, $_column['args']['total']['columns']);echo $_totals[$key]; ?>
                      <?php else :?>
                      0
                      <?php endif ?>
                <?php endif ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <?php if(!empty($_footer)) :?>
    <tfoot class="table-secondary">
        <?php if(!empty($_total_row) && is_array($_total_row) && count($_total_row) > 0 && !empty($value) && is_array($value)) :?>
        <tr>
            <?php foreach($_columns as $key=>$_column) :?>
                <?php if(in_array($key, $_total_row)) :?>
                <td<?= is_array($_column['args']) && array_key_exists('style', $_column['args'])? ' style="'.$_column['args']['style'].'"' : '' ?><?=$_column['name']=='_action' ? ' data-action="true"' : ''?>>
                    <?php if ($key=='_total') :?>
                        <?php if (!empty($_totals) && is_array($_totals) && count($_totals) > 0) :?>
                            <?= array_sum($_totals); ?>
                        <?php endif ?>
                    <?php else :?>
                    <?= array_sum(array_column($value, $key)); ?>
                    <?php endif ?>
                </td>
                <?php else :?>
                <td></td>
                <?php endif ?>
            <?php endforeach; ?>
        </tr>
        <?php endif ?>
    </tfoot>
    <?php endif ?>
</table>
<?php if(!empty($_pagination)) :?>
<div class="d-flex" id="<?= $id ?>_pagination">
    <?= $_pagination ?>
</div>
<?php endif ?>
<script>
<?php if(!empty($_pagination)) :?>
$(function(){
    $('#<?= $id ?>_pagination').find('nav').addClass('ml-auto');
});
<?php endif ?>
function <?= $id ?>_addnewrow(rowData,otherData=null){
    var columns=JSON.parse(atob('<?= base64_encode(json_encode($_columns))?>'));
    var rowid=$('#<?= $id?>_table').find('tbody tr').length;
    var html='<tr id="<?= $id ?>_tablerow_'+rowid+'">';
    jQuery.each(columns, function(index, item) {
        if ('args' in item && 'style' in item['args']){
            html+='<td style="'+item['args']['style']+'">';
        }else{
            html+='<td>';
        }
        if (index=='_action'){
            html+='<button type="button" class="btn btn-danger btn-sm" onclick="$('+"'#<?= $id ?>_tablerow_"+rowid+"'"+').remove()">';
            html+='<i class="far fa-trash-alt"></i>';
            html+='</button>';
            if (otherData!=null){
                jQuery.each(otherData, function(key, val){
                    html+='<input type="hidden" name="<?= $name ?>['+rowid+']['+key+']" value="'+val+'">';
                });
            }
            html+='</td>';
        }else if (index in rowData){
            var item_val=rowData[index];
            var item_txt=item_val;
            if ('args' in item && 'list' in item['args'] && item_val in item['args']['list']){
                item_txt=item['args']['list'][item_val];
            }
            if ('editable' in item && item['editable'] && 'args' in item && 'field' in item['args']){
                var field=item['args']['field'];
                field=field.replace('#rowid',rowid).replace('value="0"','value="'+item_val+'"');
                html+=field;
            }else{
                html+=item_txt
                html+='<input type="hidden" name="<?= $name ?>['+rowid+']['+index+']" value="'+item_val+'">';
            }
        }
        html+='</td>';
    });
    
    html+='<tr>';
    $('#<?= $id?>_table').find('tbody').append(html);
}
</script>