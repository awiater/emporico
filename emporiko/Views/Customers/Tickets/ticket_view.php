<div class="container col-12 p-2 m-0">
    <div class="row">
        <div class="col-12">
            <div class="breadcrumb p-1">
               <?= $currentView->getMenuBar() ?>
            </div>
        </div>
    </div>
</div>
<div class="container col-12 p-2" id="id_tickets_container">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="card card-info card-outline">
                <div class="card-header p-2">
                    <h3 class="card-title"></h3>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex p-1 mb-1 p-2">
                        <div class="col-xs-12 col-md-4 font-weight-bold">
                            <?= lang('tickets.tck_subject') ?>
                        </div>
                        <div class="col-xs-12 col-md-8 text-right">
                            <?= $record['tck_subject'] ?>
                        </div>
                    </li>
                    <li class="list-group-item d-flex p-1 mb-1 p-2">
                        <div class="col-xs-12 col-md-4 font-weight-bold">
                            <?= lang('tickets.tck_account') ?>
                        </div>
                        <div class="col-xs-12 col-md-8 text-right">
                            <?= $record['tck_account'] ?>
                        </div>
                    </li>
                    <li class="list-group-item d-flex p-1 mb-1 p-2">
                        <div class="col-xs-12 col-md-4 font-weight-bold">
                            <?= lang('tickets.tck_type') ?>
                        </div>
                        <div class="col-xs-12 col-md-8 text-right">
                            <?= is_array($ticket_types) && array_key_exists($record['tck_type'], $ticket_types) ? $ticket_types[$record['tck_type']] : $record['tck_type'] ?>
                        </div>
                    </li>
                    <li class="list-group-item d-flex p-1 mb-1 p-2">
                        <div class="col-xs-12 col-md-4 font-weight-bold">
                            <?= lang('tickets.tck_priority') ?>
                        </div>
                        <div class="col-xs-12 col-md-8 text-right">
                            <?= is_array($ticket_priority) && array_key_exists($record['tck_priority'], $ticket_priority) ? $ticket_priority[$record['tck_priority']] : $record['tck_priority'] ?>
                        </div>
                    </li>
                    <li class="list-group-item d-flex p-1 mb-1 p-2">
                        <div class="col-xs-12 col-md-4 font-weight-bold">
                            <?= lang('tickets.tck_status') ?>
                        </div>
                        <div class="col-xs-12 col-md-8 text-right">
                            <?= is_array($ticket_statuses) && array_key_exists($record['tck_status'], $ticket_statuses) ? $ticket_statuses[$record['tck_status']] : $record['tck_status'] ?>
                        </div>
                    </li>
                    <li class="list-group-item d-flex p-1 mb-1 p-2">
                        <div class="col-xs-12 col-md-4 font-weight-bold">
                            <?= lang('tickets.tck_addedon') ?>
                        </div>
                        <div class="col-xs-12 col-md-8 text-right">
                            <?= strlen($record['tck_addedon']) > 0 ? convertDate($record['tck_addedon'], null, 'd M Y H:i') : $record['tck_addedon'] ?>
                        </div>
                    </li>
                    <li class="list-group-item p-1 mb-1 p-2">
                        <div class="row">
                            <div class="col-12 font-weight-bold">
                                <?= lang('tickets.tck_desc') ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <?= $record['tck_desc'] ?>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="card card-info card-outline direct-chat-warning h-100">
                <div class="card-header p-2">
                    
                </div>
                <div class="card-body">
                    <div class="direct-chat-messages h-100">
                        <?php foreach($movements as $key=>$mov) :?>
                        <div class="direct-chat-msg <?= $record['tck_user']!=$mov['mhuser'] ? 'right' : ''?>">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name float-<?= $record['tck_user']!=$mov['mhuser'] ? 'right' : 'left'?>">
                                    <?= $mov['mhfrom'] ?>
                                </span>
                                <span class="direct-chat-timestamp float-<?= $record['tck_user']!=$mov['mhuser'] ? 'left' : 'right'?>">
                                    <?= strlen($mov['mhdate']) > 0 ? convertDate($mov['mhdate'], null, 'd M Y H:i') : $mov['mhdate'] ?>
                                </span>
                            </div>
                            <img class="direct-chat-img" src="data:image/jpeg;base64,<?= createDefaultAvatar($mov['mhfrom'] ) ?>" alt="Message User Image">
                            <div class="direct-chat-text">
                                <?= $mov['mhinfo'] ?>
                            </div>

                        </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Priority Change Modal -->
<div class="modal" tabindex="-1" role="dialog" id="id_tickets_modal_changepriority">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('tickets.modal_changepriority_title') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open($_formview_action,['id'=>'id_tickets_modal_changepriority_form'],['ticket_id'=>$record['tiid']])?>
                <div class="form-group" id="id_tickets_modal_changepriority_value_field">
                    <label for="id_tickets_modal_changepriority_value" class="mr-2">
                        <?= lang('tickets.tck_status') ?>
                    </label>
                    <?= form_dropdown('priority', $ticket_priority, [$record['tck_priority']], ['id'=>'id_tickets_modal_changepriority_value','class'=>'form-control form-control-sm']); ?>
                    
 		</div>
                <div class="form-group" id="id_tickets_modal_changepriority_comment_field">
                    <label for="id_tickets_modal_changepriority_comment" class="mr-2">
                        <?= lang('tickets.modal_comment') ?>
                    </label>
                    <textarea class="form-control form-control-sm" id="id_tickets_modal_changepriority_comment" name="comment"></textarea>
 		</div>
                <?= form_close() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm  btn-danger mr-auto" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel') ?>
                </button>
                <button type="submit" form="id_tickets_modal_changepriority_form" class="btn btn-sm btn-success">
                    <i class="far fa-save mr-1"></i><?= lang('system.buttons.submit') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- /Priority Change Modal -->

<!-- Comment Modal -->
<div class="modal" tabindex="-1" role="dialog" id="id_tickets_modal_comment">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('tickets.btn_addcomm') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open($commendaddurl,['id'=>'id_tickets_modal_comment_form'],['ticket_id'=>$record['tiid'],'_only_comment'=>TRUE])?>
                <div class="form-group" id="id_tickets_modal_comment_input_field">
                    <label for="id_tickets_modal_comment_input" class="mr-2">
                        <?= lang('tickets.modal_comment') ?>
                    </label>
                    <textarea class="form-control form-control-sm" id="id_tickets_modal_comment_input" name="comment"></textarea>
 		</div>
                <?= form_close() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm  btn-danger mr-auto" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel') ?>
                </button>
                <button type="submit" onclick="addLoader('#id_tickets_modal_comment .modal-body');$('#id_tickets_modal_comment_form').submit()" class="btn btn-sm btn-success">
                    <i class="far fa-save mr-1"></i><?= lang('system.buttons.submit') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- /Comment Modal -->

<!-- Status Change Modal -->
<div class="modal" tabindex="-1" role="dialog" id="id_tickets_modal_">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">M<?= lang('tickets.') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open($_formview_action,['id'=>''],['ticket_id'=>$record['tiid']])?>
                <?= form_close() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm  btn-danger mr-auto" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.cancel') ?>
                </button>
                <button type="submit" form="" class="btn btn-sm btn-success">
                    <i class="far fa-save mr-1"></i><?= lang('system.buttons.submit') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- /Status Change Modal -->
<script>
    $('.modal').on('hide.bs.modal',function(){
        $(this).find('.modal-body').find('input').val('');
        $(this).find('.modal-body').find('textarea').val('');
        $(this).find('.modal-body').find('[name="status"').remove();
    });
    $('[data-url]').on('click',function(){
        addLoader('.container');
        if ($(this).attr('data-status')!=undefined){
            $('#id_tickets_modal_comment').find('[name="ticket_id"]').after('<input type="hidden" name="status" value="'+$(this).attr('data-status')+'">');
            $('#id_tickets_modal_comment').modal('show');
        }else{
            window.location=$(this).attr('data-url');
        }
    });
</script>