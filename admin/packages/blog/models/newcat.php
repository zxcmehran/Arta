<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogModelNewcat extends ArtaPackageModel{
	
	function getCat($ids){
		if(ArtaUsergroup::getPerm('can_addedit_categories', 'package', 'blog')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT BLOG CATEGORIES'));
		}		
		$id=@array_shift($ids);
		if($id==false){
			$r=new stdClass;
			$r->id=0;
			$r->title='';
			$r->sef_alias='';
			$r->desc='';
			$r->accmask='';
			$r->parent=0;
		}else{
			$db=Artaloader::DB();
			$db->setQuery('SELECT * FROM #__blogcategories WHERE id='.$db->Quote($id));
			$r=$db->loadObject();
			if($r==null){
				ArtaError::show(404, trans('CATEGORY NOT FOUND'));
			}
			
		}
		return $r;
	}
	

	
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
		
		foreach($r as &$v){
			
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
	
	function getCats($cat){
		$db=ArtaLoader::DB();
		$x=$cat;
		$p=array($x->accmask);
		while(@(int)$x->parent>0){
			$db->setQuery('SELECT * FROM #__blogcategories WHERE id='.$db->Quote($x->parent));
			$x=$db->loadObject();
			
			if($x!==null){
				$p[]=$x->accmask;
			}
		}
		
		krsort($p);
		$z=$p;
		array_pop($z);
		$this->fromParents=ArtaUsergroup::processAccessMask($z);
		
		return ArtaUsergroup::processAccessMask($p);
		
	}
	
	


}
?>