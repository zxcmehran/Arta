<?php
if(!defined('ARTA_VALID')){die('No access');}
class PagesViewPages extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('PAGES MANAGEMENT'));
		
		ArtaAdminTips::addTip(trans('PAGES AND WIDGETS DESC'));
		
		$model=$this->getModel();
		
		ArtaAdminButtons::addNew();
		ArtaAdminButtons::addEdit();
		ArtaAdminButtons::addDelete();
		ArtaAdminButtons::addSetting('pages');
		
		$this->assign('pagez', $model->getPagez());
		$this->assign('count', $model->count);
		$this->assign('users', $model->getAuthors());
		$this->render();
	}

}
?>