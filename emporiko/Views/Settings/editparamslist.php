<?= $currentView->includeView('System/form') ?>

<button type="button" class="mr-1 btn btn-primary btn-sm" id="id_btn_new">
	<i class="fa fa-plus mr-1"></i><?= lang('system.buttons.new') ?>
</button>

<script>
	$(function(){
		$("input[data-del]").each(function(){
			addRefButton($(this).attr('id'),"deleteItem('"+$(this).attr('data-del')+"','"+$(this).attr('id')+"')",'far fa-trash-alt','','danger');
		});
		
		$('.form-group').addClass("row");
		$('label').addClass("col-3");
		$('.card-footer').prepend($('#id_btn_new'));
	});
	
	$('#id_btn_new').on("click",function(){
		var id='new_item_'+$('#'+$('#id_formview_submit').attr('form')).length;
		var html='<div class="form-group row" id="'+id+'_field">';
    	html+='<div class="col-3"><input type="text"  value="" class="form-control new_field_value w-75" id="'+id+'" required></div>';
   	 	html+='<div class="input-group mb-3  w-25">';
   	 	html+='<input type="text" name="settings[new_'+id+'][value]" value="" class="form-control  w-25" required>';
   	 	html+='<input type="hidden" name="settings[new_'+id+'][paramsgroups]" value="<?= $paramsgroups?>">';
   	 	html+='<input type="hidden" name="settings[new_'+id+'][param]" id="'+id+'_param">';
   	 	html+='<div class="input-group-append">';
   	 	html+='<button type="button" class="btn btn-danger btn-sm" onclick="';
   	 	html+="$('#"+id+"_field').remove();";
   	 	html+='" id="bolt_btn_id_settings_prod_origin_uk">';
   	 	html+='<i class="far fa-trash-alt"></i></button></div></div><div>';
   	 	$('#'+$('#id_formview_submit').attr('form')).append(html);
	});
	
	$(document).on('change','.new_field_value',function(){
		var val=$(this).val();
		val=val.replace(/\ /g, '_');
		$('#'+$(this).attr('id')+'_param').val('<?= $param ?>_'+val);
	});
	
	function deleteItem(value,id){
		$('#'+$('#id_formview_submit').attr('form')).append('<input type="hidden" name="settings[delete][]" value="'+value+'">');
		$("#"+id+"_field").remove();
	}
</script>