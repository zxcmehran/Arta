<?php
/**
 * ArtaInstaller for Extensions
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
 * ArtaInstallerExtension Class
 */
 
class ArtaInstallerExtension{
	
	
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
	
	/**
	 * Extension Updater instance, in case of extension update process
	 * @var	object
	 */
	var $extupdate=null;

	function __construct($inst){
		$this->inst=$inst;
		$this->xml=$inst->xml;
	}
	
	function Install(){
		ob_start();
		$xml=$this->xml;
		$r=array();
		
		$todo=count($xml->extension); // to find out are all taken care or something is remaining?
		$done=0;
		$this->inst->todo=$todo;
		foreach($xml->extension as $ext){
			$res=true;
			$done++;
			$d=$this->getData($ext);
			if(is_string($d)){
				return $d;
			}
			$this->inst->installing = $d;
			if(in_array($d['client'].'|'.$d['type'].'|'.$d['name'], $this->inst->installed)){
				continue; // go to next ext
			}
			$res=$this->installExt($ext);
			if($res===true){
				$this->inst->installed[]=$d['client'].'|'.$d['type'].'|'.$d['name'];
			}
			break; // finished installing one. say ok to user.
		}
		if($done==$todo && $res===true){
			$this->inst->fully_installed=true;
		}
		$this->inst->content.=ob_get_contents();
		ob_end_clean();
		return $res;
	}
	
	function installExt($ext){
		$ver=$this->checkVer($ext);
		if($ver!==true){
			return $ver;
		}
		$data=$this->getData($ext);	
		if(!is_array($data)){
			return $data;
		}
		
		$exists=$this->checkExistence($data);
		
		if($exists!==false){
			if(!isset($ext->extUpdate)){
				return 'ERROR_EXISTS';
			}else{
				ArtaLoader::Import('#installer->extupdate');
				$update= new ArtaInstallerExtUpdate;
				$update->ext=$ext;
				$update->data=$data;
				$update->inst=$this->inst;
				$init=$update->init();
				$this->extupdate = $update;
				if($init!==true){
					if($init=='ERROR_NO_USEFUL_UPDATES_FOUND' && @$ext['onNewerAvailable']=='ignore'){
						return true;
					}
					return $init;
				}else{
					// check dependencies
					$deps = $this->checkDependenciesError($ext);		
					if(count($deps)>0){
						return $deps;
					}
					return $update->update();
				}
			}
		}
		
		// check dependencies
		$deps = $this->checkDependenciesError($ext);		
		if(count($deps)>0){
			return $deps;
		}
		
		if(isset($ext->steps) && @count($ext->steps->step)>0){
			$this->inst->steps = count($ext->steps->step);
			if($this->inst->step == 0){
				return null;
				$this->inst->step = 1;
			}
			$step = $this->inst->step;
			$ret=false;
			if(isset($_POST['pack'])){ // make $_POST available only for step parameters.
				unset($_POST['pack']);
			}
			// try to load last step file to validate submitted data 
			if($this->inst->step!=1 && count($_POST)>0 && $this->inst->step-1<=$this->inst->steps){
				$i=1;
				foreach($ext->steps->step as $st){
					if($this->inst->step==$i+1){
						$last_step=$st;
						break;
					}
					$i++;
				}
				$action='validate';
				include $this->inst->path.'/'.$last_step['file'];
				$ret=true;
			}
			// try to load next step which is going to be showed currently.
			if($this->inst->step<=$this->inst->steps){
				$i=1;
				foreach($ext->steps->step as $st){
					if($this->inst->step==$i){
						$current_step=$st;
						break;
					}
					$i++;
				}
				$action='show';
				include $this->inst->path.'/'.$current_step['file'];
				$ret=true;
			}
			if($ret){
				return null;
			}
		}
		
	/*	var_dump('installed '.$ext->title); // used on debug cases
		return true;
		*/
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
		
		if(!is_dir($fullpth)){
			ArtaFile::mkdir_extra($fullpth, $clientdir);
			$made=true;
		}else{
			$made=false;
		}
		
		// Move XML
		$xmlmove=$this->moveXML($fullpth.'/'.str_replace(array('/','\\',':','*','?','"','<','>','|'), '_',$data['name']).'.xml', 
				$made, 
				$ext);
				
		if($xmlmove!==true){
			return $xmlmove;
		}
		
		// Include PHPs
		if(isset($ext->install->PHP->code)){
			foreach($ext->install->PHP->code as $c){
				if(isset($c['type']) && $c['type']=='file'){
					include($this->inst->path.'/'.$c);
				}else{
					eval($c);
				}
			}
		}
		
		
		if(function_exists(ucfirst($data['name']).'InstallerBefore')){
			eval('$ib = '.ucfirst($data['name']).'InstallerBefore($ext, $data);');
			if($ib == false){
				return 'ERROR_OCCURED';
			}
		}
		
		$idb=$this->installDB($ext,$data);
		if($idb!==true){
			return 'ERROR_INSTALLING_IN_DB';
		}
		
		$db=ArtaLoader::DB();		
		if(isset($ext->install->SQL->query)){
			foreach($ext->install->SQL->query as $q){
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
		
		$if=$this->installFiles($ext,$data,$pth);
		if(count($if)!==0){
			$this->inst->fileError=$if;
			return 'ERROR_INSTALLING_FILES';
		}
		
		$il=$this->installLanguages($ext,$data);
		if($il!==true){
			return 'ERROR_INSTALLING_LANGUAGES';
		}
		
		$icc=$this->installCacheCleaning($ext,$data);
		if($icc!==true){
			return 'ERROR_INSTALLING_CACHE_CLEANING';
		}
		
		$is=$this->installSettings($ext,$data);
		if($is!==true){
			return 'ERROR_INSTALLING_SETTINGS';
		}
		
		$iuf=$this->installUserfields($ext,$data);
		if($iuf!==true){
			return 'ERROR_INSTALLING_USERFIELDS';
		}
		
		$ip=$this->installPermissions($ext,$data);
		if($ip!==true){
			return 'ERROR_INSTALLING_PERMISSIONS';
		}
		
		$im=$this->installMenu($ext,$data);
		if($im!==true){
			return 'ERROR_INSTALLING_MENUS';
		}
		
		if($data['type']=='library'){
			$ili=$this->installLibraryIndex($ext,$data);
			if($im!=true){
				return 'ERROR_INSTALLING_LIBRARY_INDEX';
			}
		}
		
		if(function_exists(ucfirst($data['name']).'InstallerAfter')){
			eval(ucfirst($data['name']).'InstallerAfter($ext, $data);');
		}
		
		return true;
	}
	
	function installLibraryIndex($ext,$data){
		$classes=array();
		if(isset($ext->libraryIndex) AND count($ext->libraryIndex->class)>0){
			foreach($ext->libraryIndex->class as $c){
				$classes[(string)$c['name']]=(string)$c;
			}
		}
		ArtaFile::chmod(ARTAPATH_LIBRARY.'/external/lib.ini', 0644);
		if($classes!=array()){
			$current= ArtaString::parseINI(ARTAPATH_LIBRARY.'/external/lib.ini', '#', true);
			$res= array_merge($current,$classes);
			$result= "# Index of external library classes.\r\n";
			foreach($res as $k=>$v){
				$result.=$k.'='.$v."\r\n";
			}
			return ArtaFile::write(ARTAPATH_LIBRARY.'/external/lib.ini', $result);
		}
		return true;
	}
	
	function _menu_additem($item, $data, $p=0, $ord=0, $done=array()){
		$db=ArtaLoader::DB();
		$x=0;
		foreach($item as $i){
			if(!in_array($i, $done)){
				$done[]=$i;
				$pic=isset($i['image']) ? $i['image'] : 'package.png';
				$t=isset($i['title']) ? $i['title'] : $data['title'];
				$parent=($p!==0) ? $p : 20;
				$link=isset($i['link']) ? $i['link'] : '';
				$db->setQuery('INSERT INTO #__admin_menu VALUES(NULL, '.$db->Quote($t).', '.$db->Quote($link).', '.$db->Quote($pic).', '.$db->Quote($parent).', '.$db->Quote($ord).')');
				if($db->query()==false){
					return false;
				}
				$db->setQuery('SELECT LAST_INSERT_ID()');
				$par=(int)$db->loadResult();
				$db->setQuery('INSERT INTO #__admin_menu_map VALUES('.$db->Quote($data['name']).', '.$db->Quote($par).')');
				$db->query();
				
				if(isset($item->item)){
					
					$xxxx=$this->_menu_additem($item->item, $data, (int)$par, $ord+1, $done);
					if($xxxx==false){
						return false;
					}
				}
			}
			
		}
		return true;
	}
	
	function installMenu($ext, $data){
		if($data['type']=='package' && isset($ext->menu->item)){
			$r= $this->_menu_additem($ext->menu->item, $data);
			return  $r;
		}
		return true;
	}
	
	function installPermissions($ext, $data){
		if(isset($ext->perms->var)){
			$db=ArtaLoader::DB();
			$set=array();
			foreach($ext->perms->var as $k=>$vv){
				$vname=isset($vv['name']) ? $vv['name'] : '';
				$cli=isset($vv['client']) ? strtolower($vv['client']) : $data['insertion_client'];
				if($cli!=='site' && $cli!=='admin'){
					$cli=$data['insertion_client'];
				}
				$varType=isset($vv['type']) ? strtolower($vv['type']) : 'text';
				if(!in_array($varType, array('custom', 'date', 'calendar', 'users', 'usergroups', 'packages', 'modules', 'plugins', 'languages', 'templates', 'bool', 'radio', 'select', 'text', 'textbox', 'imagesets'))){
					$xx=true;
				}
				$varTypeData=isset($vv['typedata']) ? $vv['typedata'] : '';
				$Check=isset($vv['check']) ? $vv['check'] : '';
				if(!isset($xx) && (string)$vname!==''){
					$set[]='(NULL, '.$db->Quote($vname).', '.$db->Quote($vv).', '.$db->Quote($data['name']).', '.$db->Quote($data['type']).', '.$db->Quote($cli).', '.$db->Quote($varType).', '.$db->Quote($varTypeData).', '.$db->Quote($Check).')';
				}else{
					unset($xx);
				}
			}
			if(count($set)){
				$db->setQuery('INSERT INTO #__usergroupperms VALUES '.implode(' , ', $set));
				if($db->query()==false){
					return false;
				}
			}
		}
		return true;
	}
	
	function installUserfields($ext, $data){
		if(isset($ext->userfields->var)){
			$db=ArtaLoader::DB();
			$set=array();
			foreach($ext->userfields->var as $k=>$vv){
				$vname=isset($vv['name']) ? $vv['name'] : '';
				$varType=isset($vv['type']) ? strtolower($vv['type']) : 'text';
				if(!in_array($varType, array('custom', 'date', 'calendar', 'users', 'usergroups', 'packages', 'modules', 'plugins', 'languages', 'templates', 'bool', 'radio', 'select', 'text', 'textbox', 'imagesets'))){
					$xx=true;
				}
				$varTypeData=isset($vv['typedata']) ? $vv['typedata'] : '';
				$Check=isset($vv['check']) ? $vv['check'] : '';
				$vCode=isset($vv['viewcode']) ? $vv['viewcode'] : '';
				$ftype=isset($vv['fieldtype']) ? strtolower($vv['fieldtype']) : 'setting';
				$showreg=isset($vv['show_on_register']) ? (bool)($vv['show_on_register']) : false;
				if($ftype!=='setting'&&$ftype!=='misc'){
					$ftype='setting';
				}
				if(!isset($xx) && (string)$vname!==''){
					$set[]='('.$db->Quote($vname).', '.$db->Quote($vv).', '.$db->Quote($data['type']).', '.$db->Quote($data['name']).', '.$db->Quote($varType).', '.$db->Quote($varTypeData).', '.$db->Quote($Check).', '.$db->Quote($vCode).', '.$db->Quote($ftype).', '.$db->Quote($showreg).')';
				}else{
					unset($xx);
				}
			}
			if(count($set)){
				$db->setQuery('INSERT INTO #__userfields VALUES '.implode(' , ', $set));
				if($db->query()==false){
					return false;
				}
			}
		}
		return true;
	}
	
	function installSettings($ext, $data){
		if(isset($ext->settings->var)){
			$db=ArtaLoader::DB();
			$set=array();
			foreach($ext->settings->var as $k=>$vv){
				$vname=isset($vv['name']) ? $vv['name'] : '';
				$cli=isset($vv['client']) ? strtolower($vv['client']) : $data['insertion_client'];
				if($cli!=='site' && $cli!=='admin'){
					$cli=$data['insertion_client'];
				}
				$Def=isset($vv['default']) ? $vv['default'] : (string)$vv;
				$varType=isset($vv['type']) ? strtolower($vv['type']) : 'text';
				if(!in_array($varType, array('custom', 'date', 'calendar', 'users', 'usergroups', 'packages', 'modules', 'plugins', 'languages', 'templates', 'bool', 'radio', 'select', 'text', 'textbox', 'imagesets'))){
					$xx=true;
				}
				$varTypeData=isset($vv['typedata']) ? $vv['typedata'] : '';
				$Check=isset($vv['check']) ? $vv['check'] : '';
				if(!isset($xx) && (string)$vname!==''){
					$set[]='('.$db->Quote($vname).', '.$db->Quote($vv).', '.$db->Quote($Def).', '.$db->Quote($data['type']).', '.$db->Quote($data['name']).', '.$db->Quote($cli).', '.$db->Quote($varType).', '.$db->Quote($varTypeData).', '.$db->Quote($Check).')';
				}else{
					unset($xx);
				}
			}
			if(count($set)){
				$db->setQuery('INSERT INTO #__settings VALUES '.implode(' , ', $set));
				if($db->query()==false){
					return false;
				}
			}
		}
		return true;
	}
	
	function installCacheCleaning($ext, $data){
		if(isset($ext->cacheCleaning->clean)){	
			$db=ArtaLoader::DB();
			$cache=array();
			foreach($ext->cacheCleaning->clean as $k=>$vv){
				$table=isset($vv['table'])? $vv['table'] : false;
				if($table!==false && (string)$vv!==''){
					$cfields=isset($vv['fields'])? $vv['fields'] : '';
					$cache[]='('.$db->Quote($table).', '.$db->Quote($vv).', '.$db->Quote($cfields).', '.$db->Quote(md5($data['insertion_client'].'|'.$data['type'].'|'.$data['name'])).')';
				}
			}
			if(count($cache)){
				$db->setQuery('INSERT INTO #__cache_cleaning VALUES '.implode(' , ', $cache));
				if(!$db->query()){
					return false;
				}
			}
		}
		return true;
	}
	
	function installLanguages($ext, $data){
		if(isset($ext->languages->file)){
			foreach($ext->languages->file as $k=>$vv){
				$n=isset($vv['name'])? $vv['name'] : 'en-US';
				$cli=isset($vv['client'])? strtolower($vv['client']) : $data['client'];
				if($cli!=='site'&& $cli!=='admin'){
					$cli=$data['client'];
				}
				eval('$basedir=ARTAPATH_'.strtoupper($cli).';');
				if((string)$vv!=='' && is_dir($basedir.'/languages/'.$n)){
					$res= ArtaFile::rename($this->inst->path.'/'.$vv, $basedir.'/languages/'.$n.'/'.ArtaFile::getFilename($vv));
					if($res!==true){
						return false;
					}
				}
				
			}
		}
		return true;
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
	
	
	function installDB($ext,$data){
		$db=ArtaLoader::DB();
		$db->die=true;
		switch($data['type']){
			case 'package':
				$den=isset($ext->denied) ? $ext->denied : '';
/*				$db->setQuery('SELECT MAX(`admin_menu_order`) FROM `#__packages` WHERE `admin_menu_enabled`=1 AND `core`=0 AND `admin_menu_parent`=0');
				$max=$db->loadResult();*/
			
				$db->setQuery('INSERT INTO `#__packages` VALUES (NULL, '.$db->Quote($data['name']).', '.$db->Quote($data['title']).', 0, 1, '.$db->Quote($den).' )');
				/*----------------------------------------------------|
				, '.($data['client']=='admin').', '.$db->Quote($pic).', '.$db->Quote((int)$max+1).', \'\', '.$db->Quote(0).'
				*/
				/*$r=$db->query();*/
			break;
			case 'module':
				$loc=isset($ext->location) ? $ext->location : 'left';
				$den=isset($ext->denied) ? $ext->denied : '';
				$sh=isset($ext->title['show']) ? (bool)$ext->title['show'] : '1';
				$db->setQuery('SELECT MAX(`order`) FROM `#__modules` WHERE `client`='.$db->Quote($data['client']).' AND `location`='.$db->Quote($loc));
				$max=$db->loadResult();
				$db->setQuery('INSERT INTO `#__modules` VALUES(NULL, '.$db->Quote($data['title']).', '.((int)$max+1).', '.$db->Quote($loc).', 1, NULL, '.$db->Quote($den).', '.$db->Quote($data['name']).',NULL, '.($sh).', 0, '.$db->Quote($data['client']).')');
			break;
			case 'plugin':
				$den=isset($ext->denied) ? $ext->denied : '';
				$db->setQuery('SELECT MAX(`order`) FROM `#__plugins` WHERE `client`='.$db->Quote($data['insertion_client']).' AND `group`='.$db->Quote($data['group']));
				$max=$db->loadResult();
				$db->setQuery('INSERT INTO `#__plugins` VALUES(NULL, '.$db->Quote($data['title']).', '.$db->Quote($data['name']).', '.$db->Quote($data['group']).', '.$db->Quote((int)$max+1).', 1, '.$db->Quote($den).', 0, '.$db->Quote($data['insertion_client']).')');
			break;
			case 'cron':
				$loop=isset($ext->runloop) ? (int)$ext->runloop : 172800;// 2 days
				$db->setQuery('INSERT INTO `#__crons` VALUES(NULL, '.$db->Quote($data['title']).', '.$db->Quote($data['name']).', 1, '.$db->Quote($loop).', '.$db->Quote(time()+60).', '.$db->Quote(time()).', 0)');
			break;
			case 'imageset':
			case 'language':
			case 'template':
				$des=isset($ext->author) ? $db->Quote($ext->author) : 'NULL';
				$db->setQuery('INSERT INTO `#__'.$data['type'].'s` VALUES(NULL, '.$db->Quote($data['name']).', '.$db->Quote($data['title']).', '.($des).', '.$db->Quote($data['client']).', 0)');
			break;
			case 'webservice':
				$db->setQuery('INSERT INTO `#__'.$data['type'].'s` VALUES(NULL, '.$db->Quote($data['name']).', '.$db->Quote($data['title']).', 1, 0)');
			break;
			case 'widget':
				$db->setQuery('INSERT INTO `#__pages_widgets_resource` VALUES(NULL, '.$db->Quote($data['title']).', '.$db->Quote($data['name']).', 0)');
			break;
		}
		if($data['type']!=='library'){
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
			$db->setQuery('INSERT INTO #__extensions_info VALUES('.$db->Quote($client).','.$db->Quote($extype).', '.$db->Quote($extname).', '.$db->Quote($data['version']).',0 )');
			$r=$db->query();
		}
		
		if($r==true){
			$db->setQuery('INSERT INTO #__installation_logs VALUES('.$db->Quote($data['title']).','.$db->Quote($data['name']).', '.$db->Quote($data['type']).', '.$db->Quote($data['client']).', '.$db->Quote($data['version']).', '.time().', \'install\' )');
			$r=$db->query();
		}
		return $r;
	}
	
	
	function moveXML($to, $made, $ext){
		$xmlf="<?xml version=\"1.0\" ?>\n<install>\n".$ext->asXML()."\n</install>";
		if(ArtaFile::write($to,$xmlf)==false){
			if($made==true){
				ArtaFile::rmdir(ArtaFile::getDir($to));
			}
			return 'ERROR_MOVING_XML';
		}else{
			return true;
		}
	}
	
	function checkVer($ext){
		if(isset($ext->minVersion) && version_compare(ArtaVersion::getVersion(), $ext->minVersion, "<")/*
			str_replace('b','.5',str_replace('.','',$ext->minVersion))
				>
				str_replace('b','.5',str_replace('.','',ArtaVersion::getVersion()))*/
		){
			return 'ERROR_UPDATE_YOUR_ARTA';
		}else{
			return true;
		}
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
		
		if(!isset($ext->name) || !isset($ext->version)){
			return 'ERROR_INVALID_INSTALLATION';
		}
		$name=$ext->name;
		
		if(!isset($ext->title)){
			$title=$ext->name;
		}else{
			$title=$ext->title;
		}
		
		$steps=1;
		if(isset($ext->steps) AND count($ext->steps->step)>0){
			$steps = count($ext->steps->step);
		}
		
		
		$r['version']=(string)($ext->version);
		$r['type']=(string)$type;
		$r['client']=(string)$client;
		$r['insertion_client']=(string)$insertion_client;
		$r['name']=(string)$name;
		$r['title']=(string)$title;
		$r['steps']=$steps;
		return $r;
	}
	
	
	function checkExistence($data){
		switch($data['type']){
			case 'package':
				if(	is_dir(ARTAPATH_BASEDIR.'/packages/'.$data['name'])	|| 
				is_dir(ARTAPATH_ADMIN.'/packages/'.$data['name'])){
					return "ERROR_DIR_EXISTS";
				}
			break;
			case 'module':
			case 'template':
			case 'language':
			case 'imageset':
				$c=$data['client']=='site' ? ARTAPATH_BASEDIR : ARTAPATH_ADMIN;
				if(is_dir($c.'/'.$data['type'].'s/'.$data['name'])){
					return "ERROR_DIR_EXISTS";
				}
			break;
			case 'plugin':
				$c=$data['client']=='site' ? ARTAPATH_BASEDIR : ARTAPATH_ADMIN;
				if(is_file($c.'/'.$data['type'].'s/'.$data['group'].'/'.$data['name'].'.php')){
					return "ERROR_FILE_EXISTS";
				}
			break;
			case 'cron':
			case 'webservice':
			case 'widget':
				$c= ARTAPATH_BASEDIR;
				if(is_file($c.'/'.$data['type'].'s/'.$data['name'].'.php')){
					return "ERROR_FILE_EXISTS";
				}
			break;
			case 'library':
				if(is_dir(ARTAPATH_LIBRARY.'/external/'.$data['name'])){
					return "ERROR_DIR_EXISTS";
				}
			break;
		}
		
		$db=ArtaLoader::DB();
		
		switch($data['type']){
			case 'package':
				$db->setQuery('SELECT id FROM #__packages WHERE name='.$db->Quote($data['name']));
				$r=$db->loadResult();
			break;
			case 'module':
				$db->setQuery('SELECT id FROM #__modules WHERE module='.$db->Quote($data['name']).' AND client='.$db->Quote($data['client']));
				$r=$db->loadResult();
			break;
			case 'plugin':
				$db->setQuery('SELECT id FROM #__plugins WHERE plugin='.$db->Quote($data['name']).' AND client='.$db->Quote($data['insertion_client']).' AND `group`='.$db->Quote($data['group']));
				$r=$db->loadResult();
			break;
			case 'cron':
			case 'webservice':
				$db->setQuery('SELECT id FROM #__'.$data['type'].'s WHERE '.$data['type'].'='.$db->Quote($data['name']));
				$r=$db->loadResult();
			break;
			case 'widget':
				$db->setQuery('SELECT id FROM #__pages_widgets_resource WHERE filename='.$db->Quote($data['name']));
				$r=$db->loadResult();
			break;
			case 'language':
			case 'template':
			case 'imageset':
				$db->setQuery('SELECT id FROM #__'.$data['type'].'s WHERE name='.$db->Quote($data['name']).' AND client='.$db->Quote($data['client']));
				$r=$db->loadResult();
			break;
			case 'library':
				return false;
			break;
		}
		if($r!==null){
			return 'ERROR_DB_EXISTS';
		}
		return false;
		
	}
	
	function checkDependencies($ext){
		$group = array();
		if(isset($ext->dependencies->dependency)){
			foreach($ext->dependencies->dependency as $d){
				$r = @$this->checkExistence(array('client'=>(string)$d['client'], 'insertion_client'=>(string)$d['client'], 'type'=>(string)$d['type'],'name'=>(string)$d['name'],'group'=>(string)$d['group']));
				if(isset($d['id'])){
					if(!isset($group[(string)$d['id']])){
						$group[(string)$d['id']]=array();
					}
					$group[(string)$d['id']][]=@array($r,(string)$d['client'],(string)$d['type'],(string)$d['name'],(string)$d['archive']);
				}else{
					$group[]=@array(array($r,(string)$d['client'],(string)$d['type'],(string)$d['name'],(string)$d['archive']));
				}
			}
		}
		return $group;
	}
	
	function checkDependenciesError($ext){
		$dep = $this->checkDependencies($ext);
		$deps = array();
		foreach($dep as $dc){
			if(count($dc)>1){
				$done = false;
				foreach($dc as $_dc){
					if($_dc[0]!=false){
						$done=true;
						break;
					}
				}
				if($done == false){
					$deps[]=$dc;
				}
			}else{
				if($dc[0][0]==false){
					$deps[]=$dc;
				}
			}
		}
		return $deps;
	}
}
 
?>