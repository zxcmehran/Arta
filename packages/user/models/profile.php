<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/21 12:29 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class UserModelProfile extends ArtaPackageModel{
	
	function getUser(){
		$uid=getVar('uid', 0, 'default', 'int');
		$u=ArtaLoader::User();
		$u=$this->u=$u->getUser($uid);

		if(@$u->id==0){
			ArtaError::show(404, trans('USER NOT FOUND'), 'index.php?pack=user&view=list');
		}
		return $this->u;
	}
	
	function getUsergroupTitle($ug){
		$ug = ArtaUsergroup::getUsergroup($ug);
		return @$ug->title;
	}
	
	function getSession($uid){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT position FROM #__sessions WHERE client=\'site\' AND userid='.$db->Quote($uid));
		$sessdat=$db->loadObject();
		return $sessdat;
	}
	
	function getMiscRows($rows){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__userfields WHERE `var` IN('.implode(',', $rows).') AND fieldtype=\'misc\'');
		return $db->loadObjectList();
	}
}

?>