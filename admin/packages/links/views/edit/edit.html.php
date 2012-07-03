<?php
if(!defined('ARTA_VALID')){die('No access');}
class LinksViewEdit extends ArtaPackageView{
	
	function display(){
		ArtaAdminButtons::addSave();
		ArtaAdminButtons::addCancel();
		
		$m=$this->getModel();
		$d=$m->getData();
		$this->assign('data',$d);
		if((int)$d->id==0){
			$this->setTitle(trans('ADD LINK'));
		}else{
			$this->setTitle(trans('EDIT LINK').': '.htmlspecialchars($d->title));
		}
		$this->assign('groups',$m->getGroups());
		$this->assign('ug',$m->getUGs());
		$this->render();
	}

}
?>