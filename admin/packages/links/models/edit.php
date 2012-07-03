<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/27 14:16 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class LinksModelEdit extends ArtaPackageModel{
	
	function getData(){
		if(ArtaUsergroup::getPerm('can_addedit_links', 'package', 'links')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT LINKS'));
		}
		$id=getVar('id',false, '', 'int');
		if($id>0){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__links WHERE id='.$db->Quote($id));
			$r=$db->loadObject();
			if($r==null){
				ArtaError::show();
			}
			if(strlen($r->denied) && $r->denied{0}=='-'){
				$r->denied_type=1;
				$r->denied=substr($r->denied,1);
			}else{
				$r->denied_type=0;
			}
			if(strlen($r->denied)){
				$r->denied=explode(',',$r->denied);
			}else{
				$r->denied=array();
			}
			
			if($r->type=='default' || $r->type=='inner'){
				$r->link=substr($r->link, strlen('index.php?'));
			}
		}else{
			$r=new stdClass;
			$r->id=false;
			$r->title='';
			$r->link=null;
			$r->type='inner';
			$r->group=0;
			$r->enabled=1;
			$r->denied=array();
			$r->denied_type=0;
			$r->newwin=0;
		}
		return $r;
	}
	
	function getGroups(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__link_groups');
		$return=$db->loadObjectList();
		$r=array();
		foreach($return as $k=>$v){
			$r[(int)$v->id]=$v->title;
		}
		return $r;
	}
	
	function getUGs(){
		$r= ArtaUsergroup::getItems();
		$x=array();
		foreach($r as $v){
			$x[$v->id]=$v->title;
		}
		return $x;
	} 
	
}

?>