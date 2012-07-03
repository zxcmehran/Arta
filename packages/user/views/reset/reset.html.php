<?php 
if(!defined('ARTA_VALID')){die('No access');}
class UserViewReset extends ArtaPackageView{

	function display(){

		$vars=ArtaRequest::getVars('default', 'object');
		if(!isset($vars->uid) || !isset($vars->reset_code)){
			redirect('index.php?pack=user&view=remind');
		}
		$path=ArtaLoader::Pathway();
		$path->add(trans('RESET PASSWORD'), 'index.php?pack=user&view=reset');
		$this->assign('uid', (string)$vars->uid);
		$this->assign('reset_code', (string)$vars->reset_code);
		$this->setTitle(trans('RESET PASSWORD'));
		$this->render();
	}
}
?>