<?php 
#########################################
# File : user.php 
# Description : User Functions for Arta
#########################################
if(!defined('ARTA_VALID')){die('No access');}
$controller = new UserController;
$controller->exec(getVar('task', 'display'));

?>