<?= $currentView->includeView('System/form') ?>
<script>
	$(function(){
		$(".form-group").each(function(){
			$(this).addClass('mt-2 mb-3 border-top border-dark');
		});
	});
</script>
