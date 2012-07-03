<?php
if(!defined('ARTA_VALID')){die('No access');}
class UsergroupController extends ArtaPackageController{
	
	function display(){
		$view=$this->getView(getVar('view', 'grouplist','','string'));
		$view->setLayout(getVar('layout', 'default','','string'));
		$view->display();
	}

	function activate(){
		$db=ArtaLoader::DB();
		$ids=getVar('ids', false,'','array');
		if($ids == false || count($ids) == 0){
			redirect('index.php?pack=usergroup');
		}
		
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
		
		if(ArtaUserGroup::getPerm('can_edit_usergroups_activity', 'package', 'usergroup') == false){
			ArtaError::Show(500 ,trans('YOU CANNOT EDIT USERGROUPS ACTIVITY'), 'index.php?pack=usergroup');
		}
		$uc=ArtaLoader::User();
		foreach($ids as $id){
			if((int)$id==0){
				ArtaError::show(403, trans('YOU CANNOT EDIT THIS OPTION FOR GUEST GROUP'));
			}
			
			$u=$uc->getUser($id, 'id');
			$db->setQuery("UPDATE #__usergroups SET active='1' WHERE id='".$id."'", array('active'));
			$db->query();
		}
		redirect('index.php?pack=usergroup', trans('ACTIVATED SUCC'));
	}

	function deactivate(){
		$db=ArtaLoader::DB();
		$id=getVar('ids', false,'','array');
		if($id == false || count($id) == 0){
			redirect('index.php?pack=usergroup');
		}
		
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
		
		if(ArtaUserGroup::getPerm('can_edit_usergroups_activity', 'package', 'usergroup') == false){
			ArtaError::Show(500 ,trans('YOU CANNOT EDIT USERGROUPS ACTIVITY'), 'index.php?pack=usergroup');
		}
		$id=$id[0];
		if((int)$id==0){
			ArtaError::show(403, trans('YOU CANNOT EDIT THIS OPTION FOR GUEST GROUP'));
		}
		$config=ArtaLoader::Config();
		$db->setQuery("UPDATE #__usergroups SET active='0' WHERE id='".$id."'", array('active'));
		$db->query();
		redirect('index.php?pack=usergroup', trans('DEACTIVATED SUCC'));
	}

	function delete(){
		if(ArtaUserGroup::getPerm('can_addedit_usergroups', 'package', 'usergroup') == false){
			ArtaError::Show(500 ,trans('YOU CANNOT ADDEDITDELETE USERGROUPS'));
		}
		$type=getVar('deltype', false, 'post', 'string');
		if($type==false){
			$ids=array();
			foreach(getVar('ids',array()) as $k=> $v){
				$ids[]='ids['.$k.']='.$v;
			}	
			redirect('index.php?pack=usergroup&view=grouplist&layout=delete&'.implode($ids, '&'));
		}
		$vars=ArtaRequest::getVars('post', 'object');
		if(in_array('0', $vars->ids)){
			ArtaError::show(403, trans('YOU CANNOT EDIT THIS OPTION FOR GUEST GROUP'));
		}
		
		ArtaFile::rmdir_extra(ARTAPATH_BASEDIR.'/tmp/cache');
		ArtaFile::rmdir_extra(ARTAPATH_ADMIN.'/tmp/cache');
		
		$db=ArtaLoader::DB();
		if($type=='delall'){
			$vars->ids=array_map(array('ArtaRequest', 'clean'), $vars->ids, array_fill(0,count($vars->ids), 'int'));
			$vars->ids=array_map(array($db, 'Quote'), $vars->ids);
			$db->setQuery('DELETE FROM #__users WHERE usergroup IN ('.implode(',',$vars->ids).')');
		}elseif($type=='delmove' && isset($vars->grouplist) && ArtaRequest::clean($vars->grouplist, 'int')>0){
			$vars->ids=array_map(array('ArtaRequest', 'clean'), $vars->ids, array_fill(0,count($vars->ids), 'int'));
			$vars->grouplist=ArtaRequest::clean($vars->grouplist, 'int');
			$vars->ids=array_map(array($db, 'Quote'), $vars->ids);
			$db->setQuery('UPDATE #__users SET usergroup='.$db->Quote($vars->grouplist).' WHERE usergroup IN ('.implode(',',$vars->ids).')', array('usergroup'));
		}else{
			redirect('index.php?pack=usergroup&view=grouplist');
		}
		$res=$db->query();
		$db->setQuery('DELETE FROM #__usergroups WHERE id IN ('.implode(',',$vars->ids).')');
		$res2=$db->query();
		$db->setQuery('DELETE FROM #__usergroupperms_value WHERE usergroup IN ('.implode(',',$vars->ids).')');
		if($db->query() && $res==true && $res2==true){
			redirect('index.php?pack=usergroup&view=grouplist', trans('DELETED SUCC'));
		}else{
			redirect('index.php?pack=usergroup&view=grouplist', trans('ERROR IN DB'), 'error');
		}
	}
	
	function save(){
		if(ArtaUserGroup::getPerm('can_addedit_usergroups', 'package', 'usergroup') == false){
			ArtaError::Show(500 ,trans('YOU CANNOT ADDEDITDELETE USERGROUPS'));
		}
		
		$vars=ArtaRequest::getVars('post', 'object');
		foreach($vars->ids as $k=>$v){
			$vars->ids=$vars->ids[$k];
			break;
		}
		
		$db=ArtaLoader::DB();
		
		
		if($vars->ids['name']==null || $vars->ids['title']==null){
			redirect('index.php?pack=usergroup&view=new', trans('FORM ISNT COMPLETE'), 'error');
		}
		
		ArtaFile::rmdir_extra(ARTAPATH_BASEDIR.'/tmp/cache');
		ArtaFile::rmdir_extra(ARTAPATH_ADMIN.'/tmp/cache');
		
		// If we are editing
		if(isset($vars->id)){
			$vars->id=(int)$vars->id;
			$db->setQuery('SELECT * FROM #__usergroups WHERE `id`='.$db->Quote($vars->id));
			$row=$db->loadObject();
			
			if($row == null){
				redirect('index.php?pack=usergroup', trans('INVALID ID SPECIFIED'), 'error');
			}
			if($vars->ids['name']!==$row->name || $vars->ids['title']!==$row->title){
				$db->setQuery('SELECT * FROM #__usergroups WHERE (`name`='.$db->Quote($vars->ids['name']).' OR `title`='.$db->Quote($vars->ids['title']).') AND id!='.$db->Quote($vars->id));
				$exist=$db->loadObjectList();
				if(count($exist) && $exist[0]->name==$vars->ids['name']){
					redirect('index.php?pack=usergroup&view=new&ids[]='.$vars->id, trans('CHOOSE ANOTHER NAME'), 'error');
				}elseif(count($exist) && $exist[0]->title==$vars->ids['title']){
					redirect('index.php?pack=usergroup&view=new&ids[]='.$vars->id, trans('CHOOSE ANOTHER TITLE'), 'error');
				}
			}
			
			$db->setQuery('SELECT *, CONCAT(`name`,\'|\',`extname`,\'|\',`extype`,\'|\',`client`) as `val` FROM #__usergroupperms');
			$perms=ArtaUtility::keyByChild($db->loadObjectList(),'val');
			$l=ArtaLoader::Language();
			$todo=array();
			foreach($perms as $k=>&$v){
				if(isset($vars->ids['perms'][$k])==false){
					redirect('index.php?pack=usergroup&view=new&ids[]='.$vars->id, trans('INVALID VALUE FOR').': '.trans('PERM_'.$v->name.'_LABEL'), 'error');
				}else{
					$value=$vars->ids['perms'][$k];
				}
				if($v->vartype=='bool'){
					$value=@(bool)$value;
				}
				$error=null;
				$l->addtoNeed($v->extname, $v->extype, ARTAPATH_ADMIN);
				$this->runEval($v,$row,$value,$error);
			//	eval($v->check);
				
				$v->value=$value;
				if($error!==null){
					redirect('index.php?pack=usergroup&view=new&ids[]='.$vars->id, $error, 'error');
				}
			}
			if($vars->ids['name']!==$row->name || $vars->ids['title']!==$row->title){
				$db->setQuery('UPDATE #__usergroups SET `name`='.$db->Quote($vars->ids['name']).', `title`='.$db->Quote($vars->ids['title']).' WHERE id='.$db->Quote($vars->id));
				$res=$db->query();
			}else{
				$res=true;
			}
			$values=array();
			$dels=array();
			foreach($perms as $k=>$v){
				$values[]='(\''.$vars->id.'\',\''.$v->id.'\','.$db->Quote(serialize($v->value)).')';
				$dels[]=$db->Quote($v->id);
			}
			if(count($dels)){
				$db->setQuery('DELETE FROM #__usergroupperms_value WHERE usergroupperm IN ('.implode(',',$dels).') AND usergroup='.$db->Quote($vars->id));
				$res2=$db->query();
			}else{
				$res2=true;
			}

			if(count($values) && $res2==true){
				$db->setQuery('INSERT INTO #__usergroupperms_value (usergroup, usergroupperm, `value`) VALUES '.implode(',',$values));
				$res3=$db->query();
			}else{
				$res3=true;
			}
			
			if($res && $res2 && $res3){
				if((int)$vars->id==0){
					ArtaCache::clearData('users','guest');
				}
				redirect('index.php?pack=usergroup', trans('SAVED SUCC'));
			}else{
				redirect('index.php?pack=usergroup&view=new&ids[]='.$vars->id, trans('ERROR IN DB'), 'error');
			}
			
		}else{
			$db->setQuery('SELECT * FROM #__usergroups WHERE `name`='.$db->Quote($vars->ids['name']).' OR `title`='.$db->Quote($vars->ids['title']));
			$exist=$db->loadObjectList();
			if(count($exist) && $exist[0]->name==$vars->ids['name']){
				redirect('index.php?pack=usergroup&view=new', trans('CHOOSE ANOTHER NAME'), 'error');
			}elseif(count($exist) && $exist[0]->title==$vars->ids['title']){
				redirect('index.php?pack=usergroup&view=new', trans('CHOOSE ANOTHER TITLE'), 'error');
			}
			$db->setQuery('SELECT *, CONCAT(`name`,\'|\',`extname`,\'|\',`extype`,\'|\',`client`) as `val` FROM #__usergroupperms');
			$perms=ArtaUtility::keyByChild($db->loadObjectList(),'val');
			$l=ArtaLoader::Language();
			foreach($vars->ids['perms'] as $k=>$v){
				$row=@$perms[$k];
				if($row!=null){
					$value=$v;
					if($row->vartype=='bool'){
						$value=@(bool)$value;
					}
					$error=null;
					$l->addtoNeed($row->extname, $row->extype, ARTAPATH_ADMIN);
					$ugrow=null;
					$this->runEval($row,$ugrow,$value,$error);
					//eval($row->check);
					$vars->ids['perms'][$k]=$value;
					$v=$value;
					if($error!==null){
						redirect('index.php?pack=usergroup&view=new', $error, 'error');
					}
				}else{
					unset($vars->ids['perms'][$k]);
				}
			}
			$db->setQuery('INSERT INTO #__usergroups (`name`,`title`,`active`) VALUES('.$db->Quote($vars->ids['name']).','.$db->Quote($vars->ids['title']).', \'1\')');
			$db->query();
			$db->setQuery('SELECT `id` FROM #__usergroups WHERE `name`='.$db->Quote($vars->ids['name']).' AND `title`='.$db->Quote($vars->ids['title']));
			$id=$db->loadObject();
			$id=$id->id;
			$values=array();
			foreach($perms as $k=>$v){
				$values[]='(\''.$id.'\',\''.$v->id.'\','.$db->Quote(serialize($vars->ids['perms'][$k])).')';
			}
			$db->setQuery('INSERT INTO #__usergroupperms_value (usergroup, usergroupperm, `value`) VALUES '.implode(',',$values));
			
			if($db->query()){
				redirect('index.php?pack=usergroup', trans('SAVED SUCC'));
			}else{
				redirect('index.php?pack=usergroup&view=new', trans('ERROR IN DB'), 'error');
			}
						
		}
	}
	
	private function runEval(&$row,&$ug,&$value,&$error){
		eval($row->check);
	}

}
?>