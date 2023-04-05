<?= $currentView->includeView('System/form') ?>
<ul class="list-group" id="id_access_groups_list">
    <li class="list-group-item border" id="id_access_groups_list_item">
        <div class="row">
            <div class="col-2"></div>
            <div class="col-8">
                <?php foreach(!empty($access_levels) && is_array($access_levels) ? $access_levels : [] as $access_level) :?>
                <div class="icheck-primary">
                    <input type="checkbox" <?= $record['access_groups']['acc_'.$access_level]==1 || $record['access_groups']['acc_'.$access_level]=='1' ? 'checked="true" ':''?>value="1" id="id_access_groups_list_item_<?=$access_level?>" name="perms[<?= $record['access_groups']['acc_ref'] ?>][<?= $access_level ?>]">
                    <label for="id_access_groups_list_item_<?= $access_level?>"><?= lang('system.auth.groups_acc_'.$access_level) ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </li>
</ul>
<script>
    $(function(){
        $('#id_access_groups_field').removeClass('border p-2').html($('#id_access_groups_list').detach());
    });
</script>