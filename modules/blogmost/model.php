<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 6 $
 * @date		2009/3/16 19:5 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class ModuleBlogmostModel extends ArtaModuleModel{
	
	function getMost($blogid){
		$time=(int)$this->getSetting('newposts_time_domain', '2592000');
		if($time<=1){
			$time=2592000;
		}
		
		$p=ArtaLoader::Package();
		$u=$this->getCurrentUser();
		$db=ArtaLoader::DB();
		ArtaLoader::Import('packages->blog->models->last', 'base');
		$bm=new BlogModelLast('last');
		
		if((int)$blogid>0){		
			$suffix=' AND blogid='.$db->Quote($blogid);
		}else{
			$suffix='';
		}
		$suffix.=' ORDER BY `hits` DESC LIMIT 0,'.$this->getSetting('last_limit', 5);
		
		if($p->getSetting('show_blockmsg_for_denied_users', '0', 'blog')==false){
			$bidam=' AND `denied` NOT REGEXP \'(^|,) *'.$u->usergroup.' *(,|$)\' ';
			$den= $bm->getDeniedBlogids();
			if($den!=array()){
				$bidam.=' AND `blogid` NOT IN ('.implode(',', $den).') ';
			}
		}
		if(!isset($bidam)){
			$bidam='';
		}
		
		if(ArtaUsergroup::getPerm('can_access_unpublished_posts', 'package', 'blog')){
			$db->setQuery('SELECT id,title,denied,hits,blogid FROM #__blogposts WHERE '.'added_time > '.$db->Quote(ArtaDate::toMySQL(time()-$time)).$bidam.$suffix);
		}else{
			$db->setQuery('SELECT id,title,denied,hits,blogid FROM #__blogposts WHERE '.'enabled=1 AND pub_time < '.$db->Quote(ArtaDate::toMySQL(time())).
			' AND (unpub_time > '.$db->Quote(ArtaDate::toMySQL(time())).
			' OR unpub_time is NULL OR unpub_time=\'0000-00-00 00:00:00\') AND added_time > '.$db->Quote(ArtaDate::toMySQL(time()-$time)).$bidam.$suffix);
		}
		$r=$db->loadObjectList();
		if($r==null){
			return null;
		}
		return $r;
	}
	
}

?>