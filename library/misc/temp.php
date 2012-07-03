<?php
/**
 * This file contains ArtaTemp Class.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(!defined('ARTA_VALID')){die('No access');}

/**
 * A class to store and manage data on temp directory.
 * This storage is permanent until tmp directory contents are not deleted.
 */
class ArtaTemp{
	
	/**
	 * Stores Data on Temp directory
	 * @param	string	$name	File name
	 * @param	mixed	$data	Data to store
	 * @return	bool
	 */
	static function putData($name, $data){
		$name=ArtaFile::safeName($name);
		$data=serialize($data);
		if($name!==null){
			$d='<?php if(!defined(\'ARTA_VALID\')){die(\'No access\');} $data=\''.ArtaFilteroutput::PHPValue($data).'\'; ?>';
			$r = ArtaFile::write(ARTAPATH_CLIENTDIR.'/tmp/temp_'.$name.'.php', $d);
			return $r;
		}
		return false;	
	}
	
	/**
	 * Reads data stored on Temp directory
	 * @param	string	$name	File name
	 * @return	mixed	False on failure and data on success.
	 */
	static function getData($name){
		$name=ArtaFile::safeName($name);
		if($name!==null){
			//$r = ArtaFile::read(ARTAPATH_CLIENTDIR.'/tmp/temp_'.$name.'.php');
			@include ARTAPATH_CLIENTDIR.'/tmp/temp_'.$name.'.php';
			return @unserialize($data);
		}
		return false;	
	}
	
	/**
	 * Removes data stored on Temp directory
	 * @param	string	$name	temp file name to delete
	 * @param	bool	$all_clients	Remove this temp file from all clients or only current client?
	 * @return	bool
	 */
	static function clearData($name, $all_clients=false){
		$name=ArtaFile::safeName($name);
		if($name!==null){
			if(!$all_clients){
				$name=ARTAPATH_CLIENTDIR.'/tmp/temp_'.$name.'.php';
				if(!file_exists($name)){
					return true;
				}
				$r = ArtaFile::delete($name);
				return $r;
			}else{
				$name1=ARTAPATH_SITE.'/tmp/temp_'.$name.'.php'; // should be many clients compatible. and others like exists()
				$name2=ARTAPATH_ADMIN.'/tmp/temp_'.$name.'.php';
				if(!file_exists($name1)){
					$r=true;
				}else{
					$r = ArtaFile::delete($name1);
				}
				if($r==true){
					if(!file_exists($name2)){
						$r=true;
					}else{
						$r = ArtaFile::delete($name2);
					}
					return $r;
				}
			}
		}
		return false;	
	}
	
	/**
	 * Checks whether is a temp file stored on Temp directory or not.
	 * @param	string	$name	File name
	 * @param	string	$client	Client to examine it's temp folder
	 * @return	bool
	 */
	static function exists($name, $client=CLIENT){
		return file_exists((CLIENT=='admin'?ARTAPATH_ADMIN:ARTAPATH_SITE).'/tmp/temp_'.$name.'.php');
	}
}

?>