<?php if (count($pager->links())>1) : ?>
<?php
$ismobile=preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);  
$pager->setSurroundCount($ismobile?1:3); 
?>
<nav>
  <ul class="pagination">
  	<?php if ($pager->hasPrevious()) : ?>
    <li class="page-item">
      <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>">
        <span><i class="fa fa-fast-backward"></i></span>
      </a>
    </li>
    <li class="page-item">
      <a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="<?= lang('Pager.previous') ?>">
        <span aria-hidden="true"><i class="fa fa-step-backward"></i></span>
        <span class="sr-only"><?= lang('Pager.previous') ?></span>
      </a>
    </li>
    <?php endif ?>
    
    <?php foreach ($pager->links() as $link) : ?>
    <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
    	<a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
    </li>
	<?php endforeach ?>
	
	<?php if ($pager->hasNext()) : ?>
    <li class="page-item">
      <a class="page-link" href="<?= $pager->getNext() ?>" aria-label="<?= lang('Pager.next') ?>">
        <span aria-hidden="true"><i class="fa fa-step-forward"></i></span>
        <span class="sr-only"><?= lang('Pager.next') ?></span>
      </a>
    </li>
     <li class="page-item">
      <a class="page-link" href="<?= $pager->getLast() ?>">
        <span><i class="fa fa-fast-forward"></i></span>
      </a>
    </li>
    <?php endif ?>
  </ul>
</nav>
<?php endif ?>