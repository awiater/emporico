<?= $currentView->includeView('System/form') ?>
<script>
$(function(){
	$('#id_rtype').trigger("change");
});
	$("#id_rtype").on("change",function(){
		var val=$("#id_rtype option:selected").val();
		if (val=='0' || val==0){
			$("#id_rsql").val(atob('<?= strlen($record['rsql']) >1 ? base64_encode($record['rsql']) : $intertpl ?>'))
		}else if (val=='2' || val==2){
			$("#id_rsql").val(atob('<?= strlen($record['rsql']) >1 ? base64_encode($record['rsql']) : $tbltpl ?>'))
		}else{
			$("#id_rsql").val(atob('<?= base64_encode($record['rsql'])?>'));
		}
	});
</script>