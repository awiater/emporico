<?php if (empty($table_noscript)) :?>
<script>
<?php endif ?>
        <?php if (empty($table_script_nodef)) :?>
        $(function(){
            enableTooltip();
            <?php if (!empty($_disabled_records) && strlen($_disabled_records) > 0) :?>
             $('<?= $_disabled_records?>').remove();
            <?php endif ?>        
        });
        <?php endif ?> 
        $(".tableview_def_btns[href]").on('click',function(){
            if ($(this).attr('data-noloader')==undefined && $(this).attr('data-newtab')==undefined){
                addLoader();
            }
        });
        
	$(".tableview_def_btns").on('click',function(){
		var confirmed=true;
		if ($(this).attr('data-actiontype')=="delete"){
                    confirmed=false;
                    var action=$(this).attr('data-action');
                    var msg='<?= lang('system.general.msg_delete_ques')?>';
                    if ($(this).attr('data-delmsg')!=undefined){
                        msg=atob($(this).attr('data-delmsg'));
                    }
                    ConfirmDialog(msg,function(){
                       $('#<?= $table_view_datatable_id; ?>_form').attr('action', action).submit();
                    });
		}
                
		if (confirmed){
			var action=$(this).attr('data-action');
			$('#<?= $table_view_datatable_id; ?>_form').attr('action', action).submit();
		}
		
	});
	
        
	<?php if(!empty($_tableview_filters)) : ?>
	
	
	function tableviewSearchFilterSearch(){
		var filter=$("#<?= $table_view_datatable_id; ?>_search_form_filter_value").val();
		table_view_datatable.search(filter).draw();
	}

	$('#<?= $table_view_datatable_id; ?>_search_form_filter_value').on('keypress', function (e) {
    	if (e.which==13){
    		tableviewSearchFilterGo();
    	}
	} );
	
	function tableviewSearchFilterGo(filter=null){
		var url='<?= !empty($_tableview_filters_url) ? $_tableview_filters_url : '' ?>';
                
		if (filter!=null && filter.length>0){
			$("#<?= $table_view_datatable_id; ?>_search_form_filter_value").val(filter);
		}
		filter='<?= $_tableview_filters ?>';
		
		var value=$("#<?= $table_view_datatable_id; ?>_search_form_filter_value").val();
		filter=atob(filter);
		addLoader('.card');
                if (value=='*='){
                    window.location=url;
                }else
		if (value!=null && value.length>0){
                    if (value.indexOf('=') >= 0){
                        value=value.replace(/=/g,'":"');
                        value=value.replace(/&/g,'","');
                        filter='{"-value-":"%value%","'+value+'"}';
                        value='';
                    }
                    value=value.replace('<?= lang('system.general.yes') ?>',1);
			value=value.replace('<?= lang('system.general.no') ?>',0);
			filter=filter.replace(/%value%/g,value);
                        filter=filter.replace(/null/g,'"'+value+'"');
			$('input[name="filtered"]').val(btoa(filter));
                        $('input[name="filter"]').val(value);
		}
		$("#<?= $table_view_datatable_id; ?>_search_form").attr('action',url).submit();
	}
	
	<?php endif ?>
<?php if (empty($table_noscript)) :?>
</script>
<?php endif ?>
<!-- /Table Scripts -->