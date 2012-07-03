<?php
/**
 * ArtaUninstaller for Extensions
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaInstaller
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaUninstallerExtension Class
 */

class ArtaUninstallerExtension{
	
	
	/**
	 * Installer object
	 * @var	object
	 */
	var $inst=null;
	
	/**
	 * XML object
	 * @var	object
	 */
	var $xml=null;

	function __construct($inst){
		$this->inst=$inst;
		$this->xml=$inst->xml;
		
	}
	
	function Uninstall(){
		ob_start();
		$xml=$this->xml;
		$r=null;
		foreach($xml->extension as $ext){
			$d=$this->getData($ext);
			$this->inst->installing = $d;
			$r=$this->uninstallExt($ext);
			break;
		}

		$this->inst->content.=ob_get_contents();
		ob_end_clean();
		return $r;
	}
	
	function uninstallExt($ext){
		
		$data=$this->getData($ext);	
		if(!is_array($data)){
			return $data;
		}
		
		$iscore=$this->checkCore($ext, $data);
		if(!is_bool($iscore)){
			return $iscore;
		}
		
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
		
		$fullpth= $clientdir.'/'.$pth;
		
		// Include PHPs
		if(isset($ext->uninstall->PHP->code)){
			foreach($ext->uninstall->PHP->code as $c){
				if(isset($c['type']) && $c['type']=='file'){
					include($fullpth.'/'.$c);
				}else{
					eval($c);
				}
			}
		}
		
		
		if(function_exists(ucfirst($data['name']).'UninstallerBefore')){
			eval('$ub = '.ucfirst($data['name']).'UninstallerBefore($ext, $data);');
			if($ub == false){
				return 'ERROR_OCCURED';
			}
		}
		
		$idb=$this->uninstallDB($ext,$data);
		if($idb!==true){
			return 'ERROR_UNINSTALLING_FROM_DB';
		}
		
		$db=ArtaLoader::DB();
		if(isset($ext->uninstall->SQL->query)){
			foreach($ext->uninstall->SQL->query as $q){
				if(isset($q['type']) && $q['type']=='file'){
					foreach($db->splitSQL(ArtaFile::read($fullpth.'/'.$q)) as $query){
						$db->setQuery($query);
						$db->query();
					}
				}else{
					$db->setQuery($q);
					$db->query();
				}
				
			}
		}
		
		$if=$this->uninstallFiles($ext,$data, $clientdir ,$pth);
		if(count($if)!==0){
			$this->inst->fileError=$if;
			return 'ERROR_UNINSTALLING_FILES';
		}
		
		$il=$this->uninstallLanguages($ext,$data);
		if($il!==true){
			return 'ERROR_UNINSTALLING_LANGUAGES';
		}
		
		$icc=$this->uninstallCacheCleaning($ext, $data);
		if($icc!==true){
			return 'ERROR_UNINSTALLING_CACHE_CLEANING';
		}
		
		$is=$this->uninstallSettings($ext,$data);
		if($is!==true){
			return 'ERROR_UNINSTALLING_SETTINGS';
		}
		
		$iuf=$this->uninstallUserfields($ext,$data);
		if($iuf!==true){
			return 'ERROR_UNINSTALLING_USERFIELDS';
		}
		
		$ip=$this->uninstallPermissions($ext,$data);
		if($ip!==true){
			return 'ERROR_UNINSTALLING_PERMISSIONS';
		}
		
		$im=$this->uninstallMenu($ext,$data);
		if($im!==true){
			return 'ERROR_UNINSTALLING_MENUS';
		}
		
		if($data['type']=='library'){
			$ili=$this->uninstallLibraryIndex($ext,$data);
			if($im!=true){
				return 'ERROR_UNINSTALLING_LIBRARY_INDEX';
			}
		}
		
		if(function_exists(ucfirst($data['name']).'UninstallerAfter')){
			eval(ucfirst($data['name']).'UninstallerAfter($ext, $data);');
		}
				
		return true;
	}
	
	function uninstallLibraryIndex($ext,$data){
		$classes=array();
		if(isset($ext->libraryIndex) AND count($ext->libraryIndex->class)>0){
			foreach($ext->libraryIndex->class as $c){
				$classes[(string)$c['name']]=(string)$c;
			}
		}
		ArtaFile::chmod(ARTAPATH_LIBRARY.'/external/lib.ini', 0644);
		if($classes!=array()){
			$current= ArtaString::parseINI(ARTAPATH_LIBRARY.'/external/lib.ini', '#', true);
			foreach($classes as $k=>$v){
				if(isset($current[$k])){
					unset($current[$k]);
				}
			}
			$result= "# Index of external library classes.\r\n";
			foreach($current as $k=>$v){
				$result.=$k.'='.$v."\r\n";
			}
			return ArtaFile::write(ARTAPATH_LIBRARY.'/external/lib.ini', $result);
		}
		return true;
	}
	
	function uninstallMenu($ext, $data){
		if($data['type']=='package' && isset($ext->menu->item)){
			$db=ArtaLoader::DB();
			$db->setQuery('DELETE FROM #__admin_menu WHERE id IN (SELECT menuid FROM #__admin_menu_map WHERE package='.$db->Quote($data['name']).')');
			$db->query();
			$db->setQuery('DELETE FROM #__admin_menu_map WHERE package='.$db->Quote($data['name']));
			$db->query();
		}
		return true;
	}
	
	function uninstallPermissions($ext, $data){
		
		
		if(isset($ext->perms->var)){
			$db=ArtaLoader::DB();
			$set=array();
			foreach($ext->perms->var as $k=>$vv){
				$vname=isset($vv['name']) ? $vv['name'] : '';
				$cli=isset($vv['client']) ? strtolower($vv['client']) : $data['insertion_client'];
				if($cli!=='site' && $cli!=='admin'){
					$cli=$data['insertion_client'];
				}
				
				if((string)$vname!==''){
					$set[]='(`name`='.$db->Quote($vname).' AND extname='.$db->Quote($data['name']).' AND extype='.$db->Quote($data['type']).' AND `client`='.$db->Quote($cli).')';
				}
			}
			if(count($set)){
				$db->setQuery('DELETE FROM `#__usergroupperms_value` WHERE usergroupperm IN (SELECT id FROM #__usergroupperms WHERE '.implode(' OR ',$set).')');
				if($db->query()==false){
					return false;
				}
				$db->setQuery('DELETE FROM `#__usergroupperms` WHERE '.implode(' OR ',$set));
				if($db->query()==false){
					return false;
				}
			}
		}
		return true;
	}
	
	function uninstallUserfields($ext, $data){
		if(isset($ext->userfields->var)){
			$db=ArtaLoader::DB();
			$set=array();
			foreach($ext->userfields->var as $k=>$vv){
				$vname=isset($vv['name']) ? $vv['name'] : '';
				
				$ftype=isset($vv['fieldtype']) ? strtolower($vv['fieldtype']) : 'setting';
				if($ftype!=='setting'&&$ftype!=='misc'){
					$ftype='setting';
				}
				if((string)$vname!==''){
					$set[]='(`var`='.$db->Quote($vname).' AND `extype`='.$db->Quote($data['type']).' AND `extname`='.$db->Quote($data['name']).' AND `fieldtype`='.$db->Quote($ftype).')';
				}
			}
			if(count($set)){
				$db->setQuery('DELETE FROM #__userfields WHERE '.implode(' OR ', $set));
				if($db->query()==false){
					return false;
				}
			}
		}
		return true;
	}
	
	function uninstallSettings($ext, $data){
		if(isset($ext->settings->var)){
			$db=ArtaLoader::DB();
			$set=array();
			foreach($ext->settings->var as $k=>$vv){
				$vname=isset($vv['name']) ? $vv['name'] : '';
				$cli=isset($vv['client']) ? strtolower($vv['client']) : $data['insertion_client'];
				if($cli!=='site' && $cli!=='admin'){
					$cli=$data['insertion_client'];
				}

				if((string)$vname!==''){
					$set[]='(`var`='.$db->Quote($vname).' AND `extype`='.$db->Quote($data['type']).' AND `extname`='.$db->Quote($data['name']).' AND `client`='.$db->Quote($cli).')';
				}
			}
			if(count($set)){
				$db->setQuery('DELETE FROM #__settings WHERE '.implode(' OR ', $set));
				if($db->query()==false){
					return false;
				}
			}
		}
		return true;
	}
	
	function uninstallCacheCleaning($ext, $data){
		if(isset($ext->cacheCleaning->clean)){	
			$db=ArtaLoader::DB();
			$cache=array();
			foreach($ext->cacheCleaning->clean as $k=>$vv){
				$table=isset($vv['table'])? $vv['table'] : false;
				if($table!==false && (string)$vv!==''){
					$cfields=isset($vv['fields'])? $vv['fields'] : '';
					$cache[]='(`table`='.$db->Quote($table).' AND `cache_name`='.$db->Quote($vv).' AND `cached_fields`='.$db->Quote($cfields).' AND `ext_unique`='.$db->Quote(md5($data['insertion_client'].'|'.$data['type'].'|'.$data['name'])).')';
				}
			}
			if(count($cache)){
				$db->setQuery('DELETE FROM #__cache_cleaning WHERE '.implode(' OR ', $cache));
				if(!$db->query()){
					return false;
				}
			}
		}
		return true;
	}
	
	function uninstallLanguages($ext, $data){
		if(isset($ext->languages->file)){
			foreach($ext->languages->file as $k=>$vv){
				$n=isset($vv['name'])? $vv['name'] : 'en-US';
				$cli=isset($vv['client'])? strtolower($vv['client']) : $data['client'];
				if($cli!=='site'&& $cli!=='admin'){
					$cli=$data['client'];
				}
				eval('$basedir=ARTAPATH_'.strtoupper($cli).';');
				
				if((string)$vv!=='' && is_dir($basedir.'/languages/'.$n) && 
				is_file($basedir.'/languages/'.$n.'/'.ArtaFile::getFilename($vv))){
					
					$res= ArtaFile::delete(
						$basedir.'/languages/'.$n.'/'.ArtaFile::getFilename($vv));
						
					if($res!==true){
						return false;
					}
				}
				
			}
		}
		return true;
	}
	
	
	function uninstallFiles($ext, $data, $clientdir ,$pth){
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
				
							
				// delete files
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
												
						if(in_array($data['type'], array('cron', 'webservice', 'widget', 'library'))){
							$subroot=ARTAPATH_SITE;
						}
						if(is_file($subroot.'/'.$file['destination'].'/'.$file)){
							$w=ArtaFile::delete($subroot.'/'.$file['destination'].'/'.$file);
							if($w==false){
								$r[]='deletefile '.$subroot.DS.$file['destination'].DS.$file;
							}
						}
					}
				}//  end delete files
				
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
						if(is_dir($subroot.'/'.$dir['destination'].'/'.$dir)){
							$w=ArtaFile::rmdir_extra(($subroot.'/'.$dir['destination'].'/'.$dir));
							if($w==false){
								$r[]='deletedir '.$subroot.DS.$dir['destination'].DS.$dir;
							}
						}
					}
				}//  end making dirs
				
			}
		}
		switch($data['type']){
			
			case 'module':
			case 'template':
			case 'language':
			case 'imageset':
			case 'library':
				if(is_dir($clientdir.'/'.$pth)){
					$w= ArtaFile::rmdir_extra($clientdir.'/'.$pth);
					if($w==false){
						$r[]='deletedir '.$clientdir.DS.$pth;
					}
				}
			break;
			case 'package':
				if(is_dir(ARTAPATH_ADMIN.'/'.$pth)){
					$w= ArtaFile::rmdir_extra(ARTAPATH_ADMIN.'/'.$pth);
					if($w==false){
						$r[]='deletedir '.ARTAPATH_ADMIN.DS.$pth;
					}
				}
				if(is_dir(ARTAPATH_SITE.'/'.$pth)){
					$w= ArtaFile::rmdir_extra(ARTAPATH_SITE.'/'.$pth);
					if($w==false){
						$r[]='deletedir '.ARTAPATH_SITE.DS.$pth;
					}
				}
			break;
		}
		
		return $r;
	}
	
	
	function uninstallDB($ext,$data){
		$db=ArtaLoader::DB();
		switch($data['type']){
			case 'package':
				$db->setQuery('DELETE FROM #__packages WHERE name='.$db->Quote($data['name']));
			break;
			case 'module':
				$db->setQuery('DELETE FROM #__modules WHERE module='.$db->Quote($data['name']).' AND client='.$db->Quote($data['client']));
			break;
			case 'plugin':
				$db->setQuery('DELETE FROM #__plugins WHERE plugin='.$db->Quote($data['name']).' AND client='.$db->Quote($data['insertion_client']).' AND `group`='.$db->Quote($data['group']));
			break;
			case 'cron':
			case 'webservice':
				$db->setQuery('DELETE FROM #__'.$data['type'].'s WHERE '.$data['type'].'='.$db->Quote($data['name']));
			break;
			case 'language':
			case 'template':
			case 'imageset':
				$db->setQuery('DELETE FROM #__'.$data['type'].'s WHERE name='.$db->Quote($data['name']).' AND client='.$db->Quote($data['client']));
			break;
			case 'widget':
				$db->setQuery('DELETE FROM #__pages_widgets_resource WHERE filename='.$db->Quote($data['name']));
			break;
		}
		
		if($data['type']!='library'){
			$r=$db->query();
		}else{
			$r=true;
		}
		
		if($r==false){
			return false;
		}else{
			$extname=$data['name'];
			$extype=$data['type'];
			$client=$data['insertion_client'];
			if($extype=='package'){
				$client='admin';
			}elseif(in_array($extype, array('cron', 'webservice', 'widget'))){
				$client='site';
			}
			$db->setQuery('DELETE FROM #__extensions_info WHERE client='.$db->Quote($client).' AND extype='.$db->Quote($extype).' AND extname='.$db->Quote($extname));
			$r=$db->query();
		}
		
		if($r==true){
			$db->setQuery('INSERT INTO #__installation_logs VALUES('.$db->Quote($data['title']).','.$db->Quote($data['name']).', '.$db->Quote($data['type']).', '.$db->Quote($data['client']).', '.$db->Quote($data['version']).', '.time().', \'uninstall\' )');
			$r=$db->query();
		}
		
		return $r;
	}
	
	
	function getData($ext){
		$r=array();
		$type=isset($ext['type']) ? $ext['type'] : 'package';
		$type=strtolower($type);
		if(!in_array($type, array('package'
				, 'module'
				, 'plugin'
				, 'cron'
				, 'webservice'
				, 'template'
				, 'language'
				, 'imageset'
				, 'widget'
				, 'library'))){
			$type='package';
		}
		
		if($type=='plugin'){
			$r['group']=isset($ext->group) ? (string)$ext->group : 'content';
		}
		
		$client=isset($ext['client']) ? $ext['client'] : 'admin';
		$client=strtolower($client);
		if($client!=='site' && $client!=='admin' && $client!=='*'){
			$client='admin';
		}
		
		if(in_array($type, array('cron', 'webservice', 'widget'))){
			$client='site';
		}
		if($client=='*'){
			$insertion_client='*';
			$client='site';
		}else{
			$insertion_client=$client;
		}
		
		if($type=='library'){
			$client = 'site'; 
			$insertion_client = '*';
		}
		
		
		if(!isset($ext->name)){
			return 'ERROR_INVALID_INSTALLATION';
		}
		$name=$ext->name;
		
		if(!isset($ext->title)){
			$title=$ext->name;
		}else{
			$title=$ext->title;
		}
		
		$r['version']=(string)($ext->version);
		$r['type']=(string)$type;
		$r['client']=(string)$client;
		$r['insertion_client']=(string)$insertion_client;
		$r['name']=(string)$name;
		$r['title']=(string)$title;
		return $r;
	}
	
	function checkCore($ext, $data){
		$db=ArtaLoader::DB();
		switch($data['type']){
			case 'package':
				$db->setQuery('SELECT core,id,title FROM #__packages WHERE name='.$db->Quote($data['name']));
				$obj=$db->loadObject();
				$this->obj=$obj;
				if($obj!==null){
					$core=$obj->core;
				}else{
					$core=null;
				}
			break;
			case 'module':
				$db->setQuery('SELECT core FROM #__modules WHERE module='.$db->Quote($data['name']).' AND client='.$db->Quote($data['client']));
				$core=$db->loadResult();
			break;
			case 'plugin':
				$db->setQuery('SELECT core FROM #__plugins WHERE plugin='.$db->Quote($data['name']).' AND client='.$db->Quote($data['insertion_client']).' AND `group`='.$db->Quote($data['group']));
				$core=$db->loadResult();
			break;
			case 'cron':
			case 'webservice':
				$db->setQuery('SELECT core FROM #__'.$data['type'].'s WHERE '.$data['type'].'='.$db->Quote($data['name']));
				$core=$db->loadResult();
			break;
			case 'language':
			case 'template':
			case 'imageset':
				$db->setQuery('SELECT core FROM #__'.$data['type'].'s WHERE name='.$db->Quote($data['name']).' AND client='.$db->Quote($data['client']));
				$core=$db->loadResult();
			break;
			case 'widget':
				$db->setQuery('SELECT core FROM #__pages_widgets_resource WHERE filename='.$db->Quote($data['name']));
				$core=$db->loadResult();
			break;
			case 'library':
				return true;
			break;
		}
		if($core==1){
			return 'ERROR_CANNOT_UNINSTALL_CORE_EXTS';
		}else{
			return true;
		}
		

	}
	
	
	
}
 
?>