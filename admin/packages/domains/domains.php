<?php 
if(!defined('ARTA_VALID')){die('No access');}
$controller=new DomainsController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>