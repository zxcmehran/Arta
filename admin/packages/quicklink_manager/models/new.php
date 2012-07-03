<?php
if(!defined('ARTA_VALID')){die('No access');}
class Quicklink_managerModelNew{
	
	function getData($id){
		if((int)$id>0){
			$db=ArtaLoader::DB();
			$db->setQuery("SELECT * FROM #__quicklink WHERE id=".$db->Quote($id));
			$return= $db->loadObject();
			if($return==null){
				ArtaError::show(404);
			}
		}else{
			$return=new stdClass;
			$return->id=0;
			$return->title='';
			$return->link='';
			$return->alt='';
			$return->img='file.png';
			$return->acckey='';
			$return->order=0;
		}
		return $return;

	}

}
?>