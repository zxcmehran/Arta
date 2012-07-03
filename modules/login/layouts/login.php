<?php if(!defined('ARTA_VALID')){die('No access');} echo $this->get('prefix');?>
<form action="<?php 
$p=ArtaLoader::Package();
$secure=$p->getSetting('secure_login_over_https', 0, 'user');
if($secure==0){
	echo ('index.php');
}else{
	echo ArtaURL::getURL(array('protocol'=>'https://', 'path'=>'','path_info'=>'', 'query'=>'')).makeURL('index.php');
}
 
?>?pack=user" name="login_module_1" method="post" onsubmit="document.login_module_1.password.value=Crypt.MD5(document.login_module_1.password_box.value);document.login_module_1.password_box.value='';">
<span id="lb_norm">
<p>
<?php echo trans('USERNAME'); ?><br/><input name="username" value="<?php if(isset($_COOKIE['arta_uname'])){echo htmlspecialchars($_COOKIE['arta_uname']);} ?>" class="acceptRet"/></p>
<p>
<?php echo trans('PASSWORD'); ?><br/><input name="password_box" type="password" class="acceptRet"/></p>
<p>
<?php echo trans('REMEMBER ME'); ?> <input type="checkbox" name="remember"/></p>
</span>
<?php
$p=ArtaLoader::Package();
if($p->getSetting('allow_openid_usage', true)==true){
 ?>
<span id="lb_oid" style="display: none;">
<p>
<?php echo trans('OPENID'); ?><br/><input name="openid_box" class="acceptRet" style="background: url(<?php echo ArtaURL::getSiteURL().Imageset('openid.png');?>) no-repeat;padding-left:17px;"/></p>
</span>
<a id="oi_login_hanlde" style="cursor: pointer;" onclick="if($('lb_oid').style.display=='none'){$('lb_oid').show();$('lb_norm').hide();$('oi_login_hanlde').innerHTML='<?php echo JSValue(trans('CANCEL OPENID'), true); ?>';}else{$('lb_oid').hide();$('lb_norm').show();document.login_module_1.openid_box.value='';$('oi_login_hanlde').innerHTML='<?php echo JSValue(trans('LOGIN USING OPENID'), true); ?>';}"> <?php echo trans('LOGIN USING OPENID'); ?></a><br/>
<?php 
}
?>
<br/><input type="submit" value="<?php echo trans('LOGIN') ?>"/>
<br/><a href="<?php echo ('index.php?pack=user&view=remind'); ?>"><?php echo trans('REMIND USER_PASS'); ?></a> 
<br/><a href="<?php echo ('index.php?pack=user&view=register'); ?>"><?php echo trans('REGISTER AT HERE'); ?></a>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="token" value="<?php echo ArtaSession::genToken(); ?>"/>
<input type="hidden" name="task" value="login"/>
<input type="hidden" name="password" value=""/>
<input type="hidden" name="redirect" value="<?php $defurl=(string)$this->get('redirect')!=='' ? $this->get('redirect') : 'index.php?'.ArtaURL::getQuery();$defurl=base64_encode($defurl); echo getVar('redirect', $defurl); ?>"/>
<br/><?php echo $this->get('suffix'); ?>
<?php
if($secure==1 && ArtaURL::getProtocol()=='http://'){
	echo '<input type="hidden" name="unsecure" value="1"/>';
}
?>
</form>
<?php if(isset($_COOKIE['arta_uname'])){
	$u=ArtaLoader::User();
	$user=$u->getUser($_COOKIE['arta_uname'], 'username',false);
	if($user!==null){
	?>
<form action="<?php 
if($secure==0){
	echo ('index.php');
}else{
	echo ArtaURL::getURL(array('protocol'=>'https://', 'path'=>'','path_info'=>'', 'query'=>'')).makeURL('index.php');
}
 ?>" name="login_module_2" id="loginform2" method="post" onsubmit="document.login_module_2.password.value=Crypt.MD5(document.login_module_2.password_box.value);document.login_module_2.password_box.value='';">
	<table class="avatarTable login" align="center">
		<tr>
			<td class="avatarTable_image">
				<img src="index.php?pack=user&view=avatar&type=jpg&uid=<?php echo $user->id;?>"/>
			</td>
		</tr>
		<tr>
			<td class="avatarTable_uname">
				<?php
	echo trans('USERNAME').': <b>'.htmlspecialchars($user->username);
?>
			</b></td>
		</tr>
		<tr>
			<td class="avatarTable_uname">
				<?php
	echo trans('PASSWORD').': ';
?><input name="password_box" size="8" type="password"/>
			</td>
		</tr>
		<tr>
			<td class="avatarTable_uname">
				<input type="submit" value="<?php echo trans('LOGIN'); ?>"/>
				<input type="button" value="&times;" onclick="Cookie.set('arta_uname','');$('loginform2').hide();"/>
			</td>
		</tr>
	</table>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="token" value="<?php echo ArtaSession::genToken(); ?>"/>
<input type="hidden" name="task" value="login"/>
<input type="hidden" name="password" value=""/>
<input type="hidden" name="remember" value="1"/>
<input type="hidden" name="redirect" value="<?php $defurl=(string)$this->get('redirect')!=='' ? $this->get('redirect') : 'index.php?'.ArtaURL::getQuery();$defurl=base64_encode($defurl); echo getVar('redirect', $defurl); ?>"/>
<input type="hidden" name="username" value="<?php echo htmlspecialchars($user->username); ?>"/>
<?php
if($secure==1 && ArtaURL::getProtocol()=='http://'){
	echo '<input type="hidden" name="unsecure" value="1"/>';
}
?>
</form>

<?php
}} ?>
