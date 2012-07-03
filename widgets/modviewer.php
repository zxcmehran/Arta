<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/12/17 16:26 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

$db=Artaloader::DB();
$db->setQuery('SELECT * FROM #__modules WHERE id='.$settings['module_to_view']);
$res= $db->loadObject();
if(ArtaUsergroup::processDenied($res->denied)==false){
	echo '<b>'.trans('YOU ARE NOT AUTHORIZED TO VIEW THIS WIDGET').'</b>';
}else{
	$module=Artaloader::Module();
	echo $module->renderBlocks($res, true);
}

?>