<?php
if(!defined('ARTA_VALID')){die('No access');}
class PagesController extends ArtaPackageController{
	
	function display(){
		$view=$this->getView(getVar('view', 'pages', '', 'string'));
		$view->display();
	}
	
	
	function save(){
		if(ArtaUsergroup::getPerm('can_addedit_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT PAGES'));
		}
		// GET DATA FROM $_POST
		$vars=ArtaRequest::getVars('post');

		// Check data and some definitions
		$vars=ArtaUtility::array_extend($vars, array('title'=>'', 'sef_alias'=>'', 'desc'=>'', 'tags'=>'', 'height'=>"600", 'width'=>"0", 'enabled'=>'0', 'page_template'=>'-','mods'=>array(), 'deny_type'=>0, 'denied'=>array(), 'denied_type'=>0, 'is_dynamic'=>0, 'content'=>''));
		
		$vars=ArtaFilterinput::clean($vars,array('title'=>'string','sef_alias'=>'string','desc'=>'string','height'=>'int','width'=>'int', 'enabled'=>'bool', 'page_template'=>'string', 'mods'=>'array', 'deny_type'=>'bool', 'denied'=>'array', 'denied_type'=>'bool', 'is_dynamic'=>'bool', 'content'=>'safe-html'));
		
		$vars=ArtaFilterinput::trim($vars);
		$vars=ArtaFilterinput::array_limit($vars,array('title'=>255,'sef_alias'=>255,'tags'=>255));
		
		if($vars['pid']){
			$vars['pid']=ArtaFilterinput::clean($vars['pid'],'int');
			if($vars['pid']>0){
				$id=$vars['pid'];
				$q='index.php?pack=pages&view=new&pid='.$id;
			}
		}
		
		if($vars['sef_alias']==''){
			$vars['sef_alias']=ArtaFilteroutput::stringURLSafe($vars['title']);
		}else{
			$vars['sef_alias']=ArtaFilteroutput::stringURLSafe($vars['sef_alias']);
		}
		
		
		if(!isset($q)){
			$q='index.php?pack=pages&view=new';
		}
		
		if(strlen(trim($vars['title']))==0){
			redirect($q, trans('INVALID TITLE'),'warning');
		}
		if($vars['width']=='0'){
			$vars['width']='auto';
		}else{
			$vars['width'].='px';
		}
		$params=serialize(array('height'=>$vars['height'].'px', 'width'=>$vars['width'], 'template'=>$vars['page_template']));
		
		$vars['mods']=implode(',',$vars['mods']);
		if($vars['deny_type']==true){
			$vars['mods']='-'.$vars['mods'];
		}
		
		$vars['denied']=implode(',',$vars['denied']);
		if($vars['denied_type']==true){
			$vars['denied']='-'.$vars['denied'];
		}
		
		$u=$this->getCurrentUser();
		$db=ArtaLoader::DB();
		
		// Start DB Updating
		
		if(@$id){
			$db->setQuery('SELECT * FROM #__pages WHERE id='.$id);
			$row=$db->loadObject();
			if(@$row->id!=$id){
				ArtaError::show(404, trans('PAGE NOT FOUND'),$q);
			}
			
			if($row->added_by!==$u->id && 
			 ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
			 	ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
			 }
			
			if(ArtaUsergroup::getPerm('can_change_pages_activity', 'package', 'pages')==false && $vars['enabled']!=$row->enabled){
				$vars['enabled']=$row->enabled;
				$deact=$row->enabled;
			}
		
			if($vars['is_dynamic']==true){
				$vars['content']='';
			}else{
				$db->setQuery('DELETE FROM #__pages_widgets WHERE pageid='.$row->id);
				$db->query();
			}
			
			$db->setQuery('UPDATE `#__pages` SET `title`='.$db->Quote($vars['title']).
			', `sef_alias`='.$db->Quote($vars['sef_alias']).
			', `desc`='.$db->Quote($vars['desc']).
			', `tags`='.$db->Quote($vars['tags']).
			', `is_dynamic`='.$db->Quote($vars['is_dynamic']).
			', `content`='.$db->Quote($vars['content']).
			', `mods`='.$db->Quote($vars['mods']).
			', `enabled`='.$db->Quote((int)$vars['enabled']).
			', `denied`='.$db->Quote($vars['denied']).
			', `params`='.$db->Quote($params).' WHERE `id`='.$id
			);
		}else{
			if(ArtaUsergroup::getPerm('can_change_pages_activity', 'package', 'pages')==false){
				$vars['enabled']=0;
				$deact=0;
			}
            
			if($vars['is_dynamic']==true){
				$vars['content']='';
			}
			$db->setQuery('INSERT INTO `#__pages` VALUES (NULL,'.$db->Quote($vars['title']).
			','.$db->Quote($vars['sef_alias']).
			','.$db->Quote($vars['desc']).
			','.$db->Quote($vars['tags']).
			','.$db->Quote($vars['is_dynamic']).
			','.$db->Quote($vars['content']).
			','.$db->Quote($vars['mods']).
			','.$db->Quote((int)$vars['enabled']).
			','.$db->Quote($vars['denied']).
			','.$db->Quote($u->id).
			','.$db->Quote($params).')'
			);
		}
		
		$r=$db->query();
		if($r==true){
			if(isset($deact)){
				ArtaApplication::enqueueMessage(sprintf(trans('SAVED BUT ACTIVATION SET TO _ BECAUSE PERMS'), (int)$deact));
			}
			redirect('index.php?pack=pages', trans('SAVED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'));
		}		
	}

	function delete(){
		if(ArtaUsergroup::getPerm('can_delete_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT DELETE PAGES'));
		}
		$id=getVar('id',0, 'post','int');
		if($id==false){ArtaError::show(404,'index.php?pack=pages');}
		$db=ArtaLoader::DB();
		
		
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($id));
		$row=$db->loadObject();
		if(@$row->id!=$id){
			ArtaError::show(404, trans('PAGE NOT FOUND'), 'index.php?pack=pages');
		}
		$u=$this->getCurrentUser();
		if($row->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
		}
		
		$db->setQuery('DELETE FROM #__pages WHERE id='.$db->Quote($id));
		$r=$db->query();
		if($r && $row->is_dynamic==true){
			$db->setQuery('DELETE FROM #__pages_widgets WHERE pageid='.$db->Quote($id));
			$r=$db->query();
		}
		if($r){
			redirect('index.php?pack=pages', trans('DELETED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'));
		}
	}
	
	function activate(){
		if(ArtaUsergroup::getPerm('can_change_pages_activity', 'package', 'pages')==false){
			ArtaError::show(403,trans('YOU CANNOT CHANGE PUBLISHED PARAMETER'));
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		$v=getVar('id', 0,'get','int');		
		$db=ArtaLoader::DB();
		
		$u=$this->getCurrentUser();
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($id));
		$row=$db->loadObject();
		if(@$row->id!=$id){
			ArtaError::show(404, trans('PAGE NOT FOUND'));
		}
		
		if($row->added_by!==$u->id && 
		 ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
		 	ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
		 }
		
		$db->setQuery('UPDATE #__pages SET enabled=1 WHERE id='.$db->Quote($v), array('enabled'));
		if($db->Query()){
			redirect('index.php?pack=blog', trans('ACTIVATED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog');
		}	
	}
	
	function deactivate(){
		if(ArtaUsergroup::getPerm('can_change_pages_activity', 'package', 'pages')==false){
			ArtaError::show(403,trans('YOU CANNOT CHANGE PUBLISHED PARAMETER'));
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		$v=getVar('id', 0,'get','int');		
		$db=ArtaLoader::DB();
		
		$u=$this->getCurrentUser();
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($id));
		$row=$db->loadObject();
		if(@$row->id!=$id){
			ArtaError::show(404, trans('PAGE NOT FOUND'));
		}
		
		if($row->added_by!==$u->id && 
		 ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
		 	ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
		 }
		
		
		$db->setQuery('UPDATE #__pages SET enabled=0 WHERE id='.$db->Quote($v), array('enabled'));
		if($db->Query()){
			redirect('index.php?pack=blog', trans('DEACTIVATED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog');
		}	
	}

}
?>