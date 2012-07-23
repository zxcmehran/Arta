<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/21 12:29 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class PagesModelPage extends ArtaPackageModel{
	
	function __construct(){
		$data=$this->getViewing();
		//page
		$this->page=$data;
		
		$plugin=ArtaLoader::Plugin();
		$plugin->trigger('onPrepareContent', array(&$this->page, 'page'));
		
		//modules
		if($data->deny_type==1){
			$n='NOT ';
		}else{
			$n='';
		}
		$t=ArtaLoader::Template();
		$t->description.=$data->desc;
		$t->keywords.=', '.$data->tags;
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
		if($data->is_dynamic==true){
			$db->setQuery('SELECT * FROM #__pages_widgets WHERE pageid ='.$data->id);
			$this->widgets=(array)$db->loadObjectList();
			foreach($this->widgets as $k=>&$v){
				$plugin->trigger('onPrepareContent', array(&$v, 'widget'));
			}
		}else{
			$this->page->langs=$this->getLangsAvailable($data->id);
			$this->widgets=array();
		}
	}
	
	function getWidgets(){
		return (array)$this->widgets;
	}
		
	function getModules(){
		return $this->modules;
	}
	
	function getPageTitle(){
		return $this->page->title;
	}
	
	function getViewing(){
		$id=getVar('pid', '', '', 'int');
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($id));
		$r=$db->loadObject();
		if($r==null){
			ArtaError::show(404);
		}
		if(strlen($r->mods) && $r->mods{0}=='-'){
			$r->mods=substr($r->mods,1);
			$r->deny_type=1;
		}else{
			$r->deny_type=0;
		}
		if(ArtaUsergroup::processDenied($r->denied)==false || ($r->enabled==false && ArtaUserGroup::getPerm('can_access_unpublished_pages', 'package', 'pages')==false)){
			ArtaError::show(403, trans('YOU CANNOT ACCESS THIS PAGE'));
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
	
	function getLangsAvailable($id){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT DISTINCT (SELECT l.title FROM #__languages as l WHERE id=t.language) FROM #__languages_translations as t WHERE `group`=\'pages\' AND enabled=1 AND row_id='.$db->Quote($id));
		return $db->loadResultArray();
	}
	

	
}

?>