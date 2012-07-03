<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/27 14:16 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class ModulesModelEdit extends ArtaPackageModel{
	
	function getData(){
		if(ArtaUsergroup::getPerm('can_addedit_mods', 'package', 'modules')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT MODULES'));
		}
		$id=getVar('id',false, '', 'int');
		if((int)$id>0){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__modules WHERE id='.$db->Quote($id));
			$r=$db->loadObject();
			if($r==null){
				ArtaError::show();
			}

			if(strlen($r->showat) && $r->showat{0}=='-'){
				$r->showat_type=1;
				$r->showat=substr($r->showat, 1);
			}else{
				$r->showat_type=0;
			}
			if(strlen($r->showat)){
				$r->showat=explode(',',$r->showat);
			}else{
				$r->showat=array();
			}
			
			
			if(strlen($r->denied) && $r->denied{0}=='-'){
				$r->denied_type=1;
				$r->denied=substr($r->denied, 1);
			}else{
				$r->denied_type=0;
			}
			if(strlen($r->denied)){
				$r->denied=explode(',',$r->denied);
			}else{
				$r->denied=array();
			}
			
			
			if(@substr($r->content,0,5)=='MENU:'&&$r->module=='linkviewer'){
				$r->linkviewer=true;
				$r->module=trans('LINKVIEWER MODULE');
				$r->content=substr($r->content,5);
			}
		}else{
			$r=new stdClass;
			$r->id=false;
			$r->title='';
			$r->module=null;
			$r->enabled=1;
			$r->location=null;
			$r->client=null;
			$r->showtitle=1;
			$r->content='';
			$r->showat=array();
			$r->showat_type=0;
			$r->denied=array();
			$r->denied_type=0;
		}
		return $r;
	}
	
	function getPacks(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT `id`,`title`,`name` FROM #__packages');
		$r=$db->loadObjectList();
		if($r==null){
			$r=array();
		}
		$x=array();
		$reserved=array();
		foreach($r as $k=>$v){
			if(!in_array($v->name, $reserved)){
				$x[$v->id]=$v->title;
				$reserved[]=$v->name;
			}
		}
		return $x;
	}
	
	function getMods(){
		return array(trans('NA'), trans('LINKVIEWER MODULE'));
	}
	
	function getGroups(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__link_groups');
		$r=$db->loadObjectList();
		$x=array();
		foreach($r as $v){
			$x[$v->id]=$v->title;
		}
		return $x;
	}
	
}

?>