<?php 
if(!defined('ARTA_VALID')){die('No access');}
class LoginViewLogin extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('LOGIN TO ADMIN CONSOLE'));
		$user = ArtaLoader::User();
		$user=$user->getCurrentUser();
		if($user->id == true){
			redirect('index.php', trans('YOU ARE ALREADY LOGGED IN'), 'warning');
		}
		$this->render();
	}

}
?>