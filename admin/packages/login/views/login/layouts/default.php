<?php if(!defined('ARTA_VALID')){die('No access');} ?>
<form action="<?php 
$p=ArtaLoader::Package();
$secure=$p->getSetting('secure_login_over_https', 0, 'user');
if($secure==0){
	echo ('index.php');
}else{
	echo ArtaURL::getURL(array('protocol'=>'https://', 'path'=>'','path_info'=>'', 'query'=>'')).makeURL('index.php');
}
 
?>" method="post" name="admin_login_form" onsubmit="document.admin_login_form.password.value=Crypt.MD5(document.admin_login_form.f_password.value);document.admin_login_form.f_password.value='';">
<fieldset style="width: 60%;">
<legend><?php echo trans('LOGIN TO ADMIN CONSOLE'); ?></legend>
<noscript style="color:red; font-weight:bold;"><?php echo trans('NO SCRIPT ERROR');?></noscript>
<table>
	<tr>
		<td align="center" colspan="2"><img src="<?php echo ArtaVersion::getLogo() ?>"/><h1 style="text-shadow: 2px 2px 12px #01307A;">
		<?php
	echo trans('ARTA');
?>
		</h1>
		</td>
	</tr>
	<tr><td>
		<table>
			<tr>
				<td><?php echo trans('USERNAME');?>:</td>
				<td><input name="username" value="<?php if(isset($_COOKIE['arta_uname'])){echo $_COOKIE['arta_uname'];} ?>" class="acceptRet"/></td>
			</tr>
			<tr>
				<td><?php echo trans('PASSWORD'); ?>:</td>
				<td><input name="f_password" type="password" class="acceptRet"/></td>
			</tr>
                        <tr>
				<td><?php echo trans('LANGUAGE'); ?>:</td>
                                <td><?php 
                                $db = ArtaLoader::DB();
                                $db->setQuery('SELECT `name`,`title` FROM #__languages WHERE `client`='.$db->Quote('admin'));
                                $langs = $db->loadObjectList();
                                ?>
                                    <select name="f_language">
                                        <option value=""><?php echo trans('USER DEFAULT'); ?></option>
                                        <?php
                                            foreach($langs as $lang){
                                                echo '<option value="'.htmlspecialchars($lang->name).'">'.htmlspecialchars($lang->title).'</option>';
                                            }
                                        ?>
                                    </select>
                                </td>
			</tr>
			<tr>
				<td><?php echo trans('REMEMBER ME'); ?></td>
				<td><input type="checkbox" name="remember"/></td>
			</tr>
		</table>
	</td>
	<td width="180">
		<?php
	echo trans('ADMIN CONSOLE LOGIN DESC');
?>
	</td>
	</tr>
</table>
<input type="submit" value="<?php echo trans('LOGIN') ?>"/>
<input type="hidden" name="token" value="<?php echo ArtaSession::genToken(); ?>"/>
<input type="hidden" name="pack" value="login"/>
<input type="hidden" name="password" value=""/>
<input type="hidden" name="task" value="login"/>
<input type="hidden" name="redirect" value="<?php echo getVar('redirect','','','string'); ?>"/>
<?php
if($secure==1 && ArtaURL::getProtocol()=='http://'){
	echo '<input type="hidden" name="unsecure" value="1"/>';
}
?>
</fieldset>
</form>
<br/>