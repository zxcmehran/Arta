<?php
if(!defined('ARTA_VALID')){die('No access');}
ArtaTagsHtml::addHeader('<style>
table.blogpost_footers td{
	width: 99%;
}
td.blogpost_icon {
	height: 16px;
	text-align:center;
/*	width: 16px;
	max-width: 16px;
	width:auto !important;*/ 
	width: 16px;
}
</style>');

$id=getVar('blogid', 0, '','int');

$m=$this->get('m');
$items=$this->get('items');
if(is_array($items)==false || count($items)==0){
	echo ArtaTagsHtml::msgBox(trans('NO POSTS FOUND'));
}else{
	$p=ArtaLoader::Plugin();
	$i=0;
	$u=$this->getCurrentUser();
	
	if((int)$id>0 || getVar('tagname','','','string')!=''){
		echo '<a style="float:'.(trans('_LANG_DIRECTION')=='ltr'?'right':'left').'" href="index.php?pack=blog&view=last">'.trans('SHOW ALL BLOG POSTS').'</a>';
	}
	foreach($items as $k=>$v){
		echo '<article>';
		echo '<div class="blogpost" id="blogpost_'.$v->id.'">';
		echo '<header>';
		echo '<h2 class="blogpost_title"><a name="post_'.$v->id.'" href="index.php?pack=blog&view=post&id='.$v->id.'">'.htmlspecialchars($v->title)."</a></h2>\n";

		echo '<table class="blogpost_headers" width="100%"><tr>';
		echo "<td width=\"%30\" nowrap=\"nowrap\">".trans('AUTHOR').': <a href="index.php?pack=user&view=profile&uid='.$v->added_by_id.'">'.htmlspecialchars($v->added_by)."</a></td>\n";
		echo '<td width="40%"><!-- added_time: '.ArtaDate::Translate($v->added_time, 'r').' -->'.trans('ADDED_TIME').' <time datetime="'.ArtaDate::toHTML5($v->added_time).'" pubdate>'.ArtaDate::_($v->added_time)."</time></td><td width=\"30%\">".trans('hits').': '.$v->hits."</td></tr>\n";
		
		$catz=$this->getCategoryPath($v->blogid->id,$m);
		echo '<tr><td colspan="3" width="100%"><!-- category: '.implode('->',$catz).'  -->'.trans('CATEGORY').': <a href="index.php?pack=blog&view=index&blogid='.$v->blogid->id.'">'.implode('<span class="pathway_separator"></span>',$catz)."</a></td></tr>\n";
		$p->trigger('onAfterShowPostHeaders', array(&$v));
		
		echo "</table></header><br/>\n";
		
		$p->trigger('onBeforeShowPostIntro', array(&$v));
		
		echo "<div class=\"blogpost_content\">\n";
		
		$p->trigger('onShowBody', array(&$v->introcontent, 'blogpost-intro'));;
		echo $v->introcontent;
		echo "</div><br/><br/>\n";
		
		$p->trigger('onAfterShowPostIntro', array(&$v));
		
		echo '<footer><table width="100%" class="blogpost_footers">';
		if($v->mod_time && $v->mod_time!=='0000-00-00 00:00:00' && $v->mod_time!=='1970-01-01 00:00:00'){
			echo '<tr><td colspan="6">'.trans('MOD_BY').' <a href="index.php?pack=user&view=profile&uid='.$v->mod_by_id.'">'.htmlspecialchars($v->mod_by)."</a></td></tr>\n";
			echo '<tr><td colspan="6"><!-- mod_time: '.ArtaDate::Translate($v->mod_time, 'r').' -->'.trans('MOD_TIME').' <time datetime="'.ArtaDate::toHTML5($v->mod_time).'">'.ArtaDate::_($v->mod_time)."</time></td></tr>\n";
		}
		echo '<tr><td>';
		if($v->morecontent){
			echo '<a href="index.php?pack=blog&view=post&id='.$v->id.'" class="readmore">'.trans('readmore').'</a>';
		}
		echo '</td>';
		
		echo '<td class="blogpost_icon">';
		
		if($v->attachments > 0){
			echo '<a href="index.php?pack=blog&view=post&id='.$v->id.'#attachments"><img src="'.Imageset('attachment.png').'" alt="'.$v->attachments.' '.trans('ATTACHMENTS').'" title="'.$v->attachments.' '.trans('ATTACHMENTS').'"/></a>';
		}
		echo '</td>';
		
		echo '<td class="blogpost_icon">';
		if($v->comments > 0 && ArtaUsergroup::getPerm('can_access_post_comments', 'package', 'blog')){
			echo '<a href="index.php?pack=blog&view=post&id='.$v->id.'#comment-1"><img src="'.Imageset('comment.png').'" alt="'.$v->comments.' '.trans('COMMENTS').'" title="'.$v->comments.' '.trans('COMMENTS').'"/></a>';
		}
		echo '</td>';
		
		echo '<td class="blogpost_icon">';
		if(strlen(trim($v->tags))>0){
			$tags=explode(',',$v->tags);
			array_map('trim', $tags);
			$v->tags=implode(', ',$tags);
			echo '<!-- tags: '.htmlspecialchars($v->tags).'  --><img src="'.Imageset('tag.png').'" alt="'.htmlspecialchars($v->tags).'" title="'.htmlspecialchars($v->tags).'"/>';
		}
		echo '</td>';
		
		echo '<td class="blogpost_icon">';
		if($this->getSetting('blogposts_generate_pdf_version', true)){
			echo '<a href="index.php?pack=blog&view=post&type=pdf&id='.$v->id.'" target="_blank"><img src="'.Imageset('pdf_small.png').'" alt="PDF" title="PDF"/></a>';
		}
		echo '</td>';
		
		
		echo '<td class="blogpost_icon">';
		if(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog') && 
		($v->added_by==null || $v->added_by==$u->id || ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog')!==false)){
			$add=true;
			echo '<a href="index.php?pack=blog&view=new&id='.$v->id.'" onclick="if(openingEditor==null){openingEditor=setTimeout(\'openAjaxEditor(\\\''.$v->id.'\\\');\', 500);}return false;" ondblclick="clearTimeout(openingEditor);openingEditor=null;location.href=this.href;"><img src="'.Imageset('edit_small.png').'" alt="'.trans('edit post').'" title="'.trans('edit post desc').'"/></a>';
		}
		echo '</td>';
		
		echo '<td class="blogpost_icon">';

		if(count($v->langs)>0){
			echo '<img width="16" height="16" src="'.Imageset('languages.png').'" alt="'.trans('AVAILABLE TRANSLATIONS').'" title="'.trans('AVAILABLE TRANSLATIONS').': '.htmlspecialchars(implode(', ',$v->langs)).'"/>';
		}
		echo '</td>';
		
		echo '</tr></table></footer>';
		echo '<br /><div class="post_separator">&nbsp;</div>';
		echo '</div>';
		echo '</article>';
	}
	
	if(getVar('tagname','','','string')!=''){
		$suf='&tagname='.urlencode(getVar('tagname','','','string'));
	}else{
		$suf='';
	}
	
	
	$c=ArtaLoader::Config();
	$rsspath=trim((string)$this->getSetting('rssfeed_alternate_url'));
	if($rsspath==''){
		$rsspath='index.php?pack=blog&view=last&type=xml'.$suf;
	}
	
	if($id>0){
		$ar=@array_shift($this->get('items'));
		ArtaTagsHtml::addRSS('index.php?pack=blog&view=last&type=xml&blogid='.$id.$suf, $ar->blogid->title.' - '.$c->site_name);
		$url="index.php?pack=blog&view=index&blogid=".$id.$suf;
		$xurl="index.php?pack=blog&type=xml&blogid=".$id.$suf;
	}else{
		$url="index.php?pack=blog&view=index".$suf;
		$xurl=$rsspath;
	}
	
	ArtaTagsHtml::addRSS($rsspath, trans('BLOG').' - '.$c->site_name);
	echo '<nav>';
	echo '<table width="100%" class="blogpost_controls"><tr><td>'.ArtaTagsHtml::SortControls(array(
		'added_time'=>trans('added_time'),
		'hits'=>trans('hits'),
		'rating'=>trans('rating')
	), 'added_time', 'desc').'</td><td nowrap="nowrap"><a href="'.$xurl.'"><img src="'.Imageset('rss.png').'"/>'.trans('RSS FEED').'</a> &nbsp; <a href="'.$url.'">'.trans('LIST LAYOUT').'</a></td></tr></table>';
	
	
	echo ArtaTagsHtml::LimitControls($this->get('count'));
	echo '</nav>';

	
}

if(isset($add)){
	
	ArtaTagsHtml::addLibraryScript('tinymce');
	ArtaTagsHtml::addHeader('<script>
	var openingEditor=null;
	var alreadyOpenedEditor=false;
	var alreadyEditingContent=false;
	var cururl=location.href;
	function openAjaxEditor(pid){
		if(alreadyOpenedEditor==true){
			alert("'.trans('YOU ARE ALREADY EDITING SOMETHING').'");
			return;
		}
		elm=$("blogpost_"+pid);
	
		if(elm!=null){
  		    location.href=cururl+"#post_"+pid;
			p=elm.select("div.blogpost_content")[0];
			alreadyEditingContent=p.innerHTML;
			p.update(\'<div id="editArea" style="text-align:center;width:100%;"><img src="'.Imageset('loading_bar.gif').'" id="loadingImg" alt="Loading..." align="center"/></div>\');
			new Ajax.Request(site_url+"index.php?pack=blog&task=ajaxGetContent&id="+pid+"&token='.ArtaSession::genToken().'", {
				method:"get",
				onSuccess: function(tr){
					xmldoc=tr.responseXML;
					makeControls(xmldoc.documentElement.firstChild.firstChild.nodeValue,
					xmldoc.documentElement.firstChild.nextSibling.firstChild.nodeValue,
					pid);
					$("loadingImg").hide();
					alreadyOpenedEditor=true;
				},
				onFailure: function(tr){
					alert("'.trans('AN ERROR OCCURED').'");
					cancelEditing(pid, alreadyEditingContent);	
					alreadyOpenedEditor=false;
				}
			});
		}
		openingEditor=null;	
	}
	
	function makeControls(title, content,pid){
		elm=$("editArea");
		elm.insert(\''.trans('POST TITLE').': <input name="ptitle" id="ptitle" value="\'+title.escapeHTML()+\'"/>\');
		elm.insert(\'<textarea style="width:100%;height:250px;" name="pcontent" id="pcontent">\'+content.escapeHTML()+\'</textarea>\');
		elm.insert(\'<input type="button" onclick="cancelEditing(\'+pid+\', alreadyEditingContent);alreadyOpenedEditor=false;" value="'.trans('CANCEL').'"/>\');
		elm.insert(\' <input type="button" onclick="submitEditing(\'+pid+\')" value="'.trans('SUBMIT').'" id="submitHandle"/>\');
		//elm.insert(\' | <input type="button" onclick="addReadmore()" value="'.htmlspecialchars(trans('INSERT READMORE')).'"/>\');
		elm.insert(\' | <a href="#post_\'+pid+\'" onclick="addReadmore()">'.htmlspecialchars(trans('INSERT READMORE')).'</a>\');
		elm.insert(\' | <a hr\'+\'ef="\'+site_url+\'index.php?pack=blog&view=new&id=\'+pid+\'">'.trans('ADVANCED EDITING').'</a>\');
		elm.insert(\' | <a href="#post_\'+pid+\'" onclick="if(this.innerHTML==\\\'&times;\\\'){tinyMCE.get(\\\'pcontent\\\').hide();this.innerHTML=\\\'o\\\';}else{tinyMCE.get(\\\'pcontent\\\').show();this.innerHTML=\\\'&times;\\\';}" style="font-weight:bold; color:red; font-size:16px;">&times;</a>\');
		
		tinyMCE.init({
			mode: "exact",
			elements: "pcontent",
			skin: "o2k7",
			document_base_url: site_url,
			plugins : "codetag,safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,inlinepopups,tabfocus",
			theme : "advanced",
			theme_advanced_buttons1 : "undo,redo,|,cut,copy,paste,pastetext,pasteword,|,codetag,styleprops,|,link,unlink,image,emotions,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,ltr,rtl,|,help",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_buttons4 : "",
			theme_advanced_toolbar_location : "bottom",
			theme_advanced_toolbar_align : "center",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true
	
			,content_css : site_url+"media/scripts/tiny_mce/style.css"
	
		});
	}
	
	function cancelEditing(pid, a){
		elm=$("blogpost_"+pid);
		c=elm.select("div.blogpost_content")[0];
		c.update(a);
		alreadyOpenedEditor=false;
		alreadyEditingContent=false;
	}
	
	function submitEditing(pid){
		if($("ptitle").value.match(/^ *$/g)!==null){
			alert("'.JSValue(trans('NO POST TITLE SPECIFIED')).'");
			return false;
		}
		$("submitHandle").value="'.trans('PLEASE WAIT').'";
		al=alreadyEditingContent;
		new Ajax.Request(site_url+"index.php?pack=blog&task=ajaxSetContent&id="+pid+"&token='.ArtaSession::genToken().'", {
			method: "post",
			parameters: {title: $("ptitle").value, content:tinyMCE.get("pcontent").getContent()},
			onSuccess: function(transport) {
				xmldoc=transport.responseXML;
				elm=$("blogpost_"+pid);
				c=elm.select("div.blogpost_content")[0];
				t=elm.select("h2.blogpost_title a")[0];
				
				pt=$("ptitle").value;
				pcon=tinyMCE.get("pcontent").getContent();
				
				if(pcon.indexOf("<hr id=\"readmore_handler\" />")>=0){
					pcon=pcon.substr(0, pcon.indexOf("<hr id=\"readmore_handler\" />"));
				}
				pcon = pcon.replace(/(href|src|action)="([^"]*)/, "$1=\"'.ArtaURL::getDir().'$2\"");
				
				c.update(pcon);
				t.update(pt);
				alert(xmldoc.documentElement.firstChild.firstChild.nodeValue);
				alreadyOpenedEditor=false;
				alreadyEditingContent=false;
			},
			onFailure: function(transport) {
				alert("'.trans('AN ERROR OCCURED').'");
				cancelEditing(pid, al);
			}
		});
	}
	function addReadmore(){ 
		separator=\'<hr id="readmore_handler" />\';
		var content = tinyMCE.get(\'pcontent\').getContent();
		if (content.match(/<hr id="readmore_handler" \/>/)) {
			return false;
		} else {
			command= tinymce.isGecko ? "InsertHTML" : "mceInsertContent";
			tinyMCE.get(\'pcontent\').execCommand(command,false,separator);
			return true;
		}
	}
	</script>
	');

}
?>