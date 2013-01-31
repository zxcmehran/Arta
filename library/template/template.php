<?php 
/**
 * ArtaTemplate Engine.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// detect arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaTemplate Class
 * Template engine for Arta
 */
class ArtaTemplate {

	/**
	 * Template name
	 *
	 * @var	string
	 * @access	protected
	 */
	var $name;

	/**
	 * Template filename e.g. index, package
	 *
	 * @var	string
	 */
	var $tmpl;
	
	/**
	 * Imageset name
	 *
	 * @var	string
	 */
	var $imgset_name;

	/**
	 * contents to output and process. loaded from template file.
	 *
	 * @var	string
	 */
	var $content;
	
	/**
	 * Page title
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $title;

	/**
	 * Template location that is set with addtoTmpl()
	 *
	 * @var	array
	 */
	var $location = array();

	/**
	 * View type
	 *
	 * @var	string
	 */
	var $type='html';

	/**
	 * Generator metatag content
	 *
	 * @var	string
	 * @access	private
	 */
	private $generator='Arta Content Management Framework';
        
        /**
	 * Keywords of page
	 *
	 * @var	string
	 */
	var $keywords='';
        
        /**
	 * Description of page
	 *
	 * @var	string
	 */
	var $description='';

	/**
	 * Document character set
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $charset='UTF-8';

	/**
	 * Page Direction
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $direction='ltr';

	/**
	 * Language full name
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $lang='English';

	/**
	 * Language intial
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $lang_id='en-us';

	/**
	 * Settings cache variable that will be filled by getSetting()
	 *
	 * @var	array
	 * @access	protected
	 */
	protected $cache_settings=array();

	/**
	 * Constructor
	 * Prepares some data
	 */
	function __construct(){
		// some debuggy data!
		$debug = ArtaLoader::Debug();
		$debug->report('ArtaTemplate loaded.', 'ArtaTemplate::__construct');
	}

	/**
	 * Initialize function
	 * Adds some data that couldn't add in __construct
	 */
	function initialize(){
		// Disable browser caching by default
		$this->Header("Expires", "Mon, 1 Jan 2001 00:00:00 GMT");
		$this->Header("Last-Modified", gmdate("D, d M Y H:i:s") . ' GMT');
		$this->Header("Cache-Control", 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->Header("Pragma", 'no-cache');
		
		$preferred = $this->getPreferredTemplate();
		$this->name = ArtaFilterinput::safeAddress($preferred);
		$this->tmpl = ArtaFilterinput::safeAddress(getVar('tmpl', 'index', '', 'string'));
		$this->imgset_name = ArtaFilterinput::safeAddress($this->getPreferredImgSet());
		
		$types=array('html', 'pdf', 'xml', 'raw');
		if(!in_array(getVar('type', $this->type, '', 'string'), $types)){
			ArtaRequest::addVar('type', 'raw');
		}
		
		// some debuggy data!
		$debug = ArtaLoader::Debug();
		$debug->report('Template set to : '.$this->name.' -> '.$this->tmpl, 'ArtaTemplate::__construct');
		
		
		
		$this->charset=trans('_LANG_CHARSET')=='_LANG_CHARSET'?$this->charset:trans('_LANG_CHARSET');
		$this->direction=trans('_LANG_DIRECTION')=='_LANG_DIRECTION'?$this->direction:trans('_LANG_DIRECTION');
		$this->lang=trans('_LANG_NAME');
		$this->lang_id=trans('_LANG_ID');
                
		$config = ArtaLoader::Config();
		$this->keywords = $config->keywords;
		$this->description = $config->description;
		
		// load HTML because we need it always...
		$this->includeViewtype();
		
		// add headers
		$this->Header("X-Powered-By", 'Arta Content Management Framework');
		$this->Header("X-Powered-By-Author", 'Mehran Ahadi');
	}
	
	
	/**
	 * Includes Viewtype's required stuff
	 */
	function includeViewtype(){
		ArtaLoader::Import('template->tags->'.strtolower(ArtaFilterinput::safeAddress($this->type)));
		if($this->type=='html'){
			ArtaTagsHtml::addLibraryScript('prototype');
			ArtaTagsHtml::addLibraryScript('scriptaculous');
			ArtaTagsHtml::addLibraryScript('arta');
		}
		
		$language=ArtaLoader::Language();
		$language->addtoNeed($this->type, 'type');
	}
	
	/**
	 * Sets Template Type
	 * @param	string	$type	Template type to set
	 */
	 function setType($type){
	 	$types=array('html', 'pdf', 'xml', 'raw');
		if(!in_array($type, $types)){
			$type='raw';
		}
	 	$this->type=$type;
	 	$this->includeViewtype();
	 }



	/**
	 * Loads Template file
	 */
	function loadTemplate(){

		$debug = ArtaLoader::Debug();
		$debug->report('Template type : '.$this->type, 'ArtaTemplate::loadTemplate');
		
		// add type langs
		$lang=ArtaLoader::Language();
		$lang->addtoNeed($this->name, 'template');
		
		// import library
		if(ArtaLoader::Import('template->type->'.strtolower(ArtaFilterinput::safeAddress($this->type)))){
			$type = ucfirst($this->type);
			// load library
			eval('ArtaTemplate'.$type.'::loadTemplate();');
		}else{
			$debug->report('Template type '.$this->type.' is not defined in ArtaTemplate Library.', 'ArtaTemplate::loadTemplate');
			ArtaError::show(404, trans('VIEW TYPE NOT FOUND'));
		}
		
		
		if(getVar('tmpltest', null)!==null && $this->type=='html'){
			$con=$this->content;
			preg_match_all('#<artatmpl type=\"(\w*)\" */?>#', $con, $i );
			$this->addtoTmpl('<input type="button" value="Remove location labels" style="position:fixed; top:20px; left:20px;" onclick="if(this.value==\'Remove location labels\'){$$(\'.tmpltest_handler\').each(function(e){e.style.display=\'none\';});this.value=\'Add location labels\';}else{$$(\'.tmpltest_handler\').each(function(e){e.style.display=\'block\';});this.value=\'Remove location labels\';} "/>', 'package');
			foreach($i[1] as $v){
				if(!in_array($v, array('package', 'head', 'afterbody', 'beforebodyend'))){
					$this->addtoTmpl('<div style="display:block;position:absolute;z-index:1002;background:#dddddd; opacity: .50; filter: alpha(opacity=50); -moz-opactiy: .50; color:red;font-weight:bold;border:1px dashed orange;" class="tmpltest_handler">'.$v.'</div>', $v);
				}
			}
			if(getVar('tmpltest', null)!='simple'){
				$this->addtoTmpl('<script>
				function resizehandlers(){
					$$(".tmpltest_handler").each(function(e){
						wid=e.parentNode.getWidth();
						hei=e.parentNode.getHeight();
						if(hei>0){
							e.setStyle({height: hei+"px"});
						}
						if(wid>0){
							e.setStyle({width: wid+"px"});
						}
						
						off=e.parentNode.cumulativeOffset();;
						e.setStyle({top: off.top+"px", left: off.left+"px"});
						})
				}
				resizehandlers();
				</script>', 'beforebodyend');
			}
		}		
	}


	/**
	 * Prepares template to render by placing modules and page contents into template.
	 */
	function prepare(){
		// call render function of template type class
		eval('$this->content = ArtaTemplate'.ucfirst($this->type).'::prepare();');
	}

	/**
	 * Renderer function calls render() at type class.
	 * It will be called after prepare().
	 */
	function render(){
		
		// call render function of template type class
		eval('$this->content = ArtaTemplate'.ucfirst($this->type).'::render();');
		
		if(getVar('xmldebug')){
			header('Content-type: text/xml');
		}

	}
	
	/**
	 * Sends output (rendered template) to client.
	 */
	function toString(){
		// pass content to GZIP Encoder. GZIP activation will be checked in function then returns content and .... FINISH!
		$this->content=trim($this->content);
		echo ArtaFilteroutput::encodeGZOutput($this->content);
	}

	/**
	 * adds content to document locations
	 *
	 * @param	string	$str	content to add
	 * @param	string	$location	target loaction
	 * @return	bool	true
	 */
	function addtoTmpl($str, $location){
		if($location=='head'){
			ArtaTagsHtml::addHeader($str);
			return true;
		}elseif($location=='head-unlocked'){
			$location='head';
		}
		$this->location[$location][]=$str;
		return true;
	}

	/**
	 * adds a HTTP Header
	 *
	 * @param	string	$key		variable name
	 * @param	string	$value	variable value
	 */
	function Header($key, $value=null){
		$file = $line = null;
		if(headers_sent($file,$line)){
			$debug = ArtaLoader::Debug();
			$debug->report('Failed to send header "'.$key.': '.$value.'". Headers are already sent'.($file!=null?' at '.ArtaFile::getRelatedPath($file).'@'.$line:'').'.', 'ArtaTemplate::Header');
			return false;
		}else
			header($key.': '.$value);
		return true;
	}
	
	/**
	 * Counts content blocks assigned to a location.
	 *
	 * @param	string	$loc	location to count it's assignments
	 * @return	int	Blocks count
	 */
	function count($loc){
		if (isset($this->location[$loc])){
			$r= count($this->location[$loc]);
		}else{
			$r= 0;
		}
		$m=ArtaLoader::Module();
		$r +=@(int)count($m->prepared[$loc]);
		if(getVar('tmpltest', null)!==null && $this->type=='html' && $r==0){
			return 1;
		}
		return $r;
	}

	/**
	 * check existence of data in locations
	 *
	 * @param	string	$loc	location to count
	 * @param	string	$data	data to check existed or not
	 * @return	bool
	 */
	function added($loc, $data){
		if (isset($this->location[$loc])){
			return in_array($data, $this->location[$loc]);
		}else{
			return false;
		}
	}

	

 	/**
	 * Gets settings from Database
	 *
	 * @param	string	$what	Variable to get value
	 * @param	string	$default	Default value to pass if no records were at database
	 * @param	string	$tmpl	Template name
	 * @param	string	$client	Site or Administrator panel settings? you can use "site" or "admin"
	 * @return	mixed	$what value in database
	 */	
	function getSetting($what, $default=null, $tmpl=null, $client=CLIENT){
		if($tmpl == null){
			$tmpl = $this->name;
		}
		if(isset($this->cache_setting[$client][$tmpl])){
			if(isset($this->cache_setting[$client][$tmpl][$what])){
				$result = unserialize($this->cache_setting[$client][$tmpl][$what]);
			}else{
				$result = $default;
			}
		}else{
			$res=ArtaCache::getData('template_setting', $client.'_'.$tmpl);
			if(!$res){
				$db =ArtaLoader::DB();
				$query="SELECT * FROM #__settings WHERE extname=".$db->Quote($tmpl)." AND extype='template' AND client= ".$db->Quote($client);
				$db->setQuery($query);
				$r = $db->loadAssocList();
				if($r == null){
					$r=array();
				}
				$res=array();
				foreach($r as $k=>$v){
					$res[$v['var']]=$v['value'];
				}
				ArtaCache::putData('template_setting', $client.'_'.$tmpl, $res);
			}

			$this->cache_setting[$client][$tmpl]= $res;
			if(!isset($res[$what])){
				$result = $default;
			}else{
				$result = unserialize($res[$what]);
			}
			
		}

		return $result;
	}
	
	/**
	 * Sets Page Title
	 * Adds sitename as title suffix.
	 *
	 * @param	string	$title	title to set
	 * @return	bool	false if title set already, else true
	 */

	function setTitle($title){
		$config=ArtaLoader::Config();
		if(CLIENT=='site' && @$_SERVER['IS_HOMEPAGE']==true && $config->homepage_title!=''){
			$this->title=$config->homepage_title.' - '.$config->site_name;
			return true;
		}
		if($this->title == null || $this->title == ' - '.$config->site_name){
			// if empty title
			$this->title = $title.' - '.$config->site_name;
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * returns locations array
	 *
	 * @return	array	locations array
	 */
	function getLocations(){
		return $this->location;
	}
	
	/**
	 * returns page title
	 *
	 * @return	string	page title
	 */
	function getTitle(){
		return $this->title;
	}

	/**
	 * returns Template name
	 *
	 * @return	string	template name
	 */
	function getName(){
		return $this->name;
	}
	
	/**
	 * returns Imageset name
	 *
	 * @return	string	Imageset name
	 */
	function getImgSetName(){
		return $this->imgset_name;
	}

	/**
	 * returns Template Filename
	 *
	 * @return	string	template filename
	 */
	function getTmpl(){
		return $this->tmpl;
	}

	/**
	 * returns Template type (html,pdf,raw,etc.)
	 *
	 * @return	string	template type
	 */
	function getType(){
		return $this->type;
	}

	/**
	 * returns Template charset
	 *
	 * @return	string	template charset
	 */
	function getCharset(){
		return $this->charset;
	}

	/**
	 * returns Template direction
	 *
	 * @return	string	template direction
	 */
	function getDirection(){
		return $this->direction;
	}

	/**
	 * returns Language
	 *
	 * @return	string	language
	 */
	function getLang(){
		return $this->lang;
	}

	/**
	 * returns Language Intials (e.g. en-US or fa-IR )
	 *
	 * @return	string	lang_id
	 */
	function getLangID(){
		return $this->lang_id;
	}

	/**
	 * returns genrator
	 *
	 * @return	string	generator
	 */
	function getGenerator(){
		return $this->generator;
	}
	
	/**
	 * gets User defined template
	 *
	 * @return	string	template name
	 */
	/*function getUserTemplate(){
		$user = ArtaLoader::User();
		$us = $user->getSetting(CLIENT.'_template', $this->getGuestTemplate());
		return $us;
	}*/
	function getUserTemplate(){
		$user = ArtaLoader::User();
		$current = $user->getCurrentUser();
		$domain = ArtaLoader::Domain();
		$params = $domain->getParamsOfDomain($_SERVER['SERVER_NAME']);
		if($current->id>0){
			$us = $user->getSetting(CLIENT.'_template', isset($params['template'])?$params['template']:$this->getGuestTemplate());
		}else{
			$us = isset($params['template'])?$params['template']:$this->getGuestTemplate();
		}
		return $us;
	}

	/**
	 * gets Guest Template that is set by admin
	 *
	 * @return	string	template name
	 */
	function getGuestTemplate(){
		$user = ArtaLoader::User();
		$gs = $user->getSetting(CLIENT.'_template', 'default', 0);
		return $gs;
	}

	/**
	 * returns preferred template
	 *
	 * @return string	 template name
	 */
	function getPreferredTemplate(){
		$res= getVar('template', $this->getUserTemplate(), '', 'string');
		return $res;
	}	
	
	
	/**
	 * gets User defined Imageset
	 *
	 * @return	string	Imageset name
	 */
	/*function getUserImgSet(){
		$user = ArtaLoader::User();
		$us = $user->getSetting(CLIENT.'_imageset', $this->getGuestImgSet());
		return $us;
	}*/
	function getUserImgSet(){
		$user = ArtaLoader::User();
		$current = $user->getCurrentUser();
		$domain = ArtaLoader::Domain();
		$params = $domain->getParamsOfDomain($_SERVER['SERVER_NAME']);
		if($current->id>0){
			$us = $user->getSetting(CLIENT.'_imageset', isset($params['imageset'])?$params['imageset']:$this->getGuestImgSet());
		}else{
			$us = isset($params['imageset'])?$params['imageset']:$this->getGuestImgSet();
		}
		return $us;
	}

	/**
	 * gets Guest Imageset that is set by admin
	 *
	 * @return	string	Imageset name
	 */
	function getGuestImgSet(){
		$user = ArtaLoader::User();
		$gs = $user->getSetting(CLIENT.'_imageset', 'default', 0);
		return $gs;
	}
	
	/**
	 * returns preferred Imageset
	 *
	 * @return string	 Imageset name
	 */
	function getPreferredImgSet(){
		$res= getVar('imageset', $this->getUserImgSet(), '', 'string');
		if(!is_dir(ARTAPATH_CLIENTDIR.'/imagesets/'.ArtaFilterinput::safeAddress($res))){
			$u=ArtaLoader::User();
			$g=$u->getGuest();
			$gs=unserialize($g->settings);
			$u=$u->getCurrentUser();
			$us=unserialize($u->settings);
			$sname=CLIENT.'_imageset';
			if(ArtaFilterinput::safeAddress($res)==@ArtaFilterinput::safeAddress($gs->$sname)){
				$db=ArtaLoader::DB();
				$db->setQuery('UPDATE #__userfields SET `default`='.$db->Quote(serialize('default')).' WHERE `extype`=\'library\' AND `var`='.$db->Quote($sname), array('default'));
				$db->query();
				redirect(ArtaURL::getURL());
			}elseif(ArtaFilterinput::safeAddress($res)==@ArtaFilterinput::safeAddress($us->$sname)){
				$us->$sname=$gs->$sname;
				$db=ArtaLoader::DB();
				$db->setQuery('UPDATE #__users SET settings='.$db->Quote(serialize($us)).' WHERE id='.$u->id, array('settings'));
				$db->query();
				redirect(ArtaURL::getURL());
			}
		}
		return $res;
	}
	
}
?>