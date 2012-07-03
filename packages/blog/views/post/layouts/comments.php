<?php if(!defined('ARTA_VALID')){die('No access');}

ArtaTagsHtml::addCSS('packages/blog/assets/style.css');

$ml=$this->get('ml');
$comments=$this->get('comments');
$canedit=$this->get('canedit');
$caneditothers=$this->get('caneditothers');
$canpub=$this->get('canpub');
$candel=$this->get('candel');
$canacc=$this->get('canacc');
$canleave=$this->get('canleave');
$cantouch=$this->get('cantouch');

$cu=$this->getCurrentUser();
$t=ArtaLoader::template();

foreach($comments as $vv=>$vvv){
	if($vvv->published==false&&$canacc==false&&($cu->id!=$vvv->added_by||$cu->id==0)){
		unset($comments[$vv]);
	}
}


ArtaTagsHtml::addHeader('<style>table.commentTable, table.commentTable tr, table.commentTable tr td.commentTitle, table.commentTable tr td.commentContent{width:95%;}
</style>');
//echo ArtaTagsHtml::FilterControls('published', array(trans('NO'),trans('YES')), trans('PUBLISHED'));
?>


<?php

if(count($comments)){
		ArtaTagsHtml::addHeader('<script>function onSuccForCommentPub(img,value){
	p=img.parentNode;
	while(p.hasClassName("comment")==false){
		p=p.parentNode;
	}
	if(value==true){
		p.removeClassName("unpublished");
	}else{
		p.addClassName("unpublished");
	}
}

function updatePoint(cid,grow){
	new Ajax.Request(client_url+"index.php?pack=blog&controller=comments&task=updatePoint&grow="+(grow?1:0)+"&cid="+parseInt(cid), {
		method: "get",
		parameters: {token: "'.ArtaSession::genToken().'"},
		onSuccess: function(transport) {
			p=parseInt($("points_"+cid).innerHTML)+(grow?1:-1);
			$("points_"+cid).innerHTML= p>0? "+"+p : p;
		},
		onFailure: function(transport){
			errmsg=\''.JSValue(trans('ERROR')).'\';
			if(transport.responseText.match(/<span class="errornum">(.*)<\/span>/i)){
				errmsg+=": "+(transport.responseText.match(/<span class="errornum">(.*)<\/span>/mi)[1]);
			}
			if(transport.responseText.match(/<span class="errortype">(.*)<\/span>/i)){
				errmsg+=" - "+(transport.responseText.match(/<span class="errortype">(.*)<\/span>/mi)[1]);
			}
			if(transport.responseText.match(/<div class="errormsg">(.*)<\/div>/i)){
				errmsg+="\n"+(transport.responseText.match(/<div class="errormsg">(.*)<\/div>/mi)[1]);
			}
			alert(errmsg);
		}
		
	});
}

</script>');
}

if($ml==true){ // with multilingual
	
	ArtaTagsHtml::addLibraryScript('livepipe_tabs');
	
	$t->addtoTmpl('<script>new Control.Tabs(\'commentLangs\');</script>', 'beforebodyend');
	$c=array();
	foreach($comments as $com){
		$c[$com->language][]=$com;
	}
	$comments=$c;
	$used=false;
	$langs=ArtaUtility::keyByChild($this->get('langs'), 'id');
	foreach($comments as $lang=>$com){
		if(!isset($langs[$lang])){
			$used=true;
			break;
		}
	}
	
?>
<div class="tabs_container">
	<ul id="commentLangs" class="tabs"><?php
	foreach($langs as $name=>$lang){
		
		if(@count($comments[$name])){
			if(isset($_first)==false){
				$x=' class="active"';
				$_first=true;
			}else{
				$x='';
			}
			echo "\n\t\t".'<li class="tab"><a'.$x.' href="#lang_'.$lang->id.'">'.htmlspecialchars($lang->title)."</a></li>";
		}
	}
	if($used==true){
		echo "\n\t\t".'<li class="tab"><a href="#lang_other">'.trans('OTHER LANGS')."</a></li>";
	}
?>

	</ul>
</div>

<br/><br/>

<?php
	if(count($comments)){
		foreach($comments as $lang=>$com){
			$name=$lang;
			if(!isset($langs[$name])){
				$name='other';
			}
			echo '<div id="lang_'.$name.'">';
			if(count($com)){
				foreach($com as $c){
					createRow($c,$canedit,$caneditothers,$canpub,$candel,$canacc,$canleave,$cantouch);
				}
			}else{
				echo ArtaTagsHtml::msgBox(trans('NO COMMENTS FOUND'));
			}
			echo '</div>';
			
		}
	}else{
		echo ArtaTagsHtml::msgBox(trans('NO COMMENTS FOUND'));
	}
?>
	
<?php
}else{ // without multilingual
	
	if(count($comments)){
			
		foreach($comments as $com){
			createRow($com,$canedit,$caneditothers,$canpub,$candel,$canacc,$canleave,$cantouch);
		}
	}else{
		echo ArtaTagsHtml::msgBox(trans('NO COMMENTS FOUND'));
	}
}

function createRow($com,$canedit,$caneditothers,$canpub,$candel,$canacc,$canleave,$cantouch){
		if(!isset($GLOBALS['__CREATEROW_I'])){
			$GLOBALS['__CREATEROW_I']=1;
		}
		
		$u=ArtaLoader::User();
		$cu=$u->getCurrentUser();
		
		if($com->published==false&&$canacc==false&&($cu->id!=$com->added_by||$cu->id==0)){
			return; //leave this one if no access or not published
		}
		echo '<article>';
		echo '<div style="margin-'.(trans('_LANG_DIRECTION')=='ltr'?'left':'right').': '.($com->level*25).'px;" class="comment level'.$com->level.($com->published==false&&($canpub||$cu->id==$com->added_by)?' unpublished': '').'">';
				
		$u=$u->getUser($com->added_by);
		$um=@unserialize($u->misc);
		
		echo '
<table>
	<tr>
		<td rowspan=2 class="commentNumber commentAvatar" style="vertical-align:top; padding:0px;"><footer><a href="#comment-'.$GLOBALS['__CREATEROW_I'].'" name="comment-'.$GLOBALS['__CREATEROW_I'].'">#'.$GLOBALS['__CREATEROW_I'].'</a>';
		
		echo '<span style="float:'.(trans('_LANG_DIRECTION')=='rtl'?'left':'right').'">';
		
		echo '<span class="commentPoints">';
		echo $cantouch?'<a onclick="updatePoint('.$com->id.', false)" style="cursor:pointer;">&#9660;</a> ':'&#9660;';
		echo '<span id="points_'.$com->id.'">'.($com->points>0?"+".$com->points:$com->points).'</span>';
		echo $cantouch?' <a onclick="updatePoint('.$com->id.', true)" style="cursor:pointer;">&#9650;</a>':'&#9650;';
		echo '</span>';
		echo '</span>';
		
		
		echo '<center>
		<table class="avatarTable">
		<tr>
			<td class="avatarTable_image">';
			
		if($u!=null && $u->id!=0){
			echo '<a href="index.php?pack=user&view=profile&uid='.$com->added_by.'"><img src="index.php?pack=user&view=avatar&type=jpg&uid='.$com->added_by.'"/></a>';
		}else{			
			echo '<img src="http://www.gravatar.com/avatar/'.md5($com->authormail).'.jpg?size=100&d='.urlencode(ArtaURL::getSiteURL().'media/avatars/unknown.jpg').'"/>';
		}

		echo '
			</td>
		</tr>
		<tr>
			<td class="avatarTable_uname">';
		if($u==null || $u->id==0){
			echo htmlspecialchars($com->author!=null?$com->author:trans('UNKNOWN'));
		}else{
			echo '<a href="index.php?pack=user&view=profile&uid='.$com->added_by.'">';
			echo htmlspecialchars($u->username);
			echo '</a>';
		}
	
		echo'
			</td>
		</tr>';
		echo ArtaTagsHtml::AvatarTableDesc($u,true);
		echo '
		</table>
		</center></footer>';
		
		
		echo '</td>
		<td class="commentTd" style="vertical-align:top; padding:0px;"><header><div class="commentTitle">';
	
		echo '<span style="float:'.(trans('_LANG_DIRECTION')!=='rtl'?'left':'right').'">'.htmlspecialchars($com->title).'</span>';
		echo '<span style="float:'.(trans('_LANG_DIRECTION')=='rtl'?'left':'right').'">';
		
		if(@trim($um->weburl)!=''){
			echo '<a href="'.htmlspecialchars($um->weburl).'" target="_blank">'.htmlspecialchars($um->weburl).'</a> &nbsp;&nbsp;';
		}
		
		echo '<a href="#comment-'.$GLOBALS['__CREATEROW_I'].'"><!-- comment_added_time : '.ArtaDate::Translate($com->added_time, 'r').' --><time datetime="'.ArtaDate::toHTML5($com->added_time).'" pubdate>'.ArtaDate::_($com->added_time).'</time></a>';
		echo '</span>';
		echo '</div></header>';
		
		echo '<div class="commentContent">';		
		if($canleave || ($canedit && (($u->id==$cu->id && $u->id!=0) || $caneditothers)) || $canpub || $candel){ 
			echo '<span style="float:'.(trans('_LANG_DIRECTION')=='rtl'?'left':'right').'"><table align="'.(trans('_LANG_DIRECTION')=='rtl'?'left':'right').'">';
			
			if($canleave){
				echo '<tr><td>'.trans('REPLY').'</td><td>';
				echo '<a href="#newcomment" onclick="$(\'replyto_title\').innerHTML=\'#'.$GLOBALS['__CREATEROW_I'].' '.htmlspecialchars(str_replace('\'','\\\'',$com->title)).'\';$(\'replyto_value\').value=\''.$com->id.'\';$(\'replyto_msg\').show();setDis(true, '.$com->language.');">';
				echo '<img src="'.imageset('reply.png').'"/></a></td></tr>';
			}
			
			if($canedit && @(($u->id==$cu->id && $u->id!=0) || $caneditothers)){
				echo '<tr><td>'.trans('EDIT').'</td><td><a href="index.php?pack=blog&view=comments&cid='.$com->id.'&id='.$com->postid.'"><img src="'.Imageset('edit_small.png').'"/></a></td></tr>';
			}
			if($canpub){
				echo '<tr><td>'.trans('PUBLISHED').'</td><td>'.ArtaTagsHtml::BooleanControls($com->published, 'index.php?pack=blog&controller=comments&task=publish&cid='.$com->id, 'index.php?pack=blog&controller=comments&task=unpublish&cid='.$com->id, 'onSuccForCommentPub').'</td></tr>';
			}elseif($cu->id==$u->id && $u->id!=0 && $com->published==0){
				echo '<tr><td colspan="2">'.trans('NOT PUBLISHED').'</td></tr>';
			}
			if($candel){
				echo '<tr><td>'.trans('DELETE').'</td><td><a href="index.php?pack=blog&controller=comments&task=delete&cid='.$com->id.'&token='.ArtaSession::genToken().'&id='.$com->postid.'"><img src="'.Imageset('delete_small.png').'"/></a></td></tr>';
			}
			echo '</table></span>';
		}
		
		$plug=ArtaLoader::Plugin();
		$plug->trigger('onShowBody', array(&$com->content, 'blogpost-comment', true));
		echo nl2br(htmlspecialchars($com->content));
		
		echo '</div></td>
	</tr>
</table>
</div>
</article>';
		$GLOBALS['__CREATEROW_I']++;
}
	
	
	
	
echo '<br/><br/><section>';
echo '<h3>'.trans('LEAVE A COMMENT').'</h3>';
if($canleave){
	$this->render('new');
}else{
	$cu=$this->getCurrentUser();
	if($cu->id==0){
		printf(trans('PLEASE LOGIN TO LEAVE COMMENTS'), '<a href="index.php?pack=user">', '</a>');
	}else{
		echo trans('YOU CANNOT LEAVE COMMENTS');
	}
}
echo '</section>';
?>
