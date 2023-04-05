<a data-toggle="modal" data-target="#appAboutModal" style="cursor: pointer">
  <i class="fas fa-info-circle"></i>
</a>

<!-- Modal -->
<div class="modal fade" id="appAboutModal" tabindex="-1" role="dialog" aria-labelledby="appAboutModalLabel" aria-hidden="true" style="color:#000!important">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body text-center">
          <div>
            <?php if (strlen($config->app->APPLogo) > 0) :?>
            <div class="row">
                <img src="data:image/png;base64,<?=$config->app->APPLogo?>" alt="logo" class="mx-auto" style="width:300px;height:250px"/>
            </div>
            <?php endif ?>
            <div class="row">
                <h4 ><?= $config->app->APPName ?></h4>
            </div>
            <div class="row">
                <strong><?= $config->app->APPDesc ?></strong>
            </div>
            <small>
            <div class="row mt-2 p-0">
                <div class="col-3 text-left pl-1">Version:</div><div class="col-8 text-left"><?= $config->app->APPVersion?></div>
            </div>
            <div class="row p-0">
                <div class="col-3 text-left pl-1">License:</div><div class="col-8 text-left">
                    <a href="https://www.apache.org/licenses/LICENSE-2.0" target="_blank">Apache License 2.0</a>
                </div>
            </div>
            <div class="row mt-2 pl-1">
                Copyright &copy;&nbsp;<?= formatDate('now','Y') ?>&nbsp;Artur&nbsp;Wiater
            </div>
          </small>
          </div> 
      </div>
      <div class="modal-footer p-1">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>