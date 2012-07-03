<?php
if(!defined('ARTA_VALID')){die('No access');}
class Quicklink_managerModelLinks{
	
	function getLinkz(){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT SQL_CALC_FOUND_ROWS * FROM #__quicklink ".ArtaTagsHtml::SortResult('order').' '.ArtaTagsHtml::LimitResult());
		$r=(array)$db->loadObjectList();
		$db->setQuery('SELECT FOUND_ROWS()');
		$this->count=$db->loadResult();
		return $r;
	}

}
?>