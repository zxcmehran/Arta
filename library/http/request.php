<?php 
/**
 * ArtaRequest is defined in this file
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 2 2012/12/06 17:55 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaRequest Class
 * Request tools and essentials.
 * 
 * @static
 */
class ArtaRequest {

	/**
	 * Adds input variable to given hash and $_REQUEST and $_SERVER['QUERY_STRING'] (if hash is set to "get")
	 * Its defined because of some processes like that we need set "pack" query variable at homepage mode.
	 *
	 * @static
	 * @param	string	$var	 Variable name
	 * @param	mixed	$value	value to set
	 * @param	string	$hash	Hash name; GET or POST 
	 * @return	bool
	 */
	static function addVar($var, $value, $hash=null){
		if(!isset($_SERVER['QUERY_STRING_ORIGINAL'])){
			$_SERVER['QUERY_STRING_ORIGINAL']=@$_SERVER['QUERY_STRING'];
		}
		if($hash==null){$hash=$_SERVER['REQUEST_METHOD'];}
		if(!is_string($var)||  is_null($value)|| ! is_string($hash)){
			return false;
		}
		$hash=strtoupper($hash);
		if($hash=='GET'){
			$arr=ArtaURL::breakupQuery($_SERVER['QUERY_STRING']);
			$arr[$var]=$value;
			$_SERVER['QUERY_STRING']=ArtaURL::makeupQuery($arr);
		}
		eval('$_'.$hash.'[$var]=$value;');
		$_REQUEST[$var]=$value;
		return true;
	}
	
	/**
	 * Converts SEF url format to Normal format.
	 * SEF structure:		artaproject.com/index.php/pack/user/view/register/
	 * SEF structure with rewrite support:		artaproject.com/pack/user/view/register/
	 *
	 * @static
	 * @param	string	$sef	 path info in SEF format
	 * @return	mixed	false on failure.It will appear on invalid SEF format. Converted URL string on success.
	 */
	static function SEFtoNormal($sef){
		if($sef){
			$l=ArtaLoader::Language();
			if(is_numeric(strpos($sef, '.'))==true){
				$x=explode('.', $sef);
				$type=$x[count($x)-1];
				$lang=$x[count($x)-2];
				$y=true;
				$sef=substr($sef, 0, strlen($sef)-(strlen($x[count($x)-1])+1));
			}else{
				$type='html';
				$lang=$l->getUserLang();
			}
			
			if(!is_dir(ARTAPATH_CLIENTDIR.'/languages/'.ArtaFilterinput::safeAddress($lang))){
				$lang=$l->getUserLang();
				$y=false;
			}
			$lang=ArtaFilterinput::safeAddress($lang);
			
						
			if($type=='htm'){$type='html';}
			$valid=array('html', 'pdf', 'xml');
			if(!in_array($type, $valid)){
				$type='raw';
			}
			
			
			if(isset($y) && $y==true){
				$sef= substr($sef, 0, strlen($sef)-strlen($lang)-1);
			}
			
			while($sef{0}=='/'){
				$sef = substr($sef, 1);
			}
			
			$vars = explode('/', $sef);
			$res = array('type'=>$type, 'language'=>$lang);
			
			if($vars==array('index')){
				return $res;
			}
			
			$value=0;
			if(is_array($vars)){
				foreach($vars as $k=>$v){
					
					if($value === 0){
						$res[$v] = '';
						$value = $v;
					}else{
						$res[$value] = $v;
						$value = 0;
					}		
				}
			}
			//return ArtaURL::makeupQuery($res);
			return $res;
		}else{
			return false;
		}
	}

	/**
	 * Converts SEF2 url format to Normal format.
	 * Works with sef.php on Package root.
	 * Note that simple queries (like ?myvar=value&yourvar=TheValue) can be passed on sef.php incompatability.
	 * SEF2 structure:		artaproject.com/index.php/user/register/
	 * SEF2 structure with rewrite support:		artaproject.com/user/register/
	 *
	 * @static
	 * @param	string	$sef2	 path info in SEF2 format
	 * @return	mixed	false on failure.It will appear on invalid SEF2 format. Converted URL string on success.
	 */
	static function SEF2toNormal($sef2){
		if($sef2){
			$l=ArtaLoader::Language();
			if(is_numeric(strpos($sef2, '.'))==true){
				$x=explode('.', $sef2);
				$type=$x[count($x)-1];
				$lang=$x[count($x)-2];
				$y=true;
				$sef2=substr($sef2, 0, strlen($sef2)-(strlen($x[count($x)-1])+1));
			}else{
				$type='html';
				$lang=$l->getUserLang();
			}
			
			if(!is_dir(ARTAPATH_CLIENTDIR.'/languages/'.ArtaFilterinput::safeAddress($lang))){
				$lang=$l->getUserLang();
				$y=false;
			}
			$lang=ArtaFilterinput::safeAddress($lang);
			$domain = ArtaLoader::Domain();
			$params = $domain->getParamsOfDomain($_SERVER['SERVER_NAME']);
			if($params){
				$pack = $params['pack'];
			}
			
			if($type=='htm'){$type='html';}
			$valid=array('html', 'pdf', 'xml');
			if(!in_array($type, $valid)){
				$type='raw';
			}
		/*	self::addVar('type',$type);
			self::addVar('language',$lang);*/
			
			$res=array('type'=>$type,'language'=>$lang);
			
			if(isset($y) && $y==true){
				$sef2= substr($sef2,0, strlen($sef2)-strlen($lang)-1);
			}
			
			$sef2 = trim($sef2, '/');
			
			$vars = explode('/', $sef2);
			if(!isset($GLOBALS['_PACKAGES_DIR'])){
				$GLOBALS['_PACKAGES_DIR']=ArtaFile::listDir(ARTAPATH_CLIENTDIR.'/packages');
			}
			if(isset($pack)){
				$vars = array_merge(array($pack),$vars);
			}
			if(@in_array($vars[0],$GLOBALS['_PACKAGES_DIR'])){
				@include_once(ARTAPATH_CLIENTDIR.'/packages/'.$vars[0].'/sef.php');
				
				if(function_exists(ucfirst($vars[0]).'SEFParser')){
					$packname=ArtaString::removeIllegalChars($vars[0], array_merge(range('a','z'),range('0','9'), array('_')));
					$packname=$vars[0];
					$secondfrag = @$vars[1];
					$vars=array_slice($vars, 1);
					
					// E_NOTICE and E_DEPRECATED are in E_ALL
					// but E_STRICT is added to E_ALL starting from PHP >= 5.4.0
					$err = @error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE & ~E_DEPRECATED);
					eval('$layout='.ucfirst($packname).'SEFParser($vars);');
					error_reporting($err);
					
					ksort($layout);
					if(isset($pack)==false){
						$res['pack']=$packname;
					}
									
					foreach($vars as $k=>$v){
					
						if(isset($layout[$k])){
							$res[$layout[$k]]=$v;
						}
						
					}
					
				}else{
					$res['pack']=$vars[0];
				}
			}else{
				$res['pack']=@$vars[0];
			}
			// INDEX is reserved here ...
			if(@$res['pack']=='index'){
				unset($res['pack']);
			}
			if(isset($pack) AND @$secondfrag=='index'){
				unset($res[$layout[0]]);
			}
			//return ArtaURL::makeupQuery($res);
			
			return $res;
		}
	}

	/**
	 * Adds variables setted via rewrite or path info to $_SERVER['QUERY_STRING'] and merges vars to $_GET or $_POST (depending to request method) and $_REQUEST
	 * @static
	 */
	static function setVars(){
		$config=ArtaLoader::Config();
		if(!isset($_SERVER['QUERY_STRING_ORIGINAL'])){
			$_SERVER['QUERY_STRING_ORIGINAL']=@$_SERVER['QUERY_STRING'];
		}
		if(isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO'])>0){
			$sef_type=ArtaRequest::getSEFType($_SERVER['PATH_INFO']);
			$q = '';
			if(!is_array($sef_type)){$sef_type=array();}
			foreach($sef_type as $k => $v){
				$_REQUEST[$k]=$v;
				eval('$_'.strtoupper($_SERVER["REQUEST_METHOD"]).'[$k]=$v;');
				$q .=$k.'='.$v.'&';
			}
			$len = strlen($q);
			$len--;
			$_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'].'&'.substr($q, 0, $len) : substr($q, 0, $len);
			
		}
		$quesmark=strpos($_SERVER['REQUEST_URI'],'?');
		if($quesmark>0){
			$redirect_url=substr($_SERVER['REQUEST_URI'],0,$quesmark);
		}else{
			$redirect_url=$_SERVER['REQUEST_URI'];
		}
		//if()
		// rewrite mode
		if(/*isset($_SERVER['REDIRECT_URL'])  && strlen($_SERVER['REDIRECT_URL'])>0*/$redirect_url!=$_SERVER['SCRIPT_NAME'].@$_SERVER['PATH_INFO']){
			if(isset($_SERVER['REDIRECT_URL'])==false){
				$_SERVER['REDIRECT_URL'] = $redirect_url; // Fill it if not available.
			}
			$xx=explode(':', $_SERVER['HTTP_HOST']);
			if(@$xx[1]){
				$_SERVER['SERVER_PORT']=$xx[1];
			}
			unset($xx);
			$path=substr($_SERVER['SCRIPT_NAME'], 0, (strlen($_SERVER['SCRIPT_NAME'])-strlen('index.php')));
			$vars=substr($redirect_url, strlen($path));
			$normal=ArtaRequest::getSEFType($vars);
			if(!is_array($normal)){$normal=array();}
			$q = '';
			foreach($normal as $k => $v){
				$_REQUEST[$k]=$v;
				eval('$_'.strtoupper($_SERVER["REQUEST_METHOD"]).'[$k]=$v;');
				$q .=$k.'='.$v.'&';
			}
			$len = strlen($q);
			$len--;
			if($q!='')
			$_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'].'&'.substr($q, 0, $len) : substr($q, 0, $len);

		}

		/* DETECTING HOMEPAGE MODE */
		$q=ArtaURL::breakupQuery(trim((string)$_SERVER['QUERY_STRING']));
		// remove legal vars
		if(isset($q['language'])) unset($q['language']);
		if(isset($q['tmpl'])) unset($q['tmpl']);
		if(isset($q['template'])) unset($q['template']);
		if(isset($q['imageset'])) unset($q['imageset']);
		if(isset($q['offline_pass'])) unset($q['offline_pass']);
		if(isset($q['limit'])) unset($q['limit']);
		if(isset($q['limitstart'])) unset($q['limitstart']);
		if(isset($q['type']) && $q['type']=='html') unset($q['type']);
		if($q==array()){
			$_SERVER['IS_HOMEPAGE']=true;
		}else{
			$_SERVER['IS_HOMEPAGE']=false;
		}
		define('IS_HOMEPAGE', $_SERVER['IS_HOMEPAGE']);	
		/* end DETECTING HOMEPAGE MODE */
		
		define('REQUEST_VARS_SET', true);

	}

	/**
	 * Secures URL if needed.
	 * @static
	 */
	static function setSecure(){
		$config=ArtaLoader::Config();
		eval('$s=$config->secure_'.CLIENT.';');
		if($s && ArtaURL::getProtocol() !== 'https://' && $_SERVER['REQUEST_METHOD']=='GET'){
			redirect(ArtaURL::getURL(array('protocol'=>'https://', 'path'=>null, 'path_info'=>null,'query'=>null)).makeURL('index.php?'.ArtaURL::getQuery()), '', '', false);
		}
	}

	/**
	 * Detects SEF Type and converts it to normal and then returns.
	 *
	 * @static
	 * @param	string	$data	path info to detect SEF type
	 * @return	string	Simple query
	 */
	static function getSEFType($data){
		$c=ArtaLoader::Config();
		switch((int)$c->sef){
			case 1:
				$sef = ArtaRequest::SEFtoNormal($data);		
			break;
			case 2:
				$sef = ArtaRequest::SEF2toNormal($data);
			break;
		}
		return @$sef;
	}
	
	/**
	 * Gets input vars 
	 * 
	 * @static
	 * @param	string	$name	var name
	 * @param	string	$default	default value on not set or null
	 * @param	string	$hash	hash type
	 * @param	string	$type	value type to clean var
	 * @return	mixed	var value.
	 */
	static function getVar($name, $default = null, $hash = 'default', $type='default')
	{
		if($name == null){
			return $default;
		}

		// Ensure hash is uppercase
		$hash = strtoupper( $hash );
		if ($hash === 'METHOD') {
			$hash = strtoupper( $_SERVER['REQUEST_METHOD'] );
		}

		// Get the input hash
		switch ($hash)
		{
			case 'GET' :
				$input = &$_GET;
				break;
			case 'POST' :
				$input = &$_POST;
				break;
			case 'FILES' :
				$input = &$_FILES;
				break;
			case 'COOKIE' :
				$input = &$_COOKIE;
				break;
			case 'ENV'    :
				$input = &$_ENV;
				break;
			case 'SERVER'    :
				$input = &$_SERVER;
				break;
			default:
				$input = &$_REQUEST;
				$hash = 'REQUEST';
				break;
		}
		$var = (isset($input[$name]) && $input[$name] !== null) ? self::clean($input[$name],$type) : $default;
		
		return $var;
	}

	/**
	 * Returns all vars of a hash on selection return type
	 *
	 * @static
	 * @param	string	$hash	hash type. POST, GET, etc.
	 * @param	string	$ret_type	Return type. you can pass "object" or "array"
	 * @return	mixed	selection ret_type
	 */
	static function getVars($hash='default', $ret_type="array"){
		$hash = strtoupper($hash);
		switch ($hash)
		{
			case 'GET' :
				$input = &$_GET;
				break;
			case 'POST' :
				$input = &$_POST;
				break;
			case 'FILES' :
				$input = &$_FILES;
				break;
			case 'COOKIE' :
				$input = &$_COOKIE;
				break;
			case 'ENV'    :
				$input = &$_ENV;
				break;
			case 'SERVER'    :
				$input = &$_SERVER;
				break;
			default:
				$input = &$_REQUEST;
				$hash = 'REQUEST';
				break;
		}
		switch($ret_type){
			case 'object':
				$res='new stdClass';
				$pre = '->{\'';
				$suf = '\'}';
			break;
			case 'array':
				$res = 'array';
				$pre="['";
				$suf="']";
			break;
		}
		eval('$res = '.$res.'();');
		foreach($input as $k => $v){
			@eval('$res'.$pre.addslashes($k).$suf.' = $v;');
		}
		return $res;
	}
	
	/**
	 * Cleans a var value by using {@link	ArtaFilterinput::clean()}
	 * @static
	 */
	static function clean($str, $type='default'){
		return ArtaFilterInput::clean($str, $type);
	}
	
	/**
	 * Caches data on user browser and lets it reload when your modified time changed.
	 * Pass your modified time to decide breaking execution or update browser cache.
	 * @param	string	$modtime	Timestamp of modification
	 * @param	int	$max_age	Period of re-checking file by browser, in seconds, set zero to check everytime.
	 * @param	string	$other_controls	Other Cache-Control directives to use. This function uses max-age and must-revalidate by itself.
	 * @static
	 */
	static function cacheByModifiedDate($modtime, $max_age=300, $other_controls=''){
		$max_age = (int)$max_age<0 ? 0 : (int)$max_age;
		$other_controls = trim($other_controls);
		if(strlen($other_controls) > 0 AND $other_controls[0]!=',')
			$other_controls = ', '.$other_controls;
		header("Pragma: ");
		header("Expires: ");
		header("ETag: ");
		header("Cache-Control: max-age=".$max_age.", must-revalidate".$other_controls);
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $modtime) . ' GMT');
		if($modtime <= strtotime(@$_SERVER['HTTP_IF_MODIFIED_SINCE'])){
			header('HTTP/1.1 304 Not Modified');
			die();
		}
	}
	
	/**
	 * Caches data on user browser and lets it reload when your ETag checksum changed.
	 * Pass your checksum to decide breaking execution or update browser cache.
	 * @param	string	$etag	Data Checksum
	 * @param	int	$max_age	Period of re-checking file by browser, in seconds, set zero to check everytime.
	 * @param	string	$other_controls	Other Cache-Control directives to use. This function uses max-age and must-revalidate by itself.
	 * @static
	 */
	static function cacheByETag($etag, $max_age=300, $other_controls=''){
		$max_age = (int)$max_age<0 ? 0 : (int)$max_age;
		$other_controls = trim($other_controls);
		if(strlen($other_controls) > 0 AND $other_controls[0]!=',')
			$other_controls = ', '.$other_controls;
		header("Pragma: ");
		header("Expires: ");
		header("Last-Modified: ");
		header("Cache-Control: max-age=".$max_age.", must-revalidate".$other_controls);
		header("ETag: ".$etag);
		if(isset($_SERVER['HTTP_IF_NONE_MATCH']) AND $etag == @$_SERVER['HTTP_IF_NONE_MATCH']){
			header('HTTP/1.1 304 Not Modified');
			die();
		}
	}
	
	
}

?>