<?php if(!empty($_formvalidation)) :?> 
<?php if(empty($_formvalidationNoScript)) :?> 
<script>
<?php endif ?>
function <?= str_replace([' ','-'], '', $_formview_action_attr['id']) ?>Validate(func=null){
    <?php if(is_array($_formvalidation) && array_key_exists('url', $_formvalidation)) :?> 
    var data = $('#<?= $_formview_action_attr['id']?>').serializeArray().reduce(function(obj, item) {
        obj[item.name] = item.value;
        return obj;
    }, {});
    
        $('input').removeClass('border border-danger');
        ajaxCall('<?= $_formvalidation['url'] ?>',data
        ,function(data){
            if ('error' in data){
                $.each(data['error'],function(k,v){
                    var object=$('[name="'+k+'"]');
                    object.addClass('border border-danger');
                    
                    if (object.attr('id')!=undefined){
                        $('#'+object.attr('id')+'_tooltip').addClass('text-danger').removeClass('text-muted').text(v);
                    }
                    if (object.attr('tab_name')!=undefined){
                        $('a[href="#tabs-'+object.attr('tab_name')+'"]').click();
                    }
                });
                Dialog('<?= lang('system.general.msg_validation_error') ?>','warning');
            }else{
                if (func && typeof(func) == 'function'){
                    func();
                }
            }
        }
        ,function(data){console.log(data);});
<?php else :?>
var submit=false;
if ($('#<?= $_formview_action_attr['id']?>').find('[required]').length < 1){
    submit=true;
}
$('#<?= $_formview_action_attr['id']?>').find('[required]').each(function(){
    $(this).removeClass('border border-danger');
    
    if ($(this).val().length < 1 || ($(this).attr('type')=='email' && !isEmail($(this).val()))){
       submit=false;
       $(this).addClass('border border-danger');
       return false;
    }else{
        submit=true;
    }
});

if (submit){
    if (func && typeof(func) == 'function'){
        func();
    }
}else{
    Dialog('<?= lang('system.general.msg_validation_error') ?>','warning');
    
}
<?php endif ?>
}  
<?php if(empty($_formvalidationNoScript)) :?> 
</script>
<?php endif ?>
<?php endif ?>
