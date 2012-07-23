<?php
/**
 * This file is used by blogpost_viewer widget.
 */
if(!defined('ARTA_VALID')){die('No access');}
class BlogModelPost extends ArtaPackageModel{
	
	function getPost($id, $die=true){
		$db=ArtaLoader::DB();
		if(ArtaUsergroup::getPerm('can_access_unpublished_posts', 'package', 'blog')){
			$db->setQuery('SELECT * FROM #__blogposts WHERE id='.$db->Quote($id));
		}else{
			$db->setQuery('SELECT * FROM #__blogposts WHERE id='.$db->Quote($id).' AND enabled=1 AND pub_time < '.$db->Quote(ArtaDate::toMySQL(time())).' AND (unpub_time > '.$db->Quote(ArtaDate::toMySQL(time())).' OR unpub_time is NULL OR unpub_time=\'0000-00-00 00:00:00\' OR unpub_time=\'1970-01-01 00:00:00\' OR unpub_time=\'\')');
		}
		$v=$db->loadObject();
		if($v==null){
			ArtaError::show(404, trans('POST NOT FOUND'));
		}
		
		$v->_tags=$v->tags;
		
		$plugin=ArtaLoader::Plugin();
		$plugin->trigger('onPrepareContent', array(&$v, 'blogpost'));
				
		// Assign Blogid
		$b=$this->getBlogID($v->blogid);
		$v->blogid=$b;
		
		
		$perms=array();
		$x=$b;
		$perms[]=$b->accmask;
		while(isset($GLOBALS['CACHE']['blog.categories'][$x->parent])){
			$x=$GLOBALS['CACHE']['blog.categories'][$x->parent];
			$perms[]=$x->accmask;
		}
		
		krsort($perms);
		$perms=ArtaUsergroup::processAccessMask($perms);

		if(ArtaUsergroup::processDenied($v->denied)==false || ArtaUsergroup::processDenied($perms)==false){
			if($die==true){
				ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
			}else{
				return 'NOT_AUTHORIZED';
			}
		}
		
		$v->added_by_id=$v->added_by;
		$v->added_by=$this->getAuthor($v->added_by);
		if($v->mod_by!==null){
			$v->mod_by_id=$v->mod_by;
			$v->mod_by=$this->getAuthor($v->mod_by);
		}
		$v->attachments=$this->getAttachments($v->id);
		$v->langs=$this->getLangsAvailable($v->id);

		
		if((string)$v->tags!==''){
			$t=ArtaLoader::Template();
			$t->keywords .=', '.$v->tags;
                        $t->description = '';
		}
		
		$db->setQuery('UPDATE #__blogposts SET hits= hits+1 WHERE id='.$db->Quote($id), array('hits'));
		$db->query();
		
		return $v;
		
	}
	
	function getBlogID($b){
		if($b!=false && $b!=0){
			if(!isset($GLOBALS['CACHE']['blog.categories'])){
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__blogcategories');
				$GLOBALS['CACHE']['blog.categories']=ArtaUtility::keyByChild((array)$db->loadObjectlist(), 'id');
			}
			
			if(!isset($GLOBALS['CACHE']['blog.categories'][$b])){
				$GLOBALS['CACHE']['blog.categories'][$b]=null;
			}
			$plugin=ArtaLoader::Plugin();
            $c=@$GLOBALS['CACHE']['blog.categories'][$b];
			$plugin->trigger( 'onPrepareContent', array(&$c, 'blogcat') );
			return $c;
		}else{
			return false;
		}
	}
		
	function getAuthor($id){
		if(!isset($this->{'user_'.$id})){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id,username,name FROM #__users WHERE id ='.$db->Quote($id));
			$u=$db->loadObject();
					
			if($u!==null){
				$t=$this->getSetting('show_first_last_name', '0');
				if($t==0){
					$this->{'user_'.$id}= $u->username;
				}else{
					$this->{'user_'.$id}= $u->name;
				}
			}else{
				$this->{'user_'.$id}= '???';
			}
		}
		return $this->{'user_'.$id};
	}

	
	function getAttachments($id){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT name, url FROM #__blog_attachments WHERE postid='.$db->Quote($id));
		$r=(array)$db->loadObjectList('name');
		foreach($r as &$v){
			$v=$v->url;
		}
		return $r;
	}
	
	
	function getComments($postid, $canacc){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__blog_comments WHERE postid='.$db->Quote($postid).' ORDER BY added_time');
		$comments=$db->loadObjectList();
		$comments=ArtaUtility::keyByChild($comments, 'replyto', true);
		foreach($comments as &$com){
			if(!is_array($com)){
				$com=array($com);
			}else{
				$com=ArtaUtility::sortByChild($com,'id');
			}
		}
		
		$result=array();
		if(isset($comments[0])){
			$this->populateComments($canacc,$comments[0], $result, $comments);
		}
		
		return $result;
	}
	
	function getComment($cid, $postid){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT *, (SELECT COUNT(s.replyto) FROM #__blog_comments as s WHERE s.replyto=m.id GROUP BY s.replyto) AS `childs` FROM #__blog_comments as m WHERE m.postid='.$db->Quote($postid).' AND m.id='.$db->Quote($cid));
		return $db->loadObject();
	}
	
	function populateComments($canacc, $data, &$result, $comments,$level=0, $accepts=null){
		$data=ArtaUtility::sortByChild($data, 'id');
		$cu=$this->getCurrentUser();
		foreach($data as $k=>$v){
			$v->level=$level;
			if(($accepts==null || $accepts==$v->language) && ($v->published==true || ($v->published==false && $canacc==true) || $v->added_by==$cu->id)){
				$result[]=$v;
				if(isset($comments[$v->id])){
					$this->populateComments($canacc, $comments[$v->id]
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
	
	function getLangsAvailable($id){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT DISTINCT (SELECT l.title FROM #__languages as l WHERE id=t.language) FROM #__languages_translations as t WHERE `group`=\'blogpost\' AND enabled=1 AND row_id='.$db->Quote($id));
		return $db->loadResultArray();
	}
	
	
}
?>