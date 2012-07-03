<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/21 12:29 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class PagesModelNew extends ArtaPackageModel{
	
	function getWidgets($pid){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__pages_widgets WHERE pageid='.$db->Quote($pid));
		$r=(array)$db->loadObjectList();
		foreach($r as &$v){
			if($v->widget>0){
				$db->setQuery('SELECT title FROM #__pages_widgets_resource WHERE id='.$db->Quote($v->widget));
				$wid=$db->loadResult();
				if($wid!==null){
					$v->title.=' ('.$wid.')';
				}
			}
		}
		return $r;
	}
	
}

?>