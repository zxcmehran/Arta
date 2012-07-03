<?php 
if(!defined('ARTA_VALID')){die('No access');}
$controller=new LinksController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>