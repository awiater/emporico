<div class="<?= $args['mode'] ?>">
    <button class="<?= $args['class']?> dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?php if(array_key_exists('icon', $args)) :?>
        <?= $args['icon'] ?>
        <?php endif ?>
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <?php foreach(!empty($items) && is_array($items) ? $items : [] as $key=>$item) :?>
            <a class="dropdown-item" href="<?= $item ?>"><?= lang($key) ?></a>
        <?php endforeach; ?>
    </div>
</div>

