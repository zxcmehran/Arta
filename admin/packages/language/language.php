<?php 
if(!defined('ARTA_VALID')){die('No access');}
$controller=new LanguageController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>