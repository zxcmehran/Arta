<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogModelPosts extends ArtaPackageModel{
	
	function getPosts(){
		if(isset($_REQUEST['where']['com']) && @$_REQUEST['where']['com']==2){
			$wid=$_REQUEST['where']['com'];
			unset($_REQUEST['where']['com']);
			$where=' HAVING comments = 0';
			
		}elseif(isset($_REQUEST['where']['com']) && @$_REQUEST['where']['com']==1){
			$wid=$_REQUEST['where']['com'];
			unset($_REQUEST['where']['com']);
			$where=' HAVING newcomments>0';
			
		}elseif(isset($_REQUEST['where']['com'])){
			$wid=$_REQUEST['where']['com'];
			unset($_REQUEST['where']['com']);
			$where=' HAVING comments>0';
		}else{
			$where='';
		}
		$db=Artaloader::DB();
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS *, 
		(SELECT cat.title FROM #__blogcategories AS cat WHERE cat.id=bp.blogid) AS cattitle, 
		(SELECT username FROM #__users AS u WHERE u.id=bp.added_by) AS username, 
		(SELECT COUNT(*) FROM #__blog_comments as bc WHERE bc.postid=bp.id) AS comments, 
		(SELECT COUNT(*) FROM #__blog_attachments as ba WHERE ba.postid=bp.id) AS attach, 
		(SELECT COUNT(*) FROM #__blog_comments as bc WHERE bc.postid=bp.id AND bc.published=0) AS newcomments 
		FROM #__blogposts AS bp'.ArtaTagsHtml::FilterResult(null, array('added_time'=>'>', 'title'=>'LIKE'), 'bp').$where.' '.ArtaTagsHtml::SortResult('added_time', 'desc').ArtaTagsHtml::LimitResult());
		
		if(isset($wid)){
			$_REQUEST['where']['com']=$wid;
		}
		
		$r=$db->loadObjectList();
		$db->setQuery('SELECT FOUND_ROWS()');
		$this->C=$db->loadResult();
		

		// Blend these SQLs in one.
		$db->setQuery('SELECT id,username FROM #__users WHERE id IN (SELECT DISTINCT added_by FROM #__blogposts)');
		$ul=(array)$db->loadObjectList();
		
		$u=array();
		foreach($ul as $v){
			$u[$v->id]=$v->username;
		}

		$this->u=$u;

		foreach((array)$r as $k=>$v){
			$r[$k]->username= $r[$k]->username==null ? '???' : $r[$k]->username;

			$r[$k]->added_time=strtotime($r[$k]->added_time);
			
			if((string)$v->unpub_time==''||
				(string)$v->unpub_time=='0000-00-00 00:00:00' ||
				(string)$v->unpub_time=='1970-01-01 00:00:00'){
				$r[$k]->unpub_time=trans('never');
			}
			
			if((string)$v->mod_time==''||
				(string)$v->mod_time=='0000-00-00 00:00:00' ||
				(string)$v->mod_time=='1970-01-01 00:00:00'){
				$r[$k]->mod_time=trans('never');
			}
		}
		
	
		return $r;
	}
	


}
?>