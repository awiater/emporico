<?php if (!array_key_exists('readonly', $args)) :?>
<div class="input-group mb-1">
    <?= $input_field->render() ?>
    <div class="input-group-append">
        <button type="button" class="btn btn-primary btn-sm" onClick="<?= $addnewfunc!=null ? $addnewfunc : $args['id'].'_listadd()' ?>">
            <i class="fas fa-plus"></i>
        </button>
    </div>
</div>
<?php endif; ?>
<ul class="list-group" id="<?= $args['id'].'_list' ?>" style="max-height:<?= $args['list_height'] ?>;overflow-y: scroll;">
    <?php foreach(is_array($items) ? $items : [] as $key=>$item) :?>
    <li class="list-group-item d-flex" id="<?= $args['id'].'_listitem_'.$key ?>">
        
        <?php if (array_key_exists('input_options', $args) && is_array($args['input_options'])) :?>
            <?php if (array_key_exists($item, $args['input_options'])) :?>
                <?= str_replace('=>', '<br>', $args['input_options'][$item]) ?>
            <?php elseif (in_array($item, $args['input_options'])) :?>
                <?= $item ?>
            <?php else :?>
                <?= $item ?>
            <?php endif ?>
        <?php else :?>
            <?= $item ?>
        <?php endif ?>
        <?= form_hidden($name.'['.(is_numeric($key) ? '': $key).']',$item==null ? '' : $item); ?>
        <?php if (array_key_exists('item_delete', $args) && $args['item_delete']) :?>
        <button type="button" class="btn btn-danger btn-sm ml-auto" onClick="$('#<?= $args['id'].'_listitem_'.$key ?>').remove()">
            <i class="fas fa-trash-alt"></i>
        </button>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>
<script>
    <?php if ($addnewfunc==null) :?>
    function <?=$args['id']?>_listadd(item=null){
        if (item==null && $("#<?= $args['id']?>_input").is('input')){
            var val=$("#<?= $args['id']?>_input").val();
            var txt=val;
            $("#<?= $args['id']?>_input").val('');
        }else if (item==null){
            var val=$("#<?= $args['id']?>_input").find(':selected').val();
            var txt=$("#<?= $args['id']?>_input").find(':selected').text();
            if (val=='*'){
                $("#<?= $args['id']?>_input").find('option').each(function(){
                    if ($(this).val()!='*'){
                        item={'txt':$(this).text(),'val':$(this).val()};
                        <?=$args['id']?>_listadd(item);
                    }
                });
                return false;
            }
        }else
        {
            var val=item['val'];
            var txt=item['txt'];
        }
        <?php if (array_key_exists('input', $args) && is_array($args['input']) && $args['input']['type']=='email') :?>
         if (!isEmail(val)){
            Dialog('<?= lang('system.errors.invalid_emailaddr')?>','danger');
         }else{    
        <?php endif ?>
        if (txt.indexOf('=>') >= 0){
            txt=txt.split('=>');
            txt=txt[0];
        }
        if ($('#<?= $args['id'].'_list' ?>').find('input[name="<?= $name?>['+txt+']"]').length > 0){
            Dialog('<?= $args['_list_item_exists_error']?>','warning');
        }else{
            var id=$("#<?= $args['id'].'_list' ?>").html().length;
            var html='<li class="list-group-item d-flex" id="<?=$args['id']?>_listitem_'+id+'">';
            html+=txt;
            html+='<button type="button" class="btn btn-danger btn-sm ml-auto" onclick="';
            html+="$('#<?= $args['id'].'_listitem_'?>"+id+"').remove()";
            html+='"><i class="fas fa-trash-alt"></i>';
            html+='</button><input type="hidden" name="<?= $name?>['+txt+']" value="'+val+'"></li>';
            $("#<?= $args['id'].'_list' ?>").append(html);
        }
        <?php if (array_key_exists('input', $args) && is_array($args['input']) && $args['input']['type']=='email') :?>
        }
        <?php endif ?>
    }
    <?php endif; ?>
</script>
