<div>
<?php if($viewer) :?>
<div class="d-flex">  
    <div id="<?= $id.'_preview_container' ?>">  
        <img src="<?=$value?>" class="mw-100 <?= $noImage ? 'd-none' : ''?>" id="<?= $id.'_preview' ?>" style="<?= !empty($maxwidth) ? 'max-width:'.$maxwidth.';': ''?>width:<?=$width?>;height:<?=$height?>;cursor:pointer;">
    </div> 
    <label class="" for="<?= $id.'_file' ?>">
        <div class="rounded bg-light border d-flex" style="<?= !empty($maxwidth) ? 'max-width:'.$maxwidth.';': ''?>width:<?=$width?>;height:<?=$height?>;cursor:pointer;">
            <i class="fas fa-plus mx-auto my-auto"></i>
        </div>
    </label >
</div>
<div class="modal" tabindex="-1" id="<?= $id.'_preview_fullmodal' ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body"></div>    
        <?php if(!empty($viewer_print)||!empty($viewer_download)) :?>
            <div class="modal-footer">
                <?php if(!empty($viewer_print)) :?>
                <button type="button" class="btn btn-sm btn-secondary viewer_print_btn" onclick="<?= $id?>_Print()" data-toggle="tooltip" data-placement="left" title="<?= lang('system.general.imagepicker_viewer_print_tooltip') ?>">
                    <i class="fas fa-print"></i>
                </button>
                <?php endif ?>
                <?php if(!empty($viewer_download)) :?>
                <a download="image" href="<?=$value?>" class="btn btn-sm btn-info viewer_download_btn" data-toggle="tooltip" data-placement="left" title="<?= lang('system.general.imagepicker_viewer_download_tooltip') ?>">
                    <i class="fas fa-download"></i>
                </a>
                <?php endif ?>
            </div>
        <?php endif ?>
        </div>
    </div>
</div>
<?php else :?>    
<label<?= $_direct ? ' for="'.$id.'_file"' : ' data-toggle="modal" data-target="#'.$id.'_pickermodal'.'"' ?>>
    <div id="<?= $id.'_imgbtn' ?>" class="rounded bg-light border d-flex imgthumb" style="min-width:150px;<?= !empty($maxwidth) ? 'max-width:'.$maxwidth.';': ''?>width:<?=$width?>;height:<?=$height?>;<?= (!empty($readonly) && !$readonly) || empty($readonly) ? 'cursor:pointer;' : '' ?>">
        <img src="<?=$value?>" class="<?= $noImage ? 'd-none' : ''?>" id="<?= $id.'_preview' ?>">
        <i class="fas fa-plus mx-auto my-auto <?= !$noImage ? 'd-none':''?>"></i>
    </div>
</label >
<?php endif ?>

<?php if (!empty($readonly) && $readonly) :?><?php else :?>
<input type="file" id="<?= $id.'_file' ?>" class="d-none"<?= !empty($format) ? ' accept="'.$format.'"' : ''?> name="<?= $name ?>">
<?php endif ?>
<?php if (!empty($_export_justname)) :?>
<?= form_hidden('_export_justname','1') ?>
<?php endif ?>
<?php if (!empty($_uploads_dir)) :?>
<?= form_hidden('_uploads_dir',$_uploads_dir) ?>
<?php endif ?>
<?php if (!empty($upload_filename)) :?>
<?= form_hidden('_upload_filename',$upload_filename) ?>
<?php endif ?>
 
</div>
<?php if (!$_direct) :?>
<div class="modal" tabindex="-1" role="dialog" id="<?= $id.'_pickermodal' ?>">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header p-1">
                <h5 class="modal-title"><?= lang('system.general.image_picker_modal_title') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-5">
                        <button type="button" class="btn btn-sm btn-default" id="<?= $id.'_pickermodal_btngoup' ?>" onclick="fetchFiles('')" disabled="true">
                            <i class="fas fa-level-up-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary ml-2" id="<?= $id.'_pickermodal_btnupload' ?>" data-dir="<?= $_uploads_dir?>">
                            <i class="fas fa-upload"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger ml-1" id="<?= $id.'_pickermodal_btnremove' ?>" data-dir="<?= $_uploads_dir?>">
                            <i class="far fa-trash-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" data-toggle="collapse" data-target="#<?= $id.'_pickermodal_btnnewdiv' ?>" aria-expanded="false" aria-controls="<?= $id.'_pickermodal_btnnewdiv' ?>">
                            <i class="fas fa-folder-plus"></i>
                        </button>
                        <div class="collapse" id="<?= $id.'_pickermodal_btnnewdiv' ?>">
                            <div class="card card-body mt-1">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm" id="<?= $id.'_pickermodal_btnnewdivinput' ?>">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-sm btn-primary" id="<?= $id.'_pickermodal_btnnew' ?>">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row mt-2" id="<?= $id.'_pickermodal_files' ?>" style="max-height:420px;overflow-y: scroll;">
                    <?php foreach($_files as $_file) :?>
                    <div class="col-3 text-center">
                        <?php if ($_file['type']=='dir') :?>
                        <div class="imgthumb">
                            <button type="button" class="btn btn-link p-0 text-warning mb-1" onclick="fetchFiles('<?= $_file['path']?>')">
                                <i class="fas fa-folder fa-5x"></i>
                            </button>
                        </div>    
                        <small>
                            <input type="checkbox" name="<?= $id.'_pickermodal_files[]' ?>" class="mr-2" value="<?= $_file['path']?>"><?= $_file['file']?>
                        </small>
                        <?php else :?>
                        <a href="#" onclick="getImagePath($(this))" data-source="<?= parsePath($_file['path'])?>" class="imgthumb" data-imagepickeritem="<?= $_file['path']?>">
                            <img src="<?= parsePath($_file['path'])?>" alt="<?= $_file['file']?>" title="<?= $_file['file']?>">
                        </a>
                        <label class="mb-1">
                            <div class="text-wrap" style="width: 100px;">
                                <small>
                                    <input type="checkbox" name="<?= $id.'_pickermodal_files[]' ?>" class="mr-2" value="<?= $_file['path']?>"><?= $_file['file']?>
                                </small>
                            </div>
                        </label>
                        <?php endif ?>
                    </div>
                    <?php endforeach;?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif ?>
<script>
    $("#<?= $id?>_file").on('change',function(){
        $("#<?= $id?>_preview").attr('src',window.URL.createObjectURL(this.files[0])).removeClass('d-none').parent().find('i').addClass('d-none');
    });
    <?php if($viewer) :?>
    $("#<?= $id.'_preview' ?>").on('click',function(){
        var img=$('#<?= $id.'_preview_full' ?>');
        var modal=$('#<?= $id.'_preview_fullmodal' ?>');
        modal.find('.modal-body').html('');
        if (img.attr('id')!='<?= $id.'_preview_full' ?>'){     
            img=$('#<?= $id.'_preview' ?>').clone().appendTo(modal.find('.modal-body'));
            img.css({'width':'100%','height':''}).on('click',function(){
                $('#<?= $id.'_preview_fullmodal' ?>').modal('hide');
            });
            modal.modal('show');
        }
    });
    $('#<?= $id.'_preview_fullmodal' ?>').on('hide.bs.modal', function (e) {
        $(this).find('.modal-body').html('');
    });
    
    
    <?php if(!empty($viewer_print)) :?>
    function <?= $id?>_Print()
    {
        $('#<?= $id.'_preview_fullmodal' ?>').find('img').printThis({
            debug: false,              
            importCSS: false,             
            importStyle: false,         
            printContainer: true,       
            pageTitle: "My Modal",             
            removeInline: false,        
            printDelay: 133,            
            header: null,             
            formValues: true 
        });
    }
    <?php endif ?>
    <?php endif ?>
    <?php if (!$_direct) :?>
        function getImagePath(obj){
             $('#<?= $id.'_preview' ?>').attr('src',obj.attr('data-source'));
             $('#<?= $id.'_pickermodal' ?>').modal('hide');
             $('#<?= $id.'_file' ?>').attr('type','text').val(obj.attr('data-imagepickeritem'));
             return false;
        };
        
        $('#<?= $id.'_pickermodal_btnupload' ?>').on('click',function(){
            var dir=$(this).attr('data-dir');
            var html='<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="form_upload_file" value="" />';
            html+='<input type="hidden" value="'+dir+'" name="_uploads_dir">';
            html+='<input type="hidden" value=".jpg,.jpeg,.bmp,.gif,.png,.ico" name="_filters">';
            html+='<input type="hidden" value="Documents::upload" name="_command">';
            <?php if (!empty($_export_justname)) :?>
            html+='<input type="hidden" value="1" name="_export_justname">';
            <?php endif ?>
            <?php if (!empty($_upload_filename)) :?>
            html+='<input type="hidden" value="<?= $_upload_filename?>" name="_upload_filename">';
            <?php endif ?>
            html+='</form>';
            $('body').prepend(html);
            $('#form-upload input[name=\'form_upload_file\']').trigger('click');
            
            if (typeof timer != 'undefined') {
    	clearInterval(timer);
	}

	timer = setInterval(function() {
		if ($('#form-upload input[name=\'form_upload_file\']').val() != '') {
			clearInterval(timer);

			$.ajax({
				url: '<?=url('Api','post')?>',
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {},
				complete: function() {
					$('#form-upload').remove();
				},
				success: function(data){
					if ('error' in data){
                                            Dialog(data['error'],'warning');
                                        }
                                        console.log(data);
                                        if ('files' in data){
                                            listFilesTiles(data['files'],data['_uploads_dir'],data['parent']);
                                        }
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
        
            
        });
        
        function fetchFiles(dir){
            callApi({'_uploads_dir':dir,'_command':'list','_filters':'images'});
        }
        
        $('#<?= $id.'_pickermodal_btnremove' ?>').on('click',function(){
            ConfirmDialog('<?= lang('system.general.msg_delete_ques')?>',function(){
                var files=[];
                $('[name="<?= $id.'_pickermodal_files[]' ?>"]').each(function(){
                    if ($(this).is(':checked')){
                        files.push($(this).val());
                    }
                    callApi({'_uploads_dir':$('#<?= $id.'_pickermodal_btnupload' ?>').attr('data-dir'),'_command':'remove','_filters':'images','_files':files});
                });
            });
        });
        
        $('#<?= $id.'_pickermodal_btnnew' ?>').on('click',function(){
            callApi({'_uploads_dir':$('#<?= $id.'_pickermodal_btnupload' ?>').attr('data-dir'),'_command':'new','_filters':'images','_folder':$('#<?= $id.'_pickermodal_btnnewdivinput' ?>').val()});
            $('#<?= $id.'_pickermodal_btnnewdivinput' ?>').val('');
            $('#<?= $id.'_pickermodal_btnnewdiv' ?>').collapse('hide');
        });
        
        function callApi(command){
            command['_command']='Documents::'+command['_command'];
            addLoader('#<?= $id.'_pickermodal' ?>.modal-body');
            $.ajax({
                url: '<?=url('Api','post')?>',
                type: 'post',
				dataType: 'json',
				data:command ,
                                completed:function(){killLoader();},
				success: function(data){
					if ('error' in data){
                                            Dialog(data['error'],'warning');
                                        }
                                        console.log(data);
                                        if ('files' in data){
                                            listFilesTiles(data['files'],data['_uploads_dir'],data['parent']);
                                        }
                                        
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
        }
        
        function listFilesTiles(data,dir,pdir){
            $('#<?= $id.'_pickermodal_btngoup' ?>').removeAttr('disabled');
            var master='<?= $_start_dir ?>';
            $('#<?= $id.'_pickermodal_btngoup' ?>').attr('onclick',"fetchFiles('"+pdir+"')");
            $('#<?= $id.'_pickermodal_btnupload' ?>').attr('data-dir',dir);
            if(dir==master){
               $('#<?= $id.'_pickermodal_btngoup' ?>').attr('disabled','true'); 
            }
            var html=''; 
            $.each(data, function(key,file) {
                html+='<div class="col-3 text-center">';
                if (file['type']=='dir'){
                    html+='<div class="imgthumb"><button type="button" class="btn btn-link p-0 text-warning mb-1" onclick="fetchFiles(';
                    html+="'"+file['path']+"')"+'">';
                    html+='<i class="fas fa-folder fa-5x"></i></button></div><small>';
                    html+='<input type="checkbox" name="<?= $id.'_pickermodal_files[]' ?>" class="mr-2" value="'+file['path']+'">'+file['file']+'</small>';
                }else{
                    html+='<a href="#" data-source="'+file['url']+'" class="imgthumb" onclick="getImagePath($(this))" data-imagepickeritem="'+file['path']+'">';
                    html+='<img src="'+file['url']+'" alt="'+file['file']+'" title="'+file['file']+'">';
                    html+='</a><label class="mb-1">';
                    html+='<div class="text-wrap" style="width: 100px;"><small>';
                    html+='<input type="checkbox" name="<?= $id.'_pickermodal_files[]' ?>" class="mr-2" value="'+file['path']+'">'+file['file']+'</small></div></label>';
                }
                html+='</div>';
            });
            $('#<?= $id.'_pickermodal_files' ?>').html(html);
            
        }
    <?php endif ?>    
</script>