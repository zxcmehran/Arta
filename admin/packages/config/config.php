<?php 
if(!defined('ARTA_VALID')){die('No access');}
$controller = new ConfigController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>