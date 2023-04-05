<?= $currentView->includeView('System/form') ?>
<table class="table table-sm table-striped" id="id_campaigns_movements_tbl">
    <tbody>
        <?php foreach($record['_movements'] as $mov) :?>
        <tr>
            <td>
                <?= convertDate($mov['mhdate'],null,'d M Y H:i');?>
            </td>
            <td>
                <a href="<?php $mov['mhto']=parsePath($mov['mhto']);echo $mov['mhto']?>" class="btn btn-link p-0">
                    <?= $mov['mhto'] ?>
                </a>
            </td>
            <td>
                <?= $mov['mhinfo']?>
            </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
<ul class="list-group" id="id_ec_links_list_new" style="max-height:250px;overflow-y: scroll;">
    <?php $i=0;foreach($record['ec_links'] as $key=>$value) :?>
    <li class="list-group-item" id="id_ec_links_listitem_<?php $i++; echo $i; ?>">
        <div class="row">
            <div class="col-5"><?= $value?></div>
            <div class="col-5"><?= $key ?></div>
            <div class="col-2 text-right">
                <button type="button" class="btn btn-primary btn-sm ml-auto" onClick="copyToClipboard('<?= $key ?>')">
                    <i class="fas fa-copy"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm ml-auto" onClick="$('#id_ec_links_listitem_<?=$i ?>').remove()">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    </li>
    <?php endforeach;?>
</ul>
<?php if ($record['ec_type']=='paper') :?>
<div id="campaign_flyerdesigner"></div>
<?php endif ?>
<script>
    $(function(){
        $('#tabs-movements').append($('#id_campaigns_movements_tbl').detach());
        $('#id_ec_links_list').before($('#id_ec_links_list_new').detach());
        $('#id_ec_links_list').remove();
        $('#id_ec_links_list_new').attr('id','id_ec_links_list');
        <?php if ($record['ec_type']=='paper') :?>
        $('#tabs-wyswig').html($('#campaign_flyerdesigner').detach());
        <?php endif ?>
    });
    <?php if ($record['ec_type']=='paper') :?>
    const editor = grapesjs.init({
        container : '#campaign_flyerdesigner',
        autorender: 0,
        noticeOnUnload: 0,
        storageManager:false,
        fromElement: false,
        plugins: ['grapesjs-mjml'],
        canvas: {
            styles:[
                '<?= $currentView->getViewData('css.bootstrap')?>'
            ],
            scripts:[
                '<?= $currentView->getViewData('scripts.popper.src')?>',
                '<?= $currentView->getViewData('scripts.bootstrap.src')?>',
            ],
        },
        
    });
    editor.render();
    <?php endif ?>
    
    function campaignAddTrackingLink(){
        var val=$('#id_ec_links_input').val();
        var txt='{@url:'+btoa(val)+'}';
        var id=$('#id_ec_links_list').html().length;
        var html='<li class="list-group-item" id="id_ec_links_listitem_'+id+'">';
        html+='<div class="row"><div class="col-5">'+val+'</div>';
        html+='<div class="col-5">'+txt+'</div><div class="col-2 text-right">';
        html+='<button type="button" class="btn btn-primary btn-sm mr-1" onClick="copyToClipboard(';
        html+="'"+txt+"')"; 
        html+='"><i class="fas fa-copy"></i></button>';
        html+='<button type="button" class="btn btn-danger btn-sm" onclick="';
        html+="$('#id_ec_links_listitem_'"+id+"').remove()";
        html+='"><i class="fas fa-trash-alt"></i>';
        html+='</button><input type="hidden" name="ec_links['+txt+']" value="'+val+'"></div></div></li>';
        $('#id_ec_links_list').append(html);
    }
</script>