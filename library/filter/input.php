<?php 
/**
 * Some filters for inputs are located at this file
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
 * ArtaFilterinput Class
 * Filters for validating and manipulating inputs
 * 
 * @static
 */
class ArtaFilterinput{

	
	/**
	 * Removes malicious characters from disk addresses like  .. and :// (for blocking remote file inclusions or etc.) and etc.
	 * note that this must be applied to file or dir names. because it strips directory separators.
	 *
	 * @static
	 * @param	mixed	$str	string to escape.you can use arrays to define many.
	 * @return	mixed	escaped string.type is like $str
	 */
	static function safeAddress($str){
		if(!is_array($str)){$str=array($str);}
		foreach($str as $k=>$v){
			$v= str_replace('..', '', $v);
			$v= str_replace('://', '', $v);
			$v= str_replace('/', '', $v);
			$v= str_replace('\\', '', $v);
			$v= str_replace(':', '', $v);
			$v= str_replace("\0", '', $v);
			$str[$k]= ArtaFile::safeName($v);
		}
		if(count($str) == 1 && isset($str[0])){
			$str=$str[0];
		}
		return $str;
	}

	/**
	 * Cleans vars by type of them.
	 * Cleaning types: safe-html,very-safe-html,int,integer,filename,double,float,array,bool,boolean,string,no-html,datetime,date,funcname,email
	 * 
	 * @static
	 * @param	mixed	$var	Variable to clean.
	 * @param	string	$type	Cleaning type.
	 * @return	mixed
	 */
	static function clean($var, $type='default'){
		if((is_array($var)||is_object($var)) && is_array($type)){
			foreach($var as $k=>$v){
				if(isset($type[$k])){
					if(is_array($var)){
						$var[$k]=self::clean($v, $type[$k]);
					}else{
						$var->$k=self::clean($v, $type[$k]);
					}
				}
			}
			$result=$var;
		}else{
			$type=strtolower($type);
			
			switch($type){
				case 'int':
				case 'integer':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					preg_match('/-?[0-9]+/', (string)$result, $matches);
					$result = @(int)$matches[0];
				break;
				case 'filename':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					$result=@self::safeAddress((string)$result);
				break;
				case 'double':
				case 'float':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					preg_match('/-?[0-9]+(\.[0-9]+)?/', (string)$result, $matches);
					$result = @(float)$matches[0];
				break;
				case 'array':
					if(!is_array($var)){
						$result=array($var);	
					}else{
						$result=$var;
					}
				break;
				case 'bool':
				case 'boolean':
					$result=@(bool)$var;
				break;
				case 'string':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
				break;
				case 'no-html':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					$result=htmlspecialchars((string)$result);
				break;
				case 'safe-html':
					$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'link', 'style', 'script', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					$result=self::rstrip_tags((string)$result, '<'.implode('><', $ra1).'>');
				break;
				case 'very-safe-html':
					$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					$result=self::rstrip_tags((string)$result, '<'.implode('><', $ra1).'>');
				break;
				case 'datetime':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					$result=ArtaDate::convertInput($result);
				break;
				case 'date':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					$result=ArtaDate::convertInput($result, true, true);
				break;
				case 'funcname':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					preg_match('@^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$@', (string)$result, $matches);
					$result = isset($matches[0])?@(string)$matches[0]:false;
				break;
				case 'email':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					if(@self::is_email((string)$result)){
						$result=@(string)$result;
					}else{
						$result=false;
					}
				break;
				default:
					$result=$var;
				break;
			}
		}
		return $result;
	}
	
	/**
	 * Acts as strip_tags() but gets INValid tags as second parameter.
	 * NOTE: Comments will not be removed.
	 * 
	 * @static
	 * @param	string	$text	Text to strip tags in it
	 * @param	string	$tags	INValid tags to be stripped.
	 * @return	string
	 */
	static function rstrip_tags($text, $tags = '') {
		preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
		$tags = @array_unique($tags[1]);
		if(is_array($tags) AND count($tags) > 0) {
			$r= preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
			return preg_replace('@<('. implode('|', $tags) .')\b.*?/?>@si', '', $r);
		}
		return $text;
	}
	
	/**
	 * Limits array values lengths.
	 * 
	 * @static
	 * @param	array	$a	Array to cut values
	 * @param	array	$lengths	length of every key of $a
	 * @return	array
	 */
	static function array_limit($a, $lengths){
		foreach($a as $k=>$v){
			if(isset($lengths[$k])){
				$a[$k]=@substr((string)$v, 0, (int)$lengths[$k]);
			}
		}
		return $a;
	}
	
	/**
	 * Trims array contents
	 * 
	 * @static 
	 * @param	array	$a	Array to process
	 * @param	array	$charlist
	 * @return	array
	 */
	static function trim($a, $charlist=null){
		foreach($a as $k=>$v){
			if(!is_array($v) && !is_object($v)){
				$v=@(string)$v;
				if(is_array($a)){
					$a[$k]=$charlist==null ? trim($v) : trim($v,$charlist);
				}else{
					$a->$k=$charlist==null ? trim($v) : trim($v,$charlist);
				}
			}else{
				if(is_array($a)){
					$a[$k]=ArtaFilterinput::trim($v, $charlist);
				}else{
					$a->$k=ArtaFilterinput::trim($v, $charlist);
				}
			}
		}
		return $a;
	}
	
	/**
	 * Gets Uploaded data error. Returns false on no error else returns Error Message
	 * 
	 * @static
	 * @param	int	$code	Error code
	 * @return	mixed
	 */
	static function uploadErr($code){
	 	if($code==UPLOAD_ERR_OK){
	 		return false;
	 	}elseif($code==UPLOAD_ERR_INI_SIZE){
			return trans('UPL_MORE THAN SIZE DEFINED IN INI');
		}elseif($code==UPLOAD_ERR_FORM_SIZE){
			return trans('UPL_MORE THAN SIZE DEFINED IN FORM');
		}elseif($code==UPLOAD_ERR_PARTIAL){
			return trans('UPL_UPLOADED PARTIAL');
		}elseif($code==UPLOAD_ERR_NO_FILE){
			return trans('UPL_NO FILE UPLOADED');
		}else{
			return trans('UPL_UNKNOWN UPLOAD ERR');	
		}
	 }
	 
	 /**
	 * Checks to see if the text is a valid email address.
	 *
	 * @static
	 * @param	string	$user_email	The email address to be checked.
	 * @return	bool	Returns true if valid, otherwise false.
	 */
	static function is_email($user_email) {
		$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
		if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
			if (preg_match($chars, $user_email)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
}
?>