<?= $currentView->includeView('System/form') ?>
<div id="editor"></div>
<script>
$(function(){
    $('#id_editor').html($('#editor').detach());
    $('#edit-form').attr('id','id_dashboard_edit');
    $('#id_formview_submit').removeAttr('form').removeAttr('onclick');
});

const editor = grapesjs.init({
        container : '#editor',
        autorender: 0,
        noticeOnUnload: 0,
        storageManager:false,
        fromElement: false,
        <?php if(strlen($record['data_edit_html']) > 0 && strlen($record['data_edit_css']) > 0) :?>
        components:atob('<?= base64_encode($record['data_edit_html'])?>'),
        style:atob('<?= base64_encode($record['data_edit_css'])?>'),
        <?php endif ?>
        //plugins: [ 'grapesjs-blocks-bootstrap4' ],
        canvas: {
            styles:[
                '<?= $currentView->getViewData('css.bootstrap')?>'
            ],
            scripts:[
                '<?= $currentView->getViewData('scripts.popper.src')?>',
                '<?= $currentView->getViewData('scripts.bootstrap.src')?>',
            ],
        },
        blockManager:{
            blocks:[
                 {
                    id:'block_layouts_one',
                    label: '<i class="fas fa-columns fa-3x"></i><p class="mt-2"><?= lang('system.dashboard.block_layouts_one') ?></p>',
                    attributes: {},
                    content:<?= view('Reports/Templates/Blocks/block_table_one',['divMode'=>TRUE])?>,
                    category:'<?= lang('system.dashboard.block_layouts') ?>',
                },
                {
                    id:'block_layouts_two',
                    label: '<i class="fas fa-columns fa-3x"></i><p class="mt-2"><?= lang('system.dashboard.block_layouts_two') ?></p>',
                    attributes: {},
                    content:<?= view('Reports/Templates/Blocks/block_table_two',['divMode'=>TRUE])?>,
                    category:'<?= lang('system.dashboard.block_layouts') ?>',
                },
                {
                    id:'block_layouts_three',
                    label: '<i class="fas fa-columns fa-3x"></i><p class="mt-2"><?= lang('system.dashboard.block_layouts_three') ?></p>',
                    attributes: {},
                    content:<?= view('Reports/Templates/Blocks/block_table_three',['divMode'=>TRUE])?>,
                    category:'<?= lang('system.dashboard.block_layouts') ?>',
                },
                {
                    id:'block_tile_div',
                    label: '<i class="fas fa-grip-lines fa-3x"></i><p class="mt-2"><?= lang('system.dashboard.block_tile_div') ?></p>',
                    attributes: {},
                    content:`<div style="width:100%;height:20px"></div>`,
                    category:'<?= lang('system.dashboard.block_tiles') ?>',
                },
                <?php foreach(!empty($tiles) ? $tiles : [] as $tile) :?>
                 {
                    id:'block_<?=$tile['name']?>',
                    label: '<img style="width:60px;height:60px;" src="<?= $tile['icon']?>"/><p class="mt-2"><?= lang($tile['desc'])?></p>',
                    attributes: {},
                    content: `<div><img data-tile="<?= $tile['name']?>" style="width:100%" src="<?= $tile['icon']?>"/></div>`,
                    category:'<?= lang('system.dashboard.block_tiles') ?>',
                },
                <?php endforeach; ?>
                
            ],
        },
    });
    editor.render();
    
$("#id_formview_submit").on('click',function(){
    var html={
        'html':editor.getHtml(),
        'css':editor.getCss()
    }; 
    $('#id_data_edit_html').text(btoa(editor.getHtml()));
    $('#id_data_edit_css').text(btoa(editor.getCss()));
    addLoader('.card');
    $('#id_dashboard_edit').submit();
 });
    
</script>