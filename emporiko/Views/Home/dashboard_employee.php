<div class="container col-12">
    <div class="row">
        <div class="col-xs-12 col-md-4">
            <?= $currentView->getTile('products_pricefile') ?>
        </div>
        <div class="col-xs-12 col-md-4">
            <?= $currentView->getTile('products_brand') ?>
        </div>
        <div class="col-xs-12 col-md-4">
            <?= $currentView->getTile('products_brand_yearusage_orders') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-md-4">
            <?= $currentView->getTile('orders_yearusage') ?>
        </div>
        <div class="col-xs-12 col-md-4">
            <?= $currentView->getTile('orders_uploadorder') ?>
        </div>
        <div class="col-xs-12 col-md-4">
             <?= $currentView->getTile('orders_custlive') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-md-4">
            <?= $currentView->getTile('products_brand_update') ?>
        </div>
        <div class="col-xs-12 col-md-4">
            
        </div>
        <div class="col-xs-12 col-md-4">
             
        </div>
    </div>
    
</div>
<?= $currentView->includeView('Home/dashboard_scripts') ?>