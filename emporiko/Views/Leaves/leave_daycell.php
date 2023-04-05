<?php if (array_key_exists($emp.'_'.substr($cd,0,8), $events)) :?>
<?php $event=$events[$emp.'_'.substr($cd,0,8)] ?>
<button <?= $event['app']=='0' ? 'style="opacity:0.5" ' : ''?>class="btn btb-sm btn-<?=$event['ltcolor']?> btn-sm w-75" data-event="<?= $emp.'_'.substr($cd,0,8) ?>" data-toggle="tooltip" data-placement="top" title="<?= $event['ltname'] ?>">
 <i class="<?=$event['lticon']?>"></i>
</button>    
<?php else :?>
<button class="btn btb-sm btn-white d-none btnnew" data-date="<?=$cd?>" data-emp="<?=$emp?>">
    <i class="fas fa-plus"></i>
</button>
<?php endif ?>