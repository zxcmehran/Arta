<?php
if(!defined('ARTA_VALID')){die('No access');}
class UsergroupModelNew{
	
	function getUsergroups($id){
		if(is_array($id)){$id=array_shift($id);}
		if($id!==false){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__usergroups WHERE id='.$db->Quote($id));
			$r=$db->loadAssocList();
		}
		if($id===false||$r==null){
			$r=array(array());
			$r[0]['name']=null;
			$r[0]['title']=null;
		}
		return $r;
	}
	
	function getPerms($id){
		if(is_array($id)){$id=array_shift($id);}
		$db=ArtaLoader::DB();
		if($id!==false){
			$db->setQuery('SELECT * FROM #__usergroupperms ORDER BY client,extype, extname');
			$base=$db->loadObjectList();
			
			$db->setQuery('SELECT * FROM #__usergroupperms_value WHERE usergroup='.$db->Quote($id));
			$vals=ArtaUtility::keyByChild($db->loadObjectList(), 'usergroupperm');
			foreach($base as $k=>&$v){
				if(isset($vals[$v->id])){
					$v->value=$vals[$v->id]->value;
				}else{
					$x=unserialize($v->default);
					if(isset($x[$id])){
						$v->value=$x[$id];
					}else{
						$v->value=@$x['*'];
					}
				}
			}
			$r=$base;
		}
		if($id===false || $r==null){
			$db->setQuery('SELECT * FROM #__usergroupperms ORDER BY client,extype, extname');
			$r=$db->loadObjectList();
			foreach($r as $k=>$v){
				$vs=unserialize($v->default);
				$r[$k]->value=$vs['*'];
			}
		}
		return $r;
	}


}
?>