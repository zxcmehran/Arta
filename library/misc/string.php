<?php 
/**
 * There are Many useful methods in ArtaString to handle strings
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
 * ArtaString Class
 * String Processor and Tools
 * @static
 */
class ArtaString {
	
	/**
	 * Splits Strings by an character. Its much like explode() but it supports
	 * Escaping Characters and Once splitting.
	 * 
	 * @static
	 * @param	string	$string	String to explode
	 * @param	string	$by	Splitter character
	 * @param	bool	$once	Split once or not
	 * @param	bool	$support_escaping	Support Escaping or not
	 * @return	array	If delimiter is an empty string (""), it will return FALSE. If delimiter contains a value that is not contained in string, then explode() will return an array containing string.
	 */
	static function split($string, $by=',', $once=false, $support_escaping=false){
		if($support_escaping==true){
			$replace = array();
			// get Escaped characters
			$escaped[0]='\\\\';
			$escaped[1]='\\'.$by;
			
			//make an unique id for escaped chars
			foreach($escaped as $v){
				if(!array_key_exists($v, $replace)){
					$rand = '_'.ArtaString::makeRandStr(4).'_';
					while(in_array($rand, $replace) || is_numeric(strpos($string, $rand)) || is_numeric(strpos($rand, $by))){
						$rand = '_'.ArtaString::makeRandStr(4).'_';
					}
					$replace[$v] = $rand;
				}
				
			}
			
			foreach($replace as $k => $v){
				$string=str_replace($k, $v, $string);
			}
			
		}
		
		$res = $once == true ? explode($by, $string,  2) : explode($by, $string);
		
		if($support_escaping==true){
			// move "\\" to end
			if(array_key_exists('\\\\', $replace)){
				$v = $replace['\\\\'];
				unset($replace['\\\\']);
				$replace['\\\\']=$v;
			}

			foreach($res as $k=>$v){
				foreach($replace as $k2=>$v2){
					$k2 = substr($k2, 1);
					$v= str_replace($v2, $k2, $v);
				}			
				$res[$k]=$v;
			}
		}
		return $res;
	}
	
	/**
	 * Opposite of {@link	ArtaString::split()}
	 * @static
	 */
	static function stick($array, $by=',', $support_escaping=false){
		if($support_escaping){
			foreach($array as &$v){
				$v=addcslashes($v, '\\'.$by);
			}
		}
		return implode($by,$array);
	}

	/**
	 * Exports vars from strings like URL queries. (myvar=myvalue&name=mehran&ok=0)
	 * 
	 * @static
	 * @param	string	$str	String to export vars from it.
	 * @param	string	$by	Variable splitter
	 * @param	string	$equality	Equality operator
	 * @param	bool	$urldecode	Decode Values using urldecode? usable when splitting QUERY_STRING
	 * @return	mixed	false on null $str else returns array where keys are varname
	 */
	static function splitVars($str, $by='&', $equality='=', $urldecode=false){
		if($str){
			$res = array();
			$vars=explode($by, $str);
			if(is_array($vars) && $vars[0]==true){
				foreach($vars as $k => $v){
					if($v==''){
						continue;
					}
					$var[$k] = 	ArtaString::split($v, $equality, true);
					if(isset($var[$k][0])){
						if(!isset($var[$k][1])){
							$var[$k][1]=null;
						}
						if((string)$var[$k][0]!==''){
							$kname=$urldecode==false ? $var[$k][0] : urldecode($var[$k][0]);
							$res[$kname]=$urldecode==false ? $var[$k][1] : urldecode($var[$k][1]);
						}
					}
					
				}
			}
			return $res;
		}else{
			return false;
		}
	}

	/**
	 * Its opposite of {@link	ArtaString::splitVars()}
	 * 
	 * @static
	 * @param	array	$vars	Vars array to stick
	 * @param	string	$by	Variable splitter
	 * @param	string	$equality	Equality operator
	 * @param	bool	$urlencode	Encode Values using urlencode? usable when sticking QUERY_STRING
	 * @return	string	
	 */
	static function stickVars($vars, $by='&', $equality='=', $urlencode=false){
		if(is_array($vars) == false && is_object($vars) == false)	{
			return false;
		}
		$var = array();
		foreach($vars as $k=>$v){
			$var[] = ($urlencode==false ? $k : urlencode($k)).$equality.($urlencode==false ? $v : urlencode($v));
		}
		$res = '';
		foreach($var as $v){
			$res .= $v.$by;
		}
		return substr($res, 0, strlen($res)-strlen($by));
	}
	
	/**
	 * Escapes string to use in Regular Expressions.
	 * 
	 * @static
	 * @param	string	$str	String to escape
	 * @param	string	$delimeter	Delimeter of regexp if any used. Most popular delimeter is '/'.
	 * @return	string
	 */
	static function escapeRegEx($str, $delimeter=null){
		return preg_quote($str, $delimeter); // it may take care of everything.
		
		if($str == null ){
			return false;
		}
		$esc = array('\\', '^', '$', '.', '[', ']', '|', '(', ')', '?', '*', '+', '{', '}', '-', '=', '!', '<', '>', ':');
		if($delimeter){
			array_push($esc, $delimeter);
		}
		$len = strlen($str);
		$chars = array();
		while($len > 0){
			$len--;
			$chars[$len]=$str{$len};
		}
		foreach($chars as $charnum => $charvalue){
			if(in_array($charvalue, $esc)){
					$chars[$charnum] = '\\'.$charvalue;
			}
		}
		ksort($chars);
		$res='';
		foreach($chars as $v){
			$res .=$v;
		}
		return $res;
		
	}
	
	/**
	 * Encodes(Hashes) strings.
	 * 
	 * @static
	 * @param	string	$str	what to hash
	 * @param	string	$type	hashing type. 'artahash' or 'artahash-no-md5'
	 * @return	string
	 */
	static function hash($str, $type='artahash'){
		if($str == null){
			return false;
		}
		switch($type){
			case 'artahash':
				$str=md5($str);
				$config = ArtaLoader::Config();
				$len = strlen($config->secret);
				return md5(substr(md5($config->secret), 0, (int)($len/2)).$str.substr(md5($config->secret), (int)($len/2), $len));
			break;
			case 'artahash-no-md5':
				$config = ArtaLoader::Config();
				$len = strlen($config->secret);
				return md5(substr(md5($config->secret), 0, (int)($len/2)).$str.substr(md5($config->secret), (int)($len/2), $len));
			break;
			default:
				return false;
			break;
		}
	}
	
	/**
	 * Makes Random string using A-Z a-z 0-9
	 * 
	 * @static
	 * @param	int	$length	String length
	 * @return	string
	 */
	static function makeRandStr($length=6) {
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makepass="";
		mt_srand(10000000*(double)microtime());
		for ($i = 0; $i < $length; $i++)
		$makepass .= $salt[mt_rand(0,$len - 1)];
		return $makepass;
	}
	
	/**
	 * Removes Illegal characters from a string
	 * 
	 * @static
	 * @param	string	$string	String to edit
	 * @param	array	$chars	Characters list
	 * @param	bool	$legal	does $chars have legal characters or illegal ones?
	 * @return	string
	 */
	static function removeIllegalChars($string, $chars, $legal=true){
		$i=0;
		$res=array();
		while($i<strlen($string)){
			if(in_array($string{$i}, $chars)==$legal){
				$res[]=$string{$i};
			}
			$i++;
		}
		return implode('', $res);
	}
	
	/**
	 * Parses file like INI format but do not parses anything.
	 * @static
	 * @param	string	$ini	INI string or INI file name
	 * @param	string	$lineEscapeChar	Character to be used to define a line as a comment
	 * @param	bool	$isFile	Is first parameter a filename or string on ini file body?
	 * @return	array
	 */
	static function parseINI($ini, $lineEscapeChar=';', $isFile=false){
		$content = $isFile?ArtaFile::read($ini):$ini;
		$content = str_replace(array("\r\n", "\r", "\n"), "\n", $content);
		if(substr(trim($content, ' '), -1) !== "\n"){
			$content=$content."\n";
		}
		$fh=explode("\n",$content);
		$res=array();
		foreach ($fh as $line) {
			$line=ltrim($line);
			if(substr($line,0,strlen($lineEscapeChar)) !== $lineEscapeChar && trim($line)!==''){
				$pos = strpos($line, '=');
				if($pos>0){
					$what = substr($line, 0, $pos);
					$is = substr($line, $pos+1);
					$res[$what]=str_replace('\n', "\n", $is);
				}
			}
		}
		return $res;
	}

}
?>