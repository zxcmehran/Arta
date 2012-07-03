<?php 
if(!defined('ARTA_VALID')){die('No access');}
$t=ArtaLoader::Template();
$t->tmpl='login';
$controller = new LoginController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>