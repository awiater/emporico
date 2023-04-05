<?= $currentView->includeView('System/form') ?>
<ul class="list-group" id="id_access_groups_list">
    <?php foreach(!empty($access_groups) && is_array($access_groups) ? $access_groups : [] as $key=>$access_group) :?>
    <li class="list-group-item border" id="id_access_groups_list_item_<?=$key?>">
        <div class="row">
        <div class="col-2"></div>
        <div class="col-8 row">
             <?php foreach(!empty($access_levels) && is_array($access_levels) ? $access_levels : [] as $access_level) :?>
            <label class="col-2">
                <?= $access_level ?>
            </label>
             <?php endforeach; ?>
        </div>
        </div>
        <div class="row">
            <div class="col-2"><?= $access_group['ugname'] ?></div>
            <div class="col-8 row">
                <?php foreach(!empty($access_levels) && is_array($access_levels) ? $access_levels : [] as $access_level) :?>
                <div class="icheck-primary col-2">
                    <input type="checkbox" <?= $access_group['acc_'.$access_level]==1 || $access_group['acc_'.$access_level]=='1' ? 'checked="true" ':''?>value="1" id="id_access_groups_list_item_<?=$key.'_'.$access_level?>" name="perms[<?= $access_group['module'].'.'.$access_group['ugref'] ?>][<?= $access_level ?>]">
                    <label for="id_access_groups_list_item_<?=$key.'_'.$access_level?>"><?= $access_level ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </li>
    <?php endforeach; ?>
</ul>
<script>
    $(function(){
        $('#id_access_groups').removeClass('border p-2').html($('#id_access_groups_list').detach());
    });
</script>

