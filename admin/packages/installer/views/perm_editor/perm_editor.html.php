<?php
if(!defined('ARTA_VALID')){die('No access');}
class InstallerViewPerm_editor extends ArtaPackageView{
	
	function display(){
		$m=$this->getModel();
		$this->setTitle(trans('PERM EDITOR'));
		
		$this->assign('var', $m->getD());
		$this->render();
	}

}
?>