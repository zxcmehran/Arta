<?php
/**
 * Import files needed by application
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

if(php_sapi_name()=='cli'){
	die('ERROR: Arta is not a command line compatible framework.');	
}/*elseif($_SERVER['SERVER_PROTOCOL']!='HTTP/1.1'){
	header('HTTP/1.0 505 HTTP Version Not Supported');
	die('ERROR: Use HTTP/1.1 protocol.');
}*/

@set_time_limit(30);

/**
 * Directory Separator <b>/</b> or <b>\</b> (According to platform)
 */
define( 'DS', DIRECTORY_SEPARATOR);

/**
 * Path to Arta root directory
 */
define('ARTAPATH_BASEDIR', strlen(ARTAPATH_CLIENTDIRNAME)>0 ? substr(ARTAPATH_CLIENTDIR, 0,  strlen(ARTAPATH_CLIENTDIR)-strlen(ARTAPATH_CLIENTDIRNAME)-1): ARTAPATH_CLIENTDIR);

/**
 * ==ARTAPATH_BASEDIR
 */
define('ARTAPATH_SITE', ARTAPATH_BASEDIR);

/**
 * Path to Library root directory
 */ 
define('ARTAPATH_LIBRARY', ARTAPATH_BASEDIR.'/library');

/**
 * Path to Admin root directory
 */
define('ARTAPATH_ADMIN', ARTAPATH_BASEDIR.'/admin');

/**
 * Path to Media directory
 */
define('ARTAPATH_MEDIA', ARTAPATH_BASEDIR.'/media');

/**
 * Indicates that the platform is windows or not
 */
define('IS_WIN', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'));

// check version
if(version_compare(phpversion(), '5.0.0', '<')) {
	// ONLY phpversion() >= 5.0.0
	die('We are sorry but you must have at least PHP 5.0.0 to run Arta.');
}

/**
 * Loads Essentials to run Application
 */
function _loadEssentials() {
	define('ARTA_VALID', true);
	if(ini_get('output_buffering')){
		ob_end_clean();
	}
	//error reporting
	error_reporting(E_ALL | E_STRICT); //STRICT IS ENABLED BY DEFAULT IN ARTA
	ini_set('display_errors', 'On');
    
    chdir(ARTAPATH_BASEDIR);
    
	require_once (ARTAPATH_LIBRARY.'/loader.php');
//	ob_start();
	// AT FIRST register globals
	if(!ArtaLoader::Import('misc->rg')) {
		die('register_globals processor not found.');
	}
//	ob_end_clean();
	
	/**
	 * Force register_globals to on or off.
	 * Defaults off
	 */
	define('RG_EMULATION', '0');
	
	/**
	 * Force magic_quotes to on or off.
	 * Defaults off
	 */
	define('MAGIC_QUOTES_EMULATION', '0');
	
	if(RG_EMULATION == 0) {
		// force register_globals = off
		ArtaRG::unregisterGlobals();
	} elseif(@ini_get('register_globals') !== '1') {
		// php.ini has register_globals = off and emulate = on
		ArtaRG::registerGlobals();
	} else {
		// php.ini has register_globals = on and emulate = on
		// just check for spoofing
		ArtaRG::checkInputArray($_FILES);
		ArtaRG::checkInputArray($_ENV);
		ArtaRG::checkInputArray($_GET);
		ArtaRG::checkInputArray($_POST);
		ArtaRG::checkInputArray($_COOKIE);
		ArtaRG::checkInputArray($_SERVER);

		if(isset($_SESSION)) {
			ArtaRG::checkInputArray($_SESSION);
		}
	}
	
	if(get_magic_quotes_gpc() AND MAGIC_QUOTES_EMULATION==0){
		foreach($_GET as $k=>$v){
			$_GET[$k]=ArtaRG::stripAllSlashes($v);
		}
		foreach($_POST as $k=>$v){
			$_POST[$k]=ArtaRG::stripAllSlashes($v);
		}
		foreach($_REQUEST as $k=>$v){
			$_REQUEST[$k]=ArtaRG::stripAllSlashes($v);
		}
		foreach($_COOKIE as $k=>$v){
			$_COOKIE[$k]=ArtaRG::stripAllSlashes($v);
		}
	}elseif(get_magic_quotes_gpc()==false AND MAGIC_QUOTES_EMULATION==1){
		foreach($_GET as $k=>$v){
			$_GET[$k]=ArtaRG::addAllSlashes($v);
		}
		foreach($_POST as $k=>$v){
			$_POST[$k]=ArtaRG::addAllSlashes($v);
		}
		foreach($_REQUEST as $k=>$v){
			$_REQUEST[$k]=ArtaRG::addAllSlashes($v);
		}
		foreach($_COOKIE as $k=>$v){
			$_COOKIE[$k]=ArtaRG::addAllSlashes($v);
		}
	}
		
	// Requires
	$result = array();
	$result[] = ArtaLoader::Import('application');
    
    ArtaApplication::gotoInstaller();
    
    $result[] = ArtaLoader::Import('misc->debug');
    $result[] = ArtaLoader::Import('version');
	$result[] = ArtaLoader::Import('misc->error');
	$result[] = ArtaLoader::Import('misc->date');
	$result[] = ArtaLoader::Import('misc->cache');
	$result[] = ArtaLoader::Import('http->request');
	$result[] = ArtaLoader::Import('http->url');
	$result[] = ArtaLoader::Import('file->file');
	$result[] = ArtaLoader::Import('file->archive');
	$result[] = ArtaLoader::Import('filter->input');
	$result[] = ArtaLoader::Import('filter->output');
	$result[] = ArtaLoader::Import('misc->links');
	$result[] = ArtaLoader::Import('misc->string');
	$result[] = ArtaLoader::Import('misc->urgentupdater');
	$result[] = ArtaLoader::Import('misc->utf8');
	$result[] = ArtaLoader::Import('misc->utility');
	$result[] = ArtaLoader::Import('user->user');
	$result[] = ArtaLoader::Import('user->usergroup');
	$result[] = ArtaLoader::Import('session->session');
	
	
	$debug= ArtaLoader::Debug();
	$debug->report('Importing essential classes finished.', '_loadEssentials');
	
	
	
	/**
	 * Arta Use agent to be used in network transactions.
	 */
	define('ARTA_USERAGENT', 'Arta/'.ArtaVersion::getVersion().' (PHP/'.phpversion().')');
	ini_set('user_agent', ARTA_USERAGENT);
	
	// load some vars via ...
	$result[] = ArtaUTF8::initialize();

	// some doings
	$result[] = ArtaCache::delExpired();
	ArtaCache::getDataChecksumTable();
	
	//Initialize Session
	$result[] = ArtaSession::initialize();

	if(in_array(false, $result)){
		die('Couldn\'t load essentials.');
	}
	
	// something to define!
	/**
	 * Scripts to add in HTML Header section
	 */
	$GLOBALS['_SCRIPTS'] = array();

	/**
	 * Style sheets to add in HTML Header section
	 */
	$GLOBALS['_CSS'] = array();

	/**
	 * Anything in HTML header
	 */
	$GLOBALS['_HEAD'] = array();


	if(!isset($GLOBALS['DEBUG'])){
		/**
		 * Debuggy data to be used by ArtaDebug.
		 */
		$GLOBALS['DEBUG'] = array();
	}
	
	if(!isset($GLOBALS['CACHE'])){
		/**
		 * Runtime cache
		 */
		$GLOBALS['CACHE'] = array();
	}
	
	$debug->report('Pre-execution processes finished.', '_loadEssentials');
	
}
?>