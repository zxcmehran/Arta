<?php
if(!defined('ARTA_VALID')){die('No access');}
class ConfigModelDefaults extends ArtaPackageModel{
	
	function getMiscFields(){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT * FROM #__userfields WHERE fieldtype='misc'");
		$data = $db->loadObjectList();
		$data=@count($data) ? $data : array();
		return $data;
	}

	function getSettingFields(){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT * FROM #__userfields WHERE fieldtype='setting'");
		$data = $db->loadObjectList();
		$data=@count($data) ? $data : array();
		return $data;
	}

}
?>