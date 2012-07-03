<?php 
if(!defined('ARTA_VALID')){die('No access');}
class UserViewRemind extends ArtaPackageView{

	function display(){
		$path=ArtaLoader::Pathway();
		$path->add(trans('REMIND REQUEST'), 'index.php?pack=user&view=remind');
		$this->setTitle(trans('REMIND REQUEST'));
		$this->render();
	}
}
?>