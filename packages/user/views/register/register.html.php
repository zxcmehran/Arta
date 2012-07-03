<?php 
if(!defined('ARTA_VALID')){die('No access');}
class UserViewRegister extends ArtaPackageView{

	function display(){
		$user=ArtaLoader::User();
		$user=$user->getCurrentUser();
		if($user->id > 0){
			redirect('', trans('YOU ARE ALREADY LOGGED IN'), 'warning');
		}
		$this->assign('token', ArtaSession::genToken());

		$enabled = $this->getSetting('allow_registering', 1);
		if($enabled==0){
			$this->setLayout('no_register');
			$this->render();
			return true;
		}
		if(getVar('layout')=='rules'){
			$this->assign('rules', $this->getSetting('rules_text', 'No Rules!'));
			$this->setLayout('rules');
			$this->setTitle(trans('RULES'));
			$this->addPath(trans('RULES'), ArtaURL::getURL());
			$this->render();
			return true;
		}
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT * FROM #__userfields WHERE fieldtype='misc' AND show_on_register=1");
		$misc=$db->loadObjectList();
		$this->assign('misc', $misc);
		$path=ArtaLoader::Pathway();
		$path->add(trans('REGISTERATION'), 'index.php?pack=user&view=register');
		$this->setTitle(trans('REGISTERATION'));
		$this->render();
	}
}
?>