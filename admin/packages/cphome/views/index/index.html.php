<?php 
if(!defined('ARTA_VALID')){die('No access');}
class CphomeViewIndex extends ArtaPackageView{
	
	function display(){
		$model=$this->getModel();
		$this->setTitle(trans('CP HOME'));
		$this->assign('adminnote', $model->getAdminData());
		$this->assign('usernote', $model->getUserData());
		$this->assign('adminmodified', $model->getAdminModified());
		$this->assign('usermodified', $model->getUserModified());
		$this->assign('onlines', $model->getOnlineUsers());
		$this->assign('status', $model->getStatus());
		$this->assign('tip', $model->getTip());
		$this->render();
	}
	

}
?>
