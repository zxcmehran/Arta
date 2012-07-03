<?php
if(!defined('ARTA_VALID')){die('No access');}
class UsergroupViewGrouplist extends ArtaPackageView{
	
	function display(){
				
		$model=$this->getModel();
		if(getVar('layout', 'default')=='default'){
			ArtaAdminButtons::addNew();
			ArtaAdminButtons::addEdit();
			
			$this->assign('usergroups', $model->getUsergroups());
			$this->setTitle(trans('USERGROUPS MANAGEMENT'));
		}else{
			$this->setTitle(trans('DELETE'));
		}
		ArtaAdminButtons::addDelete('',getVar('layout', 'default')=='default'?'':
		'if(confirm(\''.JSValue(trans('WARNING DELETE USERS'), true).'\')==false){
			return false;
		}');
		
		ArtaAdminTips::addTip(trans('USERGROUPS DESC'));
		
		$this->render();
	}

}
?>