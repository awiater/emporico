<?= $this->extend('Leaves/leavescalendar') ?>

<?= $this->section('calendar') ?> 
    <div class="row attedance_calendar_header">
        <?php for($i=0;$i<$daysinweek;$i++) :?>
        <div class="col<?= $i>0 ? ' border-left ' : ' ' ?>text-center">
            <?= convertDate(formatDate($header_date,'+ '.$i.' days'), 'DB', 'D') ?>
        </div>   
        <?php endfor ?>
    </div>
    <?php $day=0; ?>
    <?php $cd=$start_day; ?>
    <div class="row" style="height:60px">
    <?php for($dm=0;$dm<$weeksInMonth*7;$dm++) :?>
        <?php if (convertDate($cd,'DB','w')==$day && convertDate($cd, 'DB', 'm')==convertDate($start_day, 'DB', 'm')) :?>     
            <div class="col attedance_calendar_cells">
                <div class="attedance_calendar_cells_day">
                    <?= convertDate($cd,'DB','d')?>
                </div>
                <?= $currentView->includeView('Leaves/leave_daycell',['cd'=>$cd]); ?>
            </div>
            <?php $cd=formatDate($cd,'+ 1 days'); ?>
        <?php else :?>
            <div class="col attedance_calendar_cells_disabled">
            </div>
        <?php endif ?>    
        <?php if ($day==6) :?>
            </div><div class="row" style="height:60px">
            <?php $day=-1; ?>
        <?php endif ?>
    <?php $day=$day+1; ?>        
    <?php endfor ?>
    </div>   
<?= $this->endSection() ?>
