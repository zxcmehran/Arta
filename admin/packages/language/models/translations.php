<?php
if(!defined('ARTA_VALID')){die('No access');}
class LanguageModelTranslations extends ArtaPackageModel{
	
	function __construct(){
		$db=ArtaLoader::DB();
		$lang=getVar('lang',null,'','int');
		$group=getVar('group', null,'','filename');
		$file=ARTAPATH_ADMIN.'/packages/language/contents/'.$group.'.php';
		if($lang==null||$group==null){
			if(isset($_SESSION['translations'])){
				$lang=$_SESSION['translations']['lang'];
				$group=$_SESSION['translations']['group'];
				ArtaRequest::addVar('lang',$lang);
				ArtaRequest::addVar('group',$group);
				$file=ARTAPATH_ADMIN.'/packages/language/contents/'.$group.'.php';
			}else{
				$this->data=null;
				$this->count=null;
				$this->controls=null;
				return null;
			}
		}else{
			$_SESSION['translations']=array();
			$_SESSION['translations']['lang']=$lang;
			$_SESSION['translations']['group']=$group;
		}
		
		if(!is_file($file)){
			$this->data=null;
			$this->count=null;
			$this->controls=null;
			return null;
		}
		$db->setQuery('SELECT client FROM #__languages WHERE id='.$db->Quote($lang));
		$langclient=$db->loadResult();
		if($langclient=='admin'){
			ArtaError::show(500, trans('ONLY SITE LANGUAGES ARE TRANSLATABLE'));
		}elseif($langclient!=='site'){
			ArtaError::show(404, trans('Language not found'));
		}
		ArtaLoader::Import('#interfaces->content', 'package');
		require_once($file);		
		eval('$exists=(class_exists(\'LanguageTranslation'.ucfirst($group).'\'));');
		if($exists==false){
			$this->data=null;
			$this->count=null;
			$this->controls=null;
			return null;
		}
		eval('$obj=new LanguageTranslation'.ucfirst($group).'();');
		if(!$obj instanceof LanguageTranslation){
			ArtaError::show(500, 'Class "LanguageTranslation'.ucfirst($group).'" should implement "LanguageTranslation" interface.');
		}
		$obj->addLanguage();
		$this->data=$obj->getRows();
		$this->count=$obj->getRowsCount();
		$this->controls=$obj->getControls();
		
		$this->row_id=/*isset($obj->row_id)?$obj->row_id:'id'*/$obj->getIDRowName();
		$this->row_title=/*isset($obj->row_title)?$obj->row_title:'title'*/$obj->getTitleRowName();
		
		
		$db->setQuery('SELECT * FROM #__languages_translations JOIN #__languages AS l ON(l.id=language) WHERE language='.$db->Quote($lang).' AND `group`='.$db->Quote($group).' AND `row_field`='.$db->Quote($this->row_title));
		$rowz=$db->loadObjectList();
		if($rowz==null){
			$rowz=array();
		}
		
		$rowz=ArtaUtility::keyByChild($rowz,'row_id', true);

		$_USERS=array();
		foreach($this->data as $k=>&$v){
			$v->__info=new stdClass;
			
			$id=$this->row_id;
			$id=$v->$id;
			$exists=(isset($rowz[$id]));
			
			if($exists){
				if(!isset($v->__value)){
					$v->__value=array();
				}
				$v->__value=$rowz[$id];
				if(!is_array($v->__value)){
					$v->__value=array($v->__value);
				}
			}else{
				$v->__value=array();
			}
			
			$v->__value=ArtaUtility::keyByChild($v->__value,'row_field');

			
			foreach($v->__value as $kk=>&$vv){
				$vv=(array)$vv;
				$v->__value[$kk]=$vv;
				$v->__info->transmod_by=$vv['mod_by'];
				$v->__info->transmod_time=$vv['mod_time'];
				$v->__info->enabled=$vv['enabled'];
				
				if(isset($v->$kk) && md5($v->$kk)!==$vv['original_md5_checksum']){
					$v->__info->invalid=true;
				}
			}
			
			if(!isset($v->__info->invalid)){
				$v->__info->invalid=false;
			}
			if(isset($v->__info->transmod_by) && !isset($_USERS[$v->__info->transmod_by])){
				$db->setQuery('SELECT username FROM #__users WHERE id='.$db->Quote($v->__info->transmod_by));
				$_USERS[$v->__info->transmod_by]=$db->loadResult();
				if($_USERS[$v->__info->transmod_by]==null){
					$_USERS[$v->__info->transmod_by]=$v->__info->transmod_by;
				}
			}
			if(isset($_USERS[@$v->__info->transmod_by])){
				$v->__info->transmod_by_user=$_USERS[$v->__info->transmod_by];
			}
			
		}
		
		unset($v);
		
		switch(@(int)getVar('show',false, '', 'int')){
			case 1:
				foreach($this->data as $k=>$v){
					if(count($v->__value)!==0){
						unset($this->data[$k]);
					}
				}
			break;
			case 2:
				foreach($this->data as $k=>$v){
					if(count($v->__value)==0){
						unset($this->data[$k]);
					}
				}
			break;
			case 3:
				foreach($this->data as $k=>$v){
					if(!isset($v->__info->enabled) || $v->__info->enabled==true){
						unset($this->data[$k]);
					}
				}
			break;
			case 4:
				foreach($this->data as $k=>$v){
					
					if($v->__info->invalid!==true && $v->__value==array()){
						unset($this->data[$k]);
					}
				}
			break;
		}
		
	}
	
	function getData(){
		return $this->data;
	}
	
	function getCount(){
		return $this->count;
	}
	
	function getControls(){
		return $this->controls;
	}
	
	function getGroups(){
		ArtaLoader::Import('#interfaces->content', 'package');
		$filez=ArtaFile::listDir(ARTAPATH_ADMIN.'/packages/language/contents');
		if(!is_array($filez)){
			$filez=array();
		}
		$x=array();
		foreach($filez as $k=>$v){
			$filename=ArtaFile::getFilename($v);
			$ext=ArtaFile::getExt($filename);
			$filename=@array_shift(explode('.',$filename));
			$file=ARTAPATH_ADMIN.'/packages/language/contents/'.$v;
			if(is_file($file) && strtolower($ext)=='php'){
				include_once($file);
				$a='LanguageTranslation'.ucfirst($filename);
				$obj=new $a;
				if(!$obj instanceof LanguageTranslation){
					ArtaError::show(500, 'Class "'.$a.'" should implement "LanguageTranslation" interface.');
				}
				$obj->addLanguage();
				$x[$filename]=$obj->getTitle();
			}
		}
		return $x;
	}
	function getLanguages(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__languages WHERE `client`=\'site\'');
		$r=$db->loadObjectList();
		$x=array();
		foreach($r as $v){
			$x[$v->id]=$v->title;
		}
		return $x;
	}

}
?>
