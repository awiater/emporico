<div class="container col-12">
    <?php $col=0;?>
    <div class="row">
    <?php foreach($_tiles as $_tile) :?>
        <div class="col-xs-12 col-md-3">
            <?php if(array_key_exists('action', $_tile) && $_tile['action']) :?>
                <?= loadModule($_tile['data']) ?>
            <?php else :?>
                <?= $_tile['data'] ?>
            <?php endif ?>
        </div>
        <?php if($col>=$_cols) :?>
        </div><div class="row">
        <?php $col=-1; ?>
        <?php endif ?>
        <?php $col++ ?>
    <?php endforeach;?>
    </div>
</div>