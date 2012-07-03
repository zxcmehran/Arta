<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/4/3 19:57 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class InstallerModelLogs extends ArtaPackageModel{
	
	var $count=0;
	
	function getLogs(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__installation_logs ORDER BY `time` DESC'.ArtaTagsHtml::LimitResult());
		$r=(array)$db->loadObjectList();
		$db->setQuery('SELECT FOUND_ROWS()');
		$this->count=$db->loadResult();
		return $r;
	}
	
}

?>