<?php if (empty($_brand_tile_nocard)||(!empty($_brand_tile_nocard) && !$_brand_tile_nocard)) :?>
<div class="card card-widget widget-user-2">
    <div class="widget-user-header bg-danger">
        <h3 class="card-title"><?= lang('products.brand_update_widget_title') ?></h3>
    </div>
    <div class="card-footer">
<?php endif ?>
    <div class="form-group" id="brand_update_widget_brands_field">
                    <label for="brand_update_widget_brands" class="mr-2">
                        <?= lang('products.brand_update_widget_brand') ?>
                    </label>
                    <?= form_dropdown('brand', $brands_dropdown, [], ['class'=>'form-control select2','id'=>'brand_update_widget_brands_list']) ?>
                    <small id="brand_update_widget_brands_tooltip" class="form-text text-muted">
                        
                    </small>
 		</div>
                <div class="form-group">
                    <label for="brand_update_widget_date_text"><?= lang('products.brand_update_widget_date') ?></label>
                    <div class="input-group">
                        <div id="brand_update_widget_date_text" class="form-control"></div>
                        <input type="hidden" value="<?= formatDate() ?>" id="brand_update_widget_date">
                        <input type="hidden" name="date" value="<?= formatDate() ?>" id="brand_update_widget_date_value">
                        <div class="input-group-append">
                            <span class="input-group-text btn" id="brand_update_widget_date_icon">
                                <i class="far fa-calendar-alt"></i>
                            </span>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                            <?= lang('products.brand_update_widget_date_tooltip') ?>
                    </small>
                </div>
                
 <?php if (empty($_brand_tile_nocard)||(!empty($_brand_tile_nocard) && !$_brand_tile_nocard)) :?>
        <div class="form-group">
            <button type="button" id="brand_update_widget_save" class="btn btn-sm btn-success float-right">
                <i class="far fa-save mr-1"></i><?= lang('system.buttons.save') ?>
            </button>
        </div>
    </div>
</div>
<?php endif ?>
    <script>
        <?php if (empty($_brand_tile_nocard)||(!empty($_brand_tile_nocard) && !$_brand_tile_nocard)) :?>
        $("#brand_update_widget_save").on('click',function(){
            var url='<?= $url ?>';
            if ($("#brand_update_widget_brands_list").find(':selected').val()==undefined){
                Dialog('<?= lang('products.error_brandtile_nobrand') ?>','warning');
            }else{
                if ($("#brand_update_widget_date_value").attr('readonly')==undefined){
                    url=url.replace('-date-',$("#brand_update_widget_date_value").val());
                }else{
                    url=url.replace('&date=-date-',''); 
                }
            
                url=url.replace('-id-',$("#brand_update_widget_brands_list").find(':selected').val());
            }
            window.location=url;
        });
        $(function(){
            $('#brand_update_widget_brands_list').select2({theme: 'bootstrap4'});
            $('input[data-for]').trigger('change');
        });
        <?php else :?>
        $(function(){
            $('input[data-for]').trigger('change');
        });
        <?php endif ?>    
        $("#brand_update_widget_date").datepicker({ dateFormat: "dd M yy" });
        $("#brand_update_widget_date_value").datepicker({ dateFormat: "yymmdd0000" });
        $("#brand_update_widget_date_value").datepicker("setDate","<?= formatDate() ?>");
        $("#brand_update_widget_date").datepicker("setDate",$("#brand_update_widget_date_value").datepicker("getDate"));
        $("#brand_update_widget_date_icon").on("click",function(){$("#brand_update_widget_date").datepicker("show");});
        $("#brand_update_widget_date").on("change",function(){
            $("#brand_update_widget_date_value").datepicker("setDate",$(this).datepicker("getDate"));
            $("#brand_update_widget_date_text").text($('#brand_update_widget_date').val());
        });
        $("#brand_update_widget_date_text").text($('#brand_update_widget_date').val());
        
        $("#brand_update_widget_fdate").datepicker({ dateFormat: "dd M yy" });
        $("#brand_update_widget_fdate_value").datepicker({ dateFormat: "yymmdd0000" });
        $("#brand_update_widget_fdate_value").datepicker("setDate","<?= formatDate() ?>");
        $("#brand_update_widget_fdate").datepicker("setDate",$("#brand_update_widget_fdate_value").datepicker("getDate"));
        $("#brand_update_widget_fdate_icon").on("click",function(){$("#brand_update_widget_fdate").datepicker("show");});
        $("#brand_update_widget_fdate").on("change",function(){
            $("#brand_update_widget_fdate_value").datepicker("setDate",$(this).datepicker("getDate"));
            $("#brand_update_widget_fdate_text").text($('#brand_update_widget_fdate').val());
        });
        $("#brand_update_widget_fdate_text").text($('#brand_update_widget_fdate').val());
        
        $('input[data-for]').on('change',function(){
            var id=$(this).attr('data-for');
            
            if (!this.checked) {
                $('#'+id+'_value').attr('readonly',true);
                $('#'+id+'_text').addClass('input-group-text');
                $('#'+id+'_icon').addClass('d-none');
            }else{
               $('#'+id+'_value').removeAttr('readonly');
               $('#'+id+'_text').removeClass('input-group-text');
               $('#'+id+'_icon').removeClass('d-none');
            }
        });
    </script>