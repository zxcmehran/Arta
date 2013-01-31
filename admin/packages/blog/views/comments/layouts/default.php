<?php if(!defined('ARTA_VALID')){die('No access');}
$ml=$this->get('ml');
$comments=$this->get('comments');

ArtaTagsHtml::addCSS('packages/blog/assets/style.css');

ArtaTagsHtml::addHeader('<style>table.commentTable, table.commentTable tr, table.commentTable tr td.commentTitle, table.commentTable tr td.commentContent{width:95%;}
</style>');

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
}</script>');

?>

<?php
if($ml==true){
	
	ArtaTagsHtml::addLibraryScript('livepipe_tabs');
	$t=ArtaLoader::template();
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
<ul id="commentLangs" class="tabs">
<?php
	foreach($langs as $name=>$lang){
		
		if(@count($comments[$name])){
			if(isset($_first)==false){
				$x=' class="active"';
				$_first=true;
			}else{
				$x='';
			}
			echo '<li class="tab"><a'.$x.' href="#lang_'.$lang->id.'">'.htmlspecialchars($lang->title).'</a></li>';
		}
	}
	if($used==true){
		echo '<li class="tab"><a href="#lang_other">'.trans('OTHER LANGS').'</a></li>';
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
		echo '<div id="lang_'.$name.'"><table class="commentTable">';
		if(count($com)){
			foreach($com as $c){
				createRow($c);
			}
		}else{
			echo '<tr><td>'.ArtaTagsHtml::msgBox(trans('NO COMMENTS FOUND')).'</td></tr>';
		}
		echo '</table></div>';
		
	}
}else{
	echo ArtaTagsHtml::msgBox(trans('NO COMMENTS FOUND'));
}
?>
	
<?php
	}else{
		/*$c=array();
		foreach($comments as $com){
			$c=array_merge($c, $com);
		}
		$comments=$c;*/
	
?>
<table class="commentTable">
<?php

if(count($comments)){
	foreach($comments as $com){
		createRow($com);
	}
}else{
	echo '<tr><td>'.ArtaTagsHtml::msgBox(trans('NO COMMENTS FOUND')).'</td></tr>';
}
?>
</table>

<?php
}
	function createRow($com){
		if(!isset($GLOBALS['__CREATEROW_I'])){
			$GLOBALS['__CREATEROW_I']=1;
		}

		echo '<tr><td style="padding-'.(trans('_LANG_DIRECTION')=='ltr'?'left':'right').': '.($com->level*25).'px;">';
		
		echo '<div class="comment level'.$com->level.(($com->published==false)?' unpublished': '').'">';

		$u=ArtaLoader::User();
		$cu=$u->getCurrentUser();
		$u=$u->getUser($com->added_by);
		$um=@unserialize($u->misc);
		
		echo '<table><tr><td class="commentNumber"><a name="comment-'.$GLOBALS['__CREATEROW_I'].'">#'.$GLOBALS['__CREATEROW_I'].'</a></td><td class="commentTitle">';
		echo '<span style="float:'.(trans('_LANG_DIRECTION')!=='rtl'?'left':'right').'">'.htmlspecialchars($com->title).'<br/>'.trans('POINTS').': '.$com->points.'</span>';
		echo '<span style="float:'.(trans('_LANG_DIRECTION')=='rtl'?'left':'right').'">'.ArtaDate::_($com->added_time);
		if(@trim($um->weburl)!=''){
			echo '<br/><a href="'.htmlspecialchars($um->weburl).'" target="_blank">'.htmlspecialchars($um->weburl).'</a>';
		}
		echo '</span>';
		echo '</td></tr><tr><td class="commentAvatar" style="vertical-align:top;"><center><table class="avatarTable"><tr><td class="avatarTable_image">';
		if($u!=null && $u->id!=0){
			echo '<a href="index.php?pack=user&view=new&ids[]='.$com->added_by.'">';
			echo'<img src="'.ArtaURL::getSiteURL().'index.php?pack=user&view=avatar&type=jpg&uid='.$com->added_by.'"/>';
		}else{			
			echo '<img src="http://www.gravatar.com/avatar/'.md5($com->authormail).'.jpg?size=100&d='.urlencode(ArtaURL::getSiteURL().'media/avatars/unknown.jpg').'"/>';
		}
		if($u!=null && $u->id!=0){
			echo '</a>';
		}
		echo '</td></tr><tr><td class="avatarTable_uname" nowrap="nowrap">';
		if($u==null || $u->id==0){
			echo htmlspecialchars($com->author!=null?$com->author:trans('UNKNOWN'));
			echo '<br/>';
			echo 'E-Mail: '.htmlspecialchars($com->authormail!=null?$com->authormail:trans('UNKNOWN'));
		}else{
			echo '<a href="index.php?pack=user&view=new&ids[]='.$com->added_by.'">';
			echo htmlspecialchars($u->username);
			echo '</a>';
		}
		echo'</td></tr>';
		echo '</table></center>';
		
		echo '</td><td class="commentContent">';
		echo nl2br(htmlspecialchars($com->content));
		
		echo '<span style="float:'.(trans('_LANG_DIRECTION')=='rtl'?'left':'right').'"><table align="'.(trans('_LANG_DIRECTION')=='rtl'?'left':'right').'">';
		
				
		echo '<tr><td>'.trans('EDIT').'</td><td><a href="index.php?pack=blog&view=comments&cid='.$com->id.'&id='.$com->postid.'"><img src="'.Imageset('edit_small.png').'"/></a></td></tr>';	
		
		echo '<tr><td>'.trans('PUBLISHED').'</td><td>'.ArtaTagsHtml::BooleanControls($com->published, 'index.php?pack=blog&controller=comments&task=publish&cid='.$com->id, 'index.php?pack=blog&controller=comments&task=unpublish&cid='.$com->id, 'onSuccForCommentPub').'</td></tr>';
	
	
		echo '<tr><td>'.trans('DELETE').'</td><td><a onclick="if(!confirm(\''.htmlspecialchars(trans('ARE YOU SURE TO DELETE THIS COMMENT')).'\')) return false;" href="index.php?pack=blog&controller=comments&task=delete&cid='.$com->id.'&token='.ArtaSession::genToken().'&id='.$com->postid.'"><img src="'.Imageset('delete_small.png').'"/></a></td></tr>';
	
		echo '</table></span>';
		
	//	echo '</td></tr><tr><td></td><td>';

		echo '</td></tr></table>';
		
		echo'</p></td></tr>';
		$GLOBALS['__CREATEROW_I']++;
	}
	
?>
