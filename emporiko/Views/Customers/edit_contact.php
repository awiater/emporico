<?= $currentView->includeView('System/form') ?>
<?= form_dropdown(' ', $customers, [], ['id'=>'id_ct_account_input_list']) ?>
<ul class="list-group" id="id_ct_account_list_values">
  <?php foreach(is_array($record['ct_account']) ? $record['ct_account'] : [] as $key=>$item) :?>
  <li class="list-group-item d-flex" id="id_ct_account_list_values_<?= $key?>">
    <?= array_key_exists($item, $customers) ? $customers[$item] : $item ?> 
      <button type="button" onclick="remove_account('<?= $key?>')" class="btn btn-sm btn-danger ml-auto">
          <i class="far fa-trash-alt"></i>
      </button>
    <input type="hidden" value="<?= $item ?>" name="ct_account[]">
  </li>
  <?php endforeach ?>
</ul>
<script>
    $(function(){
        $('#id_ct_account_input').after($('#id_ct_account_input_list'));
        $('#id_ct_account_input').remove();
        $('#id_ct_account_input_list').select2({theme: 'bootstrap4'});
        $('#id_ct_account_input_list').parent().find('.btn-primary').attr('onclick','add_new_account()');
        $('#id_ct_account_input_list').parent().after($('#id_ct_account_list_values').detach());
        $('#id_ct_account_list').parent().remove();
    });
    
    function add_new_account(){
       var val=$('#id_ct_account_input_list').find(':selected');
       var id=$('#id_ct_account_list_values').html().length;
       var html='<li class="list-group-item d-flex" id="id_ct_account_list_values_'+id+'">';
       html+=val.text();
       html+='<button type="button" onclick="remove_account(';
       html+="'"+id+"'"+')" class="btn btn-sm btn-danger ml-auto"><i class="far fa-trash-alt"></i>';
       html+='<input type="hidden" value="'+val.val()+'" name="ct_account[]"></button></li>';
       $('#id_ct_account_list_values').append(html);
    }
    
    function remove_account(id){
        $('#id_ct_account_list_values_'+id).remove();
    }
    
</script>