<?php 
/**
 * There are Many useful methods in ArtaURL that is defined here.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
//only if on arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaURL Class
 * URL Processor and tools.
 * 
 * @static
 */
class ArtaURL{
	
	/**
	 * Contains user language
	 * @staticvar	string
	 * @access	private
	 */
	private static $language=null;

	/**
	 * Contains user template
	 * @staticvar	string
	 * @access	private
	 */
	private static $template=null;
	
	/**
	 * Contains user Imageset
	 * @staticvar	string
	 * @access	private
	 */
	private static $imageset=null;
	
	/**
	 * Processes URLs and return ready URLs.
	 * Input must be in this case. index.php?pack=user&view=reset
	 * it must contain "index.php?" and then simple query.
	 * You can use external links (like http://artaproject.com) ... it will pass them out without any modifications...
	 *
	 * ***** NOTE *****
	 * Some URLs match case <pre>(href|src)=('|").*('|")</pre> (<b>href="some_url" or src="some_url"</b>)<br>
	 * and <pre>url\(('|").*('|")\)</pre> (<b>url('some_url')</b>)<br>
	 * and <pre>&lt;param name="movie,src,url" value="some_url"&gt;</pre><br>
	 * are automatically passed to this function but in other places you must manually use it. (you can call makeURL() too.)
	 * ****************
	 * 
	 * It provides:
	 * 1.It remembers "tmpl","template","offline_pass" and "language" vars to add in URL. Use # at first of URL to discard it.
	 * 2.Supports plugins to edit URLs by plugin.
	 * 3.Supports SEF1 and 2 
	 * 4.Supports rewrite method
	 *
	 * @static
	 * @param	string	$url	url to process
	 * @return	string	processed URL.
	 */
	static function make($url){
		$plugin = ArtaLoader::Plugin();
		if($url == null){
			$url='index.php';
		}
		
		$plugin->trigger('onBeforeMakeURL', array(&$url));
		
		if(@($url{0} == '#')){
			$rem=false;
			$url=substr($url,1);
		}
		
		if(@$url{0} == '/' || substr($url,0,2) == './' || 
			substr($url,0,3) == '../' || strtolower(substr($url,0,11)) == 'javascript:'){
			return $url;
		}

		$config = ArtaLoader::config();
		
		if(strpos($url, 'index.php') === 0){
			$hasQuery =true;
		}else{
			$hasQuery =false;
			$inbase=array('media', 'library', 'admin');
			foreach($inbase as $v){
				if(strpos($url, $v) === 0 && !isset($byClient)){
					$byClient =false;
					break;
				}
			}
		}
		
		if(!isset($byClient)){
			$byClient=true;
		}
		
		$p=	self::getDir();
		if(CLIENT !=='site' && !$byClient){
			$sitepath=substr($p, 0, strlen($p)-6); // go back if we are on /admin/ dir.
		}else{
			$sitepath=$p;
		}
		
		if($hasQuery){
			// if query started with "index.php"
			// split Scriptname And Querystring
			$matched = preg_match('@\?(.*)@', $url, $query);
			$domain = ArtaLoader::Domain();
			if(self::$language==null){
				$l=ArtaLoader::Language();
				self::$language = $l->getUserLang();
			}
			if(self::$template==null){
				$t=ArtaLoader::Template();
				self::$template = $t->getUserTemplate();
			}
			if(self::$imageset==null){
				$t=ArtaLoader::Template();
				self::$imageset = $t->getUserImgSet();
			}
			
			// Seperate those url's have query string and those haven't
			if(!$matched){
				if(strpos($url, '#')!==false){
					$hash=explode('#', $query[1]);
					$hash=$hash[1];
				}
				
				$suffix ='';

				if(!isset($rem)){
					if(($v=getVar('tmpl', false, '', 'string')) !== false){
						$suffix .='&tmpl='.$v;
					}
					if(($v=getVar('language', false, '', 'string')) !== false AND self::$language!=$v){
						$suffix .='&language='.$v;
					}
					if(($v=getVar('template', false, '', 'string')) !== false AND self::$template!=$v){
						$suffix .='&template='.$v;
					}
					if(($v=getVar('imageset', false, '', 'string')) !== false AND self::$imageset!=$v){
						$suffix .='&imageset='.$v;
					}
					if(($v=getVar('offline_pass', false, '', 'string')) !== false){
						$suffix .='&offline_pass='.$v;
					}
				}

				if(@($suffix{0}== '&')){
					$suffix=substr($suffix,1);
				}

				if(CLIENT=='site' AND $domain->getParamsOfDomain($_SERVER['SERVER_NAME'])){
					// we are on foreign domain name.
					$sitepath = self::getURL(array('domain'=>$config->main_domain, 'path'=>$sitepath,'path_info'=>'','query'=>''));
				}

				if($suffix == ''){
					$res = $sitepath;
				}else{
					switch(ArtaURL::getConfig()){
						case false:
						default:
							$res = ArtaURL::makeNone($suffix);
						break;
						case '1':
							$res = ArtaURL::makeSEF($suffix);
						break;
						case '2':
							$res = ArtaURL::makeSEF2($suffix);
						break;
					}

					$res = $sitepath.$res;
				}
			}else{

				$query = @$query[1];

				if(strpos($query, '#')!==false){
					$query=explode('#', $query);
					$hash=$query[1];
					$query=$query[0];
				}

				if(!isset($rem)){
					//break the query
					$arr = ArtaURL::breakupQuery($query);

					if(($v=getVar('tmpl', false, '', 'string')) !== false && !isset($arr['tmpl'])){
						$query .='&tmpl='.$v;
					}
					if(($v=getVar('language', false, '', 'string')) !== false  && !isset($arr['language']) && self::$language!=$v){
						$query .='&language='.$v;
					}
					if(($v=getVar('template', false, '', 'string')) !== false  && !isset($arr['template']) && self::$template!=$v){
						$query .='&template='.$v;
					}
					if(($v=getVar('imageset', false, '', 'string')) !== false  && !isset($arr['imageset']) && self::$imageset!=$v){
						$query .='&imageset='.$v;
					}
					if(($v=getVar('offline_pass', false, '', 'string')) !== false && !isset($arr['offline_pass'])){
						$query .='&offline_pass='.$v;
					}
					
					if(@($query{0} == '&')){
						$query=substr($query,1);
					}
					
					//break the query
					$arr = ArtaURL::breakupQuery($query);
					
					//var_dump($query);
					// SUBDOMAIN AND LINK MANAGEMENT SHOULD BE ONE IN ADMINCP.
					// assingning subdomains to links is very similar to define default link on a subdomain.
					// dont forget to add cache cleaning.
					// SEF SHOULD REMOVE ITS PACK COMPONENT.
					if(CLIENT=='site'){
						if(isset($arr['pack'])){ 
							//// if not, it's home page which will not be on a subdomain.
							$dname=$domain->getDomainOfPackage($arr['pack']);
							
							if($dname){
								$dparams = $domain->getAbsoluteParamsOfDomain($dname);

								$sitepath = self::getURL(array('domain'=>$dname, 'path'=>$sitepath,'path_info'=>'','query'=>''));
								
								foreach (array('tmpl','offline_pass') as $key){
									if(isset($dparams[$key]) AND !isset($arr[$key])) {
										$arr[$key] = $dparams[$key];
									}
								}
								
								if($arr == $dparams){
									if(isset($hash)){
										$sitepath .='#'.$hash;
									}
									return $sitepath;
								}
								
								$_arr = $arr;
								$__arr = array();
								$_dparams = $dparams;
								foreach(array('tmpl','template','language','imageset','offline_pass') as $key){
									if(isset($_arr[$key])){
										$__arr[$key] = $_arr[$key];
										unset($_arr[$key]);
									}
									if(isset($_dparams[$key]))	unset($_dparams[$key]);
								}
								
								if($_dparams == $_arr){
									$arr = $__arr;
									$arr['pack'] = 'index';
								}else{
									$_p = $arr['pack'];
									unset($arr['pack']); // AGAIN unsetting this may make sef creator to use default link. i should take care of it too.
								}
								
								$query = ArtaURL::makeupQuery($arr);
							}else{// So, it's pack is not set to a domain. Returning to main domain...
								if($domain->getParamsOfDomain($_SERVER['SERVER_NAME'])){
									// we are on foreign domain name.
									$sitepath = self::getURL(array('domain'=>$config->main_domain, 'path'=>$sitepath,'path_info'=>'','query'=>''));
								}//else{
									$deflink = ArtaLinks::getDefaultVars();
									foreach (array('tmpl','offline_pass') as $key){
										if(isset($deflink[$key]) AND !isset($arr[$key])) {
											$arr[$key] = $deflink[$key];
										}
									}

									if($arr == $deflink){
										if(isset($hash)){
											$sitepath .='#'.$hash;
										}
										return $sitepath;
									}
									
									$_arr = $arr;
									$__arr = array();
									$_deflink = $deflink;
									foreach(array('tmpl','template','language','imageset','offline_pass') as $key){
										if(isset($_arr[$key])){
											$__arr[$key] = $_arr[$key];
											unset($_arr[$key]);
										}
										if(isset($_deflink[$key]))	unset($_deflink[$key]);
									}

									if($_deflink == $_arr){
										$arr = $__arr;
										$_p= 'index';
									}

									$query = ArtaURL::makeupQuery($arr);
									/*
									if($deflink==$arr){
										$arr = ArtaURL::breakupQuery($query);
										foreach($arr as $k=>$v){
											if(isset($deflink[$k])){
												unset($arr[$k]);
											}
										}
										
										$sitepath = self::getURL($config->main_domain?array('domain'=>$config->main_domain, 'path'=>$sitepath,'path_info'=>'','query'=>''):
																						array('path'=>$sitepath,'path_info'=>'','query'=>''));
										
										if($arr!=array()){
											$query = ArtaURL::makeupQuery($arr);
										}else{
											return $sitepath;
										}

									}
								}*/
								// if we are on a domain assigned to a pack, make link to original hostname.
								// else use current domain.
								// and create link for $arr['pack']
							}
						}else{
							if($domain->getParamsOfDomain($_SERVER['SERVER_NAME'])){
								// we are on foreign domain name.
								$sitepath = self::getURL(array('domain'=>$config->main_domain, 'path'=>$sitepath,'path_info'=>'','query'=>''));
							}
							$deflink = ArtaLinks::getDefaultVars();
							foreach (array('tmpl','offline_pass') as $key){
								if(isset($deflink[$key]) AND !isset($arr[$key])) {
									$arr[$key] = $deflink[$key];
								}
							}
							$_p= 'index';
							$query = ArtaURL::makeupQuery($arr);
							
							// Default link cannot be assigned to a domain.
							// so it will behave like that in up!
							// check if on a domain assigned to a pack, return to main URL.
							// else use current.
							// and create home link.
						}
					}
				}

				

				//make Query
				switch(ArtaURL::getConfig()){
					case false:
					default:
						$res = ArtaURL::makeNone($query);
					break;
					case '1':
						$res = ArtaURL::makeSEF($query);						
					break;
					case '2':
						$res = ArtaURL::makeSEF2($query, isset($_p) ? $_p : null);						
					break;
				}
				
				$res = $sitepath.$res;
			}
			if(isset($hash)){
				$res .='#'.$hash;
			}
		}else{
			// simple URLs which not start with "index.php".
			if(strpos($url, '://') == true){
				return $url;
			}else{
				$res = $sitepath.$url;
			}
		}//finish $hasQuery
		
		
		$plugin->trigger('onAfterMakeURL', array($url, &$res, $hasQuery));
		
		return $res;
		
	}
	
	/**
	 * Generates URLs from imageset images.
	 * @static
	 * @param	string	$img	Image name
	 * @return	string
	 */
	 static function Imageset($image){
		$t=ArtaLoader::Template();
		$tmp='imagesets/'.$t->getImgSetName().'/'.$image;
		if(!file_exists(ARTAPATH_CLIENTDIR.'/'.$tmp)){
			$tmp='imagesets/default/'.$image;
		}
	 	return $tmp;
	 }

	/**
	 * Gets SEF enabled or not.
	 * 
	 * @static
	 * @return	bool
	 */
	static function getConfig(){
		$config = ArtaLoader::config();
		global $_CLIENTDATA;
		if($_CLIENTDATA['SEF_ENABLED'] == true){
			return $config->sef;
		}else{
			return 0;
		}
	}

	/**
	 * Makes simple URL using provided Query string
	 * 
	 * @static
	 * @param	string	$str	Query String
	 * @return	string
	 */
	static function makeNone($str){
		if(strlen($str) !==0){
			return 'index.php?'.$str;
		}else{
			return 'index.php';
		}

	}
	
	/**
	 * Makes SEF1 URL using provided Query string
	 * 
	 * @static
	 * @param	string	$str	Query String
	 * @return	string
	 */
	static function makeSEF($str){
		$config=ArtaLoader::Config();
		$arr = ArtaURL::breakupQuery($str);
		$q='';
		$type='';
		
		if(!isset($arr['language']) || $arr['language']==null){
			if(self::$language==null){
				$l=ArtaLoader::Language();
				self::$language = $l->getUserLang();
			}
			$arr['language']=self::$language;	
		}
		$type.='.'.$arr['language'];
		
		if(!isset($arr['type']) || $arr['type']==null){
			$type.='.html';
		}else{
			$type.='.'.$arr['type'];
		}
		
		
		if(isset($arr['type'])) unset($arr['type']);
		
		if(isset($arr['language']) && count($arr)==1) {
			if($config->sef_rewrite==0){
				return self::makeNone($str);
			}else{
				return 'index'.$type;
			}
		}elseif(isset($arr['language'])){
			unset($arr['language']);
		}
		
		
		if(is_array($arr)){
			foreach($arr as $k => $v){
				$q .= '/'.$k.'/'.$v;
			}
		}
		$q .=$type;
		if($config->sef_rewrite==1){
			return substr($q, 1);
		}else{
			return 'index.php'.$q;
		}
	}

	/**
	 * Makes SEF2 URL using provided Query string
	 * 
	 * @static
	 * @param	string	$str	Query String
	 * @param	string	$_p	Missing 'pack' variable from $str; if it's removed on makeURL() removed.
	 * @return	string
	 */
	static function makeSEF2($str, $_p=null){
		$config=ArtaLoader::Config();
		$arr = ArtaURL::breakupQuery($str);
		$domain = ArtaLoader::Domain();
		if(!isset($arr['pack']) || $arr['pack']==null){
			$arr['pack']=$_p ? $_p : 'index';
			if($config->sef_rewrite==0 AND $arr['pack']=='index'){
				return self::makeNone($str);
			}
		}
		$type='';
		
		if(!isset($arr['language']) || $arr['language']==null){
			if(self::$language==null){
				$l=ArtaLoader::Language();
				self::$language = $l->getUserLang();
			}
			$type.='.'.self::$language;
		}else{
			$type.='.'.$arr['language'];
		}
		
		if(!isset($arr['type']) || $arr['type']==null){
			$type.='.html';
		}else{
			$type.='.'.$arr['type'];
		}
		
		if(!$domain->getDomainOfPackage($arr['pack'])){
			$q='/'.$arr['pack'];
		}else{
			$q='';
		}
		$pack=$arr['pack'];
		unset($arr['pack']);
		
		if(isset($arr['type'])) unset($arr['type']);
		if(isset($arr['language'])) unset($arr['language']);
		
		if(!isset($GLOBALS['_PACKAGES_DIR'])){
			$GLOBALS['_PACKAGES_DIR']=ArtaFile::listDir(ARTAPATH_CLIENTDIR.'/packages');
		}
		if(in_array($pack,$GLOBALS['_PACKAGES_DIR'])){
			@include_once(ARTAPATH_CLIENTDIR.'/packages/'.$pack.'/sef.php');

			if(function_exists(ucfirst($pack).'SEFMaker')){
				$packname=ArtaString::removeIllegalChars($pack, array_merge(range('a','z'),range('0','9'), array('_')));
				
				$err = @error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE & ~E_DEPRECATED);
				eval('$layout='.ucfirst($packname).'SEFMaker($arr);');
				error_reporting($err);
				
				ksort($layout);
				foreach($layout as $k => $v){
					if(isset($arr[$v])){
						$q .= '/'.$arr[$v];
						unset($arr[$v]);
					}else{
						break;
					}
				}
				
				if(count($arr)!==0){
					$q .=$type.'?'.self::makeupQuery($arr);
				}else{
					$q .=$type;
				}
			}else{
				if(count($arr)!==0){
					$q .=$type.'?'.self::makeupQuery($arr);
				}else{
					$q .=$type;
				}
			}
		}else{
			if(count($arr)!==0){
				$q .=$type.'?'.self::makeupQuery($arr);
			}else{
				$q .=$type;
			}
		}
		if($config->sef_rewrite==1){
			return substr($q, 1);
		}else{
			return 'index.php'.$q;
		}
		
	}

	/**
	 * Breaks Query Strings in array where key is var name and value is var value
     * This method will not parse array indices.
	 * 
	 * @static
	 * @param	string	$q	Query String
	 * @return	mixed	false on non-array && non-object $vars, else returns array
	 */
	static function breakupQuery($q){
		if(is_string($q) == false){
			return false;
		}
        /*$res=array();
        parse_str($q, $res);
        return $res;*/
        
        $q = ArtaString::splitVars($q, '&', '=', true);
		return $q;	
	}

	/**
	 * Makes Query Strings from array where key is var name and value is var value
	 * 
	 * @static
	 * @param	array	$vars	Vars Array
	 * @return	mixed	false on non-array && non-object $vars, else returns string
	 */
	static function makeupQuery($vars){
		$res= ArtaString::stickVars($vars, '&', '=', true);
		return $res;
	}

    /**
	 * Returns Current Protocol. (http:// or https://)
	 * 
	 * @static
	 * @return	string
	 */
	static function getProtocol(){
		$prefix="http://";
		if((int)$_SERVER['SERVER_PORT'] == 443){
			$prefix='https://';
		}
		return $prefix;
	}
	
	/**
	 * Returns Current Domain name.
	 * 
	 * @static
	 * @return	string
	 */
	static function getDomain(){
		return $_SERVER['SERVER_NAME'];
	}
	
	/**
	 * Returns Current Port.
	 * 
	 * @static
	 * @return	int
	 */
	static function getPort(){
		return (int)$_SERVER['SERVER_PORT'];
	}
	
	/**
	 * Returns Current Directory.
	 * 
	 * @static
	 * @return	string
	 */
	static function getDir(){
		$p=(array)explode('/', $_SERVER['SCRIPT_NAME']);
		foreach($p as $k=>$v){
			if($v==''){
				unset($p[$k]);
			}
		}
		array_pop($p);
        if(count($p)==0){
            $s='/';
        }else{
		  $s='/'.implode('/', $p).'/';
        }
		return $s;
	}
	
	/**
	 * Returns Current Filename.
	 * 
	 * @static
	 * @return	string
	 */
	static function getFilename(){
		$p=explode('/', $_SERVER['SCRIPT_NAME']);
		return $p[count($p)-1];
	}
	
	/**
	 * Returns Current File Path.
	 * 
	 * @static
	 * @return	string
	 */
	static function getPath(){
		return $_SERVER['SCRIPT_NAME'];
	}
	
	/**
	 * Returns Current Pathinfo
	 * 
	 * @static
	 * @return	string
	 */
	static function getPathinfo(){
		return isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null;
	}

	/**
	 * Returns Query String
	 * 
	 * @static
	 * @return	string
	 */
	static function getQuery(){
		return $_SERVER['QUERY_STRING'];
	}
	
	/**
	 * Returns Current URL
	 * 
	 * @static
	 * @param	array	$c	Custom parts to use instead of real (protocol, domain, port, path, path_info, query).
	 * @return	string
	 */
	static function getURL($c=array()){
		$x=array(
			'protocol'=>self::getProtocol(),
			'domain'=>self::getDomain(),
			'port'=>self::getPort(),
			'path'=>self::getPath(),
			'path_info'=>self::getPathinfo(),
			'query'=>self::getQuery()
		);
		$c = ArtaUtility::array_extend($c, $x);
		$url = $c['protocol'];
		$url .= $c['domain'];
		$p = $c['port'];
		$url .= ($p== 80 || $p== 443) ? '' : ':'.$p ;
		$url .= $c['path'];
		$url .= $c['path_info'];
		$url .= $c['query'] != null ? '?'.$c['query'] : null;
                                
		return $url;
	}
	
	/**
	 * Returns site URL
	 * 
	 * @static
     * @param   bool    $add_port   Add port number to address if not 80?
     * @param	bool	$default_domain	Use default domain defined in configuration if available?
	 * @return	string
	 */
	static function getSiteURL($add_port=true, $default_domain=false){
		$p=self::getPort();
		$c=ArtaLoader::Config();
		$url = $c->secure_site == 1 ? 'https://' : 'http://';
		if($c->secure_site==0 && $p== 443){
			$url='https://';
		}
		$url .= ($default_domain AND $c->main_domain!='') ? $c->main_domain : self::getDomain();
        if($add_port==true){
		  $url .= ($p== 80 || $p== 443) ? '' : ':'.$p ;
        }
		if(CLIENT=='site'){
			$url .= self::getDir();
		}else{
			$url .= substr($d=self::getDir(),0 , strlen($d)-6);
		}

		return $url;
	}
	
	/**
	 * Returns admin URL
	 * 
	 * @static
     * @param   bool    $add_port   Add port number to address if not 80?
     * @param	bool	$default_domain	Use default domain defined in configuration if available?
	 * @return	string
	 */
	static function getAdminURL($add_port=true, $default_domain=false){
		$p=self::getPort();
		$c=ArtaLoader::Config();
		$url = $c->secure_admin == 1 ? 'https://' : 'http://';
		if($c->secure_admin==0 && $p== 443){
			$url='https://';
		}
		$url .= ($default_domain AND $c->main_domain!='') ? $c->main_domain : self::getDomain();
        if($add_port==true){
		  $url .= ($p== 80 || $p== 443) ? '' : ':'.$p ;
        }
		if(CLIENT=='admin'){
			$url .= self::getDir();
		}else{
			$url .= self::getDir().'admin/';
		}
		return $url;
	}
	
	/**
	 * Returns current client URL
	 * 
	 * @static
	 * @return	string
	 */
	static function getClientURL(){
		return (CLIENT=='site')?self::getSiteURL():self::getAdminURL();
	}
    
    /**
	 * Returns site URL in user friendly version
	 * 
	 * @static
     * @param   bool
	 * @return	string
	 */
	static function getFriendlyURL(){
        $dir=CLIENT=='site' ? ArtaURL::getDir() : substr($d=self::getDir(),0 , strlen($d)-6);
        return ArtaURL::getURL(array(
            'protocol'=>'',
            'path'=> substr($dir, 0, strlen($dir)-1),
            'path_info'=>'',
            'query'=>''
        ));
        
	}
	
	
}
?>