<div class="container col-12">
    <div class="col-12 p-0">
        <?php if ($currentView->isMenuBarSet()) :?>
        <div class="breadcrumb p-1">
            <?= $currentView->getMenuBar() ?>
        </div>
        <?php endif ?>
    </div>
    <div class="row">
    <?php if (!empty($folders) && is_array($folders)) :?>
    <div class="col-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Folders</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-pills flex-column">
                    <?php foreach($folders as $key=>$folder) :?>
                    <li class="nav-item<?= $key==$curr_folder ? ' bg-gray' : '' ?>">
                        <button class="btn btn-link text-<?= $key==$curr_folder ? 'white' : 'dark' ?>"" data-url="<?= str_replace('-dir-', $key, $url_folder)?>" class="nav-link">
                            <i class="far fa-folder mr-1"></i><?= $folder ?>
                        </button>
                    </li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif ?>
    <div class="col-9">
        <div class="card card-info card-outline" id="emails_body_content">
        <?= $this->renderSection('_panel_content') ?>
        </div>
    </div>
    </div>
</div>
<script>
    $(function(){
        $('#menu').addClass('navbar-white bg-white col-12')
                  .removeClass('navbar-light bg-light');
        $('#id_filter').addClass('border-primary');
    });
   
    
    $('button[data-move]').on('click',function(){
        var id=$(this).attr('data-move');
        var url='<?= $move_url ?>';
        url=url.replace('-id-',id);
        url=url.replace('-folder-',$(this).parent().parent().find('select').find(':selected').val());
        addLoader('#emails_body_content');
        window.location=url;
    });
    
    $('button[data-delurl]').on('click',function(){
        var url=$(this).attr('data-delurl');
        var form=$(this).attr('form');
        ConfirmDialog('<?= lang('emails.msg_delete_ques')?>',function(){
            if (form==undefined){
                window.location=url;
            }else{
                $('#'+form).attr('action',url).submit();
            }
            
        });
    });
    
    $('button[data-submit]').on('click',function(){
        var url=$(this).attr('data-submit');
        var form=$(this).attr('form');
        addLoader('#emails_body_content');
        $('#'+form).attr('action',url).submit();
    });
</script>