<?= $currentView->includeView('System/form') ?>
<?php if ((!empty($record['can_convert']) && $record['can_convert']) || (array_key_exists('_email', $record) && is_array($record['_email']))) :?>
<div id="form_toolbar">
    <?php if ((!empty($record['can_convert']) && $record['can_convert'])) :?>
    <button class="btn bg-warning mr-3" data-toggle="tooltip" data-placement="right" title="<?= lang('orders.opportunities_msg_convert') ?>" id="form_toolbar_convert">
        <i class="fas fa-retweet"></i>  
    </button>
    <?php endif ?>
    <?php if (array_key_exists('_email', $record) && is_array($record['_email'])) :?>
    <button class="btn bg-info" data-toggle="tooltip" data-placement="right" title="<?= lang('orders.opportunities_msg_convertemail_src') ?>" id="form_toolbar_convert" onclick="$('#opport_emailviewer').modal('show');">
        <i class="fas fa-envelope-open-text"></i>
    </button>
    <div class="modal" tabindex="-1" role="dialog" id="opport_emailviewer">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('orders.opportunities_msg_convertemail_src') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= $currentView->includeView('Emails/view',['record'=>$record['_email'],'_external_call'=>TRUE]); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?= lang('system.buttons.close') ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php endif ?>
</div>
<?php endif ?>
<?php if (!array_key_exists('ordid', $record) || (array_key_exists('ordid', $record) && !is_numeric($record['ordid']))) :?>
<div class="modal" tabindex="-1" role="dialog" id="newitemmodal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('orders.newitemmodal_title') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?=lang('system.buttons.close')?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="newitemmodal_part">
                        <?= lang('products.prd_apdpartnumber') ?>
                    </label>
    		    <select id="newitemmodal_part" class="form-control">
                        <option></option>
                    </select>
                    <input type="hidden" name="prd_brand">
                    <input type="hidden" name="prd_description">
                    <input type="hidden" name="prd_tecdocpart">
                    <input type="hidden" name="prd_data">
                </div>
                <div class="form-group row">
                    <div class="col-xs-12 col-md-6">
                        <label for="newitemmodal_qty">
                            <?= lang('orders.opportunities_qty') ?>
                        </label>
                        <input type="number" class="form-control form-control-sm" dir="rtl" min="1" max="100000" id="newitemmodal_qty">
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <label for="newitemmodal_value">
                            <?= lang('orders.opportunities_value') ?>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-money-bill-alt"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control form-control-sm" dir="rtl" data-mask-reverse="true" data-mask="00000000.00" id="newitemmodal_value">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger mr-auto btn-sm" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel') ?>
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="newitemmodal_addbtn">
                    <i class="fas fa-plus mr-1"></i><?= lang('system.buttons.add') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<button type="button" class="btn btn-primary btn-sm" onclick="$('#newitemmodal').modal('show')" data-toggle="tooltip" data-placement="top" title="<?= lang('orders.newitemmodal_title') ?>" id="newitemmodal_showbtn">
  <i class="fas fa-plus"></i>
</button>
<?php endif ?>
<script>
$(function(){
    $('#id_formview_submit').parent().before($('#form_toolbar').detach());
    <?php if (!empty($record['can_convert']) && $record['can_convert']) :?>
    $('#form_toolbar_convert').on('click',function(){
             ConfirmDialog('<?= lang('orders.opportunities_msg_convert')?>',function(){
                addLoader();
                window.location='<?= $converturl?>';
             });  
    });
    <?php endif ?>
    $('#id_ord_ref').on('change',function(){
        var val=$(this).val();
        addLoader('#id_ord_ref_field');
        ajaxCall('<?= api_url('sales','checkref') ?>',{'ref':val},
        function(data){
            if ('error' in data){
                $('#id_ord_ref_tooltip').append('<p class="text-danger" id="id_ord_ref_tooltip_error">'+data['error']+'</p>');
                $('#id_ord_ref').addClass('border-danger');
                killLoader();
            }else
            {
                $('#id_ord_ref_tooltip_error').remove();
                $('#id_ord_ref').removeClass('border-danger');
                killLoader();
            }
        },
        function(data){
            console.log(data);
        });
    });
    
        $('th[data-action="true"]').html($('#newitemmodal_showbtn').detach());
        $('#newitemmodal_part').select2({
        ajax:{
            url: "<?= api_url('products','findpartforfield')?>",
            dataType: 'json',
            delay: 250,
            data:function(params){
                return {
                    part: params.term, // search term
                    page: params.page,
                    acc:$('#id_ord_cusacc').find('option:selected').val()
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                var results = [];
                $.each(data, function(k, v) {
                    results.push({
                        id: v.prd_apdpartnumber,
                        text: v.prd_description,
                        description_txt: v.prd_description,
                        raw:v
                    });
                });
                return {
                    results: results,
                    pagination:{
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true        
        },
        placeholder: '<?=lang('products.prd_tecdocpart') ?>',
        minimumInputLength: 3,
        dropdownAutoWidth:true,
        templateResult: function(result){
            if (!result.id){
                return $('<span><?= lang('products.msg_partpicker_search') ?></span>');
            }
            return $('<span>'
                    +'<div>'+result.id+'</div><small>'
                    +'<div>'+result.text+'</div>'
                    +'</small></span>');
        },
        templateSelection: function(result){
            $('#newitemmodal_part').attr('data-value',btoa(JSON.stringify(result.raw)));
             if (result.id){
            $('input[name="prd_brand"').val(result.raw.prd_brand);
            $('input[name="prd_description"').val(result.raw.prd_description);
            $('input[name="prd_tecdocpart"').val(result.raw.prd_tecdocpart);
            $('input[name="prd_data"').val(btoa(JSON.stringify(result.raw)));
            $('#newitemmodal_value').val(result.raw.prd_price);
             }
            //$('#select2-newitemmodal_part-container').removeClass('text-muted').parent().parent().parent().attr('style','min-width:150px');
            return result.id;
        }
    });
    
    $('#newitemmodal_addbtn').on('click',function(){
        
        id_parts_addnewrow({
            'prd_brand':$('input[name="prd_brand"').val(),
            'prd_description':$('input[name="prd_description"').val(),
            'qty':$('#newitemmodal_qty').val(),
            'value':$('#newitemmodal_value').val(),
            'prd_apdpartnumber':$('#newitemmodal_part').find('option:selected').val()
        },{'data':$('input[name="prd_data"').val()});
        
        $('#newitemmodal_qty, #newitemmodal_value').val(''),
        $('#select2-newitemmodal_part-container').text('');
        $('#newitemmodal').modal('hide');
    });
});

</script>