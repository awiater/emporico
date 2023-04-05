<?= $currentView->includeView('System/form') ?>
<div id="editor_blocks"></div>
<script>
$(function(){
    $("#id_formview_submit").attr('type','button');
    $("#id_rconfig").change();
});
const reportFieldsPlugin = editor => {
  editor.DomComponents.addType('field',{
      isComponent: el => el.tagName === 'FIELD',
      model:{
          defaults:{
              tagName: 'field',
              droppable: false,
              editable :true,
              attributes:{
                  field:'text',
                  formatin:'DB',
                  formatout:'d M Y',
                  indata:'no',
              },
              traits:[
                  {
                      name:'field',
                      type:'select',
                      label:'Field Type',
                      options:[
                          {id:'text',name:'Text'},
                          {id:'date',name:'Date'},
                          {id:'lang',name:'Language Tag'},
                      ],
                  },
                  {
                      name:'formatin',
                      type:'text',
                      label:'Date Format In',  
                  },
                  {
                      name:'formatout',
                      type:'text',
                      label:'Date Format Out',  
                  },
                  {
                      name:'indata',
                      type:'select',
                      label:'In Data Container',
                      options:[
                          {id:'yes',name:'Yes'},
                          {id:'no',name:'No'},
                      ],
                  },
              ],
          },
      },
      view:{
          events:{
            dblclick: 'onActive',
            focusout: 'onDisable',
          },
          onActive(){
              this.el.contentEditable = true;
          },
          onDisable(){
              const { el, model } = this;
              el.contentEditable = false;
              model.set('content', el.innerHTML)
          },
      },
  });
};
const editor = grapesjs.init({
        container : '#id_rsql',
        autorender: 0,
        storageManager:false,
        fromElement: false,
        components:atob($('input[name="rsql"]').val()),
        style:atob($('input[name="rfilters"]').val())+atob('<?= base64_encode(file_get_contents($currentView->getViewData('css')['template'])) ?>'),
        plugins: [ reportFieldsPlugin ],
        blockManager:{
            blocks:[
                <?php foreach($layouts_blocks as $block) :?>
                 {
                    id:'block_<?=$block['name']?>',
                    label: '<i class="<?= $block['icon']!=null && strlen($block['icon']) > 0 ? $block['icon'] : $blocks_images['layouts']?> fa-3x"></i><p class="mt-2"><?=$block['title']?></p>',
                    attributes: {},
                    content: atob('<?= base64_encode($block['content'])?>'),
                    category:'<?= lang('system.reports.tpl_editor_tplblockscat') ?>',
                },
                <?php endforeach; ?>
                
                <?php foreach($blocks['Layouts/'] as $key=>$block) :?>
                 {
                    <?php $block=str_replace('.php', '', $block); ?>
                    id:'block_layouts_<?=$key?>',
                    label: '<i class="<?=array_key_exists($block, $blocks_images) ? $blocks_images[$block] :$blocks_images['layouts'] ?> fa-3x"></i><p class="mt-2"><?= lang('system.reports.tpl_'.$block) ?></p>',
                    attributes: {},
                    content: <?= view('Reports/Templates/Layouts/'.$block)?>,
                    category:'<?= lang('system.reports.tpl_editor_tplblockscat') ?>',
                },
                <?php endforeach; ?>
                
                <?php foreach($blocks['Blocks/'] as $key=>$block) :?>
                 {
                    <?php $name=str_replace(['.php','.json'], '', $block); ?>
                    id:'block_<?=$key?>',
                    label: '<i class="<?= array_key_exists($name, $blocks_images) ? $blocks_images[$name] : $blocks_images['block']?> fa-3x"></i><p class="mt-2"><?= lang('system.reports.tpl_'.$name) ?></p>',
                    attributes: {},
                    content: <?= (view('Reports/Templates/Blocks/'.$block)) ?>,
                    category:'<?= lang('system.reports.tpl_editor_blockscat') ?>',
                },
                <?php endforeach; ?>
                
                <?php foreach($replacements as $grpname=>$group) :?>
                    <?php foreach($group as $key=>$block) :?>
                    {
                    id:'block_model_<?=$block?>',
                    label:'<?= lang($key) ?><br>(<?=$block?>)',
                    content:{
                        type:'field',
                        style:{display:'inline-block'},
                        attributes:{'field':'text'},
                        content:'{$<?= $block?>}'
                    },
                    category:'<?= lang('system.reports.tpl_editor_modelblockscat',[$grpname]) ?>'
                    },
                    <?php endforeach; ?>
                <?php endforeach; ?>
            ],
        },
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