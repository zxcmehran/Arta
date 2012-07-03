<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/4/3 19:57 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class LinksModelPerm_editor extends ArtaPackageModel{
	
	function getD(){
		if(ArtaUsergroup::getPerm('can_addedit_links', 'package', 'links')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT LINKS'));
		}
		$i=ArtaRequest::getVar('pid',false,'','int');
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__links WHERE id='.$db->Quote($i));
		$r=$db->loadObject();
		if($r==null){
			ArtaError::show();
		}
		$r->type='link';
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