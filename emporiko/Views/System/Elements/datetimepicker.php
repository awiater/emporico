<div class="input-group">
    <input type="hidden"  id="<?= $id ?>_readonly">
    <input type="hidden" value="<?= $value ?>" id="<?= $id ?>_value">
    <input type="hidden" name="<?= $name ?>" value="<?= $value ?>" id="<?= $id ?>">
    
    <div class="form-control" id="<?= $id ?>_viewer"></div>
    
    <div class="input-group-append">
        <span class="input-group-text btn" id="<?= $id ?>_icon" onclick="$('#<?= $id ?>_readonly').datepicker('show');">
            <i class="<?= $icon ?>"></i>
        </span>
        <?php if (!empty($_timevalue)) :?>
        <span class="input-group-text btn" type="button" data-toggle="collapse" data-target="#<?= $id ?>_collapse" aria-expanded="false" aria-controls="<?= $id ?>_collapse">
            <i class="far fa-clock"></i>
        </span>
        <?php endif ?>
    </div>
</div>
<?php if (!empty($_timevalue)) :?>
    <div class="collapse" id="<?= $id ?>_collapse">
        <div class="card mt-1" style="width: 12rem;">
            <div class="card-body">
                <div class="row">
                    <input type="number" class="form-control form-control-sm" value="<?= is_array($_timevalue) ? $_timevalue[0] : '' ?>" style="width:50px" min="0" max="23" id="<?= $id ?>_timer_hr" placeholder="H" onchange="if(parseInt(this.value,10)<10)this.value='0'+this.value;" >&nbsp;:&nbsp;
                    <input type="number" class="form-control form-control-sm" value="<?= is_array($_timevalue) ? $_timevalue[1] : '' ?>" style="width:50px" min="0" max="59" id="<?= $id ?>_timer_min" placeholder="M" onchange="if(parseInt(this.value,10)<10)this.value='0'+this.value;" >
                    <button type="button" class="btn btn-primary btn-sm ml-2" id="<?= $id ?>_timer_okbtn">
                        <i class="fas fa-arrow-circle-up"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>
<script>
    $(function(){
        $("#<?= $id ?>_readonly").datepicker({ dateFormat: "<?= $_viewformat ?>"});
        $("#<?= $id ?>_value").datepicker({ dateFormat: "<?= $_dbwformat ?>" });
        $("#<?= $id ?>_value").datepicker("setDate","<?= convertDate($value, null, 'Ymd') ?>");
        
        <?php if (!empty($value_view)) :?>   
        $("#<?= $id ?>_readonly").datepicker("setDate","<?= $value_view ?>");
        <?php endif ?>
        $("#<?= $id ?>").datepicker("setDate",$("#<?= $id ?>_value").datepicker("getDate"));
        $("#<?= $id ?>_icon").on("click",function(){$("#<?= $id ?>").datepicker("show");});
        <?php if (!empty($minDate)) :?>
        $('#<?= $id ?>').datepicker('option', 'minDate',new Date('<?= $minDate ?>'));
        <?php endif ?>
        <?php if (!empty($maxDate)) :?>
        $('#<?= $id ?>').datepicker('option', 'maxDate',new Date('<?= $maxDate ?>'));
        <?php endif ?>
        $("#<?= $id ?>_readonly").on("change",function(){
            $("#<?= $id ?>_value").datepicker("setDate",$(this).datepicker("getDate"));
            <?php if (!empty($_timevalue)) :?>
            var time=$("#<?= $id ?>_timer_hr").val()+$("#<?= $id ?>_timer_min").val();
            <?php else :?>
            var time='0000';    
            <?php endif ?>
            var view=$(this).val().replace('[t]',time);
            if (time.length < 4){
                time='0000';
            }
            $("#<?= $id ?>").val($("#<?= $id ?>_value").val()+time);
            $("#<?= $id ?>_viewer").text(view);
        });
        $("#<?= $id ?>_readonly").trigger("change");
        
        <?php if (!empty($_timevalue)) :?>
        $('#<?= $id ?>_collapse input[type="number"]').on('keydown',function(e){
             e.preventDefault();
        });
        $('#<?= $id ?>_collapse input[type="number"]').on('change',function(){
            $("#<?= $id ?>_readonly").trigger("change");
        })
        $('#<?= $id ?>_timer_okbtn').on('click',function(){
            $('#<?= $id ?>_collapse').collapse('hide');
        });
        <?php endif ?>
        
        
});
</script>