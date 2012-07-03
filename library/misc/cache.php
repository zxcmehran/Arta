<?php 
/**
 * ArtaCache Class
 * Cache engine for Arta
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaCache class
 * Caching tool.
 * 
 * @static
 */
class ArtaCache{

	/**
	 * Cached items map
	 * @access	private
	 * @staticvar	array
	 */
	private static $map=null;

	/**
	 * Gets cached data if cache is enabled. Data is loaded from "tmp/cache/{$group}.{$name}.php" 
	 * e.g. "tmp/cache/mypackage_users.userid1.php"
	 *
	 * @static
	 * @param	string	$group	 Cache group e.g. "mypackage_users"
	 * @param	string	$name	name of cache file e.g. "userid1"
	 * @return	mixed	returns false on failure else returns cache data
	 */
	static function getData($group, $name){
		$group=ArtaFile::safeName($group);
		$name=ArtaFile::safeName($name);
		if(self::isEnabled() && self::isCached($group, $name)){
			
			if(self::isValid($group, $name)==false){
				self::clearData($group, $name);
				return false;
			}
			
			@$d=include(ARTAPATH_CLIENTDIR.'/tmp/cache/'.$group.'.'.$name.'.php');
			if(!$d===false){
				$data=@unserialize($data);
				return $data;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
    
    /**
	 * Puts cached data if cache is enabled. Data will be written to "tmp/cache/{$group}.{$name}.php" 
	 * e.g. "tmp/cache/mypackage_users.userid1.php"
	 *
	 * @static
	 * @param	string	$group	Cache group e.g. "mypackage_users"
	 * @param	string	$name	name of cache file e.g. "userid1"
	 * @param	mixed	$data	data to cache it
	 * @return	bool	returns false on failure else returns true
	 */
	static function putData($group, $name, $data){
		$group=ArtaFile::safeName($group);
		$name=ArtaFile::safeName($name);
		$data=serialize($data);
		if(self::isEnabled()){
			if($name!==null && $group!==null){
				$d='<?php if(!defined(\'ARTA_VALID\')){die(\'No access\');} $data=\''.ArtaFilteroutput::PHPValue($data).'\'; ?>';
				$r = ArtaFile::write(ARTAPATH_CLIENTDIR.'/tmp/cache/'.$group.'.'.$name.'.php', $d);
				if($r==true){
					self::$map[ARTAPATH_CLIENTDIR.'/tmp/cache/'.$group.'.'.$name.'.php']=md5_file(ARTAPATH_CLIENTDIR.'/tmp/cache/'.$group.'.'.$name.'.php');
					self::putDataChecksumTable();
					return true;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
    
    /**
	 * Cleans cached data that you specified. Data is deleted from "tmp/cache/{$group}.{$name}.php" 
	 * e.g. "tmp/cache/mypackage_users.userid1.php"
	 *
	 * @static
	 * @param	string	$group	 Cache group e.g. "mypackage_users"
	 * @param	string	$name	name of cache file e.g. "userid1"
	 * @return	mixed	returns false on failure else returns true
	 */
	static function clearData($group, $name){
		$group=ArtaFile::safeName($group);
		$name=ArtaFile::safeName($name);
		
		if(!is_dir(ARTAPATH_ADMIN.'/tmp/cache')){ArtaFile::mkdir(ARTAPATH_ADMIN.'/tmp/cache');}

		if(file_exists(ARTAPATH_ADMIN.'/tmp/cache/'.$group.'.'.$name.'.php')){
			$r1= ArtaFile::delete(ARTAPATH_ADMIN.'/tmp/cache/'.$group.'.'.$name.'.php');
			if(isset(self::$map[ARTAPATH_ADMIN.'/tmp/cache/'.$group.'.'.$name.'.php'])){
				unset(self::$map[ARTAPATH_ADMIN.'/tmp/cache/'.$group.'.'.$name.'.php']);
				$del=true;
			}
		}else{
			$r1=true;
		}
		
		
		if(!is_dir(ARTAPATH_SITE.'/tmp/cache')){ArtaFile::mkdir(ARTAPATH_SITE.'/tmp/cache');}
		if(file_exists(ARTAPATH_SITE.'/tmp/cache/'.$group.'.'.$name.'.php')){
			$r2= ArtaFile::delete(ARTAPATH_SITE.'/tmp/cache/'.$group.'.'.$name.'.php');
			if(isset(self::$map[ARTAPATH_SITE.'/tmp/cache/'.$group.'.'.$name.'.php'])){
				unset(self::$map[ARTAPATH_SITE.'/tmp/cache/'.$group.'.'.$name.'.php']);
				$del=true;
			}
		}else{
			$r2=true;
		}
		
		if(isset($del)){
			self::putDataChecksumTable();
		}
		
		return ($r1 && $r2);
	}

	/**
	 * Returns cache is enabled or not. Data is got from ArtaConfig
	 *
	 * @static
	 * @return	bool
	 */
	static function isEnabled(){
		$config=ArtaLoader::Config();
		return $config->cache;
	}
    
    /**
	 * Checks that the specified cached data exists or not.
	 *
	 * @static
	 * @param	string	$group	 Cache group e.g. "mypackage_users"
	 * @param	string	$name	name of cache file e.g. "userid1"
	 * @return	bool	returns false on failure else returns true
	 */
	static function isCached($group, $name){
		$group=ArtaFile::safeName($group);
		$name=ArtaFile::safeName($name);
		if(!is_dir(ARTAPATH_CLIENTDIR.'/tmp/cache')){ArtaFile::mkdir(ARTAPATH_CLIENTDIR.'/tmp/cache');}
		return file_exists(ARTAPATH_CLIENTDIR.'/tmp/cache/'.$group.'.'.$name.'.php');
	}
	
	/**
	 * Is cached item valid?
	 * 
	 * @static
	 * @param	string	$group	 Cache group e.g. "mypackage_users"
	 * @param	string	$name	name of cache file e.g. "userid1"
	 * @return	bool	returns false on invalid else returns true
	 */
	static function isValid($group, $name){
		$group=ArtaFile::safeName($group);
		$name=ArtaFile::safeName($name);
		$md5=md5_file(ARTAPATH_CLIENTDIR.'/tmp/cache/'.$group.'.'.$name.'.php');
		if(@self::$map[ARTAPATH_CLIENTDIR.'/tmp/cache/'.$group.'.'.$name.'.php']!=null &&
			self::$map[ARTAPATH_CLIENTDIR.'/tmp/cache/'.$group.'.'.$name.'.php']!=$md5
		){
			return false;
		}
		return true;
	}
	
	/**
	 * Is cached item ready to use? it means:
	 * Is caching enabled? And is this item cached? And is this cached item valid cache file?
	 * Use when you want to decide using cache or getting fresh data.
	 * 
	 * @static
	 * @param	string	$group	 Cache group e.g. "mypackage_users"
	 * @param	string	$name	name of cache file e.g. "userid1"
	 * @return	bool	returns false on failure else returns true
	 */
	static function isUsable($group, $name){
		return (self::isEnabled() && self::isCached($group, $name) && self::isValid($group, $name));
	}

	/**
	 * Sets cached data last modified time so you can prevent them from expiring. Date will be written to "tmp/cache/{$group}.{$name}.php" 
	 * e.g. "tmp/cache/mypackage_users.userid1.php"
	 *
	 * @static
	 * @param	string	$group	 Cache group e.g. "mypackage_users"
	 * @param	string	$name	name of cache file e.g. "userid1"
	 * @param	int	$time	timestamp to set as last modified. if null passed it will be current time!
	 * @return	bool	returns false on failure else returns true
	 */
	static function setLastModified($group, $name, $time=null){
		$group=ArtaFile::safeName($group);
		$name=ArtaFile::safeName($name);
		if(self::isEnabled()){
			if(!is_dir(ARTAPATH_CLIENTDIR.'/tmp/cache')){ArtaFile::mkdir(ARTAPATH_CLIENTDIR.'/tmp/cache');}
			$time=@strtotime($time);
			if(is_int($time) !== true)	{
				$time=time();
			}
			return touch(ARTAPATH_CLIENTDIR.'/tmp/cache/'.$group.'.'.$name.'.php', $time);
		}else{
			return false;
		}
	}
    
    /**
	 * Cleans cached data if cache is expired.
	 *
	 * @static
	 * @param	string	$lifetime	 Lifetime. If null or nothing passed, cache_lifetime from ArtaConfig will be selected
	 *	@return	bool	true
	 */
	static function delExpired($lifetime=0){
		if(!is_int($lifetime) || $lifetime == false){
			$config=ArtaLoader::Config();
			$lifetime=$config->cache_lifetime;
		}
		
		if(!is_dir(ARTAPATH_ADMIN.'/tmp/cache')){ArtaFile::mkdir(ARTAPATH_ADMIN.'/tmp/cache');}
		
		$list=(array)ArtaFile::listDir(ARTAPATH_ADMIN.'/tmp/cache');
		foreach($list as $v){
			if(is_file(ARTAPATH_ADMIN.'/tmp/cache/'.$v) AND filemtime(ARTAPATH_ADMIN.'/tmp/cache/'.$v) < time()-($lifetime)){
				
				ArtaFile::delete(ARTAPATH_ADMIN.'/tmp/cache/'.$v);
				if(isset(self::$map[ARTAPATH_ADMIN.'/tmp/cache/'.$v])){
					unset(self::$map[ARTAPATH_ADMIN.'/tmp/cache/'.$v]);
					$del=true;
				}
			}
		}
		
		if(!is_dir(ARTAPATH_SITE.'/tmp/cache')){ArtaFile::mkdir(ARTAPATH_SITE.'/tmp/cache');}
		
		$list=(array)ArtaFile::listDir(ARTAPATH_SITE.'/tmp/cache');
		foreach($list as $v){
			if(@filemtime(ARTAPATH_SITE.'/tmp/cache/'.$v) < time()-($lifetime)){
				
				ArtaFile::delete(ARTAPATH_SITE.'/tmp/cache/'.$v);
				if(isset(self::$map[ARTAPATH_SITE.'/tmp/cache/'.$v])){
					unset(self::$map[ARTAPATH_SITE.'/tmp/cache/'.$v]);
					$del=true;
				}
			}
		}
		
		if(isset($del)){
			self::putDataChecksumTable();
		}
		
		return true;
	}
	
	/**
	 * Updates Data Checksum table
	 * @access	private
	 * @static
	 */
	private static function putDataChecksumTable(){
		$config=ArtaLoader::Config();

		if(@count(self::$map)==0){
			self::$map = array();
		}
		$content='';
		foreach(self::$map as $address => $data){
			if((strlen($data)&&strlen($address))==false || file_exists($address)==false){
				unset(self::$map[$address]);
			}
		}
		$content=serialize(self::$map);
		
		$checksum=md5($content,true);
		$checksum2=md5($content);
		return file_put_contents(ARTAPATH_ADMIN.'/tmp/cache.map', gzcompress(base64_encode($content).$checksum2).$checksum);
	}
	
	/**
	 * Loads Data Checksum table
	 * @static
	 */
	static function getDataChecksumTable(){
		$config=ArtaLoader::Config();
		if(!file_exists(ARTAPATH_ADMIN.'/tmp/cache.map')){
			ArtaFile::rmdir_extra(ARTAPATH_ADMIN.'/tmp/cache');
			ArtaFile::rmdir_extra(ARTAPATH_SITE.'/tmp/cache');
			if(!is_dir(ARTAPATH_ADMIN.'/tmp/cache')){ArtaFile::mkdir(ARTAPATH_ADMIN.'/tmp/cache');}
			if(!is_dir(ARTAPATH_SITE.'/tmp/cache')){ArtaFile::mkdir(ARTAPATH_SITE.'/tmp/cache');}
			self::$map=array();
			return;
		}
		$data=@file_get_contents(ARTAPATH_ADMIN.'/tmp/cache.map');
		
		if(strlen(trim($data))){			
			$checksum = substr($data, strlen($data)-16);
			$data = substr($data, 0, strlen($data)-16);
			$data_raw = @gzuncompress($data);
			
			$data = base64_decode(substr($data_raw, 0, strlen($data_raw)-32));
			if(md5($data)!= substr($data_raw, strlen($data_raw)-32) || md5($data,true) != $checksum){
				ArtaFile::delete(ARTAPATH_ADMIN.'/tmp/cache.map');
				self::$map=array();
				return;
			}
			
			$data= @(array)unserialize($data);
								
			foreach($data as $k=>$v){
				if(file_exists($k)==false){
					$did=true;
					unset($data[$k]);
				}
			}
			
			if($data!=false){
				self::$map=$data;
				if(isset($did)){
					self::putDataChecksumTable();
				}
			}else{
				ArtaFile::delete(ARTAPATH_ADMIN.'/tmp/cache.map');
				self::$map=array();	
			}
		}else{
			ArtaFile::delete(ARTAPATH_ADMIN.'/tmp/cache.map');
			self::$map=array();
		}
	}
	
}
?>