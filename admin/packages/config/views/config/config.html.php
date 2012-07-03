<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ConfigViewConfig extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('c_config'));
		if(ArtaUserGroup::getPerm('can_edit_settings_config', 'package', 'config')== false){
			ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
		}
		
		ArtaAdminTips::addTip(trans('SYSTEM CONFIG TIP'));
		
		ArtaAdminButtons::addSave(array('task'=>'config_save'));
		ArtaAdminButtons::addReset();
		ArtaAdminButtons::addCancel();
		$c=ArtaLoader::Config();
		$this->render();
	}

}
?>