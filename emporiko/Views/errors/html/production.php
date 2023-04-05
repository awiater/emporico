<?php $error_id = uniqid('error', true); ?>
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="robots" content="noindex">

	<title><?= !empty($title) ? esc($title) :'' ?></title>
  	<link href="<?= site_url() ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= site_url() ?>/assets/vendor/bootstrap/css/bootstrap-switch-button.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= site_url() ?>/assets/vendor/jquery/jquery-ui.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= site_url() ?>/assets/template/css/adminlte.min.css" rel="stylesheet" type="text/css" />
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet" type="text/css" />
</head>
<body>
	<script src="<?= site_url() ?>/assets/vendor/jquery/jquery.min.js" type="text/javascript"></script>
	<script src="<?= site_url() ?>/assets/vendor/jquery/jquery-ui.min.js" type="text/javascript"></script>
	<script src="<?= site_url() ?>/assets/vendor/jquery/popper.js" type="text/javascript"></script>
	<script src="<?= site_url() ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js" type="text/javascript"></script>
	<script src="<?= site_url() ?>/assets/vendor/bootstrap/js/bootstrap-switch-button.js" type="text/javascript"></script>
	<script src="<?= site_url() ?>/assets/template/js/adminlte.min.js" type="text/javascript"></script>
<section class="content" style="text-align:center">
      <div class="error-page">
        <h2 class="headline text-danger">500</h2>

        <div class="error-content">
          <h3><i class="fas fa-exclamation-triangle text-danger"></i> Oops! Something went wrong.</h3>

          <p>
            System crash with fatal error.  
            You can report this error to our support team <a href="mailto:<?= config('Email')->supportEmail?>?subject=Error_<?= $exception->getCode() ?>"><?= config('Email')->supportEmail?></a>
          </p>
          
          <div class="card w-75 mx-auto">
            <div class="card-header">
              <h5 class="card-title">Error Code</h5>
            </div>
            <div class="card-body">
                
                <p class="card-text">
                    <?= ((!empty($title) ? esc($title) :'').esc($exception->getCode() ? ' #' . $exception->getCode() : '')).PHP_EOL.
          	(esc($exception->getMessage())).PHP_EOL.
          	(!empty($line)?(esc(static::cleanPath($file, $line)).' at line '.esc($line)): '')?>
                </p>
            </div>
        </div>
        </div>
      </div>
      <!-- /.error-page -->

    </section>

</body>
</html>
