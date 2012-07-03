<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/20 12:19 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
function plgProfileVisitorMsgs($owner, $cu){
	$settings=unserialize($owner->settings);
	if(@$settings->disable_private_messaging==true){
		return null;
	}
	
	$subcontent='';
	$db=ArtaLoader::DB();
	if(ArtaUsergroup::getPerm('can_post_visitormessage', 'package', 'user') || $owner->id==$cu->id){
		$subcontent.='<section><span style="display: inline-block;padding-bottom: 15px;cursor:pointer;" onclick="Effect.toggle(\'new_vis_msg\',\'blind\');im=$(\'collapsing_img\');if(im.alt==1){im.src=\''.makeURL(Imageset('collapse.png')).'\';im.alt=0;}else{im.src=\''.makeURL(Imageset('uncollapse.png')).'\';im.alt=1;}">
		
		<img alt="1" src="'.Imageset('uncollapse.png').'" id="collapsing_img"/> '.trans('LEAVE A MESSAGE').'</span>';
		$subcontent.='<div style="display:none;" id="new_vis_msg"><div style="padding:5px;">'.trans('TITLE').': <input name="title"/></div>'.ArtaTagsHtml::addEditor('content', '', array('toolbarset'=>'mini', 'height'=>200)).ArtaTagsHtml::CAPTCHA('vis','vis').'<input type="submit" value="'.trans('submit').'"/></div></section><hr style="clear:both;padding:3px;border:0;border-bottom:1px dashed #ccc;"><br/><br/>';
	}
	
	$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__user_visitormessages WHERE `for`='.$owner->id.' ORDER BY `added_time` DESC '.ArtaTagsHtml::LimitResult(null, 'vmsg'));
	$msgs=(array)$db->loadObjectList();
	
	if(count($msgs)>0){
		$db->setQuery('SELECT FOUND_ROWS()');
		$coun=$db->loadResult();
		$xx=ArtaTagsHtml::LimitControls($coun, 'vmsg');
	}else{
		$xx='';
	}
	
	$uby=ArtaLoader::User();
	
	if($owner->id==$cu->id || ArtaUsergroup::getPerm('can_edit_others_visitormessage', 'package', 'user')){
		$ok=true;
	}
	
	$shouldCheckout=array();
	
	ArtaTagsHtml::addHeader('<style>
table.visitormsg:hover img.delMsgHandler{
	visibility:visible !important;
}
table.visitormsg{
	border: 0;
	padding-'.(trans('_LANG_DIRECTION')=='ltr'?'left':'right').':1px;
}
table.visitormsg:hover {
	border-'.(trans('_LANG_DIRECTION')=='ltr'?'left':'right').': 1px solid gray;
	padding-'.(trans('_LANG_DIRECTION')=='ltr'?'left':'right').':0;
}
span.newMsgIndicator {
	color:red;
	font-weight: bold;
}
</style>');
	
	foreach($msgs as $v){
		
		if(isset($ok)){
			$del='<img class="delMsgHandler" style="visibility:hidden;" align="'.(trans('_LANG_DIRECTION')=='ltr'?'right':'left').'" src="'.Imageset('false.png').'" alt="'.trans('DELETE').'" onclick="if(confirm(\''.trans('ARE YOU SURE TO DELETE THIS VISMSG').'\')){document.visitormsgform.task.value=\'deleteMsg\';document.visitormsgform.id.value=\''.$v->id.'\';document.visitormsgform.submit();}"/>';
		}else{
			$del='';
		}
		
		if($v->checkedout==false){
			$shouldCheckout[]=$v->id;
			if($owner->id==$cu->id ){
				$del .= '<span style="float:'.(trans('_LANG_DIRECTION')=='ltr'?'right':'left').'" class="newMsgIndicator"> '.trans('NEW MSG').' </span>';
			}
		}
		
		$_uby=$uby->getUser($v->by);
		@$subcontent .='<article><table class="visitormsg" height="100%" width="100%"><tr><td width="100" class="sentby" rowspan="3">';
		
		/****/
		$subcontent .= '<footer><table class="avatarTable"><tr><td class="avatarTable_image">';
		if($_uby!=null && $_uby->id!=0){
			$subcontent .= '<a href="index.php?pack=user&view=profile&uid='.$_uby->id.'">';
		}
		$subcontent .='<img src="index.php?pack=user&view=avatar&type=jpg&uid='.$_uby->id.'" style="max-width:75px;max-height: 75px;"/>';
		if($_uby!=null && $_uby->id!=0){
			$subcontent .= '</a>';
		}
		$subcontent .= '</td></tr><tr><td class="avatarTable_uname">';
		if($_uby==null || $_uby->id==0){
			$subcontent .= trans('UNKNOWN');
		}else{
			$subcontent .= '<a href="index.php?pack=user&view=profile&uid='.$_uby->id.'">';
			$subcontent .= htmlspecialchars($_uby->username);
			$subcontent .= '</a>';
		}
		
		$subcontent .='</td></tr>';
		$subcontent .= ArtaTagsHtml::AvatarTableDesc($_uby,true);
		$subcontent .= '</table></footer>';
		
		/*****/
		$subcontent .='</td><td height="20">'.$del.'<header><b>'.htmlspecialchars($v->title).'</b></header></td></tr>
		<tr><td style="vertical-align:top;"><p>'.$v->content.'</p></td></tr><tr><td height="20"><p style="color:rgb(150,150,150);"><time datetime="'.ArtaDate::toHTML5($v->added_time).'" pubdate>'.ArtaDate::_($v->added_time).'</time></p></td></tr></table></article>';
	}
	if($msgs == array()) {$subcontent.='<b>'.trans('NO RESULTS FOUND').'</b>';}
	
	if($shouldCheckout!=array() && $owner->id==$cu->id){
		$db->setQuery('UPDATE #__user_visitormessages SET `checkedout`=1 WHERE `id` IN ('.implode(',',$shouldCheckout).')');
		$db->query();
	}
	
	return '
	<fieldset class="profile visitor"><legend class="profileHandler">'.trans('VISITOR MESSAGES').'</legend>
	<form method="post" action="index.php?pack=user" name="visitormsgform">
	'.$subcontent.'
	<input type="hidden" name="pack" value="user"/>
	<input type="hidden" name="task" value="saveMsg"/>
	<input type="hidden" name="id" value="0"/>
	<input type="hidden" name="owner" value="'.$owner->id.'"/>
	</form>
	'.$xx.'
	</fieldset>
	';
}
?>