<?php
if(!defined('ARTA_VALID')){die('No access');}
class LanguageViewMissing extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('MISSING LANGUAGE FILES'));
		ArtaAdminTips::addTip(trans('MISSING LANGUAGE FILES DESC'));

		$m=$this->getModel();
		$data=$m->getData();

		$this->assign('site',$data['site']);
		$this->assign('admin',$data['admin']);
		$this->render();
	}

}
?>