<?php if (empty($emails) || (!empty($emails) && (!is_array($emails) || (is_array($emails) && count($emails) < 1)))) :?>
<h6 class="text-center"><?=lang('emails.error_noemailstoshow') ?></h6>
<?php else :?>
<div class="table-fixed">
<table class="table table-hover table-striped" id="accounts_emails_container">
    <tbody>
        <?php foreach($emails as $email) :?>
        <tr<?= intval($email['enabled'])==1 ? ' class="font-weight-bold"' : '' ?> style="cursor:pointer" data-id="<?= $email['emid'] ?>" data-url="<?= str_replace('-id-', $email['emid'], $url_email_view) ?>">
            <td class="mailbox-name" style="width:25%">
                <?= strlen($email['mail_from_name']) > 0 ? $email['mail_from_name'] : $email['mail_from'] ?>
            </td>
            <td class="mailbox-subject">
                <?= $email['mail_subject'] ?>
            </td>
            <td class="mailbox-date text-sm" style="width:15%">
                <?= convertDate($email['mail_rec'],'DB','d M Y H:i') ?>
            </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
</div>
<?php endif; ?>