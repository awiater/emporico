<?= $this->extend('System/Table/table_index') ?>

<?= $this->section('table_toolbar') ?>
    <?= $currentView->IncludeView('System/Table/table_toolbar',['_table_filters'=>$currentView->IncludeView('System/Table/table_filters')]); ?>
<?= $this->endSection() ?>

<?= $this->section('table_body') ?>
<?= $currentView->IncludeView('System/Table/table_body'); ?>
<?= $this->endSection() ?>

<?= $this->section('table_script') ?>
    <?= $currentView->IncludeView('System/Table/table_script'); ?>
<?= $this->endSection() ?>