<?php if(!defined('ARTA_VALID')){die('No access');}?>
<?php ArtaTagsHtml::addScript('packages/user/scripts/register_validate.js'); ?>
<?php
	ArtaTagsHtml::addtoTmpl('
<script>
var NOT_EQUALS="'.JSValue(trans('NOT_EQUALS')).'";
var PLEASE_CORRECT="'.JSValue(trans('PLEASE CORRECT')).'";
var true_image="'.JSValue(makeURL(Imageset('true.png'))).'";
var false_image="'.JSValue(makeURL(Imageset('false.png'))).'";
var loading_image="'.JSValue(makeURL(Imageset('loading_small.gif'))).'";
</script>
	', 'head');
?>

<form action="<?php echo ArtaURL::make('index.php'); ?>?pack=user" method="post" onsubmit="return makePasswords()" name="user_register_form_1">
<fieldset><legend><?php echo ArtaTagsHtml::Tooltip(trans('REQUIRED FIELDS'), trans('REQUIRED INTRO')); ?></legend>
<table>
	<tr><td><?php echo trans('NAME'); ?></td><td><input name="name" id="name" onchange="makeEffect(this.id, 'true');" maxlength="255"/></td><td id="name_stat"> </td></tr>
	<tr><td><?php echo trans('USERNAME'); ?></td><td><input name="username" id="username" onchange="checkform(this.id, this.value);" maxlength="255"/></td><td id="username_stat"> </td></tr>
	<tr><td><?php echo trans('EMAIL'); ?></td><td><input name="email" id="email"  onchange="checkform(this.id, this.value);" maxlength="255"/></td><td id="email_stat"> </td></tr>
	<tr><td><?php echo trans('VERIFY EMAIL'); ?></td><td><input name="email_verify" id="email_verify" onchange="checkEquality('email', this.id);" maxlength="255"/></td><td id="email_verify_stat"> </td></tr>
	<tr><td id="pass_td"><?php echo trans('PASSWORD'); ?></td><td><input name="_password" type="password" id="password" onchange="checkform(this.id, this.value);"/></td><td id="password_stat"> </td></tr>
	<tr><td><?php echo trans('VERIFY PASSWORD'); ?></td><td><input name="_password_verify" type="password" id="password_verify" onchange="checkEquality('password', this.id);"/> </td><td id="password_verify_stat"></td></tr>
	</table>
	</fieldset>
	<?php
		$misc = $this->get('misc');
		if($misc !== null && count($misc) > 0){
	?>
	<fieldset style="display: none;"><legend><?php echo ArtaTagsHtml::Tooltip(trans('ADDITIONAL FIELDS'), trans('ADDITIONAL INTRO')); ?></legend>
	<table>
	<?php
		foreach($misc as $k=>$v){
			$lang=ArtaLoader::Language();
			$lang->addtoNeed($v->extname, $v->extype, ARTAPATH_SITE);
			if($lang->exists('MISC_'.strtoupper($v->var).'_DESC')){
				$desc=trans('SETTING_'.strtoupper($v->var).'_DESC');
			}else{
				$desc='';
			}
			echo '<tr><td>'.ArtaTagsHtml::Tooltip(trans(strtoupper('misc_'.$v->var.'_label')),$desc).'</td><td>'.
				ArtaTagsHtml::PreFormItem('misc['.$v->var.']', unserialize($v->default), $v->vartype, $v->vartypedata).'</td></tr>';
		}
	?>
	</table>
</fieldset>
<?php }?>
<fieldset><legend><?php echo trans('HUMAN VERIFICATION'); ?></legend>
<?php echo ArtaTagsHtml::CAPTCHA('captcha', 'user_register');?>
</fieldset>
<p><input type="checkbox" name="rules" value="1"/> <?php echo ArtaTagsHtml::Window(trans('I READ RULES'), 'index.php?pack=user&view=register&layout=rules&tmpl=package', trans('RULES')); ?></p>

<br/>
<input type="submit" value="<?php echo trans('submit'); ?>"/>
<input type="hidden" name="redirect" value="<?php echo	isset($_SERVER['HTTP_REFERER']) ? base64_encode($_SERVER['HTTP_REFERER']) : '';?>"/>
<input type="hidden" name="token" value="<?php echo $this->get('token');?>"/>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="task" value="register"/>
<input type="hidden" name="password" id="m_password" value=""/>
<input type="hidden" name="password_verify" id="m_password_verify" value=""/>
</form>