<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewCategory extends ArtaPackageView{
	
	function display(){
		$model=$this->getModel();
		$this->setTitle(trans('BLOG CAT MANAGER'));
		$this->assign('cats', $model->getData());
		$this->assign('c', $model->count);
		
		ArtaAdminButtons::addNew(array('view'=>'newcat'));
		ArtaAdminButtons::addEdit(array('view'=>'newcat'));
		ArtaAdminButtons::addDelete(array('task'=>'deleteCat'));
		ArtaAdminButtons::addSetting('blog');
		
		ArtaAdminTips::addTip(trans('BLOG CATS TIP'));
		$this->render();
	}

}
?>