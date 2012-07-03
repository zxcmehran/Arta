<?php

function BlogSEFParser(&$frags){
	$r=array('view');
	if(@trim($frags[0])=='edit'){
		$frags[0]='new';
	}
	switch($frags[0]){
		case 'post':
			if(isset($frags[1])){
				$i=0;
				while(is_numeric($frags[1]{$i})){
					$i++;
				}
				$frags[1]=substr($frags[1], 0, $i);
			}
			$r[1]='id';
		break;
		case 'blogindex':
			$frags[0] = 'index';
		case 'last':
		default:
			if(isset($frags[1])){
				$i=0;
				while(is_numeric($frags[1]{$i})){
					$i++;
				}
				$frags[1]=substr($frags[1], 0, $i);
			}
			$r[1]='blogid';
		break;
		case 'new':
			if(isset($frags[1])){
				$i=0;
				while(is_numeric($frags[1]{$i})){
					$i++;
				}
				$frags[1]=substr($frags[1], 0, $i);
			}
			$r[1]='id';
		break;
	}
	return $r;
}

function BlogSEFMaker(&$frags){
	$r=array('view');
	
	if(@trim($frags['view'])==''){
		$frags['view']='last';
	}
	
	switch($frags['view']){
		case 'post':
			$x=getBlogSEFData();
			if(isset($x[$frags['id']]) && trim($x[$frags['id']])!==''){
				$frags['id'].='-'.$x[$frags['id']];
			}
			$r[1]='id';
		break;
		case 'index':
			$frags['view'] = 'blogindex';
		case 'last':
		default:
			$x = getBlogCatsSEFData();
			if(isset($x[$frags['blogid']]) && trim($x[$frags['blogid']])!==''){
				$frags['blogid'].='-'.$x[$frags['blogid']];
			}
			$r[1]='blogid';
		break;
		case 'new':
			$x=getBlogSEFData();
			if(isset($x[$frags['id']]) && trim($x[$frags['id']])!==''){
				$frags['id'].='-'.$x[$frags['id']];
				$frags['view']='edit';
			}
			$r[1]='id';
		break;
	}
	return $r;
}

function getBlogSEFData(){
	if(!isset($GLOBALS['CACHE']['blog.sef_alias'])){
		if(ArtaCache::isUsable('blog','sef_aliases')){
			$GLOBALS['CACHE']['blog.sef_alias']= ArtaCache::getData('blog','sef_aliases');
		}else{
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id,sef_alias FROM #__blogposts');
			$r=$db->loadObjectList();
			$x=array();
			foreach($r as $v){
				$x[$v->id]=$v->sef_alias;
			}
			ArtaCache::putData('blog','sef_aliases', $x);
			$GLOBALS['CACHE']['blog.sef_alias']= $x;
		}
	}
	return $GLOBALS['CACHE']['blog.sef_alias'];
}

function getBlogCatsSEFData(){
	if(!isset($GLOBALS['CACHE']['blog.cat_sef_alias'])){
		if(ArtaCache::isUsable('blog','cat_sef_aliases')){
			$GLOBALS['CACHE']['blog.cat_sef_alias']= ArtaCache::getData('blog','cat_sef_aliases');
		}else{
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id,sef_alias FROM #__blogcategories');
			$r=$db->loadObjectList();
			$x=array();
			foreach($r as $v){
				$x[$v->id]=$v->sef_alias;
			}
			ArtaCache::putData('blog','cat_sef_aliases', $x);
			$GLOBALS['CACHE']['blog.cat_sef_alias']= $x;
		}
	}
	return $GLOBALS['CACHE']['blog.cat_sef_alias'];
}

?>