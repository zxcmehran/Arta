<?php
if(!defined('ARTA_VALID')){die('No access');}
class ModulesController extends ArtaPackageController{
	
	function display(){
		$view=$this->getView(getVar('view', 'list', '', 'string'));
		$view->display();
	}
	
	function saveM(){
		if(ArtaUsergroup::getPerm('can_addedit_mods', 'package', 'modules')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT MODULES'));
		}
		$vz=ArtaRequest::getVars('post');
		$db=ArtaLoader::DB();
		
		if(!@$vz['pid'] || !isset($vz['denied_type'])){
			ArtaError::show(500);
		}
		
		$vz=ArtaUtility::array_extend($vz,array('denied'=>array()));
		$vz=ArtaFilterinput::clean($vz,array('pid'=>'int', 'denied'=>'array', 'denied_type'=>'bool'));
		
		$vz['denied']=implode(',',$vz['denied']);
		$vz['denied']= @$vz['denied_type']==true ? '-'.$vz['denied'] : $vz['denied'];
		
		$db->setQuery('UPDATE #__modules SET denied='.$db->Quote($vz['denied']).' WHERE id='.$db->Quote($vz['pid']), array('denied'));
		
		if($db->query()){
			redirect('index.php?pack=modules&view=perm_editor&tmpl=package&done=1&pid='.$vz['pid'], trans('PERM EDITED SUCC'));
		}else{
			redirect('index.php?pack=modules&view=perm_editor&tmpl=package&pid='.$vz['pid'], trans('ERROR IN DB'), 'error');
		}
	}
	
	function activate(){
		if(ArtaUsergroup::getPerm('can_edit_mod_activity', 'package', 'modules')==false){
			ArtaError::show(403);
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__modules SET enabled=1 WHERE id='.$db->Quote(getVar('pid', 0, '', 'int')), array('enabled'));
		if(!$db->query()){
			ArtaError::show(500);
		}
	}
	
	function deactivate(){
		if(ArtaUsergroup::getPerm('can_edit_mod_activity', 'package', 'modules')==false){
			ArtaError::show(403);
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__modules SET enabled=0 WHERE id='.$db->Quote(getVar('pid', 0, '', 'int')), array('enabled'));
		if(!$db->query()){
			ArtaError::show(500);
		}
	}
	
	function reorder(){
		if(ArtaUsergroup::getPerm('can_edit_mod_order', 'package', 'modules')==false){
			ArtaError::show(403);
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		$vars=ArtaRequest::getVars();
		if(!isset($vars['pos'])){
			ArtaError::show(500);
		}
		if((int)@$vars['pos']<0){
			ArtaError::show(500);
		}
		
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__modules WHERE id='.$db->Quote(getVar('id', 0, '', 'int')));
		$mod=$db->loadObject();
		if($mod==null){
			ArtaError::show();
		}
		$db->setQuery('SELECT * FROM #__modules WHERE location='.$db->Quote($mod->location).' AND client='.$db->Quote($mod->client).' ORDER BY `order`');
		$mods=$db->loadObjectList();
		foreach($mods as $k=>$v){
			if((int)$v->order!==$k){
				$incorrect=true;
				break;
			}
		}
		if(isset($incorrect)){
			foreach($mods as $k=>$v){
				$db->setQuery('UPDATE #__modules SET `order`='.$db->Quote($k).' WHERE id='.$db->Quote($v->id), array('order'));
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
		
		
		$db->setQuery('UPDATE #__modules SET '.$ph.' WHERE location='.$db->Quote($mod->location).' AND client='.$db->Quote($mod->client).' AND `order` BETWEEN '.$db->Quote($x).' AND '.$db->Quote($y), array('order'));
		$db->query();
		
		$db->setQuery('UPDATE #__modules SET `order`='.$db->Quote($vars['pos']).' WHERE id='.$db->Quote($vars['id']), array('order'));
		
		
		if($db->query()){
			redirect('index.php?pack=modules', trans('MOVED SUCC'));
		}else{
			redirect('index.php?pack=modules', trans('ERROR IN DB'), 'error');
		}
	}
	
	function save(){
		if(ArtaUsergroup::getPerm('can_addedit_mods', 'package', 'modules')==false){
			ArtaError::show(403);
		}
		$v=ArtaRequest::getVars('post');
		
		if(!ArtaUtility::keysExists(array(
		'title',
		'location',
		),$v)){
			ArtaError::show(500);
		}
		
		$v=	ArtaFilterinput::trim($v);
		$v=	ArtaFilterinput::array_limit($v, array('title'=>255,'location'=>255));
		
		$v=ArtaUtility::array_extend($v, array(
		'enabled'=>0,
		 'showtitle'=>1,
		  'showat_type'=>0,
		   'showat'=>array(),
		    'client'=>'1',
			 'content'=>null,
			  'denied'=>array(),
			   'denied_type'=>0,
			    'id'=>0,
				 'ismenu'=>0));
			 
		$v=ArtaFilterinput::clean($v, 
		array('id'=>'int',
		 'enabled'=>'bool',
		 'title'=>'string',
		 'location'=>'string',
		 'showat_type'=>'bool',
		 'showat'=>'array',
		 'denied_type'=>'bool',
		 'denied'=>'array',
		 'client'=>'int',
		 'content'=>'safe-html',
		 'showtitle'=>'int',
		 'module'=>'int',
		 'ismenu'=>'bool',
		 'menugroup'=>'int'
		 )
		);
	/*	var_dump($v);
		die();*/
		$q= $v['id'] ? '&id='.$v['id'] : '';
		
		if($v['client']==1){
			$v['client']='admin';
		}else{
			$v['client']='site';
		}
		
		if($v['ismenu']==true){
			if(!isset($v['menugroup'])){
				ArtaError::show(500);
			}
			$v['client']='site';
			$v['content']='MENU:'.$v['menugroup'];
		}
		
		if(strlen($v['title'])==0){
			redirect('index.php?pack=modules&view=edit'.$q, trans('INVALID TITLE'), 'warning');
		}
		
		if(strlen($v['location'])==0){
			redirect('index.php?pack=modules&view=edit'.$q, trans('INVALID LOCATION'), 'warning');
		}
		
		$v['showat']= $v['showat_type']=='1' ? '-'.implode(',', $v['showat']) : implode(',', $v['showat']);
		
		$v['denied']= $v['denied_type']=='1' ? '-'.implode(',', $v['denied']) : implode(',', $v['denied']);
		
		
		
		$db=ArtaLoader::DB();
		if($v['id'] > 0){
			$db->setQuery('SELECT `module`,`enabled` FROM #__modules WHERE id='.$db->Quote($v['id']));
			$r=$db->loadObject();
			if($r===null){
				ArtaError::show(404);
			}
			if($r->enabled!=$v['enabled'] &&  ArtaUsergroup::getPerm('can_edit_mod_activity', 'package', 'modules')==false){
				$v['enabled']=$r->enabled;
			}
			$r= $r->module;
			if($r==''){
				$c=',client='.$db->Quote($v['client']);
			}else{
				$c='';
			}
			$db->setQuery('UPDATE #__modules SET '.
			'title='.$db->Quote($v['title']).
			',enabled='.$db->Quote($v['enabled']).
			',location='.$db->Quote($v['location']).
			',showat='.$db->Quote($v['showat']).
			',denied='.$db->Quote($v['denied']).
			',showtitle='.$db->Quote($v['showtitle']).$c.
			',content='.$db->Quote($v['content']).
			' WHERE id='.$db->Quote($v['id'])
			);
		}else{
			if(ArtaUsergroup::getPerm('can_edit_mod_activity', 'package', 'modules')==false){
				$v['enabled']=0;
			}
			if($v['ismenu']){
				$m='linkviewer';
			}else{
				$m='';
			}
			$db->setQuery('SELECT MAX(`order`) FROM #__modules WHERE location='.$db->Quote($v['location']).' AND client='.$db->Quote($v['client']));
			$order=(int)$db->loadResult()+1;
			$db->setQuery('INSERT INTO #__modules VALUES ('.
			'NULL'.
			','.$db->Quote($v['title']).
			','.$order.
			','.$db->Quote($v['location']).
			','.$db->Quote($v['enabled']).
			','.$db->Quote($v['showat']).
			','.$db->Quote($v['denied']).
			',\''.$m.'\''.
			','.$db->Quote($v['content']).
			','.$db->Quote($v['showtitle']).
			',0'.
			','.$db->Quote($v['client']).')'
			);
		}
		if($db->query()){
			redirect('index.php?pack=modules', trans('SAVED SUCC'));
		}else{
			redirect('index.php?pack=modules&view=edit'.$q, trans('ERROR IN DB'), 'error');
		}
	}
	
	function delete(){
		if(ArtaUsergroup::getPerm('can_delete_mods', 'package', 'modules')==false){
			ArtaError::show(403);
		}
		$id=getVar('id', 0, 'post', 'int');
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__modules WHERE id='.$db->Quote($id));
		$r=$db->loadObject();
		if($r==null){
			ArtaError::show(404, 'No such module found.');
		}
		if($r->core==1){
			redirect('index.php?pack=modules', trans('YOU CANNOT DELETE CORE MODULES'), 'error');
		}
		
		if((string)$r->module!=='' && $r->module!=='linkviewer'){
			redirect('index.php?pack=modules', trans('PLEASE UNINSTALL'), 'warning');
		}else{
			$db->setQuery('DELETE FROM #__modules WHERE id='.$db->Quote($id));
			if($db->query()){
				redirect('index.php?pack=modules', trans('DELETED SUCC'));
			}else{
				redirect('index.php?pack=modules', trans('ERROR IN DB'), 'error');
			}
		}
		
	}
}
?>