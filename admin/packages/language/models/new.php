<?php
if(!defined('ARTA_VALID')){die('No access');}
class LanguageModelNew extends ArtaPackageModel{
	
	
	function getRow($id, $group, $lang){
		$db=ArtaLoader::DB();
		$group=strtolower($group);
		
		$file=ARTAPATH_ADMIN.'/packages/language/contents/'.$group.'.php';
		if($lang==null||$group==null||!is_file($file)){
			ArtaError::show();
		}
		$db->setQuery('SELECT * FROM #__languages WHERE id='.$db->Quote($lang));
		$langclient=$db->loadObject();
		if($langclient->client=='admin'){
			ArtaError::show(500, trans('Only Site Languages are translatable'));
		}elseif($langclient->client!=='site'){
			ArtaError::show(500, trans('Language not found'));
		}
		$this->lang=$langclient;
		ArtaLoader::Import('#interfaces->content', 'package');
		require_once($file);
		eval('$exists=(class_exists(\'LanguageTranslation'.ucfirst($group).'\'));');
		if($exists==false){
			ArtaError::show();
		}
		eval('$obj=new LanguageTranslation'.ucfirst($group).'();');
		if(!$obj instanceof LanguageTranslation){
			ArtaError::show(500, 'Class "LanguageTranslation'.ucfirst($group).'" should implement "LanguageTranslation" interface.');
		}
		$obj->addLanguage();
		$this->data=$obj->getRow($id);
		
		$this->row_id=/*isset($obj->row_id)?$obj->row_id:'id'*/$obj->getIDRowName();
		$this->row_title=/*isset($obj->row_title)?$obj->row_title:'title'*/$obj->getTitleRowName();
		$row_id=$this->row_id;
		if(!is_object($this->data)){
			ArtaError::show();
		}
		$db->setQuery('SELECT * FROM #__languages_translations JOIN #__languages AS l ON(l.id=language) WHERE language='.$db->Quote($lang).' AND `group`='.$db->Quote($group).' AND `row_id`='.$this->data->$row_id);
		$rows=$db->loadObjectList();
		if($rows==null){
			$rows=array();
		}
		$rows=ArtaUtility::keyByChild($rows,'row_field');
		$xmlfile=ARTAPATH_ADMIN.'/packages/language/contents/'.$group.'.xml';
		
		ArtaLoader::Import('#xml->simplexml');
		$xml=@ArtaSimpleXML::parseFile($xmlfile);
		if($xml==false){
			ArtaError::show(500, trans('Invalid XML Descriptor'));
		}
		$data=array();
		foreach($xml->field as $f){
			$f['name']=(string)$f['name'];
			$t=!(isset($f['notrans']));
			$data[]=@array('title'=>$t==true ? trans((string)$f['title']) : (string)$f['title'],
			'name'=>(string)$f['name'],
			'type'=>(string)$f['type'],
			'typedata'=>isset($f['typedata'])?(string)$f['typedata']:(string)$f,
			'default'=>$this->data, // default row (all fields)
			'defaultvalue'=>$this->data->$f['name'], // default value of field
			'value'=>$rows[(string)$f['name']]->value // translated value of field
			);
		}
		return $data;
	}
	

}
?>