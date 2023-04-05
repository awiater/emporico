<?= $currentView->includeView('System/form') ?>
<script>
    $(function(){
       $("#id_emails_field").find('.input-group-append button').attr('onclick',' addCopyEmailsBtn()'); 
       <?php if (array_key_exists('_readonly', $record) && $record['_readonly']) :?>
       $('input[type=text]').each(function(){
           var name=$(this).attr('name');
           addRefButton($(this).attr('id'),"copyFielValue('"+name+"')",'fas fa-copy','tooltip','btn btn-secondary');
       });
       function copyFielValue(name){
           copyToClipboard($('[name="'+name+'"]').val());
        }
    <?php endif ?>
    });
    
    function addCopyEmailsBtn()
    {
       var btn=$("#id_emails_field").find('.input-group-append');
       var html='<button type="button" class="btn btn-secondary btn-sm" onclick="copy_acc_emails()">';
       html+='<i class="fas fa-copy"></i></button>';
       html+=$("#id_emails_field").find('.input-group-append').html();
       btn.html(html);
       $("#id_emails_field").find('.input-group-append .btn-primary').attr('onclick',' id_emails_listadd()');
       id_emails_listadd();
    }
    
    function copy_acc_emails()
    {
        var emails=[];
        $("#id_emails_list div").each(function(){
            var val=$(this).find('.badge').text();
            
            if(isEmail(val)){
                emails.push(val);
            }
            
        });
        copyToClipboard(emails.join(';'));
    }
</script>