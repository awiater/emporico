<div class="d-none">
<div id="leaveReportPrint">
    <div class="row col-12">
        <div class="col-6">
            <img src="<?= protected_link(parsePath($config->theme_logo,TRUE),TRUE)?>" style="width:70%;">
        </div>
        <div class="col-6">
            <h3><?=lang('attedance.leave_report_title')?></h3>
        </div>
    </div>
    <div class="row col-12">
        <div class="col-12" id="leaveReportPrintBody"></div>
    </div>
</div>
</div>    
<div class="modal-header">
    <h5 class="modal-title"><?=lang('attedance.leave_report_title')?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="<?=lang('system.buttons.close')?>">
        <span aria-hidden="true">&times;</span>
     </button>
</div>
<div class="modal-body" id="leaveReportBody">                
<table class="table table-borderless">
    <tbody>
        <tr>
            <td style="width:33%"><?= lang('attedance.leave_emp') ?></td>
            <td>
                <div class="form-control"><?= $data['fullname'] ?></div>
            </td>
         </tr>
         <tr>
            <td style="width:33%"><?= lang('attedance.leave_type') ?></td>
            <td>
                <div class="form-control"><?= $data['ltname'] ?></div>
            </td>
        </tr>
        <tr>
            <td style="width:33%"><?= lang('attedance.leave_start') ?></td>
            <td>
                <div class="form-control"><?= convertDate($data['start'], 'DB', 'd M Y') ?></div>
            </td>
        </tr>
        <tr>
            <td style="width:33%"><?= lang('attedance.leave_end') ?></td>
            <td>
                <div class="form-control"><?= convertDate($data['end'], 'DB', 'd M Y') ?></div>
            </td>
        </tr>
        <?php if (!empty($data['text'])) :?>
        <tr>
            <td style="width:33%"><?= lang('attedance.leave_text') ?></td>
            <td>
                <div class="form-control"><?= $data['text']?></div>
            </td>
        </tr>
        <?php endif ?>
        <?php if ($data['app']==1 || $data['app']=='1') :?>
        <tr>
            <td style="width:33%"><?= lang('attedance.leave_app_on') ?></td>
            <td>
                <div class="form-control"><?= convertDate($data['app_on'], 'DB', 'd M Y')?></div>
            </td>
        </tr>
        <tr>
            <td style="width:33%"><?= lang('attedance.leave_app_by') ?></td>
            <td>
                <div class="form-control"><?= $data['app_by']?></div>
            </td>
        </tr>
        <?php endif ?>
    </tbody>
</table>
</div>
<div class="modal-footer">
    <?php if($data['ismanager'] && ($data['app']==0 || $data['app']=='0') && !$currentView->isMobile()) :?>
    <div class="mr-auto p-0">
    <a href="<?= $data['url_app'] ?>" class="btn btn-success" data-btntype="approve" data-toggle="tooltip" data-placement="top" title="<?=lang('attedance.leave_app_btn_tooltip')?>">
        <i class="fas fa-check"></i>
    </a>
    <a href="<?= $data['url_dec'] ?>" class="btn btn-danger ml-1" data-btntype="decline" data-toggle="tooltip" data-placement="top" title="<?=lang('attedance.leave_dec_btn_tooltip')?>">
        <i class="fas fa-ban"></i>
    </a>
    </div>
    <?php endif ?>
    <button type="button" class="btn btn-dark" data-btntype="print" data-toggle="tooltip" data-placement="top" title="<?=lang('attedance.leave_prnt_btn_tooltip')?>">
        <i class="fas fa-print"></i>
    </button>
    
    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=lang('system.buttons.close')?></button>
</div>
<script>
$('[data-btntype="print"]').on('click',function(){
    $('#leaveReportPrintBody').html('');
    $('#leaveReportBody').clone().appendTo('#leaveReportPrintBody');
    $('#leaveReportPrint').printThis({
            debug: false,              
            importCSS: true,             
            importStyle: false,         
            printContainer: true,       
            pageTitle: "My Modal",             
            removeInline: false,        
            printDelay: 133,            
            header: '',             
            formValues: true
        });
});
</script>
