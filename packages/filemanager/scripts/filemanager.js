var tree_got=new Array('/');
function tree_setURL(url){
	parentObj=$('fm_tree_'+url).parentNode;
	obj=$('fm_tree_'+url);
	ulObject=obj;
	/*if(ulObject.tagName!=='UL'){
		ulObject=parent.childNodes[2];
	}*/
	//refresh tree
	$$('ul.'+(ulObject.className)).each(function(e){
		if(e.tagName=='UL'){
			Element.setStyle(e, {display:'none'});
		}
	});
	if(!in_array(url, tree_got)){
		Element.setStyle(parentObj, {listStyle:'url('+img_url+'loading_small.gif)'});
		new Ajax.Request(site_url+'index.php?pack=filemanager&type=xml&do=tree&url='+Base64.encode(url)+'&c='+(parseInt(ulObject.className.substr(ulObject.className.length-1))), {
			method: 'get',
			onSuccess: function(transport) {					
					ulObject.innerHTML += transport.responseText;
					tree_got.push(url);
					parentObj.setStyle({listStyle:'url('+img_url+'folder_small.png)'});
					ulObject.setStyle({display:'none'});
					new Effect.Appear(ulObject);
			},
			onFailure: function(transport) {
				alert(AjaxOnFailureErrorMsg);
				parentObj.setStyle({listStyle:'url('+img_url+'folder_small.png)'});
			}
		});
	}else{
		ulObject.setStyle({display:'none'});
		new Effect.Appear(ulObject);
	}
	childs=ulObject.childNodes;
	for(i=0;i!==childs.length;i++){
		if(childs[i].tagName=='LI'){
			for(j=0;j!==childs[i].childNodes.length;j++){
				if(childs[i].childNodes[j].tagName=='UL'){
					Element.setStyle(childs[i].childNodes[j], {display:'none'});
				}
			}
		}
	}
}
function address_setURL(url){
	$('fm_address').innerHTML='<img src="'+img_url+'home_small.png">'+url;
	new Effect.Highlight($('fm_address'));
}
function content_setURL(url){
	obj=$('fm_content');
	obj.innerHTML='<center><img src="'+img_url+'loading.gif"></center>';
	new Ajax.Request(site_url+'index.php?pack=filemanager&type=xml&do=content&url='+Base64.encode(url)+'&editor='+FM_Mode, {
			method: 'get',
			onSuccess: function(transport) {
					obj.innerHTML = transport.responseText;
					obj.setStyle({display: 'none'});
					Effect.BlindDown(obj);
			},
			onFailure: function(transport) {
				alert(AjaxOnFailureErrorMsg);
				obj.innerHTML = '';
			}
		});
}
function setURL(url){
	tree_setURL(url);
	address_setURL(url);
	content_setURL(url);
	document.fm_uploadform.dest.value=url;
	document.fm_newform.dest.value=url;
}

function DeleteFile(file, dir){
	if(confirm(DeleteFileConfirmationMsg+"\n"+file)){
		new Ajax.Request(site_url+'index.php?pack=filemanager&type=xml&do=delete&token='+TOKEN+'&url='+Base64.encode(file), {
				method: 'get',
				onSuccess: function(transport) {
					$('fm_tree_'+dir).innerHTML='';
					for(i=0;i!==tree_got.length;i++){
						if(tree_got[i]==dir){
							delete tree_got[i];
							continue;
						}
					}
					setURL(dir);
				},
				onFailure: function(transport) {
					alert(AjaxOnFailureErrorMsg);
				},
				on403: function(t){
					alert(AjaxOnUnauthorizedMsg);
				}
			});
	}
}

function RenameFile(file, dir, fname){
	newname=prompt(RenameFileConfirmationMsg+"\n"+fname, fname);
	if(newname!=null){
		new Ajax.Request(site_url+'index.php?pack=filemanager&type=xml&do=rename&token='+TOKEN+'&url='+Base64.encode(file)+'&newname='+Base64.encode(newname), {
				method: 'get',
				onSuccess: function(transport) {
					$('fm_tree_'+dir).innerHTML='';
					for(i=0;i!==tree_got.length;i++){
						if(tree_got[i]==dir){
							delete tree_got[i];
							continue;
						}
					}
					setURL(dir);
				},
				onFailure: function(transport) {
					alert(AjaxOnFailureErrorMsg);
				},
				on403: function(t){
					alert(AjaxOnUnauthorizedMsg);
				}
			});
	}
}
