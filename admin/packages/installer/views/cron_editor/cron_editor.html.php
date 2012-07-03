<?php
if(!defined('ARTA_VALID')){die('No access');}
class InstallerViewCron_editor extends ArtaPackageView{
	
	function display(){
		$m=$this->getModel();
		$this->setTitle(trans('CRON EDITOR'));

		$this->assign('var', $m->getD());
		$this->render();
	}

}
?>