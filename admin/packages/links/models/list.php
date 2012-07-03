<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/27 14:16 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class LinksModelList extends ArtaPackageModel{
	
	function getData(){
		$db=ArtaLoader::DB();
		if(getVar('order_by','order','','string')=='order'){
			$before='`group`';
		}else{
			$after='`group`';
		}
		if(getVar('order_by',false,'','string')=='group'){
			$before=null;
			$after=null;
		}
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__links'.ArtaTagsHtml::FilterResult().ArtaTagsHtml::SortResult('order', 'ASC', @$before, @$after).ArtaTagsHtml::LimitResult());
		$r=$db->loadObjectList();
		if($r==null){
			$this->c=0;
			return null;
		}
		$res=ArtaUtility::keyByChild($r, 'id');
		$db->setQuery('SELECT FOUND_ROWS();');
		$this->c=$db->loadResult();
		
		// Re-arrange items in best order
		// if you have this orders : 0,1,2,5,6,7,8,16,41,42,43
		// new order will be : 0,1,2,3,4,5,6,7,8,9,10
		if(getVar('order_by','order','','string')=='order' && getVar('order_dir','asc','','string')=='asc'){
			
			$xx=array();
			foreach($res as $k=>$v){
				$xx[$v->group][]=$v;
			}
			foreach($xx as $r){
				foreach($r as $k=>$v){
					if((int)$v->order!==$k){
						$incorrect=true;
					}
				}
				if(isset($incorrect)){
					foreach($r as $k=>$v){
						$db->setQuery('UPDATE #__links SET `order`='.$db->Quote($k).' WHERE id='.$db->Quote($v->id), array('order'));
						if($db->query()){
							$res[$v->id]->order=$k;
						}
					}	
				}
			}
		}
		$x=array();
		foreach($res as $v){
			$x[]=$v;
		}
		return $x;
	}
	
	function getLocations(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__link_groups');
		$r=$db->loadObjectList();
		$res=array();
		foreach($r as $v){
			$res[$v->id]=$v->title;
		}
		return $res;
	}
	
}

?>