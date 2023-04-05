<div class="input-group"<?= !empty($args['id']) ? ' id="'.$args['id'].'_group"' : ''?>>
    <?php if (!array_key_exists('readonly', $args) || (array_key_exists('readonly', $args) && !$args['readonly'])) :?>
    <?= $input_field->render() ?>
    <div class="input-group-append">
        <button type="button" class="btn btn-primary btn-sm" onClick="<?= $args['btnonclick'] ?>" data-toggle="tooltip" data-placement="left" title="<?= lang('system.buttons.add_tolist') ?>">
            <i class="fas fa-plus"></i>
        </button>
    </div>
    <?php endif ?>
    <div class="border m-0 mt-1 rounded col-12 p-0 row<?= count($items) < 1 ? ' d-none' : ''?>">
        <div class="col-11 p-1 row ml-0 mr-0" id="<?= $args['id'].'_list'?>">
        <?php $id=0 ?>
        <?php foreach($items as $key=>$item) :?>  
            <div id="<?= $args['id'].'_listitem_'.$id ?>" class="ml-1">
                <?php if (!array_key_exists('readonly', $args) || (array_key_exists('readonly', $args) && !$args['readonly'])) :?>
                <div class="badge badge-<?= $args['data-badge'] ?> p-0 pl-1 pr-0"<?= $args['data-badgetip'] ? ' data-toggle="tooltip" data-placement="bottom" title="'.$item.'"':''?>>
                <?php else :?>
                <div class="badge badge-<?= $args['data-badge'] ?> p-1 pr-0"<?= $args['data-badgetip'] ? ' data-toggle="tooltip" data-placement="bottom" title="'.$item.'"':''?>>    
                <?php endif ?>
                <?php if ($input_field->isTypeOf('DropDownField') && array_key_exists(is_numeric($key) ? $item : $key , $input_field->getArgs('options'))) :?>
                <?= $input_field->getArgs('options')[is_numeric($key) ? $item : $key] ?> 
                <?php else :?> 
                    <?= is_numeric($key) ? $item : $key ?>
                <?php endif ?>
                <?php if (!array_key_exists('readonly', $args) || (array_key_exists('readonly', $args) && !$args['readonly'])) :?>
                    <button type="button" class="btn btn-danger m-0 p-0 pr-1 pl-1 text-light ml-1" onClick="<?= $args['id']?>_listitemremove('<?= $id ?>')">
                        <i class="far fa-trash-alt fa-xs"></i>
                    </button>
                    
                    <input type="hidden" name="<?=$name?>[<?= $key ?>]" value="<?= $item ?>">
                <?php endif ?>
                </div>
            </div>
            <?php $id++ ?> 
        <?php endforeach; ?>
        </div>
        <div class="col-1 p-0">
            <button type="button" class="btn btn-danger btn-sm float-right full-height rounded-0" onclick="<?= $args['id']?>_listitemremove('*');" data-toggle="tooltip" data-placement="left" title="<?= lang('system.buttons.clear_list') ?>">
                <i class="far fa-trash-alt"></i>
            </button>
        </div>
    </div>
</div>

<script>
    function <?= $args['id']?>_listitemremove(id){
        if (id=='*'){
            ConfirmDialog('<?= lang('system.general.msg_clear_list')?>',function(){
                $('#<?= $args['id'].'_list'?>').html('').parent().addClass('d-none');
            });  
        }else{
            ConfirmDialog('<?= lang('system.general.msg_delete_ques')?>',function(){
                $('#<?= $args['id']?>_listitem_'+id).remove(); 
            });
        }
    }
    
    function <?= $args['id']?>_listadd(val=null,txt=null){
        
      if (val==null){
       <?php if ($input_field->isTypeOf('DropDownField')) :?>
          val=$("#<?= $args['id']?>_input").find(':selected').val();
          if (val==undefined || (val!=undefined  && val.length <1)){
              val=$("#id_<?= $args['id']?>_input").find(':selected').val();;
          } 
          var txt=$("#<?= $args['id']?>_input").find(':selected').text();
          if (txt==undefined || (txt!=undefined  && txt.length <1)){
              txt=$("#id_<?= $args['id']?>_input").find(':selected').text();;
          } 
          
          if (val==undefined || (val!=undefined  && val.length <1)){
              val=null;
          }else
          if (val=='[*]' || val=='*'){
              val=[];
              $('#<?=$args['id']?>_input').find('option').each(function(){
                 if ($(this).val()!='*'){ 
                     val.push({'val':$(this).val(),'txt':$(this).text()}); 
                }
              });
          }else{
            val=[{'val':val,'txt':txt}];
          }
       <?php else :?>     
        val=$("#<?= $args['id']?>_input").val();
        
        <?php if ($input_field->isTypeOf('EmailField')) :?>
            if (!isEmail(val)){
                Dialog('<?= lang('system.errors.invalid_emailaddr')?>','danger');
                val=null;
            }else{
                val=[{'val':val,'txt':val}];
            }
        <?php else :?>
        
        if (val.length>0){
            val=[{'val':val,'txt':val}];
        }
        <?php endif ?>
       <?php endif ?>
       }
       if (txt!=null && val !=null){
           val=[{'val':val,'txt':txt}];
       }
       if (Array.isArray(val)){
       $("#<?= $args['id']?>_input").val('');
       jQuery.each(val, function(key,item) {
            if ($('input[name="<?=$name?>['+item['txt']+']"]').length < 1){
            var id=$("#<?= $args['id'].'_list' ?>").html().length;
            var color='<?= $args['data-badge'] ?>';
            if ('color' in item){
                color=item['color'];
            }
            if ($.isArray(item['val'])){
                item['val']=item['val'][0]['val'];
            }
            var html='<div id="<?= $args['id']?>_listitem_'+id+''+key+'" class="ml-1"><div class="badge badge-'+color+' p-0 pl-1 pr-0">';
            html+=item['txt'];
            html+='<button type="button" class="btn btn-danger m-0 p-0 pr-1 pl-1 text-light ml-1" onClick="<?= $args['id']?>_listitemremove('+id+''+key+')">';
            html+='<i class="far fa-trash-alt fa-xs"></i></button>';
            html+='<input type="hidden" name="<?=$name?>['+item['txt']+']" value="'+item['val']+'">';
            html+='</div></div>';
            $("#<?= $args['id'].'_list' ?>").append(html);
            }else{
                Dialog('<?= lang('system.general.msg_item_exists') ?>'.replace('{0}',item['txt']),'warning');
            }
       });
        $("#<?= $args['id'].'_list' ?>").removeClass('d-none').parent().removeClass('d-none');
        }
    }
</script>