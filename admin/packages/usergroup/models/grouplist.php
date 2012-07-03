<?php
if(!defined('ARTA_VALID')){die('No access');}
class UsergroupModelGrouplist{
	
	function getUsergroups(){
		$db=ArtaLoader::DB();
		$s=ArtaTagsHtml::SortResult('id');
		if(getVar('order_by')=='usercount'){
			$s='ORDER BY `id`';
			$sort=true;
		}
		$db->setQuery("SELECT * FROM #__usergroups ".ArtaTagsHtml::FilterResult().' '.$s.' '.ArtaTagsHtml::LimitResult());
		$r=$db->loadObjectList();
		
		$db->setQuery('SELECT usergroup,count(*) as c FROM #__users GROUP BY `usergroup`');
		$c=ArtaUtility::keybyChild($db->loadObjectList(), 'usergroup');
		
		foreach($r as $k=>$v){
			if(isset($c[$v->id])){
				$r[$k]->usercount=$c[$v->id]->c;
			}else{
				$r[$k]->usercount=0;
			}
		}
		if(isset($sort)){
			if(getVar('order_dir')=='asc'){$o=false;}else{$o=true;}
			$r=ArtaUtility::sortByChild($r, 'usercount', $o);
		}
		return $r;
	}

	function getUsergroup($id){
		if(is_array($id)){
			return array_map(array('UsergroupModelGrouplist', 'getUsergroup'), $id);
		}
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT * FROM #__usergroups WHERE id=".$db->Quote($id));
		$r=$db->loadObject();
		return $r;
	}

}
?>