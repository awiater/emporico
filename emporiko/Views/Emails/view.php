<?php if(empty($_external_call)) :?>
<?= $this->extend('Emails/index') ?>
<?= $this->section('_panel_content') ?>
<?php endif ?>
<div class="card-body p-0">
    <?php if(!empty($edittoolbar)) :?>
     <div class="mailbox-controls with-border text-left mt-2">
        <?= $edittoolbar?>
    </div>
    <?php endif ?>
    <div class="mailbox-read-info">
        <h6>
        <?= lang('emails.'.(!empty($is_sent_folder) && $is_sent_folder ? 'mail_to' : 'mail_from')) ?>&nbsp;&nbsp;
        <?php if(array_key_exists('mail_from_name',$record) && strlen($record['mail_from_name']) > 0) :?>
            <?= $record['mail_from_name']?>&lt;<?=$record['mail_from'] ?>&gt;
        <?php else :?>
            <?php if (!empty($is_sent_folder) && $is_sent_folder) :?>
                <?= $record['mail_to'] ?>
            <?php else :?>
                <?= $record['mail_from'] ?>
            <?php endif ?>
        <?php endif ?>
            <span class="mailbox-read-time float-right"><?= convertDate($record['mail_rec'], null, 'd M Y, H:i') ?></span>
        </h6>
        <h6><?= lang('emails.mail_subject') ?>&nbsp;&nbsp;<?= $record['mail_subject'] ?></h6>
    </div>

    <div class="mailbox-read-message">
        <?= $record['mail_body'] ?>
    </div>

</div>

<?php  if (is_array($record['mail_attachements'])) :?>   
<div class="card-footer bg-white">
    <ul class="mailbox-attachments d-flex align-items-stretch clearfix">
    <?php $url_attach=empty($url_attach) ? url('Emails','message',['view',$record['emid']],['attachement'=>'-id-','refurl'=> current_url(FALSE,TRUE)]) : $url_attach ?>
    <?php foreach($record['mail_attachements'] as $key=>$file) :?>
    <li>
        <span class="mailbox-attachment-icon">
            <i class="<?= config('Mimes')->getIconForMime($file['mime']) ?>"></i>
        </span>
        <div class="mailbox-attachment-info">
            <?= $file['name'] ?>
            <span class="mailbox-attachment-size clearfix mt-1">
                <span><?= convertBytesSize($file['size'],'text') ?></span>
                <a href="<?= str_replace('-id-',$key,$url_attach) ?>" class="btn btn-default btn-sm float-right"<?= !empty($_external_call) ? ' data-noloader="1"' : ''?>>
                    <i class="fas fa-cloud-download-alt"></i>
                </a>
            </span>
        </div>
    </li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif ?>
<?php if(empty($_external_call)) :?>
<?= $this->endSection() ?>
<?php endif ?>