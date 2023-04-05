<div id="cookies_manager_container">
    <?php $_settings=Config('Cookies') ?>
    <div class="cookies_manager_bar">
        <div class="row col-12">
            <div class="col-10">
                <?= lang('cookies_manager.cookieBarMsg') ?>
            </div>
            <div class="col-2 my-auto text-right">
                <?php if ($_settings->cookieBarSettings) :?>
                <button class="btn btn-light btn-sm mr-2" data-toggle="modal" data-target="#cookies_manager_settings_window">
                    <?= lang('cookies_manager.cookieBarBtnCfg') ?>
                </button>
                <?php endif; ?>
                <button class="btn btn-success btn-sm" onclick="cookieManagerSetCookie('all')">
                    <?= lang('cookies_manager.cookieBarBtnAll') ?>
                </button>                
            </div>
        </div>
    </div>
    <?php if ($_settings->cookieBarSettings) :?>
    <div class="modal" tabindex="-1" role="dialog" id="cookies_manager_settings_window">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header p-1">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>  
                </div>
                <div class="modal-body">
                    <h4>
                        <?= lang('cookies_manager.cookieBarModCfgTitle') ?>
                        <a class="btn btn-link" data-toggle="collapse" href="#cookies_manager_settings_policy" role="button" aria-expanded="false" aria-controls="cookies_manager_settings_policy">
                            <i class="fas fa-angle-down"></i>
                        </a>
                    </h4>
                    <div class="collapse mb-2" id="cookies_manager_settings_policy">
                        <?= lang($_settings->cookiePrivacy); ?>
                    </div>
                    <div id="accordion" id="cookies_manager_settings_policy_list">
                       <?php foreach($_settings->scopes as $key=>$scope) :?>
                        <div class="card mb-0">
                            <div class="cookies_manager_cfg_item d-flex" id="cookies_manager_settings_heading_<?=$key?>">
                                <button class="btn btn-link mr-2" data-toggle="collapse" data-target="#cookies_manager_settings_collapse_<?=$key?>" aria-expanded="true" aria-controls="cookies_manager_settings_collapse_<?=$key?>">
                                    <i class="fas fa-angle-down"></i>
                                </button>
                                <p class="my-auto"><?= lang($scope['title']) ?></p>
                                <p class="ml-auto my-auto">
                                    <?php if ($scope['required']) :?>
                                    <?= lang('cookies_manager.cookieBarModCfgAlways') ?>
                                    <?php else :?>
                                    <div class="icheck-primary">
                                        <input type="checkbox" value="<?= $key ?>" id="cookies_manager_settings_option_<?= $key ?>" data-id="">
                                        <label for="cookies_manager_settings_option_<?= $key ?>"></label>
                                    </div>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div id="cookies_manager_settings_collapse_<?=$key?>" class="collapse" aria-labelledby="cookies_manager_settings_heading_<?=$key?>" data-parent="#accordion">
                                <div class="card-body">
                                    <?= lang($scope['tooltip']) ?>
                                </div>
                            </div>
                        </div>
                       <?php endforeach; ?>
                    </div>
                    <button class="btn btn-success btn-sm float-right mt-2" data-dismiss="modal" onclick="cookieManagerSetCookie('checked')">
                        <?= lang('cookies_manager.cookieBarBtnSave') ?>
                    </button> 
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <script>

        
        function cookieManagerSetCookie(scope){
            if (scope=='all'){
                scope=atob('<?= base64_encode(implode(',', array_keys($_settings->scopes)))?>').split(',');
            }else if (scope=='checked'){
                scope=[];
                $('input[id^="cookies_manager_settings_option_"]').each(function(){
                    if ($(this).is(':checked')){
                        scope.push($(this).val());
                    }
                });
            }
            var date = new Date();
            date.setTime(date.getTime() + (<?= $_settings->cookieExpiry ?> * 24 * 60 * 60 * 1000));
            var expires = "; expires=" + date.toGMTString();
            document.cookie = encodeURIComponent('<?= $_settings->cookieName ?>') + "=" + encodeURIComponent(JSON.stringify(scope)) + expires + "; path=/";
            $('.cookies_manager_bar').remove();
        }
        
    </script>
</div>
