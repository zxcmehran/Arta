<?php
if(!defined('ARTA_VALID')){die('No access');}
class LanguageController extends ArtaPackageController{
	
	function display(){
		ArtaAdminTabs::addTab(trans('CONTENT ITEMS'), 'index.php?pack=language');
		ArtaAdminTabs::addTab(trans('FIND UNUSABLE ITEMS'), 'index.php?pack=language&view=unusable');
		ArtaAdminTabs::addTab(trans('FIND MISSING LANGUAGE FILES'), 'index.php?pack=language&view=missing');
		$view=$this->getView(getVar('view', 'translations'), getVar('type', 'html'));
		$view->display();
	}
	
	
	function save(){
		if(ArtaUserGroup::getPerm('can_addedit_translations', 'package', 'language')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT TRANSLATIONS'));
		}
		// GET DATA FROM $_POST
		$vars=ArtaRequest::getVars('post');
		$rv=$vars;
		unset($rv['id']);
		unset($rv['lang']);
		unset($rv['group']);
		unset($rv['task']);
		unset($rv['pack']);
		if(isset($rv['limit'])) unset($rv['limit']);
		if(isset($rv['limitstart'])) unset($rv['limitstart']);
		if(isset($rv['where'])) unset($rv['where']);
		if(isset($rv['view'])) unset($rv['view']);
		
		
		$lang=ArtaFilterinput::clean($vars['lang'], 'int');
		$group=ArtaFilterinput::clean($vars['group'], 'filename');
		$id=ArtaFilterinput::clean($vars['id'], 'int');
		
		$db=ArtaLoader::DB();
		
		
		$file=ARTAPATH_ADMIN.'/packages/language/contents/'.$group.'.php';
		if($lang==null||$group==null||!is_file($file)){
			ArtaError::show();
		}
		
		$db->setQuery('SELECT * FROM #__languages WHERE id='.$db->Quote($lang));
		$langclient=$db->loadObject();
		
		if($langclient->client=='admin'){
			ArtaError::show(500, trans('Only Site Languages are translatable'));
		}elseif($langclient->client!=='site'){
			ArtaError::show(404, trans('Language not found'));
		}
		
		$this->lang=$langclient;
		
		ArtaLoader::Import('#interfaces->content', 'package');
		require_once($file);
		
		$exists=(class_exists('LanguageTranslation'.ucfirst($group)));
		
		if($exists==false){
			ArtaError::show();
		}
		
		eval('$obj=new LanguageTranslation'.ucfirst($group).'();');
		if(!$obj instanceof LanguageTranslation){
			ArtaError::show(500, 'Class "LanguageTranslation'.ucfirst($group).'" should implement "LanguageTranslation" interface.');
		}
		// ex. row from arta_blogpost
		$this->data=$obj->getRow($id);
		
		if(method_exists($obj, 'checkInput')){
			$rv=$obj->checkInput($rv, $this->data);
		}

		$original=$this->data;
		if($original==null){
			ArtaError::show();
		}
		$obj->addLanguage();
		$this->row_id=$obj->getIDRowName();
		$this->row_title=$obj->getTitleRowName();
		
		$row_id=$this->row_id;
		if(!is_object($this->data)){
			ArtaError::show();
		}
		$db->setQuery('SELECT *,t.id FROM #__languages_translations as t JOIN #__languages AS l ON(l.id=t.language) WHERE t.language='.$db->Quote($lang).' AND t.`group`='.$db->Quote($group).' AND t.`row_id`='.$this->data->$row_id);
		$rows=$db->loadObjectList();
		if($rows==null){
			$rows=array();
		}
		$rows=ArtaUtility::keyByChild($rows,'row_field');
		
		// those must be updated
		$updaties=array();
		// those must be inserted
		$inserties=array();
		foreach($rv as $var=>$val){
			if(isset($rows[$var])){
				$updaties[$var]=$val;
			}else{
				$inserties[$var]=$val;
			}
		}
		$u=$this->getCurrentUser();
		foreach($updaties as $k=>$v){
			$db->setQuery('UPDATE #__languages_translations SET `value`='.$db->Quote($v).', `original_md5_checksum`='.$db->Quote(md5($original->$k)).',`mod_by`='.$db->Quote($u->id).', `mod_time`='.$db->Quote(ArtaDate::toMySQL(time())).' WHERE `id`='.$db->Quote($rows[$k]->$row_id));
			if($db->query()==false){
				$inv=true;
			}
		}
		
		foreach($rows as $v){
			if((int)$v->enabled==1 && ArtaUserGroup::getPerm('can_change_translations_activation', 'package', 'language')==true){
				$en=1;
				break;
			}
		}
		
		if(!isset($en)){
			$en=0;
		}
		
		$ins=array();
		if(count($inserties)){
			foreach($inserties as $k=>$v){
				$ins[]='('.$db->Quote($langclient->id).','.$db->Quote($group).','.$db->Quote($original->$row_id).','.$db->Quote($k).','.$db->Quote($v).','.$db->Quote(md5($original->$k)).','.$u->id.','.$db->Quote(ArtaDate::toMySQL(time())).','.$db->Quote($en).')';
			}
			$db->setQuery('INSERT INTO #__languages_translations (`language`,`group`,`row_id`,`row_field`,`value`,`original_md5_checksum`,`mod_by`,`mod_time`,`enabled`) VALUES '.implode(',',$ins));
			if($db->query()==false){
				$inv=true;
			}	
		}
		if(!isset($inv)|| $inv==false){
			ArtaCache::clearData('lang_translate', $lang.'_'.$group);
			redirect('index.php?pack=language', trans('SAVED SUCC').($en==false?' '.trans('NOTE THAT ITS UNPUBLISHED'):''));
		}else{
			ArtaError::show(500, trans('SAVED WITH ERRORS'),'index.php?pack=language');
		}
	}

	function delete(){
		if(ArtaUserGroup::getPerm('can_delete_translations', 'package', 'language')==false){
			ArtaError::show(403, trans('YOU CANNOT DELETE TRANSLATIONS'));
		}
		$vars=ArtaRequest::getVars();
		$lang=ArtaFilterinput::clean($vars['lang'], 'int');
		$group=ArtaFilterinput::clean($vars['group'], 'filename');
		$id=ArtaFilterinput::clean($vars['id'], 'int');
		
		$db=ArtaLoader::DB();
		$db->setQuery('DELETE FROM #__languages_translations WHERE language='.$db->Quote($lang).' AND `group`='.$db->Quote($group).' AND `row_id`='.$db->Quote($id));
		if($db->query()){
			ArtaCache::clearData('lang_translate', $lang.'_'.$group);
			redirect('index.php?pack=language', trans('DELETED SUCC'));
		}else{
			ArtaError::show(500,trans('ERROR IN DB'),'index.php?pack=language');
		}
	}
	
	function activate(){
		if(ArtaUserGroup::getPerm('can_change_translations_activation', 'package', 'language')==false){
			ArtaError::show(403, trans('YOU CANNOT CHANGE TRANSLATIONS ACTIVATION STATUS'));
		
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}}
		
		$vars=ArtaRequest::getVars();
		$lang=ArtaFilterinput::clean($vars['lang'], 'int');
		$group=ArtaFilterinput::clean($vars['group'], 'filename');
		$id=ArtaFilterinput::clean($vars['id'], 'int');
		
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__languages_translations SET `enabled`=1 WHERE language='.$db->Quote($lang).' AND `group`='.$db->Quote($group).' AND `row_id`='.$db->Quote($id), array('enabled'));
		if($db->query()){
			ArtaCache::clearData('lang_translate', $lang.'_'.$group);
			redirect('index.php?pack=language', trans('ACTIVATED SUCC'));
		}else{
			ArtaError::show(500,trans('ERROR IN DB'));
		}
	}
	
	function deactivate(){
		if(ArtaUserGroup::getPerm('can_change_translations_activation', 'package', 'language')==false){
			ArtaError::show(403, trans('YOU CANNOT CHANGE TRANSLATIONS ACTIVATION STATUS'));
		}
		
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		
		$vars=ArtaRequest::getVars();
		$lang=ArtaFilterinput::clean($vars['lang'], 'int');
		$group=ArtaFilterinput::clean($vars['group'], 'filename');
		$id=ArtaFilterinput::clean($vars['id'], 'int');
		
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__languages_translations SET `enabled`=0 WHERE language='.$db->Quote($lang).' AND `group`='.$db->Quote($group).' AND `row_id`='.$db->Quote($id), array('enabled'));
		if($db->query()){
			ArtaCache::clearData('lang_translate', $lang.'_'.$group);
			redirect('index.php?pack=language', trans('DEACTIVATED SUCC'));
		}else{
			ArtaError::show(500,trans('ERROR IN DB'));
		}
	}
	
	function deleteUnusable(){
		if(ArtaUserGroup::getPerm('can_remove_unusable_translations', 'package', 'language')==false){
			ArtaError::show(403, trans('YOU CANNOT REMOVE UNUSABLE TRANSLATIONS'));
		}
		$db=ArtaLoader::DB();
		$vars=ArtaRequest::getVars();
		$id=ArtaFilterinput::clean($vars['ids'], 'array');
		foreach($id as $k=>$v){
			$id[$k]=$db->Quote(ArtaFilterinput::clean($v,'int'));
		}
		$db->setQuery('SELECT * FROM #__languages_translations WHERE id IN('.implode(',',$id).')');
		$r=$db->loadObjectList();
		$where=array();
		foreach($r as $k=>$v){
			$where[]='(`language`='.$db->Quote($v->language).' AND `group`='.$db->Quote($v->group).' AND `row_id`='.$db->Quote($v->row_id).')';
		}
		
		$db->setQuery('DELETE FROM #__languages_translations WHERE '.implode(' OR ',$where));
		if($db->query()){
			redirect('index.php?pack=language&view=unusable', trans('DELETED SUCC'));
		}else{
			ArtaError::show(500, trans('TRANSLATION DELETE ERROR'),'index.php?pack=language&view=unusable');
		}
	}

}
?>