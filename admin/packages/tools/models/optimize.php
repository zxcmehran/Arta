<?php
if(!defined('ARTA_VALID')){die('No access');}
class ToolsModelOptimize extends ArtaPackageModel{
	
	function __construct(){
		$db=ArtaLoader::DB();
		$db->setQuery('SHOW TABLE STATUS');
		$tables_data=$db->loadObjectList('Name');
	/*	$tables_data=array();
		foreach($tables as $t){
			$db->setQuery('SHOW TABLE STATUS LIKE '.$db->Quote($t));
			$tables_data[$t]=$db->loadObject();
		}*/
		$this->data=$tables_data;
		$tables=array();
		foreach($tables_data as $tbl){
			$tables[]=$db->CQuote($tbl->Name);
		}
		$db->setQuery('OPTIMIZE TABLE '.implode(',',$tables));
		$db->query();
	}
	
	function getResult(){
		return $this->data;
	}

}
?>