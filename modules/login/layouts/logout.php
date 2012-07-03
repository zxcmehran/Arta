<?php if(!defined('ARTA_VALID')){die('No access');}?>
<form action="<?php echo ('index.php'); ?>?pack=user" method="post" name="logout_form">
<table>
<tr><td><?php echo trans('HELLO'); $user=ArtaLoader::User();$user=$user->getCurrentUser(); ?> <?php echo $this->get('username') ? htmlspecialchars($user->username) : htmlspecialchars($user->name); ?>!<br/><?php echo $this->get('greeting'); ?><br/>
<input type="submit" value="<?php echo trans('LOGOUT'); ?>" onclick="if(confirm('<?php echo trans('ARE YOU SURE TO LOG OUT'); ?>')){document.logout_form.submit();return true;}else{return false;}"/>
</td></tr>
</table>
<input type="hidden" name="token" value="<?php echo ArtaSession::genToken(); ?>"/>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="task" value="logout"/>
<input type="hidden" name="redirect" value="<?php echo base64_encode(getVar('redirect', 'index.php?'.ArtaURL::getQuery())); ?>"/>
</form>
<br/>

<?php
	$user=$this->getCurrentUser();
	?>
	<table class="avatarTable login" align="center">
		<tr>
			<td class="avatarTable_image">
				<a href="index.php?pack=user&view=edit"><img src="index.php?pack=user&view=avatar&type=jpg&uid=<?php echo $user->id;?>"/></a>
			</td>
		</tr>
		<tr>
			<td class="avatarTable_uname"><a href="index.php?pack=user&view=edit"><b>
				<?php
	echo htmlspecialchars($user->username);
?>
			</b></a></td>
		</tr>
	</table>