<?php 
/**
 * Version Processor 
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
// ensure that import.php is loaded
if(!defined('ARTA_VALID')) {
	die('No access');
}

/**
 * ArtaVersion Class
 * Processes Version string
 * @static
 */
class ArtaVersion{

	/**
	 * Arta version
	 */
	const VERSION = '1.0.0b2';

	/**
	 * Returns Arta credits
	 * 
	 * @static
	 * @param	bool	$link	Make author's homepage as HTML-Link or not
	 * @param	bool	$showVersion	Show arta version?
	 * @param	bool	$showAuthor	Show arta's author name?
	 * @return	string	Credits
	 */
	static function getCredits($link = true, $showVersion=false, $showAuthor=true) {				
		if($link == true) {
			return 'Arta '.($showVersion?self::getVersion().' ':'').
				($showAuthor?'by Mehran Ahadi ':'').'(<a href="http://www.artaproject.com/" target="_blank">www.artaproject.com</a>)';
		} else {
			return 'Arta '.($showVersion?self::getVersion().' ':'').
				($showAuthor?'by Mehran Ahadi ':'').'(www.artaproject.com)';
		}
	}
	
	/**
	 * Returns Arta Logo URL
	 * @static
	 */
	static function getLogo() {
		return 'imagesets/default/arta.png';
	}

	/**
	 * Just like ArtaVersion::getFullVersion()
	 * @static
	 */
	static function getVersion() {
		return self::VERSION;
	}

	/**
	 * Returns full version
	 * 
	 * @static
	 * @return	string	Version number
	 */
	static function getFullVersion() {
		return self::getVersion();
	}

	/**
	 * Returns sub version (version second digit)
	 * 
	 * @static
	 * @return	string	sub Version number
	 */
	static function getSubVersion() {
		$v = explode('.', self::getFullVersion());
		return $v[1];
	}

	/**
	 * Returns sub sub version (version third digit)
	 * 
	 * @static
	 * @return	string	sub sub Version number
	 */
	static function getSubSubVersion() {
		$v = explode('.', self::getFullVersion());
		return $v[2];
	}

	/**
	 * Returns Main version (version first digit)
	 * 
	 * @static
	 * @return	string	main Version number
	 */
	static function getMainVersion() {
		$v = explode('.', self::getFullVersion());
		return $v[0];
	}

}

?>