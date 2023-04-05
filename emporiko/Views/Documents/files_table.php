<div class="card border" id="<?= $table_view_datatable_id; ?>_container">
    <div class="card-header d-flex p-1">
        <?php if (!empty($_tableview_card_title) && strlen($_tableview_card_title) > 0 ) :?>
        <h4><?= $_tableview_card_title ?></h4>
         <?php endif ?>
        <div class="ml-auto" id="<?= $table_view_datatable_id; ?>_toolbar_section">
            <?= $currentView->IncludeView('System/Table/table_toolbar',['_table_filters'=>$currentView->IncludeView('System/Table/table_filters')]); ?>
        </div>
    </div>
    <div class="card-body overflow-auto p-0">
        <div id="<?= $table_view_datatable_id; ?>_body_section" class="p-2">
            <?= $currentView->IncludeView('System/Table/table_body'); ?>
        </div>       
    </div>
</div>
<script>
    $(function(){
        $('#<?= $table_view_datatable_id; ?>_toolbar_nav').addClass('p-0 pr-1 pt-1 navbar-info m-0');
    });
    
    $('[data-url],[data-action]').on('click',function(){
        if ($(this).attr('data-noloader')==undefined){
            addLoader('#table_view_datatable_container');
        }
        if ($(this).attr('data-url')==undefined){
            window.location=$(this).attr('data-url');
        }
        if ($(this).attr('data-action')==undefined){
            window.location=$(this).attr('data-action');
        }
    });
</script>

