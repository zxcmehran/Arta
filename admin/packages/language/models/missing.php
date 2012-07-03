<?php
if(!defined('ARTA_VALID')){die('No access');}
class LanguageModelMissing extends ArtaPackageModel{
	
	function getData(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__languages WHERE `name`!='.$db->Quote('en-US'));
		$langs = $db->loadObjectList();
		$site=array();
		$admin=array();
		foreach($langs as $v){
			if($v->client=='site' || $v->client=='admin'){
				eval('$'.$v->client.'[]=$v;');
			}
		}
		
		
		$files=ArtaFile::listDir(ARTAPATH_SITE.'/languages/en-US');
		foreach($files as $fk=>$f){
			$exploded=explode('.', $f);
			if(strtolower($exploded[count($exploded)-1])=='ini'){
				array_shift($exploded);
				$files[$fk]=implode('.', $exploded);
			}else{
				unset($files[$fk]);
			}
		}
		foreach($site as $v){
			$v->missing=array();
			foreach($files as $f){
				if(!file_exists(
					ARTAPATH_SITE.'/languages/'.$v->name.'/'.$v->name.'.'.$f
				)){
					$v->missing['site|en-US.'.$f]=$v->name.'.'.$f;
				}
			}
		}
		
		$files=ArtaFile::listDir(ARTAPATH_ADMIN.'/languages/en-US');
		foreach($files as $fk=>$f){
			$exploded=explode('.', $f);
			array_shift($exploded);
			$files[$fk]=implode('.', $exploded);
		}
		foreach($admin as $v){
			$v->missing=array();
			foreach($files as $f){
				if(!file_exists(
					ARTAPATH_ADMIN.'/languages/'.$v->name.'/'.$v->name.'.'.$f
				)){
					$v->missing['admin|en-US.'.$f]=$v->name.'.'.$f;
				}
			}
		}
		return array('site'=>$site, 'admin'=>$admin);		
	}


}
?>
