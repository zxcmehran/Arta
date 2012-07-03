<?php 
if(!defined('ARTA_VALID')){die('No access');}
class PagesViewPage extends ArtaPackageView{

	function display(){
		$this->m=$this->getModel();
		$this->selectModules();
		if($this->m->page->is_dynamic==true){
			$this->assign('wids', $this->getWidgets());
		}else{
			$this->assign('page', $this->m->page);
		}
		
		$this->assign('pid', $this->m->page->id);
		
		$params=unserialize($this->m->page->params);
		if(isset($params['canvasAlign'])){
			$this->assign('align', $params['canvasAlign']);
			unset($params['canvasAlign']);
		}else{
			$this->assign('align', null);
		}
		$this->assign('params', $params);
		
		if(@$params['template']!='' && @$params['template']!='-'){
			$t=ArtaLoader::Template();
			$t->name=$params['template'];
		}
		$this->setTitle($this->m->getPageTitle());
		$this->addpath($this->m->getPageTitle(), 'index.php?pack=pages&pid='.$this->m->page->id);
		$this->render($this->m->page->is_dynamic?null:'static');
	}
	
	function selectModules(){
		$m=$this->m;
		$mods = $m->getModules();
		if($mods=='ALL'){
			$module=ArtaLoader::Module();
			$module->items=array();
		}elseif($mods!='NOT ALL'){
			$module=ArtaLoader::Module();
			$module->items=(array)$mods;
		}
	}
	
	function getWidgets(){
		$m=$this->m;
		$w=$m->getWidgets();
		$r='';
		$invalid=false;
		$lang=ArtaLoader::Language();
		$plug=ArtaLoader::Plugin();
		foreach($w as $k=>$v){
			$params=unserialize($v->params);
			$params=ArtaUtility::array_extend($params, array('width'=>'250px', 'height'=>'250px', 'top'=>'0', 'left'=>'0'));
			$settings=@$params['settings'];
			if($v->widget>0){
				$wid=$m->getWidgetResource($v->widget);
				if($wid!==null){			
					$lang->addtoNeed($wid->filename,'widget');
					$settings=ArtaUtility::array_extend($settings, $m->getSettings($wid->filename));
					ob_start();
					$this->includeFile($wid, $settings, ARTAPATH_BASEDIR.'/widgets/'.$wid->filename.'.php');
					$v->content=ob_get_contents();
					ob_end_clean();
				}else{
					$static=true;
				}
			}else{
				$static=true;
			}
			if(isset($static)){
				unset($static);
				$plug->trigger('onShowBody', array(&$v->content, 'widget'));
			}
			
			$position=' top:'.htmlspecialchars($params['top']).'; left: '.htmlspecialchars($params['left']);
			if(ArtaBrowser::getPlatform() == 'midp'){
				$position='';
			}
			$position.=';float:left;';
			if(trim($v->title)!=''){
				$title='<tr><td class="widget_title"><header>'.htmlspecialchars($v->title).'</header></td></tr>';
			}else{
				$title='';
			}
			$r.='<section><div id="widget_'.$v->id.'" class="custom_widget" style="width:'.htmlspecialchars($params['width']).'; height:'.htmlspecialchars($params['height']).';'.$position.@htmlspecialchars($params['other']).'"><table>'.$title.'<tr><td class="widget_content">'.$v->content.'</td></tr></table></div></section>';
		}
		return $r;
	}
	
	function getPerm(){
		$u=$this->getCurrentUser();
		if(ArtaUserGroup::getPerm('can_addedit_pages', 'package', 'pages') AND 
			($u->id==$this->m->page->added_by || ArtaUserGroup::getPerm('can_edit_others_pages', 'package', 'pages')==true)){
			return true;
		}
		return false;
	}
	
	/**
	 * Processes files in separated environment
	 */
	function includeFile($wid, $settings){
		include func_get_arg(2);
	}
}
?>