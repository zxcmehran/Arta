<?php
/**
 * This file contains functions and classes for loading library classes. 
 *
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */


if(!defined('ARTA_VALID')) {
	die('No access');
}

/**
 * PHP5 Autoload function
 * This function will try include a class file on calling that when not included.
 */

function __autoload($class) {
	$lib = ArtaLoader::findLibrary($class);
	if($lib!=false){
		$debug = ArtaLoader::Debug();
		$debug->report('Tried to include library file containing "'.$class.'" class using __autoload() function. It\'s recommended to include file manually.', '__autoload()');
		ArtaLoader::Import($lib);
	}
    
	return;
}

/**
 * ArtaLoader Class
 * Contains object manufacturer methods.
 * 
 * @static
 */
class  ArtaLoader {
	
	/**
	 * Contains list of library dir files.
	 * 
	 * @staticvar
	 */
	private static $files = null;
	
	/**
	 * Contains index of external library classes
	 * 
	 * @staticvar
	 */
	private static $external_libraries = null;
	
	/**
	 * Finds php file of a class in library.
	 * 
	 * @param	string	$class	Class name to find it's file.
	 * @return	mixed	string of file address (imploded by ->) or false on failure.
	 */
	static function findLibrary($class){
		if(strpos($class, 'Arta') !== 0){
			return self::findExternalLibrary($class);
		}
		$class = strtolower(substr($class, 4));
		switch($class){
	        case 'err':
	            $class='error';
	            break;
	        case 'filterinput':
	            $class='input';
	            break;
	        case 'filteroutput':
	            $class='output';
	        break;
	        case 'str':
	            $class='string';
	        break;
	    }
	    
		ArtaLoader::Import('file->file');
		if(is_file(ARTAPATH_LIBRARY.'/'.$class.'.php')){
			return $class;
		}
		
		if(self::$files==null){
			self::$files = (array)ArtaFile::listDir(ARTAPATH_LIBRARY);
		}
		
		foreach(self::$files as $v) {
			if(is_file(ARTAPATH_LIBRARY.'/'.$v.'/'.$class.'.php')) {
				return $v.'->'.$class;
			}
		}
		return false;
	}
	
	/**
	 * Finds php file of an external class in library using lib.ini file.
	 * 
	 * @param	string	$class	Class name to find it's file.
	 * @return	mixed	string of file address (imploded by ->) or false on failure.
	 */
	static function findExternalLibrary($class){
		if(self::$external_libraries==null){
			ArtaLoader::Import('misc->string');
			self::$external_libraries = @(array)ArtaString::parseINI(ARTAPATH_LIBRARY.'/external/lib.ini', '#', true);
		}
		return @self::$external_libraries[$class]?'external->'.self::$external_libraries[$class]:false;
	}

	/**
	 * Script Importer
	 * Includes files. NOTE: if you want to require add '#' sign at first of $file. e.g. '#file->file'
	 * 
	 * @static
	 * @param	string	$file	path to file. use '->' instead of directory separator.
	 * @param	string	$from	base path to start exploring. Valid values: library, base, client, package, module 
	 * @return	bool
	 */
	static function Import($file, $from = "library") {

		if((string)$file==''){
			return false;
		}
		if($file{0} == "#") {
			$die = true;
			$file = substr($file, 1);
		} else {
			$die = false;
		}
		
		$file = str_replace('->', '/', $file);
		switch($from) {
			case 'library':
			default:
				$pre = ARTAPATH_LIBRARY;
				break;
			case 'base':
				$pre = ARTAPATH_BASEDIR;
				break;
			case 'client':
				$pre = ARTAPATH_CLIENTDIR;
				break;
			case 'package':
				$pre = ARTAPATH_PACKDIR;
				break;
			case 'module':
				$m=self::Module();
				$pre = ARTAPATH_CLIENTDIR.'/modules/'.ArtaFilterinput::safeAddress($m->name);
				unset($m);
				break;
			case 'media':
				$c=ArtaLoader::Config();
				unset($c);
				$pre = ARTAPATH_MEDIA;
				break;
		}
		$_=$pre.'/'.$file.'.php';
		unset($pre);
		unset($file);
		unset($from);
		if(!file_exists($_)) {
			if(class_exists('ArtaError') && class_exists('ArtaDebug')){
				$debug = ArtaLoader::Debug();
				$die ? ArtaError::show(500, 'File "'.$_.'" not exists.') :
				$debug->report('"'.$_.'" Not found.', 'ArtaLoader::Import()');
			}else{
				if($die){
					die('File "'.$_.'" not exists.');
				}else{
					echo('File "'.$_.'" not exists.');
				}
			}
			return false;
		} else {
			unset($die);
			require_once($_);
			return true;
		}
	}

	/**
	 * Import ArtaDB
	 * 
	 * @static
	 * @return	object
	 */
	static function DB() {
		global $artamain;
		if(isset($artamain->DB)) {
			$artamain->DB->die=false;
			return $artamain->DB;
		} else {
			$config = ArtaLoader::Config();
			ArtaLoader::Import('db->db');
			if(!in_array($config->db_type, array('mysql', 'mysqli'))){
				die('Invalid DB Type.');
			}
			ArtaLoader::Import('db->type->'.$config->db_type);
			$creator = '$artamain->DB = new ArtaDB_'.$config->db_type."('".$config->db_host.
				"', '".$config->db_user."', '".$config->db_pass."', '".$config->db_name."');";
			eval($creator);
			$artamain->DB->setPrefix($config->db_prefix);
			
			/*$artamain->DB->setQuery('SHOW TABLE STATUS LIKE \'#__packages\'');
			$tbl= $artamain->DB->loadObject();
			if(@strtolower($tbl->Engine)!=='myisam'){
				ArtaError::show(500, 'Invalid database tables. All tables should be available and use MyISAM storage engine.');
			}*/
	      
			return $artamain->DB;
		}
	}

	/**
	 * Import Package related files
	 * @static
	 */
	static function PackageFiles() {
		ArtaLoader::Import('#extension->package->controller');
		ArtaLoader::Import('#extension->package->view');
		ArtaLoader::Import('#extension->package->model');
		ArtaLoader::Import('#extension->package->helper');
		if(file_exists(ARTAPATH_PACKDIR.'/controller.php')){
			ArtaLoader::Import('controller', 'package');
		}

	}
	
	/**
	 * Import Module related files
	 * @static
	 */
	static function ModuleFiles() {
		ArtaLoader::Import('#extension->module->model');
		ArtaLoader::Import('#extension->module->helper');
	}

	/**
	 * Application Launcher
	 *
	 * @static
	 * @return	object	application instance
	 */
	static function Application() {
		ArtaLoader::Import('#application');
        ArtaApplication::start();
        
	}

	/**
	 * Import ArtaPackage
	 *
	 * @static
	 * @return	object
	 */
	static function Package() {
		global $artamain;
		if(isset($artamain->package)) {
			return $artamain->package;
		} else {
			ArtaLoader::Import('#extension->package->package');
			$artamain->package = new ArtaPackage;
			return $artamain->package;
		}
	}

	/**
	 * Import ArtaUser
	 *
	 * @static
	 * @return	object
	 */
	static function User() {
		global $artamain;
		if(isset($artamain->user)) {
			return $artamain->user;
		} else {
			ArtaLoader::Import('#user->user');
			$artamain->user = new ArtaUser;
			return $artamain->user;
		}
	}

	/**
	 * Import ArtaPathway
	 *
	 * @static
	 * @return	object
	 */
	static function Pathway() {
		global $artamain;
		if(isset($artamain->pathway)) {
			return $artamain->pathway;
		} else {
			ArtaLoader::Import('#misc->pathway');
			$artamain->pathway = new ArtaPathway;
			return $artamain->pathway;
		}
	}

	/**
	 * Import ArtaPlugin
	 *
	 * @static
	 * @return	object
	 */
	static function Plugin() {
		global $artamain;
		if(isset($artamain->plugin)) {
			return $artamain->plugin;
		} else {
			ArtaLoader::Import('#extension->plugin->plugin');
			$artamain->plugin = new ArtaPlugin;
			return $artamain->plugin;
		}
	}

	/**
	 * Import ArtaDebug
	 *
	 * @static
	 * @return	object
	 */
	static function Debug() {
		global $artamain;
		if(isset($artamain->debug)) {
			return $artamain->debug;
		} else {
			ArtaLoader::Import('#misc->debug');
			$artamain->debug = new ArtaDebug;
			return $artamain->debug;
		}
	}

	/**
	 * Import ArtaMail
	 *
	 * @static
	 * @return	object
	 */
	static function Mail() {
		global $artamain;
		if(isset($artamain->mail)) {
			return $artamain->mail;
		} else {
			ArtaLoader::Import('#mail->mail');
			$artamain->mail = new ArtaMail;
			return $artamain->mail;
		}
	}

	/**
	 * Import ArtaModule
	 *
	 * @static
	 * @return	object
	 */
	static function Module() {
		global $artamain;
		if(isset($artamain->module)) {
			return $artamain->module;
		} else {
			ArtaLoader::Import('#extension->module->module');
			$artamain->module = new ArtaModule;
			return $artamain->module;
		}
	}

	/**
	 * Import ArtaCron
	 *
	 * @static
	 * @return	object
	 */
	static function Cron() {
		global $artamain;
		if(isset($artamain->cron)) {
			return $artamain->cron;
		} else {
			ArtaLoader::Import('#extension->cron->cron');
			$artamain->cron = new ArtaCron;
			return $artamain->cron;
		}
	}

	/**
	 * Import ArtaConfig
	 *
	 * @static
	 * @return	object
	 */
	static function Config() {
		global $artamain;
		if(!is_object($artamain)){
			$artamain=new stdClass;
		}
		if(isset($artamain->config)) {
			return $artamain->config;
		} else {
			ArtaLoader::Import('#config', 'base');
			$artamain->config = new ArtaConfig;
			return $artamain->config;
		}
	}

	/**
	 * Import ArtaLanguage
	 *
	 * @static
	 * @return	object
	 */
	static function Language() {
		global $artamain;
		if(isset($artamain->lang)) {
			return $artamain->lang;
		} else {
			ArtaLoader::Import('#language->language');
			$artamain->lang = new ArtaLanguage;
			return $artamain->lang;
		}
	}

	/**
	 * Import ArtaTemplate
	 *
	 * @static
	 * @return	object
	 */
	static function Template() {
		global $artamain;
		if(isset($artamain->template)) {
			return $artamain->template;
		} else {
			ArtaLoader::Import('#template->template');
			$artamain->template = new ArtaTemplate;
			return $artamain->template;
		}
	}

	/**
	 * Import ArtaFTP
	 *
	 * @static
	 * @return	object
	 */
	static function FTP() {
		global $artamain;
		if(isset($artamain->ftp)) {
			return $artamain->ftp;
		} else {
			ArtaLoader::Import('#file->ftp->ftp');
			$artamain->ftp = new ArtaFTP();
			return $artamain->ftp;
		}
	}

	/**
	 * Import ArtaPDF
	 *
	 * @static
	 * @return	object
	 */
	static function PDF() {
		global $artamain;
		if(isset($artamain->pdf)) {
			return $artamain->pdf;
		} else {
			ArtaLoader::Import('#pdf->pdf');
			$artamain->pdf = new ArtaPDF();
			return $artamain->pdf;
		}
	}
	
	/**
	 * Import ArtaDomain
	 *
	 * @static
	 * @return	object
	 */
	static function Domain() {
		global $artamain;
		if(isset($artamain->domain)) {
			return $artamain->domain;
		} else {
			ArtaLoader::Import('#http->domain');
			$artamain->domain = new ArtaDomain();
			return $artamain->domain;
		}
	}
		
	/**
	 * Import OEmbed Processor class
	 *
	 * @static
	 * @return	object
	 */
	static function OEmbed(){
		global $artamain;
		if(isset($artamain->oembed)) {
			return $artamain->oembed;
		} else {
			ArtaLoader::Import('#misc->oembed');
			$artamain->oembed = new ArtaOEmbed();
			return $artamain->oembed;
		}
		
	}

	/**
	 * Import XMLRPC
	 *
	 * @static
	 * @return	object
	 */
	static function XMLRPC() {
		global $artamain;
		if(isset($artamain->xmlrpc)) {
			return $artamain->xmlrpc;
		} else {
			ArtaLoader::Import('#xmlrpc->xmlrpc');
			$artamain->xmlrpc = new ArtaXMLRPC();
			return $artamain->xmlrpc;
		}
	}

	/**
	 * Import ArtaWebService
	 *
	 * @static
	 * @return	object
	 */
	static function WebService() {
		global $artamain;
		if(isset($artamain->websrv)) {
			return $artamain->websrv;
		} else {
			ArtaLoader::Import('#extension->webservice->webservice');
			$artamain->websrv = new ArtaWebService();
			return $artamain->websrv;
		}
	}
	
}

//out of classes
/**
 * to avoid long function!
 *
 * @param	string	$url	URL to redirect
 * @param	string	$msg	message of redirection
 * @param	string	$msgType	tip, warning or error
 * @param	bool	$make	make url
 */
function redirect($url, $msg = '', $msgType = 'tip') {
	ArtaApplication::redirect($url, $msg, $msgType);
}

/**
 * to avoid long function!
 *
 * @param	string	$msg	message of redirection
 * @param	string	$msgType	tip, warning or error
 */
function enqueueMessage($msg, $msgType = 'tip') {
	ArtaApplication::enqueueMessage($msg, $msgType);
}

/**
 * to avoid long function!
 *
 * @param	string	$var	 phrase to translate
 * @return	string	translated
 */
function trans($var) {
	$lang = ArtaLoader::Language();
	return $lang->translate($var);
}

/**
 * to avoid long function!
 *
 * @param	string	$name	variable to get
 * @param	mixed	$default	default value if var unavailable
 * @param	string	$hash	var type
 * @param	string	$type	value type to clean var
 * @return	mixed	var value
 */
function getVar($name, $default = null, $hash = 'default', $type='default') {
	return ArtaRequest::getVar($name, $default, $hash, $type);
}

/**
 * to avoid long function!
 *
 * @param	string	$url	url to prepare
 * @return	string	prepared url!
 */
function makeURL($url){
	return ArtaURL::make($url);
}

/**
 * to avoid long function!
 *
 * @param	string	$image	Image Name
 * @return	string
 */
function Imageset($url){
	return ArtaURL::Imageset($url);
}


?>