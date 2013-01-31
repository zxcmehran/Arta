<?php 
/**
 * Controller class of packages is included in this file.
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
 * ArtaPackageController Class
 * Supports Controllers for MVC Development modes.
 * This class must be extended by package's controller class.
 */
 
class ArtaPackageController{
	
	/**
	 * Settings cache variable that will be filled by getSetting().
	 * It must be static because it will be used in ArtaPackage and ArtaPackageController.
	 * 
	 * @staticvar	array
	 * @access	protected
	 */
	protected static $cache_setting=array();

	/**
	 * Executes Methods from Package's Controller
	 *
	 * @param	string	$what	method name
	 */
	function exec($what){
		$methods = get_class_methods($this);
		if(in_array($what, $methods)){
			eval('return $this->'.$what.'();');
		}else{
			ArtaError::show(500, 'Method "'.$what.'" is not exists to do that.');
		}
	}

	/**
	 * Gets package name
	 *
	 * @return	string
	 */
	function getPackage(){
		$package=ArtaLoader::Package();
		return $package->getPackage();
	}

	/**
	 * Loads View then returns it's instance
	 *
	 * @param	string	$name	View name
	 * @param	string	$type	View type
	 * @param	bool	$setDoctype	Set $type as Doctype or not
	 * @return	object
	 */
	function getView($name, $type="html", $setDoctype=true){
		$name= ArtaFilterinput::safeAddress((string)$name);
		$type= ArtaFilterinput::safeAddress((string)$type);
		$pkg=$this->getPackage();
		$class = ucfirst($pkg).'View'.ucfirst($name);
		
		if(!ArtaLoader::Import('views->'.$name.'->'.$name.'.'.$type, 'package')){
			ArtaError::show(500, 'View "'.$name.'" with type "'.$type.'" not found.');
		}
		
		if(class_exists($class)){
			$res = new $class($name,$type);
			if($setDoctype){
				$this->setDoctype($type);
			}
			return $res;
		}else{
			ArtaError::show(500, 'View Class "'.$class.'" not found.');
		}
			
	}
	
	/**
	 * Sets Output document type.
	 * @param	string	$type	Doctype
	 */
	function setDoctype($type){
		$p=ArtaLoader::Template();
		$p->setType($type);
	}
	
	/**
	 * Loads Controller then returns it's instance
	 *
	 * @param	string	$name	Controller name
	 * @return	object
	 */
	function getController($name=null){
		$name= ArtaFilterinput::safeAddress($name);
		$pkg=$this->getPackage();
		
		if($name==null){
			$class = ucfirst($pkg).'Controller';
			$path='controller';
			$name='default';
		}else{
			$class = ucfirst($pkg).'Controller'.ucfirst($name);
			$path='controllers->'.$name;
		}

		if(!ArtaLoader::Import($path,'package')){
			ArtaError::show(500, 'Controller "'.$name.'" not found.');
		}
		
		$res = new $class;
		return $res;	
	}

	/**
	 * Loads Model then returns it's instance
	 *
	 * @param	string	$name	Model name
	 * @return	object
	 */
	function getModel($name){
		$name= ArtaFilterinput::safeAddress($name);
		$pkg=$this->getPackage();
		$class = ucfirst($pkg).'Model'.ucfirst($name);

		if(!ArtaLoader::Import('models->'.$name, 'package')){
			ArtaError::show(500, 'Model "'.$name.'" not found.');
		}
		
		$res = new $class($name);
		return $res;	
	}

	/**
	 * Loads Helper then returns it's instance
	 *
	 * @param	string	$name	Helper name
	 * @return	object
	 */
	 function getHelper($name){
	 	$name= ArtaFilterinput::safeAddress($name);
		$pkg=$this->getPackage();
		$class = ucfirst($pkg).'Helper'.ucfirst($name);

		if(!ArtaLoader::Import('helpers->'.$name, 'package')){
			ArtaError::show(500, 'Helper "'.$name.'" not found.');
		}
		
		$res = new $class($name);
		return $res;
	}

 	/**
	 * Gets settings from Database
	 *
	 * @param	string	$what	Variable to get value
	 * @param	string	$default	Default value to pass if no records were at database
	 * @param	string	$pack	package name
	 * @param	string	$client	Site or Administrator panel settings? you can use "site" or "admin"
	 * @return	mixed	$what value in database
	 */
	function getSetting($what, $default=null, $pack=null, $client=CLIENT){
		if($pack == null){
			$pack = $this->getPackage();
		}
		if(isset(ArtaPackageController::$cache_setting[$client][$pack])){
			if(isset(ArtaPackageController::$cache_setting[$client][$pack][$what])){
				$result = unserialize(ArtaPackageController::$cache_setting[$client][$pack][$what]);
			}else{
				$result = $default;
			}
		}else{
			$res=ArtaCache::getData('package_setting', $client.'_'.$pack);
			if(!$res){
				$db =ArtaLoader::DB();
				$query="SELECT * FROM #__settings WHERE extname=".$db->Quote($pack)." AND extype='package' AND client= ".$db->Quote($client);
				$db->setQuery($query);
				$r = $db->loadAssocList();
				if($r == null){
					$r=array();
				}
				$res=array();
				foreach($r as $k=>$v){
					$res[$v['var']]=$v['value'];
				}
				ArtaCache::putData('package_setting', $client.'_'.$pack, $res);
			}
			ArtaPackageController::$cache_setting[$client][$pack] = $res;
			if(!isset($res[$what])){
				$result = $default;
			}else{
				$result = unserialize($res[$what]);
			}
			
		}
		return $result;
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
	 * Gets Current User data
	 * @return	object	User DB Row
	 */
	function getCurrentUser(){
		$u=ArtaLoader::User();
		return $u->getCurrentUser();
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

}
?>