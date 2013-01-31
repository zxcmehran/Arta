<?php 
/**
 * This file contains definition of ArtaUrgentUpdater class.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
// ensure that import.php is loaded
if(!defined('ARTA_VALID')) {
	die('Essentials are not loaded. Please try to load essentials by calling loadEssentials() from import.php');
}

/**
 * ArtaUrgentUpdater class
 * This class retrieves critical fixes should be done from server then applies 
 * the fixes to the code of Arta.
 * It's exclusiveness is in blocking usage of zero-day bugs found on Arta by 
 * hackers. This task will be done by quick fixing process of this class.
 * 
 * @static
 */
class ArtaUrgentUpdater {
	
	/**
	 * Initializes the class then stars doing tasks.
	 * @static
	 */
	static function initialize(){
		self::applyFixes();
		self::loadTodo();
	}
	
	/**
	 * Starts applying fixes necessary.
	 * 
	 * @static
	 * @access	private
	 */
	private static function applyFixes(){
		if(is_file(ARTAPATH_ADMIN.'/tmp/urgentupdatecheckres.tmp') AND self::getLock()==false){
			self::setLock();
			register_shutdown_function(array('ArtaUrgentUpdater', 'unsetLock'));
			
			// let requests passed lock checking in beggining of Arta execution
			// pass and finish the script.
			sleep(3);
			
			$todo = @(array)unserialize(ArtaFile::read(ARTAPATH_ADMIN.'/tmp/urgentupdatecheckres.tmp'));
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
			curl_setopt($ch, CURLOPT_USERAGENT,ARTA_USERAGENT);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_FILETIME, true);
			@set_time_limit(60);
			foreach($todo as $k => $task){
				curl_setopt($ch, CURLOPT_URL, $task['url']);
				$c= @curl_exec($ch);
				if($c!==false AND curl_errno($ch)==0 AND md5($c) == $task['checksum']){
					if(is_file(ARTAPATH_BASEDIR.'/'.$task['file'])){
						$i='';
						while(is_file(ARTAPATH_ADMIN.'/backup/urgent/'.date('Ymd').'/'.$task['file'].$i)){
							$i = (int)$i+1;
						}
						
						if(!ArtaFile::copy(ARTAPATH_BASEDIR.'/'.$task['file'], ARTAPATH_ADMIN.'/backup/urgent/'.date('Ymd').'/'.$task['file'].$i)){
							continue;
						}
					}
					if(!ArtaFile::write(ARTAPATH_BASEDIR.'/'.$task['file'], $c)){
						continue;
					}
					$mtime = curl_getinfo($ch, CURLINFO_FILETIME);
					if($mtime>0){
						@touch(ARTAPATH_BASEDIR.'/'.$task['file'], $mtime);
					}
					
					unset($todo[$k]);
				}
				if(curl_getinfo($ch, CURLINFO_HTTP_CODE)=='404'){
					unset($todo[$k]);
				}
			}
			if(count($todo)>0){
				ArtaFile::write(ARTAPATH_ADMIN.'/tmp/urgentupdatecheckres.tmp', serialize($todo));
			}else{
				ArtaFile::delete(ARTAPATH_ADMIN.'/tmp/urgentupdatecheckres.tmp');
			}
		}
	}
	
	/**
	 * Loads to-do list of updates from server
	 * 
	 * @static
	 * @access	private
	 */
	private static function loadTodo(){
		if(is_file(ARTAPATH_ADMIN.'/tmp/urgentupdatecheck.tmp')){
			$time=ArtaFile::read(ARTAPATH_ADMIN.'/tmp/urgentupdatecheck.tmp');
		}else{
			$time=0;
		}
		if(time()-$time > 86400){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://cc.artaproject.com/arta/urgent.php?version='.ArtaVersion::getVersion());
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
			curl_setopt($ch, CURLOPT_USERAGENT,ARTA_USERAGENT);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			// grab URL
			$c= @curl_exec($ch);
					
			ArtaFile::write(ARTAPATH_ADMIN.'/tmp/urgentupdatecheck.tmp', time()-(($c==false OR curl_errno($ch)!=0)?64800:0));
				
			if($c!==false AND curl_errno($ch)==0){
				ArtaLoader::Import('#xml->simplexml');
				$xml=@ArtaSimpleXML::parseString($c);
				$todo = array();
				if($xml AND isset($xml->change)){
					foreach($xml->change as $v){
						if((!isset($v['before']) OR version_compare($v['before'], ArtaVersion::VERSION, '>=')) AND (!isset($v['after']) OR version_compare($v['after'], ArtaVersion::VERSION, '<=')) AND (!is_file(ARTAPATH_BASEDIR.'/'.$v['file'])  OR md5_file(ARTAPATH_BASEDIR.'/'.$v['file'])==@$v['old_checksum'])){
							$todo [] = array('url'=>trim((string)$v), 'file'=>(string)$v['file'], 'checksum'=>(string)$v['new_checksum']);
						}
					}
				}
				if(count($todo)>0){
					ArtaFile::write(ARTAPATH_ADMIN.'/tmp/urgentupdatecheckres.tmp', serialize($todo));
				}
				
			}
		}
	}
	
	/**
	 * Removes updater lock file. It's not private because it's going to be
	 * registered as a shutdown function.
	 * 
	 * @static
	 */
	static function unsetLock(){
		ArtaFile::delete(ARTAPATH_ADMIN.'/tmp/urgentupdatelock.tmp');
	}
	
	/**
	 * Sets updater lock file. It's required to use a lock file to avoid 
	 * synchronous update processes in high traffic situations.
	 * 
	 * @access	private
	 * @static
	 */
	private static function setLock(){
		ArtaFile::write(ARTAPATH_ADMIN.'/tmp/urgentupdatelock.tmp', 'lock');
	}
	
	/**
	 * Checks existence of update lock file.
	 * 
	 * @access	private
	 * @static
	 */
	private static function getLock(){
		$if = is_file(ARTAPATH_ADMIN.'/tmp/urgentupdatelock.tmp');
		if($if==false){
			return false;
		}
		$mf = ArtaFile::getModified(ARTAPATH_ADMIN.'/tmp/urgentupdatelock.tmp');
		if($mf < time()-300){
			ArtaFile::delete(ARTAPATH_ADMIN.'/tmp/urgentupdatelock.tmp');
			return false;
		}
		return true;
	}
	
}

?>