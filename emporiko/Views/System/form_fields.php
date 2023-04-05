<?php foreach ($fields as $field):?>
	<?php if(is_string($field)) :?>
		<?= $field?>
	<?php elseif ($field['label']=='@hidden'): ?>
		<?= $field['value']; ?>
	<?php else : ?>
		<div class="form-group<?= !empty($_form_fields_group_class) ? ' '.$_form_fields_group_class : null ?><?= ((array_key_exists('type', $field['args']) &&  $field['args']['type']=='hidden') || (array_key_exists('data-mode', $field['args']) &&  $field['args']['data-mode']=='hidden')) ? ' d-none' : null ?>" id="<?= array_key_exists('id', $field['args']) ? $field['args']['id'].'_field' : 'id_'.$field['name'].'_field'  ?>">
    		<?php if (!empty($_formview_action_attr) && is_array($_formview_action_attr) && array_key_exists('inline', $_formview_action_attr) && $_formview_action_attr['inline']) :?>
                <div class="row">
                    <div class="col-xs-12 col-md-4">
                <?php endif ?>
                <?php if (!empty($field['args']['data-optional']) && $field['args']['data-optional']) :?>        
                <?php $field['args']['data-optional']=array_key_exists('id', $field['args']) ? $field['args']['id'].'_field_collapse' : 'id_'.$field['name'].'_field_collapse'  ?>
                <?php else :?>
                <?php $field['args']['data-optional']=null ?>        
                <?php endif ?>        
                <?php if (is_string($field['label']) && $field['label']!=null && $field['label']!='null') :?>
    		<label for="<?= array_key_exists('id', $field['args']) ? $field['args']['id'] : 'id_'.$field['name']  ?>" class="mr-2"<?=!empty($field['args']['data-optional']) ? ' data-toggle="collapse" href="#'.$field['args']['data-optional'].'" role="button" aria-expanded="false" aria-controls="'.$field['args']['data-optional'].'"':''?>>
    			<?php if (!empty($field['args']['data-optional'])):?>
                        <i class="fas fa-chevron-circle-down mr-1 ml-1"></i>
                        <?php endif ?>
                        <?= lang($field['label']) ?>
    			<?php if (array_key_exists('required', $field['args'])) : ?>
    			<b class="text-danger">*</b>
    			<?php endif ?>
    		</label>
                <?php else :?>
                        <?php if (!empty($field['args']['data-optional'])):?>
                        <i class="fas fa-chevron-circle-down mr-1 ml-1"></i>
                        <?php endif ?>
    		<?php endif ?>
                <?php if (!empty($_formview_action_attr) && is_array($_formview_action_attr) && array_key_exists('inline', $_formview_action_attr) && $_formview_action_attr['inline']) :?>
                    </div><div class="col-xs-12 col-md-8">
                <?php endif ?>
                        <?php if (!empty($field['args']['data-optional'])):?>
                        <div class="collapse" id="<?= $field['args']['data-optional']?>">
                        <?php endif ?>
   	 		<?= $field['value'] ?>
    		<small id="<?= array_key_exists('id', $field['args']) ? $field['args']['id'].'_tooltip' : 'id_'.$field['name'].'_tooltip'  ?>" class="form-text text-muted">
    			<?php $_tooltip=array_key_exists('tooltip', $field['args']) ? lang($field['args']['tooltip']) : lang($field['label'].'_tooltip'); ?>
    			<?= $_tooltip==$field['label'].'_tooltip' ? '' : $_tooltip ?>
    		</small>
                        <?php if (!empty($field['args']['data-optional'])):?>
                        </div>    
                        <?php endif ?>
                <?php if (!empty($_formview_action_attr) && is_array($_formview_action_attr) && array_key_exists('inline', $_formview_action_attr) && $_formview_action_attr['inline']) :?>
                    </div></div>
                <?php endif ?>
 		</div>
 	<?php endif ?>
<?php endforeach;?>