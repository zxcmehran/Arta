<?php 
if(!defined('ARTA_VALID')){die('No access');}
$controller = new CphomeController;
$controller->exec(getVar('task', 'display', '', 'string'));
?>