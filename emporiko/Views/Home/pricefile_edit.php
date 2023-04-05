<?= $currentView->includeView('System/form') ?>
<div class="form-group d-none" id="id_prf_file_upload_field">
    <div class="form-control" id="id_prf_file_upload_text"></div>
    <label for="id_prf_file_upload" id="id_prf_file_upload_btn" class="d-none">Upload</label>
    <input type="file" id="id_prf_file_upload" name="prf_file_upload" class="d-none">
</div>
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
        $('#id_prf_file_upload').on('change',function(){
            $('#id_prf_file_upload_text').text($(this).val());
            $('#id_prf_file_upload_field').removeClass('d-none');
        });
    });
</script>