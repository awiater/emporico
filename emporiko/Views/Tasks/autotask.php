<?= $this->extend('System/form') ?>
<?= $this->section('form_body') ?>
<div id="id_error"></div>
<?php if (!is_array($task)) :?>
	<?= $notasks ?>
<?php else : ?>
	<div id="id_step_1">
	<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields, 0,3)]); ?>
	</div>
	<div id="id_step_2" class="d-none">
	<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields, 3,10)]); ?>
	</div>
<?php endif ?>

<script>
	$(function(){
		$("#id_formview_submit").addClass('d-none').html('<i class="fas fa-check-circle mr-1"></i><?=lang('system.buttons.confirm')?>').attr('type','button');
		
		$("#id_formview_submit").on("click",function(){
			alert($(this).attr('form'));
			$("#"+$(this).attr('form')).submit();
		});
	});
	$("#id_old_location").on("change",function(){
		$("#id_error").html('');
		$(this).addClass("form-control-lg");
		if($(this).val()===$("#id_old_location_text").val()){
			$("#id_reference").prop("readonly", false);
			$(this).removeClass("form-control-lg");
			$("#id_reference").addClass("form-control-lg");
			$("#id_reference").focus();
			$(this).prop("readonly", true);
		}else{
			$("#id_error").html(atob('<?= base64_encode($locerror) ?>'));
			$(this).addClass('border-danger');
		}
	});
	$("#id_reference").on("change",function(){
		$("#id_error").html('');
		$("#id_formview_submit").addClass('d-none').attr('type','button');
		
		if(btoa($(this).val())===$(this).attr('data-valid')){
			if (!checkForSubmit('check')){
				$("#id_step_2").removeClass('d-none');
				$("#id_step_1").addClass('d-none');
			}
		}else{
			$("#id_error").html(atob('<?= base64_encode($palerror) ?>'));
			$(this).addClass('border-danger');
		}
	});
	
	$("#id_location").on("change",function(){
		$("#id_error").html('');
		if($(this).val()===$("#id_location_text").val()){
			$(this).prop("readonly", true);
			if (!checkForSubmit('move')){
				
			}
		}else{
			$("#id_error").html(atob('<?= base64_encode($locerror) ?>'));
			$(this).addClass('border-danger');
		}
	});
	
	//0000236
	function checkForSubmit(mode){
		if (mode==='<?= $task['type']?>'){
			$("#id_formview_submit").removeClass('d-none');
			return true;
		}
		return false;
	}
</script>
<?= $this->endSection() ?>