<?php if(!defined('ARTA_VALID')){die('No access');}
$u=$this->get('users');
$m=$this->getModel();
?>
<form method="post" name="adminform" action="index.php" enctype="multipart/form-data">
<?php 
foreach($u as $k=>$v){
?>
<fieldset><legend><?php echo trans('NEW/EDIT_USER'); ?></legend>
<table class="admintable">
<tbody>
<tr>
	<td class="label"><?php echo trans('NAME'); ?></td>
	<td class="value"><input name="ids[<?php echo $k; ?>][name]" value="<?php echo htmlspecialchars($v['name']); ?>" maxlength="255"/> </td>
	<td class="label"><?php echo trans('USERNAME'); ?></td>
	<td class="value"><input id="uname" name="ids[<?php echo $k; ?>][username]" value="<?php echo htmlspecialchars($v['username']); ?>" maxlength="255"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('email'); ?></td>
	<td class="value"><input name="ids[<?php echo $k; ?>][email]" value="<?php echo htmlspecialchars($v['email']); ?>" maxlength="255"/></td>
	<td class="label"><?php echo trans('usergroup');?></td>
	<td class="value">
		<?php echo ArtaTagsHtml::PreFormItem("ids[".$k."][usergroup]", $v['usergroup'], 'usergroups'); ?>
	</td>
</tr>
<tr>
	<td class="label"><?php echo trans('password'); ?></td>
	<td class="value"><input name="ids[<?php echo $k; ?>][password]" value="" type="password" id="base" maxlength="255"/> <?php 
	if(@$v['password']{0}=='_'){echo ArtaTagsHtml::Tooltip('<img src="'.Imageset('info.png').'" alt="I"/>', trans('USER HAS NO PASS'));} ?></td>
	<td class="label"><?php echo trans('verify_password'); ?></td>
	<td class="value"><input name="ids[<?php echo $k; ?>][verify_password]" value="" type="password" id="verify" maxlength="255"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('IS ACTIVE'); ?></td>
	<td class="value"><?php echo ArtaTagsHtml::PreFormItem("ids[".$k."][activation]", $v['activation']=='0', 'bool'); ?></td>
	<td class="label" rowspan="2"><?php echo trans('ban_reason'); ?></td>
	<td class="value" rowspan="2"><textarea name="ids[<?php echo $k; ?>][ban_reason]"><?php echo htmlspecialchars($v['ban_reason']); ?></textarea></td>
<tr>
	<td class="label"><?php echo trans('ban'); ?></td>
	<td class="value"><?php echo ArtaTagsHtml::PreFormItem("ids[".$k."][ban]", $v['ban'], 'bool'); ?></td>
</tr>
<tr>
	<td class="label"><?php echo trans('register_date'); ?></td>
	<td class="value"><?php echo ArtaTagsHtml::PreFormItem("ids[".$k."][register_date]", ArtaDate::_($v['register_date'], 'jscal'), 'calendar'); ?></td>
	<td class="label"><?php echo trans('lastvisit_date'); ?></td>
	<td class="value"><?php echo ArtaTagsHtml::PreFormItem("ids[".$k."][lastvisit_date]", $v['lastvisit_date']=='' ? '' : ArtaDate::_($v['lastvisit_date'], 'jscal'), 'calendar'); ?></td>
</tr>
<tr>
	<td class="label"><?php echo trans('user settings'); ?></td>
	<td class="value" colspan="3"><table class="admintable"><?php
$lang=ArtaLoader::Language();
		$path=ARTAPATH_ADMIN;
		$v['settings']=unserialize($v['settings']);
		$v['misc']=unserialize($v['misc']);
foreach($this->get('settings') as $k2=>$v2){
	$lang->addtoNeed($v2->extname, $v2->extype, $path);
	echo '<tr><td class="label">';
	
	if($lang->exists('SETTING_'.strtoupper($v2->var).'_DESC')){
		$desc=trans('SETTING_'.strtoupper($v2->var).'_DESC');
	}else{
		$desc='';
	}

	echo ArtaTagsHtml::Tooltip(trans('SETTING_'.strtoupper($v2->var).'_LABEL'),$desc);
	echo '</td><td class="value">';
	$var=$v2->var;
	$val=@isset($v['settings']->$var) ?  $v['settings']->$var : unserialize($v2->default);
	echo ArtaTagsHtml::PreFormItem("ids[".$k."][settings][".$var."]", @$val, $v2->vartype, '$scope="adminedit";'.$v2->vartypedata);
	echo '</td></tr>';
} ?>
</table>
</td>
</tr>
<tr>
	<td class="label"><?php echo trans('user misc'); ?></td>
	<td class="value" colspan="3"><table class="admintable"><?php
foreach($this->get('misc') as $k2=>$v2){
	$lang->addtoNeed($v2->extname, $v2->extype, $path);
	echo '<tr><td class="label">';
	
	if($lang->exists('MISC_'.strtoupper($v2->var).'_DESC')){
		$desc=trans('MISC_'.strtoupper($v2->var).'_DESC');
	}else{
		$desc='';
	}

	echo ArtaTagsHtml::Tooltip(trans('MISC_'.strtoupper($v2->var).'_LABEL'),$desc);
	echo '</td><td class="value">';
	$var=$v2->var;
	$val=@isset($v['misc']->$var) ?  $v['misc']->$var : unserialize($v2->default);
	echo ArtaTagsHtml::PreFormItem("ids[".$k."][misc][".$var."]", @$v['misc']->$var, $v2->vartype, '$scope="adminedit";'.$v2->vartypedata);
	echo '</td></tr>';
} ?>
</table>
</td>
</tr>
<?php
	if((string)$v['username']!==''){
?>
<tr>
	<td class="label"><?php echo trans('avatar'); ?></td>
	<td class="value" colspan="3">
	<table>
	<tr>
	<td width="">
	
	<?php
	echo ArtaTagsHtml::ModalWindow('<img src="'.ArtaURL::getSiteURL().'index.php?pack=user&view=avatar&type=jpg&uid='.$v['id'].'"/>', ArtaURL::getSiteURL().'index.php?pack=user&view=avatar&type=jpg&big=1&uid='.$v['id'],trans('avatar'), true);
?>
	</td><td nowrap="nowrap"><input type="radio" name="<?php echo "ids[".$k."]";?>[av_type]" value="none" id="av_none" onclick="setIn('none')" checked="checked"/><label for="av_none"><?php echo trans('av_none'); ?></label><br/>
<input type="radio" name="<?php echo "ids[".$k."]";?>[av_type]" value="delete" id="av_del" onclick="setIn('delete')"/><label for="av_del"><?php echo trans('av_del'); ?></label><br/>
<input type="radio" name="<?php echo "ids[".$k."]";?>[av_type]" value="upload" id="av_up" onclick="setIn('upload')"/><label for="av_up"><?php echo trans('av_up'); ?></label><br/>
<input type="radio" name="<?php echo "ids[".$k."]";?>[av_type]" value="link" id="av_l" onclick="setIn('link')"/><label for="av_l"><?php echo trans('av_l'); ?></label><br/>
<input type="radio" name="<?php echo "ids[".$k."]";?>[av_type]" value="gravatar" id="av_gr" onclick="setIn('gravatar')"/><label for="av_gr"><?php echo trans('av_gr'); ?></label><br/>
</td><td>
<div id="uploadContainer"></div>
</td>
</tr>
</table>
<?php
	$t=ArtaLoader::Template();
	$t->addtoTmpl('<script>
	function setIn(type){
		if(type==\'delete\'){
			new Effect.Highlight($("uploadContainer"));
			$("uploadContainer").innerHTML="'.trans('WILL BE DELETED').'";
		}
		if(type==\'upload\'){
			new Effect.Highlight($("uploadContainer"));
			$("uploadContainer").innerHTML="<p>'.trans('WILL BE UPLOADED').'</p><input type=\"file\" name=\"uploadFile\">";
		}
		if(type==\'link\'){
			new Effect.Highlight($("uploadContainer"));
			$("uploadContainer").innerHTML="'.trans('WILL BE LINKED').'<br><input type=\"text\" name=\"ids['.$k.'][linkFile]\" size=\"25\" maxlength=\"255\">";
		}
		if(type==\'gravatar\'){
			new Effect.Highlight($("uploadContainer"));
			$("uploadContainer").innerHTML="'.trans('WILL BE USED').'";
		}
		if(type==\'none\'){
			$("uploadContainer").innerHTML="";
		}
	}
                </script>','afterbody');
?>
</td>
</tr>
<?php
	}else{
		echo '<tr><td class="label">'.trans('avatar').'</td><td class="value" colspan="3">'.trans('AVATAR SETTINGS WILL BE AVAILABLE AFTER SAVING');
	}
?>
</tbody>
</table>
</fieldset>

<?php } 
if(getVar('moderation',false)==true){
	echo '<input type="hidden" name="moderation" value="true">';
}
?>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="task" value="save"/>
</form>