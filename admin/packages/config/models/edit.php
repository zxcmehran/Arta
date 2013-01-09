<?php
if(!defined('ARTA_VALID')){die('No access');}
class ConfigModelEdit extends ArtaPackageModel{
	
	function getPackage($id){
		$by='id';
		if(is_numeric($id)==false){$by='name';}
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT * FROM #__packages WHERE ".$by."=".$db->Quote($id));
		$data=$db->loadObject();
		if($data==null){
			ArtaError::show();
		}
		return $data;
	}

	function getModule($id){
		$db=ArtaLoader::DB();
		$by='id';
		if(is_numeric($id)==false){$by='module';}
		$db->setQuery("SELECT *,module as name FROM #__modules WHERE ".$by."=".$db->Quote($id)." ORDER BY `order`");
		$data = $db->loadObject();
		if($data==null){
			ArtaError::show();
		}
		$data->title = $data->title.' ('.trans($data->client).')';
		if($data->name == null){
			$data->name='custom';
		}
		return $data;
	}

	function getPlugin($id){
		$by='id';
		if(is_numeric($id)==false){$by='plugin';}
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT *,plugin as name  FROM #__plugins WHERE ".$by."=".$db->Quote($id)." ORDER BY `order`");
		$data = $db->loadObject();
		if($data==null){
			ArtaError::show();
		}
		$data->title = $data->title.' ('.($data->client =='*' ?trans('BOTH SITE AND ADMIN'):trans($data->client)).')';
		return $data;
	}

	function getTemplate($id){
		$by='id';
		if(is_numeric($id)==false){$by='name';}
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT *  FROM #__templates WHERE ".$by."=".$db->Quote($id)." ORDER BY `id`");
		$data = $db->loadObject();
		if($data==null){
			ArtaError::show();
		}
		$data->title = $data->title.' ('.trans($data->client).')';
		return $data;
	}

	function getCron($id){
		$by='id';
		if(is_numeric($id)==false){$by='cron';}
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT *, cron as name , 'admin' as client  FROM #__crons WHERE ".$by."=".$db->Quote($id)." ORDER BY `id`");
		$data = $db->loadObject();
		if($data==null){
			ArtaError::show();
		}
		return $data;
	}

	function getSettings($extname, $extype, $client='site'){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT * FROM #__settings WHERE extname=".$db->Quote($extname)." AND extype=".$db->Quote($extype)." AND  (client=".$db->Quote($client).' OR client=\'*\')');
		$data = (array)$db->loadObjectList();
		
		if($extype=='package'){
			// Packages are multi-client
			$db->setQuery("SELECT * FROM #__settings WHERE extname=".$db->Quote($extname)." AND extype='package' AND client=".$db->Quote($client=='site' ? 'admin' : 'site'));
			$data2 = (array)$db->loadObjectList();
			$data=array_merge($data,$data2);
		}
		if(@count($data)==false){
			//ArtaError::show();
		}
		return $data;
	}
}
?>