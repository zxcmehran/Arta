<?php
if(!defined('ARTA_VALID')){die('No access');}
class UserViewModeration extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('WAIT FOR MODERATION'));
		ArtaAdminTips::addTip(trans('WAIT FOR MODERATION DESC'));
		
		$model=$this->getModel();
		
		ArtaAdminButtons::addEdit();
		ArtaAdminButtons::addButton(trans('ACTIVATE'), Imageset('switch.png'), 
			array('onclick'=> 
				"document.adminform.task.value='activate';document.adminform.submit();"));
		ArtaAdminButtons::addDelete();
		ArtaAdminButtons::addSetting('user');
		
		$this->assign('users', $model->getUsers());
		$this->assign('model', $model);
		$this->render();
	}

}
?>