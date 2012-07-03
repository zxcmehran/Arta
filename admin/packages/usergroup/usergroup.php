<?php 
if(!defined('ARTA_VALID')){die('No access');}
$controller=new UsergroupController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>