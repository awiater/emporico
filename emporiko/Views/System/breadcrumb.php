<nav aria-label="breadcrumb"<?= array_key_exists('nav',$class) ? ' class="'.$class['nav'].'"':''?>>
    <ol class="breadcrumb<?= array_key_exists('ol',$class) ? ' '.$class['ol']:''?>">
        <?php $key=0; ?>
        <?php foreach($crumbs as $text=>$url) :?>
        <li class="breadcrumb-item<?= array_key_exists('li',$class) ? ' '.$class['li']:''?>
            <?= array_key_exists('li.'.$key,$class) ? ' '.$class['li.'.$key]:''?>" id="breadcrumb_item_<?=$key?>">
            <?php if ($count>0 && $key==$count) :?>
            <?=$text?>
            <?php else :?>
            <a href="<?= $url ?>"<?= array_key_exists('a',$class) ? ' class="'.$class['a'].'"':''?> data-loader="true"><?= $text ?></a>
            <?php endif; ?>
            <?php $key++ ?>
        </li>
        <?php endforeach ?>
    </ol>
</nav>

