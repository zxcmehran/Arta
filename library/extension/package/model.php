<?php 
/**
 * Model classes of packages extender is included in this file.
 * This file will be loaded by ({@link	ArtaLoader::PackageFiles()})
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaPackage
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaPackageModel Class
 * Supports Models for MVC Development modes.
 * All models must extend this class.
 */
class ArtaPackageModel{
	
	/**
	 * Model Name.
	 * @var	string
	 */
	var $name;
	
	/**
	 * Sets Model name variable for construction.
	 * @param	string	$name	Model Name
	 */
	function __construct($name){
		$this->name=$name;
		
	}
	
	/**
	 * Loads Helper then returns it's instance
	 *
	 * @param	string	$name	Helper name
	 * @return	object
	 */
	function getHelper($name=null){
		$name = $name==null? $this->name : $name;
	 	$pkg=ArtaLoader::Package();
		$res = $pkg->getHelper($name);
		return $res;	
	}
	/**
	 * Refer to ArtaPackageController::getSetting()
	 */
	function getSetting($what, $default=null, $n=null, $c=CLIENT){
		$pkg=ArtaLoader::Package();
		return $pkg->getSetting($what, $default, $n, $c);
	}
	
	/**
	 * Adds a path to Pathway
	 *
	 * @param	string	$p	Path
	 * @param	string	$link	Path link
	 * @param	int	$level	Path Level
	 */
	function addPath($p, $link=null, $level=null){
		$path=ArtaLoader::Pathway();
		$path->add($p, $link, $level);
	}
	
	/**
	 * Loads Controller then returns it's instance
	 *
	 * @param	string	$name	Controller name
	 * @return	object
	 */
	function getController($name=null){
		$pkg=ArtaLoader::Package();
		$res = $pkg->getController($name);
		return $res;
	}
	
		
	/**
	 * Returns current user row
	 * @return	object
	 */
	function getCurrentUser(){
		$u=ArtaLoader::User();
		return $u->getCurrentUser();
	}
	
	/**
	 * Sets page title
	 *
	 * @param	string	$title	page title
	 */
	function setTitle($title){
		$template=ArtaLoader::Template();
		$template->setTitle($title);
	}
	
/**
	 * Loads View then returns it's instance
	 *
	 * @param	string	$name	View name
	 * @param	string	$type	View type
	 * @param	bool	$setDoctype	Set $type as Doctype or not
	 * @return	object
	 */
	function getView($name=null, $type="html", $setDoctype=true){
		$name = $name==null? $this->name : $name;
		$pkg=ArtaLoader::Package();
		$res = $pkg->getView($name, $type, $setDoctype);
		return $res;	
	}
	
	/**
	 * Loads Model then returns it's instance
	 *
	 * @param	string	$name	Model name
	 * @return	object
	 */
	function getModel($name=null){
		$name = $name==null? $this->name : $name;
		$pkg=ArtaLoader::Package();
		$res = $pkg->getModel($name);
		return $res;	
	}

}
?>