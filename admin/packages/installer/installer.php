<?php 
if(!defined('ARTA_VALID')){die('No access');}
if(ArtaUsergroup::getPerm('can_access_installer', 'package', 'installer')==false){
	ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
}
$controller=new InstallerController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>