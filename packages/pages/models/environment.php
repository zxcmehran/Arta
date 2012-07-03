<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/21 12:29 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class PagesModelEnvironment extends ArtaPackageModel{
	
	function __construct(){
		$u=$this->getCurrentUser();
		if(ArtaUsergroup::getPerm('can_addedit_pages', 'package', 'pages')==false){
			if($u->id==0){
				redirect('index.php?pack=user&view=login&redirect='.base64_encode('index.php?pack=pages&view=environment'));
			}else{
				ArtaError::show(403, trans('YOU CANNOT ADDEDIT PAGES'));
			}
		}
		$data=$this->getEditing();
		
		if($data->is_dynamic==false && getVar('view')=='environment'){
			redirect('index.php?pack=pages&view=new&pid='.$data->id);
		}
		
		//page
		$this->page=$data;
		
		if(getVar('view')=='environment' AND $data->id==0){
			
			redirect('index.php?pack=pages&view=new');
		}
		
		if($data->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
		}
		//modules
		//$mods=getVar('mods', explode(',',$data->mods), null, 'array');
		if($data->deny_type==1){
			$n='NOT ';
		}else{
			$n='';
		}
		$mods=explode(',',$data->mods);
		foreach($mods as $mk=>$mv){
			if(trim($mv)==''){
				unset($mods[$mk]);
			}
		}
		$db=ArtaLoader::DB();
		if(count($mods)>0){
			$mods=array_map(array($db, 'Quote'), $mods);
			$db->setQuery('SELECT * FROM #__modules WHERE id '.$n.'IN ('.implode(',', $mods).') AND `enabled`=1 AND client=\'site\' ORDER BY `location`, `order`');
			$this->modules=(array)$db->loadObjectList();
		}else{
			$this->modules=$n.'ALL';
		}
		//widgets
		$db->setQuery('SELECT * FROM #__pages_widgets WHERE pageid ='.$data->id);
		$this->widgets=(array)$db->loadObjectList();
	}
	
	function getWidgets(){
		return (array)$this->widgets;
	}
	
	function getAllModules(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__modules WHERE `enabled`=1 AND client=\'site\' ORDER BY `location`,`order`');
		return (array)$db->loadObjectList();
	}
	
	function getModules(){
		return $this->modules;
	}
	
	function getPageTitle(){
		return $this->page->title;
	}
	
	function getEditing(){
		$id=getVar('arta_environment_editing_pageid', '', 'cookie', 'int');
		
		if(getVar('view')=='new'){
			$id=getVar('pid', '', '', 'int');
		}else{
			
			ArtaRequest::addvar('pid',$id);
		}
		
		if($id>0){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($id));
			$r=$db->loadObject();
			if(strlen($r->mods) && $r->mods{0}=='-'){
				$r->mods=substr($r->mods,1);
				$r->deny_type=1;
			}else{
				$r->deny_type=0;
			}
			if(strlen($r->denied) && $r->denied{0}=='-'){
				$r->denied=substr($r->denied,1);
				$r->denied_type=1;
			}else{
				$r->denied_type=0;
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
			$r->enabled=0;
			$r->deny_type=1;
			$r->denied='';
			$r->denied_type=0;
			$u=$this->getCurrentUser();
			$r->added_by=$u->id;
			$r->params='a:2:{s:6:"height";s:5:"600px";s:5:"width";s:3:"0px";}';
			
		}
		
		return $r;
	}
	
	function getWidgetResource($id){
		if(!isset($GLOBALS['CACHE']['pages.widget_resources']) && ArtaCache::isUsable('pages', 'widget_resources')){
			$GLOBALS['CACHE']['pages.widget_resources']=ArtaCache::getData('pages', 'widget_resources');
		}
		
		if(!isset($GLOBALS['CACHE']['pages.widget_resources'][(int)$id])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__pages_widgets_resource WHERE id='.$db->Quote($id));
			@$GLOBALS['CACHE']['pages.widget_resources'][(int)$id]=$db->loadObject();
			ArtaCache::putData('pages','widget_resources',$GLOBALS['CACHE']['pages.widget_resources']);
		}
		return @$GLOBALS['CACHE']['pages.widget_resources'][(int)$id];
	}
	
	function getSettings($id){
		if(!isset($GLOBALS['CACHE']['pages.widget_settings']) && ArtaCache::isUsable('pages', 'widget_settings')){
			$GLOBALS['CACHE']['pages.widget_settings']=ArtaCache::getData('pages', 'widget_settings');
		}
		
		if(!isset($GLOBALS['CACHE']['pages.widget_settings'][$id])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__settings WHERE extype=\'widget\' AND extname='.$db->Quote($id));
			$return= (array)$db->loadObjectList();
			$x=array();
			foreach($return as $v){
				$x[$v->var]=$v->value;
			}
			@$GLOBALS['CACHE']['pages.widget_settings'][$id]=$x;
			ArtaCache::putData('pages','widget_settings',$GLOBALS['CACHE']['pages.widget_settings']);
		}
		return @$GLOBALS['CACHE']['pages.widget_settings'][$id];
	}
	
}

?>