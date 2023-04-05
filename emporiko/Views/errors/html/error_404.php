<div class="bg-danger">
	<h1><?= lang('system.errors.nopagefound_h'); ?></h1>
	<p>
		<?php if (! empty($message) && $message !== '(null)') : ?>
			<?= esc($message) ?>
		<?php else : ?>
			<?= lang('system.errors.nopagefound_small'); ?>
		<?php endif ?>
	</p>
</div>