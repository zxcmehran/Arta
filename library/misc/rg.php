<?php
/**
 * Register Globals and Magic Quotes Handler
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 2 2013/09/10 22:14 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
//Check arta
if(!defined('ARTA_VALID')){
	die('No access');
}

/**
 * ArtaRG Class
 * Checks inputs and emulates register_globals and magic_quotes_gpc to OFF.
 * 
 * @static
 */
class ArtaRG {

	/**
	 * Checks script input for illegal variables.
	 * 
	 * @static
	 * @param array	One of magic variables, e.g. $_SERVER or $_REQUEST
	 */
	static function checkInputArray(&$array){
		static $banned = array('_files', '_env', '_get', '_post', '_cookie', '_server',
	'_session', 'globals');

		foreach($array as $key=> $value){
			// PHP GLOBALS injection bug
			$failed = in_array(strtolower($key), $banned);
			// PHP Zend_Hash_Del_Key_Or_Index bug
			$failed |= is_numeric($key);
			if($failed){
				die('Illegal variable <b>'.implode('</b> or <b>', $banned).'</b> or a numeric var passed to script.');
			}
		}
	}

	/**
	 * Emulates register globals = off at cost of unsetting all global variables defined until execution of the method.
	 * Should be called at the very beggining of the script to avoid data loss.
	 * Only works if register_globals is currently set on.
	 * 
	 * @static
	 */
	static function unregisterGlobals(){
		if(ini_get('register_globals') == '0')
			return;

		self::checkInputArray($_FILES);
		self::checkInputArray($_ENV);
		self::checkInputArray($_GET);
		self::checkInputArray($_POST);
		self::checkInputArray($_COOKIE);
		self::checkInputArray($_SERVER);

		if(isset($_SESSION)){
			self::checkInputArray($_SESSION);
		}

		$REQUEST = $_REQUEST;
		$GET = $_GET;
		$POST = $_POST;
		$COOKIE = $_COOKIE;
		if(isset($_SESSION)){
			$SESSION = $_SESSION;
		}
		$FILES = $_FILES;
		$ENV = $_ENV;
		$SERVER = $_SERVER;
		foreach($GLOBALS as $key=> $value){
			if($key != 'GLOBALS'){
				unset($GLOBALS [$key]);
			}
		}
		$_REQUEST = $REQUEST;
		$_GET = $GET;
		$_POST = $POST;
		$_COOKIE = $COOKIE;
		if(isset($SESSION)){
			$_SESSION = $SESSION;
		}
		$_FILES = $FILES;
		$_ENV = $ENV;
		$_SERVER = $SERVER;
	}

	/**
	 * Strips slashes in vars; maybe string or array. Used in Magic Quotes emulation
	 * 
	 * @static
	 * @param	mixed	$var	var to strip slashes in it.
	 * @return	mixed
	 */
	static function stripAllSlashes($var){
		$var = is_array($var) ?
				array_map(array('ArtaRG', 'stripAllSlashes'), $var) :
				stripslashes($var);

		return $var;
	}

	/**
	 * Disables Magic Quotes GPC if its enabled.
	 * 
	 * @static
	 * @uses	ArtaRG::stripAllSlashes()
	 */
	static function disableMagicQuotes(){
		if(!get_magic_quotes_gpc()){
			return;
		}
		foreach($_GET as $k=> $v){
			$_GET[$k] = self::stripAllSlashes($v);
		}
		foreach($_POST as $k=> $v){
			$_POST[$k] = self::stripAllSlashes($v);
		}
		foreach($_REQUEST as $k=> $v){
			$_REQUEST[$k] = self::stripAllSlashes($v);
		}
		foreach($_COOKIE as $k=> $v){
			$_COOKIE[$k] = self::stripAllSlashes($v);
		}
	}

}

?>