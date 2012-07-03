<?php if(!defined('ARTA_VALID')){die('No access');}?>
<form action="<?php echo ('index.php'); ?>?pack=user" method="post" name="logout_form">
<table>
<tr><td><?php
	$user=$this->getCurrentUser();
	?>
	<table class="avatarTable login" align="center" style="min-width:0px;">
		<tr>
			<td class="avatarTable_image">
				<a href="index.php?pack=user&view=edit"><img style="height:25px;width:25px;max-height:25px;max-width:25px;" src="index.php?pack=user&view=avatar&type=jpg&uid=<?php echo $user->id;?>"/></a>
			</td>
		</tr>
	</table></td><td align="center"><?php echo trans('SMALL_HELLO'); $user=ArtaLoader::User();$user=$user->getCurrentUser(); ?> <?php echo $this->get('username') ? htmlspecialchars($user->username) : htmlspecialchars($user->name); ?>! <br/><?php echo $this->get('greeting'); ?> 
</td><td>
<input type="submit" value="<?php echo trans('LOGOUT'); ?>" onclick="if(confirm('<?php echo trans('ARE YOU SURE TO LOG OUT'); ?>')){document.logout_form.submit();return true;}else{return false;}"/>
</td></tr>
</table>
<input type="hidden" name="token" value="<?php echo ArtaSession::genToken(); ?>"/>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="task" value="logout"/>
<input type="hidden" name="redirect" value="<?php echo base64_encode(getVar('redirect', 'index.php?'.ArtaURL::getQuery())); ?>"/>
</form>

