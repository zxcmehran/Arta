<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/21 12:29 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class UserModelOpenid extends ArtaPackageModel{
	
	function getOpenIDs(){
		$u=$this->getCurrentUser();
		if(@$u->id==0){
			ArtaError::show(403);
		}
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__openid_map WHERE `userid`='.$u->id);
		$d = $db->loadObjectList();
		return $d;
	}
	
}

?>