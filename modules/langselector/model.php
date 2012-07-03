<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 19:5 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class ModuleLangselectorModel extends ArtaModuleModel{

	function getLanguages(){
		if(ArtaCache::isUsable('langselector', 'langnames')==false){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT name,title FROM #__languages WHERE client=\'site\'');
			$langs=$db->loadObjectList('name');
			ArtaCache::putData('langselector', 'langnames', $langs);
		}else{
			$langs=ArtaCache::getData('langselector', 'langnames');
		}
		$l=ArtaLoader::Language();
		$g=$l->getUserLang();
		if(isset($langs[$g])){
			$langs[$g]->default=true;
		}
		return $langs;
	}
}

?>