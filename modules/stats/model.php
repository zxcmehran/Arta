<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 18:44 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class ModuleStatsModel extends ArtaModuleModel{
	function getUsersOnline(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT userid FROM #__sessions WHERE client=\'site\'');
		$r=$db->loadObjectList();
		return $r;
	}	
	
	function getInfo(){
		$r=$this->getUsersOnline();
		$i=0;
		$j=0;
		$onlines=array();
		foreach($r as $k=>$v){
			if($v->userid==0){
				$i++;
			}else{
				$j++;
				$onlines[]=$v->userid;
			}
		}
		$onlines=array_unique($onlines);
		if(count($onlines)){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT username,id FROM #__users WHERE id IN ('.implode(',', $onlines).')');
			$onlines=$db->loadObjectList();
		}
		$this->g=$i;
		$this->u=$j;
		$this->on=$onlines;
		return true;
	}
}


?>
