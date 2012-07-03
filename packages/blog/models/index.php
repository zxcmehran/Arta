<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogModelIndex extends ArtaPackageModel{
	
	var $author_cache=array();
	
	function getCategories($p=0, $level=0){
		$j=$level;
		if($level!==0){
			
			$level='';
			$i=0;
			while($j>$i){
				$level.='..';
				$i++;
			}
			$level.=' ';
		}else{
			$level='';
		}

		if(!isset($GLOBALS['CACHE']['blog.new_categories'])){
			$r=ArtaCache::getData('blog','new_categories');
			if($p==0&&$level==0&&$r!==false){
				$GLOBALS['CACHE']['blog.new_categories']= $r;
			}else{
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__blogcategories');
				$GLOBALS['CACHE']['blog.new_categories']=
					ArtaUtility::keyByChild((array)$db->loadObjectList(), 'parent', true);
				ArtaCache::putData('blog','new_categories', $GLOBALS['CACHE']['blog.new_categories']);
			}
		}

		$r=@$GLOBALS['CACHE']['blog.new_categories'][$p];
		if(is_object($r)){
			$r=array($r);
		}
		$r=@count($r) ? $r : array();
		$plugin=ArtaLoader::Plugin();
		foreach($r as &$v){
			$plugin->trigger( 'onPrepareContent', array(&$v, 'blogcat') );
			$v->title=$level.$v->title;
		}
		$r=ArtaUtility::keyByChild($r, 'id');
		$r=ArtaUtility::SortByChild($r, 'parent');
		
		$p=1;
		foreach($r as $k=>$v){
			$c=$this->getCategories($v->id, $j+1);
			$x=count(array_slice($r, 0, $p))+count($c)+1;
			$r=array_merge(array_slice($r, 0, $p),$c,array_slice($r, $p));
			$p=$x;
		}
			
		return $r;
	}
	
	function getPostCount($bid){
		if(!isset($GLOBALS['CACHE']['blog.postcount'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT blogid, COUNT(*) AS `c` FROM #__blogposts GROUP BY blogid');
			$r=(array)$db->loadObjectList();
			$GLOBALS['CACHE']['blog.postcount']=array();
			foreach($r as $v){
				$GLOBALS['CACHE']['blog.postcount'][$v->blogid]=$v->c;
			}
		}
		return @(int)$GLOBALS['CACHE']['blog.postcount'][$bid];
	}
	
	function getItems(){
		$blogid = getVar('blogid',false, '', 'int');
		
		$db=ArtaLoader::DB();
		$b=$this->getBlogID($blogid);
		$u=$this->getCurrentUser();
		
		if($blogid!=false){
			if($b==false){
				ArtaError::show(404, trans('CATEGORY NOT FOUND'));
			}
			$suffix=' AND bp.blogid='.$db->Quote($b->id);
		}else{
			$suffix='';
		}
		
		$t=getVar('tagname','','','string');
		if(trim($t)!==''){ // NOTE: Tags shouldn't be translated!
			$t=trim(strtolower($db->getEscaped(preg_quote($t))));
			$suffix.=' AND LOWER(bp.tags) REGEXP \'(^|,) *'.$t.' *($|,)\'';
		}
		
		$c=ArtaLoader::Config();
		$ll=$c->list_limit;
		$c->list_limit=$this->getSetting('lastposts_list_limit','10');
		if($c->list_limit==0){
			$c->list_limit=$ll;
		}
		
		if(ArtaUserGroup::getPerm('can_access_unpublished_comments', 'package', 'blog')==false){
			$en=' AND `published`=1';
		}else{
			$en='';
		}
		
		$cols=',(SELECT COUNT(*) FROM #__blog_attachments as ba WHERE ba.postid=bp.id) AS attachments
		,(SELECT COUNT(*) FROM #__blog_comments as bc WHERE bc.postid=bp.id'.$en.') AS comments
		';
		
		
		if($this->getSetting('show_blockmsg_for_denied_users', '0')==false){
			$bidam=' AND bp.denied NOT REGEXP \'(^|,) *'.$u->usergroup.' *(,|$)\' ';
			$den= $this->getDeniedBlogids();
			if($den!=array()){
			//	$GLOBALS['CACHE']['blog.denied_blogids']=$den;
				$bidam.=' AND bp.`blogid` NOT IN ('.implode(',', $den).') ';
			}
		}
		if(!isset($bidam)){
			$bidam='';
		}
		
		// Making query
		if(ArtaUsergroup::getPerm('can_access_unpublished_posts', 'package', 'blog')){
			$db->setQuery('SELECT SQL_CALC_FOUND_ROWS *'.$cols.' FROM #__blogposts as bp WHERE '.
					'1=1'.$suffix.$bidam.
				ArtaTagsHtml::SortResult('added_time', 'desc').
				ArtaTagsHtml::LimitResult()
			);
		}else{
			$db->setQuery('SELECT SQL_CALC_FOUND_ROWS *'.$cols.' FROM #__blogposts as bp WHERE '.
					'bp.enabled=1 AND bp.pub_time < '.$db->Quote(ArtaDate::toMySQL(time())).' AND (bp.unpub_time > '.$db->Quote(ArtaDate::toMySQL(time())).
					' OR bp.unpub_time is NULL OR bp.unpub_time=\'0000-00-00 00:00:00\' OR bp.unpub_time=\'1970-01-01 00:00:00\' OR bp.unpub_time=\'\')'.
					$suffix.$bidam.
				ArtaTagsHtml::SortResult('added_time', 'desc').
				ArtaTagsHtml::LimitResult()
			);
		}
		
		
		$r=(array)$db->loadObjectList();
		
		
		$db->setQuery('SELECT FOUND_ROWS()');
		$this->count=(int)($db->loadResult());
		
		$plugin=ArtaLoader::Plugin();
		
		foreach($r as $v){
			$plugin->trigger('onPrepareContent', array(&$v, 'blogpost'));
		}
		

		foreach($r as $k=>$v){
			$perms=array();
			$b=$this->getBlogID($v->blogid);
			$r[$k]->blogid=$b;
			
			$plugin->trigger('onPrepareContent', array(&$r[$k]->blogid, 'blogcat'));
			
		/*	if($this->getSetting('show_blockmsg_for_denied_users', '0')){
				$x=$b;
				$perms[]=$b->accmask;
				while(isset($GLOBALS['CACHE']['blog.categories'][$x->parent])){
					$x=$GLOBALS['CACHE']['blog.categories'][$x->parent];
					$perms[]=$x->accmask;
				}
				
				krsort($perms);
				$perms=ArtaUsergroup::processAccessMask($perms);
				
				
				if(ArtaUsergroup::processDenied($v->denied)==false || ArtaUsergroup::processDenied($perms)==false){
					
					$r[$k]->introcontent='<b>'.trans('DENIED_BLOCKMSG').'</b>';	
					
				}
			}*/

		}
		
		foreach($r as $k=>$v){
			$r[$k]->added_by_id=$r[$k]->added_by;
			$r[$k]->added_by=$this->getAuthor($v->added_by);
			$r[$k]->langs=$this->getLangsAvailable($v->id);
		}
		
		return $r;
	}
	
	function getDeniedBlogids(){
		$u=$this->getCurrentUser();
		if(!isset($GLOBALS['CACHE']['blog.denied_blogids'])){ // must fill the var
			if(ArtaCache::isUsable('blog', 'denied_blogids')){// get cache
				$GLOBALS['CACHE']['blog.denied_blogids']=ArtaCache::getData('blog', 'denied_blogids');
			}
			if(!isset($GLOBALS['CACHE']['blog.denied_blogids'][$u->usergroup])){ // make then set cache for later use
				if(!isset($GLOBALS['CACHE']['blog.categories'])){
					$this->getBlogID(1); // just to fill the cache array
				}
				$bids=$GLOBALS['CACHE']['blog.categories'];
				$den=array();
				foreach($bids as &$bid){
					$x=$bid;
					$bid->am=array($bid->accmask);
					while(@$bids[$x->parent]!=null){
						$x=$bids[$x->parent];
						$bid->am[]=$x->accmask;
					}
					$bid->am=ArtaUsergroup::processAccessMask($bid->am);
					if(in_array($u->usergroup, explode(',',$bid->am))){
						$den[]=$bid->id;
					}
				}
				$GLOBALS['CACHE']['blog.denied_blogids'][$u->usergroup]=$den;
				ArtaCache::putData('blog', 'denied_blogids',$GLOBALS['CACHE']['blog.denied_blogids']);
			}
		}
		$den=$GLOBALS['CACHE']['blog.denied_blogids'][$u->usergroup];
		return $den;
	}
	
	function _getItems(){
		$blogid = getVar('blogid',false, '', 'int');
		
		$db=ArtaLoader::DB();
		$b=$this->getBlogID($blogid);
		
		if($blogid!==false){		
			if($b==false){
				ArtaError::show(404, trans('CATEGORY NOT FOUND'));
			}
			$suffix=' AND bp.blogid='.$db->Quote($b->id);
		}else{
			$suffix='';
		}
		
		$t=getVar('tagname','','','string');
		if(trim($t)!==''){
			$t=trim(strtolower($db->getEscaped(preg_quote($t))));
			$suffix.=' AND (LOWER(bp.tags) REGEXP \'^'.$t.'$\' OR LOWER(bp.tags) REGEXP \'^'.$t.' *,\' OR LOWER(bp.tags) REGEXP \', *'.$t.' *,\' OR LOWER(bp.tags) REGEXP \', *'.$t.'$\')';
		}
		
		if(ArtaUserGroup::getPerm('can_access_unpublished_comments', 'package', 'blog')==false){
			$en=' AND `published`=1';
		}else{
			$en='';
		}
		
		$cols=',(SELECT COUNT(*) FROM #__blog_attachments as ba WHERE ba.postid=bp.id) AS attachments
		,(SELECT COUNT(*) FROM #__blog_comments as bc WHERE bc.postid=bp.id'.$en.') AS comments
		';
		
		// Making query
		if(ArtaUsergroup::getPerm('can_access_unpublished_posts', 'package', 'blog')){
			$db->setQuery('SELECT SQL_CALC_FOUND_ROWS *'.$cols.' FROM #__blogposts as bp WHERE '.
					'1=1'.$suffix.
				ArtaTagsHtml::SortResult('added_time', 'desc').
				ArtaTagsHtml::LimitResult()
			);
		}else{
			$db->setQuery('SELECT SQL_CALC_FOUND_ROWS *'.$cols.' FROM #__blogposts as bp WHERE '.
					'bp.enabled=1 AND bp.pub_time < '.$db->Quote(ArtaDate::toMySQL(time())).' AND (bp.unpub_time > '.$db->Quote(ArtaDate::toMySQL(time())).
					' OR bp.unpub_time is NULL OR bp.unpub_time=\'0000-00-00 00:00:00\' OR bp.unpub_time=\'1970-01-01 00:00:00\' OR bp.unpub_time=\'\')'.
					$suffix.
				ArtaTagsHtml::SortResult('added_time', 'desc').
				ArtaTagsHtml::LimitResult()
			);
		}
		
		
		$r=(array)$db->loadObjectList();
		
		$plugin=ArtaLoader::Plugin();
		
		foreach($r as $v){
			$plugin->trigger('onPrepareContent', array(&$v, 'blogpost'));
		}
		
		$db->setQuery('SELECT FOUND_ROWS()');
		$this->count=(int)($db->loadResult());
		
		$u=$this->getCurrentUser();
		$dbmsg=$this->getSetting('show_blockmsg_for_denied_users', '0');
		foreach($r as $k=>$v){
			if($k==='unset'){
				continue;
			}
			$perms=array();
			$b=$this->getBlogID($v->blogid);
			$r[$k]->blogid=$b;
			if($dbmsg==false){
				$x=$b;
				$perms[]=$b->accmask;
				while(isset($GLOBALS['CACHE']['blog.categories'][$x->parent])){
					$x=$GLOBALS['CACHE']['blog.categories'][$x->parent];
					$perms[]=$x->accmask;
				}
				
				krsort($perms);
				$perms=ArtaUsergroup::processAccessMask($perms);
				
				
				if(ArtaUsergroup::processDenied($v->denied)==false || ArtaUsergroup::processDenied($perms)==false){
					unset($r[$k]);
					$r['unset']=true;
				}
			}
		}
		
		foreach($r as $k=>$v){
			if($k==='unset'){
				continue;
			}
			$r[$k]->langs=$this->getLangsAvailable($v->id);
			$r[$k]->added_by_id=$r[$k]->added_by;
			$r[$k]->added_by=$this->getAuthor($v->added_by);
		}
		if(isset($r['unset'])) unset($r['unset']);
		return $r;
	}
	
	function getBlogID($b){
		if($b!=false && $b!=0){
			if(@$GLOBALS['CACHE']['blog.categories']==false){
				if(ArtaCache::isUsable('blog', 'categories')){
					$GLOBALS['CACHE']['blog.categories'] = ArtaCache::getData('blog', 'categories');
				}else{
					$db=ArtaLoader::DB();
					$db->setQuery('SELECT * FROM #__blogcategories');
					$byk=(array)$db->loadObjectList('id');
					ArtaCache::putData('blog', 'categories', $byk);
					$GLOBALS['CACHE']['blog.categories']=$byk;
				}

				if(!isset($GLOBALS['CACHE']['blog.categories'][$b])){
					$GLOBALS['CACHE']['blog.categories'][$b]=null;
				}
				return @$GLOBALS['CACHE']['blog.categories'][$b];
			}else{
				if(!isset($GLOBALS['CACHE']['blog.categories'][$b])){
					$GLOBALS['CACHE']['blog.categories'][$b]=null;
				}
				return @$GLOBALS['CACHE']['blog.categories'][$b];
			}
		}else{
			return false;
		}
	}
		
	function getAuthor($id){
		if(!isset($GLOBALS['CACHE']['blog.authors'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id,username,name FROM #__users WHERE id IN (SELECT DISTINCT added_by FROM #__blogposts)');
			$r=(array)$db->loadObjectList('id');
			$GLOBALS['CACHE']['blog.authors']=$r;
		}
		
		$u= @$GLOBALS['CACHE']['blog.authors'][$id];
		
		if($u!==null){
			$t=$this->getSetting('show_first_last_name', '0');
			if($t==0){
				return $u->username;
			}else{
				return $u->name;
			}
		}else{
			return '???';
		}
	}
	
	function getCount(){
		return $this->count;
	}
	
/*	function getAttachments($id){
		if(!isset($GLOBALS['CACHE']['blog.attachments'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT postid, COUNT(*) AS `c` FROM #__blog_attachments GROUP BY postid');
			$r=(array)$db->loadObjectList();
			$GLOBALS['CACHE']['blog.attachments']=array();
			foreach($r as $v){
				$GLOBALS['CACHE']['blog.attachments'][$v->postid]=$v->c;
			}
		}
		return @(int)$GLOBALS['CACHE']['blog.attachments'][$id];
	}
	
	function getComments($v){
		if(!isset($GLOBALS['CACHE']['blog.comments'])){
			$GLOBALS['CACHE']['blog.comments']=array();
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT postid,COUNT(*) AS c FROM #__blog_comments GROUP BY postid');
			$at=(array)$db->loadObjectList();
			foreach($at as $a){
				$GLOBALS['CACHE']['blog.comments'][$a->postid]=$a->c;
			}
		}
		if((int)@$GLOBALS['CACHE']['blog.comments'][$v->id]>0){
			return (int)$GLOBALS['CACHE']['blog.comments'][$v->id];
		}
		return false;
	}
	*/
	
	function getLangsAvailable($id){
		if(!isset($GLOBALS['CACHE']['blog.translations'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT DISTINCT (SELECT l.title FROM #__languages as l WHERE id=t.language) as lang, t.row_id FROM #__languages_translations as t WHERE `group`=\'blogpost\' AND enabled=1');
			$tr=$db->loadobjectList();
			$data=array();
			foreach($tr as $v){
				if(!isset($data[$v->row_id])){
					$data[$v->row_id]=array();
				}
				$data[$v->row_id][]=$v->lang;
			}
			$GLOBALS['CACHE']['blog.translations']=$data;
		}
		
		if(isset($GLOBALS['CACHE']['blog.translations'][$id])){
			return $GLOBALS['CACHE']['blog.translations'][$id];
		}else{
			return null;
		}
	}
}
?>