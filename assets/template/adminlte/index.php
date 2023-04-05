<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= strip_tags($currentView->getTitle()) ?></title>
   <?= $currentView->getCSS() ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed sidebar-collapse">

<?= $currentView->getScripts() ?>
<script>
    $('[data-widget="pushmenu"]').PushMenu('collapse'); 
</script>
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <p class="nav-link m-0"></p>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="fas fa-user-circle fa-md"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <div class="dropdown-item">
                  <a href="<?= url('myaccount') ?>" class="d-block text-dark">
                    <i class="fas fa-user-edit mr-5"></i><?= lang('system.auth.profile') ?>
                  </a>
              </div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item">
                  <a href="<?= url('logout') ?>" class="d-block text-dark">
                    <i class="fas fa-door-open mr-5"></i><?= lang('system.auth.logout') ?>
                  </a>
              </div>
          </div>
      </li>
      <!-- Notifications Dropdown Menu -->
      <?php $notify=$currentView->getIconData('notifications',null, EMPORIKO\Helpers\AccessLevel::edit); if(is_array($notify) && \EMPORIKO\Helpers\Arrays::KeysExists(['qty','items'], $notify)):?>
        <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <?php if ($notify['qty'] > 0) :?>
          <span class="badge badge-warning navbar-badge"><?= $notify['qty'] ?></span>
          <?php endif ?>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header"><?= lang('system.movements.notifications_qty',[$notify['qty']]) ?></span>
          <div class="dropdown-divider"></div>
          <?php foreach($notify['items'] as $item) :?>
          <div class="dropdown-item">
            <div>
                <div class="text-muted text-sm col-12 d-flex">
                    <div>
                        <i class="far fa-clock mr-1"></i><?= convertDate($item['mhdate'], null, 'd M Y, H:i') ?>
                    </div>
                    <i class="fas fa-times ml-auto" style="cursor:pointer" onclick="window.location='<?=url('Settings','notification',[$item['mhid']],['refurl'=>current_url(FALSE,TRUE)])?>'"></i>
                </div>
                <div class="text-muted text-sm col-12">
                    <?= lang($item['mhinfo'],$item) ?>
                </div>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <?php endforeach; ?>
        </div>
      </li>
      <?php endif ?>
      <!-- Emails Notification Icon -->
      <?php $emailsIcon=$currentView->getIconData('emails'); if(is_array($emailsIcon) && array_key_exists('url', $emailsIcon) && array_key_exists('qty', $emailsIcon)):?>
      <li class="nav-item"> 
          <a class="nav-link" href="<?= $emailsIcon['url']?>" aria-expanded="false" data-toggle="tooltip" data-placement="bottom" title="<?= lang('system.emails.btnnotify_tooltip');?>">
            <i class="fas fa-envelope-open-text"></i>
            <?php if ($emailsIcon['qty'] > 0) :?>
            <span class="badge badge-danger navbar-badge"><?= $emailsIcon['qty'] ?></span>
            <?php endif ?>
          </a> 
      </li>
      <?php endif ?>
      <!-- User Dropdown Menu -->
     <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button" data-toggle="tooltip" data-placement="bottom" title="<?= lang('system.buttons.fullscrbtn_tooltip') ?>">
            <i class="fas fa-expand-arrows-alt"></i>
        </a>
    </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <div class="brand-link logo-switch">
      <img src="<?= parsePath('@template/apd/img/logo.png')?>" class="brand-image-xl logo-xl">
      <img src="<?= parsePath('@template/apd/img/logo_small.png')?>" class="brand-image-xs logo-xs" style="height:50px">
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <?= $currentView->getSection('mainmenu'); ?>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-12">
            <?= $currentView->getBreadcrumbs(TRUE,['home'=>'<i class="fas fa-home"></i>']);//'bg-white p-0 border',TRUE); ?>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
          <?php if (!empty($notify) && is_array($notify)&& array_key_exists('qty', $notify) && $notify['qty'] > 0) :?>
          <?= view('errors/html/exception',['type'=>'warning','msg'=>lang('system.movements.notifications_qty',[$notify['qty']])]) ?>
          <?php endif ?>
          <?php if (!empty($emailsIcon) && is_array($emailsIcon) && array_key_exists('qty', $emailsIcon) && $emailsIcon['qty'] > 0) :?>
          <?= view('errors/html/exception',['type'=>'warning','msg'=>lang('system.emails.notification',[$emailsIcon['qty']])]) ?>
          <?php endif ?>
          
 		<?= !empty($error) ? $error : ''; ?>
    	<?= $_content ?>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong  class="text-teal">Copyright &copy; 2006-<?= formatDate('now','Y') ?>&nbsp;<?= $config->company ?>.
    All rights reserved.
    </strong>
    <div class="float-right d-none d-sm-inline-block text-teal">
     
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
</body>
</html>
