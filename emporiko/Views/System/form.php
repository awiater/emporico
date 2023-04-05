<div class="<?= $_formview_card_attr['class'] ?>" id="<?= $_formview_card_attr['id'] ?>">
<div class="card<?= !empty($_formview_card_class) ? ' '.$_formview_card_class : null ?>">
	<div class="card-header">
		<?php if(!empty($_formview_title)) : ?>
    	<h3 class="card-title"><?= $_formview_title; ?></h3>
    	<?php endif ?>
    	<?php if (!empty($_formview_custom) && $_formview_custom) : ?>
            <?= $this->renderSection('form_header') ?>
	<?php endif ?>
    </div>
    <div class="card-body">
    	<?= !empty($_form_error) && strlen($_form_error) ? $_form_error : '' ?>
    	
    	<?php if ($currentView->isTabbed() && count($_fieldstabs) > 1) :?>
    	<ul class="nav nav-tabs" id="<?= array_key_exists('tabs_id', $_formview_card_attr) ? $_formview_card_attr['tabs_id'] : 'tabs-tab' ?>" role="tablist">
    		<?php $start=TRUE ?>
    		<?php foreach ($_fieldstabs as $key => $value) :?>
    			<?php $start=(array_key_exists('tab', $_GET) ? $_GET['tab']==$key :  $start); ?>
    			<li class="nav-item">
    				<a class="nav-link <?=  $start ? 'active' :'' ?>" id="tabs-<?= $key ?>-tab" data-toggle="pill" href="#tabs-<?= $key ?>" role="tab" aria-controls="tabs-<?= $key ?>" aria-selected="true">
   						<?= lang($value) ?>
   					</a>
    			</li>
    			<?php $start=FALSE ?>	
    		<?php endforeach ?>
    	</ul>		
    	<?php endif ?>
    	<?php if (!empty($_formview_action)) :?>
    	<?= form_open_multipart(empty($_formview_action) ? '': $_formview_action,empty($_formview_action_attr) ? []: $_formview_action_attr,empty($_formview_action_hidden) ? []: $_formview_action_hidden); ?>
    	<?php endif ?>
        <?php if(!empty($_formview_validation)) : ?>
    		<?= $_formview_validation->listErrors() ?>
    	<?php endif ?>

    	<?php if (empty($_formview_custom) || (!empty($_formview_custom) && !$_formview_custom)) : ?>
    			<?php if ($currentView->isTabbed()) :?>
    				<div class="tab-content" id="<?= array_key_exists('tabs_id', $_formview_card_attr) ? $_formview_card_attr['tabs_id'] : 'tabs-tab' ?>Content">
    					<?php $start=TRUE ?>
    					<?php foreach ($_fieldstabs as $key => $value) :?>
    					<?php $start=(array_key_exists('tab', $_GET) ? $_GET['tab']==$key :  $start); ?>
    					<div class="tab-pane fade <?= $start ? 'show active' :'' ?> <?= count($_fieldstabs) > 1 ? 'border border-top-0' : '' ?> p-2" id="tabs-<?= $key ?>" role="tabpanel" aria-labelledby="tabs-<?= $key ?>-tab">
    						<?= $currentView->includeView('System/form_fields',['fields'=>array_key_exists($key, $fields) ? $fields[$key] : []]); ?>
    					</div>
    					<?php $start=FALSE ?>	
    					<?php endforeach ?>
    				</div>
    			<?php else :?>	
				<?= $currentView->includeView('System/form_fields',['fields'=>$fields]); ?>
				<?php endif ?>
		<?php else : ?>
			<?= $this->renderSection('form_body') ?>
		<?php endif ?>
        <?php if (!empty($_formview_action)) :?>
        <?= form_close(); ?>
        <?php endif ?>
    </div>
    <div class="card-footer d-flex">
        <div class="<?= $currentView->isMobile() ? 'w-100' : 'ml-auto' ?>">
        	<?php if (!empty($_formview_action) && !empty($_formview_savebtn) && is_array($_formview_savebtn)) : ?>
        	<button type="button" onclick="submit<?= str_replace([' ','-'], '', $_formview_action_attr['id']) ?>Form()" class="<?= $_formview_savebtn['class'] ?><?= $currentView->isMobile() ? ' mb-2 w-100 btn-lg"' : '"'?> id="<?= $_formview_savebtn['id'] ?>">
            	<?php if (!empty($_formview_custom_save) && is_array($_formview_custom_save)) :?>
              		<i class="<?= $_formview_custom_save[0]; ?>"></i><?= $_formview_custom_save[1]; ?>
              	<?php else :?>
              		<i class="<?= $_formview_savebtn['icon'] ?> mr-1"></i><?= lang($_formview_savebtn['text']); ?>
              	<?php endif ?>
            </button>
            <?php endif ?>
            <?php if(!empty($_formview_urlcancel)) :?>
            <a class="btn btn-outline-danger<?= $currentView->isMobile() ? ' btn-lg w-100' : null ?>" href="<?= !empty($_formview_urlcancel) ? $_formview_urlcancel: '#'  ?>" id="id_formview_cancel" data-loader="true">
              	<i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel'); ?>
            </a>
            <?php endif ?>
          </div>
	</div>
</div>
</div>
<?= $this->renderSection('form_html') ?>
<script>
    $(function(){
        <?php if ($currentView->isTabbed() && count($_fieldstabs) > 1) :?>
        $('a[data-toggle="pill"]').on('dblclick',function(){
                    var id=$(this).attr('id');
                    id=id.replace('tabs-','').replace('-tab','');
                    requestSetGet('tab',id);
                    location.reload();
         });
         $('button[form="<?= $_formview_action_attr['id'] ?>"]').attr('type','button');
         
         $('button[form="<?= $_formview_action_attr['id'] ?>"]').on('click',function(){
             var submit=true;
             var fnc=$(this).attr('form')+'_submit';
             fnc=replaceAll(fnc,'-','_');
             if (typeof window[fnc]==='function'){
                window[fnc]();
             }else{
             $('[required]').each(function(){
                 if ($(this).val().length < 1){
                     var label='<?= lang('system.errors.required_notset')?>';
                     var id=$(this).attr('id');
                     id=id.replaceAll('_value','');
                     label=label.replaceAll('{0}',$('label[for="'+id+'"]').text());
                     label=label.replaceAll('*','');
                     Dialog(label,'warning');
                     submit=false;
                     return false;
                 }
             });
             if (submit){
                $('#'+$(this).attr('form')).submit();
             }
             }
         });
         $("[group],[data-group]").each(function(){
             var group=$(this).attr('group');
             group=group==undefined  ? $(this).attr('data-group') : group;
             var groupdiv=$('#'+group+'_group').attr('class');
             var size=4;
             if($('[group="'+group+'"],[data-group="'+group+'"]').length>3){
                 size=3;
             }
             if (groupdiv==undefined){
               $('#'+$(this).attr('id')+'_field').wrap('<div class="row" id="'+group+'_group">');
               $('#'+$(this).attr('id')+'_field').wrap('<div class="col-'+size+'">');
               $(this).removeAttr('group').removeAttr('data-group');
             }else{
                $('#'+$(this).attr('id')+'_field').detach().appendTo('#'+group+'_group');
                $(this).removeAttr('group').removeAttr('data-group');
                $('#'+$(this).attr('id')+'_field').wrap('<div class="col-'+size+'">');
             }
             
            
         });
         <?php endif ?>
         <?= $this->renderSection('form_script_afterload') ?>
    });
 
 <?= $currentView->includeView('System/form_validation',['_formvalidationNoScript'=>TRUE]) ?>

function submit<?= str_replace([' ','-'], '', $_formview_action_attr['id']) ?>Form(){
    <?php if(!empty($_formvalidation)) :?>
        <?= str_replace([' ','-'], '', $_formview_action_attr['id']) ?>Validate(function(){
           addLoader('#<?= $_formview_card_attr['id'] ?>');
           $('#<?= $_formview_action_attr['id'] ?>').submit(); 
        });
    <?php else :?>
    addLoader('#<?= $_formview_card_attr['id'] ?>');
    $('#<?= $_formview_action_attr['id'] ?>').submit();
    <?php endif ?>
}
<?= $this->renderSection('form_script') ?>
</script>


