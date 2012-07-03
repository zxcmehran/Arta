<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ConfigViewEdit extends ArtaPackageView{
	
	function display(){
				
		$type=getVar('extype', false, '', 'string');
		$id=getVar('extname', false, '', 'string');
		$client=getVar('client', false, '', 'string');
		$tmpl=getVar('tmpl', false, '', 'string');

		if($type == false){
			redirect('index.php?pack=config&view=group');
		}

		if($id == false){
			redirect('index.php?pack=config&view=group');
		}

		$allowed=array('package', 'module', 'plugin', 'template', 'cron');
		if(in_array($type, $allowed) == false){
			redirect('index.php?pack=config&view=group');
		}
		if($type !== 'cron' && $client==false){
			redirect('index.php?pack=config&view=group');
		}
		
		if(ArtaUserGroup::getPerm('can_edit_settings_'.$type, 'package', 'config')== false){
			ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
		}
		
		$model=$this->getModel();
		eval('$e=$model->get'.ucfirst($type).'($id);');

		$this->assign('title', $e->title);
		$this->setTitle(sprintf(trans('CONFIGURATION OF_'),$e->title));
		if($type=='cron'){
			$client='site';
		}
		
		ArtaAdminButtons::addSave();
		ArtaAdminButtons::addReset();
		ArtaAdminButtons::addCancel($tmpl=='package'?false:'index.php?pack=config&view=extension&extype='.$type);
		
		$data=$model->getSettings($id, $type, $client);
		
		$language=ArtaLoader::Language();
		// but we need language of Admin CP
		$path=ARTAPATH_ADMIN;
		$language->addtoNeed($id, $type, $path);

		$this->assign('data', $data);
		$this->render();
	}

}
?>