<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/2/13 17:33 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
if(ArtaUsergroup::getPerm('can_open_filemanager', 'package', 'filemanager') == true){
	$c=new FilemanagerController();
	$c->exec(getVar('task', 'display', '', 'string'));
}else{
	if(getVar('editor', 0)){
		$x='&editor=1';
	}else{
		$x='';
	}
	ArtaError::show(401, false, 'index.php?pack=user&view=login&redirect='.base64_encode('index.php?pack=filemanager'.$x), 1);
}

?>