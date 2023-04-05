<?= $currentView->includeView('System/form') ?>
<div class="form-group d-none" id="id_prf_file_upload_field">
    <div class="form-control" id="id_prf_file_upload_text"></div>
    <label for="id_prf_file_upload" id="id_prf_file_upload_btn" class="d-none">Upload</label>
    <input type="file" id="id_prf_file_upload" name="prf_file_upload" class="d-none">
</div>
<div class="input-group" id="id_ppf_source_calcmode_group">
    <div class="input-group-append">
        <input type="number" class="form-control ml-2 d-none" value="0" name="ppf_source_calcmode" onkeydown="return false" >
    </div>
</div>
<?php if (array_key_exists('ppf_pricingmode', $record) &&  $record['ppf_pricingmode']=='db' && array_key_exists('ppfid', $record) && is_numeric($record['ppfid'])):?>
<button class="btn btn-dark" id="btn_form_edit_upload" data-url="<?= $import_url?>" data-toggle="tooltip" data-placement="right" title="<?= lang('products.btn_importfiles')?>">
    <i class="fas fa-cloud-upload-alt mr-1"></i><?= lang('system.buttons.upload')?>
</button>
<?php endif ?>  
<script>
    $(function(){
        $('#id_prf_file_field').after($('#id_prf_file_upload_field').detach());
        $('.select2').on('select2:select',function(e){
            var val=$(this).find(':selected').val();
            $('#id_prf_file_upload_field').addClass('d-none');
            $('#id_prf_file_upload').val('');
            if (val.length==0){
                $("#id_prf_file_upload_btn").trigger('click');
                $('#id_prf_file').removeAttr('required');
            }
        });
        <?php if (array_key_exists('ppf_pricingmode', $record) &&  $record['ppf_pricingmode']=='db') :?>
                <?php if (array_key_exists('ppfid', $record) && is_numeric($record['ppfid'])) :?>
                $('#id_formview_submit').parent().before($('#btn_form_edit_upload').detach());
                <?php endif ?>
        <?php else :?>        
        $('#id_ppf_source_calcmode_group').find('.input-group-append').before($('#id_ppf_source_calcmode_list').detach());
        $('#id_ppf_source_calcmode_list_field').find('label[for="id_ppf_source_calcmode_list"]').after($('#id_ppf_source_calcmode_group').detach());
        
        $('#id_ppf_source_calcmode_list').on('change',function(){
            var val=$(this).find('option:selected').val();
            if (val=='+'){
                $('input[name="ppf_source_calcmode"]').removeClass('d-none').val('<?= array_key_exists('ppf_source_calcmode',$record) ?  $record['ppf_source_calcmode']: '1' ?>').attr('min',1).attr('max',1000);
            }else if(val=='-'){
                $('input[name="ppf_source_calcmode"]').removeClass('d-none').val('<?= array_key_exists('ppf_source_calcmode',$record) ?  $record['ppf_source_calcmode']: '-1' ?>').attr('min','-1000').attr('max','-1');
            }else{
                $('input[name="ppf_source_calcmode"]').addClass('d-none').attr('min','0').attr('max','0').val(0);
            }
        });
        
        $('#id_ppf_source_calcmode_list').trigger('change');
        <?php endif ?>
        $('#id_formview_submit').removeAttr('onclick');
        $('#id_formview_submit').on('click',function(){
            if ($('input[name^="ppf_source_brands["]').length==0){
                $('#id_ppf_source_brands_field').find('.input-group').addClass('border border-danger');
                Dialog('<?= lang('products.error_brands_list_empty')?>','warning');
            }else{
                $('#id_ppf_source_brands_field').find('.input-group').removeClass('border border-danger');
                var submit=false;
                $('#edit-form').find('[required]').each(function(){
                    $(this).removeClass('border border-danger');
                    if ($(this).val().length < 1){
                        $(this).addClass('border border-danger');
                        $('#tabs-'+$(this).attr('tab_name')+'-tab').tab('show');
                        submit=false;
                        Dialog('<?= lang('system.general.msg_validation_error') ?>','warning');exit;
                    }else{
                        submit=true;
                    }
                });
                if (submit){
                    $('#edit-form').submit();
                }
            }
        });
        
        $('#id_prf_file_upload').on('change',function(){
            $('#id_prf_file_upload_text').text($(this).val());
            $('#id_prf_file_upload_field').removeClass('d-none');
        });
    });
</script>