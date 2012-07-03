<?php if(!defined('ARTA_VALID')){die('No access');} 
class ModuleLeftmenuModel extends ArtaModuleModel{
	
	function getData(){
		if(!isset($GLOBALS['CACHE']['admin_menu.items'])){
			if(ArtaCache::isUsable('admin_menu', 'items')){
				$data=ArtaCache::getData('admin_menu', 'items');
			}else{
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__admin_menu ORDER BY `parent`,`order`');
				$r=$db->loadObjectList();
				$data=ArtaUtility::keyByChild($r, 'parent', true);
				ArtaCache::putData('admin_menu', 'items', $data);
			}
			$GLOBALS['CACHE']['admin_menu.items']=$data;
		}
		$this->r=$GLOBALS['CACHE']['admin_menu.items'];
	}

}
?>