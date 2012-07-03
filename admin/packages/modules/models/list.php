<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/27 14:16 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class ModulesModelList extends ArtaPackageModel{
	
	function getData(){
		$db=ArtaLoader::DB();
		if(getVar('order_by','order','','string')=='order'){
			$before='client,location';
		}else{
			$after='client,location';
		}
		if(getVar('order_by',false,'','string')=='location'){
			$before='';
			$after='client';
		}
		if(getVar('order_by',false,'','string')=='client'){
			$before='';
			$after='location';
		}
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__modules'.ArtaTagsHtml::FilterResult().ArtaTagsHtml::SortResult('order', 'ASC', @$before, @$after).ArtaTagsHtml::LimitResult());
		$res=ArtaUtility::keyByChild((array)$db->loadObjectList(), 'id');

		$db->setQuery('SELECT FOUND_ROWS();');
		$this->c=$db->loadResult();
		if(getVar('order_by','order','','string')=='order' && getVar('order_dir','asc','','string')=='asc'){
			$bycli=array('site'=>array(),'admin'=>array());
			foreach($res as $k=>$v){
				if($v->client=='site'){
					$bycli['site'][]=$v;
				}else{
					$bycli['admin'][]=$v;
				}
			}
			$site=array();
			$admin=array();
			foreach($bycli['site'] as $k=>$v){
				$site[$v->location][]=$v;
			}
			foreach($bycli['admin'] as $k=>$v){
				$admin[$v->location][]=$v;
			}
			foreach($admin as $r){
				foreach($r as $k=>$v){
					if((int)$v->order!==$k){
						$incorrect=true;
					}
				}
				if(isset($incorrect)){
					foreach($r as $k=>$v){
						$db->setQuery('UPDATE #__modules SET `order`='.$db->Quote($k).' WHERE id='.$db->Quote($v->id), array('order'));
						if($db->query()){
							$res[$v->id]->order=$k;
						}
					}	
				}
			}
			foreach($site as $r){
				foreach($r as $k=>$v){
					if((int)$v->order!==$k){
						$incorrect=true;
					}
				}
				if(isset($incorrect)){
					foreach($r as $k=>$v){
						$db->setQuery('UPDATE #__modules SET `order`='.$db->Quote($k).' WHERE id='.$db->Quote($v->id), array('order'));
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
		$db->setQuery('SELECT DISTINCT location FROM #__modules');
		$r=(array)$db->loadResultArray();
		$res=array();
		foreach($r as $v){
			$res[$v]=$v;
		}
		return $res;
	}
	
}

?>