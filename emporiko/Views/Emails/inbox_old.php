<?= $this->extend('Emails/index') ?>
<?= $this->section('_panel_content') ?>    
            <div class="card-body p-0 full-height">
                <div class="table-responsive">
                    <?php if (is_array($emails) && count($emails) > 0) :?>
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                               <th><input type="checkbox" class="p-0 m-0" onclick="$('input[name*=\'emid\']').prop('checked', this.checked);"></th>
                               <th style="display:contents">
                                   <div class="dropdown" style="display:contents">
                                       <button class="btn btn-dark btn-sm dropdown-toggle mt-2" type="button" id="emailsInboxDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            ...
                                       </button>
                                       <div class="dropdown-menu" aria-labelledby="emailsInboxDropdown">
                                           <a class="dropdown-item" href="#">Action</a>
                                       </div>
                                   </div>
                               </th>
                                <th><?= lang('emails.mail_from') ?></th>
                                <th><?= lang('emails.mail_subject') ?></th>
                                <th><?= lang('emails.mail_rec') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= form_open('',['id'=>'emails_msgslist_form'],['model'=>'emails']) ?>
                            <?php foreach($emails as $email) :?>
                            <tr <?= !$email['mail_read'] ? 'style="font-weight: 700!important;"':''?>>
                                <td>
                                    <div class="icheck-primary">
                                        <input type="checkbox" value="<?= $email['emid'] ?>" name="emid[]">
                                        <label for="check4"></label>
                                    </div>
                                </td>
                                <td class="mailbox-attachment">
                                    <?php if (strlen($email['mail_attachements']) > 0) :?>
                                    <i class="fas fa-paperclip text-secondary"></i>
                                    <?php endif ?>
                                </td>
                                <td class="mailbox-name">
                                    <?= array_key_exists($email['mail_from'], $contacts) ? $contacts[$email['mail_from']] : $email['mail_from']?>
                                </td>
                                <td class="mailbox-subject" data-emailid="<?= $email['emid'] ?>" style="cursor: pointer;">
                                    <?=$email['mail_subject']?>
                                </td>
                                
                                <td class="mailbox-date">
                                    <small>
                                    <?= convertDate($email['mail_rec'], null, 'd M Y H:i')?>
                                        </small>
                                </td>
                            </tr>
                            <?php endforeach ?>
                            </form>
                        </tbody>
                    </table>
                    <?php else :?>
                    <h5 class="alert alert-info m-1"><?= lang('emails.msg_mailbox_empty') ?></h5>
                    <?php endif ?>
                </div>
            </div>
            <div class="card-footer p-0 pb-1">
                <?php if (is_array($emails) && count($emails) > 0) :?>
                <div class="mailbox-controls">
                    <div class="float-right">
                        <?= $pagination['start'] ?>-<?= $pagination['end'] ?>/<?= $pagination['max'] ?>
                        <div class="btn-group">
                            <?= $pagination['links'] ?>
                        </div>
                    </div>
                </div>
                <?php endif ?>
            </div>
        </div>
        
<script>
    $('[data-emailid]').on('click',function(){
        var url='<?= $url_view ?>';
        url=url.replace('-id-',$(this).attr('data-emailid'));
        window.location=url;
    });
    
    $('[data-submit]').on('click',function(){
        var submit=true;
        if ($(this).attr('data-yesno')!=undefined){
            submit=false;
            var url=$(this).attr('data-submit');
            ConfirmDialog('<?= lang('system.general.msg_delete_ques')?>',function(){
                $('#emails_msgslist_form').attr('action',url).submit();
            });
        }
        if (submit){
            $('#emails_msgslist_form').attr('action',url).submit();
        }
    });
    
</script>
<?= $this->endSection() ?>