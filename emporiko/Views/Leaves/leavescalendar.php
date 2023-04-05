<?= $this->extend('System/form') ?>

<?= $this->section('form_header') ?>
<div class="d-flex p-0 m-0">
    <h5 class="ml-auto"><?= $month ?></h5>
</div>
<?= $this->endSection() ?>

<?= $this->section('form_body') ?>
<?= $currentView->getFields('navigation') ?>
<div class="container p-0 m-0 col-12 attedance_calendar_container">
<?= $this->renderSection('calendar') ?>
</div>

<div class="modal" tabindex="-1" role="dialog" id="leaveNewEventOptions">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <?php foreach($leavetypes as $leavetype) :?>
                    <div class="col-xs-12 col-md-<?= count($leavetypes) > 2 ? '4':'6'?>" data-leavetype="<?=$leavetype['wrkltid']?>" style="cursor: pointer">
                        <div class="small-box bg-<?=$leavetype['ltcolor']?>" >
                            <div class="inner">
                                <h3><?=$leavetype['ltname']?></h3>
                                <p><?=$leavetype['ltdesc']==null ? '...' : $leavetype['ltdesc']?></p>
                            </div>
                            <div class="icon">
                                <i class="<?=$leavetype['lticon']?>"></i>
                            </div>
                        </div> 
                    </div>
                    <?php endforeach;?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="leaveEventDetails">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            
        </div>
    </div>
</div>

<script>
$(function(){
    $('.attedance_calendar_cells').on('mouseover',function(){
        $(this).find('.btnnew').removeClass('d-none');
    });
    $('.attedance_calendar_cells').on('mouseout',function(){
        $(this).find('.btnnew').addClass('d-none');
    });
    
    $('#btn_leavereport').parent().addClass('d-flex w-100');
    $('#btn_leavereport').parent().parent().addClass('w-100');
});

$('[data-event]').on('click',function(){
     var events=JSON.parse(atob('<?= base64_encode(json_encode($events))?>'));
     var id=$(this).attr('data-event');
     $("#leaveEventDetails").find('.modal-content').html('');
     ajaxCall(
             '<?= $url_getreportform?>',
             events[id],
             function(data){
                 $("#leaveEventDetails").find('.modal-content').html(atob(data['html']));//
             },
             null);
    $('#leaveEventDetails').modal('show');
});
$('[name^="filter_"]').on('change',function(){
    if ($(this).attr('name')=='filter_site'){
        $('[name="filter_empl"]').val('');    
    }
    filterGo();
});

$('.btnnew').on('click',function(){
    $('.btnnew').removeAttr('data-clicked');
    <?php if (count($leavetypes) >1 ) :?>
     $(this).attr('data-clicked','true');       
    $("#leaveNewEventOptions").modal('show');
    <?php else :?>
        neweventGo('<?=$leavetypes[0]['wrkltid'] ?>',$(this).attr('data-emp'),$(this).attr('data-date'));       
    <?php endif ?>
});

$('[data-leavetype]').on('click',function(){
    var item=$('[data-clicked="true"]');
    neweventGo($(this).attr('data-leavetype'),item.attr('data-emp'),item.attr('data-date'));
});

function neweventGo(id,emp,dt){
    var url='<?= $url_new ?>';
    url=url.replace('-id-',id);
    url=url.replace('-emp-',emp);
    url=url.replace('-dt-',dt);
    window.location=url;
}   

function filterGo(){
    var url=atob('<?= $url_filter?>');
    var emp=$('[name="filter_empl"] option:selected').val();
    
    url=url.replace('-site-',$('[name="filter_site"] option:selected').val());
    url=url.replace('-date-',$('[name="filter_date"] option:selected').val());
    if (emp!=undefined  && emp.length > 0){
        url+='?'+'worker='+emp;
    }
    window.location=url;
}
</script>
<?= $this->endSection() ?>
