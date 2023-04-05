<?= $currentView->includeView('System/form') ?>
<script>
    $(function(){
        <?php if (!empty($record['edit_acc']) && $record['edit_acc']) :?>
        addRefButton('id_username','unlock_username_editing()','fas fa-lock','<?= lang('system.auth.profile_username_unlbtn')?>','btn btn-secondary');
        <?php endif ?>
        <?php if (!empty($access_levels_values)) :?>
        $.each(JSON.parse(atob('<?= base64_encode(json_encode($access_levels_values))?>')),function(key,opt){
            add_custom_module_access_item(opt['acc_ref'],opt);
        });
        <?php endif ?>
        $('#id_pass, #id_password').val('');
        $('#id_password_field').addClass('d-none');
    });
    function unlock_username_editing(){
            if ($('#id_username').attr("readonly")){
                $('#id_username').removeAttr('readonly');
                $('#bolt_btn_id_username').html('<i class="fas fa-lock-open"></i>');
            }else{
                $('#id_username').attr('readonly','true');
                $('#bolt_btn_id_username').html('<i class="fas fa-lock"></i>');
            }
    }
    
    $('#id_pass, #id_password').on('change',check_pass);
    
    function check_pass(){
         if ($('#id_pass').val().length > 0){
            $('#id_password_field').removeClass('d-none');
        }
        $('#id_pass').removeClass('border-danger').parent().find('small').text('');
        if ($('#id_pass').val().length > 0 && $('#id_pass').val().length < 6){
            $('#id_pass').addClass('border-danger').parent()
                    .find('#id_pass_tooltip')
                    .addClass('text-danger')
                    .removeClass('text-muted')
                    .text('<?= lang('system.auth.recover_error_pass_len')?>');
            return false;
       }
       
       $('#id_password').removeClass('border-danger').parent().find('small').text('');
        if ($('#id_password').val()!=$('#id_pass').val() && $('#id_password').val().length > 0){
           $('#id_password').addClass('border-danger').parent()
                    .find('small')
                    .addClass('text-danger')
                    .removeClass('text-muted')
                    .text('<?= lang('system.auth.error_pass_equal')?>');
            return false;
       }
       return true;
    }
    function profile_edit_submit(){
        var submit=true;
        $('[required]').each(function(){
                 if ($(this).attr('type')!='password' && $(this).val().length < 1){
                     var id=$(this).attr('id');
                     submit=false;
                     $('#tabs-'+$(this).attr('tab_name')+'-tab').tab('show');
                     fielderror('#'+id,'<?= lang('system.errors.required_notset',[''])?>');
                     return false;
                 }
             });
        if (check_pass() && submit){
            $('#profile_edit').submit();
        }
    }
    
    function fielderror(id,error){
         $(id).addClass('border-danger').parent()
                    .find('small')
                    .addClass('text-danger')
                    .removeClass('text-muted')
                    .text(error);
    }
    
    function add_custom_module_access()
    {
        var val=$('#id_accessgroups_modules_list').find(':selected').val();
        add_custom_module_access_item(val,[]);
    }
    
    function add_custom_module_access_item(module,values)
    {
        var id=$("#id_accessgroups_module_list").html().length;
        var html='<li class="list-group-item" id="id_accessgroups_module_list_listitem_'+id+'">';
        html+='<div class="row"><div class="col-2">'+module.toUpperCase()+'</div><div class="col-8">';
        console.log(values);
        $.each(JSON.parse(atob('<?= base64_encode(json_encode($access_levels))?>')),function(key,opt){
            var val='';
            if ('acc_'+key in values){
                val=values['acc_'+key]=='1' ? 'checked' : '0';
            }
            html+='<div class="icheck-primary mr-2">';
            html+='<input type="checkbox" '+val+' value="1" id="id_accessgroups_module_list_listitem_'+id+'_'+key+'_field" name="perms['+module+']['+key+']"/>';
            html+='<label for="id_accessgroups_module_list_listitem_'+id+'_'+key+'_field">'+opt+'</label>';
            html+='</div>';
        });
        html+='</div><div class="col-2"><button type="button" class="btn btn-danger btn-sm ml-auto" onclick="';
        html+="$('#id_accessgroups_module_list_listitem_"+id+"').remove()";
        html+='"><i class="fas fa-trash-alt"></i>';
        html+='</button></div></div></li>';
        $('#id_accessgroups_module_list').append(html);
    }
</script>