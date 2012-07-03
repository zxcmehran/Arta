<?php if(!defined('ARTA_VALID')){die('No access');} ?>
<form action="<?php echo ('index.php'); ?>?pack=user" method="post" name="reset_form" onsubmit="document.reset_form.password.value=Crypt.MD5(document.reset_form._password.value);s=new String(document.reset_form._verify_password.value);document.reset_form.verify_password.value=Crypt.MD5(document.reset_form._verify_password.value)+s.length;document.reset_form._verify_password.value='';document.reset_form._password.value='';">
<table>
<tr><td><?php echo trans('NEW PASSWORD'); ?></td><td><input name="_password" type="password"/></td></tr>
<tr><td><?php echo trans('VERIFY NEW PASSWORD'); ?></td><td><input name="_verify_password" type="password"/></td></tr>
</table>
<input type="submit" value="<?php echo trans('SEND') ?>"/>
<input type="hidden" name="token" value="<?php echo ArtaSession::genToken(); ?>"/>
<input type="hidden" name="uid" value="<?php echo $this->get('uid'); ?>"/>
<input type="hidden" name="reset_code" value="<?php echo $this->get('reset_code'); ?>"/>
<input type="hidden" name="task" value="reset"/>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="password" value=""/>
<input type="hidden" name="verify_password" value=""/>
</form>