<?php 
/**
 * Arta Usergroup Manager and Loader
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaUser
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// detect arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaUserGroup Class
 * Usergroup Loader and manager
 * @static
 */
 
class ArtaUserGroup{

	/**
	 * Gets an usergroup from DB.
	 * @static
	 * @param	string	$id	Usergroup id or name or ...
	 * @param	string	$by	Select user by id,name or etc.
	 * @return	mixed
	 */
	static function getUsergroup($id=false, $by='id') {
		$db = ArtaLoader::DB();
		if($id===false){
			return false;
		}
		if($by=='id' && isset($GLOBALS['CACHE']['users.usergroups'][$id])){
			return $GLOBALS['CACHE']['users.usergroups'][$id];
		}
		$q="SELECT * FROM #__usergroups WHERE ".$db->CQuote($by)."=".$db->Quote($id);
		$db->setQuery($q);
		$r= $db->loadObject();
		if($r!==null){
			$GLOBALS['CACHE']['users.usergroups'][$r->id]=$r;
		}
		return $r;
			
	}

	/**
	 * Gets all usergroups.
	 * @static
	 * @return	array
	 */
	static function getItems(){
		if(!isset($GLOBALS['CACHE']['users._usergroups'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__usergroups');
			$GLOBALS['CACHE']['users.usergroups']=(array)$db->loadObjectList('id');
			$GLOBALS['CACHE']['users._usergroups']=true;// Indicates that are ALL ugs loaded in this var or not. 
		}
		return $GLOBALS['CACHE']['users.usergroups'];
	}

	/**
	 * Gets Usergroup Permission
	 * @static
	 * @param	string	$name	Permisiion  name
	 * @param	string	$extype	Extension type
	 * @param	string	$extname	Extension name
	 * @param	string	$client	Extesion Client
	 * @param	string	$usergroup	Usergroup ID
	 * @return	mixed
	 */
	static function getPerm($name, $extype, $extname, $client=CLIENT, $usergroup=false){
		$c=ArtaLoader::Config();
		if($c->cache==true){
			return self::getPerm1($name, $extype, $extname, $client, $usergroup);
		}else{
			return self::getPerm0($name, $extype, $extname, $client, $usergroup);
		}
	}
	
	
	static private function getPerm0($name, $extype, $extname, $client=CLIENT, $usergroup=false){
		
		if($usergroup === false){
			$user = ArtaLoader::User();
			$usergroup= $user->getUserGroup();
		}
		if(isset($GLOBALS['_UPERM_CACHE'][$name.'|'.$extype.'|'.$extname.'|'.$client.'|'.$usergroup])){
			$res=$GLOBALS['_UPERM_CACHE'][$name.'|'.$extype.'|'.$extname.'|'.$client.'|'.$usergroup];
		}else{
			$db = ArtaLoader::DB();
			$q=sprintf("SELECT * FROM #__usergroupperms as list JOIN #__usergroupperms_value as value WHERE list.id=value.usergroupperm AND value.usergroup=%s AND list.name=%s AND list.extype=%s AND list.extname=%s AND list.client=%s ", $db->Quote($usergroup), $db->Quote($name), $db->Quote($extype), $db->Quote($extname), $db->Quote($client));
			$db->setQuery($q);
			$res=$db->loadObject();
			
			if($res==null){
				$res = self::autocompleteUGValues($name, $extype, $extname, $client, $usergroup);
			}
			$GLOBALS['_UPERM_CACHE'][$name.'|'.$extype.'|'.$extname.'|'.$client.'|'.$usergroup]=$res;
		}
		return isset($res->value) ? unserialize($res->value) : false;
		
	}
	
	static private function getPerm1($name, $extype, $extname, $client=CLIENT, $usergroup=false){
		
		if($usergroup === false){
			$user = ArtaLoader::User();
			$usergroup= $user->getUserGroup();
		}
		
		if(!isset($GLOBALS['CACHE']['users.usergroupperms'][$client])){
			$GLOBALS['CACHE']['users.usergroupperms'][$client]=array();
		}
		
		if(ArtaCache::isUsable('users_usergroups',$client.'_'.$usergroup.'_perms')){
			$GLOBALS['CACHE']['users.usergroupperms'][$client][$usergroup]=ArtaCache::getData('users_usergroups',$client.'_'.$usergroup.'_perms');
		}else{
			$db = ArtaLoader::DB();
			$q=sprintf("SELECT *,CONCAT(list.extype,'|',list.extname,'|',list.name) as `key` FROM #__usergroupperms as list JOIN #__usergroupperms_value as value WHERE list.id=value.usergroupperm AND list.client=%s AND value.usergroup=%s", $db->Quote($client), $db->Quote($usergroup));
			$db->setQuery($q);
			$res=$db->loadObjectList('key');
			ArtaCache::putData('users_usergroups',$client.'_'.$usergroup.'_perms', $res);
			$GLOBALS['CACHE']['users.usergroupperms'][$client][$usergroup]=$res;
		}
		if(isset($GLOBALS['CACHE']['users.usergroupperms'][$client][$usergroup]
					[$extype.'|'.$extname.'|'.$name])){
			$res=$GLOBALS['CACHE']['users.usergroupperms'][$client][$usergroup]
					[$extype.'|'.$extname.'|'.$name];
		}else{
			$res = self::autocompleteUGValues($name, $extype, $extname, $client, $usergroup);
		}
		return isset($res->value) ? unserialize($res->value) : false;
		
	}
	
	/**
	 * Automatically fills #__usergroupperms_value according to default values in #__usergroupperms
	 * @static
	 */
	static function autocompleteUGValues($name, $extype, $extname, $client, $usergroup){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__usergroupperms WHERE name='.$db->Quote($name).' AND extype='.$db->Quote($extype).' AND extname='.$db->Quote($extname).' AND client='.$db->Quote($client));
		$r=$db->loadObject();
		if($r!==null){
			$def=@unserialize($r->default);
			if(is_array($def) && isset($def['*'])){
				$db->setQuery('SELECT usergroup FROM #__usergroupperms_value WHERE usergroupperm='.$db->Quote($r->id));
				$added=$db->loadAssocList();
				$added=ArtaUtility::keyByChild($added, 'usergroup');
				$ugs=self::getItems();
				$q=array();
				foreach($ugs as $v){
					if(!isset($added[$v->id])){
						$val=isset($def[$v->id])? $def[$v->id] : $def['*'];
						$q[]='('.$db->Quote($r->id).','.$db->Quote($v->id).','.$db->Quote($val).')';
						if($v->id==$usergroup){$res=new stdClass; $res->value=$val;}
					}
				}
				if(count($q)){
					ArtaCache::clearData('users_usergroups',$client.'_'.$usergroup.'_perms');
					$db->setQuery('INSERT INTO #__usergroupperms_value (usergroupperm, usergroup, value) VALUES '.implode(',',$q));
					$db->query();
				}
				if(@$res==null){
					$res=new stdClass; $res->value=@$def['*'];
				}
			}else{
				$res=null;
			}
		}else{
			$res=null;
		}
		return $res;
	}
	
	
	/**
	 * Processes 'denied' keys to find out given usergroup is allowed or not.
	 * 'denied' keys are like this:
	 * 		1,2,6,4		it means that usergroups with ids 1,2,6 or 4 are blocked. (or 'please block 1,2,6,4 ugs.')
	 * 		-1,2,6,4	it means that any usergroups without ids 1,2,6 or 4 are blocked. (or 'please block all but 1,2,6,4 ugs.')
	 * @static
	 * @param	string	$denied	'denied' key
	 * @param	int		$ug	Usergroup ID. Null means current ug.
	 * @return	bool
	 */
	static function processDenied($denied, $ug=null){
		if($ug==null){
			$u=ArtaLoader::User();
			$u=$u->getCurrentUser();
			$ug=$u->usergroup;
		}
		if(strlen($denied)){
			if($denied{0}=='-'){
				$rev=true;
				$denied=substr($denied,1);
			}else{
				$rev=false;
			}
			$d=explode(',', $denied);
			if(!is_array($d) && strlen($denied)){
				$d=array($denied);
			}elseif(!is_array($d) && !strlen($denied)){
				return ($rev==false);
			}
			if($rev){
				if(in_array($ug, $d)){
					return true;
				}
				return false;
			}else{
				if(in_array($ug, $d)){
					return false;
				}
				return true;
			}
		}else{
			return true;
		}
	}
	
	/**
	 * Processes Access masks to identify allowed Usergroups
	 * @static
	 * @param	array	$accmask	Access mask
	 * @return	string
	 */
	static function processAccessMask($accmask){
		$ugs=array();
		foreach($accmask as $mask){
			$mask=explode(',',$mask);
			foreach($mask as $val){
				if(strlen($val) && $val{0}=='-'){
					if(isset($ugs[(string)substr($val,1)])){
						unset($ugs[(string)substr($val,1)]);
					}
				}elseif(strlen($val)){
					$ugs[(string)$val]=true;
				}
			}
		}
		return implode(',',array_keys($ugs));
		
	}		
}

?>