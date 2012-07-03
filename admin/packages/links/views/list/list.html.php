<?php
if(!defined('ARTA_VALID')){die('No access');}
class LinksViewList extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('LINKS MANAGEMENT'));
		ArtaAdminTips::addTip(trans('LINKS MANAGEMENT DESC'));
		
		ArtaAdminButtons::addNew(array('view'=>'edit'));
		ArtaAdminButtons::addEdit(array('view'=>'edit'));
		ArtaAdminButtons::addDelete();
		ArtaAdminButtons::addButton(trans('SET DEFAULT'), Imageset('apply.png'), array('onclick'=>'
		if(AdminFormTools.hasChecked($$(\'.idcheck\'))==true){
			AdminFormTools.setMethod(\'post\');
			AdminFormTools.setVar(\'task\', \'setDefault\');
			AdminFormTools.submitForm();
		}else{
			alert(\''.trans('PLEASE SELECT A ROW').'\');
		}'));
		$this->render();
	}

}
?>