<?php if(!defined('ARTA_VALID')){die('No access');}?>
<form action="<?php echo ('index.php'); ?>?pack=user" method="post" name="logout_form">
<?php
	$user=$this->getCurrentUser();
	?>
				<a href="index.php?pack=user&view=edit"><?php echo htmlspecialchars($this->get('username') ? htmlspecialchars($user->username) : htmlspecialchars($user->name)) ?></a> | 
<input type="submit" value="<?php echo trans('LOGOUT'); ?>" onclick="if(confirm('<?php echo trans('ARE YOU SURE TO LOG OUT'); ?>')){document.logout_form.submit();return true;}else{return false;}"/>

<input type="hidden" name="token" value="<?php echo ArtaSession::genToken(); ?>"/>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="task" value="logout"/>
<input type="hidden" name="redirect" value="<?php echo base64_encode(getVar('redirect', 'index.php?'.ArtaURL::getQuery())); ?>"/>
</form>

