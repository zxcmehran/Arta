<?php

function UserSEFParser(&$frags){
	$r=array('view');
	switch($frags[0]){
		case 'avatar':
			$r[1]='uid';
		break;
		case 'list':
			$r[1]='l';
		break;
		case 'profile':
			if(isset($frags[1])){
				$i=0;
				while(is_numeric($frags[1]{$i})){
					$i++;
				}
				$frags[1]=substr($frags[1], 0, $i);
			}
			$r[1]='uid';
		break;
	}
	if($frags[0]=='avatar' && substr($frags[1],-4)=='_big'){
		$frags[1]=intval($frags[1]);
		ArtaRequest::addvar('big', '1');
	}
	return $r;
}

function UserSEFMaker(&$frags){
	$r=array('view');
	if(@$frags['view']==''){
		$frags['view']='login';
	}
	switch($frags['view']){
		case 'avatar':
			$r[1]='uid';
		break;
		case 'list':
			$r[1]='l';
		break;
		case 'profile':
			$user=getUserSEFData();
			
			if(isset($frags['uid']) && $frags['uid']>0 && isset($user[$frags['uid']])){
				$frags['uid'].='-'.ArtaFilteroutput::stringURLSafe($user[$frags['uid']]);
			}
			$r[1]='uid';
		break;
	}
	if($frags['view']=='avatar' && @$frags['big']==true){
		unset($frags['big']);
		$frags['uid'].='_big';
	}
	return $r;
}

function getUserSEFData(){
	if(!isset($GLOBALS['CACHE']['users.sef_alias'])){
		if(ArtaCache::isUsable('user','sef_aliases')){
			$GLOBALS['CACHE']['users.sef_alias']= ArtaCache::getData('user','sef_aliases');
		}else{
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id,username FROM #__users');
			$r=(array)$db->loadObjectList();
			$x=array();
			foreach($r as $v){
				$x[$v->id]=$v->username;
			}
			ArtaCache::putData('user','sef_aliases', $x);
			$GLOBALS['CACHE']['users.sef_alias']= $x;
		}
	}
	return $GLOBALS['CACHE']['users.sef_alias'];
}


?>