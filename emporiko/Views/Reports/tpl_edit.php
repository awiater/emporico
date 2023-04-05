<?= $currentView->includeView('System/form') ?>
<div id="editor_blocks"></div>
<script>
$(function(){
    $("#id_formview_submit").attr('type','button');
    $("#id_rconfig").change();
});
  var editor = grapesjs.init({
      container : '#id_rsql',
       autorender: 0,
        storageManager:false,
        fromElement: false,
        components:atob($('input[name="rsql"]').val()),
        style:atob($('input[name="rfilters"]').val())+atob('<?= base64_encode(file_get_contents($currentView->getViewData('css')['template'])) ?>'),
        
      plugins: ['gjs-preset-newsletter'],
      pluginsOpts: {
        'gjs-preset-newsletter': {
          modalTitleImport: 'Import template',
          // ... other options
        }
      }
  });
  editor.render();
 $("#id_formview_submit").on('click',function(){
    $('input[name="rfilters"]').val(btoa(editor.getCss()));
    $('input[name="rsql"]').val(btoa(editor.getHtml()));
    $('#'+$(this).attr('form')).submit();
 });
 $("#id_rconfig").on('change',function(){
     var val=$("#id_rconfig option:selected").val();
     $("#id_rcolumns_field").addClass('d-none');
     if (val==1 || val=='1'){
        $("#id_rcolumns_field").removeClass('d-none');
     }
         
 });
</script>