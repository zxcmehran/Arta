<?php
if(!defined('ARTA_VALID')){die('No access');}
class LanguageViewUnusable extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('UNUSABLE ITEMS'));
		ArtaAdminTips::addTip(trans('UNUSABLE ITEMS DESC'));
		if(ArtaUsergroup::getPerm('can_remove_unusable_translations','package','language')==false){
			ArtaError::show(403, trans('YOU CANNOT REMOVE UNUSABLE TRANSLATIONS'));
		}
		$m=$this->getModel();
		$data=$m->getData();
		ArtaAdminButtons::addDelete(array('task'=>'deleteUnusable'));
		$this->assign('data',$data);
		$this->render();
	}

}
?>