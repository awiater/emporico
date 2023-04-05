<div class="row col-12">
	<div class="col-12">
		<?= $currentView->includeView('System/form') ?>
	</div>
	<div class="col-xs-12 col-md-4 d-none">
		<div class="card">
			<div class="card-header">
				<h5><?= lang('system.products.navigation_lbl') ?></h5>
			</div>
			<div class="card-body">
				
			</div>
		</div>
	</div>
</div>

<div id="navbar" class="ml-auto">
	<button type="button" data-url="<?= $record['pdid']==$navrecords['prev'] ? '#' : $navrecords['url_prev'] ?>" class="btn bn-sm btn-secondary" data-enabletooltip="true" data-placement="top" title="<?= lang('system.buttons.nav_prev')?>" <?= $record['pdid']==$navrecords['prev'] ? 'disabled' : '' ?>>
		<i class="fas fa-backward mr-1"></i>
	</button>	
	<button type="button" data-url="<?= $record['pdid']!=$navrecords['next']  ? $navrecords['url_next'] : '#' ?>" class="btn bn-sm btn-secondary ml-1" data-enabletooltip="true" data-placement="top" title="<?= lang('system.buttons.nav_next')?>" <?= $record['pdid']==$navrecords['next'] ? 'disabled' : '' ?>>
		<i class="fas fa-forward"></i>
	</button>
	<button type="button" onClick="startSearch()" class="btn bn-sm btn-dark ml-2" data-enabletooltip="true" data-placement="bottom" title="<?= lang('system.buttons.search')?>" >
		<i class="fas fa-search"></i>
	</button>
	<button type="button" data-url="<?= is_numeric($record['pdid']) ? $navrecords['url_new'] : '#' ?>" class="btn bn-sm btn-primary ml-3" data-enabletooltip="true" data-placement="top" title="<?= lang('system.buttons.new')?>" <?= !is_numeric($record['pdid']) ? ' disabled' : '' ?>>
		<i class="fas fa-plus"></i>
	</button>
	<button type="button" id="nav_btn_del" class="btn bn-sm btn-danger ml-2" data-enabletooltip="true" data-placement="top" title="<?= lang('system.buttons.remove')?>"<?= !is_numeric($record['pdid']) ? ' disabled' : '' ?>>
		<i class="fas fa-trash-alt"></i>
	</button>
</div>
<div class="modal" tabindex="-1" id="statusChangeModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('system.products.statusChangeModalTitle') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?= $currentView->includeView('System/form_fields',['fields'=>array_key_exists('statusChangeModal', $fields) ? $fields['statusChangeModal'] : []]); ?>
      </div>
      <div class="modal-footer">
        <button type="button" id="statusChangeModalSubmit" class="btn btn-primary">
        	<?= lang('system.buttons.confirm') ?>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal" tabindex="-1" id="searchModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('system.products.searchModalFilter_title') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?= $currentView->includeView('System/form_fields',['fields'=>array_key_exists('searchModal', $fields) ? $fields['searchModal'] : []]); ?>
      </div>
      <div class="modal-footer">
        <button type="button" id="searchModalSubmit" class="btn btn-primary">
        	<?= lang('system.buttons.search') ?>
        </button>
      </div>
    </div>
  </div>
</div>
<script>
	$(function(){
		$("#id_status").wrap('<div class="row col-12">');
		
		$("#id_status").after(function(){
			var refhtml='<div id="id_status_part" class="form-control bg-light text-danger col-4<?= array_key_exists('super_part', $record) && strlen($record['super_part']) < 1 ? ' d-none' :'' ?>">';
			refhtml+='<?=$record['super_part']?>'
			refhtml+='</div>';
			return refhtml;
		});
		
		$("#id_status").addClass("col-8");
		$("#id_formview_submit").attr('type','button');	
		
		getGroupCode($("#id_groupcode").val());
		
		$("#id_groupcode_list").on("change",function(){
			getGroupCode($(this).val());
		});
		
		$(".btn").addClass('btn-sm');
		$("#id_dim_width_field").addClass('col-3').removeClass('col-12').wrap('<div class="row col-12" id="id_dims">');
                
		$(".id_dims_child").parent().addClass('col-3').removeClass('col-12').detach().appendTo("#id_dims");
		$("#id_isserial_field").addClass('col-4').removeClass('col-12').wrap('<div class="row col-12" id="id_isserial_group">').addClass('col-4');
		$(".isserial_group").parent().addClass('col-4').removeClass('col-12').detach().appendTo("#id_isserial_group");
		$('.text-muted').addClass('d-none');
		$('.form-group').find('label').hover(function(){
			var tooltip=$(this).parent().find('.text-muted').text();
			if (tooltip.length > 21){
				$(this).css('cursor','help');
				$(this).tooltip({placement:'top',title:tooltip});
			}
			
		},function() {
                    $(this).css('cursor','auto');
                });
    	<?php if (is_numeric($record['pdid'])) :?>
    	$("input[data-url], select[data-url]").each(function(){
    		var url='<?= $configurl ?>';
    		url=url.replace('-id-',$(this).attr('data-url'));
    		addListConfigButton($(this).attr('id'),url,'aa');
    	});
		<?php endif ?>
		$('.card-header').addClass('d-flex').append($('#navbar'));
		
		enableTooltip('[data-enabletooltip="true"]');
                //addMovement('input[type="text"],select','change_details',type,mhref,mhfrom='',mhto='',mhinfo='',false)
	});
	
	$("#id_status").on("change",function(){
		$("#id_super_part").removeAttr('required');
		$("#id_super_part").val('');
		$("#id_status_part").addClass('d-none');
	});
	
	$("#nav_btn_del").on("click",function(){
		ConfirmDialog('<?= lang('system.general.msg_delete_ques') ?>',function(){
			var url='<?= $navrecords['url_del'] ?>';
			window.location=url;
		});
	});
	
	
	$("#statusChangeModalSubmit").on("click",function(){
		$("#statusChangeModal").modal("hide");
		$("#"+$(this).attr('form')).append($('#id_super_part')).submit();		
	});
	
	$("button[data-url]").on('click',function(){
		window.location=$(this).attr('data-url');
	});
	
	$("#searchModalSubmit").on('click',function(){
		var filter='';
		var url=atob('<?= base64_encode($filterurl) ?>');
		$("[tab_name='searchModal']").each(function(){
			if ($(this).attr('type')=='checkbox' || $(this).attr('type')=='radio'){
				if ($(this).is(':checked')){
					filter+='&'+$(this).attr('name')+'='+btoa($(this).val());
				}
			}else
			{
				filter+='&'+$(this).attr('name')+'='+btoa($(this).val());
			}
			
		});
		url=url.replace('-filters-',filter);
		window.location=url;
	});
	
	
	
	$("#id_formview_submit").on("click",function(){
		var val=$("#id_status option:selected").val();
		var supval=$('#id_super_part').val();
		if ((val=="S" || val=="W") && supval.length < 1){
			$("#statusChangeModalSubmit").attr('form',$(this).attr('form'));
			$("#id_super_part").attr('required','true');
			$("#statusChangeModal").modal("show");
		}else{
			$("#"+$(this).attr('form')).append($('#id_super_part')).submit();
		}
	});
	
	function startSearch()
	{
		$("[tab_name='searchModal']").each(function(){
			if ($(this).attr('type')=='checkbox' || $(this).attr('type')=='radio'){
				$(this).prop('checked',false);
			}else{
				$(this).val('');
			}
			
		});
		$('#searchModal').modal('show');
	}
	function getGroupCode(val){
		var groups=JSON.parse(atob('<?= base64_encode(json_encode($groupcodes)) ?>'));
		var groups_alt=JSON.parse(atob('<?= base64_encode(json_encode(array_flip($groupcodes))) ?>'));
		if (val in groups){
			$("#id_groupcode_list").val(groups[val]);
		}else if(val in groups_alt){
			$("#id_groupcode").val(groups_alt[val]);
		}
		
	}
</script>