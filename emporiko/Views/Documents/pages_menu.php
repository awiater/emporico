<?php if (!empty($menu) && is_array($menu) && count($menu) > 0) :?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#pagesMenuListContainer" aria-controls="pagesMenuListContainer" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="pagesMenuListContainer">
        <ul class="navbar-nav mr-auto">
            <?php foreach($menu as $menu_name=>$menu_data) :?>
            <li class="nav-item">
                <a class="nav-link<?=$menu_name==$menu_curpage ? ' active':''?>" href="<?= $menu_data['url'] ?>"><?= $menu_data['title'] ?></a>
            </li>
            <?php endforeach ?>
        </ul>
    </div>
</nav>
<?php endif ?>