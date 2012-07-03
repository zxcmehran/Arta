<?php 
if(!defined('ARTA_VALID')){die('No access');}
class InfoViewInfo extends ArtaPackageView{
	
	function display(){
		$m=$this->getModel();
		ArtaAdminTabs::addTab(trans('SYSTEM INFO'), 'index.php?pack=info');
		ArtaAdminTabs::addTab(trans('PHP INFO'), 'index.php?pack=info&infotype=php');
		ArtaAdminTabs::addTab(trans('DIRS INFO'), 'index.php?pack=info&infotype=dir');
		ArtaAdminTabs::addTab(trans('CRONLOGS'), 'index.php?pack=info&infotype=cronlogs');
		ArtaAdminTabs::addTab(trans('ADMINALERTS'), 'index.php?pack=info&infotype=adminalerts');
		if(getvar('infotype')=='cronlogs' || getvar('infotype')=='adminalerts'){
			ArtaAdminButtons::addButton(trans('EMPTY'), imageset('trash.png'), array('onclick'=>'document.adminform.submit()'));
		}
		$this->assign('data', $m->getInfo(getVar('infotype', 'sys', '', 'string')));
		$this->render();
	}

}
?>