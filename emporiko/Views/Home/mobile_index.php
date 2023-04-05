<div class="row">
	
	<?php foreach ($currentView->getHTMLMenu('mobile',null,-1) as $button) : ?>
	<div class="col-6 mt-3">
		<a href="<?= url($button['mroute']) ?>" class="btn btn-lg btn-<?= strlen($button['mkeywords']) > 0 ? $button['mkeywords'] : 'info' ?> text-white ml-3" style="width:160px;height:160px;">
			<div style="height:100%;">
				<div class="mt-3">
					<i class="<?= $button['mimage']; ?> fa-3x"></i>
					<p class="mt-2 font-weight-bold"><?= lang($button['mtext']); ?></p>
				</div>
			</div>
		</a>
	</div>
	<?php endforeach;?>
</div>