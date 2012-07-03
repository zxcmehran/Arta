<?php
if(!defined('ARTA_VALID')){die('No access');}
class ModulesViewList extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('MODULES MANAGER'));
		ArtaAdminTips::addTip(trans('MODULES MANAGER TIP'));
		ArtaAdminButtons::addNew(array('view'=>'edit'));
		ArtaAdminButtons::addEdit(array('view'=>'edit'));
		ArtaAdminButtons::addDelete();
		$this->render();
	}

}
?>