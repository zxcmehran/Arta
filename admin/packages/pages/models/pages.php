<?php
if(!defined('ARTA_VALID')){die('No access');}
class PagesModelPages extends ArtaPackageModel{
	
	function getPagez(){
		$db=ArtaLoader::DB();
		$orderby=getVar('order_by','id','','string');

		$db->setQuery("SELECT SQL_CALC_FOUND_ROWS *, (SELECT COUNT(*) FROM #__pages_widgets as w WHERE w.pageid=p.id) AS widcount, (SELECT username FROM #__users AS u WHERE u.id=p.added_by) AS username FROM #__pages as p ".ArtaTagsHtml::FilterResult(null, array(), 'p').ArtaTagsHtml::SortResult('id', 'desc').' '.ArtaTagsHtml::LimitResult());
		$r=$db->loadObjectList();
		
		$db->setQuery('SELECT FOUND_ROWS()');
		$this->count=$db->loadResult();
		
		$u=ArtaLoader::User();
		
		foreach($r as $k=>&$v){
			$v->username= $v->username==null?'???':$v->username;
		}
		

		return $r;
	}
	
	function getAuthors(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT DISTINCT u.id as uid,u.username as uname FROM #__pages JOIN #__users as u WHERE u.id=added_by');
		$r= $db->loadObjectList();
		$x=array();
		foreach($r as $v){
			$x[$v->uid]=$v->uname;
		}
		return $x;
	}

}
?>