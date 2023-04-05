<?php $kkey=0; ?>
<ul class="list-group">
    <?php foreach ($items as $key => $listvalue) :?>
    <li class="list-group-item p-2 d-flex border">
        <div class="my-auto">
            <input class="<?= $chb ?>" type="checkbox" value="<?= $key ?>" id="id_<?=$name?>_option_<?=$kkey?>" name="<?= $name?>"<?= \EMPORIKO\Helpers\Strings::contains($value,strval($key)) ? ' checked':null?>>
            <label class="<?= $chb_label ?> m-0" for="id_<?=$name?>_option_<?=$kkey?>"><?= lang($listvalue) ?></label>
        </div>
    </li>
    <?php $kkey++; ?>
    <?php endforeach; ?>
</ul>
