<?php
/**  
 * ArtaInstaller for Updates
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
 * ArtaInstallerExtension Class
 */
 
class ArtaInstallerUpdate{
	
	
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
	 * Log of process
	 * @var	array
	 */
	var $log=array();

	function __construct($inst){
		$this->inst=$inst;
		$this->xml=$inst->xml;
	}
	
	function Install(){
		ob_start();
		$xml=$this->xml;
		$r=array();

		if(isset($xml['minVersion']) && 
		version_compare(ArtaVersion::getVersion(), $xml['minVersion'], "<")
		){
			return 'ERROR_UPDATE_NOT_SUITABLE_FOR_THIS_VER';
		}
		
		
		$order=array();
		$i=0;
		foreach($xml->update as $k=>$v){
			$order[$i]=$v['ver'];
			$i++;
		}
		uasort($order, 'version_compare');
		
		$new=array();
		foreach($order as $k=>$v){
			// If update ver is more than current ver
			if(version_compare(ArtaVersion::getVersion(), $xml->update[(int)$k]['ver'], "<")){
				$new[$k]=$xml->update[(int)$k];
			}
		}
		
		$this->inst->logs=array();
		
		// to find out are all taken care or something is remaining?
		if(!isset($_SESSION['UPDATE_TODO'])){
			$_SESSION['UPDATE_TODO']=count($new); 
		}
		$todo=$_SESSION['UPDATE_TODO'];
		$done=$_SESSION['UPDATE_TODO']-count($new);
		
		if($todo==0 || count($new)==0){
			return 'ERROR_NOTHING_USEFUL_FOUND';
		}
		$this->inst->todo=$todo;
		foreach($new as $upd){
			$result=true;
			$done++;
			
			$this->inst->installing = array('ver'=>(string)$upd['ver']);
			if(in_array($upd['ver'], $this->inst->installed)){
				continue; // go to next ext
			}
			
			$result=$this->installUpdate($upd);
			if($result===true){
				$this->inst->installed[]=(string)$upd['ver'];
			}else{
				$this->log[]="\nResult: $result \n";
			}
			$this->log[]="\n\n\nEnding Update to ".$upd['ver'];
			if($result !=='ERROR_CANNOT_CREATE_BACKUP_DIR'){
				ArtaFile::write(ARTAPATH_ADMIN.'/backup/'.$upd['ver'].'/update.log', implode("\n", $this->log));
				$f=fopen(ARTAPATH_ADMIN.'/backup/'.$upd['ver'].'/update.log','r');
				if($f){
					echo '<fieldset style="direction:ltr;text-align:left;">';
					echo '<legend>'.$upd['ver'].'</legend><ol>';
					while (!feof($f)) {
						$line = fgets($f, 4096);
						if(trim($line)!==''){
							echo '<li>';
							if(substr($line, 0, 5)=='ERROR'){
								echo '<font color="red"><b>ERROR:</b></font>'.substr($line, 6);
							}elseif(substr($line, 0, 3)=='MSG'){
								echo '<font color="green"><b>MSG:</b></font>'.substr($line, 4);
							}elseif(substr($line, 0, 4)=='SQL:'){
								echo '<font color="#1B41A9"><b>SQL:</b></font>'.substr($line, 4);
							}else{
								echo $line;
							}
							echo '</li>';
						}
						
					}
					echo '</ol></fieldset>';
					fclose($f);
				}

				$this->log=array();
			}

			break;
			
		}
		if($done==$todo && $result===true){
			$this->inst->fully_installed=true;
		}
		
		if(($done==$todo && $result===true) || $result!==true){
			if(isset($_SESSION['UPDATE_TODO'])){
				unset($_SESSION['UPDATE_TODO']); 
			}
		}
		
		$this->inst->content.=ob_get_contents();
		ob_end_clean();
		
		return $result;
	}
	
	function installUpdate($upd){
		//var_dump('updated to '. $upd['ver']);return true;
		$this->log[]="\n\n\nTIME: ".time().' ('.gmdate("Y-m-d H:i:s").' GMT)';
		$this->log[]="MSG: Starting Update to ".$upd['ver'];
		$dc=$this->createBackupDir($upd);
		if($dc==false){
			return 'ERROR_CANNOT_CREATE_BACKUP_DIR';
		}
		
		$mx=$this->moveXML($upd);
		if(!$mx){
			$this->log[]='ERROR: XML_MOVE_FAILED';
			return 'ERROR_MOVE_XML';
		}
		$this->log[]='MSG: Created XML file.';
		
		$uf=$this->updateFiles($upd);
		if(is_string($uf)){
			return $uf;
		}
		
		$db=ArtaLoader::DB();		
		if(isset($upd->install->SQL->query)){
			foreach($upd->install->SQL->query as $q){
				if(isset($q['type']) && $q['type']=='file'){
					if(!is_file($this->inst->path.'/'.$upd['ver'].'/sql/'.$q)){
						$this->log[]='ERROR: SQL_NOT_EXISTS '.str_replace(array("\n", "\r"), ' ', $q);
						return 'ERROR_SQL_NOT_FOUND';
					}
					foreach($db->splitSQL(ArtaFile::read($this->inst->path.'/'.$upd['ver'].'/sql/'.$q)) as $query){
						$this->log[]='SQL: '.str_replace(array("\n", "\r"), ' ', $query);
						$db->setQuery($query);
						if($db->query()==false){
							$this->log[]='ERROR: SQL '.str_replace(array("\n", "\r"), ' ', $query);
							return 'ERROR_SQL_STMT_NOT_EXEC';
						}
					}
				}else{
					$this->log[]='SQL: '.str_replace(array("\n", "\r"), ' ', $q);
					$db->setQuery($q);
					if($db->query()==false){
						$this->log[]='ERROR: SQL '.str_replace(array("\n", "\r"), ' ', $q);
						return 'ERROR_SQL_STMT_NOT_EXEC';
					}
				}
				
			}
			
			foreach($upd->install->SQL->query as $c){
				if(isset($c['type']) && $c['type']=='file'){
					if(ArtaFile::rename($this->inst->path.'/'.$upd['ver'].'/sql/'.$c, ARTAPATH_ADMIN.'/backup/'.$upd['ver'].'/sql/'.$c)==false){
						$this->log[]='ERROR: RENAME_SQL_FILE '.$this->inst->path.DS.$upd['ver'].DS.'sql'.DS.$c.' ::to:: '.ARTAPATH_ADMIN.DS.'backup'.DS.$upd['ver'].DS.'sql'.DS.$c;
						return 'ERROR_MOVING_INSTALL_SQL';
					
					}else{
						$this->log[]='MSG: Renamed file '.$this->inst->path.DS.$upd['ver'].DS.'sql'.DS.$c.' to '.ARTAPATH_ADMIN.DS.'backup'.DS.$upd['ver'].DS.'sql'.DS.$c;
					}
				}
			}
		}
		
		
		if(isset($upd->install->PHP->code)){
			foreach($upd->install->PHP->code as $c){
				if(isset($c['type']) && $c['type']=='file'){
					if(file_exists($this->inst->path.'/'.$upd['ver'].'/php/'.$c)){
						include($this->inst->path.'/'.$upd['ver'].'/php/'.$c);
					}else{
						$this->log[]='ERROR: PHP_NOT_EXISTS '.str_replace(array("\n", "\r"), ' ', $c);
						return 'ERROR_PHP_SCRIPT_NOT_FOUND';
					}
				}else{
					eval($c);
				}
			}
			
			foreach($upd->install->PHP->code as $c){
				if(isset($c['type']) && $c['type']=='file'){
					if(ArtaFile::rename($this->inst->path.'/'.$upd['ver'].'/php/'.$c, ARTAPATH_ADMIN.'/backup/'.$upd['ver'].'/php/'.$c)==false){
						$this->log[]='ERROR: RENAME_PHP_FILE '.$this->inst->path.DS.$upd['ver'].DS.'php'.DS.$c.' ::to:: '.ARTAPATH_ADMIN.DS.'backup'.DS.$upd['ver'].DS.'php'.DS.$c;
						return 'ERROR_MOVING_INSTALL_PHP';
					
					}else{
						$this->log[]='MSG: Renamed file '.$this->inst->path.DS.$upd['ver'].DS.'php'.DS.$c.' to '.ARTAPATH_ADMIN.DS.'backup'.DS.$upd['ver'].DS.'php'.DS.$c;
					}
				}
			}
			
		}

		if($this->updateVer($upd['ver'])==false){
			return 'ERROR_CHANGING_VERSION_VALUE';
		}
				
		return true;
	}
	
	function createBackupDir($upd){
		if(is_dir(ARTAPATH_ADMIN.'/backup/'.$upd['ver'])){
			$pre='_';
			while(is_dir(ARTAPATH_ADMIN.'/backup/'.$pre.$upd['ver'])){
				$pre.='_';
			}
			ArtaFile::rename(ARTAPATH_ADMIN.'/backup/'.$upd['ver'], ARTAPATH_ADMIN.'/backup/'.$pre.$upd['ver']);
		}
		return ArtaFile::mkdir_extra(ARTAPATH_ADMIN.'/backup/'.$upd['ver']);
	}
	
	function moveXML($upd){
		return ArtaFile::write(ARTAPATH_ADMIN.'/backup/'.$upd['ver'].'/update.xml', "<?xml version=\"1.0\" ?>\n<install>\n".$upd->asXML()."\n</install>");
	}
	
	function updateVer($ver){
		$cur= isset($this->curver) ? $this->curver : ArtaVersion::getVersion();
		ArtaFile::chmod(ARTAPATH_LIBRARY.'/version.php', 0644);
		$app=ArtaFile::read(ARTAPATH_LIBRARY.'/version.php');
		$app=str_replace('const VERSION = \''.$cur.'\';','const VERSION = \''.$ver.'\';', $app);
		$res=ArtaFile::write(ARTAPATH_LIBRARY.'/version.php', $app);
		if($res==false){
			$this->log[]='ERROR: WRITE_NEW_VER_VAL';
		}else{
			$this->curver=$ver;
			$db=ArtaLoader::DB();
			$db->setQuery('UPDATE #__extensions_info SET `version`='.$db->Quote($ver).' WHERE core=1', array('version'));
			$db->query();
			$this->log[]='MSG: Version updated to '.$ver;
		}
		return $res;
	}
	
	function updateFiles($upd){
		$r=array();
		if(isset($upd->files->file)){
			$backdir=ARTAPATH_ADMIN.'/backup/'.$upd['ver'].'/oldfiles';
			
			$this->log[]='MSG: Starting to backup files...';
			
			foreach($upd->files->file as $f){
				$current=ArtaFile::replaceSlashes(ARTAPATH_BASEDIR.'/'.$f);
				if(file_exists($current)){
					$currentdir=ArtaFile::getDir(ArtaFile::replaceSlashes($f));
					if(!is_dir($backdir.'/'.$currentdir)){
						if(ArtaFile::mkdir_extra($backdir.'/'.$currentdir)){
							$this->log[]='MSG: Directory created: '.$backdir.DS.$currentdir;
						}else{
							$this->log[]='ERROR: MAKE_DIR '.$backdir.DS.$currentdir;
							$err=true;
							break;
						}
					}
					if(file_exists($backdir.'/'.$f)){
						$f=ArtaFile::getDir($f).'/'.time().'_'.ArtaFile::getFilename($f);
					}
					$ww=ArtaFile::rename($current, $backdir.'/'.$f);
					if($ww==false){
						$this->log[]='ERROR: BACKUP_FILE '.$current.' ::to:: '.$backdir.'/'.$f;
						$err=true;
						break;
					}else{
						$this->log[]='MSG: Backed up file '.$current/*.' to '.$backdir.DS.$f*/;
					}
					
				}
			}
			
			if(@$err===true){
				return 'ERROR_BACKING_UP_FILES';
			}
			
			$this->log[]='MSG: Backing up of files completed.';
			$this->log[]='MSG: Starting to update files...';
			
			foreach($upd->files->file as $f){
				if(file_exists( ARTAPATH_BASEDIR.'/'.$f)){
					ArtaFile::delete( ARTAPATH_BASEDIR.'/'.$f);
				}
				$w= ArtaFile::rename($this->inst->path.'/'.$upd['ver'].'/files/'.$f, ARTAPATH_BASEDIR.'/'.$f);
				if($w==false){
					$this->log[]='ERROR: RENAME_FILE '.$this->inst->path.DS.$upd['ver'].DS.'files'.DS.$f.' ::to:: '.ARTAPATH_BASEDIR.DS.$f;
					$err=true;
				}else{
					$this->log[]='MSG: Updated file '.ARTAPATH_BASEDIR.DS.$f;
				}
			}
			
			if(@$err===true){
				return 'ERROR_UPDATING_FILES';
			}
			
			$this->log[]='MSG: Updating files completed.';
		}
		return true;
	}

	
}
 
?>