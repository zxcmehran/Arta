<?php 
/**
 * Model classes ofmodules extender is included in this file.
 * This file will be loaded by ({@link	ArtaLoader::ModuleFiles()})
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaModule
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaModuleModel Class
 * Supports Models for MVC Development modes.
 * All models must extend this class.
 */
class ArtaModuleModel{

	/**
	 * Loads Helper then returns it's instance
	 *
	 * @param	string	$name	Helper name
	 * @return	object
	 */
	function getHepler(){
		$mod=ArtaLoader::Module();
		$res = $mod->getHelper();
		return $res;
	}

	/**
	 * Refer to ArtaPackageController::getSetting()
	 */
	function getSetting($what, $default=null, $n=null, $c=CLIENT){
		$mod=ArtaLoader::Module();
		return $mod->getSetting($what, $default, $n, $c);
	}
	
	/**
	 * Loads Model then returns it's instance
	 *
	 * @param	string	$name	Model name
	 * @return	object
	 */
	function getModel(){
		$mod=ArtaLoader::Module();
		$res = $mod->getModel();
		return $res;	
	}
	
	/**
	 * Returns Current user
	 * @return	object
	 */
	function getCurrentUser(){
		$u=ArtaLoader::User();
		return $u->getCurrentUser();
	}

}
?>