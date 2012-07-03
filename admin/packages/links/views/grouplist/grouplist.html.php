<?php
if(!defined('ARTA_VALID')){die('No access');}
class LinksViewGrouplist extends ArtaPackageView{
	
	function display(){
		if(getVar('layout', false,'','string')=='deleter'){
			$this->setTitle(trans('WHAT TO DO BEFORE'));
			
			ArtaAdminTips::addTip(trans('LINK GROUPS DELETE DESC'));
			
			ArtaAdminButtons::addDelete(array('task'=>'deleteGroup'));
			ArtaAdminButtons::addCancel();
			
			$this->render('deleter');
		}else{
			$this->setTitle(trans('LINK GROUPS MANAGEMENT'));
						
			ArtaAdminButtons::addNew(array('view'=>'editgroup'));
			ArtaAdminButtons::addEdit(array('view'=>'editgroup'));
			ArtaAdminButtons::addDelete(array('task'=>'deleteGroup'));
			
			$this->render();
		}
	}

}
?>