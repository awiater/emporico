<div class="input-group">
    <input type="hidden" name="<?= $name ?>" value="<?= $value ?>" id="<?= $id ?>_value">
    <div class="form-control" id="<?= $id ?>_viewer"><?= !empty($value_view) ? $value_view: ''?></div>
    <div class="input-group-append">
        <span class="input-group-text btn" type="button" data-toggle="collapse" data-target="#<?= $id ?>_collapse" aria-expanded="false" aria-controls="<?= $id ?>_collapse">
            <i class="<?= $icon ?>"></i>
        </span>
    </div>
</div>
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
<script>
    $(function(){
        $('#<?= $id ?>_collapse input[type="number"]').on('change',function(){
           $('#<?= $id ?>_viewer').text($("#<?= $id ?>_timer_hr").val()+' : '+$("#<?= $id ?>_timer_min").val());
           $('#<?= $id ?>_value').val($("#<?= $id ?>_timer_hr").val()+$("#<?= $id ?>_timer_min").val()); 
        });
        
        $('#<?= $id ?>_collapse input[type="number"]').on('keydown',function(e){
             e.preventDefault();
        });
        
        $('#<?= $id ?>_timer_okbtn').on('click',function(){
            $('#<?= $id ?>_collapse').collapse('hide');
        });
    });
</script>