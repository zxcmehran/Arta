<?php 
if(!defined('ARTA_VALID')){die('No access');}
class UserViewLogin extends ArtaPackageView{

	function display(){
		$this->addPath(trans('login'), 'index.php?pack=user&view=login');
		$this->setTitle(trans('LOGIN'));
		$user=$this->getCurrentUser();
		
		if($user->id == true){
			redirect('index.php?pack=user&view=logout', trans('YOU ARE ALREADY LOGGED IN'), 'warning');
			
		}

		$this->render();
	}
}
?>