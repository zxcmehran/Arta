<?php
if(!defined('ARTA_VALID')){die('No access');}
class ToolsModelRepair extends ArtaPackageModel{
	
	function __construct(){
		$db=ArtaLoader::DB();
		$db->setQuery('SHOW TABLES');
		$tables_data=$db->loadResultArray();
	/*	$tables_data=array();
		foreach($tables as $t){
			$db->setQuery('SHOW TABLE STATUS LIKE '.$db->Quote($t));
			$tables_data[$t]=$db->loadObject();
		}*/

		$tables=array();
		foreach($tables_data as $tbl){
			$tables[]=$db->CQuote($tbl);
		}
		$db->setQuery('REPAIR TABLE '.implode(',',$tables));
		$this->data=$db->loadObjectList();
	}
	
	function getResult(){
		return $this->data;
	}

}
?>