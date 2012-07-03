<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogModelCategory extends ArtaPackageModel{
	
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
	
	/*function getCategories($p=0, $level=0){
		$r=ArtaCache::getData('blog','new_categories');
		if(($p==0&&$level==0&&$r!=false)==false){
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
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__blogcategories');
				$GLOBALS['CACHE']['blog.new_categories']=
					ArtaUtility::keyByChild((array)$db->loadObjectList(), 'parent', true);
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
			ArtaCache::putData('blog','new_categories', $r);
		}
		return $r;
	}*/
	
	function getCount($bid){
		if(!isset($GLOBALS['CACHE']['blog.postcount'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT blogid,COUNT(*) as c FROM #__blogposts GROUP BY blogid');
			$x=array();
			$r=(array)$db->loadObjectList();
			foreach($r as $v){
				$x[$v->blogid]=$v->c;
			}
			$GLOBALS['CACHE']['blog.postcount']=$x;
		}
		return @$GLOBALS['CACHE']['blog.postcount'][$bid];
	}
	
	function getData(){
		$c=$this->getCategories();
		$this->count=count($c);
		$by=strtolower(getVar('order_by', 'p'));
		if(!in_array($by, array('id', 'title', 'c', 'p'))){
			$by='p';
		}
		$dir=strtolower(getVar('order_dir', 'asc'));
		if(!in_array($dir, array('asc', 'desc'))){
			$dir='asc';
		}
		
		$k=0;
		$p=array();
		foreach($c as $v){
			if($v->parent==$k){
				$p[]=$v->accmask;
				$v->_accmask=$v->accmask;
				$v->accmask=ArtaUsergroup::processAccessMask($p);
				$k=$v->id;
			}else{
				$p=array();
				$k=0;
				$p[]=$v->accmask;
				$v->_accmask=$v->accmask;
				$v->accmask=ArtaUsergroup::processAccessMask($p);
				$k=$v->id;
			}
		}
		
		if($by!=='p'){
			if($by=='title'){
				$cc=array();
				foreach($c as $k=>$v){
					$cc[$k]=clone $v;
					while(substr($cc[$k]->title,0, 2)=='..'){
						$cc[$k]->title=substr($cc[$k]->title, 2);
					}
					$cc[$k]->title=trim($cc[$k]->title);
				}
				$cc=ArtaUtility::sortbyChild($cc, $by, ($dir=='desc'));
				$x=$c;
				$c=array();
				foreach($cc as $k=>$v){
					$c[$k]=$x[$k];
				}
			}else{
				if($by=='c'){
					foreach($c as $k=>$v){
						$c[$k]->c=(int)($this->getCount($v->id));
					}
					$ok=true;
				}
				$c=ArtaUtility::sortbyChild($c, $by, ($dir=='desc'));
			}
		}
		$c=ArtaTagsHtml::LimitResult((array)$c);
		if(!isset($ok) && (int)count($c)>0){
			foreach($c as $k=>$v){
				$c[$k]->c=(int)($this->getCount($v->id));
			}
		}
		return $c;
	}

}
?>