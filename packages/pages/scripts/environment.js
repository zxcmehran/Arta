var win;
var dropped;
var containerLastLeft;

function widgets_initialize(){
	widgets=$$('div#widgets_container div.custom_widget');
	elm=Element.cumulativeOffset($('widgets_container'));
	containerLastLeft = elm.left;
	// Page Resizer
	new Resizable($('widgets_container'), {handle: $('canvasResizer'), onResize: setRHPos});
	setRHPos();
	
	// events for dim	
	$$('span.resizeHandle').each(function(ev){;Event.observe(ev, 'mouseout', function(event){
		elmen = $('widgets_container');
		$('dim').innerHTML=DIMS+': '+elmen.getWidth()+'&times;'+elmen.getHeight();
	});});
	
	$$('span.resizeHandle').each(function(ev){;Event.observe(ev, 'mouseover', function(event){elm=event.element().parentNode;$('dim').innerHTML=DIMS+': '+elm.getWidth()+'&times;'+elm.getHeight();});});
		
	for(i=0;i<widgets.length;i++){
		if(widgets[i].style.width=='auto'){
			widgets[i].style.width=widgets[i].getWidth()+"px";
		}
		// draggable
		new Draggable(widgets[i] ,{revert:function(){r=(dropped===false);dropped=null;return r;},snap: _snapFunc, scroll: window});
		// observer
		Event.observe(widgets[i], 'dblclick', function(e){openEditor(e);});
		// resize
		new Resizable(widgets[i], {handle: widgets[i].select('span.resizeHandle')[0], snap: _snapFunc2, onResize: function(drg){
			elm=drg.element;
			$('dim').innerHTML=DIMS+': '+elm.getWidth()+'&times;'+elm.getHeight();
			}});
	}
	// delete handler
	Droppables.add('delete_handler',{accept:['custom_widget'],onDrop:function(e){deleteWidget(e)}});
	
	// new item
	Event.observe($('widgets_container'), 'dblclick', function(e){if(e.element().id=='widgets_container') openNew(curpageid);});
}

function setRHPos(){
	elm = $('canvasResizer');
	element_offset = Element.cumulativeOffset(elm);
	parent_offset = Element.cumulativeOffset($('widgets_container'));
	offset= [element_offset.left - parent_offset.left,
  			 element_offset.top - parent_offset.top];
  	
  	
  	
	_top=
		parent_offset.top+Element.getHeight($('widgets_container')) - Element.getHeight(elm);
/*	if(PAGEDIR=='rtl'){
		_left=
			parent_offset.left;
	}else{*/
		_left=
			parent_offset.left+Element.getWidth($('widgets_container')) - Element.getWidth(elm);
//	}

	Element.setStyle(elm, {display:'block', position: 'absolute', top: _top+'px', left:_left+'px'});
	elm = $('widgets_container');
	$('dim').innerHTML=DIMS+': '+elm.getWidth()+'&times;'+elm.getHeight();
	containerLastLeft = parent_offset.left;
}

function _snapFunc(x,y,draggable){
  function constrain(n, lower, upper) {
    if (n > upper) return upper;
    else if (n < lower) return lower;
    else return n;
  }
 
  element_dimensions = Element.getDimensions(draggable.element);
  del_dim = Element.getDimensions($('delete_handler'));
  del_off=Element.cumulativeOffset($('delete_handler'));
  parent_dimensions = Element.getDimensions($('widgets_container'));
  elm=Element.cumulativeOffset($('widgets_container'));
	
  return[
    constrain(x, elm[0], parent_dimensions.width - element_dimensions.width+elm[0]),
    constrain(y, elm[1], parent_dimensions.height - element_dimensions.height+elm[1])];
}

function _snapFunc2(x,y,draggable){
  function constrain(n, lower, upper) {
    if (n > upper) return upper;
    else if (n < lower) return lower;
    else return n;
  }
 
  element_dimensions = Element.getDimensions(draggable.element);
  parent_dimensions = Element.getDimensions($('widgets_container'));
  
  element_offset = Element.cumulativeOffset(draggable.element);
  parent_offset = Element.cumulativeOffset($('widgets_container'));
  offset= [element_offset.left - parent_offset.left,
  			 element_offset.top - parent_offset.top];

  return[
    constrain(x, 0, parent_dimensions.width - (offset[0])),
    constrain(y, 0, parent_dimensions.height - (offset[1]))];
}

function widgets_move(){
	widgets=$$('div#widgets_container div.custom_widget');
	elm=Element.cumulativeOffset($('widgets_container'));
	for(i=0;i<widgets.length;i++){
		wtop=widgets[i].getStyle('top');
		wtop=parseInt(wtop);
		wleft=widgets[i].getStyle('left');
		wleft=parseInt(wleft);

		widgets[i].setStyle({top: (wtop+elm[1])+'px'});
		widgets[i].setStyle({left: (wleft+elm[0])+'px'});
	}
}

function openEditor(element){
	element=Event.element(element);
	
	while(Element.readAttribute(element, 'class')!=='custom_widget'){
		element=element.parentNode;
		
	}
	win=window.open(site_url+"index.php?pack=pages&view=environment_editor&tmpl=package&id="+Base64.encode(element.id)+"&pid="+(curpageid), "EditorWin",",height=600,width=800,scrollbars");
}

function updateWidget(wid, pid){
	elm=$(wid);
	$('loading_img').show();
	new Ajax.Request(site_url+'index.php?pack=pages&type=xml&task=getWidget&id='+wid+'&token='+TOKEN+'&pid='+pid+'', {method:'post', onSuccess:function(t){
		var XMLdoc = t.responseXML;
		tags=XMLdoc.documentElement.getElementsByTagName('inner');
		elm.innerHTML=tags[0].firstChild.data;
		elm.writeAttribute('style',tags[1].firstChild.data);
		$('loading_img').hide();
		win.close();
		widgets=$$('div#widgets_container div.custom_widget');
		for(i=0;i<widgets.length;i++){
			new Draggable(widgets[i], {revert:function(){r=(dropped===false);dropped=null;return r;},snap: _snapFunc, scroll: window});
			Event.observe(widgets[i], 'dblclick', function(e){openEditor(e);});
			new Resizable(widgets[i], {handle: widgets[i].select('span.resizeHandle')[0], snap: _snapFunc2, onResize: function(drg){
			elm=drg.element;
			$('dim').innerHTML=DIMS+': '+elm.getWidth()+'&times;'+elm.getHeight();
			}});
		}
		// move them
		elmi=Element.cumulativeOffset($('widgets_container'));
		wtop=elm.getStyle('top');
		wtop=parseInt(wtop);
		wleft=elm.getStyle('left');
		wleft=parseInt(wleft);
		
		elm.setStyle({top: (wtop+elmi[1])+'px'});
		elm.setStyle({left: (wleft+elmi[0])+'px'});
	}, onFailure:function(t){$('loading_img').hide();alert(ERROR_IN_CONTACTING_SERVER);}})
}

function addWidget(wid, pid){
	elm=$('widgets_container');
	$('loading_img').show();
	new Ajax.Request(site_url+'index.php?pack=pages&type=xml&task=getWidget&id='+wid+'&token='+TOKEN+'&pid='+pid, {method:'post', onSuccess:function(t){
		var XMLdoc = t.responseXML;
		tags=XMLdoc.documentElement.getElementsByTagName('inner');
		elm.innerHTML=elm.innerHTML+'<div class="custom_widget" id="'+wid+'" style="'+tags[1].firstChild.data+'">'+tags[0].firstChild.data+'</div>';
		$('loading_img').hide();
		win.close();
		widgets=$$('div#widgets_container div.custom_widget');
		for(i=0;i<widgets.length;i++){
			new Draggable(widgets[i], {revert:function(){r=(dropped===false);dropped=null;return r;},snap: _snapFunc, scroll: window});
			Event.observe(widgets[i], 'dblclick', function(e){openEditor(e);});
			new Resizable(widgets[i], {handle: widgets[i].select('span.resizeHandle')[0], snap: _snapFunc2, onResize: function(drg){
			elm=drg.element;
			$('dim').innerHTML=DIMS+': '+elm.getWidth()+'&times;'+elm.getHeight();
			}});
		}
		elmi=Element.cumulativeOffset($('widgets_container'));
		elm=$(wid);
		wtop=elm.getStyle('top');
		wtop=parseInt(wtop);
		wleft=elm.getStyle('left');
		wleft=parseInt(wleft);
		
		elm.setStyle({top: (wtop+elmi[1])+'px'});
		elm.setStyle({left: (wleft+elmi[0])+'px'});
		new Effect.Highlight(elm, {duration: 3});
                Droppables.add('delete_handler',{accept:['custom_widget'],onDrop:function(e){deleteWidget(e)}});

	}, onFailure:function(t){$('loading_img').hide();alert(ERROR_IN_CONTACTING_SERVER);}})
}

function deleteWidget(wid){
	if(confirm(DO_YOU_REALLY_WANT_TO_DELETE_THIS_WIDGET)){
		dropped=true;
		$('loading_img').show();
		
		new Ajax.Request(site_url+'index.php?pack=pages&type=xml&task=delWidget&id='+wid.id+'&token='+TOKEN+'&pid='+curpageid, {method:'post', onSuccess:function(t){
			wid.remove();
			$('loading_img').hide();
			alert(WIDGET_DELETED);
		}, onFailure:function(t){$('loading_img').hide();alert(ERROR_IN_CONTACTING_SERVER);}})
		
	}else{
		dropped=false;
	}

}

function openNew(pid){
	win=window.open(site_url+"index.php?pack=pages&view=environment_editor&tmpl=package&pid="+pid, "EditorWin",",height=600,width=800,scrollbars");
}






var counted= new Array();
var saved_c= new Array();
var saved_all=new Array();
var saved=new Array();

function saveData(){
	$('loading_img').show();
	$('status_bar').innerHTML=TRYING_TO_SAVE+'...'
	
	par=$('widgets_container').select('div.custom_widget');
	i=0;
	while(Object.isUndefined(par[i])==false){
		if(in_array(par[i], counted)==false){
			counted[counted.length]=par[i];
		}
		i++;
	}
	counted[counted.length]='page';
	_sendSaveRequest(0);
	
		
	_sendSaveRequest2();
}

function _sendSaveRequest(i){
	
	par=$('widgets_container').select('div.custom_widget');
	elm=Element.cumulativeOffset($('widgets_container'));

	if(Object.isUndefined(par[i])==true){
		return true;
	}
	
	if(par[i].parentNode.id=='widgets_container' && in_array(par[i], saved)==false){
		wtop=par[i].getStyle('top');
		wtop=parseInt(wtop);
		wleft=par[i].getStyle('left');
		wleft=parseInt(wleft);
		ww=par[i].getWidth();
		wh=par[i].getHeight();

		wtop=(wtop-elm[1]);
		wleft=(wleft-elm[0]);

		ajax_ins=new Ajax.Request(site_url+'index.php?pack=pages&type=xml&task=AjaxsaveWidget&id='+par[i].id+'&pid='+curpageid+'&token='+TOKEN+'&data='+Base64.encode(wtop+'|'+wleft+'|'+ww+'|'+wh), {method:'post', onSuccess:function(t){
			saved_c[saved_c.length]=1;
			$('status_bar').innerHTML=DONE+" ("+saved_c.length+"/"+counted.length+")";
			saved_all[saved_all.length]=1;
			
		}, onFailure:function(t){
			saved_all[saved_all.length]=1;
		}});
		saved[saved.length]=par[i];
		_sendSaveRequest(i+1);
	}else{
		_sendSaveRequest(i+1);
	}
	return false;
}

function _sendSaveRequest2(){
	if(saved_all.length+1==counted.length && saved_c.length+1!=counted.length){
		alert(ERROR_IN_CONTACTING_SERVER);
		$('loading_img').hide();
		$('status_bar').innerHTML=ERROR_IN_CONTACTING_SERVER;
		_rubuildVars();
		return false;
	}
	
	if(saved_all.length+1!=counted.length){
		setTimeout(_sendSaveRequest2,200, saved_all,saved_c,counted);
		return;
	}
	
	
	h=$('widgets_container').getHeight();
	w=$('widgets_container').style.width=='auto' ? 0 : $('widgets_container').getWidth();
	contAlign = $('canvasAligner').align;
	// page
	new Ajax.Request(site_url+'index.php?pack=pages&type=xml&task=AjaxsavePage&pid='+curpageid+'&token='+TOKEN+'&data='+Base64.encode(w+'|'+h+'|'+contAlign), {method:'post', onSuccess:function(t){
			$('status_bar').innerHTML=DONE+" ("+counted.length+"/"+counted.length+")";	
			$('loading_img').hide();
			alert(SAVING_FINISHED);	
			_rubuildVars();	
		}, onFailure:function(t){
			$('status_bar').innerHTML=ERROR_IN_CONTACTING_SERVER;
			$('loading_img').hide();
			alert(ERROR_IN_CONTACTING_SERVER);
			_rubuildVars();	
			}
		});
	
}

function _rubuildVars(){
	counted= new Array();
	saved_c= new Array();
	saved_all=new Array();
	saved=new Array();
	
}


function closeenv(){
	$('loading_img').show();
	new Ajax.Request(site_url+'index.php?pack=pages&type=xml&task=closeenv', {method:'post', onSuccess:function(t){
		XMLdoc = t.responseXML;
		to=XMLdoc.documentElement.firstChild.data;
		document.location.href=to;		
	}, onFailure:function(t){$('loading_img').hide();alert(ERROR_IN_CONTACTING_SERVER);}})
}


if ( typeof window.addEventListener != "undefined" )
    window.addEventListener( "load", widgets_initialize, false );
else if ( typeof window.attachEvent != "undefined" )
    window.attachEvent( "onload", widgets_initialize );
else {
    if ( window.onload != null ) {
        var oldOnload = window.onload;
        window.onload = function ( e ) {
            oldOnload( e );
            widgets_initialize();
        };
    }
    else
        window.onload = widgets_initialize;
}

function setCanvasAlign(cal){
	$('canvasAligner').align=cal;
	contLeft = Element.cumulativeOffset($('widgets_container')).left;
	leftDelta = contLeft - containerLastLeft;
	
	widgets=$$('div#widgets_container div.custom_widget');
	for(i=0;i<widgets.length;i++){
		wleft=widgets[i].getStyle('left');
		wleft=parseInt(wleft);
		widgets[i].setStyle({left: (wleft+leftDelta)+'px'});
	}
	
	containerLastLeft = contLeft;
	
	setRHPos();
}
function setCanvWidAuto(){
	if(confirm(RESET_TO_AUTO)){
		$('widgets_container').style.width='auto';
		setRHPos();
	}
}