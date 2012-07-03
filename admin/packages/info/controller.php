<?php 
if(!defined('ARTA_VALID')){die('No access');}
class InfoController extends ArtaPackageController{

	function display(){
		$view = $this->getView('info');
		$view->display();
	}
	
	function emptycron(){
		if(ArtaUserGroup::getPerm('can_clear_logs_and_alerts', 'package', 'info')==false){
			ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
		}
		$c=ArtaLoader::Cron();
		$c->cleanReports(0, false);
		redirect('index.php?pack=info&infotype=cronlogs');
	}
	
	function emptyalert(){
		if(ArtaUserGroup::getPerm('can_clear_logs_and_alerts', 'package', 'info')==false){
			ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
		}
		ArtaError::cleanAdminAlert(0, false);
		redirect('index.php?pack=info&infotype=adminalerts');
	}
	
}
?>