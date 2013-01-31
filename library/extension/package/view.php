<?php 
/**
 * ArtaPackageView class (Used for MVC Standards)
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaPackage
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
//only if on arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaPackageView class
 * Supports Views for MVC Development modes.
 * All views must extend this class.
 */
class ArtaPackageView{
	
	/**
	 * View Layout
	 *
	 * @var	string
	 */
	var $layout = 'default';

	/**
	 * View Name
	 *
	 * @var	string
	 */
	var $name = '';

	/**
	 * View Type
	 *
	 * @var	string
	 */
	var $type = 'html';

	/**
	 * Assigned Variables
	 *
	 * @var	array
	 */
	var $assignments = array();

	/**
	 * Constructor
	 *
	 * @param	string	$name	View Name
	 * @param	string	$type	View Type
	 */
	function __construct($name, $type=false){
		$this->name = $name;
		$this->type = $type ? $type : getVar('type', 'html', '', 'string');
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
	 * Sets view layout
	 *
	 * @param	string	$layout	Layout name
	 */
	function setLayout($layout){
		$this->layout = $layout;
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

	/**
	 * Renders layout. This must be called in view classes.
	 * @param	string	$layout	Layout name
	 */
	function render($layout=null){
		$this->name = ArtaFilterinput::safeAddress($this->name);
		if($layout==null){
			$lay = ArtaFilterinput::safeAddress($this->layout);
		}else{
			$lay = ArtaFilterinput::safeAddress($layout);
		}
		$file=ARTAPATH_PACKDIR.'/views/'.$this->name.'/layouts/'.$lay.'.php';
		if(is_file($file) == false){
			ArtaError::show(500, 'View Layout not found.');
		}else{
			require $file;
		}
	}


	/**
	 * Loads Helper then returns it's instance
	 *
	 * @param	string	$name	Helper name
	 * @return	object
	 */
	function getHepler($name=null){
		$name = $name==null? $this->name : $name;
		$pkg=ArtaLoader::Package();
		$res = $pkg->getHelper($name);
		return $res;
	}

	/**
	 * Assigns variables
	 *
	 * @param	string	$var	 variable name
 	 * @param	mixed	$value	 variable value
	 */
	function assign($var, $value){
		$this->assignments[$var] = $value;
	}

	/**
	 * Gets an assigned variable
	 *
	 * @param	string	$var	 variable name
 	 * @param	mixed	$value	 variable value
	 * @return	mixed
	 */
	function get($var, $default=''){
		if(isset($this->assignments[$var])){
			return $this->assignments[$var];
		}else{
			return $default;
		}
	}

	/**
	 * Refer to ArtaPackageController::getSetting()
	 */
	function getSetting($what, $default=null, $n=null, $c=CLIENT){
		$pkg=ArtaLoader::Package();
		return $pkg->getSetting($what, $default, $n, $c);
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
	 * Returns current user row
	 * @return	object
	 */
	function getCurrentUser(){
		$u=ArtaLoader::User();
		return $u->getCurrentUser();
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

}
?>