<?= $currentView->includeView('System/form',['fields'=>array_slice($fields, 0,9)]); ?>

<div class="modal" tabindex="-1" role="dialog" id="mrouteSubMenuModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('system.menu.submenu_dialog_title') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields, 9,8)]); ?>
          <div id="mrouteSubMenuModalFields"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="mrouteSubMenuModalAddBtn"><?= lang('system.buttons.add') ?></button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('system.buttons.cancel') ?></button>
      </div>
    </div>
  </div>
</div>
<script>
	$(function(){
		$("#id_mroute").wrap('<div class="input-group mb-3">');
		$("#id_mroute").after(function(){
			var refhtml='<div class="input-group-append">';
			refhtml+='<button type="button" class="btn btn-secondary btn-sm" onClick="addSubMenu()" id="bolt_btn_id_mroute"><i class="fas fa-bolt"></i></button>';
			refhtml+='</div>';
			return refhtml;
		});
		//parseCtrAct();
	});
	
	function addSubMenu(){
		var val=$("#id_mroute").val();
		if (val.substring(1,0)=='[' || val.substring(1,0)=='@'){
			$("#id_routectr").val('submenu');
			parseCtrAct();
			val=val.replace('[','').replace(']');
			$("#id_menuname").val(val);
		}else{
			val=val.split('/');
			$("#id_menuname").val('');
                        $("#id_wizard_route_controller").filter(function(){
                            return $(this).text() == val[0].toUpperCase();
                        }).attr('selected', true);
                        parseCtrAct();
                        var index=1;
                        $('[name^="wizard_route"').each(function(){
                            if ($(this).attr('name')!='wizard_route[controller]' && $(this).is('select')){
                                $(this).val(val[index]).change();
                            }else
                            if ($(this).attr('name')!='wizard_route[controller]'){
                                $(this).val(val[index]);
                            }
                            index++;
                });
			/*$("#id_routectr option").each(function() {
  				if($(this).text().toLowerCase() == val[1]) {
    				$(this).attr('selected', 'selected');            
  				}                        
			});
			parseCtrAct();
			$("#id_routeact option").each(function() {
  				if($(this).text().toLowerCase() == val[2]) {
    				$(this).attr('selected', 'selected');            
  				}                        
			});*/
		}	
		$("#mrouteSubMenuModal").modal('show');
	}
	
	function parseCtrAct1()
	{
		var val=$("#id_routectr option:selected").val();
		var ctr=$("#id_routectr option:selected").text()
		ctr=ctr.toLowerCase();
		ctr=ctr.replace('home','/');
		$("#id_menuname_field").addClass('d-none');
		$("#id_routeact_field").addClass('d-none');
		if (val=='submenu'){
			$("#id_menuname_field").removeClass('d-none');
		}else{
			$("#id_routeact_field").removeClass('d-none');
			$("#id_menuname").val();
			var items=JSON.parse(atob(val));
			var html='';
			$.each(items, function( index, value ){
				var url='/'+ctr+'/'+value;
				url=url.replace('/index','');
				url=url.replace('//','');
				html+='<option value="'+url+'">'+value+'</option>';
			});
			$("#id_routeact").html(html);
		}
	}
	function parseCtrAct()
        {
            var val=$("#id_wizard_route_controller option:selected").val();
            $("#id_menuname_field").addClass('d-none');
            $("#mrouteSubMenuModalFields").html('');
            if (val=='submenu'){
                $("#id_menuname_field").removeClass('d-none');
            }else{
                if (val!=undefined  && val.length >0){
                    $("#mrouteSubMenuModalFields").html(atob(val)); 
                }
            }
        }
        
	$("#id_wizard_route_controller").on('change',function(){
		parseCtrAct()
	});
	
	$("#mrouteSubMenuModalAddBtn").on("click",function(){
		/*var tpl=$("#id_routeact option:selected").val();
		if ($("#id_menuname").val().length > 0)
		{
			tpl='['+$("#id_menuname").val()+']';
		}*/
                var tpl='';
                $('[name^="wizard_route"').each(function(){
                    if ($(this).attr('name')=='wizard_route[controller]'){
                       tpl+='/'+$(this).find(":selected").text().toLowerCase(); 
                    }else
                    if ($(this).is('select')){
                        tpl+='/'+$(this).find(":selected").val();
                    }else{
                        tpl+='/'+$(this).val();
                    }
                });
		$("#id_mroute").val(tpl);
		$("#mrouteSubMenuModal").modal('hide');
	});
</script>
