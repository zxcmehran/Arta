<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/27 14:16 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class LinksModelEditgroup extends ArtaPackageModel{
	
	function getData(){
		if(ArtaUsergroup::getPerm('can_addedit_link_groups', 'package', 'links')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT LINKS'));
		}
		$id=getVar('id',false, '', 'int');
		if($id>0){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__link_groups WHERE id='.$db->Quote($id));
			$r=$db->loadObject();
			if($r==null){
				ArtaError::show();
			}
		}else{
			$r=new stdClass;
			$r->id=false;
			$r->title='';
		}
		return $r;
	}
	
}

?>