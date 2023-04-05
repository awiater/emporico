<?= $currentView->includeView('System/form') ?>
<div class="form-group" id="spreadsheet_container">
    <label for="spreadsheet" class="w-100"><?= lang('orders.parts_grid')?></label>
    <small class="form-text text-muted"><?= lang('orders.parts_grid_tooltip')?></small>
    <div id="spreadsheet" class="transparent_scroll_y" style="height:400px;"></div>
    <input name="parts_data" type="hidden">
</div>
<script>
    $(function(){
        $('#tabs-general').append($('#spreadsheet_container').detach());
        $('#id_formview_submit').removeAttr('onclick');
        const mySpreadsheet = jspreadsheet(document.getElementById('spreadsheet'),{
            contextMenu: false,
            data:[['','']],
            columns:[
                {title:'Part', width:'300'},
                {title:'Qty', width:'100'}
            ], 
        });
        
        $('#id_formview_submit').on('click',function(){
            addLoader('#form_container');
            $('[name="parts_data"]').val(JSON.stringify(mySpreadsheet.getJson()));
            $('#edit-form').submit();
        });
    });
    
</script>