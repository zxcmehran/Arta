<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/4/3 19:57 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class InstallerModelPerm_editor extends ArtaPackageModel{
	
	function getD(){
		$i=ArtaRequest::getVars();
		$i['pid']=ArtaFilterinput::clean($i['pid'], 'string');
		$ex=explode('|',$i['pid']);
		if($ex==false){
			ArtaError::show();
		}
		array_shift($ex);
		$type=strtolower($ex[1]);
		$name=strtolower($ex[0]);
		if(@$ex[2]){
			$client=strtolower($ex[2]);
		}elseif($type=='package'){
			$client='admin';
		}
		if(@$ex[3]){
			$group=ArtaFilterinput::safeAddress(strtolower($ex[3]));
		}
		if($client!=='site' && $client!=='admin'){
			ArtaError::show(500);
		}
		if(!in_array($type, array('package', 'plugin'))){
			ArtaError::show(500);
		}
		if($type=='plugin' && !isset($group)){
			ArtaError::show(500);
		}
		$db=ArtaLoader::DB();
		switch($type){
			case 'package':
				$db->setQuery('SELECT * FROM #__packages WHERE name='.$db->Quote($name));
			break;
			case 'plugin':
				$db->setQuery('SELECT * FROM #__plugins WHERE plugin='.$db->Quote($name).' AND `group`='.$db->Quote($group).' AND `client`='.$db->Quote($client));
			break;
		}
		$r=$db->loadObject();
		if($r==null){
			ArtaError::show(404);
		}
		$r->type=$type;
		if(strlen($r->denied)&&$r->denied{0}=='-'){
			$r->denied=substr($r->denied, 1);
			$r->denied_type=1;
		}else{
			$r->denied_type=0;
		}
		$r->id=$r->title.'|'.$name.'|'.$type;
		if(isset($r->client)){
			$r->id .= '|'.$r->client;
		}
		if(isset($r->group)){
			$r->id .= '|'.$r->group;
		}
		return $r;
	}
	
}

?>