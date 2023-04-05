<?= $currentView->includeView('System/table') ?>
<?php if ($edit_acc) :?>
<!-- Order Confirm Modal -->
<?= $currentView->includeView('Orders/order_confirm') ?>
<!-- /Order Confirm Modal -->
<?php else :?>  
<!-- Order Cancel Modal -->

<!-- / Order Cancel Modal -->
<?php endif ?>