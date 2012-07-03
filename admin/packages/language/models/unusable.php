<?php
if(!defined('ARTA_VALID')){die('No access');}
class LanguageModelUnusable extends ArtaPackageModel{
	
	function __construct(){
		ArtaLoader::Import('#interfaces->content', 'package');
		$db=ArtaLoader::DB();
		$filez=ArtaFile::listDir(ARTAPATH_ADMIN.'/packages/language/contents');
		$x=array();
		$objs=array();
		foreach($filez as $k=>$v){
			$ext=ArtaFile::getExt($v);
			if(strtolower($ext)=='php'){
				$file=ARTAPATH_ADMIN.'/packages/language/contents/'.$v;
				if(is_file($file)){
					include_once($file);
					$ext=explode('.',$v);
					array_pop($ext);
					eval('$obj=new LanguageTranslation'.ucfirst(implode('.',$ext)).'();');
					if(!$obj instanceof LanguageTranslation){
						ArtaError::show(500, 'Class "LanguageTranslation'.ucfirst(implode('.',$ext)).'" should implement "LanguageTranslation" interface.');
					}
					$obj->addLanguage();
					$objs[implode('.',$ext)]=$obj;
					$_row_id=/*isset($obj->row_id)?$obj->row_id:'id'*/$obj->getIDRowName();
					$_row_title=/*isset($obj->row_title)?$obj->row_title:'title'*/$obj->getTitleRowName();
					$x[implode('.',$ext)]=array('id'=>$_row_id, 'title'=>$_row_title);
				}
			}
		}
		$where=array();
		if(count($x)>0){
			foreach($x as $group => $data){
				$where[]='(`group`='.$db->Quote($group).' AND `row_field`='.$db->Quote($data['title']).')';
			}
		}
		$db->setQuery('SELECT * FROM #__languages_translations WHERE '.implode(' OR ', $where));
		$rows=$db->loadObjectList();
		$res=array();
		foreach($rows as $row){
			$obj=$objs[$row->group];
			if($obj->getRowExistence($row->row_id)==false){
				$xx=new stdClass;
				$xx->title=$row->value;
				$xx->id=$row->id;
				$xx->group_title=$obj->getTitle();
				$xx->lang=$this->getLanguage($row->language);
				$res[]=$xx;
			}
		}
		$this->res=$res;
		return true;
		
	}
	
	function getData(){
		return $this->res;
	}
	
	function getLanguage($lang){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT title FROM #__languages WHERE id='.$db->Quote($lang));
		return $db->loadResult();
	}

}
?>
