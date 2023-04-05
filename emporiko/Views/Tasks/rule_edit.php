<?= $currentView->includeView('System/form') ?>
<script>
$(function(){
    $('#id_rulaction').trigger('change');
});
$('#id_rulaction').on('change',function(){
    var val=$(this).find(':selected').val();
    
    $('#action_args_theme_label_user, #action_args_email_label, #action_args_theme_label, #action_args_command_label').addClass('d-none');
    $('#action_args_user, #action_args_email, #action_args_user_group, #action_args_theme, #action_args_command, .select2').addClass('d-none').attr('disabled','TRUE');
    if (val=='send_email'){
        $('#action_args_email_label, #action_args_theme_label').removeClass('d-none');
        $('#action_args_email, #action_args_theme, .select2').removeClass('d-none').removeAttr('disabled');
    }else
    if (val=='notify_cust'){
        $('#action_args_theme_label').removeClass('d-none');
        $('#action_args_theme, .select2').removeClass('d-none').removeAttr('disabled');
    }else
    if (val=='notify_user'){
        $('#action_args_theme_label_user, #action_args_theme_label').removeClass('d-none');
        $('#action_args_user_group, #action_args_user, #action_args_theme, .select2').removeClass('d-none').removeAttr('disabled');
    }else
    if (val=='command'){
        $('#action_args_command_label').removeClass('d-none');
        $('#action_args_command').removeClass('d-none').removeAttr('disabled');
    }
});

</script>
