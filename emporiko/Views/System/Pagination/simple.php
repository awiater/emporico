<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(0);
?>
<?php if ($pager->hasPrevious()||$pager->hasNext()) : ?>
<nav>
	<ul class="pagination">
		<li class="page-item<?= $pager->hasPrevious() ? '' : ' disabled' ?>">
			<a class="page-link" href="<?= $pager->getPrevious() ?? '#' ?>" aria-label="<?= lang('Pager.previous') ?>">
				<span aria-hidden="true">&laquo;</span>
			</a>
		</li>
		<li class="page-item<?= $pager->hasNext() ? '' : ' disabled' ?>">
			<a class="page-link" href="<?= $pager->getnext() ?? '#' ?>" aria-label="<?= lang('Pager.next') ?>">
				<span aria-hidden="true">&raquo;</span>
			</a>
		</li>
	</ul>
</nav>
<?php endif ?>