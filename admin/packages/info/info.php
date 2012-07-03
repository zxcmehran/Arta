<?php 
if(!defined('ARTA_VALID')){die('No access');}
if(ArtaUserGroup::getPerm('can_open_info', 'package', 'info')==false){
	ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
}
$controller = new InfoController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>