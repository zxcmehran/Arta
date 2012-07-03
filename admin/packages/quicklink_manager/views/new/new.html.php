<?php
if(!defined('ARTA_VALID')){die('No access');}
class Quicklink_managerViewNew extends ArtaPackageView{
	
	function display(){
		$id=getVar('id', false, null, 'int');

		if($id==false){
			$title=trans('NEW LINK');
		}else{
			$title=trans('EDIT LINK');
		}
		$this->setTitle($title);
		
		$model=$this->getModel();
		
		$this->assign('data', $model->getData($id));
		
		ArtaAdminButtons::addSave();
		ArtaAdminButtons::addCancel();
		
		$this->render();
	}

}
?>