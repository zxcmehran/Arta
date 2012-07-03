<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/12/17 16:26 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

//It's dependent to linkviewer module

$linkgroup=$settings['linkgroup_to_list'];
$m=ArtaLoader::Module();
$mod=new stdClass;
$mod->title='';
$mod->content='MENU:'.$linkgroup;
$mod->module='linkviewer';
$mod->showtitle=0;
echo $m->renderBlocks($mod, true);


?>