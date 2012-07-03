<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ConfigViewDefaults extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('c_defaults'));
		
		if(ArtaUserGroup::getPerm('can_edit_settings_defaults', 'package', 'config', 'admin')== false){
			ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
		}
		
		ArtaAdminTips::addTip(trans('DEFAULT PARAMS TIP'));
				
		$model=$this->getModel();
		$data_M=$model->getMiscFields();
		$data_S=$model->getSettingFields();
		$language=ArtaLoader::Language();
		// but we need language of Admin CP
		$path=ARTAPATH_ADMIN;
		foreach($data_M as $v){
			$language->addtoNeed($v->extname, $v->extype, $path);
		}
		foreach($data_S as $v){
			$language->addtoNeed($v->extname, $v->extype, $path);
		}
		
		ArtaAdminButtons::addSave(array('task'=>'save_defaults'));
		ArtaAdminButtons::addReset();
		ArtaAdminButtons::addCancel('index.php?pack=config&view=group');
		
		$this->assign('m', $data_M);
		$this->assign('s', $data_S);
		$this->render();
	}

}
?>