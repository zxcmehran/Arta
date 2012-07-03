<?php
if(!defined('ARTA_VALID')){die('No access');}
class UserModelModeration{
	
	function getUsers(){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT SQL_CALC_FOUND_ROWS * FROM #__users ".ArtaTagsHtml::FilterResult('activation='.$db->Quote('MODERATOR'), array('lastvisit_date'=> '>', 'register_date'=> '>')).' '.ArtaTagsHtml::SortResult('id').' '.ArtaTagsHtml::LimitResult());
		$r=(array)$db->loadObjectList();
		
		$db->setQuery('SELECT FOUND_ROWS()');
		$this->count=$db->loadResult();
		
		return $r;
	}

	function getUsersCount(){
		return $this->count;
	}

}
?>