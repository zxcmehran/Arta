<?php 
if(!defined('ARTA_VALID')){die('No access');}

function plgTranslateTranslate(&$row,$type, $id_row='id'){
	$config=ArtaLoader::Config();
	//beta test
/*	if($config->cache==false){
		$config->cache='1';
		$forced=true;
	}*/
	if($type=='config'){
		$res = plgTranslateTranslateConfig($row);
	/*	if($forced==true){
			$config->cache='0';
		}*/
		return $res;
	}
	$db=ArtaLoader::DB();


	if(isset($GLOBALS['CACHE']['plgTarnslate.langid'])==false){
		$language=ArtaLoader::Language();
		$lang=$language->getName();
		$langs_id_map=(array)ArtaCache::getData('plugin_translate','langids');
		if(isset($langs_id_map[$lang])){
			$langid=$langs_id_map[$lang];
		}else{
			$db->setQuery('SELECT id FROM #__languages WHERE `name`='.$db->Quote($lang));
			$langid=$db->loadResult();
			$langs_id_map[$lang]=$langid;
			ArtaCache::putData('plugin_translate','langids',$langs_id_map);
		}
		if(defined('REQUEST_VARS_SET')){
			$GLOBALS['CACHE']['plgTarnslate.langid']=$langid;
		}
	}else{
		$langid=$GLOBALS['CACHE']['plgTarnslate.langid'];
	}
	
	
	
	if($config->cache==false){
		if(isset($GLOBALS['CACHE']['plgTranslate.translations'][$type][$row->$id_row])==false){
			
			$db->setQuery('SELECT * FROM #__languages_translations WHERE row_id='.$db->Quote($row->$id_row).' AND `group`='.$db->Quote($type).' AND `enabled`=\'1\' AND `language`='.$db->Quote($langid));
			$data=$db->loadObjectList();
			$GLOBALS['CACHE']['plgTranslate.translations'][$type][$row->$id_row]=$data;
		}else{
			$data=$GLOBALS['CACHE']['plgTranslate.translations'][$type][$row->$id_row];
		}
	}else{
		if(ArtaCache::isUsable('lang_translate', $langid.'_'.$type)){
			$GLOBALS['CACHE']['plgTranslate.translations'][$type]=
				ArtaCache::getData('lang_translate', $langid.'_'.$type);
		}else{
			$db->setQuery('SELECT * FROM #__languages_translations WHERE `group`='.$db->Quote($type).' AND `enabled`=\'1\' AND `language`='.$db->Quote($langid));
			$data=(array)$db->loadObjectList();
			$data=ArtaUtility::keyByChild($data, 'row_id', true);
			ArtaCache::putData('lang_translate', $langid.'_'.$type, $data);
			$GLOBALS['CACHE']['plgTranslate.translations'][$type]=$data;
		}
		
		$data=@$GLOBALS['CACHE']['plgTranslate.translations'][$type][$row->$id_row];
	}

	
	
	if($data!=null){
		if(is_object($data)){
			$data=array($data);
		}
		foreach($data as $k=>$v){
			$xx=$v->row_field;
			if(isset($row->$xx)){
				$row->$xx=$v->value;
			}
		}
	}
	
/*	if($forced==true){
		$config->cache='0';
	}*/
	return true;
}

function plgTranslateTranslateConfig(&$row){
	$db=ArtaLoader::DB();
	
	// legal ones to be translated
	$x=array('site_name', 'homepage_title','description', 'keywords', 'time_format', 'cal_type', 'offline_msg');

	
	if(isset($GLOBALS['CACHE']['plgTarnslate.langid'])==false){
		$language=ArtaLoader::Language();
		$lang=$language->getName();
		$langs_id_map=(array)ArtaCache::getData('plugin_translate','langids');
		if(isset($langs_id_map[$lang])){
			$langid=$langs_id_map[$lang];
		}else{
			$db->setQuery('SELECT id FROM #__languages WHERE `name`='.$db->Quote($lang));
			$langid=$db->loadResult();
			$langs_id_map[$lang]=$langid;
			ArtaCache::putData('plugin_translate','langids',$langs_id_map);
		}
		if(defined('REQUEST_VARS_SET')){
			$GLOBALS['CACHE']['plgTarnslate.langid']=$langid;
		}
	}else{
		$langid=$GLOBALS['CACHE']['plgTarnslate.langid'];
	}
	
	$db->setQuery('SELECT * FROM #__languages_translations WHERE `group`=\'config\' AND row_field=\'value\' AND `enabled`=\'1\' AND `language`='.$db->Quote($langid));
	$data=$db->loadObjectList('row_id');


	foreach($x as $k=>$v){
		if(isset($data[$k+1])){
			$row->{$v}=$data[$k+1]->value;
		}
	}

	
}

?>