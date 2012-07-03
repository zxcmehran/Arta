<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogModelComments{
	
		
	function getComment($cid, $postid){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT *, (SELECT COUNT(s.replyto) FROM #__blog_comments as s WHERE s.replyto=m.id GROUP BY s.replyto) AS `childs` FROM #__blog_comments as m WHERE m.postid='.$db->Quote($postid).' AND m.id='.$db->Quote($cid));
		return $db->loadObject();
	}
	
	function populateComments($data, &$result, $comments,$level=0, $accepts=null){
		$data=ArtaUtility::sortByChild($data, 'added_time');
		foreach($data as $k=>$v){
			$v->level=$level;
			if($accepts==null || $accepts==$v->language){
				$result[$v->language][]=$v;
				if(isset($comments[$v->id])){
					$this->populateComments($comments[$v->id]
					, $result, $comments, $level+1, $v->language);
				}
			}
		}
	}
	
	function getLanguages(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT id,title FROM #__languages WHERE client=\'site\'');
		return $db->loadObjectList();
	}


}
?>