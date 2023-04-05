<?php if(empty($_external_call)) :?>
<?= $this->extend('Emails/index') ?>
<?= $this->section('_panel_content') ?>
<div class="card-body">
    <?= form_open_multipart($action, ['id'=>'emailsEditMessageForm'], ['mailbox'=>$mailbox,'folder'=>'out','id'=>$emid,'msgnote'=>$msgnote]) ?>
    <div class="mailbox-controls border rounded text-left mt-1 mb-2">
        <?= $edittoolbar?>
    </div>
<?php endif ?>
    <?php if(!empty($record['mail_from'])) :?>
    <div class="form-group">
        <div class="input-group mb-3">
            <div class="input-group-prepend" style="min-width:85px">
                <span class="input-group-text w-100">
                    <?= lang('emails.mail_from') ?>
                </span>
            </div>
            <?php if (is_array($record['mail_from'])) :?>
            <?= form_dropdown('mail_from', $record['mail_from'], [loged_user('email')], ['class'=>'form-control'])?>
            <?php else :?>
            <input class="form-control" value="<?= $record['mail_from'] ?>" name="mail_from">
            <?php endif ?>
        </div>
    </div>
    <?php endif ?>
    <div class="form-group">
        <div class="input-group mb-3">
            <div class="input-group-prepend" style="min-width:85px">
                <span class="input-group-text w-100">
                    <?= lang('emails.mail_to') ?>
                </span>
            </div>
            <input class="form-control" value="<?= empty($record['mail_to']) ? '' :$record['mail_to'] ?>" name="mail_to">
        </div>
    </div>
    
    <div class="form-group<?= empty($record['mail_cc']) ? ' d-none' :'' ?>" id="emailsEditMessageCC">
        <div class="input-group mb-3">
            <div class="input-group-prepend" style="min-width:85px">
                <span class="input-group-text w-100">
                    <?= lang('emails.mail_cc') ?>
                </span>
            </div>
            <input class="form-control" value="<?= empty($record['mail_cc']) ? '' :$record['mail_cc'] ?>" name="mail_cc">
        </div>
    </div>
    
    <div class="form-group<?= empty($record['mail_bcc']) ? ' d-none' :'' ?>" id="emailsEditMessageBCC">
        <div class="input-group mb-3">
            <div class="input-group-prepend" style="min-width:85px">
                <span class="input-group-text w-100">
                    <?= lang('emails.mail_bcc') ?>
                </span>
            </div>
            <input class="form-control" value="<?= empty($record['mail_bcc']) ? '' :$record['mail_bcc'] ?>" name="mail_bcc">
        </div>
    </div>
    
    <div class="form-group">
        <div class="input-group mb-3">
            <div class="input-group-prepend" style="min-width:85px">
                <span class="input-group-text w-100">
                    <?= lang('emails.mail_subject') ?>
                </span>
            </div>
            <input class="form-control" value="<?= empty($record['mail_subject']) ? '' :$record['mail_subject'] ?>" name="mail_subject">
        </div>
    </div>
   
    <div class="form-group">
        <textarea class="form-control editor" style="height: 300px;" name="mail_body">
            <?= empty($record['mail_body']) ? '' :$record['mail_body'] ?>
        </textarea>
    </div>
<?php if(empty($_external_call)) :?>
    </form> 
</div>
<?php endif ?>
<script>
    <?= view('System/tinymce',['id'=>'.editor','tinytoolbar'=>'emailext','height'=>600]) ?>
    $('button[data-target]').on('click',function(){
        var id=$(this).attr('data-target');
        if ($('#'+id).hasClass('d-none')){
            $('#'+id).removeClass('d-none');
        }else{
           $('#'+id).addClass('d-none');
           $('#'+id).find('input').val(' ');
        }
    });
    <?php if(empty($_external_call)) :?>
    $('#emailsEditMessageSendBtn').on('click',function(){
        var mail_to=$('input[name="mail_to"]').val();
        var mail_cc=$('input[name="mail_cc"]').val();
        var mail_bcc=$('input[name="mail_bcc"]').val();
        var receipQty=(mail_to.split(';').length)+(mail_cc.split(';').length)+(mail_bcc.split(';').length);
        
        var send=true;
        
        if (receipQty >= <?= !empty($maxrecpermessage) ? $maxrecpermessage : 10 ?>){
            send=false;
            Dialog('<?= lang('emails.error_maxrecpermessage') ?>','warning');
            return false;
        }
        
        if ((mail_to.length > 0 && !isEmailMulti(mail_to))
             ||(mail_cc.length > 0 && !isEmailMulti(mail_cc))
             ||(mail_bcc.length > 0 && !isEmailMulti(mail_bcc))){
            Dialog('<?= lang('emails.error_invalidemail') ?>','warning');
            send=false;
        }
        
        if (send && $('input[name="mail_subject"]').val().length < 1){
            send=false;
            ConfirmDialog('<?= lang('emails.msg_empty_subject')?>',function(){
                $('#emailsEditMessageForm').submit();
            });
        }
        
        if (send && tinymce.activeEditor.getContent().length < 1){
            send=false;
            ConfirmDialog('<?= lang('emails.msg_empty_body')?>',function(){
                $('#emailsEditMessageForm').submit();
            });
        }
        
        if (send){
          $('#emailsEditMessageForm').submit();  
        }
        
    });
    
    $('#emailsEditMessageCancelBtn').on('click',function(){
         ConfirmDialog('<?= lang('emails.msg_savedraft')?>',function(){
                addLoader('#emails_body_content');
                $('input[name="folder"]').val('drafts');
                $('#emailsEditMessageForm').submit();
            },function(){
                addLoader('#emails_body_content');
                window.location='<?= $refurl ?>';
            });
    });
    
    $('#emailsEditMessageDraftBtn').on('click',function(){
         addLoader('#emails_body_content');
         $('input[name="folder"]').val('drafts');
         $('#emailsEditMessageForm').submit();
    });
    <?php endif ?>
    function isEmailMulti(email){
       var ret=true;
       $.each(email.split(';'),function(index, value){
            if (value.length > 0 && !isEmail(value)){
               ret= false;
           }
       });
       return ret;
    }
</script>
<?php if(empty($_external_call)) :?>
<?= $this->endSection() ?>
<?php endif ?>