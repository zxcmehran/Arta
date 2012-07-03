<?php
if(!defined('ARTA_VALID')){die('No access');}
class UserModelUserlist{
	
	function getUsers(){
		$db=ArtaLoader::DB();
		$order=getVar('order_by','id','','string');
		if(getVar('order_by')=='usergroup'){
			ArtaRequest::addVar('order_by', 'usergrouptitle');
		}

		$db->setQuery("SELECT SQL_CALC_FOUND_ROWS *, (SELECT title FROM #__usergroups as ug WHERE ug.id=u.usergroup) AS usergrouptitle FROM #__users AS u ".ArtaTagsHtml::FilterResult(null, array('lastvisit_date'=> '>', 'register_date'=> '>'), 'u').' '.ArtaTagsHtml::SortResult('id').' '.ArtaTagsHtml::LimitResult());
		$r=(array)$db->loadObjectList();
		
		$db->setQuery('SELECT FOUND_ROWS()');
		$this->count=$db->loadResult();
		
		foreach($r as &$v){
		//	$v->usergroup=$this->getUsergroup($v->usergroup);
			if($v->lastvisit_date=='1970-01-01 00:00:00' || $v->lastvisit_date=='0000-00-00 00:00:00'){
				$v->lastvisit_date=trans('never');
			}
		}

		
		return $r;
	}

	function getUsersCount(){
		return $this->count;
	}

	function getUsergroup($id){
		return ArtaUsergroup::getUsergroup($id);
	}

	function getOnline($id){
		if(!isset($GLOBALS['CACHE']['session.active'])){
			$db=ArtaLoader::DB();
			$db->setQuery("SELECT userid,position,ip FROM #__sessions ORDER BY `client` DESC");
			$GLOBALS['CACHE']['session.active']=$db->loadObjectList('userid');
		}
		
		if(isset($GLOBALS['CACHE']['session.active'][$id])){
			return 'true';
		}else{
			return 'false';
		}
	}
	
	function getUserInfo($id){
		$db=ArtaLoader::DB();
		$id=$db->Quote($id);
		$db->setQuery("SELECT * FROM #__sessions WHERE `userid`=$id ORDER BY `client` DESC, `time` DESC");
		return $db->loadObject();
	}

	function getPosition($id){
		if(!isset($GLOBALS['CACHE']['session.active'])){
			$db=ArtaLoader::DB();
			$db->setQuery("SELECT userid,position,ip FROM #__sessions ORDER BY `client` DESC");
			$GLOBALS['CACHE']['session.active']=$db->loadObjectList('userid');
		}
		
		$d=@$GLOBALS['CACHE']['session.active'][$id];
		if($d==null){
			return null;
		}else{
			if(($p=explode(',', $d->position))==true){
				return trans('POSITION').' : '. implode(' / ', $p).'<br/>'.'IP : '. ($d->ip);
			}else{
				return null;
			}
		}
	}

}
?>