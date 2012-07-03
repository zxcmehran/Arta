var formvars={username:null,email:null,email_verify:null,password:null,password_verify:null};
function checkform(id, value){
	if(id == 'password'){
		pass=new String(value);
		value=pass.length;
	}
	makepreEffect(id);
	new Ajax.Request(site_url+'index.php?pack=user&view=register&type=xml&subject=' + Base64.encode(id) + '&data=' + Base64.encode(value), {
		method: 'get',
		onSuccess: function(transport) {
			XMLdoc = transport.responseXML;
			if (typeof(XMLdoc) == 'object'){
				makeEffect(id, XMLdoc.documentElement.firstChild.data);
			}
		}
	});
	
}

function makepreEffect(id){
	document.getElementById(id).style.borderColor = 'gray';
	document.getElementById(id).style.borderStyle = 'dashed';
	document.getElementById(id).style.borderWidth = '2px';
	document.getElementById(id + '_stat').innerHTML = ' <img style="display: inline;" src="'+loading_image+'"/>';
}

function makeEffect(id, res){
	
	if(res == 'true'){
		document.getElementById(id).style.borderColor = '#66aa66';
		document.getElementById(id).style.borderStyle = 'solid';
		document.getElementById(id).style.borderWidth = '2px';
		document.getElementById(id + '_stat').innerHTML = ' <img style="display: inline;" src="'+true_image+'"/>';
		formvars[id]=true;
	}else{
		document.getElementById(id).style.borderColor = '#aa6666';
		document.getElementById(id).style.borderStyle = 'solid';
		document.getElementById(id).style.borderWidth = '2px';
		document.getElementById(id + '_stat').innerHTML = ' <img style="display: inline;" id="tooltip_'+id+'" src="'+false_image+'"/>';
		new Control.ToolTip($('tooltip_'+id),str_replace('"','&quot;',res),{className:'tooltip',width:'200'});
		formvars[id]=false;
		
	}
}
function checkEquality(id, id2){
	if(document.getElementById(id).value !== document.getElementById(id2).value){
		makeEffect(id2, NOT_EQUALS);
	}else{
		makeEffect(id2, 'true');
	}
}

function makePasswords(){
	if(formvars.username===false || formvars.email===false || formvars.email_verify===false || formvars.password===false || formvars.password_verify===false){
		alert(PLEASE_CORRECT);
		return false;
	}
	$('m_password').value=Crypt.MD5($('password').value);
	s=new String($('password_verify').value);
	$('m_password_verify').value=Crypt.MD5($('password_verify').value)+s.length;
	return true;
}