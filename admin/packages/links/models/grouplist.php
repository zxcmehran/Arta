<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/27 14:16 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class LinksModelGrouplist extends ArtaPackageModel{
	
	function getData(){
		$db=ArtaLoader::DB();
		if(getVar('order_by','id', '', 'string')=='count'){
			ArtaRequest::addVar('order_by', 'id');
			$x=1;
		}
		$db->setQuery('SELECT * FROM #__link_groups'.ArtaTagsHtml::SortResult('id', 'ASC'));
		$r=$db->loadObjectList();
		$r=@count($r) ? $r : array();
		foreach($r as $k=>$v){
			$r[$k]->count=$this->getCount($v->id);
		}
		if(@$x){
			$r=ArtaUtility::sortByChild($r, 'count', (strtolower(getVar('order_dir', 'asc', '', 'string'))=='desc'));
		}
		return $r;
	}
	
	function getCount($gr){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT COUNT(*) FROM #__links WHERE `group`='.$db->Quote($gr));
		$r=$db->loadResult();
		return $r;
	}
	
	function getTitle(){
		$id=getVar('id', 0, '', 'int');
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT `title` FROM #__link_groups WHERE `id`='.$db->Quote($id));
		$title=$db->loadResult();
		if($title==null){
			ArtaError::show();
		}
		return $title;
	}
	
	function getGroups(){
		$id=getVar('id', 0, '', 'int');
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__link_groups WHERE `id`!='.$db->Quote($id));
		$r=$db->loadObjectList();
		$x=array();
		foreach($r as $v){
			$x[$v->id]=$v->title;
		}
		return $x;
	}
	
}

?>