<?php
if(!defined('ARTA_VALID')){die('No access');}
class UserModelNew{
	
	function getUsers($id){
		if(ArtaUserGroup::getPerm('can_addedit_users', 'package', 'user') == false){
			ArtaError::show(403,trans('YOU CANNOT ADDEDIT USERS'));
		}
		$id=@ArtaFilterinput::clean(array_shift($id), 'int');
		if($id !==false && ($id) > 0){
			$db=ArtaLoader::DB();
			$db->setQuery("SELECT * FROM #__users WHERE id=".$db->Quote($id));
			$r=$db->loadAssoc();
			if($r==null){
				ArtaError::show(500, trans('USER NOT FOUND'), 'index.php?pack=user&view=userlist');
			}
			
			if($r['lastvisit_date']=='1970-01-01 00:00:00' || $r['lastvisit_date']=='0000-00-00 00:00:00'){
				$r['lastvisit_date']='';
			}
			
			$res=array();
			$res[$r['id']]=$r;
			return $res;
		}else{
			$user=ArtaLoader::User();
			$user=$user->getGuest();
			return array(array('name'=>null, 'username'=>null, 'email'=>null, 'usergroup'=>0, 'ban'=>null, 'ban_reason'=>null, 'register_date'=>ArtaDate::toMySQL(time()), 'lastvisit_date'=>null, 'settings'=>$user->settings, 'misc'=>$user->misc, 'activation'=>'something'));
		}
	}
	
	function getFields($type){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT * FROM #__userfields WHERE fieldtype=".$db->Quote($type));
		return $db->loadObjectList();

	}

}
?>