<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/18 13:26 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
$u=$this->getCurrentUser();
?>
<form method="post" action="index.php?pack=user" name="edit_form" onsubmit="f=document.edit_form;if(f._password.value.length>0){len=f._password.value.length;f.password.value=Crypt.MD5(f._password.value);f.password_verify.value=Crypt.MD5(f._password_verify.value)+len;}if(f._password_current.value.length>0){f.password_current.value=Crypt.MD5(f._password_current.value);}f._password_current.value='';f._password.value='';f._password_verify.value='';">
<fieldset>
<legend><?php
	echo trans('NAME AND AVATAR');
?></legend>
<table>
<tr>
	<td style="vertical-align:top;" width="90%">
	<table>
	<tr><td>
	<?php
	echo trans('NAME');
?>:</td><td><input name="name" value="<?php
	echo htmlspecialchars($u->name);
?>" maxlength="255"/></td>
	</tr>
	<tr><td></td><td>&nbsp;</td></tr>
	<tr>
	<td></td><td><?php
	echo '<a target="_blank" href="index.php?pack=user&view=profile&uid='.$u->id.'">'.trans('VIEW YOUR PROFILE').'</a>';
?></td>
	</tr>
	</table>
	</td>
	<td width="200" style="vertical-align:top;"><table class="avatarTable"><tr><td class="avatarTable_image"><a title="<?php
	echo trans('change avatar');
?>" href="index.php?pack=user&view=avatar"><img src="index.php?pack=user&view=avatar&type=jpg&big=1&uid=<?php
	echo htmlspecialchars($u->id);
?>"/></a></td></tr><tr><td class="avatarTable_uname"><?php
	echo htmlspecialchars($u->username);
?></td></tr>
<?php
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT title FROM #__usergroups WHERE id='.$db->Quote($u->usergroup));
	$ug=$db->loadObject();
	if($ug!==null){
		echo '<tr><td class="avatarTable_ug">'.trans('USERGROUP').': '.htmlspecialchars($ug->title).'</td></tr>';
	}
?>
</table></td>
</tr>
</table>
</fieldset>
<fieldset>
<legend><?php
	echo trans('EMAIL AND PASSWORD');
?></legend><p>
<?php
if($u->password{0}!=='_'){
	echo trans('EMAIL AND PASSWORD INTRO');
?>
<br/>
<?php echo trans('CURRENT PASSWORD'); ?>: &nbsp;<input name="_password_current" type="password" value=""/>
<?php
}else{
	echo trans('EMAIL AND NOPASSWORD INTRO');
}
?>
</p>

<table>
<tr>
	<td><?php
	echo trans('EMAIL');
?>:</td><td><input name="email" value="<?php
	echo htmlspecialchars($u->email);
?>" maxlength="255"/></td>
</tr>
<tr>
	<td><?php
	echo trans('VERIFY EMAIL');
?>:</td><td><input name="email_verify" value="<?php
	echo '';
?>" maxlength="255"/></td>
</tr>
</table>
<hr/>
<table>
<tr>
	<td><?php
	echo trans('PASSWORD');
?>:</td><td><input name="_password" type="password" value="<?php
	echo '';
?>"/> <?php
	printf(trans('PASSWORD MUST BE AT LEAST _ CHARS'), $this->getSetting('password_min_length', 6))
?></td>
</tr>
<tr>
	<td><?php
	echo trans('VERIFY PASSWORD');
?>:</td><td><input name="_password_verify" type="password" value="<?php
	echo '';
?>"/></td>
</tr>
<?php 
if($this->getSetting('allow_openid_usage', true)==true){

?>
<tr>
	<td colspan="2" align="center"><a href="index.php?pack=user&view=openid"><?php echo trans('MANAGE OPENIDS') ?></a><br/>
	<small><a href="http://openid.net" target="_blank"><?php echo trans('WHAT IS OPENID') ?></a></small></td>
</tr>
<?php } ?>
</table>
</fieldset>
<fieldset>
<legend><?php
	echo trans('YOUR SETTINGS');
?></legend>
<table>
<?php
	$can=ArtaUsergroup::getPerm('can_login_admin_side', 'plugin', 'authorization', 'admin');
	$denied=array('admin_language', 'admin_template', 'admin_imageset');
	$db->setQuery('SELECT * FROM #__userfields WHERE fieldtype=\'setting\'');
	$set=$db->loadObjectList();
	if($set==null){
		echo '<tr><td style="text-align:center;"><b>'.trans('NO SETTING PARAMETERS FOUND').'</b></td></tr>';
	}else{
		$u->settings=unserialize($u->settings);
		$l=ArtaLoader::Language();
		foreach($set as $k=>$s){
		if((in_array($s->var, $denied) && $can==false)==false){
			
		
		$l->addtoNeed($s->extname, $s->extype);
		
?>
	<tr>
		<td><?php
		
		if($l->exists('SETTING_'.strtoupper($s->var).'_DESC')){
			$desc=trans('SETTING_'.strtoupper($s->var).'_DESC');
		}else{
			$desc='';
		}
	echo ArtaTagsHtml::Tooltip(trans('SETTING_'.strtoupper($s->var).'_LABEL'), $desc);
?></td>
		<td><?php
		$val=$s->var;
		$val=isset($u->settings->$val) ?  $u->settings->$val : unserialize($s->default);
	echo ArtaTagsHtml::PreFormItem('settings['.$s->var.']', $val, $s->vartype, '$scope="useredit";'.$s->vartypedata);
?></td>
	</tr>
<?php
		}}
	}
?>
</table>
</fieldset>
<fieldset>
<legend><?php
	echo trans('YOUR MISC');
?></legend>
<table>
<?php
	$db->setQuery('SELECT * FROM #__userfields WHERE fieldtype=\'misc\'');
	$set=$db->loadObjectList();
	if($set==null){
		echo '<tr><td style="text-align:center;"><b>'.trans('NO MISC PARAMETERS FOUND').'</b></td></tr>';
	}else{
		$u->misc=unserialize($u->misc);
		$l=ArtaLoader::Language();
		foreach($set as $k=>$s){
		$l->addtoNeed($s->extname, $s->extype);
?>
	<tr>
		<td><?php
		if($l->exists('MISC_'.strtoupper($s->var).'_DESC')){
			$desc=trans('MISC_'.strtoupper($s->var).'_DESC');
		}else{
			$desc='';
		}
	echo ArtaTagsHtml::Tooltip(trans('MISC_'.strtoupper($s->var).'_LABEL'), $desc);
?></td>
		<td><?php
		$val=$s->var;
		$val=isset($u->misc->$val) ?  $u->misc->$val : unserialize($s->default);
	echo ArtaTagsHtml::PreFormItem('misc['.$s->var.']', $val, $s->vartype, '$scope="useredit";'.$s->vartypedata);
?></td>
	</tr>
<?php
		}
	}
?>
</table>
</fieldset>
<br/>
<input type="submit" value="<?php
	echo trans('SAVE CHANGES');
?>"/>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="password_current" value=""/>
<input type="hidden" name="password" value=""/>
<input type="hidden" name="password_verify" value=""/>
<input type="hidden" name="task" value="edit"/>
<input type="hidden" name="uid" value="<?php
	echo $u->id;
?>"/>
</form>