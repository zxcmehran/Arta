<?php 
/**
 * ArtaDomain is defined in this file
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaDomain Class
 * Domain handler.
 * 
 */
class ArtaDomain {
	
	private $domains = array();
	private $packages = array();
	
	function __construct(){
		if(CLIENT != 'site' || $this->isMainDomainSet()==false){
			return;
		}
		$this->loadDomainDescriptions();
		foreach($this->domains as $d=>$p){
			@$this->packages[$p['pack']]=$d;
		}
		// id, address, params, enabled
	}
	
	function initialize(){
		if(CLIENT != 'site' || $this->isMainDomainSet()==false){
			return;
		}
		$server = strtolower($_SERVER['SERVER_NAME']);
		if(isset($this->domains[$server]) AND !defined('XMLRPC_FILE') AND @$_REQUEST['pack']==false){
			// First, add pack in order to let ArtaRequest to compile SEF string.
			ArtaRequest::addVar('pack', $this->domains[$server]['pack']);
		}
	}
	
	function initialize2(){
		if(CLIENT != 'site' || $this->isMainDomainSet()==false){
			return;
		}
		$server = strtolower($_SERVER['SERVER_NAME']);
		$q=ArtaURL::breakupQuery(trim((string)$_SERVER['QUERY_STRING']));
		// remove legal vars
		$_q = $q;
		if(isset($q['language'])) unset($q['language']);
		if(isset($q['tmpl'])) unset($q['tmpl']);
		if(isset($q['template'])) unset($q['template']);
		if(isset($q['imageset'])) unset($q['imageset']);
		if(isset($q['offline_pass'])) unset($q['offline_pass']);
		if(isset($q['limit'])) unset($q['limit']);
		if(isset($q['limitstart'])) unset($q['limitstart']);
		if(isset($q['type']) && $q['type']=='html') unset($q['type']);
		if(isset($q['pack']) && @$this->domains[$server]['pack']==$q['pack']) unset($q['pack']);
		// Now that parameters are processed and no params are existing, add default vars.
		if(isset($this->domains[$server]) AND $q==array()){
			foreach($this->domains[$server] as $var=>$val){
				if($var == 'pack' OR isset ($_q[$var])){
					continue;
				}
				ArtaRequest::addVar($var, $val);
			}
		}
	}
	
	function getDomainOfPackage($pack){
		return @$this->packages[$pack];
	}
	
	function getParamsOfDomain($domain){
		return @$this->domains[$domain];
	}
	
	function getAbsoluteParamsOfDomain($domain){
		$params =  @$this->domains[$domain];
		foreach (array('language', 'imageset', 'template') as $v){
			if(isset($params[$v]))
				unset ($params[$v]);
		}
		return $params;
	}
	
	function loadDomainDescriptions(){
		if(ArtaCache::isUsable('domain','data')){
			$this->domains = ArtaCache::getData('domain','data');
		}else{
			$db = ArtaLoader::DB();
			$db->setQuery('SELECT `address`, `params` FROM #__domains WHERE `enabled`=1');
			$r = (array)$db->loadObjectList();
			$res = array();
			foreach($r as $v){
				$res[strtolower($v->address)] = ArtaString::splitVars($v->params, '&', '=', true);
			}
			$this->domains = $res;
			ArtaCache::putData('domain','data', $res);
		}
	}

	function isMainDomainSet(){
		$c = ArtaLoader::Config();
		return trim($c->main_domain)!='';
	}
	
}

?>