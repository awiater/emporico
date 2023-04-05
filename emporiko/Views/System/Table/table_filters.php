<?php if(!empty($_tableview_filters)) : ?>
<?php if($currentView->isMobile()) :?>
<div class="btn-group dropleft">
    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-filter"></i>
    </button>
    <div class="dropdown-menu"<?= $currentView->isMobile() ? ' style="min-width:250px"': ''?>>
<?php endif ?> 
<?= form_open(!empty($_tableview_filters_url) ? $_tableview_filters_url : '',['id'=>$table_view_datatable_id.'_search_form','method'=>'get','class'=>!$currentView->isMobile() ? 'form-inline' : 'p-2'],['filtered'=>'']) ?>
    <div class="input-group"> 
    <?php if(!empty($_tableview_filters_fields) && is_array($_tableview_filters_fields) && count($_tableview_filters_fields) >0) :?>
        <?= implode('',$_tableview_filters_fields) ?>
    <?php else :?>
        <input type="text" class="form-control form-control-sm" placeholder="Filter" id="<?= $table_view_datatable_id; ?>_search_form_filter_value" value="<?= array_key_exists('filter', $_GET) ? $_GET['filter'] : '' ?>">
        <input type="hidden" id="<?= $table_view_datatable_id; ?>_search_form_filter" name="filter">
    <?php endif ?>
    <?php if (!empty($_GET['refurl'])) :?>
        <input type="hidden" name="refurl" value="<?= $_GET['refurl'] ?>">
    <?php endif ?>   
    <div class="input-group-append" id="id_tableview_search_form_buttons">
        <?php if (!$currentView->isMobile() && !empty($_tableview_filters_fixed) && is_array($_tableview_filters_fixed) && count($_tableview_filters_fixed)>0) : ?>
        <div class="btn-group">
            <button type="button" class="btn btn-secondary btn-sm" onclick="tableviewSearchFilterGo()" id="id_tableview_search_form_submit">
                <i class="fas fa-filter"></i>
            </button>
            <button class="btn btn-secondary btn-sm dropdown-toggle dropdown-toggle-split" type="button" id="id_tableview_filter_enabled" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            </button>
            <div class="dropdown-menu" aria-labelledby="id_tableview_filter_enabled" id="id_tableview_search_form_filterbuttons">
                <?php foreach($_tableview_filters_fixed as $label=>$filter) : ?>
                    <?php if (substr($filter,0,1)=='#') :?>
                        <?php if (strlen(substr($filter,1)) > 0) :?>
                        <div class="font-weight-bold p-1"><?= substr($filter,1)?></div>
                        <?php else :?>
                         <div class="dropdown-divider"></div>
                        <?php endif ?>
                    <?php else :?>
                    <button type="button" class="dropdown-item btn btn-link" onclick="tableviewSearchFilterGo('<?= $filter ?>')" >
                        <?= lang($label); ?>
                    </button>
                    <?php endif ?>
                <?php endforeach ?>	
            </div>	
        </div>
        <?php else : ?>
        <button type="button" class="btn btn-secondary btn-sm<?= $currentView->isMobile() ? ' w-100' : ''?>" onclick="tableviewSearchFilterGo()" id="id_tableview_search_form_submit">
            <i class="fas fa-filter"></i>
        </button>
        <?php endif ?>
    </div>
    </div>
<?= form_close() ?>
<?php if($currentView->isMobile()) :?>        
    </div>
</div>
<script>
    $(function(){
        $(".dropdown-menu").click(function(e){
            e.stopPropagation();
        });
    });
</script>
<?php endif ?>    
<?php endif; ?>
