
    <div class="breadcrumb p-1">
        <?= !empty($_menubar) ? $_menubar : '' ?>
    </div>
<div class="container col-12" id="id_suppliers_container">
    <?php if(!empty($_nodata) && $_nodata) :?>
    <?= createErrorMessage('products.error_no_supp_data', 'info', FALSE); ?>
    <?php else :?>
    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <div class="card">
                        <div class="card-header p-2"></div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('products.sup_name') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['sup_name'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('products.sup_code') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['sup_code'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('products.sup_currency') ?></div>
                                <div class="col-xs-12 col-md-8 text-right">
                                    <i class="<?= $curr_icon?>" data-toggle="tooltip" data-placement="bottom" title="<?= $record['sup_currency'] ?>"></i>
                                </div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('products.sup_leadtime') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['sup_leadtime'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('products.sup_isifa') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= lang($record['sup_isifa'] ? 'system.general.yes' : 'system.general.no') ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('products.sup_contactnr') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['sup_contactnr'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('products.sup_minorderval') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['sup_minorderval'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('products.sup_rebate') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['sup_rebate']==null ? lang('products.sup_norreb') :  $record['sup_rebate']?></div>
                            </li>
                        </ul>
                    </div>
                    <div class="card">
                        <div class="card-header p-2">
                            <h5 class="card-title font-weight-bold"><?= lang('products.sup_brand') ?></h5>
                        </div>
                        <div class="card-body p-1">
                            <div class="col-12">
                                <div class="row">
                                    <?php foreach($brands as $brand) :?>
                                    <div class="col-2 p-0 mr-1">
                                        <img src="<?= parsePath($brand['prb_logo'])?>" alt="<?=$brand['prb_name']?>" class="img-thumbnail border-0" data-url="<?= url('Products','brands',[$brand['prbid']],['refurl'=>current_url(FALSE,TRUE)])?>" style="cursor: pointer;box-shadow:none;">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>    
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <div class="card">
                        <div class="card-header p-2"></div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('products.sup_ordermode') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['sup_ordermode'] ?></div>
                            </li>
                            <li class="list-group-item p-1 mb-1">
                                <div class="col-12 font-weight-bold"><?= lang('products.sup_ordernote') ?></div>
                                <div class="col-12 text-right"><?= $record['sup_ordernote'] ?></div>
                            </li>
                            <li class="list-group-item p-1 mb-1">
                                <div class="col-12 font-weight-bold"><?= lang('products.sup_invoicenote') ?></div>
                                <div class="col-12 text-right"><?= $record['sup_invoicenote'] ?></div>
                            </li>
                            <li class="list-group-item p-1 mb-1">
                                <div class="col-12 font-weight-bold"><?= lang('products.sup_bookingnote') ?></div>
                                <div class="col-12 text-right"><?= $record['sup_bookingnote'] ?></div>
                            </li>
                        </ul>
                    </div>
                    <div class="card">
                        <div class="card-header p-2">
                            <h5 class="card-title font-weight-bold"><?= lang('products.sup_orderdays') ?></h5>
                        </div>
                        <div class="card-body">
                            <?php $index=0; ?> 
                            <table class="table table-bordered">
                                <tr>
                                <?php foreach($sup_orderdays_list as $id=>$day) :?>
                                 <?php if ($index == 4) :?>
                                </tr><tr>
                                <?php endif ?>
                                <td class="text-center
                                <?php if (\EMPORIKO\Helpers\Strings::contains($record['sup_orderdays'], $id)) :?>
                                 bg-primary
                                <?php endif ?>
                                 "><p class="p-0 m-0" data-toggle="tooltip" data-placement="bottom" title="<?= array_key_exists($id,$sup_orderdays_list_tooltip) ? $sup_orderdays_list_tooltip[$id]: '' ?>">
                                        <?= $day ?>
                                    </p>
                                </td>
                                <?php $index++; ?>
                                <?php endforeach ?>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header p-2">
                            <h5 class="card-title font-weight-bold"><?= lang('products.sup_rebate') ?></h5>
                        </div>
                        <div class="card-body">
                            <?= $record['sup_rebate'] ?>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
             <!-- Contacts -->
            <?php if (array_key_exists('contacts', $record) && is_array($record['contacts'])) :?>
            <div class="card">
                <div class="card-header p-2 d-flex">
                    <h5 class="card-title font-weight-bold"><?= lang('products.sup_cardcontacts') ?></h5>
                    <button type="button" class="btn btn-sm btn-dark ml-auto btn-xs" data-placement="top" title="" data-toggle="tooltip" data-url="<?= $url_contacts_new ?>">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
                <?php if(count($record['contacts']) < 1) :?>
                <div class="card-body">
                    <h6><?= lang('products.sup_cardcontacts_no') ?></h6>
                </div>
                <?php else :?>
                <ul class="list-group list-group-flush">
                    <?php foreach($record['contacts'] as $key=>$contact) :?>
                    <li class="list-group-item d-flex p-1 mb-1">
                        <div>
                            <?= $contact['ct_name'] ?><br>
                            <small>
                                <?= $contact['ct_email'] ?>
                                <?php if (strlen($contact['ct_phone']) > 0 || strlen($contact['ct_phone2']) > 0 ||strlen($contact['ct_phone3']) > 0) :?>    
                                <div>
                                <?= $contact['ct_phone'] ?>
                                <?php if (strlen($contact['ct_phone2']) > 0) :?>
                                &nbsp;|&nbsp;<?= $contact['ct_phone2'] ?>
                                <?php endif?>
                                <?php if (strlen($contact['ct_phone3']) > 0) :?>
                                &nbsp;|&nbsp;<?= $contact['ct_phone3'] ?>
                                <?php endif?>
                                </div>
                            <?php endif?>
                            </small>
                        </div>
                        <div class="ml-auto d-xs-none d-md-flex mr-2">
                            <div>
                                <?php if (strlen($contact['ct_phone']) > 0 || strlen($contact['ct_phone2']) > 0 ||strlen($contact['ct_phone3']) > 0) :?>
                                <button type="button" class="btn btn-success btn-sm" data-phone="<?= $contact['ct_phone']?>" data-phonedisable="true">
                                    <i class="fas fa-phone-volume"></i>
                                </button>
                                <?php endif?>
                                <button type="button" class="btn btn-info btn-sm" data-url="<?= str_replace('-id-', $contact['ctid'], $url_cts_email)?>">
                                    <i class="far fa-envelope"></i>
                                </button>
                            </div>
                            <?php if (!empty($edit_acc) && $edit_acc) :?>
                            <div class="dropleft ml-1">
                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="id_suppview_editbtn_<?= $key?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="id_suppview_editbtn_<?= $key?>">
                                    <button class="dropdown-item" data-url="<?= str_replace('-id-',$contact['ctid'],$url_cts_edit) ?>">
                                        <?= lang('connections.contacts_btn_edit')?>
                                    </button>
                                    <button class="dropdown-item" data-delete="true" data-url="<?= str_replace('-id-',$contact['ctid'],$url_cts_del) ?>">
                                        <?= lang('connections.contacts_btn_del')?>
                                    </button>
                                </div>
                            </div>
                            <?php endif?>
                        </div>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </div>
             <?= $_record_call_form ?>
            <?php endif ?>
            <!-- /Contacts -->
            
            <?php if (!empty($filesform)) :?>
            <!-- filesform -->
                <?= $filesform ?>
            <!-- /filesform -->
            <?php endif ?>
            <?php if (!empty($movements)) :?>
            <!-- Movements -->
            <?= $movements ?>
            <!-- / Movements -->
            <?php endif ?>
        </div>
    </div>
    <?php endif ?>
</div>
<?php if (!empty($orderemail_modal)) :?>
<!-- Order Send Modal -->
<div class="modal" tabindex="-1" role="dialog" id="supp_sendordermodal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('products.sup_sendordermodal_title')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close')?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open_multipart($orderemail_modal, ['id'=>'supp_sendordermodal_form'], ['account'=>$record['sup_code']]) ?>
                <div class="form-group">
                    <label for="supp_sendordermodal_form_mail_to" class="mr-2">
                        <?= lang('products.sup_orderemail') ?>
                    </label>
                    <input type="email" name="mail_to" id="supp_sendordermodal_form_mail_to" value="<?=$record['sup_orderemail']?>" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="supp_sendordermodal_form_ordernr" class="mr-2">
                        <?= lang('products.sup_ordernr') ?>
                    </label>
                    <input type="text" name="ordernr" id="supp_sendordermodal_form_ordernr" value="" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="supp_sendordermodal_form_mail_file" class="mr-2">
                        <?= lang('products.sup_mail_file') ?>
                    </label>
                    <?= \EMPORIKO\Controllers\Pages\HtmlItems\UploadField::create()->setArgs(['name'=>'mail_file','id'=>'supp_sendordermodal_form_mail_file','accept'=>'.xls,.csv,.xlsx'])->render()?>
                </div>
                <div class="form-group">
                    <label for="supp_sendordermodal_form_mail_subject" class="mr-2">
                        <?= lang('products.sup_mail_subject') ?>
                    </label>
                    <input type="text" name="mail_subject" id="supp_sendordermodal_form_mail_subject" value="<?= !empty($orderemail_tpl) ? $orderemail_tpl['subject'] : '' ?>" class="form-control" >
                </div>
                <div class="form-group">
                    <label for="supp_sendordermodal_form_mail_body" class="mr-2">
                        <?= lang('products.mail_body') ?>
                    </label>
                    <?= \EMPORIKO\Controllers\Pages\HtmlItems\TinyEditor::create()->setArgs(['id'=>'supp_sendordermodal_form_mail_body','name'=>'mail_body','value'=>!empty($orderemail_tpl) ? $orderemail_tpl['body'] : ''])->setEmailToolbar()->render()?>
                </div>
                
                </form>
            </div>
            <div class="modal-footer d-flex">
                <button type="button" class="btn btn-danger mr-auto" id="supp_sendordermodal_cancel">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel') ?>
                </button>
                <button type="button" onclick="addLoader('#supp_sendordermodal');$('#supp_sendordermodal_form').submit();" class="btn btn-success">
                <i class="fas fa-share-square mr-1"></i><?= lang('system.buttons.submit') ?>    
                </button>
            </div>
        </div>
    </div>
</div>
<!-- / Order Send Modal -->
<?php endif ?>
<script>
 $(function(){
     $("#_menubar").addClass('navbar-white bg-white').removeClass('navbar-light bg-light');
     $('.select2').select2({theme: 'bootstrap4'});
     $('.select2-selection').attr('style','height:31px!important');
 });

$('#supp_sendordermodal_cancel').on('click',function(){
    $('[name="mail_subject"]').val(atob('<?= base64_encode(!empty($orderemail_tpl) ? $orderemail_tpl['subject'] : '') ?>'));
    tinymce.activeEditor.setContent(atob('<?= base64_encode(!empty($orderemail_tpl) ? $orderemail_tpl['body'] : '') ?>'));
    $('#supp_sendordermodal').modal('hide');       
});

$('#id_suppliers_btn_print').on('click',function(){
    $("#id_suppliers_container").printThis({
            debug: false,              
            importCSS: true,             
            importStyle: true,         
            printContainer: true,       
            //loadCSS: ['<?= $css['template']?>','<?= $css['systemcss']?>'],
            pageTitle: "<?= lang('products.sup_prntheader',[$record['sup_code']]) ?>",             
            removeInline: false,        
            printDelay: 133,            
            header: '<h3><?= lang('products.sup_prntheader',[$record['sup_code']]) ?></h3>',             
            formValues: true 
        });
});

$('.select2').on('change',function(e){
    var id=$('#id_suppliers_filter_part').find(':selected').val();
    var url='<?= $url_filter?>';
    url=url.replace('-id-',id);
    addLoader();
    window.location=url;
});

$('[data-url]').on('click',function(){
     var url=$(this).attr('data-url');
     if ($(this).attr('data-delete')!=undefined){
             ConfirmDialog('<?= lang('system.general.msg_delete_ques')?>',function(){
                 addLoader();
                 window.location=url; 
             });
         }else{
             addLoader();
             window.location=url;
         }
});



</script>