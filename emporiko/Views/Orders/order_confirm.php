<?php if (empty($only_script)) :?>
<div class="modal" tabindex="-1" role="dialog" id="ord_modalconfirm">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('orders.ord_modalconfirm_title')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close')?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open($url_confirm, ['id'=>'ord_modalconfirm_form'], ['confirm_form_id'=>'']) ?>
                <div class="form-group">
                    <label for="ord_modalconfirm_form_email" class="mr-2">
                        <?= lang('orders.ord_modalconfirm_form_email') ?>
                    </label>
                    <input type="text" name="mail_to" id="ord_modalconfirm_form_email" value="" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="ord_modalconfirm_form_ref" class="mr-2">
                        <?= lang('orders.ord_ref') ?>
                    </label>
                    <input type="text" name="ref" id="ord_modalconfirm_form_ref" value="" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="ord_modalconfirm_form_subject" class="mr-2">
                        <?= lang('orders.ord_modalconfirm_form_subj') ?>
                    </label>
                    <input type="text" name="mail_subject" id="ord_modalconfirm_form_subject" value="" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="ord_modalconfirm_form_body" class="mr-2">
                        <?= lang('orders.ord_modalconfirm_form_body') ?>
                    </label>
                    <?= \EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor::create()->setArgs(['id'=>'ord_modalconfirm_form_body','name'=>'mail_body','value'=>''])->setEmailToolbar()->render()?>
                </div>
                </form>
            </div>
            <div class="modal-footer d-flex">
                <button type="button" class="btn btn-danger mr-auto" id="ord_modalconfirm_form_cancel">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel') ?>
                </button>
                <button type="button" id="ord_modalconfirm_form_submit" class="btn btn-success">
                <i class="fas fa-share-square mr-1"></i><?= lang('system.buttons.send') ?>    
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if (empty($only_body)) :?>

<?php if (empty($only_script_no)) :?>
<script>
<?php endif; ?>

 $('#ord_modalconfirm_form_cancel').on('click',function(){
        $('#ord_modalconfirm_form_email').removeClass('border-danger').val('');
        $('#ord_modalconfirm_form_ref').removeClass('border-danger').val('');
        $('#ord_modalconfirm_form_subject').removeClass('border-danger').val('');
        tinyMCE.activeEditor.setContent('');
        $('#ord_modalconfirm').modal('hide');
    });
    
    $('#ord_modalconfirm_form_submit').on('click',function(){
        var result=true;
        var emails=$('#ord_modalconfirm_form_email').val();
        $('#ord_modalconfirm_form_email').removeClass('border-danger');
        $('#ord_modalconfirm_form_ref').removeClass('border-danger');
        $('#ord_modalconfirm_form_subject').removeClass('border-danger');
        $.each(emails.split(';'),function(key,value){
            if (value.length > 0 && !isEmail(value)){
                result=false;
                Dialog('<?= lang('orders.error_confirm_email_notvalid') ?>','warning');
                $('#ord_modalconfirm_form_email').addClass('border-danger');
            }
        });
        
        if ($('#ord_modalconfirm_form_ref').val().length < 1){
            result=false;
            Dialog('<?= lang('orders.error_confirm_ref_notvalid') ?>','warning');
            $('#ord_modalconfirm_form_ref').addClass('border-danger');
        }
        
        if ($('#ord_modalconfirm_form_subject').val().length < 1){
            result=false;
            Dialog('<?= lang('orders.error_confirm_subject_notvalid') ?>','warning');
            $('#ord_modalconfirm_form_subject').addClass('border-danger');
        }
        
        if (tinymce.activeEditor.getContent().length < 1){
            result=false;
            Dialog('<?= lang('orders.error_confirm_body_notvalid') ?>','warning');
        }
        
        if (result){
            $('#ord_modalconfirm').modal('hide');
            addLoader();
            $('#ord_modalconfirm_form').submit();
        }
    });
    
    function callOrderApi(command,orderid,changeorder=true,action=null){
        addLoader();
        $('#ord_modalconfirm_form').attr('action',action==null ? '<?= !empty($url_confirm) ? $url_confirm : ''?>' : action); 
        if (changeorder){
            $('#ord_modalconfirm_form_ref').parent().removeClass('d-none');
        }else{
            $('#ord_modalconfirm_form_ref').parent().addClass('d-none');
        }
        ajaxCall('<?= url('Api','orders',['-command-']) ?>'.replace('-command-',command)
        ,{'ref':orderid,'template':true}
        ,function(data){
            if ('error' in data){
                Dialog(data['error'],'warning');
            }else
            if ('data' in data){
                $('#ord_modalconfirm_form_email').val(data['data']['acc_emails']);
                $('#ord_modalconfirm_form_ref').val(data['data']['ord_ref']);
                $('input[name="confirm_form_id"]').val(data['data']['ordid']);
                if ('template' in data){
                    $('#ord_modalconfirm_form_subject').val(data['template']['subject']);
                    tinyMCE.activeEditor.setContent(data['template']['body']);
                }
                if ('title' in data){
                    $('#ord_modalconfirm').find('.modal-title').text(data['title']);
                }
                $('#ord_modalconfirm').modal('show');
            }
            killLoader();
            console.log(data);
        }
        ,function(data){console.log(data)}
        ,'POST');
    }
    <?php if (!empty($url_confirm)) :?>
    $('button[data-conforder]').on('click',function(){
       callOrderApi('getOrderConf',$(this).attr('data-conforder'));
    });
    <?php endif; ?>
        
    <?php if (!empty($url_send)) :?>
    $('button[data-sendorder]').on('click',function(){
        callOrderApi('getOrderTpl',$(this).attr('data-sendorder'),false,'<?= $url_send?>');
    });
    <?php endif; ?>
<?php if (empty($only_script_no)) :?>
</script>
<?php endif; ?>    

<?php endif; ?>
