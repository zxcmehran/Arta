<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 3 $
 * @date		2009/3/18 13:26 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
?>
<fieldset>
<legend><?php echo trans('Your OPENID ACCOUNTS');?></legend>

<?php
$o = $this->get('d'); 
if(@count($o)==0){
	echo '<center><b>'.trans('NO OPENID ACCOUNT FOUND').'</b></center>';
}else{
	foreach($o as $v){
		echo '<a onclick="if(confirm(\''.JSValue(trans('ARE YOU SURE TO DELETE THIS OPENID ACCOUNT'), true).'\')){return true;}else{return false;}" href="index.php?pack=user&task=del_oi&id='.$v->id.'&token='.ArtaSession::genToken().'" title="'.trans('DELETE').'"><img src="'.Imageset('false.png').'" alt="'.trans('DELETE').'"/></a>&nbsp;';
		echo htmlspecialchars($v->server_url).'<br/>';
	}
}
?>

<br />
<br />
<p>
<?php echo trans('OPENID DESC');?>
</p>
<form action="index.php?pack=user" method="post">
	<?php echo trans('ADD ANOTHER ACCOUNT');?>: <input style="background: url(<?php echo ArtaURL::getSiteURL().Imageset('openid.png');?>) no-repeat;padding-left:17px;" name="user_openid" maxlength="255" size="40"/>
	<input type="submit" value="<?php echo trans('ADD');?>"/>
	<input type="hidden" name="pack" value="user"/>
	<input type="hidden" name="task" value="save_oi"/>
	<input type="hidden" name="token" value="<?php echo ArtaSession::genToken();?>"/>
</form>

</fieldset>
