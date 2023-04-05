<div class="modal" tabindex="-1" role="dialog" id="id_caller_record_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('connections.caller_record_modal_title') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open($action_url, ['id'=>'id_caller_record_modal_form'], ['call_action'=>'call','call_target'=>$call_target]) ?>
                <div class="form-group">
                    <label for="id_caller_record_modal_nr">
                        <?= lang('connections.call_number')?>
                    </label>
                    <?php if (!empty($caller_number) && is_array($caller_number)) :?>
                    <?= form_dropdown('caller_number', $caller_number, [], ['class'=>'form-control','id'=>'id_caller_record_modal_nr']) ?>
                    <?php else :?>
                    <input type="text" class="form-control" id="id_caller_record_modal_nr" name="caller_number" value="<?= !empty($caller_number) ? $caller_number : '' ?>">
                    <?php endif ?>
                </div>
                <div class="form-group">
                    <label for="id_caller_record_modal_info">
                        <?= lang('connections.call_info')?>
                    </label>
                    <textarea class="form-control" id="id_caller_record_modal_info" name="call_info"></textarea>
                </div>
                <?= form_close() ?>
            </div>
            <div class="modal-footer">
                <div class="mr-auto">
                    <button type="button" class="btn btn-sm btn-info" onclick="caller_record_modal_action('record')">
                        <i class="fas fa-file-medical-alt mr-1"></i><?= lang('connections.call_recordbtn') ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="caller_record_modal_action('call')">
                        <i class="fas fa-phone-volume mr-1"></i><?= lang('connections.call_makebtn') ?>
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">
                    <i class="fas fa-ban mr-1"></i><?= lang('system.buttons.close') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        <?php if (!empty($initcall)) :?>
        window.location='tel:<?= $initcall ?>';
        <?php endif ?>
    });
    <?php if (!empty($call_modalinit) && $call_modalinit) :?>
    $('[<?= $call_modalinit ?>]').on('click',function(){
        $('#id_caller_record_modal_nr').val($(this).attr('data-phone'));
        if ($(this).attr('data-phonedisable')!=undefined){
            $('#id_caller_record_modal_nr').attr('disabled','true');
        }else{
            $('#id_caller_record_modal_nr').removeAttr('disabled');
        }
        $('#id_caller_record_modal').modal('show');
    });
    <?php endif ?>
    function caller_record_modal_action(action){
        $('[name="call_action"]').val(action);
        $('#id_caller_record_modal_form').submit();
    }
</script>