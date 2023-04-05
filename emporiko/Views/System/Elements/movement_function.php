function addMovement(itemid,mhtype,type,mhref,mhfrom='',mhto='',mhinfo='',insertNew=false,movement_field='movements_logger'){	
	if(!$.isArray(itemid)){
		itemid=[itemid,'change'];
	}
	
	$(itemid[0]).on(itemid[1],function(){
		addMovementData(mhtype,type,mhref,mhfrom,mhto,mhinfo,insertNew,movement_field)
	});
}
function addMovementData(mhtype,type,mhref,mhfrom='',mhto='',mhinfo='',insertNew=false,movement_field='movements_logger'){
	movement_arr=$("input[name='"+movement_field+"']").val();
		if (movement_arr.length > 0){
			movement_arr=JSON.parse(atob(movement_arr));
		}
	
		if(!$.isArray(movement_arr)){
			movement_arr=[];
		}
		var newItem=
		{
			'mhtype':mhtype,
			'type':type,
			'mhref':$.isArray(mhref) ?$(mhref[0])[mhref[1]]() : mhref,
			'mhfrom':$.isArray(mhfrom) ?$(mhfrom[0])[mhfrom[1]]() : mhfrom,
			'mhto':$.isArray(mhto) ?$(mhto[0])[mhto[1]]() : mhto,
			'mhinfo':$.isArray(mhinfo) ?$(mhinfo[0])[mhinfo[1]]() : mhinfo,
		};
		if (insertNew){
			movement_arr=[newItem];	
		}else{
			movement_arr.push(newItem);
		}
		
		$("input[name='"+movement_field+"']").val(btoa(JSON.stringify(movement_arr)));
}
$(function () {
  	enableTooltip();
});
