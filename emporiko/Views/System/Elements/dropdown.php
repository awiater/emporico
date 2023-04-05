<div class="dropdown">
  <button class="<?= !empty($class) ? $class : 'btn btn-secondary'?> dropdown-toggle" type="button" id="<?= $id ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <?= $text ?>
  </button>
  <div class="dropdown-menu" aria-labelledby="<?= $id ?>">
    <?php foreach($dropdownitems as $item) :?>
    	<?php if (is_array($item) && array_key_exists('text', $item) && array_key_exists('href', $item)) :?>
    		<a class="dropdown-item" href="<?= $item['href'] ?>"><?= $item['text'] ?></a>
    	<?php elseif (is_string($item)) :?>
    		<?= $item ?>
    	<?php endif ?>	
    <?php endforeach ?>
  </div>
</div>