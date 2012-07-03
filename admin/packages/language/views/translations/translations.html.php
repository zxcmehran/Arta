<?php
if(!defined('ARTA_VALID')){die('No access');}
class LanguageViewTranslations extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('LANGUAGE TRANSLATIONS MANAGER'));
		ArtaAdminTips::addTip(trans('CONTENT ITEMS DESC'));
		$m=$this->getModel();
		$data=$m->getData();
		$count=$m->getCount();
		$controls=$m->getControls();
		$group=$m->getGroups();
		$lang=$m->getLanguages();
		ArtaAdminButtons::addEdit();
		ArtaAdminButtons::addDelete();
		$this->assign('data',$data);
		$this->assign('count',$count);
		$this->assign('controls',$controls);
		$this->assign('group',$group);
		$this->assign('lang',$lang);
		@$this->row_id=$m->row_id;
		@$this->row_title=$m->row_title;
		$this->render();
	}

}
?>