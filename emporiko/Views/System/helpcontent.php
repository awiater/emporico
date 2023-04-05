<!-- Help Content -->
<!-- Help Content Button trigger -->
<button type="button" class="btn <?= $args['button_class'] ?>" data-toggle="tooltip" data-placement="right" title="<?= lang('system.general.helpbtn_tooltip') ?>" id="helpContentButton">
	<i class="far fa-question-circle"></i>
</button>

<!-- Help Content Modal -->
<div class="modal fade" id="helpContentModal" tabindex="-1" role="dialog" aria-labelledby="helpContentModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="helpContentModalLabel"><?= lang('system.general.helpmodal_title') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				 <iframe src="" name="test" height="120" width="600" id="helpContentModalFrame"></iframe>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			</div>
		</div>
	</div>
</div>
<script>
$("#helpContentButton").on("click",function(){
	$("#helpContentModalFrame").attr('srcdoc',atob('<?= $content ?>'));
	$("#helpContentModal").modal('show');
});	
</script>
<!-- /Help Content -->