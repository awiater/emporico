-------<?= lang($msg) ?>-------
<table>
    <tr>
        <td class="font-weight-bold"><?= lang('emails.mail_subject') ?></td>
        <td><?= $data['mail_subject'] ?></td>
    </tr>
    <tr>
        <td class="font-weight-bold"><?= lang('emails.mail_rec') ?></td>
        <td><?= convertDate($data['mail_rec'], null, 'd M Y, H:i') ?></td>
    </tr>
    <tr>
        <td class="font-weight-bold"><?= lang('emails.mail_from') ?></td>
        <td><?= $data['mail_from'] ?></td>
    </tr>
    <tr>
        <td class="font-weight-bold"><?= lang('emails.mail_to') ?></td>
        <td><?= $data['mail_to_orig'] ?></td>
    </tr>
</table> 

