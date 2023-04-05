<?= $currentView->includeView('System/form') ?>
<table class="table" id="id_access_groups_list">
    <tbody>
        <tr class="bg-dark">
            <th scope="row"></th>
            <?php foreach(!empty($access_levels) && is_array($access_levels) ? $access_levels : [] as $access_level) :?>
            <td>
                <label>
                    <?= lang('system.auth.groups_acc_'.$access_level) ?>
                </label>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php foreach(!empty($access_groups) && is_array($access_groups) ? $access_groups : [] as $key=>$access_group) :?>
        <tr>
            <th scope="row" class="bg-dark"><?= $access_group['ugname'] ?></th>
            <?php foreach(!empty($access_levels) && is_array($access_levels) ? $access_levels : [] as $access_level) :?>
            <td class="text-center">
                <div class="icheck-primary">
                    <input type="checkbox" <?= $access_group['acc_'.$access_level]==1 || $access_group['acc_'.$access_level]=='1' ? 'checked="true" ':''?>value="1" id="id_access_groups_list_item_<?=$key.'_'.$access_level?>" name="perms[<?= $access_group['module'].'.'.$access_group['ugref'] ?>][<?= $access_level ?>]">
                    <label for="id_access_groups_list_item_<?=$key.'_'.$access_level?>"></label>
                </div>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<script>
    $(function(){
        $('#id_access_groups').removeClass('border p-2').html($('#id_access_groups_list').detach());
    });
</script>

