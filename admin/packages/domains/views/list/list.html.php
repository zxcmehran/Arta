<?php
if(!defined('ARTA_VALID')){die('No access');}
class DomainsViewList extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('DOMAINS MANAGEMENT'));
		ArtaAdminTips::addTip(trans('DOMAINS MANAGEMENT DESC'));
					
		ArtaAdminButtons::addNew(array('view'=>'edit'));
		ArtaAdminButtons::addEdit(array('view'=>'edit'));
		ArtaAdminButtons::addDelete();
		
		$m = $this->getModel();
		$this->assign('data', $m->getData());
		$this->assign('c', $m->c);
		
		$this->render();
	}

}
?>