<?php
if(!defined('ARTA_VALID')){die('No access');}
class PagesViewNew extends ArtaPackageView{
	
	function display(){
		$id=getVar('id', false, null, 'int');

		if($id==false){$title=trans('NEW PAGE');}else{$title=trans('EDIT PAGE');}
		$this->setTitle($title);
		
		$model=$this->getModel();
		$data=$model->getData($id);
		$this->assign('data', $data);
		if($data->is_dynamic==true){
			$widgets=$model->getWidgets($id);
		}else{
			$widgets=array();
		}
		$this->assign('wids', $widgets);
		
		ArtaAdminTips::addTip(trans('NEW PAGE DESC'));
		
		if(count($widgets)>0){
			ArtaAdminButtons::addSave('','if($(\'is_dynamic_0\').checked && confirm(\''.addslashes(trans('IT WILL DELETE PAGE WIDGETS')).'\')==false){return false;}');
		}elseif($data->is_dynamic==false AND strlen(trim($data->content))>0){
			ArtaAdminButtons::addSave('','if($(\'is_dynamic_1\').checked && confirm(\''.addslashes(trans('IT WILL DELETE PAGE CONTENTS')).'\')==false){return false;}');
		}else{
			ArtaAdminButtons::addSave();
		}
		
		ArtaAdminButtons::addCancel();
		
		$this->render();
	}
	
	function getAllModules(){
		$mod=$this->getModel();
		$m=(array)$mod->getAllModules();
		$x=array();
		foreach($m as $v){
			$x[$v->id]=$v->title;
		}
		return $x;
	}

}
?>