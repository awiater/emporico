<div class="input-group">
    <input type="text" class="form-control" name="<?= $args['name']?>" id="<?= $args['id']?>" readonly="true" style="background-color: #FFF!important">
    <div class="input-group-append">
        <button class="btn btn-secondary" id="<?= $args['id']?>_button" onClick="showFilePickerWindow()">
            <i class="far fa-folder-open"></i>
        </button>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="<?= $args['id']?>_window">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('system.buttons.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row" style="max-height:420px;overflow-y: scroll;" id="<?= $args['id']?>_filescontainer">
                   
                </div>
            </div>          
        </div>
    </div>
</div>



<script>

var filePickerFolderTile='<div class="col-3" id="<?= $args['id']?>_foldertile"><button class="btn btn-link w-100"><div class="card"><i class="fas fa-folder text-warning fa-3x card-img-top"></i><div class="card-body"></div></div> </button></div>';
var filePickerFileTile='<div class="col-3" id="<?= $args['id']?>_filetile"><button class="btn btn-link w-100"><div class="card"><i class="fas fa-file fa-3x card-img-top"></i><div class="card-body"></div></div></button></div>';

function showFilePickerWindow(){
    //filePickerAllFiles=JSON.parse(atob('<?= base64_encode(json_encode($args['dir_list']))?>'));
    //filePickerCurrFolder=filePickerAllFiles;
    FilePickerDrawTiles('.',true);
    
}
function FilePickerDrawTiles(source,shomodal=false){
    ajaxCall(
            '<?= url('Api','Documents',['getfilelist']) ?>',
            {
                '_uploads_dir':source
            },
            function(data){
                console.log(data);
                if ('list' in data){
                    if (shomodal){
                        $('#<?= $args['id']?>_window').modal('show');
                    }
                    $('#<?= $args['id']?>_filescontainer').html('');
                    if (!data['master_folder']){
                        var tile=$(filePickerFolderTile);
                        tile.find('.card-body').text('..');
                        tile.find('button').attr('onclick',"FilePickerDrawTiles('"+data['parent_dir']+"')");
                        $('#<?= $args['id']?>_filescontainer').append(tile);
                    }
                    $.each(data['list'],function(index,value){
                        if ($.isNumeric(index)){
                            var tile=$(filePickerFileTile);
                            tile.find('.card-body').text(value);
                            tile.find('button').attr('onclick',"FilePickerGetTilePath('"+value+"','"+data['current_dir']+"')");
                            $('#<?= $args['id']?>_filescontainer').append(tile);
                        }else{
                            var tile=$(filePickerFolderTile);
                            tile.find('.card-body').text(index.substring(0,index.length-1));
                            tile.find('button').attr('onclick',"FilePickerDrawTiles('"+data['parent_dir']+'/'+index+"')");
                            $('#<?= $args['id']?>_filescontainer').append(tile);
                        }
                    });
                }
            },
            function(data){console.log(data);},
            'POST'
    );
}
function FilePickerWdrawTiles1(source){
    $('#<?= $args['id']?>_filescontainer').html('');
    if (source=='..'){
        filePickerCurrFolder=filePickerLastFolder;
    }else
    if (source=='.'){
        filePickerCurrFolder=filePickerAllFiles;
        filePickerLastFolder=filePickerAllFiles;
    }else
    if (source in filePickerCurrFolder){
        filePickerLastFolder=filePickerCurrFolder;
        filePickerCurrFolder=filePickerCurrFolder[source];
    }

    if (source!='.'){
        var tile=$(filePickerFolderTile);
        tile.find('.card-body').text('..');
        if (filePickerLastFolder==filePickerCurrFolder){
            tile.find('button').attr('onclick',"FilePickerWdrawTiles('.')");
        }else{
            tile.find('button').attr('onclick',"FilePickerWdrawTiles('..')");
        }
        $('#<?= $args['id']?>_filescontainer').append(tile);
    }
    if ($.map(filePickerCurrFolder, function(n, i) { return i; }).length > 0){
        $.each(filePickerCurrFolder,function(index,value){
            if (index.substring(index.length-1)!='/'){
                var tile=$(filePickerFileTile);
                tile.find('.card-body').text(value['file']);
                tile.find('button').attr('onclick',"FilePickerWdrawTiles('"+value+"')");
                $('#<?= $args['id']?>_filescontainer').append(tile);
                
            }else{
                var tile=$(filePickerFolderTile);
                tile.find('.card-body').text(index.substring(0,index.length-1));
                tile.find('button').attr('onclick',"FilePickerWdrawTiles('"+index+"')");
                $('#<?= $args['id']?>_filescontainer').append(tile);
            }
        });
    }
}
</script>
<?= dump($args['dir_list'],FALSE) ?>