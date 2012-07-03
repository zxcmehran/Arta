<?php 
if(!defined('ARTA_VALID')){die('No access');}
class PagesViewNew extends ArtaPackageView{

	function display(){
		$this->m=$this->getModel('environment');
		$t=$this->m->page->id==0 ? trans('NEW PAGE') : trans('EDIT PAGE').': '.$this->m->page->title;
		$this->setTitle($t);
		$this->addPath($t, 'index.php?pack=pages&view=new'.($this->m->page->id==0?'':'&pid='.$this->m->page->id));
		$mod=$this->getModel();
		$this->assign('p',$this->m->page);
		$this->assign('wids',$mod->getWidgets($this->m->page->id));
		$this->render();
	}
	
	function getModules(){
		$m=(array)$this->m->modules;
		$x=array();
		foreach($m as $v){
			$x[]=$v->id;
		}
		return $x;
	}
	
	function getAllModules(){
		$m=(array)$this->m->getAllModules();
		$x=array();
		foreach($m as $v){
			$x[$v->id]=$v->title;
		}
		return $x;
	}
	
}
?>