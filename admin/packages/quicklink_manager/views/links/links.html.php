<?php
if(!defined('ARTA_VALID')){die('No access');}
class Quicklink_managerViewLinks extends ArtaPackageView{
	
	function display(){
		$model=$this->getModel();
		$this->setTitle(trans('QUICKLINK MANAGEMENT'));
		ArtaAdminTips::addTip(trans('QUICKLINK DESC'));
		
		ArtaAdminButtons::addNew();
		ArtaAdminButtons::addEdit();
		ArtaAdminButtons::addDelete();
		
		$GLOBALS['_BUTTONS'][]=(ArtaTagsHtml::Window('<div class="mod_button"><img src="'.(Imageset('config.png')).'"><br>'.trans('CHANGE SETTINGS').'</div>', 'index.php?pack=config&view=edit&extype=module&extname=quicklink&client=admin&tmpl=package', trans('CHANGE SETTINGS')));
		
		
		$this->assign('linkz', $model->getLinkz());
		$this->assign('count', $model->count);
		$this->render();
	}

}
?>