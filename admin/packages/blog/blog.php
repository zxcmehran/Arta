<?php 
if(!defined('ARTA_VALID')){die('No access');}
$controller=$this->getController(getVar('controller', 'default', '', 'string'));
//$controller=new BlogController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>