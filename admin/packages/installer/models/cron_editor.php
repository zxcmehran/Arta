<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/4/3 19:57 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class InstallerModelCron_editor extends ArtaPackageModel{
	
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
		if(!in_array($type, array('cron'))){
			ArtaError::show(500);
		}
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__crons WHERE cron='.$db->Quote($name));
		$r=$db->loadObject();
		if($r==null){
			ArtaError::show(404);
		}
		/*if($r->nextrun==9999999999){
			ArtaError::show(500, trans('THIS CRON WILL NEVER RUN'));
		}*/
		$r->type=$type;
		$r->id=$r->title.'|'.$name.'|'.$type;
		return $r;
	}
	
}

?>