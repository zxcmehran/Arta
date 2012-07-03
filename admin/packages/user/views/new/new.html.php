<?php
if(!defined('ARTA_VALID')){die('No access');}
class UserViewNew extends ArtaPackageView{
	
	function display(){
		$ids=getVar('ids', false,'','array');
		
		if($ids==false){$title=trans('NEW USER');}else{$title=trans('EDIT USER');}

		$this->setTitle($title);
		
		$model=$this->getModel();
		$this->assign('users', $model->getUsers($ids));
		$this->assign('settings', $model->getFields('setting'));
		$this->assign('misc', $model->getFields('misc'));
		ArtaAdminButtons::addSave('', "
			if($('base').value !== $('verify').value){
				alert('".JSValue(trans('PASSWORD VERIFY FAILED'))."');
				return false;
			}
			if($('uname').value.indexOf(' ')!=-1){
				alert('".JSValue(trans('DO NOT USE SPACE IN UNAME'))."');
				return false;
			}
			if($('base').value !==''){
				$('base').value=Crypt.MD5($('base').value);
				$('verify').value=Crypt.MD5($('verify').value);
			}
		");
		ArtaAdminButtons::addCancel(getVar('moderation',false)==true?'index.php?pack=user&view=moderation':'index.php?pack=user');

		$this->render();
	}

}
?>