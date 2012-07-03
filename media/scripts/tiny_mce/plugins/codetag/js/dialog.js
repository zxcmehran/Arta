tinyMCEPopup.requireLangPack();

var selectedNode;

function escapeHTML(s) {
	return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function stripScripts(s) {
	return s.replace(new RegExp('<script[^>]*>([\\S\\s]*?)<\/script>', 'img'), '');
}
function stripTags(s) {
    return s.replace(/<\w+(\s+("[^"]*"|'[^']*'|[^>])+)?>|<\/\w+>/gi, '');
}
function unescapeHTML(s) {
    return s.replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&amp;/g,'&');
}

var CodetagDialog = {
	init : function() {
		var f = document.forms[0];
		selectedNode=tinyMCEPopup.editor.selection.getNode();
		f.codecontent; // i don't know why but if you remove this, the f.codecontent will be undefined after codecontent.edit()
		if(selectedNode.nodeName=='CODE'){
			cc=selectedNode.innerHTML;
			cc=stripScripts(cc);
			cc=cc.replace(/\n/gi,'').replace(/(<br([^>]+)>|<br>) /gi,'<br>').replace(/<br([^>]+)>|<br>|<\/p>|<p([^>]+)>|<p>/gi, "\n").replace(/&nbsp;&nbsp;&nbsp;/gi, '\t').replace(/&nbsp;/gi,' ');
			cc=stripTags(cc);
			cc=unescapeHTML(cc);
			f.codecontent.value=cc;
		}
		cn=selectedNode.className;
		var types=new Array('csharp', 'css', 'generic', 'html', 'javascript', 'java', 'perl', 'ruby', 'php','text','sql','vbscript');
		i=0;
		done=false;
		while(i<types.length){
			ct=cn.indexOf(types[i]);
			if(ct>=0 && ct!==false){
				ct=types[i];
				done=true;
				break;
			}
			i++;
		}

		if(cn.indexOf('no-highlight')>=0 && cn.indexOf('no-highlight')!==false){
			f.no_highlight.checked=true;
		}
		
		if(done==true){
			// Get the selected contents as text and place it in the input
			if(f.no_highlight.checked==false){
				setTimeout('codecontent.edit("codecontent_cp", ct);', 500);
			}
			f.codetype.value = ct;
		}else if(f.no_highlight.checked==false){
			setTimeout('codecontent.edit("codecontent_cp", "generic");', 500);
		}
	},
	
	noHighlight : function(){
		var f = document.forms[0];
		var c=f.codecontent;
		c.value=codecontent.getCode();
		if(f.no_highlight.checked==true){
			codecontent.edit(false, 'text');
		}else{
			codecontent.edit(false, f.codetype.value);
		}
	},

	codeType : function(){
		var f = document.forms[0];
		var c=f.codecontent;
		c.value=codecontent.getCode();
		codecontent.edit(false, f.codetype.value);
	},

	insert : function() {
		var f = document.forms[0];

		var ed = tinyMCEPopup.editor;
		var e = selectedNode;
		cn='';
		if(e.nodeName == 'CODE'){
			cn=e.className;
			var types=new Array('csharp', 'css', 'generic', 'html', 'javascript', 'java', 'perl', 'ruby', 'php','text','sql','vbscript');
			i=0;
			while(i<types.length){
				ct=cn.indexOf(types[i]);
				if(ct>=0 && ct!==false){
					cn=cn.substr(0, ct)+cn.substr(ct+types[i].length);
				}
				i++;
			}

			if(cn.indexOf('no-highlight')>=0 && cn.indexOf('no-highlight')!==false){
				cn=cn.substr(0, cn.indexOf('no-highlight'))+cn.substr(cn.indexOf('no-highlight')+12);
			}
		
			while((ind = cn.indexOf('  '))>=0){
				cn=cn.substr(0, ind)+' '+cn.substr(ind+2);
			}
			while(cn.substr(0,1)==' '){
				cn=cn.substr(1);
			}
			while(cn.substr(cn.length-1, 1)==' '){
				cn=cn.substr(0, cn.length-1);
			}

		}
		// Insert the contents from the input into the document
		
		
		content=codecontent.getCode();
		win=tinyMCEPopup.getWin();
		
		content=escapeHTML(content);
		content=content.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;');
		content=content.replace(/ /gi, '&nbsp;');
		content=content.replace(/\n/g, '<br />\n');		

		if (e.nodeName == 'CODE') {
			e.className=f.codetype.value+(f.no_highlight.checked?' no-highlight':'')+(cn==''?'':' '+cn);
			e.innerHTML=content;
		}else{
			//dat=e.innerHTML;
			//e.innerHTML='';
			ed.execCommand('mceInsertContent', false, '<code class="'+f.codetype.value+(f.no_highlight.checked?' no-highlight':'')+'">'+content+'</code>');
		}

		ed.nodeChanged();
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(CodetagDialog.init, CodetagDialog);
