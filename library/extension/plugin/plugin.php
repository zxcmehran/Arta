<?php 
/**
 * This file contains ArtaPlugin Class.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaPlugin Class
 * Plug-In engine for Arta. You can Register methods to events or add events 
 * to your codes by using this helpful class. Then you can create plugins for codes.
 */
class ArtaPlugin{
	
	/**
	 * Registered events
	 *
	 * @var	array
	 * @access	private
	 */
	private $registered = array();
	
	/**
	 * Runtime registered events
	 *
	 * @var	array
	 * @access	private
	 */
	private $runtime_registered = array();
	
	/**
	 * Settings Cache
	 *
	 * @var	array
	 * @access	private
	 */
	private $cache_setting = array();

	/**
	 * Current Plugin
	 *
	 * @var	string
	 */
	var $current;

	/**
	 * Handlers map to find handlers' functions
	 *
	 * @var	array
	 */
	var $map=array();
	
	/**
	 * Triggered events. Just for users information.
	 *
	 * @var	array
	 */
	var $triggered=array();
	
	/**
	 * Included files. Just for prevent duplicate inclusion.
	 *
	 * @var	array
	 */
	var $included=array();

	/**
	 * Constructor. only some debuggy data only!
	 */
	function __construct(){
		$debug = ArtaLoader::Debug();
		$debug->report('ArtaPlugin loaded.', 'ArtaPlugin::__construct');
	}

	/**
	 * Initializer; Just populate needed data
	 */
	function initialize(){
		$debug = ArtaLoader::Debug();
		
		if(ArtaCache::isUsable('plugin',CLIENT.'_files')){
			$data = ArtaCache::getData('plugin',CLIENT.'_files');
		}else{
			$data = $this->loadDB();
			if($data == null){
				$data = array();
			}
			ArtaCache::putData('plugin',CLIENT.'_files', $data);
		}
		
		$data = $this->filterUGPerm($data);
		$this->loadFiles($data);
		$debug->report('ArtaPlugin Initialized.', 'ArtaPlugin::initialize');

	}

	/**
	 * Loads files
	 *
	 * @param	array	$data	Data fetched from DB
	 */
	 function loadFiles($data){
	 	ArtaLoader::Import('#xml->simplexml');
		$path = ARTAPATH_CLIENTDIR.'/plugins/';
		$debug = ArtaLoader::Debug();
		$dat=array();
		$l=ArtaLoader::Language();
		foreach($data as $v){
			if($v->client=='*'){
				$path = ARTAPATH_BASEDIR.'/plugins/';
			}else{
				$path = ARTAPATH_CLIENTDIR.'/plugins/';
			}
			$v->group = ArtaFilterinput::safeAddress($v->group);
			$v->plugin = ArtaFilterinput::safeAddress($v->plugin);
			
			if(file_exists($path.$v->group.'/events.'.$v->plugin.'.xml')){
				$xml=@ArtaSimpleXML::parseFile($path.$v->group.'/events.'.$v->plugin.'.xml');
				if($xml==true){
					foreach($xml->event as $vv){
						$callback=(string)$vv;
						if(isset($vv['class'])){
							$callback=array((string)$vv['class'], $callback);
						}
						if(isset($dat[(string)$vv['on']])==false){
							$dat[(string)$vv['on']]=array();
						}
						$dat[(string)$vv['on']]
							[$v->plugin.'|'.$v->group.'|'.$v->client][]=serialize($callback);	
						$this->map[(string)$vv['on']][serialize($callback)]=$v->plugin;
					}
					
				}
				
			}else{
				$debug->report('Plugin '.htmlspecialchars($v->group).' -> '.htmlspecialchars($v->plugin).' not found.', 'ArtaPlugin::loadFiles');
			}
		}
		$this->registered=$dat;
		
	}
	
	/**
	 * Processes files in separated environment
	 */
	function includeFile(){
		include_once func_get_arg(0);
	}

	/**
	 * Loads DB Data. Just enabled and client related plugins...
	 *
	 * @return	array
	 */
	function loadDB(){
		$db = ArtaLoader::DB();
		$db->setQuery("SELECT * FROM #__plugins WHERE enabled='1' AND (client=".$db->Quote(CLIENT)." OR client=".$db->Quote('*').") ORDER BY client,`group`,`order`");
		$r = $db->loadObjectList();
		return $r;
	}

	/**
	 * Blocks denied plugins for the usergroup
	 *
	 * @param	array	$dbd	Data fetched from DB
	 * @return	array	Filtered data.
	 */
	function filterUGPerm($dbd){
		foreach($dbd as $k=>$v){
			if(ArtaUsergroup::processDenied($v->denied)==false){
				unset($dbd[$k]);
			}
		}
		return $dbd;
	}
	
	/**
	 * Calls registered functions of a specified event
	 *
	 * @param	string	$event	 event name e.g. "onBeforeTemplateRender"
	 * @param	string	$args	Arguments to pass to function.
	 * @return	array	array of handlers' results
	 */
	function trigger($event, $args=null)
	{
		if(!in_array($event, $this->triggered)){$this->triggered[]=$event;}
		$result = array ();

		if ($args == null) {
			$args = array ();
		}
		$debug = ArtaLoader::Debug();
		foreach ($this->registered as $e =>$h){
			if($e == $event){
				foreach($h as $k=>$v){
					// $v is functions of $e event in $k file
					// Include file if needed
					if(!in_array($k, $this->included)){
						$this->included[]=$k;
						$k=explode('|',$k);
						if($k[2]=='*'){
							$path = ARTAPATH_BASEDIR.'/plugins/';
						}else{
							$path = ARTAPATH_CLIENTDIR.'/plugins/';
						}
						if(file_exists($path.$k[1].'/'.$k[0].'.php')){
							$l=ArtaLoader::Language();
							$l->addtoNeed($k[0], 'plugin', ARTAPATH_CLIENTDIR);
							$this->includeFile($path.$k[1].'/'.$k[0].'.php');
						}
					}

					foreach($v as $vv){
						$_vv=unserialize($vv);
						if(/*function_exists($vv)*/is_callable($_vv)){
							$this->current=$this->map[$event][$vv];
							$result[] = call_user_func_array($_vv, $args);
							$this->current='';
						}else{
							if(is_array($_vv)){
								$_vv=array_pop($_vv);
							}
							$debug->report('Function not exists : '.htmlspecialchars($_vv), 'ArtaPlugin::trigger');
						}
					}
				}
				break;
			}
		}
		
		foreach ($this->runtime_registered as $e =>$h){
			if($e == $event){
				foreach($h as $vv){
					// $vv is functions of $h event and $e is name of the event
					if(/*function_exists($vv)*/is_callable($vv)){
						$result[] = call_user_func_array($vv, $args);
					}else{
						if(is_array($vv)){
							$vv=array_pop($vv);
						}
						$debug->report('Function not exists : '.htmlspecialchars($vv), 'ArtaPlugin::trigger');
					}
				}
				break;
			}
		}
		
		return $result;
	}

 	/**
	 * Gets settings from Database
	 *
	 * @param	string	$what	Variable to get value
	 * @param	string	$default	Default value to pass if no records were at database
	 * @param	string	$plugin	 Plugin name
	 * @param	string	$client	Site or Administrator panel settings? you can use "site" or "admin"
	 * @return	mixed	$what value in database
	 */
	function getSetting($what, $default=null, $plugin=null, $client=CLIENT){
		if($plugin == null){
			$plugin = $this->current;
			if($plugin==''){
				die('ERROR: Plugin name is needed to get setting.');
			}
		}
		if(isset($this->cache_setting[$client][$plugin])){
			if(isset($this->cache_setting[$client][$plugin][$what])){
				$result = unserialize($this->cache_setting[$client][$plugin][$what]);
			}else{
				$result = $default;
			}
		}else{
			$res=ArtaCache::getData('plugin_setting', $client.'_'.$plugin);
			if(!$res){
				$db =ArtaLoader::DB();
				$query="SELECT * FROM #__settings WHERE extname=".$db->Quote($plugin)." AND extype='plugin' AND (client= ".$db->Quote($client).' OR client=\'*\')';
				$db->setQuery($query);
				$r = $db->loadAssocList();
				if($r == null){
					$r=array();
				}
				$res=array();
				foreach($r as $k=>$v){
					$res[$v['var']]=$v['value'];
				}
				ArtaCache::putData('plugin_setting', $client.'_'.$plugin, $res);
			}

			$this->cache_setting[$client][$plugin]= $res;
			if(!isset($res[$what])){
				$result = $default;
			}else{
				$result = unserialize($res[$what]);
			}
			
		}

		return $result;
	}
	
	
	/**
	 * Assigns a function to an event.
	 * Example: register('onPrepareContent', 'myFunction');  
	 * @param	string	$event	Event name
	 * @param	callback	$funcname	Callback Name
	 */
	function register($event, $funcname){
		$event=(string)$event;
		if(!isset($this->runtime_registered[$event])){
			$this->runtime_registered[$event]=array();
		}
		$this->runtime_registered[$event][]=$funcname;
	}
}

?>