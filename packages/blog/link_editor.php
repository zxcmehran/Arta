<?php

function BlogLinkTypes(){
	return array(
	array('title'=>trans('LAST POSTS IN BLOG LAYOUT'), 'link'=>'index.php?pack=blog&view=last{0}'),
	array('title'=>trans('LAST POSTS IN INDEX LAYOUT'), 'link'=>'index.php?pack=blog&view=index{0}'),
	array('title'=>trans('POST PERMALINK'), 'link'=>'index.php?pack=blog&view=post&id={0}'),
	array('title'=>trans('NEW POST'), 'link'=>'index.php?pack=blog&view=new')
	);
}

function BlogLinkControls($linkid, &$default){
	$l=ArtaLoader::Language();
	$l->addtoNeed('blog', 'package');
	switch($linkid){
		case 0:
		case 1:
			$catz=getCategoriesTree4linked();
			$data=array(trans('ALL CATEGORIES'));
			foreach($catz as $c){
				$data[$c->id]=$c->title;
			}
			$default['0']='';
			$r=trans('CATEGORY').': '.ArtaTagsHtml::select('catid', $data, 0, 0, array('onchange'=>
			'if(this.options[0].selected == true){assign(\'0\', \'\');}else{
				assign(\'0\', \'&blogid=\'+this.value);
			}'
			));
		break;
		case 2:
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id,title FROM #__blogposts ORDER BY added_time DESC');
			$posts = $db->loadObjectList();
			$data=array();
			foreach($posts as $c){
				if(!isset($first)){
					$first=$c->id;
				}
				$data[$c->id]=$c->title;
			}
			$default['0']=$first;
			@$r=trans('POST').': '.ArtaTagsHtml::select('postid', $data, 0, 0, array('onchange'=>
			'assign(\'0\', this.value);', 'style'=>'width:300px;'
			));
		break;
	}
	return $r;
}


	function getCategoriesTree4linked($p=0, $level=0){
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
			$c=getCategoriesTree4linked($v->id, $j+1);
			$x=count(array_slice($r, 0, $p))+count($c)+1;
			$r=array_merge(array_slice($r, 0, $p),$c,array_slice($r, $p));
			$p=$x;
		}
			
		return $r;
	}

?>