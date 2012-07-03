<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 19:5 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class ModuleBlogcatsModel extends ArtaModuleModel{
	var $cache=false;
	
	
	function getPCat($parent=0){
		
		if(@$GLOBALS['CACHE']['blog.categories']==false){
			if(ArtaCache::isUsable('blog', 'categories')){
				$byk = ArtaCache::getData('blog', 'categories');
			}else{
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__blogcategories');
				$byk=(array)$db->loadObjectList('id');
				ArtaCache::putData('blog', 'categories', $byk);
			}
			$GLOBALS['CACHE']['blog.categories']=$byk;
		}
		
		if(@$this->cache==false){
			$r=ArtaUtility::keyByChild($GLOBALS['CACHE']['blog.categories'], 'parent', true);
			$this->cache=$r;
		}else{
			$r=$this->cache;
		}
		
		
		$byk=$GLOBALS['CACHE']['blog.categories'];
		
		if(isset($r[$parent])){
			$p=$r[$parent];
			if(is_object($p)){
				$p=array($p);
			}
		}else{
			$p=array();
		}
		
		
		$pack=ArtaLoader::Package();
		if($pack->getSetting('show_blockmsg_for_denied_users', '0', 'blog')==false){
			ArtaLoader::Import('packages->blog->models->last', 'base');
			$bm=new BlogModelLast('last');
			$den = $bm->getDeniedBlogids();
			foreach($den as $d){
				if(isset($p[$d])){
					unset($p[$d]);
				}
			}
		}
		
		
		$plug=ArtaLoader::Plugin();
		foreach($p as $k=>$v){
			$plug->trigger('onPrepareContent', array(&$p[$k], 'blogcat'));
	/*		$perms=array();
			$x=$v;
			$perms[]=$x->accmask;
			while(isset($byk[$x->parent])){
				$x=$byk[$x->parent];
				$perms[]=$x->accmask;
			}
			krsort($perms);

			$perms=ArtaUsergroup::processAccessMask($perms);

			if(ArtaUsergroup::processDenied($perms) == false){
				unset($p[$k]);
				continue;
			}
			
			$plug->trigger('onPrepareContent', array(&$p[$k], 'blogcat'));
		*/	
		}
		return $p;
		
	}
}

?>