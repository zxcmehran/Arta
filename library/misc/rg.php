<?php
/**
 * Register Globals Manager
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
 * ArtaRG Class
 * Checks inputs and emulates register_globals
 * 
 * @static
 */

class ArtaRG {
	 /**
	 * Adds an array to the GLOBALS array and checks that the GLOBALS variable is
	 * not being attacked
	 *
	 * @static
	 * @param array
	 * @param boolean True if the array is going to be added to the GLOBALS
	 */

	static function checkInputArray( &$array, $globalise=false ) {
		static $banned = array( '_files', '_env', '_get', '_post', '_cookie', '_server', '_session', 'globals' );

		foreach ($array as $key => $value) {
			$intval = intval( $key );
			// PHP GLOBALS injection bug
			$failed = in_array( strtolower( $key ), $banned );
			// PHP Zend_Hash_Del_Key_Or_Index bug
			$failed |= is_numeric( $key );
			if ($failed) {
				die( 'Illegal variable <b>' . implode( '</b> or <b>', $banned ) . '</b> or a numeric var passed to script.' );
			}
			if ($globalise) {
				$GLOBALS[$key] = $value;
			}
		}
	}

	/**
	 * Emulates register globals = off
	 * @static
	 */
	static function unregisterGlobals () {
		ArtaRG::checkInputArray( $_FILES );
		ArtaRG::checkInputArray( $_ENV );
		ArtaRG::checkInputArray( $_GET );
		ArtaRG::checkInputArray( $_POST );
		ArtaRG::checkInputArray( $_COOKIE );
		ArtaRG::checkInputArray( $_SERVER );

		if (isset( $_SESSION )) {
			ArtaRG::checkInputArray( $_SESSION );
		}
	
		$REQUEST = $_REQUEST;
		$GET = $_GET;
		$POST = $_POST;
		$COOKIE = $_COOKIE;
		if (isset ( $_SESSION )) {
			$SESSION = $_SESSION;
		}
		$FILES = $_FILES;
		$ENV = $_ENV;
		$SERVER = $_SERVER;
		foreach ($GLOBALS as $key => $value) {
			if ( $key != 'GLOBALS' ) {
				unset ( $GLOBALS [ $key ] );
			}
		}
		$_REQUEST = $REQUEST;
		$_GET = $GET;
		$_POST = $POST;
		$_COOKIE = $COOKIE;
		if (isset ( $SESSION )) {
			$_SESSION = $SESSION;
		}
		$_FILES = $FILES;
		$_ENV = $ENV;
		$_SERVER = $SERVER;
	}

	/**
	 * Emulates register globals = on
	 * @static
	 */
	static function registerGlobals() {
		ArtaRG::checkInputArray( $_FILES, true );
		ArtaRG::checkInputArray( $_ENV, true );
		ArtaRG::checkInputArray( $_GET, true );
		ArtaRG::checkInputArray( $_POST, true );
		ArtaRG::checkInputArray( $_COOKIE, true );
		ArtaRG::checkInputArray( $_SERVER, true );

		if (isset( $_SESSION )) {
			ArtaRG::checkInputArray( $_SESSION, true );
		}

		foreach ($_FILES as $key => $value){
			$GLOBALS[$key] = $_FILES[$key]['tmp_name'];
			foreach ($value as $ext => $value2){
				$key2 = $key . '_' . $ext;
				$GLOBALS[$key2] = $value2;
			}
		}
	}
	
	/**
	 * Strips slashes in vars; maybe string or array. Used in Magic Quotes emulation
	 * @static
	 * @param	mixed	$var	var to strip slashes in it.
	 * @return	mixed
	 */
	static function stripAllSlashes($var){
		if(is_array($var)){
			foreach($var as $k=>$v){
				if(!is_array($v)){
					$var[$k]=stripslashes($v);
				}else{
					$var[$k]=ArtaRG::stripAllSlashes($v);
				}
			}
		}else{
			$var=stripslashes($var);
		}
		return $var;
	}
	
	/**
	 * Adds slashes in vars; maybe string or array. Used in Magic Quotes emulation
	 * @static
	 * @param	mixed	$var	var to add slashes in it.
	 * @return	mixed
	 */
	static function addAllSlashes($var){
		if(is_array($var)){
			foreach($var as $k=>$v){
				if(!is_array($v)){
					$var[$k]=addslashes($v);
				}else{
					$var[$k]=ArtaRG::addAllSlashes($v);
				}
			}
		}else{
			$var=addslashes($var);
		}
		return $var;
	}
}

?>