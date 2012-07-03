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
<?php echo trans('USERNAME'); ?>: <input size="7" style="font-size:9px;" name="username" value="<?php if(isset($_COOKIE['arta_uname'])){echo htmlspecialchars($_COOKIE['arta_uname']);} ?>" class="acceptRet"/>
<?php echo trans('PASSWORD'); ?>: <input size="7" style="font-size:9px;" name="password_box" type="password" class="acceptRet"/>
</span>
<?php
$p=ArtaLoader::Package();
if($p->getSetting('allow_openid_usage', true)==true){
 ?>
<span id="lb_oid" style="display: none;"><?php echo trans('OPENID'); ?>: <input size="25" style="font-size:9px;" name="openid_box" class="acceptRet"/></span>
<?php } ?>
<input type="submit" value="<?php echo trans('LOGIN') ?>"/>

<a href="<?php echo ('index.php?pack=user&view=remind'); ?>"><?php echo trans('SMALL_REMIND USER_PASS'); ?></a> | 
<a href="<?php echo ('index.php?pack=user&view=register'); ?>"><?php echo trans('SMALL_REGISTER AT HERE'); ?></a><?php
if($p->getSetting('allow_openid_usage', true)==true){
 ?> | 
<img src="<?php echo Imageset('openid.png') ?>" style="cursor: pointer; border: 1px solid gray;background: #eeeeee; " onclick="if($('lb_oid').style.display=='none'){$('lb_oid').show();$('lb_norm').hide();}else{$('lb_oid').hide();$('lb_norm').show();document.login_module_1.openid_box.value='';}" alt="OpenID" title="<?php echo trans('LOGIN USING OPENID'); ?>"/>
<?php } ?>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="token" value="<?php echo ArtaSession::genToken(); ?>"/>
<input type="hidden" name="task" value="login"/>
<input type="hidden" name="password" value=""/>
<input type="hidden" name="redirect" value="<?php $defurl=(string)$this->get('redirect')!=='' ? $this->get('redirect') : 'index.php?'.ArtaURL::getQuery();$defurl=base64_encode($defurl); echo getVar('redirect', $defurl); ?>"/>
<?php
if($secure==1 && ArtaURL::getProtocol()=='http://'){
	echo '<input type="hidden" name="unsecure" value="1"/>';
}
?>
</form>
<?php echo $this->get('suffix'); ?>
