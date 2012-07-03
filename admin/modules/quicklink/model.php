<?php
if(!defined('ARTA_VALID')){die('No access');}
class ModuleQuicklinkModel{

	function getItems(){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT * FROM #__quicklink ORDER BY `order`");
		return $db->loadObjectList();
	}


}
?>