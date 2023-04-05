<?= $currentView->IncludeView('System/form') ?>

<div id="id_picktime_group">
    <div class="custom-control custom-radio" id="id_radio_picktime_spec_grp">
        <input type="radio" id="id_radio_picktime_spec" class="custom-control-input" checked>
        <label class="custom-control-label" for="id_radio_picktime_spec"><?= lang('crontab.picktime_spec') ?></label>
        <div class="d-flex">
            <input type="number" style="width:70px" name="hour" placeholder="h" autocomplete="off" class="form-control" min="0" max="23" > : 
            <input type="number" style="width:70px" name="minute" placeholder="m" autocomplete="off" class="form-control" min="0" max="59">
        </div>
    </div><br>
    <div class="custom-control custom-radio" id="id_radio_picktime_hours_grp">
        <input type="radio" id="id_radio_picktime_hours" class="custom-control-input">
        <label class="custom-control-label" for="id_radio_picktime_hours"><?= lang('crontab.picktime_hours') ?></label>
        <div class="d-flex">
            <input type="number" style="width:70px" name="step_hour" placeholder="h" autocomplete="off" class="form-control" min="0" max="23" value="1"> : 
            <input type="number" style="width:70px" name="step_minute" placeholder="m" autocomplete="off" class="form-control" min="0" max="59" value="00">
        </div>
    </div><br>
    <div class="custom-control custom-radio" id="id_radio_picktime_minutes_grp">
        <input type="radio" id="id_radio_picktime_minutes" class="custom-control-input">
        <label class="custom-control-label" for="id_radio_picktime_minutes"><?= lang('crontab.picktime_minutes') ?></label>
        <div class="d-flex">
            <input type="number" style="width:70px" name="step_minute" placeholder="m" autocomplete="off" class="form-control" min="0" max="59">
        </div>
    </div>
</div>
<input type="number" style="width:70px" name="days" id="id_repeat_yearly_days" autocomplete="off" class="form-control" min="1" max="31">
<script>
    $(function(){
        $('#id_command, #id_radio_picktime_spec, #id_patern').trigger('change');
        $('#id_picktime').attr('class','bg-white').html('');
        $('#id_picktime_group').detach().appendTo('#id_picktime');
        $('#id_repeat_yearly').attr('id','id_repeat_yearly_month').addClass('mr-2').wrap('<div id="id_repeat_yearly_input" class="d-flex">');
        $('#id_repeat_yearly_days').detach().appendTo('#id_repeat_yearly_input');
    });
    
    $('#id_patern').on('change',function(){
        var val=$('#id_patern option:selected').val();
        $('#id_repeat_monthly_field, #id_repeat_weekly_field, #id_repeat_yearly_field').addClass('d-none');
        if (val=='weekly'){
            $('#id_repeat_weekly_field').removeClass('d-none');
        }else
        if (val=='monthly'){
            $('#id_repeat_monthly_field').removeClass('d-none').find('#id_repeat_monthly').attr('name','days');
            $('#id_repeat_yearly_days').removeAttr('name');
        }else
        if (val=='yearly'){
            $('#id_repeat_yearly_field').removeClass('d-none').find('#id_repeat_yearly_days').attr('name','days');
            $('#id_repeat_monthly').removeAttr('name');
            $('#id_repeat_monthly_list').html('').addClass('d-none');
        }
    });
    
    $('#id_command').on('change',function(){
        if ($('#id_name').val().length < 2)
        {
            $('#id_name').val($('#id_command option:selected').text());
        }
    });
    
    $('[type="radio"]').on('change',function(){
        var radio=$(this).attr('id');
        $(this).parent().find('[type="number"]').removeAttr('disabled');
        $('[type="radio"]').each(function(){
            if ($(this).attr('id')!=radio){
               $(this).prop("checked", false);
               $(this).parent().find('[type="number"]').attr('disabled',true);
            }
        });
    });
</script>

