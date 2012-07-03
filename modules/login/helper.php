<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ModuleLoginHelper extends ArtaModuleHelper{
	function getState(){
		$u = ArtaLoader::User();
		$u= $u->getCurrentUser();
		if($u->id > 0){
			return true;
		}else{
			return false;
		}
	}


}

?>