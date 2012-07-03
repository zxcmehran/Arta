<?php
if(!defined('ARTA_VALID')){die('No access');}
class LinksViewEditgroup extends ArtaPackageView{
	
	function display(){
		ArtaAdminButtons::addSave(array('task'=>'saveGroup'));
		ArtaAdminButtons::addCancel();
		$this->setTitle(trans('ADD/EDIT LINK GROUP'));
		$m=$this->getModel();
		$this->assign('data',$m->getData());
		$this->render();
	}

}
?>