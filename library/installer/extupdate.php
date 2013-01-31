<?php
/**
 * ArtaInstaller for Extension Updates
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaInstaller
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaInstallerExtUpdate Class
 * Used to update Extensions.
 */
 
class ArtaInstallerExtUpdate{
	
	/**
	 * Extension XMLNode
	 * @var	object
	 */
	var $ext=null;
	
	/**
	 * Extension Data array
	 * @var	array
	 */
	var $data=null;
	
	/**
	 * Installer Instance
	 * @var	object
	 */
	var $inst=null;
	
	/**
	 * new versions
	 * @var	object
	 */
	var $new=null;
	
	/**
	 * Contains current version of the extension.
	 * 
	 * @var	string
	 */
	var $current_version = '';
	
	function Init(){
		$ext=$this->ext;
		$data=$this->data;
		
		$db=ArtaLoader::DB();
		
		$extname=$data['name'];
		$extype=$data['type'];
		$client=$data['insertion_client'];
		if($extype=='package'){
			$client='admin';
		}elseif(in_array($extype, array('cron', 'webservice', 'widget'))){
			$client='site';
		}
		
		$db->setQuery('SELECT `version` FROM #__extensions_info WHERE client='.$db->Quote($client).' AND extype='.$db->Quote($extype).' AND extname='.$db->Quote($extname));
		$version=$db->loadResult();
		if($version==null){
			return 'ERROR_EXT_NOT_FOUND';
		}
		
		$this->current_version=$version;
		
		$order=array();
		$i=0;
		foreach($ext->extUpdate as $k=>$v){
			$order[$i]=$v["to"];
			$i++;
		}
		uasort($order, 'version_compare');
		
		$new=array();
		foreach($order as $k=>$v){
			// If update ver is more than current ver
			if(version_compare($version, $ext->extUpdate[(int)$k]['from'], "<=") || $ext->extUpdate[(int)$k]['from']=='*'){
				$new[$k]=$ext->extUpdate[(int)$k];
			}
		}
		
		if(count($new)==0){
			return 'ERROR_NO_USEFUL_UPDATES_FOUND';
		}
		
		$first=$version;
		foreach($new as $k=>$v){
			if($v['from']!='*' AND version_compare($v['from'], $version, "!=")){
				return 'ERROR_INVALID_EXTUPDATE';
			}else{
				if($v['from']=='*' AND version_compare($version, $v['to'], ">=")){
					return 'ERROR_NO_USEFUL_UPDATES_FOUND';
				}
				$version=$v['to'];
			}
		}
		
		foreach($new as $k=>$v){
			if(version_compare(ArtaVersion::getVersion(), $v['minVersion'], "<")){
				return 'ERROR_UPDATE_YOUR_ARTA';
			}
		}
		
		$this->new=$new;
		
		return true;
	}
	
	function update(){
		$path=$this->inst->path;
		$ext=$this->ext;
		$data=$this->data;
		
		switch($data['type']){
			case 'package':
			case 'module':
			case 'template':
			case 'language':
			case 'imageset':
				$pth=$data['type']."s/".$data['name'].'/';
			break;
			case 'plugin':
				$pth=$data['type']."s/".($data['group']).'/';
			break;
			case 'cron':
			case 'webservice':
			case 'widget':
				$pth=$data['type'].'s/';
			break;
			case 'library':
				$pth='library/external/'.$data['name'].'/';
			break;
		}
		
		// Client dir for attach to $pth
		if($data['client']=='admin'){
			$clientdir=ARTAPATH_ADMIN;
		}else{
			$clientdir=ARTAPATH_SITE;
		}
		if(in_array($data['type'], array('cron', 'webservice', 'widget', 'library'))){
			$clientdir=ARTAPATH_SITE;
		}
		// Full path to ext dir is $clientdir.DS.$pth
		
		$fullpth = $clientdir.'/'.$pth;
		
		// Move XML
		$xmlmove=$this->moveXML($fullpth.'/'.str_replace(array('/','\\',':','*','?','"','<','>','|'), '_',$data['name']).'.xml', 
				$ext);
		if($xmlmove!==true){
			return $xmlmove;
		}
		
		$db=ArtaLoader::DB();	
		
		foreach($this->new as $upd){
			if(isset($upd->PHP->code)){
				foreach($upd->PHP->code as $c){
					if(isset($c['type']) && $c['type']=='file'){
						include($this->inst->path.'/'.$c);
					}else{
						eval($c);
					}
				}
			}
			
				
			if(isset($upd->SQL->query)){
				foreach($upd->SQL->query as $q){
					if(isset($q['type']) && $q['type']=='file'){
						foreach($db->splitSQL(ArtaFile::read($this->inst->path.'/'.$q)) as $query){
							$db->setQuery($query);
							$db->query();
						}
					}else{
						$db->setQuery($q);
						$db->query();
					}
					
				}
			}
			
			$fi=$this->installFiles($upd,$data,$pth);
			if(count($fi)!==0){
				$this->inst->fileError=$fi;
				return 'ERROR_UPDATING_FILES';
			}
			
			$udb=$this->updateDB($data, $upd['to']);
			if($udb!==true){
				return 'ERROR_UPDATING_VERSION_NUM_IN_DB';
			}
			
		}
		return true;
	}
	
	function updateDB($data, $ver){
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__extensions_info SET `version`='.$db->Quote($ver).' WHERE client='.$db->Quote($data['type']=='package'?'admin':$data['insertion_client']).' AND extype='.$db->Quote($data['type']).' AND extname='.$db->Quote($data['name']),array('version'));
		$r = $db->query();
		if($r==true){
			$db->setQuery('INSERT INTO #__installation_logs VALUES('.$db->Quote($data['title']).','.$db->Quote($data['name']).', '.$db->Quote($data['type']).', '.$db->Quote($data['client']).', '.$db->Quote($this->current_version.'|'.$data['version']).', '.time().', \'update\' )');
			$r=$db->query();
		}
		return $r;
	}
	
	function moveXML($to, $ext){
		$xmlf="<?xml version=\"1.0\" ?>\n<install>\n".$ext->asXML()."\n</install>";
		if(ArtaFile::write($to,$xmlf)==false){
			return 'ERROR_MOVING_XML';
		}else{
			return true;
		}
	}
	
	function installFiles($ext, $data, $pth){
		$r=array();
		if(isset($ext->files)){
			foreach($ext->files as $files){
				// <Populate data>
				// if client not set or invalid
				if(!isset($files['client']) || !in_array($files['client'], array('site','admin'))){
					$files['client']=$data['client'];
				}
				
				if(!isset($files['source'])){
					$files['source']='';
				}else{
					$files['source']=ArtaFile::replaceSlashes(trim($files['source'], '/:\\'));
				}
				
				if(!isset($files['destination'])){
					$files['destination']=$pth;
				}else{
					$files['destination']=ArtaFile::replaceSlashes(trim($files['destination'], '/:\\'));
				}
				
				if($files['client']=='admin'){
					$root=ARTAPATH_ADMIN;
				}else{
					$root=ARTAPATH_SITE;
				}
				if(in_array($data['type'], array('cron', 'webservice', 'widget', 'library'))){
					$root=ARTAPATH_SITE;
				}
				// </Populate data>
				
				// making dirs
				if(isset($files->dir)){
					foreach($files->dir as $dir){
						if(isset($dir['client']) && in_array($dir['client'], array('site','admin'))){
							if($dir['client']=='admin'){
								$subroot=ARTAPATH_ADMIN;
							}else{
								$subroot=ARTAPATH_SITE;
							}
						}else{
							$subroot=$root;
						}
						
						if(!isset($dir['destination'])){
							$dir['destination']=$files['destination'];
						}else{
							$dir['destination']=ArtaFile::replaceSlashes(trim($dir['destination'], '/:\\'));
						}
						
						if(in_array($data['type'], array('cron', 'webservice', 'widget', 'library'))){
							$subroot=ARTAPATH_SITE;
						}
						$w=ArtaFile::mkdir_extra(($subroot.'/'.$dir['destination'].'/'.$dir), ARTAPATH_BASEDIR);
						if($w==false){
							$r[]='makedir '.$subroot.DS.$dir['destination'].DS.$dir;
						}
					}
				}//  end making dirs
				
				
				// moving files
				if(isset($files->file)){
					foreach($files->file as $file){
						
						if(isset($file['client']) && in_array($file['client'], array('site','admin'))){
							if($file['client']=='admin'){
								$subroot=ARTAPATH_ADMIN;
							}else{
								$subroot=ARTAPATH_SITE;
							}
						}else{
							$subroot=$root;
						}
						
						if(!isset($file['destination'])){
							$file['destination']=$files['destination'];
						}else{
							$file['destination']=ArtaFile::replaceSlashes(trim($file['destination'], '/:\\'));
						}
						
						if(!isset($file['source'])){
							$file['source']=$files['source'];
						}else{
							$file['source']=ArtaFile::replaceSlashes(trim($file['source'], '/:\\'));
						}
						
						if(in_array($data['type'], array('cron', 'webservice', 'widget', 'library'))){
							$subroot=ARTAPATH_SITE;
						}
						
						if(!is_dir(ArtaFile::getDir($subroot.'/'.$file['destination'].'/'.$file))){
							ArtaFile::mkdir_extra(ArtaFile::getDir($subroot.'/'.$file['destination'].'/'.$file), $subroot);
						}
						
						$from=ArtaFile::replaceSlashes($this->inst->path.'/'.$file['source'].'/'.$file);
						if(file_exists($subroot.'/'.$file['destination'].'/'.$file) && file_exists($from)){
							ArtaFile::delete($subroot.'/'.$file['destination'].'/'.$file);
						}
						$w=ArtaFile::rename($from, $subroot.'/'.$file['destination'].'/'.$file);
						if($w==false){
							$r[]='copyfile '.$from.' ::to:: '.$subroot.DS.$file['destination'].DS.$file;
						}
					}
				}//  end moving files
				
			}
		}
		return $r;
	}
	
	
}
 
?>