<?php
/**
 * Prepares application by importing essentials.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 2 2013/09/10 22:15 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(php_sapi_name() == 'cli'){
	die('ERROR: Arta is not a command line compatible framework.');
}/* elseif($_SERVER['SERVER_PROTOCOL']!='HTTP/1.1'){
  header('HTTP/1.0 505 HTTP Version Not Supported');
  die('ERROR: Use HTTP/1.1 protocol.');
  } */

@set_time_limit(30);

/**
 * Directory Separator <b>/</b> or <b>\</b> (According to platform)
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Path to Arta root directory
 */
define('ARTAPATH_BASEDIR', strlen(ARTAPATH_CLIENTDIRNAME) > 0 ?
				substr(ARTAPATH_CLIENTDIR, 0, strlen(ARTAPATH_CLIENTDIR) - strlen(ARTAPATH_CLIENTDIRNAME) - 1) :
				ARTAPATH_CLIENTDIR);

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
if(version_compare(phpversion(), '5.0.0', '<')){
	// ONLY phpversion() >= 5.0.0
	die('We are sorry but you must have at least PHP 5.0.0 to run Arta.');
}

/**
 * Loads Essentials to run Application
 */
function _loadEssentials(){
	define('ARTA_VALID', true);

	if(ini_get('output_buffering')){
		ob_end_clean();
	}
	//error reporting
	error_reporting(E_ALL | E_STRICT); //STRICT IS ENABLED BY DEFAULT IN ARTA
	ini_set('display_errors', 'On');

	chdir(ARTAPATH_BASEDIR);

	require_once (ARTAPATH_LIBRARY.'/loader.php');

	// AT FIRST register globals
	if(!ArtaLoader::Import('misc->rg')){
		die('register_globals processor not found.');
	}

	// force register_globals = off
	ArtaRG::unregisterGlobals();

	// force magic_quotes_gpc  = off
	ArtaRG::disableMagicQuotes();

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


	$debug = ArtaLoader::Debug();
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

	if(!isset($GLOBALS['CACHE'])){
		/**
		 * Runtime cache
		 */
		$GLOBALS['CACHE'] = array();
	}

	$debug->report('Pre-execution processes finished.', '_loadEssentials');
}

?>