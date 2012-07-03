<?php 
if(!defined('ARTA_VALID')){die('No access');}
class UserViewLogout extends ArtaPackageView{

	function display(){
		$this->addPath(trans('logout'), 'index.php?pack=user&view=logout');
		$this->setTitle(trans('LOGOUT'));
		$user=$this->getCurrentUser();
		if($user->id == false){
			redirect('index.php?pack=user&view=login', trans('YOU ARE ALREADY LOGGED OUT'), 'warning');
		}

		$this->render();
	}
}
?>