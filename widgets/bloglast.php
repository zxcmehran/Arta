<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/12/17 16:26 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

//It's dependent to bloglast module

$blogid_to_show=$settings['blogid_to_show'];
$m=ArtaLoader::Module();
$mod=new stdClass;
$mod->title='';
$mod->content='';
$mod->module='bloglast';
$mod->showtitle=0;
echo $m->renderBlocks($mod, true, array('blogid_to_show'=>$blogid_to_show));


?>