<?= $this->extend('System/Table/table_index') ?>

<?= $this->section('table_toolbar') ?>
    <?= $currentView->IncludeView('System/Table/table_toolbar',['_table_filters'=>$currentView->IncludeView('System/Table/table_filters')]); ?>
<?= $this->endSection() ?>

<?= $this->section('table_body') ?>
<?php if ($currentView->isMobile()) :?>
<div class="container col-12">
    <?php foreach($currentView->getViewData('_tableview_data') as $row) :?>
    <div class="row" style="cursor:pointer" data-url="<?=url('Sales','all',[$row['ordid']],['mode'=>$row['ord_type'],'refurl'=>current_url(FALSE,TRUE)])?>">
        <div class="info-box bg-<?= str_replace([0,1,2], ['warning','info','success'], $row['ord_type'])?>">
            <span class="info-box-icon">
                <?php if (array_key_exists($row['ord_type'], $type_images)) :?>
                <?= $type_images[$row['ord_type']] ?>
                <?php else :?>
                <i class="fas fa-shopping-cart"></i>
                <?php endif?>
            </span>
            <div class="info-box-content">
                <span class="info-box-text"><?= $row['ord_ref'] ?></span>
                <span class="info-box-number"><?= $row['ord_cus_curr'] ?>&nbsp;<?=  number_format($row['ord_our_value'], 2, '.', '') ?></span>
                <div class="progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
                <small class="progress-description">
                    <?php if (strlen($row['ord_desc'])>0) :?>
                    <p class="mb-1"><?= $row['ord_desc'] ?></p>
                    <?php endif?>
                    <p>
                        <?php if (strlen($row['ord_addon'])>0) :?>
                        <i class="far fa-calendar-plus mr-1"></i><?= convertDate($row['ord_addon'], null, 'd M Y') ?>
                        <i class="fas fa-ellipsis-v"></i>
                        <i class="fas fa-user-tie mr-1"></i><?= $row['ord_addby'] ?>
                        <i class="fas fa-ellipsis-v"></i>
                        <?php endif?>
                        <i class="far fa-building mr-1"></i><?= $row['ord_cusacc'] ?>
                        
                    </p>
                </small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else : ?>
<?= $currentView->IncludeView('System/Table/table_body'); ?>
<?php endif?>
<?= $this->endSection() ?>

<?= $this->section('table_script') ?>
    <?= $currentView->IncludeView('System/Table/table_script'); ?>
<?= $this->endSection() ?>