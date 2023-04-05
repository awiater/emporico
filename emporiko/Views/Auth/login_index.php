<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title><?= $currentView->getTitle() ?></title>
  
  <!-- Default styles definition-->
  <?= $currentView->getCSS() ?>
</head>
<body class="hold-transition login-index">	
	<?= $currentView->getScripts() ?>
	<script>
		$(function () {
  			$('[data-toggle="tooltip"]').tooltip();
  			$('.alert').alert();
		});
	</script>
	<div class="login-box bg-white card">
  		<div class="login-logo">
                    <img src="<?= protected_link(parsePath($config->theme_logo),TRUE); ?>" alt="" class="img-fluid mt-1" style="opacity: .8;height:60px;">
  		</div>
                <?php if (!empty($error)) :?>
  		<div id="loginindex_error">
                    <?= $error ?>
                </div>
                <?php endif ?>
  		
  		<?= $this->renderSection('body') ?>
	</div>
</body>
</html>
