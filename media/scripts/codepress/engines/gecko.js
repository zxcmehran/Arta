CodePress={scrolling:false,autocomplete:true,initialize:function(){if(typeof(editor)=="undefined"&&!arguments[0]){return}body=document.getElementsByTagName("body")[0];body.innerHTML=body.innerHTML.replace(/\n/g,"");chars="|32|46|62|8|";cc="\u2009";editor=document.getElementsByTagName("pre")[0];document.designMode="on";document.addEventListener("keypress",this.keyHandler,true);window.addEventListener("scroll",function(){if(!CodePress.scrolling){CodePress.syntaxHighlight("scroll")}},false);completeChars=this.getCompleteChars();completeEndingChars=this.getCompleteEndingChars()},keyHandler:function(a){keyCode=a.keyCode;charCode=a.charCode;fromChar=String.fromCharCode(charCode);if((a.ctrlKey||a.metaKey)&&a.shiftKey&&charCode!=90){CodePress.shortcuts(charCode?charCode:keyCode)}else{if((completeEndingChars.indexOf("|"+fromChar+"|")!=-1||completeChars.indexOf("|"+fromChar+"|")!=-1)&&CodePress.autocomplete){if(!CodePress.completeEnding(fromChar)){CodePress.complete(fromChar)}}else{if(chars.indexOf("|"+charCode+"|")!=-1||keyCode==13){top.setTimeout(function(){CodePress.syntaxHighlight("generic")},100)}else{if(keyCode==9||a.tabKey){CodePress.snippets(a)}else{if(keyCode==46||keyCode==8){CodePress.actions.history[CodePress.actions.next()]=editor.innerHTML}else{if((charCode==122||charCode==121||charCode==90)&&a.ctrlKey){(charCode==121||a.shiftKey)?CodePress.actions.redo():CodePress.actions.undo();a.preventDefault()}else{if(charCode==118&&a.ctrlKey){top.setTimeout(function(){CodePress.syntaxHighlight("generic")},100)}else{if(charCode==99&&a.ctrlKey){}}}}}}}}},findString:function(){if(self.find(cc)){window.getSelection().getRangeAt(0).deleteContents()}},split:function(b,a){if(a=="scroll"){this.scrolling=true;return b}else{this.scrolling=false;mid=b.indexOf(cc);if(mid-2000<0){ini=0;end=4000}else{if(mid+2000>b.length){ini=b.length-4000;end=b.length}else{ini=mid-2000;end=mid+2000}}b=b.substring(ini,end);return b}},getEditor:function(){if(!document.getElementsByTagName("pre")[0]){body=document.getElementsByTagName("body")[0];if(!body.innerHTML){return body}if(body.innerHTML=="<br>"){body.innerHTML="<pre> </pre>"}else{body.innerHTML="<pre>"+body.innerHTML+"</pre>"}}return document.getElementsByTagName("pre")[0]},syntaxHighlight:function(a){if(a!="init"){window.getSelection().getRangeAt(0).insertNode(document.createTextNode(cc))}editor=CodePress.getEditor();o=editor.innerHTML;o=o.replace(/<br>/g,"\n");o=o.replace(/<.*?>/g,"");x=z=this.split(o,a);x=x.replace(/\n/g,"<br>");if(arguments[1]&&arguments[2]){x=x.replace(arguments[1],arguments[2])}for(i=0;i<Language.syntax.length;i++){x=x.replace(Language.syntax[i].input,Language.syntax[i].output)}editor.innerHTML=this.actions.history[this.actions.next()]=(a=="scroll")?x:o.split(z).join(x);if(a!="init"){this.findString()}},getLastWord:function(){var a=CodePress.getRangeAndCaret();words=a[0].substring(a[1]-40,a[1]);words=words.replace(/[\s\n\r\);\W]/g,"\n").split("\n");return words[words.length-1].replace(/[\W]/gi,"").toLowerCase()},snippets:function(a){var d=Language.snippets;var b=this.getLastWord();for(var c=0;c<d.length;c++){if(d[c].input==b){var e=d[c].output.replace(/</g,"&lt;");e=e.replace(/>/g,"&gt;");if(e.indexOf("$0")<0){e+=cc}else{e=e.replace(/\$0/,cc)}e=e.replace(/\n/g,"<br>");var f=new RegExp(b+cc,"gi");a.preventDefault();this.syntaxHighlight("snippets",f,e)}}},readOnly:function(){document.designMode=(arguments[0])?"off":"on"},complete:function(b){window.getSelection().getRangeAt(0).deleteContents();var a=Language.complete;for(var c=0;c<a.length;c++){if(a[c].input==b){var e=new RegExp("\\"+b+cc);var d=a[c].output.replace(/\$0/g,cc);parent.setTimeout(function(){CodePress.syntaxHighlight("complete",e,d)},0)}}},getCompleteChars:function(){var a="";for(var b=0;b<Language.complete.length;b++){a+="|"+Language.complete[b].input}return a+"|"},getCompleteEndingChars:function(){var a="";for(var b=0;b<Language.complete.length;b++){a+="|"+Language.complete[b].output.charAt(Language.complete[b].output.length-1)}return a+"|"},completeEnding:function(b){var a=window.getSelection().getRangeAt(0);try{a.setEnd(a.endContainer,a.endOffset+1)}catch(c){return false}var d=a.toString();a.setEnd(a.endContainer,a.endOffset-1);if(d!=b){return false}else{a.setEnd(a.endContainer,a.endOffset+1);a.deleteContents();return true}},shortcuts:function(){var a=arguments[0];if(a==13){a="[enter]"}else{if(a==32){a="[space]"}else{a="["+String.fromCharCode(charCode).toLowerCase()+"]"}}for(var b=0;b<Language.shortcuts.length;b++){if(Language.shortcuts[b].input==a){this.insertCode(Language.shortcuts[b].output,false)}}},getRangeAndCaret:function(){var b=window.getSelection().getRangeAt(0);var a=b.cloneRange();var c=b.endContainer;var d=b.endOffset;a.selectNode(c);return[a.toString(),d]},insertCode:function(f,d){var b=window.getSelection().getRangeAt(0);var e=window.document.createTextNode(f);var c=window.getSelection();var a=b.cloneRange();c.removeAllRanges();b.deleteContents();b.insertNode(e);a.selectNode(e);a.collapse(d);c.removeAllRanges();c.addRange(a)},getCode:function(){if(!document.getElementsByTagName("pre")[0]||editor.innerHTML==""){editor=CodePress.getEditor()}var a=editor.innerHTML;a=a.replace(/<br>/g,"\n");a=a.replace(/\u2009/g,"");a=a.replace(/<.*?>/g,"");a=a.replace(/&lt;/g,"<");a=a.replace(/&gt;/g,">");a=a.replace(/&amp;/gi,"&");return a},setCode:function(){var a=arguments[0];a=a.replace(/\u2009/gi,"");a=a.replace(/&/gi,"&amp;");a=a.replace(/</g,"&lt;");a=a.replace(/>/g,"&gt;");editor.innerHTML=a;if(a==""){document.getElementsByTagName("body")[0].innerHTML=""}},actions:{pos:-1,history:[],undo:function(){editor=CodePress.getEditor();if(editor.innerHTML.indexOf(cc)==-1){if(editor.innerHTML!=" "){window.getSelection().getRangeAt(0).insertNode(document.createTextNode(cc))}this.history[this.pos]=editor.innerHTML}this.pos--;if(typeof(this.history[this.pos])=="undefined"){this.pos++}editor.innerHTML=this.history[this.pos];if(editor.innerHTML.indexOf(cc)>-1){editor.innerHTML+=cc}CodePress.findString()},redo:function(){this.pos++;if(typeof(this.history[this.pos])=="undefined"){this.pos--}editor.innerHTML=this.history[this.pos];CodePress.findString()},next:function(){if(this.pos>20){this.history[this.pos-21]=undefined}return ++this.pos}}};Language={};window.addEventListener("load",function(){CodePress.initialize("new")},true);