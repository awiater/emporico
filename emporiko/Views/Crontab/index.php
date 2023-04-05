
<?= $currentView->includeView('errors/html/exception',['msg'=>lang('crontab.index_note',['*/5 * * * * /usr/bin/curl -s '. site_url().'cron.php > /dev/null']),'type'=>'warning']); ?>

<?php if(!empty($_msg)) :?>
<?= $currentView->includeView('errors/html/exception',['msg'=>$_msg,'type'=>'warning']); ?>
<?php endif ?>
<?= $currentView->includeView('System/table') ?>
