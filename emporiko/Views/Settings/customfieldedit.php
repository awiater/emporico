<?= $currentView->includeView('System/form') ?>
<script>
    $(function(){
       $("#id_type").trigger('change');
       $("#id_target_list").trigger('change');
    });
    $("#id_type").on('change',function(){
        var val=$("#id_type option:selected").val();
        $('#id_options_field').addClass('d-none');
        if (val=='DropDown'){
            $('#id_options_field').removeClass('d-none');
        }
    });
    $("#id_target_list").on('change',function(){
        var val=$("#id_target_list option:selected").val();
        val=JSON.parse(atob(val));
        $('#id_target').val(val['type']);
        $('#id_tab').html('');
        $.each(val['tabs'], function( index, value ){
           $('#id_tab').append('<option value="'+index+'">'+value+'</option>'); 
        });
    });
</script>
    