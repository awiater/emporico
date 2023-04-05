<?= $this->extend('Emails/index') ?>
<?= $this->section('_panel_content') ?>
<div class="card-body">
    <?= $edittoolbar?>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th scope="col" style="width:30px"><input type="checkbox" onclick="$('input[name*=\'emid\']').prop('checked', this.checked);"></th>
                <th scope="col" style="width:30px"></th>
                <th scope="col" style="width:20%"><?= lang('emails.'.($is_sent_folder ? 'mail_to' : 'mail_from')) ?></th>
                <th scope="col"><?= lang('emails.mail_subject') ?></th>
                <th scope="col" style="width:10%"><?= lang('emails.mail_rec') ?></th>
                <th scope="col" style="width:5%" id="emailsInboxToolbox"></th>
            </tr>
        </thead>
        <tbody>
            <?= form_open('',['id'=>'emails_msgslist_form'],['model'=>'emails']) ?>
            <?php foreach($emails as $email) :?>
            <tr<?= !$email['mail_read'] ? ' style="font-weight: 700!important;"':''?>>
                <td>
                    <input type="checkbox" value="<?= $email['emid'] ?>" name="emid[]">
                </td>
                <td>
                    <?php if (strlen($email['mail_attachements']) > 0) :?>
                    <i class="fas fa-paperclip text-secondary"></i>
                    <?php endif ?>
                </td>
                <td>
                    <?php if ($is_sent_folder) :?>
                    <?= array_key_exists($email['mail_to'], $contacts) ? ($contacts[$email['mail_to']]).'&lt;'.$email['mail_to'].'&gt;' : $email['mail_to']?>
                    <?php else :?>
                     <?= array_key_exists($email['mail_from'], $contacts) ? $contacts[$email['mail_from']].'&lt;'.$email['mail_from'].'&gt;' : $email['mail_from']?>
                    <?php endif ?>
                </td>
                <td>
                     <?=$email['mail_subject']?>
                </td>
                <td>
                    <small>
                        <?= convertDate($email['mail_rec'], null, 'd M Y H:i')?>
                    </small>
                </td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="<?= lang('emails.btn_open') ?>" data-url="<?= str_replace('-id-', $email['emid'], $url_view)?>">
                        <i class="fas fa-envelope-open-text"></i>
                    </button> 
                </td>
            </tr>
            <?php endforeach ?>
            </form>
        </tbody>
        <tfoot>
            <tr>
                <td>
                    <?= $pagination['links'] ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<?= form_open(current_url(FALSE,FALSE),['id'=>'id_emailmsg_filter_form']) ?></form>
<script>
$(function(){
    $('#id_emailmsg_filter').before($('#id_emailmsg_filter_form').detach());
    $('#id_emailmsg_filter_form').append($('#id_emailmsg_filter').detach());
});

$('#id_emailmsg_filtergo_button').on('click',function(){
    addLoader('#emails_body_content');
    $('#id_emailmsg_filter_form').submit();
});
</script>
<?= $this->endSection() ?>