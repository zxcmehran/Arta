<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 18:44 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

/* for using with bloglast widget */
if(isset($params['blogid_to_show'])==false){
	$blogid_to_show=$this->getSetting('blogid_to_show', false);
}else{
	$blogid_to_show=$params['blogid_to_show'];
}

$H=$this->getHelper();
$m=$this->getModel();
$out=$H->addtoUList($m->getLast($blogid_to_show));
echo $out;
?>