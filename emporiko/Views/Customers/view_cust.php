<style>
    .field_divider{
        border-right:1px dotted #000;
        padding-right: 8px;
    }
    <?php if ($record['enabled']==0) :?>
    .card-header{
        background-color:#dc3545!important;
        color:#FFF!important; 
    }
    <?php endif ?>
</style>
 
<div class="col-12">
    <div class="breadcrumb p-1">
    <?= view('System/Elements/toolbar',$fields['toolbar']['args']) ?>
    </div>
</div>
<div id="account_view_container">
<?php if ($record['enabled']==0) :?>    
<h2 class="card-title text-center font-weight-bold col-12 bg-danger mb-3 p-1 rounded-top">
    <marquee>
        <div class="d-flex">
            <b class="mr-2"><?= lang('customers.error_dormant') ?></b>
            <b class="mr-2"><?= lang('customers.error_dormant') ?></b>
            <b class="mr-2"><?= lang('customers.error_dormant') ?></b>
        </div>
    </marquee>
</h2>
<?php endif ?>    
   
<!-- Details -->
<div class="row">
    <!-- Details Left-->
    <div class="col-xs-12 col-md-8">
        <!-- Stats -->
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <h2 class="card-title text-center font-weight-bold col-12"><?= $record['name'] ?></h2>
                    <h5 class="card-title text-center text-md col-12"><?= $record['code'] ?></h5>
                </div> 
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-3 border-right">
                        <div class="description-block">
                            <h5 class="description-header">
                            <?php if (array_key_exists('stat_orders',$record) && array_key_exists('opportunities', $record['stat_orders'])) :?>
                                <?php if ($record['stat_orders']['orders']>0) :?>
                                <a href="<?= $url_show_oport?>" class="text-dark">
                                    <?=$record['stat_orders']['opportunities']?>
                                </a>
                                <?php else :?>
                                0
                                <?php endif ?>
                            <?php else :?>
                            0
                            <?php endif ?>
                            </h5>
                            <span class="description-text"><?= lang('orders.opportunities_msg_cusstat') ?></span>
                        </div>
                    </div>
                    
                     <div class="col-sm-3 border-right">
                        <div class="description-block">
                            <h5 class="description-header">
                            <?php if (array_key_exists('stat_orders',$record) && array_key_exists('quotes', $record['stat_orders'])) :?>
                                <?php if ($record['stat_orders']['quotes']>0) :?>
                                <a href="<?= $url_show_quotes?>" class="text-dark">
                                    <?=$record['stat_orders']['quotes']?>
                                </a>
                                <?php else :?>
                                0
                                <?php endif ?>
                            <?php else :?>
                            0
                            <?php endif ?>
                            </h5>
                            <span class="description-text"><?= lang('orders.quotes_msg_cusstat') ?></span>
                        </div>
                    </div>
                    
                     <div class="col-sm-3 border-right">
                        <div class="description-block">
                            <h5 class="description-header">
                            <?php if (array_key_exists('stat_orders',$record) && array_key_exists('orders', $record['stat_orders'])) :?>
                                <?php if ($record['stat_orders']['orders']>0) :?>
                                <a href="<?= $url_show_orders?>" class="text-dark">
                                    <?=$record['stat_orders']['orders']?>
                                </a>
                                <?php else :?>
                                0
                                <?php endif ?>
                            <?php else :?>
                            0
                            <?php endif ?>
                            </h5>
                            <span class="description-text"><?= lang('customers.accounts_stat_orders') ?></span>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="description-block">
                            <h5 class="description-header">
                            <?php if ($unread_emails>0) :?>
                                <a href="<?= $url_email_unread ?>" class="text-dark"><?= $unread_emails ?></a>
                            <?php else :?>
                            0
                            <?php endif ?>
                            </h5>
                            <span class="description-text"><?= lang('customers.accounts_stat_emails') ?></span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- /Stats -->
        
        <!-- Info --> 
        <div class="container p-0 col-12">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <div class="card">
                        <div class="card-header p-2">
                            <h6 class="card-title font-weight-bold"><?= lang('customers.tab_basic') ?></h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('customers.accounts_group') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['group'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('customers.accounts_type') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['type'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('customers.accounts_terms_inco') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['terms_inco'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('customers.accounts_terms_pay') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['terms_pay'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('customers.accounts_terms_price') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['terms_price'] ?></div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <div class="card">
                        <div class="card-header p-2">
                            <h6 class="card-title font-weight-bold"><?= lang('customers.tab_others') ?></h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('customers.accounts_terms_area') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['terms_area'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('customers.accounts_terms_credit') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['terms_credit'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('customers.accounts_terms_delco') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['terms_delco'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('customers.accounts_terms_buyco') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['terms_buyco'] ?></div>
                            </li>
                            <li class="list-group-item d-flex p-1 mb-1">
                                <div class="col-xs-12 col-md-4 font-weight-bold"><?= lang('customers.accounts_employee') ?></div>
                                <div class="col-xs-12 col-md-8 text-right"><?= $record['employee'] ?></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Info -->
        
        <!-- Notes -->
        <?php if (array_key_exists('notes', $record) && strlen($record['notes']) > 0) :?>
        <div class="card">
            <div class="card-header p-2">
                <h5 class="card-title font-weight-bold"><?= lang('customers.accounts_notes') ?></h5>
                <div class="float-right">
                    <button type="button" class="btn btn-dark btn-xs mr-2" onclick="$('#accounts_addnotemodal').modal('show');" data-toggle="tooltip" data-placement="top" title="<?= lang('customers.btn_addnewnote') ?>">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-2">
                <?= $record['notes'] ?>
            </div>
        </div>
        <?php endif?>
        <!-- /Notes -->
        
         <!-- Addresses -->
        <?php if (strlen($record['address_pay']) > 0 || strlen($record['address_ship']) > 0) :?>
        <div class="container p-0">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <div class="card">
                        <div class="card-header p-2">
                            <h5 class="card-title font-weight-bold"><?= lang('customers.accounts_address_pay') ?></h5>
                        </div>
                        <div class="card-body p-2">
                            <?= $record['address_pay'] ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <div class="card">
                        <div class="card-header p-2">
                            <h5 class="card-title font-weight-bold"><?= lang('customers.accounts_address_ship') ?></h5>
                        </div>
                        <div class="card-body p-2">
                            <?= $record['address_ship'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif?>
        <!-- /Addresses -->
        
        <?php if (intval($settings['customers_viewsales']) ==1) :?>
        <!-- Sales Widget -->
        <div class="card">
            <div class="card-header p-2">
                <h5 class="card-title font-weight-bold"><?= lang('orders.sales_widget_title') ?></h5>
                <div class="float-right">
                    <button type="button" class="btn btn-dark btn-xs mr-2" data-url="<?=$url_newoport?>" data-toggle="tooltip" data-placement="top" title="<?= lang('orders.opportunities_btn_new') ?>">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-xs mr-2" data-card-widget="collapse" data-toggle="tooltip" data-placement="top" title="<?= lang('system.buttons.collapse_tooltip') ?>">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-2">
                <?= loadModule('Sales','getWidgetData',[-1,$record['code']]) ?>
            </div>
        </div>
        <!-- /Sales Widget -->
        <?php endif ?>
        
        
        <?php if (intval($settings['customers_viewcases']) > 0) :?>
        <!-- Tickets -->
        <div class="card">
            <div class="card-header p-2">
                <h5 class="card-title font-weight-bold"><?= lang('tickets.customer_view') ?></h5>
                <div class="float-right">
                <button type="button" class="btn btn-default btn-xs mr-2" data-card-widget="collapse" data-toggle="tooltip" data-placement="top" title="<?= lang('system.buttons.collapse_tooltip') ?>">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-xs btn-default m-0" data-toggle="tooltip" data-placement="top" title="<?= lang('tickets.customer_view_openbtn') ?>" data-url="<?=url('Tickets','cases',[],['customer'=>$record['code'],'refurl'=>current_url(FALSE,TRUE)])?>">
                    <i class="fas fa-external-link-alt"></i>
                </button>
                </div>
            </div>
            <div class="card-body p-2">
                <?php if (!empty($tickets) && is_array($tickets) && count($tickets)>0) :?>
                <table class="table table-sm table-hover table-striped table-hiddenhead">
                    <thead class="bg-dark d-none">
                        <tr>
                            <th><?= lang('tickets.tck_subject') ?></th>
                            <th><?= lang('tickets.tck_status') ?></th>
                            <th><?= lang('tickets.tck_priority') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tickets as $ticket) :?>
                        <tr data-url="<?= url('Tickets','cases',[$ticket['tiid']],['refurl'=>current_url(FALSE,TRUE)])?>" style="cursor:pointer" > 
                            <td><?= $ticket['tck_subject'] ?></td>
                            <td><?= lang($ticket['tck_status_full']) ?></td>
                            <td><?= lang('tickets.tck_priority_list')[$ticket['tck_priority']] ?></td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>    
                </table>
                <?php else :?>
                <h5 class="text-center w-100">
                    <?= lang('tickets.customer_view_notickets') ?>
                </h5>
                <?php endif ?> 
            </div>
        </div>
        <!-- /Tickets -->
        <?php endif ?>
        
        <?php if (intval($settings['customers_viewemails']) == 1) :?>
        <!-- Emails -->
        <div class="card">
            <div class="card-header p-2">
                <h5 class="card-title font-weight-bold"><?= lang('customers.accounts_emails') ?></h5>
                <div class="float-right">
                    <button type="button" class="btn btn-dark btn-xs mr-2" data-url="<?=$url_email_new?>" data-toggle="tooltip" data-placement="top" title="<?= lang('customers.btn_addnewnote') ?>">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-xs mr-2" data-card-widget="collapse" data-toggle="tooltip" data-placement="top" title="<?= lang('system.buttons.collapse_tooltip') ?>">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-2">
                <?= loadModule('Emails','getEmailsByAccount',[$record['code'],'widget']) ?>
            </div>
        </div>
        <!-- /Emails -->
        <?php endif ?>
    </div>
    <!-- / Details Left-->
    <!-- Details Right-->
    <div class="col-xs-12 col-md-4">
        <!-- Contacts -->
        <?php if (array_key_exists('contacts', $record) && is_array($record['contacts'])) :?>
        <div class="card">
            <div class="card-header p-2 d-flex">
                <h5 class="card-title font-weight-bold"><?= lang('customers.tab_emp') ?></h5>
                <?= html_button('id_customers_view_add_cts','new',null,null,['data-url'=>$url_cts_new,'class'=>'ml-auto btn-xs']) ?>
            </div>
            <?php if(count($record['contacts']) < 1) :?>
            <div class="card-body">
                <h6><?= lang('customers.error_nocontacts_acc') ?></h6>
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
                                <button type="button" class="btn btn-warning btn-sm" data-phone="<?= $contact['ct_phone']?>">
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
        <?php endif ?>
        <?= $_record_call_form ?>
        <!-- /Contacts -->
        
       <?php if (!empty($movements)) :?>
        <!-- Movements -->
        <?= $movements ?>
        <!-- / Movements -->
        <?php endif ?>
        
        <?php if (!empty($filesform)) :?>
        <!-- filesform -->
        <?= $filesform ?>
        <!-- /filesform -->
        <?php endif ?>
    </div>
    <!-- / Details Right--> 
</div>
<!-- /Details -->
<!-- / account_view_container -->     
</div>

<!-- Add New Note Modal -->
<div class="modal" tabindex="-1" role="dialog" id="accounts_addnotemodal" data-full="0">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?= lang('customers.btn_addnewnote') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('systems.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>   
            </div>
            <div class="modal-body">
                <?= form_open($_formview_action,['id'=>'accounts_addnotemodal_form'],['cid'=>$record['cid'],'notes[]'=> base64_encode($record['notes'])]) ?>
                <div class="form-group">
                    <label fo=""><?= lang('customers.accounts_notes') ?></label>
                    <textarea class="form-control" name="notes[]" id="accounts_addnotemodal_form_notes"></textarea>
                </div>
                </form>
            </div>
            <div class="modal-footer d-flex">
                <button type="button" class="btn btn-danger btn-sm mr-auto" onclick="$('#accounts_addnotemodal_form_notes').val('');$('#accounts_addnotemodal').modal('hide');">
                    <i class="fas fa-ban mr-2"></i><?= lang('system.buttons.close') ?> 
                </button>
                <button type="button" class="btn btn-success btn-sm" id="accounts_addnotemodal_form_submit">
                    <i class="far fa-save mr-2"></i><?= lang('system.buttons.submit') ?> 
                </button>
            </div>
        </div>
    </div>    
</div>
<!-- / Add New Note Modal -->
<script>
    $(function(){
        $("#toolbar").addClass('navbar-white bg-white').removeClass('navbar-light bg-light');
        
        $('select[name="customer_filter"]').addClass('ml-3').attr('style','width:350px');
        $('select[name="customer_filter"]').select2({theme: 'bootstrap4'});
        $('select[name="customer_filter"]').parent().find('.select2-selection').attr('style','height:31px!important');//
        
        $('#id_products_sendpricefile_modal_fields').find('.select2').attr('style','width:100%').parent().find('.select2-selection').attr('style','height:31px!important');
        
        $('select[name="customer_filter"]').on('change',function(e){
            var val=$(this).find(':selected').val();
            url='<?= $url_view?>';
            url=url.replace('-id-',val);
            addLoader();
            window.location=url;
        });
        <?php if (!$record['enabled']) :?>
        $('#account_disable').removeClass('btn-danger')
                             .addClass('btn-success')
                             .attr('data-original-title','<?= lang('customers.btn_enable')?>')
                             .attr('data-val',1)
                             .find('i')
                             .removeClass('fa fa-eye-slash')
                             .addClass('fa fa-eye');
        <?php endif ?>
    });
    
    $('#id_email_custacc').on('click',function(){
        ajaxCall($(this).attr('data-url'),[],
        function(data){
            if ('error' in data){
                Dialog(data['error'],'danger');
            }
            if ('url' in data){
                window.location=data['url'];
            }
        }
        ,function(data){console.log(data)});
    });
    
    
    $('#accounts_addnotemodal_form_submit').on('click',function(){
        $('#accounts_addnotemodal').modal('hide');
        addLoader('#account_view_container');
        $('#accounts_addnotemodal_form').submit();
    });
    
    
    $('.table-hiddenhead tr').on('mouseover',function(){
        $(this).parent().parent().find('thead').removeClass('d-none');
    });
    
    $('.table-hiddenhead tr').on('mouseout',function(){
        $(this).parent().parent().find('thead').addClass('d-none');
    });
    
    $('button[data-email]').on('click',function(){
        var url='<?= $url_ct_email ?>';
        url=url.replace('-email-',$(this).attr('data-email'));
        window.location=url;
    });
    
    
    $('#account_disable').on('click',function(){
        var url=$(this).attr('data-durl');
        url=url.replace('-val-',$(this).attr('data-val'));
        ConfirmDialog('<?= lang(!$record['enabled'] ? 'customers.msg_enable_acc':'customers.msg_disable_acc') ?>',function(){
           window.location=url; 
        });      
    });
    
    $("#accounts_btn_print").on('click',function(){
        $("#account_view_container").printThis({
            debug: false,              
            importCSS: true,             
            importStyle: true,         
            printContainer: true,       
            //loadCSS: ['<?= $css['template']?>','<?= $css['systemcss']?>'],
            pageTitle: "<?= lang('customers.accounts_prntheader',[$record['code']]) ?>",             
            removeInline: false,        
            printDelay: 133,            
            header: '<h3><?= lang('customers.accounts_prntheader',[$record['code']]) ?></h3>',             
            formValues: true 
        });
    });
</script>
