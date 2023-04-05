<?php if (!empty($_loginPage)) :?>
<?= $this->extend($_loginPage); ?>
<?= $this->section('body') ?>
<?php else :?>   
<div class="mx-auto col-md-4 col-xs-12">
    <?php if (!empty($error)) :?>
    <div class="w-100">
        <?= $error ?>
    </div>
    <?php endif ?>
<?php endif ?>
<div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg"><?= lang('system.auth.recover_title') ?></p>

      <?= form_open(current_url(),'',$form_hidden); ?>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="<?= lang('system.auth.loginform_password') ?>" id="recoveryform_password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="<?= lang('system.auth.recover_password') ?>" id="recoveryform_password2" name="password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block" id="recoveryform_btn"><?= lang('system.auth.recover_btn') ?></button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <p class="mt-3 mb-1">
        <a href="<?= site_url(); ?>"><?= lang('system.auth.forget_login') ?></a>
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
<?php if (empty($_loginPage)) :?>
</div>
<?php endif ?>
  <script>
  	function checkPass(){
  		var pass = $("#recoveryform_password").val();
  		var strength = 1;
		var arr = [/.{5,}/, /[a-z]+/, /[0-9]+/, /[A-Z]+/];
		jQuery.map(arr, function(regexp) {
  			if(pass.match(regexp))
     		strength++;
		});
		$("#loginindex_error").html("");
		if (strength<5){
			$("#recoveryform_btn").attr("disabled", true);
			$("#loginindex_error").html(atob('<?= $error_pass_len; ?>'));
			return false;
		}
		return true;
  	} 
  	
  	$("#recoveryform_password").on("change",function(){
  		checkPass()
  	});
  	
  	$("#recoveryform_password2").on("change",function(){
  		if (checkPass()){
  		if ($("#recoveryform_password").val()==$("#recoveryform_password2").val()){
			$("#loginindex_error").html("");
  		}else{
  			$("#recoveryform_btn").attr("disabled", true);
			$("#loginindex_error").html(atob('<?= $error_pass_equal; ?>'));
  		}}
  	});
  		
  </script>
<?php if (!empty($_loginPage)) :?>
<?= $this->endSection() ?>
<?php endif ?>