<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ConfigViewExtension extends ArtaPackageView{
	
	function display(){
		$type=getVar('extype', false, '', 'string');
		if($type == false){
			redirect('index.php?pack=config&view=group');
		}
		$allowed=array('package', 'module', 'plugin', 'template', 'cron');
		if(in_array($type, $allowed) == false){
			redirect('index.php?pack=config&view=group');
		}
				
		$this->setTitle(trans('c_'.$type));
		
		$model=$this->getModel();
		eval('$data=$model->get'.ucfirst($type).'s();');

		$this->assign('data', $data);
		$this->render();
	}

}
?>