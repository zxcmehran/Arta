<?php

/**
 * Package Engine Class that is the most important section of Arta is in this file.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
//only if on arta
if(!defined('ARTA_VALID')) {
	die('No access');
}

ArtaLoader::Import('extension->package->controller');

/**
 * ArtaPackage Class
 * Package engine for Arta. Dispatches packages to Application then runs them with output
 * buffering then ArtaTemplate gets buffered data and places them in desired location.
 */

class ArtaPackage extends ArtaPackageController{

	/**
	 * Package name
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $name;

	/**
	 * Package result buffer
	 *
	 * @var	string
	 */
	var $content;

	/**
	 * Package DB row
	 *
	 * @var	object
	 */
	var $data = null;

	/**
	 * package row cache
	 *
	 * @var	array
	 */
	var $cache = array();

	/**
	 * default package
	 *
	 * @var	string
	 */
	var $default = null;
	
	/**
	 * Indicates that we are in middle of processing pack or not
	 *
	 * @var	bool
	 */
	var $in_process = false;

	/**
	 * Constructor. Some debuggy data only!
	 */
	function __construct() {
		$debug = ArtaLoader::Debug();
		$debug->report('ArtaPackage loaded.',
			'ArtaPackage::__construct');
	}

	/**
	 * Initialize Class
	 */
	function initialize() {

		$this->getDefault();
		$this->name = $this->getPackage();
		if($this->name=='index'){
			ArtaError::show(500, '"index" is an reserved word and cannot be as a package\'s name.');
		}
		//package name could be loaded via ways different from request params.
		//So, we must set 'pack' request param again.
		ArtaRequest::addVar('pack', $this->name);
		define('ARTAPATH_PACKDIR', ARTAPATH_CLIENTDIR.'/packages/'.$this->name);
		ArtaLoader::PackageFiles();

		$dbd = $this->loadDB($this->name);
		if(!isset($dbd->name)) {
			ArtaError::show(404);
		}
		if(!$dbd->enabled) {
			ArtaError::show(403);
		}
		
		if(ArtaUsergroup::processDenied($dbd->denied)==false AND CLIENT=='site'){
			ArtaError::show(403);
		}
		
		$this->data = $dbd;
	}

	/**
	 * Returns default package from DB
	 *
	 * @return	string
	 */
	function getDefault() {
		if($this->default == null) {
			if($GLOBALS['_CLIENTDATA']['DEFAULT_PACKAGE']){
				$this->default=$GLOBALS['_CLIENTDATA']['DEFAULT_PACKAGE'];
			}else{
				$d=ArtaLinks::getDefault();
				if(!$d==true){
					ArtaError::show(500, 'Default link not found.');
				}
				$d=ArtaURL::breakupQuery(substr($d->link, strlen('index.php?')));
				$this->default=ArtaFilterinput::safeAddress($d['pack']);
			}
			
		}

		return $this->default;
	}

	/**
	 * Loads and processes package
	 */
	function load() {
		$debug = ArtaLoader::Debug();
		$debug->report('Start loading Package...',
			'ArtaPackage::load');
		$req = $this->name;
		$dbd = $this->data;

		$lang = ArtaLoader::Language();
		$lang->addtoNeed($req, 'package');
		if(!file_exists(ARTAPATH_CLIENTDIR.'/packages/'.$req.'/'.$req.'.php')) {
			ArtaError::show(404);
		}

		define('PACKAGE_TITLE', $dbd->title);
		$pathway = ArtaLoader::Pathway();
		$pathway->add($dbd->title, 'index.php?pack='.$dbd->name);
		
		$plugin = ArtaLoader::Plugin();
		$plugin->trigger('onBeforeProcessPackage', array(&$dbd));
		
		$this->process($dbd->name);
		$this->addResult();

		
		$plugin->trigger('onAfterProcessPackage', array(&$dbd, &$this->content));

		$template = ArtaLoader::Template();
		$template->setTitle($dbd->title);
	}

	/**
	 * Gets Current package name
	 *
	 * @return	string
	 */
	function getPackage() {
		//  - - - defined in default link
		//  - - defined in $GLOBALS['_CLIENTDATA']['DEFAULT_PACKAGE']
		//  -  defined in getVar('pack');
		$r = ArtaRequest::getVar('pack', $p=$this->getDefault(), '', 'string');
		// If we are on homepage and client has no default package set.
		if(($_SERVER['QUERY_STRING']==null || IS_HOMEPAGE==true) && $GLOBALS['_CLIENTDATA']['DEFAULT_PACKAGE'] == false){
			ArtaLinks::setDefaultVars();
		}
		$r=ArtaFilterinput::safeAddress($r);
		return $r;
	}

	/**
	 * Starts buffering and imports package
	 *
	 * @param	string	$req	 Package Name
	 */
	function process($req) {
		$req = ArtaFilterinput::safeAddress($req);
		$debug=ArtaLoader::Debug();
		$debug->report('Package Processing Started.', 'ArtaPackage::process', true);
		if($this->in_process==false){
			$GLOBALS['DEBUG']['SQL'][]='**** Package Processing started. ****';
			ob_start();
			$this->in_process=true;
			$this->includeFile(ARTAPATH_CLIENTDIR.'/packages/'.$req.'/'.$req.'.php');
		}
	}
	
	/**
	 * Processes files in separated environment
	 */
	function includeFile(){
		include func_get_arg(0);
	}

	/**
	 * Adds result then closes buffer
	 *
	 * @return	bool
	 */
	function addResult() {
		if($this->in_process==true){
			$this->content = ob_get_contents();
			ob_end_clean();
			$GLOBALS['DEBUG']['SQL'][]='**** Package Processing finished. ****';
			$debug=ArtaLoader::Debug();
			$debug->report('Package Processing Finished.', 'ArtaPackage::addResult', true);
			$this->in_process=false;
			return true;
		}
		return false;
		
	}

	/**
	 * Returns result
	 *
	 * @return	string
	 */
	function getResult() {
		return $this->content;
	}

	/**
	 * Returns package specified
	 *
	 * @param	string	$name	Package Name
	 * @return	array
	 */
	function loadDB($name) {
		if($name=='__sandbox'){
			return $this->getSandbox(); 
		}
		$db = ArtaLoader::DB();
		$c = ArtaLoader::Config();
		if($c->cache==false){
			if(!isset($this->cache[$name]) || $this->cache[$name] == null) {
				$db->setQuery("SELECT * FROM #__packages WHERE name=".
					$db->Quote($name));
				$this->cache[$name] = $db->loadObject();
			}
		}elseif($this->cache==array()){
			if(ArtaCache::isUsable('package','items')){
				$this->cache=ArtaCache::getData('package','items');
			}else{
				$db->setQuery("SELECT * FROM #__packages");
				$this->cache=$db->loadObjectList('name');
				ArtaCache::putData('package','items',$this->cache);
			}
		}
		return @$this->cache[$name];
	}
	
	/**
	 * Returns Sandbox package table row.
	 * @return	object
	 */
	function getSandbox(){
		$sb=new stdClass;
		$sb->id=0;
		$sb->name='__sandbox';
		$sb->title='Sandbox package for developers';
		$sb->core=0;
		$sb->enabled=1;
		$sb->denied='';
		return $sb;
	}
}

?>