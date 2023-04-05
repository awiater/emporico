function copyToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(element).select();
    document.execCommand("copy");
    $temp.remove();
}
function addRefButton(id,func,icon,tooltip='',buttontype='secondary',islink=false){
    if ($.isArray(id)){
        $.each(id, function(index, value ){
            addRefButton(value,func,icon,tooltip,buttontype,islink)
        });
    }else{
        var matches = $("#"+id).attr('class').match(/(?:^|\s+)w-(\d+)(?:\s+|$)/g);
        
    	if (matches !== null) {
        	$("#"+id).wrap('<div class="input-group mb-3 '+matches+'">');
    	}else{
    		$("#"+id).wrap('<div class="input-group mb-3">');
    	}
		
		$("#"+id).after(function(){
			var refhtml='<div class="input-group-append">';
                        if (buttontype.length <1)
                        {
                            buttontype='input-group-text btn';
                        }
                        if (islink){
                           refhtml+='<a class="'+buttontype+'" href="'+func+'" id="bolt_btn_'+id+'"'; 
                        }else{
                           refhtml+='<button type="button" class="'+buttontype+'" onClick="'+func+'" id="bolt_btn_'+id+'"'; 
                        }
			
			if (tooltip.length>1){
				refhtml+='data-toggle="tooltip" data-placement="top" title="'+tooltip+'"';
			}
			if (islink){
                            refhtml+='><i class="'+icon+'"></i></a>';
                        }else{
                           refhtml+='><i class="'+icon+'"></i></button>'; 
                        }
			refhtml+='</div>';
			return refhtml;
		});
       }
}

function addListConfigButton(id,url,tooltip){
	if (url.length > 0){
		addRefButton(id,"window.location='"+url+"';",'fas fa-cog',tooltip);
	}
	
}

function enableTooltip(tag=null){
	tag=tag==null ? '[data-toggle="tooltip"]' : tag;
	$(tag).tooltip();
  	$('.alert').alert();
}

function generatePassword(passwordLength=12){
  var passwordChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
  return [...window.crypto.getRandomValues(new Uint32Array(passwordLength))]
    .map(x => passwordChars[x % passwordChars.length])
    .join('');
}

function ConfirmDialog(message,yesfunction,nofunction=null) {
  		$('<div id="ConfirmDialog"></div>').appendTo('body')
    .html('<div class="d-flex"><i class="fas fa-question-circle fa-2x mr-2 text-info"></i><h6 class="my-auto">' + message + '?</h6></div>')
    .dialog({
      modal: true,
      title: '',
      stack:false,
      zIndex: 1060,
      autoOpen: true,
      width: 'auto',
      resizable: false,
      buttons: {
        Yes: function() {
          yesfunction();
		  $(this).dialog("close");
        },
        No: function() {
            if (nofunction!=null){
                nofunction();
            }
          $(this).dialog("close");
        }
      },
      close: function(event, ui) {
        $(this).remove();
      },
    });
    $(".ui-dialog-titlebar").addClass('bg-info h-25 p-0');
    //$('.ui-dialog-title');
    $(".ui-dialog-titlebar-close").addClass('d-none');
   }
   
function Dialog(message,type='info') {
    var typeHtml='';
    if (type.toLowerCase()=='info'){
        typeHtml='<i class="fas fa-info-circle fa-2x mr-2 text-info"></i>';
    }else
    if (type.toLowerCase()=='danger'){
        typeHtml='<i class="fas fa-exclamation-circle fa-2x mr-2 text-danger"></i>';
    }
    else
    if (type.toLowerCase()=='warning'){
        typeHtml='<i class="fas fa-exclamation-triangle fa-2x mr-2 text-warning"></i>';
    } 
    $('<div></div>').appendTo('body')
    .html('<div class="d-flex">'+typeHtml+'<h6 class="my-auto">' + message + '</h6></div>')
    .dialog({
      modal: true,
      title: '',
      zIndex: 10000,
      autoOpen: true,
      width: 'auto',
      resizable: false,
      buttons: {
        Ok: function() {
          $(this).dialog("close");
        }
      },
      close: function(event, ui) {
        $(this).remove();
      }
    });
    $(".ui-dialog-titlebar").addClass('bg-'+type+' h-25 p-0');
    $(".ui-dialog-titlebar-close").addClass('d-none');
}

function errorDialog(message){
	Dialog(message,'danger');
}

function setActiveTabToUrl(type='pill'){
		$('a[data-toggle="'+type+'"]').on('shown.bs.tab', function (event) {
  			var id=$(this).attr('id');
  			id=id.replace('tabs-','').replace('-tab','');
  			requestSetGet('tab',id);
		});
}

function requestSetGet(key,value,url=null,replaceHistory=true){
    url=url==null ? window.location.search : url;
    var queryParams = new URLSearchParams(url);
    queryParams.set(key, value);
    if (replaceHistory){
      history.replaceState(null, null, "?"+queryParams.toString());   
    }
    return queryParams.toString();
}

function addMovement(itemid,mhtype,type,mhref,mhfrom='',mhto='',mhinfo='',insertNew=false,movement_field='movements_logger'){	
	if(!$.isArray(itemid)){
		itemid=[itemid,'change'];
	}
	$(itemid[0]).on(itemid[1],function(){
            if ($.type(mhref)=='function'){
                mhref=mhref($(this));
            }
            if ($.type(mhfrom)=='function'){
                mhfrom=mhfrom($(this));
            }
            if ($.type(mhto)=='function'){
                mhto=mhto($(this));
            }
            if ($.type(mhinfo)=='function'){
                mhinfo=mhinfo($(this));
            }
		addMovementData(mhtype,type,mhref,mhfrom,mhto,mhinfo,insertNew,movement_field);
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
function escapeRegExp(string){
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}
    
function replaceAll(str, term, replacement) {
    return str.replace(new RegExp(escapeRegExp(term), 'g'), replacement);
}

function isEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}

function exportCSV(title,data){
    var downloadLink = document.createElement("a");
    var blob = new Blob(["\ufeff", data]);
    var url = URL.createObjectURL(blob);
    downloadLink.href = url;
    downloadLink.download = title+".csv";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

function convertObjectToCSV(data,keys){
    var arr=Object.keys(data).map(function (key) { var sarr=Object.keys(data[key]).map(function (kkey) {return data[key][kkey];}); return sarr.join(","); });
    arr=arr.join("\r\n");
    var aar=Object.keys(keys).map(function (key){return key});
    aar=aar.join(",");
    return aar+"\r\n"+arr;
}

function precise_round(num, decimals) {
   var t = Math.pow(10, decimals);   
   return (Math.round((num * t) + (decimals>0?1:0)*(Math.sign(num) * (10 / Math.pow(100, decimals)))) / t).toFixed(decimals);
}

function ajaxCall(callUrl,callData,successFunc,errorFunc,callMethod='GET',callType='json')
{
     $.ajax({
        type: callMethod,
        dataType: callType,
        url: callUrl,
        data:callData,
        success:successFunc,
        error:errorFunc,
     });
}

function addLoader(element='body',killOnMove=false,loadername='_loader'){
    if (!$(element).hasClass('overlay-wrapper'))
    {
        $(element).addClass('overlay-wrapper');
    }
    var overclass='overlay';
    if (killOnMove){
        $(element).append('<div class="'+overclass+'" id="'+loadername+'" onMouseMove="killLoader()"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>');
    }else{
        $(element).append('<div class="'+overclass+'" id="'+loadername+'"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>');
    }
}

function killLoader(loadername='_loader'){
    $('#'+loadername).remove();
}