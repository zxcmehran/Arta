<?php

function UserLinkTypes(){
	return array(
	array('title'=>trans('AVATAR SETTINGS'), 'link'=>'index.php?pack=user&view=avatar'),
	array('title'=>trans('EDIT USER DETAILS'), 'link'=>'index.php?pack=user&view=edit'),
	array('title'=>trans('MEMBERS LIST'), 'link'=>'index.php?pack=user&view=list'),
	array('title'=>trans('LOGIN PAGE'), 'link'=>'index.php?pack=user&view=login'),
	array('title'=>trans('LOGOUT PAGE'), 'link'=>'index.php?pack=user&view=logout'),
	array('title'=>trans('USER NOTES'), 'link'=>'index.php?pack=user&view=notes'),
	array('title'=>trans('USER PROFILE'), 'link'=>'index.php?pack=user&view=profile&uid={userid}'),
	array('title'=>trans('REGISTER PAGE'), 'link'=>'index.php?pack=user&view=register'),
	array('title'=>trans('REGISTERATION RULES PAGE'), 'link'=>'index.php?pack=user&view=register&layout=rules'),
	array('title'=>trans('REMIND USERNAME/PASSWORD'), 'link'=>'index.php?pack=user&view=remind'),
	);
}

function UserLinkControls($linkid, &$default){
	$l=ArtaLoader::Language();
	$l->addtoNeed('pages', 'package');
	switch($linkid){
		case 6:
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id,username,usergroup,register_date FROM #__users');
			$r=$db->loadObjectList();
			$r=ArtaUtility::keyByChild($r, 'usergroup', true);
			$cache=array();
			$db->setQuery('SELECT id,title FROM #__usergroups WHERE id!=0');
			$ugs=$db->loadObjectList();
			foreach($ugs as $v){
				$cache[$v->id]=$v->title;
			}
			$cache[0]=trans('UNKNOWN');
			
			$res=array();
			
			foreach($r as $k=>$v){
				if(!isset($cache[$k])){
					$key=0;
				}else{
					$key=$k;
				}
				$ugtitle=$cache[$k];
				
				$users=array();
				
				$v=ArtaUtility::sortByChild($v, 'register_date');
				
				foreach($v as $vv){
					if(!isset($first)){
						$first=$vv->id;
					}
					$users[$vv->id]=$vv->username;
				}
				$res[$ugtitle]=$users;
			}
			
			
			$default['userid']=$first;
			@$r=trans('PROFILE USERNAME').': '.ArtaTagsHtml::select('uid', $res, 0, 0, array('onchange'=>
			'assign(\'userid\', this.value);', 'style'=>'width:300px;'
			));
		break;
	}
	return $r;
}



?>