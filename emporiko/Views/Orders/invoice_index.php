<?= $currentView->includeView('System/table') ?>
<script>
    $(function(){
        <?php if (!empty($invalid_orders) && is_array($invalid_orders) && count($invalid_orders) > 0) :?>
            <?php $html_orderid=''; ?>
            <?php foreach($invalid_orders as $key=>$order) :?>
                <?php if($key > 0) :?>
                    <?php $html_orderid.=','; ?>
                <?php endif ?>
                <?php $html_orderid.='input[name="ordid[]"][value="'.$order.'"]';?>
            <?php endforeach;?>
        $('<?= $html_orderid?>').each(function(){
            $(this).parent().parent().addClass('bg-danger disabled').find('[data-download]').remove();
            
        });
        <?php endif ?>
    });
    
</script>