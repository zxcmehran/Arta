<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/18 13:9 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
 
class UserViewProfile extends ArtaPackageView{
	function display(){
		$cu=$this->getCurrentUser();
		
		$uid=getVar('uid', 0, 'default', 'int');
		$ord=getVar('profileList', false, '', 'array');
		if(getVar('do')=='saveOrder' && $ord!==false && ((int)$uid>0) && ((int)$uid===(int)$cu->id || ArtaUsergroup::getPerm('can_edit_others_profilepage', 'package', 'user'))){
			$this->saveOrder();
			return true;
		}elseif(getVar('do')=='saveOrder' && ((int)$uid>0)){
			ArtaError::show(403);
		}
		
		if(getVar('do')=='saveMsg' && ((int)$uid>0) && ((int)$uid===(int)$cu->id || ArtaUsergroup::getPerm('can_edit_others_profile_msg', 'package', 'user'))){
			$this->saveMsg();
			return true;
		}elseif(getVar('do')=='saveMsg' && ((int)$uid>0)){
			ArtaError::show(403);
		}
		
		$m=$this->getModel();
		$u=$m->getUser();
		if(@$u!==null){
			$this->setTitle(sprintf(trans('_ PROFILE'), htmlspecialchars($u->username)));
			$this->addPath(sprintf(trans('_ PROFILE'), $u->username), 'index.php?pack=user&view=profile&uid='.$u->id);
			$this->assign('user', $u);
			$this->assign('ug', $m->getUsergroupTitle($u->usergroup));
			$this->assign('sid', $m->getSession($u->id));
			$this->assign('m', $m);
			$this->render();
		}else{
			ArtaError::show('404', 'No such user found.', 'index.php?pack=user&view=list');
		}
		
	}
	
	function saveOrder(){
		$u=Artaloader::User();
		if(ArtaSession::checkToken()==false){
			ArtaError::show(400);
		}
		$cu=$u->getUser(getVar('uid', 0, '', 'int'));
		if(@$cu->id==0){
			ArtaError::show();
		}
		$settings=unserialize($cu->settings);
		$settings->profile_page_order=serialize(getVar('profileList', array(0,1), '', 'array'));
		$db=Artaloader::DB();
		$db->setQuery('UPDATE #__users SET settings='.$db->Quote(serialize($settings)).' WHERE id='.$db->Quote($cu->id), array('settings'));
		

		if($db->query()==false){
			ArtaError::show(500);
		}
	}
	
	function saveMsg(){
		$u=Artaloader::User();
		if(ArtaSession::checkToken()==false){
			ArtaError::show(400);
		}
		$cu=$u->getUser(getVar('uid', 0, '', 'int'));
		if(@$cu->id==0){
			ArtaError::show();
		}
		$settings=unserialize($cu->settings);
		$settings->profile_msg=getVar('profile_msg', '', '', 'string');
		$db=Artaloader::DB();
		$db->setQuery('UPDATE #__users SET settings='.$db->Quote(serialize($settings)).' WHERE id='.$db->Quote($cu->id), array('settings'));
		
		if($db->query()==false){
			ArtaError::show(500);
		}
	}
	
}

?>