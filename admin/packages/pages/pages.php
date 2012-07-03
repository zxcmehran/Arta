<?php 
if(!defined('ARTA_VALID')){die('No access');}
$controller=new PagesController;
$controller->exec(getVar('task', 'display', '', 'string'));

?>