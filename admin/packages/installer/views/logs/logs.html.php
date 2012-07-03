<?php
if(!defined('ARTA_VALID')){die('No access');}
class InstallerViewLogs extends ArtaPackageView{
	
	function display(){
		$m=$this->getModel();
		$this->setTitle(trans('INSTALLATION LOGS'));

		$this->assign('logs', $m->getLogs());
		$this->assign('c', $m->count);
		$this->render();
	}

}
?>