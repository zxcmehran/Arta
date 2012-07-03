<?php 
if(!defined('ARTA_VALID')){die('No access');}
$controller=new ModulesController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>