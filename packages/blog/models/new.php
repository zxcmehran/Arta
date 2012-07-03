<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogModelNew extends ArtaPackageModel{
	
	function getItem($id){
		if($id){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__blogposts WHERE id='.$db->Quote($id));
			$r=$db->loadObject();
			if($r==null){
				ArtaError::show(404, trans('INVALID POST TO EDIT'));
			}
			if(strlen($r->denied)>0 && $r->denied{0}=='-'){
				$r->denied_type=1;
				$r->denied=substr($r->denied, 1);
			}else{
				$r->denied_type=0;
			}
			if((string)$r->denied==''){
				$r->denied= array();
			}else{
				$r->denied=explode(',',$r->denied);
			}
			$r=$this->getAttachments($r);
			return $r;
		}else{
			$r=new stdClass;
			$r->id=0;
			$r->title='';
			$r->sef_alias='';
			$r->introcontent='';
			$r->morecontent='';
			$r->enabled=1;
			$r->denied=array();
			$r->denied_type=0;
			$r->blogid=0;
			$r->added_time=ArtaDate::toMySQL(time());
			$r->added_by=null;
			$r->mod_time='';
			$r->pub_time=ArtaDate::toMySQL(time());
			$r->unpub_time='';
			$r->hits=0;
			$r->rating=0;
			$r->tags='';
			$r->attachments=null;
			return $r;
			
		}
	}
	
	function getAttachments($r){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT name,url FROM #__blog_attachments WHERE postid='.$db->Quote($r->id));
		$at=$db->loadObjectList('name');
		if(@count($at)>0){
			foreach($at as &$a){
				$a=$a->url;
			}
			$r->attachments=$at;
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
	
	function getTags(){
		$r=ArtaCache::getData('blog','new_tags');
		if($r!=false){
			return $r;
		}else{
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT tags FROM #__blogposts WHERE tags !=\'\'');
			$r=$db->loadObjectList();
			$Res=array();
			if(count($r)){
				foreach($r as $v){
					$tagz=explode(',', $v->tags);
					foreach($tagz as $t){
						$t=trim($t);
						foreach($Res as $rk=>$rv){
							if(strtolower(trim($rk))==strtolower(trim($t))){
								$dup=$rk;
								break;
							}
						}
						if(isset($dup)){
							$Res[$dup]++;
							unset($dup);
						}else{
							
							$Res[$t]=1;
						}
					}
				}
			}
			ArtaCache::putData('blog','new_tags', $Res);
			return $Res;
		}
	}
	
}
?>