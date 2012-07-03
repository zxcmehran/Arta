<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/27 14:16 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class DomainsModelList extends ArtaPackageModel{
	
	function getData(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__domains '.ArtaTagsHtml::SortResult('id', 'DESC', @$before, @$after).ArtaTagsHtml::LimitResult());
		$r=(array)$db->loadObjectList('id');
		
		if(count($r)==0){
			$this->c = 0;
			return $r;
		}
		
		$db->setQuery('SELECT FOUND_ROWS();');
		$this->c=$db->loadResult();
		
		return $r;
	}
		
}

?>