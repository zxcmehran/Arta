<?php if(!defined('ARTA_VALID')){die('No access');} ?>
<table>
<tr><td><?php echo trans('ARE YOU SURE TO LOG OUT'); ?></td>
<td><center><form action="<?php echo ArtaURL::make('index.php'); ?>?pack=user"><input name="logout" type="submit" value="<?php echo trans('YES'); ?>"/><input type="hidden" name="pack" value="user"/><input type="hidden" name="task" value="logout"/></form> <br/> <input type="button" onclick="window.history.go(-1);" value="<?php echo trans('NO'); ?>"/></center></td></tr>
</table>
