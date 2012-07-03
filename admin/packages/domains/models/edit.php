<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/27 14:16 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class DomainsModelEdit extends ArtaPackageModel{
	
	function getData(){
		if(ArtaUsergroup::getPerm('can_addedit_domains', 'package', 'domains')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT DOMAINS'));
		}
		$id=getVar('id',false, '', 'int');
		if($id>0){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__domains WHERE id='.$db->Quote($id));
			$r=$db->loadObject();
			
			if($r==null){
				ArtaError::show();
			}
			$params = ArtaURL::breakupQuery($r->params);
			$this->current = (string)$params['pack'];			
		}else{
			$r=new stdClass;
			$r->id=false;
			$r->address='';
			$r->params='';
			$r->enabled=0;
		}
		return $r;
	}
	
	function getSitePackages(){
		$dir = ArtaFile::listDir(ARTAPATH_BASEDIR.'/packages');
		$db=ArtaLoader::DB();
		$dir = array_map(array($db, 'Quote'), $dir);
		$db->setQuery('SELECT `title`,`name` FROM #__packages WHERE `name` IN('.implode(',',$dir).')');
		$return=$db->loadObjectList();
		$r=array();
		foreach($return as $k=>$v){
			$r[$v->name]=$v->title;
		}
		return $r;
	}
	
	function getUnusablePackages(){
		$r=array();
		$homepage = ArtaLinks::getDefault();
		$params=ArtaURL::breakupQuery(substr($homepage->link, 10));
		if(@$this->current!=(string)$params['pack']){
			$r[]=(string)$params['pack'];
		}
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT `params` FROM #__domains');
		$res = $db->loadResultArray();
		foreach($res as $params){
			$params = ArtaURL::breakupQuery($params);
			if(@$this->current!=(string)$params['pack']){
				$r[]=(string)$params['pack'];
			}
		}
		return $r;
	} 
	
}

?>