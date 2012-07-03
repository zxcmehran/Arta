<?php
if(!defined('ARTA_VALID')){die('No access');}
class PagesModelNew extends ArtaPackageModel{
	
	function getData($id){
		if(ArtaUsergroup::getPerm('can_addedit_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT PAGES'));
		}
		if((int)$id>0){
			$db=ArtaLoader::DB();
			$db->setQuery("SELECT * FROM #__pages WHERE id=".$db->Quote($id));
			$return= $db->loadObject();
			if($return==null){
				ArtaError::show();
			}
			
			$u=$this->getCurrentUser();
			
			if($u->id!== $return->added_by && 
			 ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
				ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
			}
			
			if(strlen($return->mods) && $return->mods{0}=='-'){
				$return->mods=substr($return->mods,1);
				$return->deny_type=1;
			}else{
				$return->deny_type=0;
			}
			
			if(strlen($return->denied) && $return->denied{0}=='-'){
				$return->denied=substr($return->denied,1);
				$return->denied_type=1;
			}else{
				$return->denied_type=0;
			}
			
		}else{
			$r=new stdClass;
			$r->id=0;
			$r->title='';
			$r->sef_alias='';
			$r->desc='';
			$r->tags='';
			$r->is_dynamic=false;
			$r->content='';
			$r->mods='';
			$r->enabled=false;
			$r->deny_type=1;
			$r->denied='';
			$r->denied_type=0;
			$u=$this->getCurrentUser();
			$r->added_by=$u->id;
			$r->params='a:1:{s:6:"height";s:5:"600px";}';
			$return=$r;
		}
		$return->params=unserialize($return->params);
		return $return;

	}
	
	function getAllModules(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__modules WHERE `enabled`=1 AND client=\'site\' ORDER BY `location`,`order`');
		return $db->loadObjectList();
	}
	
	function getWidgets($pid){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__pages_widgets WHERE pageid='.$db->Quote($pid));
		$r=(array)$db->loadObjectList();
		foreach($r as &$v){
			if($v->widget>0){
				$db->setQuery('SELECT title FROM #__pages_widgets_resource WHERE id='.$db->Quote($v->widget));
				$wid=$db->loadResult();
				if($wid!==null){
					$v->title.=' ('.$wid.')';
				}
			}
		}
		return $r;
	}

}
