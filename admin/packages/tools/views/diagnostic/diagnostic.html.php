<?php
if(!defined('ARTA_VALID')){die('No access');}
class ToolsViewDiagnostic extends ArtaPackageView{
	
	function display(){
		ArtaAdminTips::addTip(trans('DIAGNOSTIC TIPS'));
		$this->setTitle(trans('WEBSITE TOOLS').' - '.trans('DIAGNOSTICS'));
		$this->render();
	}

}
?>