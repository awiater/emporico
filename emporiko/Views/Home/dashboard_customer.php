<div class="container col-12">
    <div class="row">
        <div class="col-xs-12 col-md-4">
            <div class="row m-0 p-0">
                <div class="col-12">
                    <?= $currentView->getTile('products_pricefile') ?>
                </div>
            </div>
            <div class="row m-0 p-0">
                <div class="col-12">
                    <?= $currentView->getTile('products_brand') ?>
                </div>
            </div>
            <div class="row p-1">
                <div class="col-12">
                    
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="row">
                <div class="col-12">
                <?= $currentView->getTile('orders_uploadorder') ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <?= $currentView->getTile('orders_custlive') ?>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="row">
                <div class="col-12">
                    <?= $currentView->getTile('tickets_notify') ?>
                </div>
            </div>
        </div>
    </div>   
</div>
<?= $currentView->includeView('Home/dashboard_scripts') ?>