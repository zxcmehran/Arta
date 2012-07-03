<?php

function PagesSEFParser(&$frags){
	$r=array('view');
	if(@trim($frags[0])=='show'){
		$frags[0]='page';
	}
	if(@trim($frags[0])=='edit'){
		$frags[0]='new';
	}elseif(@is_numeric($frags[0]{0})){
		$frags[1]=$frags[0];
		$frags[0]='page';
		
	}
	switch($frags[0]){
		case 'page':
		case 'new':
			if(isset($frags[1])){
				$i=0;
				while(is_numeric($frags[1]{$i})){
					$i++;
				}
				$frags[1]=substr($frags[1], 0, $i);
			}
			$r[1]='pid';
		break;
	}

	return $r;
}

function PagesSEFMaker(&$frags){
	$r=array();
	
	if(!isset($frags['view']) || trim($frags['view'])==''){
		$frags['view']='page';
	}
	switch($frags['view']){
		case 'page':
			$x=getPagesSEFData();
			if(isset($x[$frags['pid']]) && trim($x[$frags['pid']])!==''){
				$frags['pid'].='-'.$x[$frags['pid']];
			}
		//	$frags['view']='show';
			unset($frags['view']);
			$r[0]='pid';
		break;
		case 'new':
		
			$x=getPagesSEFData();
			if(isset($x[$frags['pid']]) && trim($x[$frags['pid']])!==''){
				$frags['pid'].='-'.$x[$frags['pid']];
				$frags['view']='edit';
			}
			$r[0]='view';
			$r[1]='pid';
		break;
	}
	return $r;
}

function getPagesSEFData(){
	if(!isset($GLOBALS['CACHE']['pages.sef_alias'])){
		if(ArtaCache::isUsable('pages','sef_aliases')){
			$GLOBALS['CACHE']['pages.sef_alias']= ArtaCache::getData('pages','sef_aliases');
		}else{
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id,sef_alias FROM #__pages');
			$r=$db->loadObjectList();
			$x=array();
			foreach($r as $v){
				$x[$v->id]=$v->sef_alias;
			}
			ArtaCache::putData('pages','sef_aliases', $x);
			$GLOBALS['CACHE']['pages.sef_alias']= $x;
		}
	}
	return $GLOBALS['CACHE']['pages.sef_alias'];
}

?>