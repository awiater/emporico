<?php if (!empty($_tableview_datatable) && is_array($_tableview_datatable)) :?>
<?php $_tableview_datatable_onlyicons=array_key_exists('onlyicons', $_tableview_datatable) ? $_tableview_datatable['onlyicons'] : false; ?>
	var table_view_datatable=null;
	$(function(){		
		table_view_datatable=$('#<?= $table_view_datatable_id ?>').DataTable({
			<?php foreach ($_tableview_datatable as $key => $value) :?>
				<?= $key ?>:<?=$value?>,
			<?php endforeach ?>
			<?php if (!empty($_tableview_datatable_filter)) :?>
			'search':{'search':"<?= base64_decode($_tableview_datatable_filter) ?>"},
			<?php endif ?>
			
			buttons: {
    			dom: {
      				button: {
        						tag: 'button',
       							className: ''
     				 		}
    				},
			buttons:[
						<?php foreach($_tableview_btns_routes as $key=>$value) :?>
						<?php if ($key=='id_tableview_btn_enable'):?>
						 { 
						 	className: 'btn btn-success btn-sm mb-2',
						 	text:'<i class="fa fa-eye mr-1"></i><?= $_tableview_datatable_onlyicons ? '' : lang('system.buttons.enable') ?>',
						 	action: function ( e, dt, node, config ) {
						 		$('#id_tableview_form').attr('action', '<?= $value['route'] ?>').submit();
            				} 
						 },
						 <?php elseif ($key=='id_tableview_btn_disable'):?>
						 { 
						 	className: 'btn btn-warning btn-sm mb-2',
						 	text:'<i class="fa fa-eye-slash mr-1"></i><?= $_tableview_datatable_onlyicons ? '' : lang('system.buttons.enable_no') ?>',
						 	action: function ( e, dt, node, config ) {
						 		$('#id_tableview_form').attr('action', '<?= $value['route'] ?>').submit();
            				} 
						 },
						 <?php elseif ($key=='id_tableview_btn_del'):?>
						 { 
						 	className: 'btn btn-danger btn-sm mb-2',
						 	text:'<i class="fa fa-trash mr-1"></i><?= $_tableview_datatable_onlyicons ? '' : lang('system.buttons.remove') ?>',
						 	action: function ( e, dt, node, config ) {
						 		ConfirmDialog('<?= lang('system.general.msg_delete_ques')?>',function(){
									$('#id_tableview_form').attr('action', '<?= $value['route'] ?>').submit();
								});
            				} 
						 },
						 <?php elseif ($key=='id_tableview_btn_new'):?>
						 { 
						 	<?php if (array_key_exists('dropdownitems', $value) && is_array($value['dropdownitems'])) :?>
						 	extend: 'collection',
						 	<?php endif ?>
						 	className: 'btn btn-primary btn-sm mb-2 <?= $key ?>',
						 	text:'<i class="fa fa-plus mr-1"></i><?= $_tableview_datatable_onlyicons ? '' : lang('system.buttons.new') ?>',
						 	<?php if (array_key_exists('dropdownitems', $value) && is_array($value['dropdownitems'])) :?>
						 	buttons:[
						 		<?php foreach ($value['dropdownitems'] as $item) :?>
						 		{
						 		 text: '<?= $item['text']?>',
						 		 className:'btn btn-link w-100',
						 		 action: function ( e, dt, node, config ) {
						 		 	window.location='<?= $item['href']?>';	
						 		 }
						 		},
						 		<?php endforeach ?>
						 	]	
						 	<?php else :?>
						 	action: function ( e, dt, node, config ) {
						 		<?php if (array_key_exists('route', $value)) :?>
						 		window.location='<?= $value['route']?>';
						 		<?php endif ?>
            				} 
            				<?php endif ?>
						 },
						 <?php else :?>
						 {
						 	className: 'mb-2 <?= $value['class'] ?>',
						 	text:'<?= $value['content'] ?>',
						 	<?php if($key=='id_tableview_btn_csv') :?>
						 	extend:'csv',
						 	filename:'<?= (!empty($_tableview_card_title) && strlen($_tableview_card_title) > 0 ) ? $_tableview_card_title : '' ?>',
						 	<?php elseif($key=='id_tableview_btn_pdf') :?>
						 	extend:'pdf',
						 	title:'<?= (!empty($_tableview_card_title) && strlen($_tableview_card_title) > 0 ) ? $_tableview_card_title : '' ?>',
						 	<?php elseif($key=='id_tableview_btn_print') :?>
						 	extend:'print',
						 	title:'<?= (!empty($_tableview_card_title) && strlen($_tableview_card_title) > 0 ) ? $_tableview_card_title : '' ?>',	
						 	<?php else :?>
						 	action: function ( e, dt, node, config ) {
						 		
						 		<?php if(strlen($value['route']) > 0) :?>
						 			<?php if(\EMPORIKO\Helpers\Strings::contains($value['class'],'tableview_def_btns')) :?>
						 				$('#id_tableview_form').attr('action', '<?= $value['route'] ?>').submit();
						 			<?php else :?>
						 				window.location='<?= $value['route'] ?>';
						 			<?php endif ?>
						 		<?php endif ?>
            				}
            				<?php endif ?>
            			},
						 <?php endif ?>
						 <?php endforeach ?>
					]
				}
		});
		
		//$(".dt-button").removeClass('dt-button');
		$("#id_tableview_search_form").attr('method','get');
		$("#id_tableview_search_form input[name='<?= config('Security')->tokenName?>']").remove();
		$("#table_view_datatable_filter").html('');
		$("#id_tableview_search_form").detach().appendTo("#table_view_datatable_filter");
		
		
	});
<?php endif ?>