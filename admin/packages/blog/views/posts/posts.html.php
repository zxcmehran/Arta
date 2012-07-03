<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewPosts extends ArtaPackageView{
	
	function display(){
		
		$model=$this->getModel();
		$this->setTitle(trans('BLOG POSTS MANAGER'));
		$this->assign('posts', $model->getPosts());
		$this->assign('c', $model->C);
		$this->assign('u', $model->u);

		$this->m=$model;
		ArtaAdminButtons::addNew();
		ArtaAdminButtons::addEdit();
		ArtaAdminButtons::addDelete();
		ArtaAdminButtons::addSetting('blog');
		
		ArtaAdminTips::addTip(trans('BLOG POSTS MANAGER TIP'));
		
		$this->render();
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
					ArtaUtility::keyByChild((array)$db->loadObjectList(), 'parent', true, true);
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


}
?>