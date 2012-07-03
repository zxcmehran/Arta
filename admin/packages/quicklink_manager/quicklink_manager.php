<?php 
if(!defined('ARTA_VALID')){die('No access');}
$controller=new Quicklink_managerController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>