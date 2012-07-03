<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/27 14:16 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class InstallerModelInstall extends ArtaPackageModel{
	
	function get($item){
		$db=ArtaLoader::DB();
		$db->die=true;
		switch($item){
			case 'package':
				$db->setQuery('SELECT CONCAT(`title`, \'|\', `name`,\'|\',\'package\') AS `id`, `title`, `name`, \'package.png\' as `image`, enabled, denied, core FROM #__packages');
				$r=array();
				$reserved=array();
				foreach($db->loadObjectList() as $v){
					if(!in_array($v->name, $reserved)){
						if(strlen($v->image)>0){
							$v->image=$v->image{0}=='#' ? substr($v->image,1) : Imageset($v->image);
						}
						$v->version=$this->getInfo('admin','package',$v->name);
						$r[]=$v;
						$reserved[]=$v->name;
					}
				}
				
			break;
			case 'module':
				$db->setQuery('SELECT CONCAT(`title`, \'|\', `module`,\'|\',\'module\',\'|\', `client`) AS `id`, `title`, `module`, `client`, enabled, denied, core FROM #__modules WHERE  (module!=\'-\' AND module!=\'\' AND module!=\'linkviewer\')');
				$r=$db->loadObjectList();
				foreach($r as &$v){
					$v->version = $this->getInfo($v->client,'module',$v->module);
				}
			break;
			case 'plugin':
				$db->setQuery('SELECT CONCAT(`title`, \'|\', `plugin`,\'|\',\'plugin\',\'|\', `client`, \'|\', `group`) AS `id`, `title`, `plugin`, `client`, enabled, denied, core, `group` FROM #__plugins');
				$r=$db->loadObjectList();
				foreach($r as &$v){
					$v->version = $this->getInfo($v->client,'plugin',$v->plugin);
				}
			break;
			case 'template':
				$db->setQuery('SELECT CONCAT(`title`, \'|\', `name`,\'|\',\'template\',\'|\', `client`) AS `id`, `title`, `name`, `client`, core FROM #__templates');
				$r=array();
				foreach($db->loadObjectList() as $v){
					if($v->client==='site'){
						$pa=ArtaURL::getSiteURL().'';
					}else{
						$pa=ArtaURL::getSiteURL().'admin/';
					}
					$v->version = $this->getInfo($v->client,'template',$v->name);
					$v->image=$pa.'templates/'.ArtaFilterinput::safeAddress($v->name).'/thumb.png';
					$r[]=$v;
				}
			break;
			case 'language':
				$db->setQuery('SELECT CONCAT(`title`, \'|\', `name`,\'|\',\'language\',\'|\', `client`) AS `id`, `title`, `name`, `client`, core FROM #__languages');
				$r=$db->loadObjectList();
				foreach($r as &$v){
					$v->version = $this->getInfo($v->client,'language',$v->name);
				}
			break;
			case 'imageset':
				$db->setQuery('SELECT CONCAT(`title`, \'|\', `name`,\'|\',\'imageset\',\'|\', `client`) AS `id`, `title`, `name`, `client`, core FROM #__imagesets');
				$r=array();
				foreach($db->loadObjectList() as $v){
					if($v->client==='site'){
						$pa=ArtaURL::getSiteURL().'';
					}else{
						$pa=ArtaURL::getSiteURL().'admin/';
					}
					$v->version = $this->getInfo($v->client,'imageset',$v->name);
					$v->image=$pa.'imagesets/'.ArtaFilterinput::safeAddress($v->name).'/imageset_thumb.png';
					$r[]=$v;
				}
			break;
			case 'cron':
				$db->setQuery('SELECT CONCAT(`title`, \'|\', `cron`,\'|\',\'cron\') AS `id`, `title`, `cron`, enabled, core, nextrun, runloop FROM #__crons');
				$r=$db->loadObjectList();
				foreach($r as &$v){
					$v->version = $this->getInfo('site','cron',$v->cron);
				}
			break;
			case 'webservice':
				$db->setQuery('SELECT CONCAT(`title`, \'|\', `webservice`,\'|\',\'webservice\') AS `id`, `title`, `webservice`, enabled, core FROM #__webservices');
				$r=$db->loadObjectList();
				foreach($r as &$v){
					$v->version = $this->getInfo('site','webservice',$v->webservice);
				}
			break;
			case 'widget':
				$db->setQuery('SELECT CONCAT(`title`, \'|\', `filename`,\'|\',\'widget\') AS `id`, `title`, `filename`, core FROM #__pages_widgets_resource');
				$r=$db->loadObjectList();
				foreach($r as &$v){
					$v->version = $this->getInfo('site','widget',$v->filename);
				}
			break;
			case 'library':
				$dir=ArtaFile::listDir(ARTAPATH_LIBRARY.'/external/');
				$r=array();
				ArtaLoader::Import('#xml->simplexml');
				foreach($dir as $d){
					if(!is_dir(ARTAPATH_LIBRARY.'/external/'.$d)){
						continue;
					}
					$xml = ArtaSimpleXML::parseFile(ARTAPATH_LIBRARY.'/external/'.$d.'/'.$d.'.xml');
					if($xml){
						$title = $xml->extension[0]->title;
						$version = $xml->extension[0]->version;
						$o=new stdClass;
						$o->id=$title.'|'.$d.'|library';
						$o->title=$title;
						$o->name=$d;
						$o->version=$version;
						$o->core=0;
						$r[]=$o;
					}
				}
			break;
		}
		if(@$r==null) $r=array();
		
		return $r;
	}
	
	function getInfo($client, $extype, $extname){
		if(!isset($GLOBALS['CACHE']['install.info'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT `version`, CONCAT(`client`,\'|\',`extype`,\'|\',`extname`) as `key` FROM #__extensions_info WHERE extype!=\'library\'');
			$data=$db->loadObjectList('key');
			$GLOBALS['CACHE']['install.info']=$data;
		}
		
		if(isset($GLOBALS['CACHE']['install.info'][$client.'|'.$extype.'|'.$extname])){
			return @$GLOBALS['CACHE']['install.info'][$client.'|'.$extype.'|'.$extname]->version;
		}else{
			return null;
		}
	}
	
	function getUploadedArchives(){
		$dir=@ArtaFile::listDir(ARTAPATH_BASEDIR.'/tmp/installer_sources');
		$res=array();
		if($dir==false){
			return $res;
		}
		foreach($dir as $d){
			if(is_dir(ARTAPATH_BASEDIR.'/tmp/installer_sources/'.$d) OR strtolower(ArtaFile::getExt($d))=='ais'){
				continue;
			}
			$count=$done=0;
			$session=ARTAPATH_BASEDIR.'/tmp/installer_sources/'.md5(ArtaFile::getFilename($d)).'.ais';
			if(is_file($session)){
				$s=@(array)unserialize(file_get_contents($session));
				if(count($s)>0){
					$count=$s['todo'];
					$done=count($s)-1;
					if($count==$done){
						ArtaFile::delete($session);
						continue;
					}
					
				}
			}
			$res[]=array('file'=>$d,'size'=>filesize(ARTAPATH_BASEDIR.'/tmp/installer_sources/'.$d),
								'todo'=>$count,'done'=>$done, 'relpath'=>ArtaFile::getRelatedPath(ARTAPATH_BASEDIR.'/tmp/installer_sources/'.$d, true));
		}
		return $res;
	}
	
}

?>