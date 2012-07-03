<?php
if(!defined('ARTA_VALID')){die('No access');}
class UserViewUserlist extends ArtaPackageView{
	
	function display(){
		
		$model=$this->getModel();
		$this->assign('model', $model);
		
		if(getVar('layout')!='online_info'){
			$this->setTitle(trans('USERS LIST'));
			
			ArtaAdminButtons::addNew();
			ArtaAdminButtons::addEdit();
			ArtaAdminButtons::addButton(trans('force logout'), Imageset('cancel.png'), array('onclick'=> "document.adminform.task.value='force_logout';document.adminform.submit();"));
			ArtaAdminButtons::addDelete();
			ArtaAdminButtons::addSetting('user');
			
			$this->assign('users', $model->getUsers());
			$this->render();
		}else{
			$u=ArtaLoader::User();
			$u=$u->getUser(getVar('uid', false, '','int'));
			if($u==false){
				ArtaError::show(404);
			}
			$info=$model->getUserInfo($u->id);
			if($info==false){
				ArtaError::show(500);
			}
			$this->setTitle(trans('ONLINE USER DETAILS'));
			$this->assign('user', $u);
			$this->assign('info', $info);
			$this->render('online_info');
		}
	}

}
?>