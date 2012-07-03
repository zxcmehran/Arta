<?php
if(!defined('ARTA_VALID')){die('No access');}
class DomainsController extends ArtaPackageController{
	
	function display(){
		$view=$this->getView(getVar('view', 'list', '', 'string'));
		$view->display();
	}

	function activate($enabled = 1){
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		if(ArtaUsergroup::getPerm('can_change_domains_activity', 'package', 'domains')==false){
			ArtaError::show(403, trans('YOU CANNOT CHANGE DOMAINS ACTIVITY'));
		}
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__domains SET enabled='.$enabled.' WHERE id='.$db->Quote(getVar('id', 0, '', 'int')), array('enabled'));
		if(!$db->query()){
			ArtaError::show(500, trans('ERROR IN DB'));
		}
	}
	
	function deactivate(){
		$this->activate(0);
	}
	
	function save(){
		if(ArtaUsergroup::getPerm('can_addedit_domains', 'package', 'domains')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT DOMAINS'));
		}
		// Populating data
		$db=ArtaLoader::DB();
		$config = ArtaLoader::Config();
		if($config->main_domain==''){
			ArtaApplication::enqueueMessage(trans('MAIN DOMAIN IS NOT DEFINED'), 'error');
		}
		$v=ArtaRequest::getVars('post');
		
		if(@$v['id']){$id='&id='.ArtaFilterinput::clean($v['id'], 'int');}else{
			$id='';
		}
		
		if(!ArtaUtility::keysExists(
				array('link',
				'address')
			,$v)){
				
				redirect('index.php?pack=domains&view=edit'.$id,trans('FORM ISNT COMPLETE'), 'warning');
		}
		
		$v=ArtaFilterinput::trim($v);
		$v=ArtaFilterinput::array_limit($v, array('address'=>255, 'link'=>255));
		
		$v=ArtaFilterinput::clean($v, array('title'=>'string',
		 'address'=>'string',
		  'link'=>'string',
		  'id'=>'int'));
		
		$id=@(int)$v['id'];
		
		if($id>0){
			//redirect
			$red='index.php?pack=domains&view=edit&id='.$id;
		}else{
			$red='index.php?pack=domains&view=edit';
		}
		
		// Checking data
		if( $v['address']=='' OR strpos($v['address'], '/')!==false OR strpos($v['address'], '\\')!==false OR strpos($v['address'], ' ')!==false){
			redirect($red, trans('INVALID ADDRESS'), 'warning');
		}
		
		$db->setQuery('SELECT COUNT(*) FROM #__domains WHERE `address`='.$db->Quote($v['address']).' AND id!='.$db->Quote($id));
		$count=(int)$db->loadResult();
		if($count > 0 || $config->main_domain == $v['address']){
			redirect($red, trans('DUPLICATE ADDRESS'), 'warning');
		}
		
		if(strlen($v['link'])==0){
			redirect($red, trans('INVALID LINK'), 'warning');
		}
		
		$params = ArtaURL::breakupQuery($v['link']);
		$default = ArtaLinks::getDefault();
		$dparams=ArtaURL::breakupQuery(substr($default->link, 10));
		if($dparams['pack']==$params['pack']){
			redirect($red, trans('INVALID LINK'));
		}
		
		$db->setQuery('SELECT COUNT(*) FROM #__domains WHERE '.($id>0 ? 'id !='.$db->Quote($id).' AND' : '').' (`params`='.$db->Quote('pack='.(string)$params['pack']).' OR `params` LIKE '.$db->Quote('%pack='.$db->getEscaped((string)$params['pack'], true).'%',false).')');
		$count=(int)$db->loadResult();		
		
		if($count>0){
			redirect($red, trans('INVALID LINK'));
		}
		
		
				
		//Separate edit and new tasks
		if($id>0){
						
			$db->setQuery('UPDATE #__domains SET '.
			'`address`='.$db->Quote($v['address']).
			',`params`='.$db->Quote($v['link']).
			'WHERE id='.$db->Quote($id)
			);
		}else{
						
			$db->setQuery('INSERT INTO #__domains VALUES(null'.
			','.$db->Quote($v['address']).
			','.$db->Quote($v['link']).
			',1)');
			
		}
		if($db->query()){
			redirect('index.php?pack=domains', trans('SAVED SUCC'));
		}else{
			redirect($red, trans('ERROR IN DB'), 'error');
		}
		
	}
	
	function delete(){
		if(ArtaUsergroup::getPerm('can_delete_domains', 'package', 'domains')==false){
			ArtaError::show(403);
		}
		/*if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}*/
		$id = getVar('id', '', 'post', 'int');
		if($id<1){
			redirect('index.php?pack=domains');
		}else{
			$db=ArtaLoader::DB();
			$db->setQuery('DELETE FROM #__domains WHERE id='.$db->Quote($id));
			if($db->query()){
				redirect('index.php?pack=domains', trans('deleted succ'));
			}else{
				redirect('index.php?pack=domains', trans('error in db'), 'error');
			}
		}	
	}
	
}
?>