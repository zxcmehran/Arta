<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/4/3 19:57 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class ModulesModelPerm_editor extends ArtaPackageModel{
	
	function getD(){
		if(ArtaUsergroup::getPerm('can_addedit_mods', 'package', 'modules')==false){
			ArtaError::show(403);
		}
		$i=ArtaRequest::getVar('pid',0,'','string');
		$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__modules WHERE id='.$db->Quote($i));
		$r=$db->loadObject();
		if($r==null){
			ArtaError::show(404);
		}
		$r->type='module';
		if(strlen($r->denied)&&$r->denied{0}=='-'){
			$r->denied=substr($r->denied, 1);
			$r->denied_type=1;
		}else{
			$r->denied_type=0;
		}
		return $r;
	}
	
}

?>