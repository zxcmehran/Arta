<?php
if(!defined('ARTA_VALID')){die('No access');}
class LinksController extends ArtaPackageController{
	
	function display(){
		$view=$this->getView(getVar('view', 'list', '', 'string'));
		ArtaAdminTabs::addTab(trans('LINKS MANAGEMENT'),'index.php?pack=links');
		ArtaAdminTabs::addTab(trans('LINK GROUPS MANAGEMENT'),'index.php?pack=links&view=grouplist');
		$view->display();
	}
	
	function reorder(){
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		if(ArtaUsergroup::getPerm('can_edit_links_order', 'package', 'links')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT LINKS ORDER'));
		}
		$vars=ArtaRequest::getVars();
		if(!isset($vars['pos'])){
			ArtaError::show(500);
		}
		$vars['pos']=(int)$vars['pos'];
		if($vars['pos']<0){
			ArtaError::show(500);
		}
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__links WHERE id='.$db->Quote(getVar('id', 0, '', 'int')));
		$mod=$db->loadObject();
		if($mod==null){
			ArtaError::show();
		}
		$db->setQuery('SELECT * FROM #__links WHERE `group`='.$db->Quote($mod->group).' ORDER BY `order`');
		$mods=$db->loadObjectList();
		foreach($mods as $k=>$v){
			if((int)$v->order!==$k){
				$incorrect=true;
				break;
			}
		}
		if(isset($incorrect)){
			foreach($mods as $k=>$v){
				$db->setQuery('UPDATE #__links SET `order`='.$db->Quote($k).' WHERE id='.$db->Quote($v->id), array('order'));
				$db->query();
			}
		}
		if($vars['pos']>$mod->order){
			$x=$mod->order+1;
			$y=$vars['pos'];
			$ph='`order`=`order`-1';
		}else{
			$y=$mod->order-1;
			$x=$vars['pos'];
			$ph='`order`=`order`+1';
		}
		$db->setQuery('UPDATE #__links SET '.$ph.' WHERE `group`='.$db->Quote($mod->group).' AND `order` BETWEEN '.$db->Quote($x).' AND '.$db->Quote($y), array('order'));
		$db->query();
		$db->setQuery('UPDATE #__links SET `order`='.$db->Quote($vars['pos']).' WHERE id='.$db->Quote($mod->id), array('order'));
		
		if($db->query()){
			redirect('index.php?pack=links', trans('MOVED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'),'index.php?pack=links');
		}
	}
	
	function activate(){
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		if(ArtaUsergroup::getPerm('can_edit_links_activity', 'package', 'links')==false){
			ArtaError::show(403, trans('YOU CANNOT CHANGE LINKS ACTIVITY'));
		}
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__links SET enabled=1 WHERE id='.$db->Quote(getVar('pid', 0, '', 'int')), array('enabled'));
		if(!$db->query()){
			ArtaError::show(500, trans('ERROR IN DB'));
		}
	}
	
	function deactivate(){
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		if(ArtaUsergroup::getPerm('can_edit_links_activity', 'package', 'links')==false){
			ArtaError::show(403, trans('YOU CANNOT CHANGE LINKS ACTIVITY'));
		}
		$db=ArtaLoader::DB();
		$home=ArtaLinks::getDefault();
		if(getVar('pid', 0, '', 'int')==$home->id){
			ArtaError::show(400, trans('DEFAULT LINK CANNOT BE DEACTIVATED'));
		}
		$db->setQuery('UPDATE #__links SET enabled=0 WHERE id='.$db->Quote(getVar('pid', 0, '', 'int')), array('enabled'));
		if(!$db->query()){
			ArtaError::show(500, trans('ERROR IN DB'));
		}
	}
	
	function saveM(){
		if(ArtaUsergroup::getPerm('can_addedit_links', 'package', 'links')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT LINKS'));
		}
		$vz=ArtaRequest::getVars('post');
		$db=ArtaLoader::DB();
		if(!@$vz['denied']){
			$vz['denied']=array();
		}
		$vz['denied']=(array)$vz['denied'];
		$vz['pid']=$vz['pid'];
		$vz['denied_type']=(int)$vz['denied_type'];
		
		if(!@$vz['pid']){
			ArtaError::show(500);
		}
		$vz['denied']=implode(',',$vz['denied']);
		$vz['denied']= @$vz['denied_type']==1 ? '-'.$vz['denied'] : $vz['denied'];
		$db->setQuery('UPDATE #__links SET denied='.$db->Quote($vz['denied']).' WHERE id='.$db->Quote($vz['pid']), array('denied'));
		if($db->query()){
			redirect('index.php?pack=links&view=perm_editor&tmpl=package&done=1&pid='.$vz['pid'], trans('PERM EDITED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'),'index.php?pack=links&view=perm_editor&tmpl=package&pid='.$vz['pid']);
		}
	}
	
	function save(){
		if(ArtaUsergroup::getPerm('can_addedit_links', 'package', 'links')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT LINKS'));
		}
		// Populating data
		$db=ArtaLoader::DB();
		$v=ArtaRequest::getVars('post');
		
		if(@$v['id']){$id='&id='.ArtaFilterinput::clean($v['id'], 'int');}else{
			$id='';
		}
		
		if(!ArtaUtility::keysExists(
				array('title',
				'linktype',
				'link',
				'group',
				'enabled',
				'denied_type',
				'newwin')
			,$v)){
				
				redirect('index.php?pack=links&view=edit'.$id,trans('FORM ISNT COMPLETE'), 'warning');
		}
		
		$v=ArtaFilterinput::trim($v);
		$v=ArtaFilterinput::array_limit($v, array('title'=>255, 'link'=>255));
		
		$v=ArtaUtility::array_extend($v, array('denied'=>array(), 'id'=>0));
		
		$v=ArtaFilterinput::clean($v, array('title'=>'string',
		 'linktype'=>'string',
		  'link'=>'string',
		  'group'=>'int',
		  'enabled'=>'bool',
		  'denied'=>'array',
		  'denied_type'=>'bool',
		  'newwin'=>'bool',
		  'id'=>'int'));
		
		// Filtering data
		$v['linktype']=strtolower($v['linktype']);
		foreach($v['denied'] as $k=>$d){
			$v['denied'][$k]=ArtaFilterinput::clean($d, 'int');
		}
		
		// some definitions
		$Denied=($v['denied_type']==true ? '-' : '').implode(',',$v['denied']);
		$id=$v['id'];
		if($id>0){
			//redirect
			$red='index.php?pack=links&view=edit&id='.$id;
		}else{
			$red='index.php?pack=links&view=edit';
		}
		
		// Checking data
		if($v['title']==''){
			redirect($red, trans('INVALID TITLE'), 'warning');
		}
		
		if($v['linktype']!=='inner' && $v['linktype']!=='outer'){
			redirect($red, trans('INVALID LINKTYPE'), 'warning');
		}
		
		if(strlen($v['link'])==0){
			redirect($red, trans('INVALID LINK'), 'warning');
		}else{
			while($v['linktype']=='inner' && substr($v['link'],0,10)=='index.php?'){
				$v['link']=substr($v['link'], 10);
			}
			$v['link']=$v['linktype']=='inner' ? 'index.php?'.$v['link'] : $v['link'];
		}
		
		if(strlen($v['group'])<=0){
			redirect($red, trans('INVALID GROUP'));
		}
		
		
		
				
		//Separate edit and new tasks
		if($id>0){
			$db->setQuery('SELECT * FROM #__links WHERE id='.$db->Quote($id));
			$indb=$db->loadObject();
			if($indb==null){
				redirect('index.php?pack=links&view=edit', trans('NO SUCH LINK FOUND TO EDIT'), 'error');
			}
			
			if($indb->enabled!=$v['enabled'] &&  ArtaUsergroup::getPerm('can_edit_links_activity', 'package', 'links')==false){
				$v['enabled']=$indb->enabled;
				if($v['enabled']==0){
					$force=true;
				}
			}
			
			if($indb->type=='default' AND $v['linktype']!=='inner'){
				redirect($red, trans('DEFAULT LINK MUST BE INNER'), 'error');
			}
			
			if($indb->type=='default'&&ArtaUsergroup::getPerm('can_set_default_link', 'package', 'links')==false){
				ArtaError::show(403, trans('YOU CANNOT EDIT DEFAULT LINKS'));
			}
			
			if($indb->type=='default'){
				$v['linktype']='default';
				if($v['enabled']==0 && !isset($force)){
					ArtaApplication::enqueueMessage(trans('DEFAULT LINKS MUST BE ENABLED SO ENABLED'), 'warning');
				}elseif($v['enabled']==0 && isset($force)){
					ArtaError::show(403, trans('YOU CANNOT ENABLE LINKS BUT DEFAULT LINK MUST BE SO OPERATION CANCELLED'));
				}
				$v['enabled']=1;
			}
			
			$db->setQuery('UPDATE #__links SET '.
			'`title`='.$db->Quote($v['title']).
			',`link`='.$db->Quote($v['link']).
			',`type`='.$db->Quote($v['linktype']).
			',`group`='.$db->Quote($v['group']).
			',`enabled`='.$db->Quote((int)$v['enabled']).
			',`denied`='.$db->Quote($Denied).
			',`newwin`='.$db->Quote((int)$v['newwin']).
			'WHERE id='.$db->Quote($id)
			);
		}else{
			if(ArtaUsergroup::getPerm('can_edit_links_activity', 'package', 'links')==false){
				$v['enabled']=0;
			}
			$db->setQuery('SELECT MAX(`order`) FROM #__links WHERE `group`='.$db->Quote($v['group']));
			$max=(int)$db->loadResult()+1;
			
			$db->setQuery('INSERT INTO #__links VALUES(null'.
			','.$db->Quote($v['title']).
			','.$db->Quote($v['link']).
			','.$db->Quote($v['linktype']).
			','.$db->Quote($v['group']).
			','.$db->Quote((int)$v['enabled']).
			','.$db->Quote($Denied).
			','.$db->Quote((int)$v['newwin']).
			','.$db->Quote($max).')'
			);
		}
		if($db->query()){
			if(isset($force)){
				ArtaApplication::enqueueMessage(trans('DISABLED BECAUSE YOU CANT ENABLE'), 'warning');
			}
			redirect('index.php?pack=links', trans('SAVED SUCC'));
		}else{
			redirect($red, trans('ERROR IN DB'), 'error');
		}
		
	}
	
	function delete(){
		if(ArtaUsergroup::getPerm('can_delete_links', 'package', 'links')==false){
			ArtaError::show(403);
		}
		$v=ArtaRequest::getVars('post');
		$black=ArtaLinks::getDefault();
		$v['id']=ArtaFilterinput::clean(@$v['id'], 'int');
		if($v['id']<1){
			redirect('index.php?pack=links');
		}else{
			if($v['id']==$black->id){
				ArtaError::show(400, trans('DEFAULT LINK CANNOT BE DELETED'));
			}
			$db=ArtaLoader::DB();
			$db->setQuery('DELETE FROM #__links WHERE id='.$db->Quote($v['id']));
			if($db->query()){
				redirect('index.php?pack=links', trans('deleted succ'));
			}else{
				redirect('index.php?pack=links', trans('error in db'), 'error');
			}
		}	
	}
	
	function saveGroup(){
		if(ArtaUsergroup::getPerm('can_addedit_link_groups', 'package', 'links')==false){
			ArtaError::show(403);
		}
		$id=getVar('id', 0, 'post', 'int');
		if($id>0){
			$red='index.php?pack=links&view=editgroup&id='.$id;
		}else{
			$red='index.php?pack=links&view=editgroup';
		}
		$title=trim(getVar('title', '', 'post', 'string'));
		if($title==''){
			redirect($red, trans('INVALID TITLE'));
		}
		if(strlen($title)>255){
			$title=substr($title,0,255);
		}
		$DB=ArtaLoader::DB();
		if($id>0){
			$DB->SetQuery('UPDATE #__link_groups SET `title`='.$DB->Quote($title).' WHERE id='.$DB->Quote($id),array('title'));
		}else{
			$DB->SetQuery('INSERT INTO #__link_groups VALUES (null, '.$DB->Quote($title).')');
		}
		if($DB->query()){
			redirect('index.php?pack=links&view=grouplist', trans('saved succ'));
		}else{
			redirect($red, trans('error in db'), 'error');
		}
	}
	
	function deleteGroup(){
		if(ArtaUsergroup::getPerm('can_delete_link_groups', 'package', 'links')==false){
			ArtaError::show(403);
		}
		$v=ArtaRequest::getVars('post');
		$v['id']=@ArtaFilterinput::clean($v['id'], 'int');
		if(!isset($v['id']) || $v['id']<1){
			redirect('index.php?pack=links&view=grouplist');
		}else{
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT COUNT(*) FROM #__links WHERE `group`='.$db->Quote($v['id']));
			$count=$db->loadResult();
			if((int)$count>0 && !@in_array($v['todo'], array('move', 'del'))){
				redirect('index.php?pack=links&view=grouplist&layout=deleter&id='.$v['id']);
			}
			if($count>0){
				switch($v['todo']){
					case 'move':
						$to=@ArtaFilterinput::clean($v['to'], 'int');
						if($to<1){
							redirect('index.php?pack=links&view=grouplist&layout=deleter&id='.$v['id'],
							trans('INVALID DESTINATION'), 'error');
						}
						$db->setQuery('SELECT id FROM #__link_groups WHERE id='.$db->Quote($to));
						if($db->loadResult()<1){
							redirect('index.php?pack=links&view=grouplist&layout=deleter&id='.$v['id'],
							trans('INVALID DESTINATION'), 'error');
						}
						if(ArtaUsergroup::getPerm('can_addedit_links', 'package', 'links')==false){
							redirect('index.php?pack=links&view=grouplist', trans('YOU CANNOT ADDEDIT LINKS SO YOU CANNOT DELETE LINK GROUPS THEN MOVE LINKS INSIDE'), 'error');
						}
						$db->setQuery('UPDATE #__links SET `group`='.$db->Quote($to).' WHERE `group`='.$db->Quote($v['id']), array('group'));
						$db->query();
					break;
					case 'del':
					
						if(ArtaUsergroup::getPerm('can_delete_links', 'package', 'links')==false){
							redirect('index.php?pack=links&view=grouplist', trans('YOU CANNOT DELETE LINKS SO YOU CANNOT DELETE LINK GROUPS WITH LINKS INSIDE'), 'error');
						}
						$db->setQuery('SELECT id FROM #__links WHERE `group`='.$db->Quote($v['id']).' AND `type`=\'default\'');
						$black=$db->loadResult();
						if($black>0){
							ArtaError::show(400, trans('DEFAULT LINK CANNOT BE DELETED MOVE IT TO ANOTHER GROUP'));
						}
						$db->setQuery('DELETE FROM #__links WHERE `group`='.$db->Quote($v['id']));
						$db->query();
					break;
				}
			}
			$db->setQuery('DELETE FROM #__link_groups WHERE id='.$db->Quote($v['id']));
			if($db->query()){
				redirect('index.php?pack=links&view=grouplist', trans('deleted succ'));
			}else{
				redirect('index.php?pack=links&view=grouplist', trans('error in db'), 'error');
			}
		}	
	}
	
	function setDefault(){
		if(ArtaUsergroup::getPerm('can_set_default_link', 'package', 'links')==false){
			ArtaError::show(403);
		}
		$id=getVar('id',0, 'post', 'int');
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT `type` FROM #__links WHERE id='.$db->Quote($id));
		$type=$db->loadResult();
		if($type==null){
			redirect('index.php?pack=links', trans('NO SUCH LINK FOUND'), 'error');
		}
		if($type=='outer'){
			redirect('index.php?pack=links', trans('DEFAULT LINK MUST BE INNER'), 'error');
		}
		$db->setQuery('UPDATE #__links SET `type`=\'inner\' WHERE `type`=\'default\'', array('type'));
		if($db->query()==false){
			redirect('index.php?pack=links', trans('ERROR IN DB'), 'error');
		}		
		$db->setQuery('UPDATE #__links SET `type`=\'default\' WHERE `id`='.$db->Quote($id), array('type'));
		if($db->query()==false){
			redirect('index.php?pack=links', trans('ERROR IN DB'), 'error');
		}else{
			redirect('index.php?pack=links', trans('DEFAULT SET SUCC'));
		}
	}
	
}
?>