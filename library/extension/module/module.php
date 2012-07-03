<?php 
/**
 * This file contains Arta Module Engine
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// arta?
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaModule Class
 * Generates HTML blocks and puts modules contents to them
 */

class ArtaModule{
	
	/**
	 * Enabled or not
	 *
	 * @var	bool
	 */
	var $enabled=true;

	/**
	 * Modules array filled from DB select query
	 *
	 * @var	array
	 * @access	private
	 */
	var $items;

	/**
	 * Prepared modules array that modules are splitted by their location in here
	 *
	 * @var	array
	 * @access	private
	 */
	var $prepared;

	/**
	 * Results array
	 *
	 * @var	array
	 * @access	private
	 */
	private $content = '';

	/**
	 * Module layout
	 *
	 * @var	string
	 */
	var $layout = 'default';

	/**
	 * Assigned Variables
	 *
	 * @var	array
	 */
	var $assignments = array();

	/**
	 * Settings cache variable that will be filled by getSetting()
	 *
	 * @var	array
	 * @access	private
	 */
	private $cache_setting=array();
	
	/**
	 * Constructor. Gathers data then checks template type to try be disabled.
	 */
	function __construct(){
		$debug = ArtaLoader::Debug();
		$debug->report('ArtaModule loaded.', 'ArtaModule::__construct');
		ArtaLoader::ModuleFiles();
	}

	/**
	 * Initialize and set some vars
	 */
	function initialize(){
		$template=ArtaLoader::Template();
		$this->enabled=$template->getType()=='html' ? true : false;
		if($this->enabled==true){
			if(ArtaCache::isUsable('module',CLIENT.'_items')){
				$this->items = ArtaCache::getData('module',CLIENT.'_items');
			}else{
				$this->items = $this->loadDB();
				ArtaCache::putData('module',CLIENT.'_items',$this->items);
			}
		}
	}

	/**
	 * Loads Data from module table from DB
	 *
	 * @return	array
	 */
	function loadDB(){
		$db = ArtaLoader::DB();
		$db->setQuery("SELECT * FROM #__modules WHERE enabled='1' AND client = ".$db->Quote(CLIENT)." ORDER BY `order`,id");
		return (array)$db->loadObjectList();
	}
	
	/**
	 * Filter data to choose those must be included
	 */
	function filterData(){
		if($this->enabled==true){
			$dbd = $this->filterShowat($this->items);
			$dbd = $this->filterUGPerm($dbd);
			$this->prepared = $this->splitLocations($dbd);
		}
	}

	/**
	 * Removes modules that they have no priviliges to run in this package
	 *
	 * @param	array	$dbd	datas have got from DB
	 * @return	array
	 */
	function filterShowat($dbd){
		$p=ArtaLoader::Package();
		$p=$p->loadDB($p->getPackage());
		foreach($dbd as $k => $v){
			if($v->showat !== null && $v->showat !== ''){
				
				if($v->showat{0}=='-'){
					$r=true;
					$v->showat=substr($v->showat, 1);
				}
				$showat = (array)explode(',', $v->showat);
				
				if(!in_array($p->id, $showat) && !isset($r)){
					unset($dbd[$k]);
				}
				if(in_array($p->id, $showat) && isset($r)){
					unset($dbd[$k]);
					unset($r);
				}
			}
		}
		return $dbd;
	}

	/**
	 * Checks Usergroup for deny some modules to load
	 *
	 * @param	array	$dbd	data from DB
	 * @return	array
	 */
	function filterUGPerm($dbd){
		foreach($dbd as $k => $v){
			if(ArtaUsergroup::processDenied($v->denied)==false){
				unset($dbd[$k]);
			}
		}
		return $dbd;
	}

	/**
	 * Splits modules by their locations
	 *
	 * @param	array	$dbd	data from DB
	 * @return	array
	 */
	function splitLocations($dbd){
		$bylocation = array();
		$c=0;
		foreach($dbd as $v){
			$c++;
			$bylocation[$v->location][] = $v;
		}
		$debug = ArtaLoader::Debug();
		$debug->report('Count of modules : '.$c, 'ArtaModule::splitLocations');
		return $bylocation;
	}
	
	/**
	 * Renderer. Renders all modules in HTML Blocks
	 */
	function renderAll(){
		$debug = ArtaLoader::Debug();
		if($this->enabled==true){
			$debug->report('ArtaModule Rendering started.', 'ArtaModule::renderAll');
			
			$t=ArtaLoader::Template();
			$con=$t->content;
			preg_match_all('#<artatmpl type=\"(\w*)\" */?>#', $con, $i );
	
			
			$plugin = ArtaLoader::Plugin();
			$plugin->trigger('onBeforeShowModules', array(&$this->prepared));
		
			$d='';
			$c=0;
			foreach($this->prepared as $k => $v){
				if(in_array($k, $i[1])){
					$d .="<br>\n".htmlspecialchars($k)." : ";
					foreach($v as $k2=>$v2){
						$c++;
						$d .= "<br>\n".($k2+1).'. '.htmlspecialchars($v2->title).' ('.htmlspecialchars($v2->module).")";
					}
					if(count($v)==0){$d .='&lt;nothing!&gt;';}
					$this->show($k, $v);
				}else{
					$d .="<br>\n".htmlspecialchars($k)." skipped. ";
				}
			}	
			
			$debug->addColumn('Loaded Modules ('.$c.') : '.$d);
			
			$plugin->trigger('onAfterShowModules', array(&$this->prepared, &$this->content));
		}else{
			$debug->report('No modules will be rendered because modules are disabled.', 'ArtaModule::renderAll');
		}
	}
	
	/**
	 * Renders modules at specified locations
	 *
	 * @param	string	$loc	One of Template locations
 	 * @param	array	$items	modules to show
	 */
	function show($loc, $items){
		if($this->enabled==true){
			$content=$this->renderBlocks($items);
		}else{
			$content=array();
		}
		foreach($content as $v){
			$this->content .=$v;
		}
		//$this->content .="";
		$template = ArtaLoader::Template();
		$template->addtoTmpl($this->content, $loc);
		$this->content = '';
	}
	
	
	/**
	 * Renders modules and returns rendered items in an array
	 *
 	 * @param	array	$items	modules to show
 	 * @param	bool	$excludeTitles	Exclude Module titles or not. It only decativates "showtitle" switch if it's already true.
 	 * @param	array	$params	This var will be passed to module base file {@example	/modules/bloglast/bloglast.php}
 	 * @return	array
	 */
	function renderBlocks($items, $excludeTitles=false, $params=array()){
		$lang = ArtaLoader::Language();
		$content = array();
		$plug=ArtaLoader::Plugin();
		if(!is_array($items)){
			$items=array($items);
			$forced_array=true;
		}
		
		foreach($items as $k => $v){
			$k++;
			$content[$k] = '<div class="module"><section>';
			$plug->trigger('onPrepareContent', array(&$v, 'module'));
			if($v->showtitle && $excludeTitles==false){
			$content[$k] .='<header><h3 class="moduletitle">'.htmlspecialchars($v->title).'</h3></header>';
			}
			if($v->content !== '' && substr($v->content, 0,5)!=='MENU:'){
				$plug->trigger('onShowBody', array(&$v->content, 'module'));
				$content[$k] .= '<article>'.$v->content.'</article>';
			}elseif(substr($v->content, 0,5)=='MENU:' && CLIENT=='site'){
				$lang->addtoNeed('linkviewer', 'module', ARTAPATH_BASEDIR);
				$g=substr($v->content, 5);
				$plug->trigger('onShowLinkmenuModule', array(&$g, &$v));
				include(ARTAPATH_BASEDIR.'/modules/linkviewer/linkviewer.php');
			}
			if($v->module == true && $v->module !== '-' && substr($v->content, 0,5)!=='MENU:'){
					
					$this->name=$v->module;
					$this->layout='default'; // reset layout
					$filename = ArtaFilterinput::safeAddress($v->module);
					$file = ARTAPATH_CLIENTDIR.'/modules/'.$filename.'/'.$filename.'.php';
					if(file_exists($file)){
						$lang->addtoNeed($filename, 'module');
						ob_start();
						$this->includeFile($params, $file);	
						$content[$k] .=ob_get_contents();
						ob_end_clean();
					}
			}
			
			$content[$k] .='</section></div>'."\n";
		}
		
		if(isset($forced_array)){
			$content=array_shift($content);
		}
		
		return $content;
	}
	
	
	/**
	 * Processes files in separated environment
	 */
	function includeFile($params){
		include func_get_arg(1);
	}


###########################################
##
##	module-side Methods
##

	/**
	 * Sets module layout
	 *
	 * @param	string	$lay	layout
	 */
	function setLayout($lay){
		$this->layout = $lay;
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
	 * Renders layout
	 */
	function render(){ //$this must be available on layouts. So we don't use ArtaLoader::Import()
		$pre = ARTAPATH_CLIENTDIR.'/modules/'.ArtaFilterinput::safeAddress($this->name);
		include $pre.'/layouts/'.ArtaFilterinput::safeAddress($this->layout).'.php';
	}

	/**
	 * Gets model from /modules/example/module.php then returns it's object
	 *
	 * @return	object	model instance
	 */
	function getModel(){
		$class = 'Module'.ucfirst($this->name).'Model';
		
		if(ArtaLoader::Import('model','module')==false){
			ArtaError::show(500, 'Model "'.'Module'.$this->name.'Model" not found.');
		}
		
		$res = new $class;
		return $res;
	}

	/**
	 * Gets Helper from /modules/example/helper.php then returns it's object
	 *
	 * @return	object	helper instance
	 */
	function getHelper(){
		$class = 'Module'.ucfirst($this->name).'Helper';

		if(ArtaLoader::Import('helper','module')==false){
			ArtaError::show(500, 'Helper "'.'Module'.$this->name.'Helper" not found.');
		}
		$res = new $class;
		return $res;	
	}

 	/**
	 * Gets settings from Database
	 *
	 * @param	string	$what	Variable to get value
	 * @param	string	$default	Default value to pass if no records were at database
	 * @param	string	$mod	module name
	 * @param	string	$client	Site or Administrator panel settings? you can use "site" or "admin"
	 * @return	mixed	$what value in database
	 */
	function getSetting($what, $default=null, $mod=null, $client=CLIENT){
		if($mod == null){
			$mod = $this->name;
		}
		if(isset($this->cache_setting[$client][$mod])){
			if(isset($this->cache_setting[$client][$mod][$what])){
				$result = unserialize($this->cache_setting[$client][$mod][$what]);
			}else{
				$result = $default;
			}
		}else{
			$res=ArtaCache::getData('module_setting', $client.'_'.$mod);
			if(!$res){
				$db =ArtaLoader::DB();
				$query="SELECT * FROM #__settings WHERE extname=".$db->Quote($mod)." AND extype='module' AND client= ".$db->Quote($client);
				$db->setQuery($query);
				$r = $db->loadAssocList();
				if($r == null){
					$r=array();
				}
				$res=array();
				foreach($r as $k=>$v){
					$res[$v['var']]=$v['value'];
				}
				ArtaCache::putData('module_setting', $client.'_'.$mod, $res);
			}
			$this->cache_setting[$client][$mod]=$res;
			if(isset($res[$what])){
				$result = unserialize($res[$what]);
			}else{
				$result = $default;
			}
		}
		return $result;
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