<?php
if(!defined('ARTA_VALID')){die('No access');}
class ConfigModelExtension extends ArtaPackageModel{
	
	function getPackages(){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT id AS id, 'site' AS client, name AS name, title AS title, 'package.png' AS image, COUNT(s.var) AS c FROM #__packages JOIN #__settings AS s ON name=s.extname WHERE extype='package' GROUP BY s.extname");
		$data= $db->loadObjectList();
		return $data;
	}

	function getModules(){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT id AS id, module AS name, title AS title, 'module.png' AS image, m.client, COUNT(s.var) AS c FROM #__modules AS m JOIN #__settings AS s ON (m.module=s.extname AND m.client=s.client) WHERE extype='module' GROUP BY s.extname ORDER BY `client`,`location`,`order`");
		$data = $db->loadObjectList();
		foreach($data as $k=>$v){
			$data[$k]->title = $v->title.' ('.trans($v->client).')';
		}

		return $data;
	}

	function getPlugins(){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT id AS id, plugin AS name, title AS title, 'plugin.png' AS image, m.client, COUNT(s.var) AS c FROM #__plugins AS m JOIN #__settings AS s ON (m.plugin=s.extname AND m.client=s.client) WHERE extype='plugin' GROUP BY s.extname ORDER BY `client`,`order`");
		$data = $db->loadObjectList();
		foreach($data as $k=>$v){
			$data[$k]->title = $data[$k]->title.' ('.($data[$k]->client =='*' ?trans('BOTH SITE AND ADMIN'):trans($data[$k]->client)).')';
		}
		return $data;
	}

	function getTemplates(){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT id AS id, name AS name, title AS title, 'template.png' AS image, m.client, COUNT(s.var) AS c FROM #__templates AS m JOIN #__settings AS s ON (m.name=s.extname AND m.client=s.client) WHERE extype='template' GROUP BY s.extname ORDER BY `client`,`id`");
		$data = $db->loadObjectList();
		foreach($data as $k=>$v){
			$data[$k]->title = $v->title.' ('.trans($v->client).')';
		}
		return $data;
	}

	function getCrons(){
		$db=ArtaLoader::DB();
		$db->setQuery("SELECT id AS id, cron AS name, title AS title, 'cron.png' AS image, 'site' as client, COUNT(s.var) AS c FROM #__crons AS m JOIN #__settings AS s ON (m.cron=s.extname AND s.client='site') WHERE extype='cron' GROUP BY s.extname ORDER BY `id`");
		$data = $db->loadObjectList();
		return $data;
	}


}
?>