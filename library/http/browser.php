<?php 
/**
 * ArtaBrowser is defined in this file
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
 * ArtaBrowser Class
 * Browser detection tools.
 * 
 * @static
 */
class ArtaBrowser {

	/**
	 * Detemines Mobile browsers
	 * @param	string	$userAgent	User agent string. leave null to use $_SERVER['HTTP_USER_AGENT'].
	 * @static
	 * @return	mixed
	 */
	static function isMobile($userAgent=null){
		$mobileClients = array(
			"midp",
			"opera mini",
			"opera mobi",
			"android",
			"symb", // symbian should be symb because opera mobile 10.1
			"s60",
			"windows phone",
			"windows ce",
			"iphone",
			"ipod",
			"ipad",
			"webos",
			"netfront",
			"bada",
			"semc-browser",
			"playstation portable",
			"nintendo wii",
			"nokiabrowser",
			"meego",
			"firefox mini",
			"maemo",			
			"240x320",
			"blackberry",
			"netfront",
			"nokia",
			"panasonic",
			"portalmmm",
			"sharp",
			"sie-",
			"sonyericsson",
			"benq",
			"mda",
			"mot-",
			"philips",
			"pocket pc",
			"sagem",
			"samsung",
			"sda",
			"sgh-",
			"vodafone",
			"xda"
		);
		if($userAgent==null)
			$userAgent = strtolower(@$_SERVER['HTTP_USER_AGENT']);

		foreach($mobileClients as $mobileClient) {
			if (strstr($userAgent, $mobileClient)) {
				return $mobileClient;
			}
		}
		return false;
	}
	
	
	/**
	 * Determines that browser is Webkit or not.
	 * @param	string	$userAgent	User agent string. leave null to use $_SERVER['HTTP_USER_AGENT'].
	 * @static
	 * @return	bool
	 */
	static function isWebkit($userAgent=null){
		if($userAgent==null)
			$userAgent = strtolower(@$_SERVER['HTTP_USER_AGENT']);
		if (strstr($userAgent, 'webkit')) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Gets accepting languages
	 * @param	string	$acceptLang	a value like $_SERVER['HTTP_ACCEPT_LANGUAGE']. leave null to use $_SERVER['HTTP_ACCEPT_LANGUAGE'] itself.
	 * @static
	 * @return	array
	 */
	static function getLanguages($acceptLang=null){
		if($acceptLang==null && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			$acceptLang=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
		
		if(trim($acceptLang)==''){
			return array();
		}else{
			$langs = explode(',', $acceptLang);
			$r=array();
			$_r=array();
			foreach($langs as $l){
				$pos=strpos($l, ';q=');
				if($pos!==false){
					$rate=floatval(substr($l, $pos+3))*100;
					$r[$rate]=substr($l, 0, $pos);
				}else{
					$rate=100;
					$_r[100]=$l;
				}
			}
			krsort($r, SORT_NUMERIC);
			return (array)array_merge($_r, $r);
		}
	}
	
	/**
	 * Determines Platform
	 * @param	string	$agent	User agent string. leave null to use $_SERVER['HTTP_USER_AGENT'].
	 * @static
	 * @return	string
	 */
	static function getPlatform($userAgent=null){
		if($userAgent==null)
			$userAgent = strtolower(@$_SERVER['HTTP_USER_AGENT']);
		
        if (strpos($userAgent, 'android') !== false) {
            return 'android';
        } elseif (strpos($userAgent, 'iphone') !== false || strpos($userAgent, 'ipad') !== false || strpos($userAgent, 'ipod') !== false) {
            return 'ios';
        } elseif (strpos($userAgent, 'windows phone') !== false || strpos($userAgent, 'windows ce') !== false) {
            return 'windows phone';
        } elseif (strpos($userAgent, 'webos') !== false) {
            return 'webos';
        } elseif (strpos($userAgent, 'bada') !== false) {
            return 'bada';
        } elseif (strpos($userAgent, 'symb') !== false) {
            return 'symbian';
        } elseif (strpos($userAgent, 'mac') !== false) {
            return 'mac';
        } elseif (strpos($userAgent, 'wind') !== false) {
            return 'windows';
        } elseif (strpos($userAgent, 'midp') !== false) {
            return 'midp';
        } else {
            return 'linux';
        }
    }
    
    /**
     * Determines robots (googlebot,slurp,msnbot,...)
     * @param	string	$userAgent	User agent string. leave null to use $_SERVER['HTTP_USER_AGENT'].
     * @static
     * @return	mixed
     */
    static function isRobot($userAgent=null){
    	$robots = array(
	        'Googlebot',
	        'msnbot',
	        'Slurp',
	        'Yahoo',
	        'Arachnoidea',
	        'ArchitextSpider',
	        'Ask Jeeves',
	        'B-l-i-t-z-Bot',
	        'Baiduspider',
	        'BecomeBot',
	        'cfetch',
	        'ConveraCrawler',
	        'ExtractorPro',
	        'FAST-WebCrawler',
	        'FDSE robot',
	        'fido',
	        'geckobot',
	        'Gigabot',
	        'Girafabot',
	        'grub-client',
	        'Gulliver',
	        'HTTrack',
	        'ia_archiver',
	        'InfoSeek',
	        'kinjabot',
	        'KIT-Fireball',
	        'larbin',
	        'LEIA',
	        'lmspider',
	        'Lycos_Spider',
	        'Mediapartners-Google',
	        'MuscatFerret',
	        'NaverBot',
	        'OmniExplorer_Bot',
	        'polybot',
	        'Pompos',
	        'Scooter',
	        'Teoma',
	        'TheSuBot',
	        'TurnitinBot',
	        'Ultraseek',
	        'ViolaBot',
	        'webbandit',
	        'www.almaden.ibm.com/cs/crawler',
	        'ZyBorg',
  		 );
  		 
  		 if($userAgent==null)
			$userAgent = strtolower(@$_SERVER['HTTP_USER_AGENT']);
		
  		 foreach($robots as $robot){
	  		 if(strpos($userAgent, strtolower($robot))!==false){
	  		 	return strtolower($robot);
	  		 }
  		 }
  		 return false;
    }
    
    
}

?>