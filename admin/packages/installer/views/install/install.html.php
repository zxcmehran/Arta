<?php
if(!defined('ARTA_VALID')){die('No access');}
class InstallerViewInstall extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('EXTENSIONS MANAGEMENT'));
		
		ArtaAdminButtons::addButton(trans('UNINSTALL'), Imageset('delete.png'), array('onclick'=>"
		
		if(AdminFormTools.hasChecked($$('.idcheck'))==true){
			if(confirm('".trans('ARE YOU SURE TO UNINSTALL THIS EXTENSION')."\\n'+$('curid').innerHTML)){
				AdminFormTools.setMethod('post','adminform1');
				AdminFormTools.submitForm('adminform1');
			}
		}else{
			alert('".trans('PLEASE SELECT A ROW')."');
		}
		"));
		
		ArtaAdminTips::addTip(trans('EXT MANAGER TIP'));
		$this->render();
	}

}
?>