<?php if (!empty($filtersform) && $filtersform) :?>
    <?= $currentView->includeView('System/form') ?>
<?php else :?>
    <?= $currentView->includeView('System/table') ?>
<?php endif ?>
<script>
    
    $(function(){
        //table_view_datatable_search_form_filter_value
        <?php if (!empty($filtersform) && $filtersform) :?>
        $("#id_formview_submit").html('<i class="fas fa-play-circle mr-1"></i><?= lang('reports.rep_runreport')?>'); 
        <?php else :?>
        $('.btn-table-back').remove();
        
        var table_view_datatable=$('#table_view_datatable').DataTable({
            'searching':false,
            'ordering':false,
            'paging':false,
            'bInfo' : false,
            dom:'Bfrtip',
            buttons: {
                dom: {
                    button: {
                                tag: 'button',
                                className: ''
                            }
                     },
		buttons:[
                        {
                            className: 'mb-2  btn btn-sm btn-dark text-white mr-5',
                            text:'<i class="far fa-arrow-alt-circle-left"></i>&nbsp;<?=lang('reports.rep_back_btn')?>',
                            titleAttr:'<?=lang('reports.rep_back')?>',
                            action: function ( e, dt, node, config ) {
                                window.location='<?= url('Reports')?>';
                            }
                        },
                        <?php if (!empty($is_fileters) && $is_fileters) :?>
                        {
                            className: 'mb-2  btn btn-sm btn-warning',
                            text:'<i class="fas fa-filter"></i>&nbsp;<?=lang('reports.rep_rfilters')?>',
                            titleAttr:'<?=lang('reports.rep_rfilters')?>',
                            action: function ( e, dt, node, config ) {
                                window.location='<?= current_url()?>';
                            }
            		},
                        <?php endif ?>
			{
                            className: 'mb-2  btn btn-sm btn-success ml-2',
                            extend: 'csv',
                            text:'<i class="fas fa-file-csv"></i>&nbsp;<?=lang('reports.rep_resdown_btn')?>',
                            filename:'<?= $report['rname']?>',
                            titleAttr:'<?=lang('reports.rep_resdown')?>'
            		},
                        {
                            className: 'mb-2  btn btn-sm btn-secondary',
                            extend: 'print',
                            text:'<i class="fas fa-print"></i>&nbsp;<?=lang('reports.rep_resprint_btn')?>',
                            title:'<?= $report['rtitle']?>',
                            titleAttr:'<?=lang('reports.rep_resprint')?>'
                        },
                            {
                            className: 'mb-2 btn btn-sm btn-outline-danger',
                            extend: 'pdf',
                            text:'<i class="far fa-file-pdf"></i>&nbsp;<?=lang('reports.rep_respdf_btn')?>',
                            title:'<?= $report['rtitle']?>',
                            filename:'<?= $report['rname']?>',
                            titleAttr:'<?=lang('reports.rep_respdf')?>'
                        },
                        ]
                    }
		});
        $("[title]").attr('data-toggle','tooltip');
        <?php endif ?>
    });
    
</script>