<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 2 $
 * @date		2009/2/27 19:46 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
$controller=$this->getController(getVar('controller', 'default', '', 'string'));
$controller->exec(getVar('task', 'display', '', 'string'));

?>