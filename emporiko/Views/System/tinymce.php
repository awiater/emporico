
function tinyMceEditorInit(){
tinymce.init({
	selector: 'textarea<?= $id ?>',
        convert_urls:0,
	<?= !empty($language) ? "language : '".$language."',":"" ?>
  	plugins: 'print preview paste importcss searchreplace autolink directionality save code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap <?= $tinytoolbar=='full' ? ' quickbars ' : ''  ?>emoticons',
  	menubar: false,
        
  	<?php if ($tinytoolbar=='simple') : ?>
  	toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | outdent indent<?=!empty($args) && array_key_exists('tinytoolbar_buttons',$args) && is_array($args['tinytoolbar_buttons']) ? ' | '.implode(' | ',array_keys($args['tinytoolbar_buttons'])) : '' ?>',
  	<?php elseif ($tinytoolbar=='email') : ?>
  	toolbar:' undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | numlist bullist  | currentdate | forecolor backcolor removeformat | charmap emoticons | image media link<?=!empty($args) && array_key_exists('tinytoolbar_buttons',$args) && is_array($args['tinytoolbar_buttons']) ? ' | '.implode(' | ',array_keys($args['tinytoolbar_buttons'])) : '' ?>',
  	<?php elseif ($tinytoolbar=='emailext') : ?>
  	toolbar:'  undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | numlist bullist  | currentdate | forecolor backcolor removeformat | charmap emoticons | image media link | code preview | pagemanager fontawesome<?=!empty($args) && array_key_exists('tinytoolbar_buttons',$args) && is_array($args['tinytoolbar_buttons']) ? ' | '.implode(' | ',array_keys($args['tinytoolbar_buttons'])) : '' ?>',
  	<?php elseif ($tinytoolbar=='full'):  ?>
  	toolbar:' undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist  | currentdate | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview print | insertfile image media link anchor codesample | ltr rtl | code  | pagemanager customInsertButton<?=!empty($args) && array_key_exists('tinytoolbar_buttons',$args) && is_array($args['tinytoolbar_buttons']) ? ' | '.implode(' | ',array_keys($args['tinytoolbar_buttons'])) : '' ?>',
  	<?php elseif ($tinytoolbar=='basic') : ?>
  	toolbar:'bold italic underline strikethrough | emoticons | fullscreen<?=!empty($args) && array_key_exists('tinytoolbar_buttons',$args) && is_array($args['tinytoolbar_buttons']) ? ' | '.implode(' | ',array_keys($args['tinytoolbar_buttons'])) : '' ?>',
  	<?php else : ?>
  	toolbar:'<?= $tinytoolbar ?>
  	<?php endif ?>
        images_upload_url: '<?= url('Documents','upload',['tiny','file']);//,['tiny','file'] ?>',
  	toolbar_sticky: true,
  	image_advtab: true,
	<?= !empty($height) ? "height:'".$height."'," : '' ?>
  	image_caption: true,
  	quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage',
  	noneditable_noneditable_class: "mceNonEditable",
  	toolbar_mode: 'sliding',
  	contextmenu: '',//"link image imagetools table",
  	branding: false,
  	<?php if (!empty($connector)  && !empty($toolbar) && $tinytoolbar!='simple') : ?>
  	file_picker_callback : vcmsFileEditor<?= !empty($editorid) ? '_'.$editorid : '' ?>.browser,
  	<?php endif  ?>
  	setup: function (editor) {
  		
    	editor.on('KeyUp change',function(e){
    		if (typeof tinyMceEditorChange == 'function') {
         		tinyMceEditorChange(editor.getContent());
         	}
         });
        <?php if (!empty($args) && array_key_exists('tinytoolbar_buttons',$args) && is_array($args['tinytoolbar_buttons'])) :?>
            <?php foreach ($args['tinytoolbar_buttons'] as $key=>$value) :?>
            editor.ui.registry.addButton('<?= $key ?>', {
                <?= array_key_exists('icon', $value) ? "icon: '".$value['icon']."'," : ''?>
                <?= array_key_exists('text', $value) ? "text: '".$value['text']."'," : ''?>
                <?= array_key_exists('tooltip', $value) ? "tooltip: '".$value['tooltip']."'," : ''?>
                <?= array_key_exists('enabled', $value) ? 'enabled: '.($value['enabled'] ? 'true':'false').',' : ''?>
                <?= array_key_exists('action', $value) ? 'onAction: (_) => '.$value['action'].',' : ''?>
                <?= array_key_exists('action_text', $value) ? "onAction: (_) => editor.insertContent(".$value['action_text'].")," : ''?>
            });
            <?php endforeach; ?>
        <?php endif ?> 
    	<?= !empty($setup) ? $setup : null ?>        
	}
});
}
tinyMceEditorInit();
