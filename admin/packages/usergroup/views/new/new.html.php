<?php
if(!defined('ARTA_VALID')){die('No access');}
class UsergroupViewNew extends ArtaPackageView{
	
	function display(){
		$ids=getVar('ids', false, '', 'array');

		if($ids==false){$title=trans('NEW Usergroup');}else{$title=trans('EDIT Usergroup');}

		$this->setTitle($title);
		
		$model=$this->getModel();
		$this->assign('usergroups', $model->getUsergroups($ids));
		$this->assign('perms', $model->getPerms($ids));
		
		ArtaAdminButtons::addSave();
		ArtaAdminButtons::addCancel();
		
		ArtaAdminTips::addTip(trans('USERGROUP AND PERMS DESC'));

		$this->render();
	}

}
?>