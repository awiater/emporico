<?= $this->extend('Leaves/leavescalendar') ?>

<?= $this->section('calendar') ?>    
<div class="row col-12 attedance_calendar_header">
        <?php if($hredit):?>
        <div class="col-1">
            <?= lang('attedance.leavecal_site')?>
        </div>
        <?php endif ?>
        <div class="col-<?=$hredit ? '3 border-left' : '4' ?>">
            <?= lang('attedance.leavecal_usr_fullname')?>
        </div>
        <div class="col-1 border-left">
            <?= lang('attedance.leavecal_lefttobook')?>
        </div>
        <?php for($i=0;$i<$daysinweek;$i++) :?>
        
        <div class="col-1 border-left text-center">
            <?= convertDate(formatDate($header_date,'+ '.$i.' days'), 'DB', 'D') ?>
        </div>   
        <?php endfor ?>
    </div>
    <?php foreach($employees as $value) :?>
    <div class="row col-12 attedance_calendar_fullrow">
        <?php if($hredit):?>
        <div class="col-1 attedance_calendar_cells_info border-left-0">
            <?= $filter_site==null ? explode(',',$value['site'])[0] : $filter_site?>
        </div>
        <?php endif ?>
        <div class="col-<?=$hredit ? '3 border-left' : '4' ?> attedance_calendar_cells_info">
            <?= $value['usr_fullname'] ?>
        </div>
        <div class="col-1 attedance_calendar_cells_info text-center pt-2">
            <?= $value['lefttobook'] ?>
        </div>
        <?php for($i=0;$i<$daysinweek;$i++) :?>
            <?php $cd=formatDate($start_date,'+ '.$i.' days') ?>
            <div class="col-1 attedance_calendar_cells">
                <div class="attedance_calendar_cells_day">
                    <?= convertDate($cd,'DB','d')?>
                </div>
                <?= $currentView->includeView('Leaves/leave_daycell',['cd'=>$cd,'emp'=>$value['wrkid']]); ?>
            </div>
        <?php endfor ?>
    </div>
    <?php endforeach ?>
<?= $this->endSection() ?>