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
      <p class="login-box-msg"><?= lang('system.auth.forget_title') ?></p>

       <?= form_open(current_url(),'',$form_hidden); ?>
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="<?= lang('system.auth.forget_email') ?>" name="email">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block"><?= lang('system.auth.forget_btn') ?></button>
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
<?php if (!empty($_loginPage)) :?>
<?= $this->endSection() ?>
<?php else :?>
</div>
<?php endif ?>