/*
* File Manager
*
* Copyright (c) 2010 Tom Kay - oridan82@gmail.com
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.*
*
*/

	var baseUrl='';
	var data=[];	
	var currentData=[];
	var currentPath='.';
	var clickable=false;
	var clickedValue='';
	var container='';
	var pathContainer='';
	var deleteEvent='';
	var noDeleteItems=null;
	
	
	function initFileManager(divContainer,divPathContainer,websiteUrl,filesArray,NoDeleteItems,ClickEvent=null,DeleteEvent=null)
	{
		baseUrl=websiteUrl;
		data=filesArray;	
		currentData=data;
		clickable=ClickEvent===null?false:true;
		container=divContainer;
		clickedValue=ClickEvent;
		pathContainer=divPathContainer;
		deleteEvent=DeleteEvent;
		noDeleteItems=NoDeleteItems;
		fileManagerListFiles(encodePath('.'),'.');
	}
	
	function fileManagerSetClickEvent(ClickEvent)
	{
		clickedValue=ClickEvent;
	}
	
	function getFolderData()
	{
		currentData=data;
		var path='.';
		var html='<nav aria-label="breadcrumb">';
		html+='<ol class="breadcrumb p-0">';
		html+='<li class="breadcrumb-item">';
		html+='<button onClick="fileManagerListFiles('+"'"+encodePath('.')+"'"+');" class="btn btn-link"><i class="fa fa-home"></i></button>';
		html+='</li>';
		$.each(currentPath.split('/'), function( key, value ) {
			if (value!='.'){
				if (value in currentData){	
					currentData=currentData[value]['files'];
					path+='/'+value;
					html+='<li class="breadcrumb-item">';
					html+='<button onClick="fileManagerListFiles('+"'"+encodePath(path)+"'"+');" class="btn btn-link">'+value+'</button>';
					html+='</li>';
				}
			}
		});
		html+='</ol>';
		html+='</nav>';
		$(pathContainer).html(html);
	}
	
	function fileManagerGoUp(folder)
	{
		var newPath=currentPath.split('/');
		currentPath='.';
		$.each(newPath, function( key, value ) {
			if (key<newPath.length-2 && key>0){
				currentPath+='/'+value;
			}
		});	
		fileManagerListFiles(folder);
	}
	
	function fileManagerListFiles(folder)
	{	
		folder=decodePath(folder);
		var parentDir=currentPath;	
		if (folder!='.'){
			currentPath=currentPath+'/'+folder;
			currentPath=folder;
			getFolderData();
		}else{
			currentData=data;
			parentDir='.';
			$(pathContainer).html('<button onClick="listFiles('+"'.'"+');" class="btn btn-link"><i class="fa fa-home"></i></button>');
		}
		
		var html='';
		var valid_types ='folder,image,video';
		if (folder!='.'){
			html+='<div class="col-2">';
			html+='<button type="button" onClick="fileManagerGoUp('+"'"+encodePath(parentDir)+"'"+');" class="btn btn-link">';
			html+='<i class="fa fa-folder text-danger fa-3x"></i></br>';
  			html+='UP</button>';
  			html+='</div>';
		}
		$.each(currentData, function( key, value ) {
			var icon='';
			
			if (valid_types.includes(value.ftype)){
			html+='<div class="col-2">';
			if (value.ftype=='folder'){
				html+='<button type="button" onClick="fileManagerListFiles('+"'"+encodePath(currentPath+'/'+key)+"'"+');" class="btn btn-link">';
				html+='<i class="'+fileManagergetIconFromType(value)+'"></i></br>';
				html+=value.name+'</button>';
			}else{
				html+='<button type="button" onClick="fileManagerItemClick('+"'"+encodePath(baseUrl+value.path)+"'"+','+"'"+value['ftype']+"'"+');" class="btn btn-link">';
				html+=fileManagergetIconFromType(value);
				html+='</br>'+value.name
				html+='</br>'+value.ftype+'</button>';
			}
			html+='</div>';
			}
		});
		$(container).html(html);
	}
	
	function fileManagergetIconFromType(value){
		if (value.ftype=='folder'){
			return 'fa fa-folder fa-3x text-warning';
		}else
		if (value.ftype=='image'){
			return '<img data-url="'+encodePath(baseUrl+value.path)+'" data-mime="'+value['ftype']+'" id="id_thb_'+value.name+'" class="fileManagerItem img-thumbnail" style="width:60px;height:60px;"><script>fileManagerItemDrawImageThb('+"'"+encodePath(baseUrl+value.path)+"','id_thb_"+value.name+"'"+');</script>';
		}else
		if (value.ftype=='video'){
  			return '<i class="fileManagerItem fa fa-film fa-3x" data-url="'+encodePath(baseUrl+value.path)+'" data-mime="'+value['ftype']+'"></i>';					
  		}else{
  			return'<i class="fileManagerItem fa fa-file fa-3x" data-url="'+encodePath(baseUrl+value.path)+'" data-mime="'+value['ftype']+'"></i>';
  		}
		
		return '<i class="fa fa-file"></i>';
	}
	
	function fileManagerGetCurrentPath(encrypt=false)
	{
		if (encrypt){
			return btoa(currentPath).replace(/\+/g, '-').replace(/\//g, '_').replace(/\=+$/, '');	
		}else{
			return currentPath;
		}
	}
	
	function fileManagerItemClick(path,mime)
	{
		clickedValue(decodePath(path),mime);
	}
	
	function fileManagerItemDelete(path)
	{
		deleteEvent(decodePath(path));
	}
	
	function fileManagerItemDrawImageThb(image,id)
	{
		var canvas = document.createElement("canvas");//$('#'+id);
		getBase64FromUrl(decodePath(image)).then(data=>$(container).find('#'+id).attr('src',data));
    	
	}
	
	function fileManagerSetCurrentPath(path)
	{
		fileManagerListFiles(encodePath(path));
	}
	
	function fileManagerUpdateData(filesData,path)
	{
		data=filesData;
		currentData=data;	
		fileManagerListFiles(encodePath(path));
	}
	
	function encodePath(path)
	{		
		return btoa(path);
	}
	
	function decodePath(path)
	{
    	return atob(path);
    	
	}
	
	const getBase64FromUrl = async (url) => {
  const data = await fetch(url);
  const blob = await data.blob();
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.readAsDataURL(blob); 
    reader.onloadend = () => {
      const base64data = reader.result;   
      resolve(base64data);
    }
  });
}
	